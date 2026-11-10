<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ApprovalStatus;
use App\Enums\CmsContentType;
use App\Enums\JobStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Enums\PlatformModule;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\ApprovalRequest;
use App\Models\CmsContent;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobListing;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\ContentItems;
use App\Services\PlatformSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JobListingAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();
    }

    public function test_manager_publishes_job_and_it_appears_on_homepage_and_search(): void
    {
        $manager = $this->signInAdmin(AdminRole::JobsAdmin);
        $company = Company::factory()->verified()->create(['name' => 'Mekong Retail Group']);
        $category = JobCategory::factory()->create(['name' => ['en' => 'Accounting', 'km' => null]]);

        $this->get('/admin/jobs/listings/create')->assertOk()->assertSee('How candidates apply');
        $response = $this->post('/admin/jobs/listings', $this->jobInput($company, [
            'job_category_id' => $category->id,
            'status' => 'published',
        ]));

        $job = JobListing::where('slug', 'senior-accountant')->firstOrFail();
        $response->assertRedirect("/admin/jobs/listings/{$job->id}/edit");
        $this->assertTrue($job->isLive());
        $this->assertSame($manager->id, $job->created_by);
        $this->assertSame(now()->addDays(30)->toDateString(), $job->closes_at->toDateString());
        $this->assertDatabaseHas('audit_logs', ['action' => 'jobs.listing.created', 'auditable_id' => $job->id]);

        $item = CmsContent::where('source_type', 'job_listing')->where('source_id', $job->id)->firstOrFail();
        $this->assertSame(CmsContentType::Job, $item->content_type);
        $this->assertSame('Accounting', $item->badge);
        $this->assertSame('/jobs/senior-accountant', $item->link_url);
        $this->assertSame('Mekong Retail Group', $item->meta['company']);
        $this->assertSame('$900 – $1,300 / month', $item->meta['salary']);
        $this->assertTrue($item->meta['verified']);
        $this->assertSame('Senior Accountant', app(ContentItems::class)->latest(CmsContentType::Job, 5)->first()->title);

        $this->get('/jobs/senior-accountant')->assertOk()->assertSee('Senior Accountant')->assertSee('Mekong Retail Group');
    }

    public function test_salary_and_apply_details_are_validated(): void
    {
        $this->signInAdmin(AdminRole::JobsAdmin);
        $company = Company::factory()->create();

        $this->post('/admin/jobs/listings', $this->jobInput($company, ['salary_min' => 1000, 'salary_max' => 500, 'apply_method' => 'external_url', 'apply_url' => '']))
            ->assertSessionHasErrors(['salary_max', 'apply_url']);
        $this->assertSame(0, JobListing::count());
    }

    public function test_jobs_past_their_closing_date_are_hidden_and_closed_by_the_scheduler(): void
    {
        $job = JobListing::factory()->create(['slug' => 'short-job', 'closes_at' => now()->addDay(), 'is_featured' => true, 'featured_until' => now()->addHours(2)]);
        $this->assertTrue($job->isFeaturedNow());
        $this->get('/jobs/short-job')->assertOk();

        $this->travel(2)->days();

        $this->get('/jobs/short-job')->assertNotFound();
        $this->assertTrue(app(ContentItems::class)->latest(CmsContentType::Job, 5)->isEmpty());

        $this->artisan('jobs:expire')->assertSuccessful();

        $job->refresh();
        $this->assertSame(JobStatus::Closed, $job->status);
        $this->assertFalse($job->is_featured);
        $this->assertDatabaseMissing('cms_contents', ['source_type' => 'job_listing', 'source_id' => $job->id]);
    }

    public function test_job_from_unverified_company_goes_to_the_approval_center(): void
    {
        $writer = $this->signInWriter();
        $company = Company::factory()->create();

        $this->post('/admin/jobs/listings', $this->jobInput($company, ['status' => 'published', 'intent' => 'submit']))->assertRedirect();

        $job = JobListing::firstOrFail();
        $this->assertSame(JobStatus::InReview, $job->status);
        $this->assertSame($writer->id, $job->submitted_by);
        $this->assertDatabaseMissing('cms_contents', ['source_type' => 'job_listing', 'source_id' => $job->id]);

        $review = ApprovalRequest::firstOrFail();
        app(ApprovalService::class)->approve($review, User::factory()->admin(AdminRole::JobsAdmin)->create());

        $this->assertTrue($job->fresh()->isLive());
        $this->assertDatabaseHas('cms_contents', ['source_type' => 'job_listing', 'source_id' => $job->id]);
    }

    public function test_verified_company_skips_review_unless_the_setting_is_off(): void
    {
        $this->signInWriter();
        $company = Company::factory()->verified()->create();
        $first = JobListing::factory()->draft()->for($company)->create();

        $this->post("/admin/jobs/listings/{$first->id}/submit")->assertSessionHas('status', 'Job published — the company is verified.');
        $this->assertTrue($first->fresh()->isLive());
        $this->assertSame(0, ApprovalRequest::count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'jobs.listing.auto_published', 'auditable_id' => $first->id]);

        $settings = app(PlatformSettings::class);
        $settings->update('jobs', array_merge($settings->group('jobs'), ['verified_skip_review' => false]));
        $second = JobListing::factory()->draft()->for($company)->create();

        $this->post("/admin/jobs/listings/{$second->id}/submit")->assertRedirect();
        $this->assertSame(JobStatus::InReview, $second->fresh()->status);
        $this->assertSame(1, ApprovalRequest::count());
    }

    public function test_rejected_job_shows_the_reason_and_can_be_resubmitted(): void
    {
        $this->signInWriter();
        $job = JobListing::factory()->draft()->create();

        $this->post("/admin/jobs/listings/{$job->id}/submit")->assertRedirect();
        app(ApprovalService::class)->reject(ApprovalRequest::firstOrFail(), User::factory()->admin(AdminRole::JobsAdmin)->create(), 'Add the salary range.');

        $this->assertSame(JobStatus::Rejected, $job->fresh()->status);
        $this->get("/admin/jobs/listings/{$job->id}/edit")->assertOk()->assertSee('Add the salary range.');

        $this->post("/admin/jobs/listings/{$job->id}/submit")->assertRedirect();
        $this->assertSame(JobStatus::InReview, $job->fresh()->status);
    }

    public function test_manager_publishing_from_the_editor_closes_the_open_review(): void
    {
        $this->signInAdmin(AdminRole::JobsAdmin);
        $job = JobListing::factory()->draft()->create(['slug' => 'pending-job']);
        $job->update(['status' => JobStatus::InReview]);
        $review = app(ApprovalService::class)->submit($job, User::factory()->create());

        $this->put("/admin/jobs/listings/{$job->id}", $this->jobInput($job->company, ['slug' => 'pending-job', 'status' => 'published']))->assertRedirect();

        $this->assertSame(ApprovalStatus::Approved, $review->fresh()->status);
        $this->assertTrue($job->fresh()->isLive());
    }

    public function test_featuring_expires_and_closing_stops_applications(): void
    {
        $this->signInAdmin(AdminRole::JobsAdmin);
        $job = JobListing::factory()->create(['title' => ['en' => 'Featured candidate']]);
        JobListing::factory()->draft()->create(['title' => ['en' => 'Unfinished draft']]);

        $this->get('/admin/jobs/listings?tab=draft')->assertOk()->assertSee('Unfinished draft')->assertDontSee('Featured candidate');

        $this->post("/admin/jobs/listings/{$job->id}/feature")->assertRedirect();
        $job->refresh();
        $this->assertTrue($job->isFeaturedNow());
        $this->assertSame(now()->addDays(14)->toDateString(), $job->featured_until->toDateString());
        $this->assertSame('Featured', CmsContent::where('source_id', $job->id)->value('badge'));
        $this->get('/admin/jobs/listings?tab=featured')->assertSee('Featured candidate');

        $this->post("/admin/jobs/listings/{$job->id}/close")->assertRedirect();
        $this->assertSame(JobStatus::Closed, $job->fresh()->status);
        $this->assertDatabaseMissing('cms_contents', ['source_type' => 'job_listing', 'source_id' => $job->id]);
    }

    public function test_jobs_admin_pages_need_jobs_permissions(): void
    {
        $job = JobListing::factory()->create();

        $this->signInAdmin(AdminRole::NewsAdmin);
        $this->get('/admin/jobs/listings')->assertForbidden();

        $this->signInWriter();
        $this->get('/admin/jobs/listings')->assertOk();
        $this->post("/admin/jobs/listings/{$job->id}/feature")->assertForbidden();
        $this->post("/admin/jobs/listings/{$job->id}/close")->assertForbidden();
        $this->delete("/admin/jobs/listings/{$job->id}")->assertForbidden();
    }

    public function test_writer_cannot_publish_by_posting_a_status(): void
    {
        $this->signInWriter();
        $company = Company::factory()->create();

        $this->post('/admin/jobs/listings', $this->jobInput($company, ['status' => 'published']))->assertRedirect();

        $this->assertSame(JobStatus::Draft, JobListing::firstOrFail()->status);
    }

    public function test_deleting_job_removes_it_everywhere(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $job = JobListing::factory()->create();

        $this->delete("/admin/jobs/listings/{$job->id}")->assertRedirect('/admin/jobs/listings');

        $this->assertDatabaseMissing('job_listings', ['id' => $job->id]);
        $this->assertDatabaseMissing('cms_contents', ['source_type' => 'job_listing', 'source_id' => $job->id]);
    }

    public function test_jobs_are_hidden_when_module_is_off(): void
    {
        JobListing::factory()->create(['slug' => 'any-job']);
        $settings = app(PlatformSettings::class);
        $settings->update('modules', array_merge($settings->group('modules'), [PlatformModule::Jobs->value => false]));

        $this->get('/jobs/any-job')->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function jobInput(Company $company, array $overrides = []): array
    {
        return array_merge([
            'company_id' => $company->id,
            'title' => ['en' => 'Senior Accountant'],
            'description' => ['en' => "## Role\n\nLead monthly closing."],
            'employment_type' => 'full_time',
            'location' => 'Phnom Penh',
            'salary_min' => 900,
            'salary_max' => 1300,
            'salary_currency' => 'USD',
            'salary_period' => 'month',
            'apply_method' => 'on_site',
        ], $overrides);
    }

    /**
     * An editor who can create and edit jobs but not publish them (no jobs.manage).
     */
    private function signInWriter(): User
    {
        $writer = $this->signInAdmin(AdminRole::SupportAgent);
        $writer->givePermissionTo([
            PermissionArea::Jobs->permission(PermissionAction::View),
            PermissionArea::Jobs->permission(PermissionAction::Create),
            PermissionArea::Jobs->permission(PermissionAction::Edit),
        ]);

        return $writer;
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
