<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ApplyMethod;
use App\Enums\EmploymentType;
use App\Models\JobCategory;
use App\Models\JobListing;
use App\Services\PlatformSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Public job board, job pages and on-site applications.
 */
class JobController extends Controller
{
    /**
     * Browse every live job, with keyword search, filters and facet counts.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::enum(EmploymentType::class)],
            'remote' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['newest', 'closing'])],
        ]);

        $term = trim($filters['q'] ?? '');
        $sort = $filters['sort'] ?? 'newest';
        $type = isset($filters['type']) ? EmploymentType::from($filters['type']) : null;
        $remoteOnly = (bool) ($filters['remote'] ?? false);

        $categories = JobCategory::query()
            ->where('is_active', true)
            ->withCount(['jobListings' => fn (Builder $query) => $query->live()])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (JobCategory $category): bool => $category->job_listings_count > 0)
            ->values();

        $selectedCategory = $categories->firstWhere('slug', $filters['category'] ?? null);

        $jobs = JobListing::live()
            ->with(['company', 'category'])
            ->when($term !== '', function (Builder $query) use ($term): void {
                // "!" is the LIKE escape character: portable across MySQL, PostgreSQL and SQLite.
                $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

                $query->where(fn (Builder $query) => $query
                    ->whereRaw("title like ? escape '!'", [$pattern])
                    ->orWhereRaw("location like ? escape '!'", [$pattern])
                    ->orWhereHas('company', fn (Builder $query) => $query->whereRaw("name like ? escape '!'", [$pattern])));
            })
            ->when($selectedCategory, fn (Builder $query) => $query->where('job_category_id', $selectedCategory->id))
            ->when($type, fn (Builder $query) => $query->where('employment_type', $type))
            ->when($remoteOnly, fn (Builder $query) => $query->where('is_remote', true))
            ->orderByRaw('case when is_featured and (featured_until is null or featured_until > ?) then 0 else 1 end', [now()])
            ->when($sort === 'closing', fn (Builder $query) => $query->orderByRaw('closes_at is null')->orderBy('closes_at'))
            ->orderByRaw('coalesce(published_at, created_at) desc')
            ->paginate(12)
            ->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
            'locale' => app()->getLocale(),
            'term' => $term,
            'sort' => $sort,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'selectedType' => $type,
            'remoteOnly' => $remoteOnly,
            'typeCounts' => JobListing::live()->selectRaw('employment_type, count(*) as total')->groupBy('employment_type')->pluck('total', 'employment_type'),
            'remoteCount' => JobListing::live()->where('is_remote', true)->count(),
            'liveJobCount' => JobListing::live()->count(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $job = JobListing::live()->with(['company', 'category'])->where('slug', $slug)->firstOrFail();
        $job->increment('views_count');

        return view('jobs.show', [
            'job' => $job,
            'locale' => app()->getLocale(),
            'hasApplied' => $request->user() !== null && $job->applications()->where('user_id', $request->user()->id)->exists(),
            'similarJobs' => JobListing::live()
                ->with(['company', 'category'])
                ->whereKeyNot($job->id)
                ->where(fn (Builder $query) => $query->where('job_category_id', $job->job_category_id)->orWhere('company_id', $job->company_id))
                ->orderByRaw('coalesce(published_at, created_at) desc')
                ->limit(3)
                ->get(),
        ]);
    }

    /**
     * Apply with a CV. The CV is stored privately; only administrators can download it.
     */
    public function apply(Request $request, string $slug, PlatformSettings $settings): RedirectResponse
    {
        $job = JobListing::live()->where('slug', $slug)->firstOrFail();
        abort_unless($job->apply_method === ApplyMethod::OnSite, 404);

        if ($job->applications()->where('user_id', $request->user()->id)->exists()) {
            return back()->withErrors(['cv' => __('You have already applied for this job.')]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:'.((int) $settings->get('uploads.max_size_mb', 10) * 1024)],
        ]);

        $job->applications()->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'cv_path' => $request->file('cv')->store('job-applications', 'local'),
            'cv_original_name' => $request->file('cv')->getClientOriginalName(),
            'status' => ApplicationStatus::New,
        ]);

        return back()->with('status', __('Your application has been sent. Good luck!'));
    }
}
