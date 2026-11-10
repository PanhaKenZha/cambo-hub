<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ApplicationStatus;
use App\Enums\ApplyMethod;
use App\Enums\CmsContentType;
use App\Enums\VerificationStatus;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\CmsContent;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobCategory;
use App\Models\JobListing;
use App\Models\User;
use Database\Seeders\ContentItemSeeder;
use Database\Seeders\JobsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobCompanyAndApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();
    }

    public function test_company_with_document_waits_for_verification_and_can_be_verified(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->signInAdmin(AdminRole::JobsAdmin);
        $owner = User::factory()->create(['email' => 'hr@angkor.test']);

        $this->post('/admin/jobs/companies', [
            'name' => 'Angkor Digital',
            'owner_email' => 'hr@angkor.test',
            'is_active' => '1',
            'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
            'verification_document' => UploadedFile::fake()->create('registration.pdf', 200, 'application/pdf'),
        ])->assertRedirect();

        $company = Company::where('name', 'Angkor Digital')->firstOrFail();
        $this->assertSame(VerificationStatus::Pending, $company->verification_status);
        $this->assertSame($owner->id, $company->owner_id);
        Storage::disk('local')->assertExists($company->verification_document_path);
        Storage::disk('public')->assertExists($company->logo_path);

        $this->get('/admin/jobs/companies')->assertOk()->assertSee('Angkor Digital')->assertSee('Review');
        $this->get("/admin/jobs/companies/{$company->id}/document")->assertOk()->assertDownload('angkor-digital-verification.pdf');

        $this->post("/admin/jobs/companies/{$company->id}/verify", ['decision' => 'reject'])->assertSessionHasErrors('note', null, 'verification');
        $this->post("/admin/jobs/companies/{$company->id}/verify", ['decision' => 'verify'])->assertRedirect();

        $company->refresh();
        $this->assertTrue($company->isVerified());
        $this->assertNotNull($company->verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'jobs.company.verification', 'auditable_id' => $company->id]);
    }

    public function test_verification_changes_refresh_homepage_job_cards(): void
    {
        $this->signInAdmin(AdminRole::JobsAdmin);
        $job = JobListing::factory()->create();
        $this->assertArrayNotHasKey('verified', CmsContent::where('source_id', $job->id)->value('meta'));

        $this->post("/admin/jobs/companies/{$job->company_id}/verify", ['decision' => 'verify'])->assertRedirect();

        $this->assertTrue(CmsContent::where('source_id', $job->id)->value('meta')['verified']);
    }

    public function test_company_with_jobs_cannot_be_deleted_and_verification_needs_manage(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $job = JobListing::factory()->create();

        $this->delete("/admin/jobs/companies/{$job->company_id}")->assertSessionHasErrors('company');
        $this->assertDatabaseHas('companies', ['id' => $job->company_id]);

        $support = $this->signInAdmin(AdminRole::SupportAgent);
        $support->givePermissionTo(['jobs.view', 'jobs.edit']);
        $this->post("/admin/jobs/companies/{$job->company_id}/verify", ['decision' => 'verify'])->assertForbidden();
    }

    public function test_candidate_applies_with_a_cv_once(): void
    {
        Storage::fake('local');
        $job = JobListing::factory()->create(['slug' => 'store-supervisor']);
        $candidate = User::factory()->create();

        $this->get('/jobs/store-supervisor')->assertOk()->assertSee('Sign in to apply');

        $this->actingAs($candidate)->get('/jobs/store-supervisor')->assertOk()->assertSee('Send application');
        $this->post('/jobs/store-supervisor/apply', [
            'name' => 'Sokha Chan',
            'email' => 'sokha@example.com',
            'cover_letter' => 'I have managed a store for three years.',
            'cv' => UploadedFile::fake()->create('sokha-cv.pdf', 300, 'application/pdf'),
        ])->assertRedirect()->assertSessionHas('status');

        $application = JobApplication::firstOrFail();
        $this->assertSame($job->id, $application->job_listing_id);
        $this->assertSame(ApplicationStatus::New, $application->status);
        $this->assertSame('sokha-cv.pdf', $application->cv_original_name);
        Storage::disk('local')->assertExists($application->cv_path);

        $this->get('/jobs/store-supervisor')->assertSee('You have applied for this job.');
        $this->post('/jobs/store-supervisor/apply', [
            'name' => 'Sokha Chan', 'email' => 'sokha@example.com', 'cv' => UploadedFile::fake()->create('again.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('cv');
        $this->assertSame(1, JobApplication::count());
    }

    public function test_cv_must_be_a_document_and_external_jobs_do_not_take_applications(): void
    {
        Storage::fake('local');
        JobListing::factory()->create(['slug' => 'onsite-job']);
        JobListing::factory()->create(['slug' => 'external-job', 'apply_method' => ApplyMethod::ExternalUrl, 'apply_url' => 'https://example.com/careers']);
        $this->actingAs(User::factory()->create());

        $this->post('/jobs/onsite-job/apply', ['name' => 'A', 'email' => 'a@example.com', 'cv' => UploadedFile::fake()->image('photo.jpg')])
            ->assertSessionHasErrors('cv');

        $this->get('/jobs/external-job')->assertOk()->assertSee('https://example.com/careers', false);
        $this->post('/jobs/external-job/apply', ['name' => 'A', 'email' => 'a@example.com', 'cv' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')])
            ->assertNotFound();
        $this->assertSame(0, JobApplication::count());
    }

    public function test_admin_reviews_application_and_downloads_cv(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('job-applications/cv.pdf', 'PDF');
        $application = JobApplication::factory()->create(['cv_path' => 'job-applications/cv.pdf', 'cv_original_name' => 'dara-cv.pdf', 'name' => 'Dara Kim']);
        $admin = $this->signInAdmin(AdminRole::JobsAdmin);

        $this->get('/admin/jobs/applications')->assertOk()->assertSee('Dara Kim');
        $this->get("/admin/jobs/applications/{$application->id}")->assertOk()->assertSee('Download CV');
        $this->get("/admin/jobs/applications/{$application->id}/cv")->assertOk()->assertDownload('dara-cv.pdf');
        $this->assertDatabaseHas('audit_logs', ['action' => 'jobs.application.cv_downloaded', 'auditable_id' => $application->id]);

        $this->put("/admin/jobs/applications/{$application->id}", ['status' => 'shortlisted', 'notes' => 'Call on Monday.'])->assertRedirect();

        $application->refresh();
        $this->assertSame(ApplicationStatus::Shortlisted, $application->status);
        $this->assertSame($admin->id, $application->reviewed_by);
    }

    public function test_cvs_are_not_available_to_other_admins(): void
    {
        $application = JobApplication::factory()->create();

        $this->signInAdmin(AdminRole::NewsAdmin);
        $this->get("/admin/jobs/applications/{$application->id}")->assertForbidden();
        $this->get("/admin/jobs/applications/{$application->id}/cv")->assertForbidden();
    }

    public function test_categories_can_be_managed(): void
    {
        $this->signInAdmin(AdminRole::JobsAdmin);

        $this->post('/admin/jobs/categories', ['name' => ['en' => 'IT & Software', 'km' => 'ព័ត៌មានវិទ្យា'], 'is_active' => '1'])
            ->assertRedirect('/admin/jobs/categories');

        $category = JobCategory::where('slug', 'it-software')->firstOrFail();
        $job = JobListing::factory()->for($category, 'category')->create();
        $this->put("/admin/jobs/categories/{$category->id}", ['name' => ['en' => 'Technology'], 'slug' => 'it-software', 'is_active' => '1'])->assertRedirect();

        $this->assertSame('Technology', CmsContent::where('source_id', $job->id)->value('badge'));
        $this->get('/admin/jobs/categories')->assertOk()->assertSee('Technology');
    }

    public function test_seeder_replaces_demo_jobs_with_real_listings(): void
    {
        $this->seed(ContentItemSeeder::class);
        $this->seed(JobsSeeder::class);
        $this->seed(JobsSeeder::class);

        $this->assertSame(6, JobListing::count());
        $this->assertSame(6, Company::count());
        $this->assertSame(6, CmsContent::where('content_type', CmsContentType::Job)->count());
        $this->assertSame(0, CmsContent::where('content_type', CmsContentType::Job)->whereNull('source_type')->count());
        $this->assertSame(6, JobListing::live()->count());
        $this->get('/jobs/english-teacher-bright-future-school')->assertOk()->assertSee('$12 / hour');
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
