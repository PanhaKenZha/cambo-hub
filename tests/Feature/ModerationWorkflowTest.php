<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\AppealStatus;
use App\Enums\ApprovalStatus;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\ModerationAppeal;
use App\Models\User;
use App\Models\UserViolation;
use App\Notifications\AppealDecided;
use App\Notifications\ViolationIssued;
use App\Services\ApprovalService;
use App\Services\ModerationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\Fixtures\TestArticle;
use Tests\TestCase;

class ModerationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    private ModerationService $moderation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        TestArticle::createTable();
        Notification::fake();
        $this->author = User::factory()->create();
        $this->moderation = app(ModerationService::class);
    }

    public function test_reporter_cannot_file_duplicate_open_reports(): void
    {
        $article = $this->article();
        $reporter = User::factory()->create();

        $first = $this->moderation->report($article, $reporter, ReportReason::Spam, 'Selling fake goods');
        $second = $this->moderation->report($article, $reporter, ReportReason::Scam);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('content_reports', 1);
    }

    public function test_resolving_takes_content_down_resolves_duplicates_and_suspends_owner(): void
    {
        $moderator = $this->signInAdmin(AdminRole::NewsAdmin);
        $article = $this->article();
        $report = $this->moderation->report($article, User::factory()->create(), ReportReason::Scam);
        $duplicate = $this->moderation->report($article, User::factory()->create(), ReportReason::Spam);

        $this->post("/admin/reports/{$report->id}/resolve", [
            'note' => 'Confirmed fake investment scheme',
            'take_down' => '1',
            'violation_type' => ViolationType::Suspension->value,
            'suspension_days' => '7',
        ])->assertRedirect('/admin/reports');

        $this->assertSame('removed', $article->fresh()->status);
        $this->assertSame(ReportStatus::Resolved, $report->fresh()->status);
        $this->assertSame(ReportStatus::Resolved, $duplicate->fresh()->status);
        $this->assertTrue($report->fresh()->content_taken_down);

        $violation = UserViolation::firstOrFail();
        $this->assertSame(ViolationType::Suspension, $violation->type);
        $this->assertSame($moderator->id, $violation->issued_by);
        $this->assertSame($report->id, $violation->content_report_id);
        $this->assertTrue($violation->expires_at->between(now()->addDays(6), now()->addDays(8)));
        $this->assertTrue($this->author->fresh()->isSuspended());
        Notification::assertSentTo($this->author, ViolationIssued::class);

        $this->post("/admin/reports/{$report->id}/dismiss", ['note' => 'Too late'])->assertForbidden();
    }

    public function test_dismissing_needs_a_note_and_keeps_content(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);
        $article = $this->article();
        $report = $this->moderation->report($article, User::factory()->create(), ReportReason::Other);

        $this->post("/admin/reports/{$report->id}/dismiss", ['note' => ''])->assertSessionHasErrors('note', null, 'dismissal');
        $this->post("/admin/reports/{$report->id}/dismiss", ['note' => 'Satire, allowed'])->assertRedirect('/admin/reports');

        $this->assertSame(ReportStatus::Dismissed, $report->fresh()->status);
        $this->assertSame('draft', $article->fresh()->status);
    }

    public function test_violations_cannot_target_administrators(): void
    {
        $moderator = User::factory()->admin(AdminRole::SocialModerator)->create();
        $admin = User::factory()->admin(AdminRole::JobsAdmin)->create();

        $this->expectException(ValidationException::class);
        $this->moderation->issueViolation($admin, $moderator, ViolationType::Ban, 'Testing');
    }

    public function test_ban_issued_from_admin_panel_can_be_overturned(): void
    {
        $this->signInAdmin(AdminRole::SocialModerator);

        $this->get('/admin/violations/create')->assertOk()->assertSee('Issue a violation');
        $this->post('/admin/violations', ['email' => $this->author->email, 'type' => 'ban', 'reason' => 'Repeated harassment'])
            ->assertRedirect('/admin/violations');
        $this->assertTrue($this->author->fresh()->isSuspended());

        $violation = UserViolation::firstOrFail();
        $this->get('/admin/violations')->assertOk()->assertSee('Repeated harassment');
        $this->post("/admin/violations/{$violation->id}/overturn")->assertRedirect();

        $this->assertSame(ViolationStatus::Overturned, $violation->fresh()->status);
        $this->assertFalse($this->author->fresh()->isSuspended());
        $this->assertDatabaseHas('audit_logs', ['action' => 'violation.overturned']);
    }

    public function test_expired_suspensions_restore_access_unless_another_block_remains(): void
    {
        $moderator = User::factory()->admin(AdminRole::SocialModerator)->create();
        $other = User::factory()->create();
        $this->moderation->issueViolation($this->author, $moderator, ViolationType::Suspension, 'Spam', 3);
        $this->moderation->issueViolation($other, $moderator, ViolationType::Suspension, 'Spam', 3);
        $this->moderation->issueViolation($other, $moderator, ViolationType::Ban, 'Fraud');

        $this->travel(4)->days();
        $this->artisan('moderation:expire-suspensions')->expectsOutput('2 suspension(s) expired.')->assertSuccessful();

        $this->assertFalse($this->author->fresh()->isSuspended());
        $this->assertTrue($other->fresh()->isSuspended());
    }

    public function test_violation_appeal_must_be_decided_by_a_different_moderator(): void
    {
        $issuer = User::factory()->withTwoFactor()->admin(AdminRole::SocialModerator)->create();
        $violation = $this->moderation->issueViolation($this->author, $issuer, ViolationType::Suspension, 'Spam links', 30);
        $appeal = $this->moderation->fileAppeal($violation, $this->author, 'Those links were to my own shop.');

        $this->actingAs($issuer)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);
        $this->get("/admin/appeals/{$appeal->id}")->assertOk()->assertSee('another moderator must decide');
        $this->post("/admin/appeals/{$appeal->id}/decide", ['decision' => 'overturn', 'note' => 'ok'])->assertForbidden();

        $this->signInAdmin(AdminRole::SocialModerator);
        $this->get('/admin/appeals')->assertOk()->assertSee('Spam links');
        $this->post("/admin/appeals/{$appeal->id}/decide", ['decision' => 'overturn', 'note' => 'Links were legitimate.'])
            ->assertRedirect('/admin/appeals');

        $this->assertSame(AppealStatus::Overturned, $appeal->fresh()->status);
        $this->assertSame(ViolationStatus::Overturned, $violation->fresh()->status);
        $this->assertFalse($this->author->fresh()->isSuspended());
        Notification::assertSentTo($this->author, AppealDecided::class);
    }

    public function test_rejected_submission_can_be_approved_on_appeal(): void
    {
        $reviewer = User::factory()->admin(AdminRole::NewsAdmin)->create();
        $article = $this->article();
        $request = app(ApprovalService::class)->submit($article, $this->author);
        app(ApprovalService::class)->reject($request, $reviewer, 'Off topic');
        $appeal = $this->moderation->fileAppeal($request->fresh(), $this->author, 'It is about local news.');

        $this->signInAdmin(AdminRole::SuperAdmin);
        $this->post("/admin/appeals/{$appeal->id}/decide", ['decision' => 'overturn', 'note' => 'Agreed, relevant.']);

        $this->assertSame(ApprovalStatus::Approved, $request->fresh()->status);
        $this->assertSame('published', $article->fresh()->status);
        $this->assertSame(['submitted', 'rejected', 'appeal_overturned'], $request->events()->pluck('action')->all());
    }

    public function test_upheld_appeal_keeps_the_original_decision(): void
    {
        $issuer = User::factory()->admin(AdminRole::SocialModerator)->create();
        $violation = $this->moderation->issueViolation($this->author, $issuer, ViolationType::Strike, 'Abusive comment');
        $appeal = $this->moderation->fileAppeal($violation, $this->author, 'I was joking.');

        $this->signInAdmin(AdminRole::SuperAdmin);
        $this->post("/admin/appeals/{$appeal->id}/decide", ['decision' => 'uphold', 'note' => 'Comment broke the rules.']);

        $this->assertSame(AppealStatus::Upheld, $appeal->fresh()->status);
        $this->assertSame(ViolationStatus::Active, $violation->fresh()->status);
    }

    public function test_only_the_affected_user_can_appeal_once_at_a_time(): void
    {
        $issuer = User::factory()->admin(AdminRole::SocialModerator)->create();
        $violation = $this->moderation->issueViolation($this->author, $issuer, ViolationType::Warning, 'Off-topic posts');

        $this->assertThrows(fn () => $this->moderation->fileAppeal($violation, User::factory()->create(), 'Not me'), ValidationException::class);

        $this->moderation->fileAppeal($violation, $this->author, 'Please review');
        $this->assertThrows(fn () => $this->moderation->fileAppeal($violation, $this->author, 'Again'), ValidationException::class);
        $this->assertSame(1, ModerationAppeal::count());
    }

    public function test_moderation_pages_render_and_require_permission(): void
    {
        $report = $this->moderation->report($this->article(), User::factory()->create(), ReportReason::HateSpeech, 'Offensive headline');
        $this->signInAdmin(AdminRole::SuperAdmin);

        $this->get('/admin/reports')->assertOk()->assertSee('Breaking news')->assertSee('Hate speech');
        $this->get("/admin/reports/{$report->id}")->assertOk()->assertSee('Offensive headline')->assertSee('Resolve report');
        $this->get('/admin/appeals')->assertOk();
        $this->get('/admin')->assertOk()->assertSee('Open reports');

        $this->signInAdmin(AdminRole::FinanceAdmin);
        $this->get('/admin/reports')->assertForbidden();
        $this->get('/admin/violations')->assertForbidden();
    }

    private function article(): TestArticle
    {
        return TestArticle::create(['user_id' => $this->author->id, 'title' => 'Breaking news']);
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
