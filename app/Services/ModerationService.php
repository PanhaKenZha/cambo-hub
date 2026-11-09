<?php

namespace App\Services;

use App\Contracts\Reportable;
use App\Enums\AppealStatus;
use App\Enums\ApprovalStatus;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Models\ApprovalRequest;
use App\Models\ContentReport;
use App\Models\ModerationAppeal;
use App\Models\User;
use App\Models\UserViolation;
use App\Notifications\AppealDecided;
use App\Notifications\ViolationIssued;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * Content reports, user violations and appeals.
 */
class ModerationService
{
    public function __construct(private AuditLogger $auditLogger, private ApprovalService $approvalService) {}

    /**
     * File a report. A reporter can only have one open report per item.
     */
    public function report(Model&Reportable $content, ?User $reporter, ReportReason $reason, ?string $details = null): ContentReport
    {
        if ($reporter !== null) {
            $existing = ContentReport::query()
                ->whereMorphedTo('reportable', $content)
                ->where('reporter_id', $reporter->id)
                ->where('status', ReportStatus::Open)
                ->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        return ContentReport::create([
            'reportable_type' => $content->getMorphClass(),
            'reportable_id' => $content->getKey(),
            'module' => $content->moderationModule(),
            'title' => $content->moderationTitle(),
            'reporter_id' => $reporter?->id,
            'reason' => $reason,
            'details' => $details,
            'status' => ReportStatus::Open,
        ]);
    }

    /**
     * Uphold a report. Other open reports about the same item are resolved with it.
     */
    public function resolveReport(ContentReport $report, User $actor, string $note, bool $takeDown = false, ?ViolationType $violationType = null, ?int $suspensionDays = null): void
    {
        $this->ensureOpen($report);

        DB::transaction(function () use ($report, $actor, $note, $takeDown, $violationType, $suspensionDays): void {
            $content = $report->reportable;

            if ($takeDown && $content instanceof Reportable) {
                $content->takeDown();
            }

            $relatedReports = ContentReport::query()
                ->where('reportable_type', $report->reportable_type)
                ->where('reportable_id', $report->reportable_id)
                ->where('status', ReportStatus::Open)
                ->get();

            foreach ($relatedReports as $relatedReport) {
                $relatedReport->update([
                    'status' => ReportStatus::Resolved,
                    'handled_by' => $actor->id,
                    'handled_at' => now(),
                    'resolution_note' => $relatedReport->is($report) ? $note : "Resolved together with report #{$report->id}.",
                    'content_taken_down' => $takeDown,
                ]);
            }

            $owner = $content instanceof Reportable ? $content->moderationOwner() : null;

            if ($violationType !== null && $owner !== null) {
                $this->issueViolation($owner, $actor, $violationType, "{$report->reason->label()}: {$note}", $suspensionDays, $report);
            }

            $this->auditLogger->log('report.resolved', $report, ['status' => ReportStatus::Open->value], array_filter([
                'status' => ReportStatus::Resolved->value,
                'note' => $note,
                'content_taken_down' => $takeDown,
                'violation' => $violationType?->value,
                'related_reports_resolved' => $relatedReports->count() - 1,
            ]), $actor);
        });
    }

    public function dismissReport(ContentReport $report, User $actor, string $note): void
    {
        $this->ensureOpen($report);

        $report->update(['status' => ReportStatus::Dismissed, 'handled_by' => $actor->id, 'handled_at' => now(), 'resolution_note' => $note]);
        $this->auditLogger->log('report.dismissed', $report, ['status' => ReportStatus::Open->value], ['status' => ReportStatus::Dismissed->value, 'note' => $note], $actor);
    }

    /**
     * Give a user a warning, strike, suspension or ban. Suspensions and bans sign the user out.
     */
    public function issueViolation(User $user, User $actor, ViolationType $type, string $reason, ?int $suspensionDays = null, ?ContentReport $report = null): UserViolation
    {
        if ($user->isAdmin()) {
            throw ValidationException::withMessages(['user' => 'Administrators are managed on the Administrators page, not through violations.']);
        }

        if ($type === ViolationType::Suspension && ($suspensionDays === null || $suspensionDays < 1)) {
            throw ValidationException::withMessages(['suspension_days' => 'Choose how many days the suspension lasts.']);
        }

        $violation = DB::transaction(function () use ($user, $actor, $type, $reason, $suspensionDays, $report): UserViolation {
            $violation = UserViolation::create([
                'user_id' => $user->id,
                'issued_by' => $actor->id,
                'content_report_id' => $report?->id,
                'type' => $type,
                'reason' => $reason,
                'status' => ViolationStatus::Active,
                'expires_at' => $type === ViolationType::Suspension ? now()->addDays($suspensionDays) : null,
            ]);

            if ($type->blocksAccess()) {
                $user->forceFill([
                    'suspended_at' => now(),
                    'suspension_reason' => Str::limit($type->label().': '.$reason, 250),
                ])->save();
            }

            $this->auditLogger->log('violation.issued', $user, newValues: array_filter([
                'type' => $type->value,
                'reason' => $reason,
                'expires_at' => $violation->expires_at?->toIso8601String(),
                'content_report_id' => $report?->id,
            ]), actor: $actor);

            return $violation;
        });

        $user->notify(new ViolationIssued($violation));

        return $violation;
    }

    public function overturnViolation(UserViolation $violation, User $actor, ?string $note = null): void
    {
        if (! $violation->isActive()) {
            throw new LogicException("Violation #{$violation->id} is no longer active.");
        }

        DB::transaction(function () use ($violation, $actor, $note): void {
            $violation->update(['status' => ViolationStatus::Overturned, 'overturned_by' => $actor->id, 'overturned_at' => now()]);
            $this->restoreAccessIfClear($violation->user);
            $this->auditLogger->log('violation.overturned', $violation->user, ['type' => $violation->type->value, 'status' => ViolationStatus::Active->value], array_filter([
                'status' => ViolationStatus::Overturned->value,
                'note' => $note,
            ]), $actor);
        });
    }

    /**
     * End suspensions whose time is up. Run by the scheduler.
     */
    public function expireSuspensions(): int
    {
        $expired = UserViolation::query()
            ->with('user')
            ->where('type', ViolationType::Suspension)
            ->where('status', ViolationStatus::Active)
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $violation) {
            $violation->update(['status' => ViolationStatus::Expired]);
            $this->restoreAccessIfClear($violation->user);
        }

        return $expired->count();
    }

    /**
     * Appeal a rejected submission or an active violation. Only the affected user may appeal, once at a time.
     */
    public function fileAppeal(ApprovalRequest|UserViolation $subject, User $appellant, string $message): ModerationAppeal
    {
        $isAllowed = match (true) {
            $subject instanceof ApprovalRequest => $subject->submitted_by === $appellant->id && $subject->status === ApprovalStatus::Rejected,
            $subject instanceof UserViolation => $subject->user_id === $appellant->id && $subject->isActive(),
        };

        if (! $isAllowed) {
            throw ValidationException::withMessages(['appeal' => 'This decision cannot be appealed.']);
        }

        if ($subject->appeals()->where('status', AppealStatus::Pending)->exists()) {
            throw ValidationException::withMessages(['appeal' => 'An appeal for this decision is already being reviewed.']);
        }

        return $subject->appeals()->create([
            'user_id' => $appellant->id,
            'message' => $message,
            'status' => AppealStatus::Pending,
        ]);
    }

    public function decideAppeal(ModerationAppeal $appeal, User $actor, bool $overturn, ?string $note = null): void
    {
        if (! $appeal->isPending()) {
            throw new LogicException("Appeal #{$appeal->id} has already been decided.");
        }

        DB::transaction(function () use ($appeal, $actor, $overturn, $note): void {
            $appeal->update([
                'status' => $overturn ? AppealStatus::Overturned : AppealStatus::Upheld,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'decision_note' => $note,
            ]);

            if ($overturn) {
                $subject = $appeal->appealable;
                match (true) {
                    $subject instanceof ApprovalRequest => $this->approvalService->approveOnAppeal($subject, $actor, $note),
                    $subject instanceof UserViolation => $this->overturnViolation($subject, $actor, $note),
                };
            }

            $this->auditLogger->log('appeal.decided', $appeal, ['status' => AppealStatus::Pending->value], array_filter([
                'status' => $appeal->status->value,
                'note' => $note,
            ]), $actor);
        });

        $appeal->user->notify(new AppealDecided($appeal));
    }

    /**
     * Lift a user's suspension when no active suspension or ban remains.
     */
    private function restoreAccessIfClear(User $user): void
    {
        $stillBlocked = $user->violations()
            ->where('status', ViolationStatus::Active)
            ->whereIn('type', [ViolationType::Suspension, ViolationType::Ban])
            ->exists();

        if (! $stillBlocked && ! $user->isAdmin()) {
            $user->forceFill(['suspended_at' => null, 'suspension_reason' => null])->save();
        }
    }

    private function ensureOpen(ContentReport $report): void
    {
        if (! $report->isOpen()) {
            throw new LogicException("Report #{$report->id} has already been handled.");
        }
    }
}
