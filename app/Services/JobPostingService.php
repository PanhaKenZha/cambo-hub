<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Submitting jobs for publication. Used by the admin panel today and by the
 * employer pages on the public site (Developer 2).
 *
 * Jobs from verified companies are published straight away when the
 * "Verified companies skip review" setting is on; everything else goes to the Approval center.
 */
class JobPostingService
{
    public function __construct(
        private ApprovalService $approvals,
        private AuditLogger $auditLogger,
        private PlatformSettings $settings,
    ) {}

    /**
     * Returns true when the job was published immediately, false when it was sent for review.
     */
    public function submit(JobListing $job, User $submitter): bool
    {
        if (! in_array($job->status, [JobStatus::Draft, JobStatus::Rejected], true)) {
            throw ValidationException::withMessages(['status' => 'Only drafts and rejected jobs can be submitted.']);
        }

        $job->loadMissing('company');
        $skipsReview = $job->company->isVerified() && $this->settings->get('jobs.verified_skip_review');

        if ($skipsReview) {
            $job->update([
                'status' => JobStatus::Published,
                'published_at' => $job->published_at ?? now(),
                'closes_at' => $job->closes_at ?? $this->defaultClosingDate(),
                'submitted_by' => $job->submitted_by ?? $submitter->id,
            ]);
            $this->auditLogger->log('jobs.listing.auto_published', $job, ['status' => JobStatus::Draft->value], ['status' => JobStatus::Published->value, 'reason' => 'verified company']);

            return true;
        }

        $job->update(['status' => JobStatus::InReview, 'submitted_by' => $job->submitted_by ?? $submitter->id]);
        $this->approvals->submit($job, $submitter, $job->company->name.' · '.$job->employment_type->label().' · '.$job->locationLabel());
        $this->auditLogger->log('jobs.listing.submitted', $job, ['status' => JobStatus::Draft->value], ['status' => JobStatus::InReview->value]);

        return false;
    }

    /**
     * Closing date for jobs published without one, from the "Default listing length" setting.
     */
    public function defaultClosingDate(): Carbon
    {
        return now()->addDays((int) $this->settings->get('jobs.default_listing_days', 30))->endOfDay();
    }
}
