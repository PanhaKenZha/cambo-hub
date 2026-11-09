<?php

namespace App\Http\Controllers;

use App\Enums\BannerPlacement;
use App\Enums\HomepageSectionType;
use App\Services\ContentItems;
use App\Services\PlatformSettings;
use App\Services\SiteContent;
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
            'search' => [
                'enabled' => (bool) $settings->get('search.enabled', true),
                'placeholder' => $settings->get('homepage.search_placeholder'),
                'suggestions' => Str::of((string) $settings->get('homepage.search_suggestions'))->explode(',')->map(fn (string $term): string => trim($term))->filter()->values()->all(),
            ],
        ]);
    }
}
