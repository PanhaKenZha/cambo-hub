<?php

namespace App\Console\Commands;

use App\Enums\JobStatus;
use App\Models\JobListing;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jobs:expire')]
#[Description('Close jobs past their closing date and end expired featuring')]
class ExpireJobListings extends Command
{
    public function handle(): int
    {
        $closed = 0;
        JobListing::where('status', JobStatus::Published)->where('closes_at', '<=', now())->each(function (JobListing $job) use (&$closed): void {
            $job->update(['status' => JobStatus::Closed]);
            $closed++;
        });

        $unfeatured = 0;
        JobListing::where('is_featured', true)->where('featured_until', '<=', now())->each(function (JobListing $job) use (&$unfeatured): void {
            $job->update(['is_featured' => false, 'featured_until' => null]);
            $unfeatured++;
        });

        $this->info("{$closed} job(s) closed, {$unfeatured} featured job(s) ended.");

        return self::SUCCESS;
    }
}
