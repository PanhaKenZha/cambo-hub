<?php

namespace App\Http\Controllers\Admin\News;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use App\Services\AuditLogger;
use App\Services\NewsContentSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(): View
    {
        return view('admin.news.categories.index', [
            'categories' => NewsCategory::withCount('articles')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Create);

        return view('admin.news.categories.form', ['category' => new NewsCategory(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Create);
        $data = $this->validatedData($request);
        $data['sort_order'] = (int) NewsCategory::max('sort_order') + 1;

        $category = NewsCategory::create($data);
        $this->auditLogger->log('news.category.created', $category, newValues: $category->only(['slug', 'name']));

        return redirect()->route('admin.news.categories.index')->with('status', 'Category added.');
    }

    public function edit(NewsCategory $category): View
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Edit);

        return view('admin.news.categories.form', ['category' => $category]);
    }

    public function update(Request $request, NewsCategory $category, NewsContentSync $sync): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Edit);

        $oldValues = $category->only(['slug', 'name', 'is_active']);
        $category->update($this->validatedData($request, $category));
        $this->auditLogger->log('news.category.updated', $category, $oldValues, $category->only(array_keys($oldValues)));

        // The category name is shown as the badge on homepage news items.
        $category->articles()->each(fn ($article) => $sync->sync($article));

        return redirect()->route('admin.news.categories.index')->with('status', 'Category saved.');
    }

    public function destroy(NewsCategory $category): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Delete);

        $this->auditLogger->log('news.category.deleted', $category, $category->only(['slug', 'name']));
        $category->delete();

        return back()->with('status', 'Category deleted. Its articles are now uncategorised.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?NewsCategory $category = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name.en', ''))]);

        $data = $request->validate([
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('news_categories', 'slug')->ignore($category?->id)],
            'name.en' => ['required', 'string', 'max:60'],
            'name.km' => ['nullable', 'string', 'max:60'],
            'description.*' => ['nullable', 'string', 'max:300'],
            'is_active' => ['boolean'],
        ], [], ['name.en' => 'English name']);

        return [
            'slug' => $data['slug'],
            'name' => NewsCategory::normalizeTranslations($data['name']),
            'description' => NewsCategory::normalizeTranslations($data['description'] ?? []),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
