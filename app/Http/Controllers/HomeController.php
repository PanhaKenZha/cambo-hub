<?php

namespace App\Http\Controllers;

use App\Enums\BannerPlacement;
use App\Enums\HomepageSectionType;
use App\Enums\PlatformModule;
use App\Models\JobListing;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Services\ContentItems;
use App\Services\PlatformSettings;
use App\Services\SiteContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Render the homepage from the sections, banners and content managed in Admin › Website.
     */
    public function __invoke(SiteContent $siteContent, ContentItems $contentItems, PlatformSettings $settings): View
    {
        $sections = collect($siteContent->homepageSections())
            ->map(function (array $section) use ($siteContent, $contentItems): array {
                $type = HomepageSectionType::from($section['type']);

                $section['items'] = match (true) {
                    $type === HomepageSectionType::PromotionalBanners => collect($siteContent->banners(BannerPlacement::HomepageHero))->take($section['item_count']),
                    $type->contentType() !== null => $contentItems->latest($type->contentType(), $section['item_count']),
                    default => collect(),
                };

                return $section;
            })
            // Item sections with nothing published are skipped instead of showing an empty block.
            ->reject(fn (array $section): bool => HomepageSectionType::from($section['type'])->showsItems() && $section['items']->isEmpty())
            ->values();

        return view('home', [
            'sections' => $sections,
            'stats' => $this->platformStats(),
            'search' => [
                'enabled' => (bool) $settings->get('search.enabled', true),
                'placeholder' => $settings->get('homepage.search_placeholder'),
                'suggestions' => Str::of((string) $settings->get('homepage.search_suggestions'))->explode(',')->map(fn (string $term): string => trim($term))->filter()->values()->all(),
            ],
        ]);
    }

    /**
     * Live counts shown under the hero, for services that are switched on and have content.
     *
     * @return list<array{value: int, label: string, url: string, icon: string}>
     */
    private function platformStats(): array
    {
        $stats = [];

        if (PlatformModule::Jobs->isEnabled()) {
            $stats[] = ['value' => JobListing::live()->count(), 'label' => __('Open jobs'), 'url' => route('jobs.index'), 'icon' => 'jobs'];
            $stats[] = ['value' => JobListing::live()->distinct()->count('company_id'), 'label' => __('Companies hiring'), 'url' => route('jobs.index'), 'icon' => 'business'];
        }

        if (PlatformModule::News->isEnabled()) {
            $stats[] = ['value' => NewsArticle::live()->count(), 'label' => __('News articles'), 'url' => route('news.index'), 'icon' => 'news'];
            $stats[] = ['value' => NewsCategory::where('is_active', true)->whereHas('articles', fn (Builder $query) => $query->live())->count(), 'label' => __('News topics'), 'url' => route('news.index'), 'icon' => 'blog'];
        }

        return array_values(array_filter($stats, fn (array $stat): bool => $stat['value'] > 0));
    }
}
