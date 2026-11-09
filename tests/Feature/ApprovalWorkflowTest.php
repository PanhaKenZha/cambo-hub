<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ApprovalStatus;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Notifications\ApprovalAssigned;
use App\Notifications\ApprovalDecided;
use App\Notifications\SubmissionAwaitingReview;
use App\Services\ApprovalService;
use App\Services\PlatformSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Fixtures\TestArticle;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        TestArticle::createTable();
        Notification::fake();
        $this->author = User::factory()->create();
    }

    public function test_submission_enters_queue_once_and_alerts_eligible_reviewers_only(): void
    {
        $newsAdmin = User::factory()->admin(AdminRole::NewsAdmin)->create();
        $jobsAdmin = User::factory()->admin(AdminRole::JobsAdmin)->create();
        $article = $this->article();

        $request = app(ApprovalService::class)->submit($article, $this->author, 'Election coverage');
        $again = app(ApprovalService::class)->submit($article, $this->author);

        $this->assertTrue($request->is($again));
        $this->assertSame(ApprovalStatus::Pending, $request->status);
        $this->assertSame('submitted', $request->events()->value('action'));
        Notification::assertSentTo($newsAdmin, SubmissionAwaitingReview::class);
        Notification::assertNotSentTo($jobsAdmin, SubmissionAwaitingReview::class);
        Notification::assertNotSentTo($this->author, SubmissionAwaitingReview::class);
    }

    public function test_submission_alerts_can_be_turned_off(): void
    {
        $newsAdmin = User::factory()->admin(AdminRole::NewsAdmin)->create();
        $settings = app(PlatformSettings::class);
        $settings->update('notifications', array_merge($settings->group('notifications'), ['submission_alerts' => false]));

        app(ApprovalService::class)->submit($this->article(), $this->author);

        Notification::assertNotSentTo($newsAdmin, SubmissionAwaitingReview::class);
    }

    public function test_reviewer_approves_and_content_is_published(): void
    {
        $reviewer = $this->signInAdmin(AdminRole::NewsAdmin);
        $article = $this->article();
        $request = app(ApprovalService::class)->submit($article, $this->author);

        $this->post("/admin/approvals/{$request->id}/approve", ['note' => 'Looks good'])->assertRedirect('/admin/approvals');

        $request->refresh();
        $this->assertSame(ApprovalStatus::Approved, $request->status);
        $this->assertSame($reviewer->id, $request->reviewed_by);
        $this->assertSame('published', $article->fresh()->status);
        $this->assertSame(['submitted', 'approved'], $request->events()->pluck('action')->all());
        $this->assertDatabaseHas('audit_logs', ['action' => 'approval.approved', 'user_id' => $reviewer->id]);
        Notification::assertSentTo($this->author, ApprovalDecided::class);

        $this->post("/admin/approvals/{$request->id}/approve")->assertForbidden();
    }

    public function test_rejection_needs_a_reason_and_resubmission_is_tracked(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);
        $article = $this->article();
        $request = app(ApprovalService::class)->submit($article, $this->author);

        $this->post("/admin/approvals/{$request->id}/reject", ['reason' => ''])->assertSessionHasErrors('reason', null, 'rejection');
        $this->post("/admin/approvals/{$request->id}/reject", ['reason' => 'Missing sources for the claims.'])->assertRedirect('/admin/approvals');

        $this->assertSame(ApprovalStatus::Rejected, $request->fresh()->status);
        $this->assertSame('Missing sources for the claims.', $request->fresh()->rejection_reason);
        $this->assertSame('rejected', $article->fresh()->status);
        Notification::assertSentTo($this->author, ApprovalDecided::class);

        $resubmission = app(ApprovalService::class)->submit($article, $this->author);
        $this->assertFalse($resubmission->is($request));
        $this->assertSame('resubmitted', $resubmission->events()->value('action'));
    }

    public function test_reviewers_only_see_and_decide_modules_they_manage(): void
    {
        $request = app(ApprovalService::class)->submit($this->article(), $this->author);
        $this->signInAdmin(AdminRole::JobsAdmin);

        $this->get('/admin/approvals')->assertOk()->assertDontSee('Breaking news');
        $this->get("/admin/approvals/{$request->id}")->assertForbidden();
        $this->post("/admin/approvals/{$request->id}/approve")->assertForbidden();

        $this->signInAdmin(AdminRole::SupportAgent);
        $this->get('/admin/approvals')->assertForbidden();
    }

    public function test_assignment_is_limited_to_eligible_reviewers_and_notifies_them(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $newsAdmin = User::factory()->admin(AdminRole::NewsAdmin)->create();
        $jobsAdmin = User::factory()->admin(AdminRole::JobsAdmin)->create();
        $request = app(ApprovalService::class)->submit($this->article(), $this->author);

        $this->post("/admin/approvals/{$request->id}/assign", ['assignee' => $jobsAdmin->id])->assertSessionHasErrors('assignee');
        $this->post("/admin/approvals/{$request->id}/assign", ['assignee' => $newsAdmin->id])->assertSessionHasNoErrors();

        $this->assertSame($newsAdmin->id, $request->fresh()->assigned_to);
        Notification::assertSentTo($newsAdmin, ApprovalAssigned::class);
        $this->get('/admin/approvals?status=pending&assigned=unassigned')->assertDontSee('Breaking news');
    }

    public function test_queue_and_review_pages_render(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $request = app(ApprovalService::class)->submit($this->article(), $this->author, 'Summary text');
        ApprovalRequest::factory()->rejected()->create(['title' => 'Old rejected item']);

        $this->get('/admin/approvals')->assertOk()->assertSee('Breaking news')->assertDontSee('Old rejected item');
        $this->get('/admin/approvals?status=rejected')->assertOk()->assertSee('Old rejected item');
        $this->get("/admin/approvals/{$request->id}")->assertOk()->assertSee('Summary text')->assertSee('Submitted for review')->assertSee('Reject');
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
