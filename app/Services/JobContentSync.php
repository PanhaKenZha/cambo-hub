<?php

namespace App\Services;

use App\Enums\CmsContentType;
use App\Enums\JobStatus;
use App\Models\CmsContent;
use App\Models\JobListing;
use Illuminate\Support\Str;

/**
 * Keeps the homepage "Latest jobs" / search item (CmsContent) for each job listing in step with it.
 *
 * Only published jobs get an item. Its start and end dates follow the publish and closing
 * dates, so scheduled jobs appear and closed jobs disappear on time.
 */
class JobContentSync
{
    public const string SOURCE_TYPE = 'job_listing';

    public function sync(JobListing $job): void
    {
        if ($job->status !== JobStatus::Published) {
            $this->remove($job);

            return;
        }

        $job->loadMissing(['company', 'category']);
        $publishedAt = $job->published_at ?? $job->created_at ?? now();

        CmsContent::updateOrCreate(
            ['source_type' => self::SOURCE_TYPE, 'source_id' => $job->id],
            [
                'content_type' => CmsContentType::Job,
                'title' => $job->translate('title', 'en'),
                'summary' => Str::limit(strip_tags($job->descriptionHtml('en')), 160) ?: null,
                'badge' => $job->isFeaturedNow() ? 'Featured' : $job->category?->translate('name', 'en'),
                'image_path' => $job->company?->logo_path ? 'storage/'.$job->company->logo_path : null,
                'link_url' => '/jobs/'.$job->slug,
                'meta' => array_filter([
                    'module' => 'jobs',
                    'company' => $job->company?->name,
                    'location' => $job->locationLabel(),
                    'employment_type' => $job->employment_type->label(),
                    'salary' => $job->salaryLabel() === 'Negotiable' ? null : $job->salaryLabel(),
                    'verified' => $job->company?->isVerified() ?: null,
                ]),
                'is_featured' => $job->isFeaturedNow(),
                'is_published' => true,
                'published_at' => $publishedAt,
                'starts_at' => $publishedAt,
                'ends_at' => $job->closes_at,
                'sort_order' => 0,
            ],
        );

        app(SiteContent::class)->flush();
    }

    public function remove(JobListing $job): void
    {
        if (CmsContent::where('source_type', self::SOURCE_TYPE)->where('source_id', $job->id)->delete() > 0) {
            app(SiteContent::class)->flush();
        }
    }
}
