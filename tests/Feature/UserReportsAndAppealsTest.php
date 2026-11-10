<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\AppealStatus;
use App\Enums\JobStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\ViolationType;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\ApprovalRequest;
use App\Models\ContentReport;
use App\Models\JobListing;
use App\Models\ModerationAppeal;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\ModerationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserReportsAndAppealsTest extends TestCase
{
    use RefreshDatabase;

    private const string APPEAL_MESSAGE = 'This job is real. We are a registered company and the salary is correct.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();
    }

    public function test_signed_in_visitor_reports_a_job_once_and_it_reaches_the_reports_page(): void
    {
        $job = JobListing::factory()->create(['slug' => 'too-good-to-be-true']);
        $reporter = User::factory()->create();

        $this->get('/jobs/too-good-to-be-true')->assertOk()->assertSee('Report a problem')->assertSee('to report this.');

        $this->actingAs($reporter)->from('/jobs/too-good-to-be-true')
            ->post("/report/job/{$job->id}", ['reason' => ReportReason::Scam->value, 'details' => 'Asks for a deposit.'])
            ->assertRedirect('/jobs/too-good-to-be-true')
            ->assertSessionHas('report_status');
        $this->post("/report/job/{$job->id}", ['reason' => ReportReason::Scam->value]);

        $report = ContentReport::sole();
        $this->assertSame(ReportStatus::Open, $report->status);
        $this->assertSame('Asks for a deposit.', $report->details);
        $this->assertTrue($report->reportable->is($job));

        $this->signInAdmin(AdminRole::JobsAdmin);
        $this->get('/admin/reports')->assertOk()->assertSee($job->translate('title', 'en'));
    }

    public function test_reports_need_sign_in_a_reason_and_live_content(): void
    {
        $article = NewsArticle::factory()->create();
        $draft = NewsArticle::factory()->draft()->create();

        $this->post("/report/news/{$article->id}", ['reason' => 'spam'])->assertRedirect('/login');

        $this->actingAs(User::factory()->create());
        $this->post("/report/news/{$article->id}", ['reason' => 'other'])->assertSessionHasErrors('details', null, 'report');
        $this->post("/report/news/{$draft->id}", ['reason' => 'spam'])->assertNotFound();
        $this->post("/report/pages/{$article->id}", ['reason' => 'spam'])->assertNotFound();
        $this->assertSame(0, ContentReport::count());
    }

    public function test_suspended_user_sees_the_reason_and_appeals_after_entering_their_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $moderator = User::factory()->admin(AdminRole::PlatformAdmin)->create();
        $violation = app(ModerationService::class)->issueViolation($user, $moderator, ViolationType::Suspension, 'Posted fake jobs.', 30);

        $this->get('/account/suspended')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
        $this->get('/account/suspended')->assertRedirect('/login');

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/account/suspended');
        $this->assertGuest();
        $this->get('/account/suspended')->assertOk()->assertSee('Your account is suspended')->assertSee('Posted fake jobs.');

        $this->post('/account/suspended/appeal', ['subject' => 'violation', 'id' => $violation->id, 'message' => 'Too short'])
            ->assertSessionHasErrors('message', null, 'appeal');
        $this->post('/account/suspended/appeal', ['subject' => 'violation', 'id' => $violation->id, 'message' => self::APPEAL_MESSAGE])
            ->assertSessionHas('status');

        $appeal = ModerationAppeal::sole();
        $this->assertTrue($appeal->appealable->is($violation));
        $this->get('/account/suspended')->assertSee('Your appeal is being reviewed.');

        $this->travel(31)->minutes();
        $this->get('/account/suspended')->assertRedirect('/login');
    }

    public function test_overturned_suspension_lets_the_user_sign_in_again(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $violation = app(ModerationService::class)->issueViolation($user, User::factory()->admin(AdminRole::PlatformAdmin)->create(), ViolationType::Suspension, 'Spam.', 7);
        $appeal = app(ModerationService::class)->fileAppeal($violation, $user, self::APPEAL_MESSAGE);

        app(ModerationService::class)->decideAppeal($appeal, User::factory()->admin(AdminRole::SuperAdmin)->create(), overturn: true, note: 'Reported by mistake.');

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->get('/account/standing')->assertOk()->assertSee('Your account is in good standing.')->assertSee('Reported by mistake.');
    }

    public function test_users_only_appeal_their_own_decisions(): void
    {
        $owner = User::factory()->create();
        $violation = app(ModerationService::class)->issueViolation($owner, User::factory()->admin(AdminRole::PlatformAdmin)->create(), ViolationType::Warning, 'Rude comment.');

        $this->actingAs($owner)->get('/account/standing')->assertOk()->assertSee('Rude comment.')->assertSee('Appeal this decision');

        $this->actingAs(User::factory()->create())
            ->post('/appeals', ['subject' => 'violation', 'id' => $violation->id, 'message' => self::APPEAL_MESSAGE])
            ->assertNotFound();
        $this->assertSame(0, ModerationAppeal::count());
    }

    public function test_writer_appeals_a_rejected_job_from_the_editor(): void
    {
        $writer = $this->signInAdmin(AdminRole::SupportAgent);
        $writer->givePermissionTo([PermissionArea::Jobs->permission(PermissionAction::View), PermissionArea::Jobs->permission(PermissionAction::Edit)]);
        $job = JobListing::factory()->draft()->create();
        $this->post("/admin/jobs/listings/{$job->id}/submit");
        $review = ApprovalRequest::sole();
        app(ApprovalService::class)->reject($review, User::factory()->admin(AdminRole::JobsAdmin)->create(), 'Looks like a scam.');

        $this->get("/admin/jobs/listings/{$job->id}/edit")->assertOk()->assertSee('Disagree? Appeal this decision');
        $this->from("/admin/jobs/listings/{$job->id}/edit")
            ->post('/appeals', ['subject' => 'submission', 'id' => $review->id, 'message' => self::APPEAL_MESSAGE])
            ->assertRedirect("/admin/jobs/listings/{$job->id}/edit");
        $this->get("/admin/jobs/listings/{$job->id}/edit")->assertSee('Your appeal is being reviewed by another moderator.');

        app(ModerationService::class)->decideAppeal(ModerationAppeal::sole(), User::factory()->admin(AdminRole::SuperAdmin)->create(), overturn: true, note: 'Company checked.');

        $this->assertSame(JobStatus::Published, $job->fresh()->status);
    }

    public function test_appeals_list_only_shows_modules_the_moderator_handles(): void
    {
        $jobReview = $this->rejectedSubmission(JobListing::factory()->draft()->create(['title' => ['en' => 'Appealed job']]));
        $newsReview = $this->rejectedSubmission(NewsArticle::factory()->draft()->create(['title' => ['en' => 'Appealed article']]));
        $user = User::factory()->create();
        foreach ([$jobReview, $newsReview] as $review) {
            ModerationAppeal::create(['appealable_type' => $review->getMorphClass(), 'appealable_id' => $review->id, 'user_id' => $user->id, 'message' => self::APPEAL_MESSAGE, 'status' => AppealStatus::Pending]);
        }

        $this->signInAdmin(AdminRole::JobsAdmin);
        $this->get('/admin/appeals')->assertOk()->assertSee('Appealed job')->assertDontSee('Appealed article');
    }

    private function rejectedSubmission(JobListing|NewsArticle $content): ApprovalRequest
    {
        $review = app(ApprovalService::class)->submit($content, User::factory()->create());
        app(ApprovalService::class)->reject($review, User::factory()->admin(AdminRole::SuperAdmin)->create(), 'Not suitable.');

        return $review->fresh();
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
