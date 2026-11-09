<?php

namespace App\Services;

use App\Enums\BannerPlacement;
use App\Enums\MenuLocation;
use App\Enums\PlatformModule;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\HomepageSection;
use App\Models\MenuItem;
use App\Models\Page;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Read-only access to website content managed in Admin › Website, for the public site.
 *
 * Every method returns plain arrays already translated into the requested language
 * (default: the current app locale). Results are cached and refreshed automatically
 * whenever an administrator saves content; publish and expiry dates are applied on
 * every call, so scheduled content appears on time.
 */
class SiteContent
{
    private const string VERSION_KEY = 'site-content.version';

    private const int CACHE_SECONDS = 3600;

    /**
     * A live page, or null when it does not exist or is not published.
     *
     * @return array{slug: string, title: string, html: string, meta_title: string, meta_description: string, updated_at: string}|null
     */
    public function page(string $slug, ?string $locale = null): ?array
    {
        $locale = $this->locale($locale);

        $page = $this->remember("page.{$slug}.{$locale}", function () use ($slug, $locale): ?array {
            $page = Page::live()->where('slug', $slug)->first();

            return $page === null ? null : [
                'slug' => $page->slug,
                'title' => $page->translate('title', $locale),
                'html' => $page->bodyHtml($locale),
                'meta_title' => $page->translate('meta_title', $locale) ?: $page->translate('title', $locale),
                'meta_description' => $page->translate('meta_description', $locale),
                'updated_at' => $page->updated_at->toIso8601String(),
                'unpublish_at' => $page->unpublish_at?->toIso8601String(),
            ];
        }, fn (?array $page): bool => $page !== null && ($page['unpublish_at'] === null || Carbon::parse($page['unpublish_at'])->isFuture()));

        return $page === null ? null : array_diff_key($page, ['unpublish_at' => true]);
    }

    /**
     * Visible menu items for a location, with one level of children.
     *
     * @return list<array{label: string, url: ?string, new_tab: bool, children: list<array{label: string, url: ?string, new_tab: bool}>}>
     */
    public function menu(MenuLocation $location, ?string $locale = null): array
    {
        $locale = $this->locale($locale);

        return $this->remember("menu.{$location->value}.{$locale}", function () use ($location, $locale): array {
            $toArray = fn (MenuItem $item): array => [
                'label' => $item->translate('label', $locale),
                'url' => $item->resolvedUrl(),
                'new_tab' => $item->opens_in_new_tab,
            ];

            return MenuItem::query()
                ->with(['page', 'children' => fn ($query) => $query->where('is_visible', true)->with('page')])
                ->where('location', $location)
                ->whereNull('parent_id')
                ->where('is_visible', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (MenuItem $item): array => [...$toArray($item), 'children' => $item->children->map($toArray)->values()->all()])
                ->values()
                ->all();
        });
    }

    /**
     * Banners currently running in a placement, in display order.
     *
     * @return list<array{title: string, subtitle: string, button_label: string, link_url: ?string, image_url: ?string}>
     */
    public function banners(BannerPlacement $placement, ?string $locale = null): array
    {
        $locale = $this->locale($locale);

        $banners = $this->remember("banners.{$placement->value}.{$locale}", fn (): array => Banner::query()
            ->where('placement', $placement)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Banner $banner): array => [
                'title' => $banner->translate('title', $locale),
                'subtitle' => $banner->translate('subtitle', $locale),
                'button_label' => $banner->translate('button_label', $locale),
                'link_url' => $banner->link_url,
                'image_url' => $banner->imageUrl(),
                'starts_at' => $banner->starts_at?->toIso8601String(),
                'ends_at' => $banner->ends_at?->toIso8601String(),
            ])
            ->all());

        return $this->withinSchedule($banners);
    }

    /**
     * Homepage sections in order. Sections for turned-off modules are left out.
     *
     * @return list<array{type: string, title: string, subtitle: string, button_label: string, link_url: ?string, image_url: ?string, item_count: int}>
     */
    public function homepageSections(?string $locale = null): array
    {
        $locale = $this->locale($locale);

        $sections = $this->remember("homepage.{$locale}", fn (): array => HomepageSection::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HomepageSection $section): array => [
                'type' => $section->type->value,
                'module' => $section->type->module()?->value,
                'title' => $section->translate('title', $locale) ?: $section->type->label(),
                'subtitle' => $section->translate('subtitle', $locale),
                'button_label' => $section->translate('button_label', $locale),
                'link_url' => $section->link_url,
                'image_url' => $section->imageUrl(),
                'item_count' => $section->item_count,
            ])
            ->all());

        return array_values(array_filter(
            $sections,
            fn (array $section): bool => $section['module'] === null || PlatformModule::from($section['module'])->isEnabled(),
        ));
    }

    /**
     * Announcements running right now, newest first.
     *
     * @return list<array{id: int, message: string, link_url: ?string, link_label: string, level: string, dismissible: bool}>
     */
    public function announcements(?string $locale = null): array
    {
        $locale = $this->locale($locale);

        $announcements = $this->remember("announcements.{$locale}", fn (): array => Announcement::query()
            ->where('is_active', true)
            ->latest('id')
            ->get()
            ->map(fn (Announcement $announcement): array => [
                'id' => $announcement->id,
                'message' => $announcement->translate('message', $locale),
                'link_url' => $announcement->link_url,
                'link_label' => $announcement->translate('link_label', $locale),
                'level' => $announcement->level->value,
                'dismissible' => $announcement->is_dismissible,
                'starts_at' => $announcement->starts_at?->toIso8601String(),
                'ends_at' => $announcement->ends_at?->toIso8601String(),
            ])
            ->all());

        return $this->withinSchedule($announcements);
    }

    /**
     * Footer text and social links from Admin › Platform settings › Footer, plus the footer menu.
     *
     * @return array{about: string, copyright: string, social_links: array<string, string>, menu: list<array<string, mixed>>}
     */
    public function footer(?string $locale = null): array
    {
        $locale = $this->locale($locale);
        $settings = app(PlatformSettings::class)->group('footer');

        return [
            'about' => ($locale === 'km' && filled($settings['about_km'])) ? $settings['about_km'] : $settings['about_en'],
            'copyright' => str_replace('{year}', (string) now()->year, $settings['copyright']),
            'social_links' => array_filter([
                'facebook' => $settings['facebook_url'],
                'telegram' => $settings['telegram_url'],
                'youtube' => $settings['youtube_url'],
                'tiktok' => $settings['tiktok_url'],
                'linkedin' => $settings['linkedin_url'],
            ]),
            'menu' => $this->menu(MenuLocation::Footer, $locale),
        ];
    }

    /**
     * Discard all cached content. Called automatically when CMS content is saved.
     */
    public function flush(): void
    {
        Cache::forever(self::VERSION_KEY, Str::random(8));
    }

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @param  (Closure(TValue): bool)|null  $isStillValid
     * @return TValue
     */
    private function remember(string $key, Closure $callback, ?Closure $isStillValid = null): mixed
    {
        $cacheKey = 'site-content.'.Cache::rememberForever(self::VERSION_KEY, fn (): string => Str::random(8)).'.'.$key;
        $value = Cache::remember($cacheKey, self::CACHE_SECONDS, $callback);

        if ($isStillValid !== null && $value !== null && ! $isStillValid($value)) {
            Cache::forget($cacheKey);

            return $callback();
        }

        return $value;
    }

    /**
     * Keep items whose start/end window includes now, and drop the internal date fields.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function withinSchedule(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item): bool => ($item['starts_at'] === null || Carbon::parse($item['starts_at'])->isPast())
                && ($item['ends_at'] === null || Carbon::parse($item['ends_at'])->isFuture()))
            ->map(fn (array $item): array => array_diff_key($item, array_flip(['starts_at', 'ends_at'])))
            ->values()
            ->all();
    }

    private function locale(?string $locale): string
    {
        return $locale ?? app()->getLocale();
    }
}
