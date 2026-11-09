<?php

namespace App\Http\Controllers;

use App\Enums\CmsContentType;
use App\Services\ContentItems;
use App\Services\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Search published content across every platform service that is switched on.
     */
    public function __invoke(Request $request, ContentItems $contentItems, PlatformSettings $settings): View
    {
        abort_unless($settings->get('search.enabled', true), 404);

        $minimumLength = (int) $settings->get('search.min_query_length', 2);
        $types = CmsContentType::searchable();

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'min:'.$minimumLength, 'max:100'],
            'type' => ['nullable', Rule::in(array_map(fn (CmsContentType $type): string => $type->value, $types))],
        ]);

        $term = trim($validated['q'] ?? '');
        $type = isset($validated['type']) ? CmsContentType::from($validated['type']) : null;

        return view('search', [
            'term' => $term,
            'type' => $type,
            'types' => $types,
            'results' => $term === '' && $type === null ? collect() : $contentItems->search($term, $type, (int) $settings->get('search.results_per_page', 20)),
            'placeholder' => $settings->get('homepage.search_placeholder'),
            'suggestions' => Str::of((string) $settings->get('homepage.search_suggestions'))->explode(',')->map(fn (string $term): string => trim($term))->filter()->values()->all(),
        ]);
    }
}
