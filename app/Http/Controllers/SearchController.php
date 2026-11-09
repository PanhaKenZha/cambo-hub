<?php

namespace App\Http\Controllers;

use App\Enums\CmsContentType;
use App\Services\CmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Search published content across every platform service.
     */
    public function __invoke(Request $request, CmsService $cms): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(array_map(fn (CmsContentType $type): string => $type->value, CmsContentType::searchable()))],
        ]);

        $term = trim($validated['q'] ?? '');
        $type = isset($validated['type']) ? CmsContentType::from($validated['type']) : null;

        return view('search', [
            'term' => $term,
            'type' => $type,
            'types' => CmsContentType::searchable(),
            'results' => $term === '' && $type === null ? collect() : $cms->search($term, $type),
            'placeholder' => $cms->setting('search.placeholder'),
            'suggestions' => $cms->setting('search.suggestions', []),
        ]);
    }
}
