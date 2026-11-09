<?php

namespace App\Services;

use App\Contracts\Approvable;
use App\Enums\ApprovalStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Notifications\ApprovalAssigned;
use App\Notifications\ApprovalDecided;
use App\Notifications\SubmissionAwaitingReview;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * The central approval workflow: submit → (assign) → approve or reject.
 *
 * Content modules call submit(); moderators act through the Approvals screens.
 */
class ApprovalService
{
    public function __construct(private AuditLogger $auditLogger, private PlatformSettings $settings) {}

    /**
     * Put content in the approval queue. If it is already waiting, the existing request is returned.
     */
    public function submit(Model&Approvable $content, ?User $submitter, ?string $summary = null): ApprovalRequest
    {
        $existing = ApprovalRequest::query()
            ->whereMorphedTo('approvable', $content)
            ->pending()
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $wasRejectedBefore = ApprovalRequest::query()
            ->whereMorphedTo('approvable', $content)
            ->where('status', ApprovalStatus::Rejected)
            ->exists();

        $approvalRequest = DB::transaction(function () use ($content, $submitter, $summary, $wasRejectedBefore): ApprovalRequest {
            $approvalRequest = ApprovalRequest::create([
                'approvable_type' => $content->getMorphClass(),
                'approvable_id' => $content->getKey(),
                'module' => $content->approvalModule(),
                'title' => $content->approvalTitle(),
                'summary' => $summary,
                'status' => ApprovalStatus::Pending,
                'submitted_by' => $submitter?->id,
            ]);
            $this->recordEvent($approvalRequest, $wasRejectedBefore ? 'resubmitted' : 'submitted', $submitter);

            return $approvalRequest;
        });

        if ($this->settings->get('notifications.submission_alerts')) {
            Notification::send(
                $this->reviewersFor($approvalRequest)->reject(fn (User $reviewer): bool => $reviewer->is($submitter)),
                new SubmissionAwaitingReview($approvalRequest),
            );
        }

        return $approvalRequest;
    }

    public function assign(ApprovalRequest $approvalRequest, User $assignee, User $actor): void
    {
        $this->ensurePending($approvalRequest);

        if (! $assignee->can('review', $approvalRequest)) {
            throw ValidationException::withMessages(['assignee' => "{$assignee->name} is not allowed to review {$approvalRequest->module->label()} submissions."]);
        }

        $previousAssignee = $approvalRequest->assignee?->name;

        DB::transaction(function () use ($approvalRequest, $assignee, $actor, $previousAssignee): void {
            $approvalRequest->update(['assigned_to' => $assignee->id]);
            $this->recordEvent($approvalRequest, 'assigned', $actor, "Assigned to {$assignee->name}");
            $this->auditLogger->log('approval.assigned', $approvalRequest, ['assigned_to' => $previousAssignee], ['assigned_to' => $assignee->name], $actor);
        });

        if ($assignee->isNot($actor)) {
            $assignee->notify(new ApprovalAssigned($approvalRequest));
        }
    }

    public function approve(ApprovalRequest $approvalRequest, User $actor, ?string $note = null): void
    {
        $this->decide($approvalRequest, $actor, ApprovalStatus::Approved, null, 'approved', $note);
    }

    public function reject(ApprovalRequest $approvalRequest, User $actor, string $reason): void
    {
        $this->decide($approvalRequest, $actor, ApprovalStatus::Rejected, $reason, 'rejected', $reason);
    }

    /**
     * Reverse a rejection after a successful appeal.
     */
    public function approveOnAppeal(ApprovalRequest $approvalRequest, User $actor, ?string $note): void
    {
        if ($approvalRequest->status !== ApprovalStatus::Rejected) {
            throw new LogicException('Only rejected submissions can be approved on appeal.');
        }

        DB::transaction(function () use ($approvalRequest, $actor, $note): void {
            $approvalRequest->update(['status' => ApprovalStatus::Approved, 'reviewed_by' => $actor->id, 'reviewed_at' => now()]);
            $this->recordEvent($approvalRequest, 'appeal_overturned', $actor, $note);
            $this->contentFor($approvalRequest)?->markApproved();
        });

        $approvalRequest->submitter?->notify(new ApprovalDecided($approvalRequest));
    }

    /**
     * Administrators allowed to review this request.
     *
     * @return Collection<int, User>
     */
    public function reviewersFor(ApprovalRequest $approvalRequest): Collection
    {
        return User::permission(PermissionArea::Approvals->permission(PermissionAction::Manage))
            ->whereNull('suspended_at')
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user): bool => $user->can('review', $approvalRequest))
            ->values();
    }

    private function decide(ApprovalRequest $approvalRequest, User $actor, ApprovalStatus $status, ?string $reason, string $action, ?string $note): void
    {
        $this->ensurePending($approvalRequest);

        DB::transaction(function () use ($approvalRequest, $actor, $status, $reason, $action, $note): void {
            $approvalRequest->update([
                'status' => $status,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);
            $this->recordEvent($approvalRequest, $action, $actor, $note);

            $content = $this->contentFor($approvalRequest);
            $status === ApprovalStatus::Approved ? $content?->markApproved() : $content?->markRejected((string) $reason);

            $this->auditLogger->log("approval.{$action}", $approvalRequest, ['status' => ApprovalStatus::Pending->value], array_filter([
                'status' => $status->value,
                'title' => $approvalRequest->title,
                'reason' => $reason,
                'note' => $status === ApprovalStatus::Approved ? $note : null,
            ]), $actor);
        });

        $approvalRequest->submitter?->notify(new ApprovalDecided($approvalRequest));
    }

    private function contentFor(ApprovalRequest $approvalRequest): ?Approvable
    {
        $content = $approvalRequest->approvable;

        return $content instanceof Approvable ? $content : null;
    }

    private function ensurePending(ApprovalRequest $approvalRequest): void
    {
        if (! $approvalRequest->isPending()) {
            throw new LogicException("Approval request #{$approvalRequest->id} has already been decided.");
        }
    }

    private function recordEvent(ApprovalRequest $approvalRequest, string $action, ?User $actor, ?string $note = null): void
    {
        $approvalRequest->events()->create(['actor_id' => $actor?->id, 'action' => $action, 'note' => $note]);
    }
}
