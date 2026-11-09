<?php

namespace App\Services;

use App\Enums\CmsContentType;
use App\Enums\PlatformModule;
use App\Models\CmsContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Published content items (news, jobs, products, events…) for homepage sections
 * and global search. Layout, menus, banners and footer come from SiteContent.
 */
class ContentItems
{
    /**
     * Visible items of one type, featured first. Service categories whose module is off are skipped.
     *
     * @return Collection<int, CmsContent>
     */
    public function latest(CmsContentType $type, int $limit): Collection
    {
        if (! $type->isAvailable()) {
            return collect();
        }

        return CmsContent::query()
            ->where('content_type', $type)
            ->visible()
            ->curated()
            ->limit(max(1, $limit) * ($type === CmsContentType::ServiceCategory ? 2 : 1))
            ->get()
            ->filter(fn (CmsContent $item): bool => ($module = PlatformModule::tryFrom((string) $item->detail('module'))) === null || $module->isEnabled())
            ->take(max(1, $limit))
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
        // "!" is the LIKE escape character: portable across MySQL, PostgreSQL and SQLite.
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
