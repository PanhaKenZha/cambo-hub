<?php

namespace App\Services;

use App\Enums\CmsContentType;
use App\Models\CmsContent;
use App\Models\CmsSetting;
use App\Models\HomepageSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Reads site settings, homepage layout, and published content from the
 * central CMS. Registered as a scoped service so settings load once per request.
 */
class CmsService
{
    /** @var array<string, mixed>|null */
    private ?array $settings = null;

    /**
     * Every CMS setting, with stored values taking precedence over config defaults.
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $settings = config('cms.settings', []);

        foreach (CmsSetting::query()->pluck('value', 'key') as $key => $stored) {
            $stored = is_string($stored) ? json_decode($stored, true) : $stored;
            $default = $settings[$key] ?? null;

            $settings[$key] = is_array($default) && is_array($stored) && ! array_is_list($default)
                ? array_replace($default, $stored)
                : $stored;
        }

        return $this->settings = $settings;
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings(), $key, $default);
    }

    /**
     * Resolve a CMS link definition to a URL.
     *
     * @param  array{route?: string, parameters?: array<string, mixed>, url?: string}  $link
     */
    public function linkUrl(array $link): string
    {
        if (isset($link['route']) && Route::has($link['route'])) {
            return route($link['route'], $link['parameters'] ?? []);
        }

        return url($link['url'] ?? '/');
    }

    /**
     * Enabled homepage sections in CMS order, each with its visible items.
     *
     * @return Collection<int, array{section: HomepageSection, items: Collection<int, CmsContent>}>
     */
    public function homepageSections(): Collection
    {
        return HomepageSection::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (HomepageSection $section): array => [
                'section' => $section,
                'items' => CmsContent::query()
                    ->where('content_type', $section->content_type)
                    ->visible()
                    ->curated()
                    ->limit(max(1, $section->item_limit))
                    ->get(),
            ])
            ->filter(fn (array $block): bool => $block['items']->isNotEmpty())
            ->values();
    }

    /**
     * Search visible content across every searchable service, grouped by type.
     *
     * @return Collection<string, Collection<int, CmsContent>>
     */
    public function search(string $term, ?CmsContentType $type = null, int $perType = 12): Collection
    {
        $types = $type !== null ? [$type] : CmsContentType::searchable();
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

        return collect($types)
            ->mapWithKeys(fn (CmsContentType $contentType): array => [
                $contentType->value => CmsContent::query()
                    ->where('content_type', $contentType)
                    ->visible()
                    ->when($term !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                        ->whereRaw("title like ? escape '!'", [$pattern])
                        ->orWhereRaw("summary like ? escape '!'", [$pattern])
                        ->orWhereRaw("badge like ? escape '!'", [$pattern])))
                    ->curated()
                    ->limit($perType)
                    ->get(),
            ])
            ->filter(fn (Collection $items): bool => $items->isNotEmpty());
    }
}
