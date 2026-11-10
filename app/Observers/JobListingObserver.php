<?php

namespace App\Observers;

use App\Models\JobListing;
use App\Services\JobContentSync;

/**
 * Mirrors each job listing onto the homepage and search whenever it changes.
 */
class JobListingObserver
{
    public function __construct(private JobContentSync $sync) {}

    public function saved(JobListing $jobListing): void
    {
        $this->sync->sync($jobListing);
    }

    public function deleted(JobListing $jobListing): void
    {
        $this->sync->remove($jobListing);
    }
}
