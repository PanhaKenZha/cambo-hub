<?php

namespace App\Http\Controllers\Admin\Jobs;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use App\Services\AuditLogger;
use App\Services\JobContentSync;
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
        return view('admin.jobs.categories.index', [
            'categories' => JobCategory::withCount('jobListings')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Create);

        return view('admin.jobs.categories.form', ['category' => new JobCategory(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Create);
        $data = $this->validatedData($request);
        $data['sort_order'] = (int) JobCategory::max('sort_order') + 1;

        $category = JobCategory::create($data);
        $this->auditLogger->log('jobs.category.created', $category, newValues: $category->only(['slug', 'name']));

        return redirect()->route('admin.jobs.categories.index')->with('status', 'Category added.');
    }

    public function edit(JobCategory $category): View
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);

        return view('admin.jobs.categories.form', ['category' => $category]);
    }

    public function update(Request $request, JobCategory $category, JobContentSync $sync): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);

        $oldValues = $category->only(['slug', 'name', 'is_active']);
        $category->update($this->validatedData($request, $category));
        $this->auditLogger->log('jobs.category.updated', $category, $oldValues, $category->only(array_keys($oldValues)));
        $category->jobListings()->each(fn ($job) => $sync->sync($job));

        return redirect()->route('admin.jobs.categories.index')->with('status', 'Category saved.');
    }

    public function destroy(JobCategory $category): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Delete);

        $this->auditLogger->log('jobs.category.deleted', $category, $category->only(['slug', 'name']));
        $category->delete();

        return back()->with('status', 'Category deleted. Its jobs are now uncategorised.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?JobCategory $category = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name.en', ''))]);

        $data = $request->validate([
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('job_categories', 'slug')->ignore($category?->id)],
            'name.en' => ['required', 'string', 'max:60'],
            'name.km' => ['nullable', 'string', 'max:60'],
            'is_active' => ['boolean'],
        ], [], ['name.en' => 'English name']);

        return [
            'slug' => $data['slug'],
            'name' => JobCategory::normalizeTranslations($data['name']),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
