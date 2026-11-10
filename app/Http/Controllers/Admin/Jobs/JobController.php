<?php

namespace App\Http\Controllers\Admin\Jobs;

use App\Enums\ApplyMethod;
use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobListing;
use App\Services\ApprovalService;
use App\Services\AuditLogger;
use App\Services\JobPostingService;
use App\Services\PlatformSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobController extends Controller
{
    /**
     * Tabs on the job list: key => label.
     */
    private const array TABS = [
        'all' => 'All',
        'published' => 'Live',
        'scheduled' => 'Scheduled',
        'in_review' => 'In review',
        'draft' => 'Drafts',
        'closed' => 'Closed',
        'rejected' => 'Rejected',
        'featured' => 'Featured',
    ];

    public function __construct(
        private AuditLogger $auditLogger,
        private ApprovalService $approvals,
        private JobPostingService $posting,
        private PlatformSettings $settings,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(array_keys(self::TABS))],
            'category' => ['nullable', 'integer'],
            'company' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $tab = $filters['tab'] ?? 'all';

        return view('admin.jobs.listings.index', [
            'jobs' => $this->tabQuery($tab)
                ->with(['company', 'category'])
                ->withCount('applications')
                ->when($filters['category'] ?? null, fn ($query, int $category) => $query->where('job_category_id', $category))
                ->when($filters['company'] ?? null, fn ($query, int $company) => $query->where('company_id', $company))
                ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                    ->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
                ->orderByRaw('coalesce(published_at, updated_at) desc')
                ->paginate(20)
                ->withQueryString(),
            'tabs' => self::TABS,
            'activeTab' => $tab,
            'tabCounts' => collect(array_keys(self::TABS))->mapWithKeys(fn (string $key): array => [$key => $this->tabQuery($key)->count()]),
            'categories' => JobCategory::orderBy('sort_order')->get(),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Create);

        return view('admin.jobs.listings.form', $this->formData(new JobListing([
            'company_id' => $request->integer('company') ?: null,
            'status' => JobStatus::Draft,
            'employment_type' => EmploymentType::FullTime,
            'apply_method' => ApplyMethod::OnSite,
            'salary_currency' => 'USD',
            'salary_period' => 'month',
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Create);

        $job = JobListing::create([...$this->validatedData($request), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        $this->auditLogger->log('jobs.listing.created', $job, newValues: ['title' => $job->title, 'company' => $job->company->name, 'status' => $job->status->value]);

        return $this->afterSave($request, $job, 'Job created.');
    }

    public function edit(JobListing $listing): View
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);

        return view('admin.jobs.listings.form', $this->formData($listing->load(['approvalRequests' => fn ($query) => $query->latest('id')])->loadCount('applications')));
    }

    public function update(Request $request, JobListing $listing): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);

        $oldValues = $listing->only(['title', 'status', 'published_at', 'closes_at', 'is_featured', 'company_id']);
        $listing->update([...$this->validatedData($request, $listing), 'updated_by' => $request->user()->id]);
        $this->auditLogger->log('jobs.listing.updated', $listing, $oldValues, $listing->only(array_keys($oldValues)));

        // An editor who can publish has decided the job; close any open review for it.
        $pendingReview = $listing->approvalRequests()->pending()->first();
        if ($pendingReview && $listing->status === JobStatus::Published && $request->user()->can('review', $pendingReview)) {
            $this->approvals->approve($pendingReview, $request->user(), 'Published from the job editor.');
        }

        return $this->afterSave($request, $listing, 'Job saved.');
    }

    public function destroy(JobListing $listing): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Delete);

        $this->auditLogger->log('jobs.listing.deleted', $listing, ['title' => $listing->title, 'company_id' => $listing->company_id]);
        $listing->delete();

        return redirect()->route('admin.jobs.listings.index')->with('status', 'Job deleted.');
    }

    public function submit(Request $request, JobListing $listing): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);

        return back()->with('status', $this->posting->submit($listing, $request->user())
            ? 'Job published — the company is verified.'
            : 'Job sent for review in the Approval center.');
    }

    /**
     * Feature a job on the homepage for the configured number of days, or stop featuring it.
     */
    public function toggleFeatured(JobListing $listing): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Manage);

        $feature = ! $listing->isFeaturedNow();
        $listing->update([
            'is_featured' => $feature,
            'featured_until' => $feature ? now()->addDays((int) $this->settings->get('jobs.featured_days', 14)) : null,
        ]);
        $this->auditLogger->log('jobs.listing.updated', $listing, ['is_featured' => ! $feature], ['is_featured' => $feature, 'featured_until' => $listing->featured_until?->toIso8601String()]);

        return back()->with('status', $feature ? "Job featured until {$listing->featured_until->toFormattedDateString()}." : 'Job no longer featured.');
    }

    /**
     * Stop accepting applications now.
     */
    public function close(JobListing $listing): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Manage);

        $listing->update(['status' => JobStatus::Closed, 'is_featured' => false]);
        $this->auditLogger->log('jobs.listing.closed', $listing, ['status' => JobStatus::Published->value], ['status' => JobStatus::Closed->value]);

        return back()->with('status', 'Job closed. It no longer accepts applications.');
    }

    private function afterSave(Request $request, JobListing $job, string $message): RedirectResponse
    {
        if ($request->input('intent') === 'submit') {
            $message = $this->posting->submit($job, $request->user())
                ? 'Job saved and published — the company is verified.'
                : 'Job saved and sent for review.';
        }

        return redirect()->route('admin.jobs.listings.edit', $job)->with('status', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?JobListing $job = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title.en', ''))]);
        $canPublish = $request->user()->can(PermissionArea::Jobs->permission(PermissionAction::Manage));

        $data = $request->validate([
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'job_category_id' => ['nullable', 'integer', Rule::exists('job_categories', 'id')],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('job_listings', 'slug')->ignore($job?->id)],
            'title.en' => ['required', 'string', 'max:150'],
            'title.km' => ['nullable', 'string', 'max:150'],
            'description.en' => ['required', 'string', 'max:50000'],
            'description.km' => ['nullable', 'string', 'max:50000'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'location' => ['nullable', 'string', 'max:150'],
            'is_remote' => ['boolean'],
            'salary_min' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'max:1000000000', 'gte:salary_min'],
            'salary_currency' => ['required', Rule::in(['USD', 'KHR'])],
            'salary_period' => ['required', Rule::in(['hour', 'day', 'month', 'year'])],
            'apply_method' => ['required', Rule::enum(ApplyMethod::class)],
            'apply_url' => ['nullable', 'required_if:apply_method,'.ApplyMethod::ExternalUrl->value, 'url', 'max:500'],
            'apply_email' => ['nullable', 'required_if:apply_method,'.ApplyMethod::Email->value, 'email', 'max:255'],
            'closes_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in([JobStatus::Draft->value, JobStatus::Published->value])],
            'published_at' => ['nullable', 'date'],
        ], ['salary_max.gte' => 'The highest salary must be at least the lowest salary.'], [
            'company_id' => 'company', 'job_category_id' => 'category', 'title.en' => 'English job title', 'description.en' => 'English description', 'closes_at' => 'closing date',
        ]);

        $values = [
            'company_id' => $data['company_id'],
            'job_category_id' => $data['job_category_id'] ?? null,
            'slug' => $data['slug'],
            'title' => JobListing::normalizeTranslations($data['title']),
            'description' => JobListing::normalizeTranslations($data['description']),
            'employment_type' => $data['employment_type'],
            'location' => $data['location'] ?? null,
            'is_remote' => $request->boolean('is_remote'),
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'salary_currency' => $data['salary_currency'],
            'salary_period' => $data['salary_period'],
            'apply_method' => $data['apply_method'],
            'apply_url' => $data['apply_method'] === ApplyMethod::ExternalUrl->value ? $data['apply_url'] : null,
            'apply_email' => $data['apply_method'] === ApplyMethod::Email->value ? $data['apply_email'] : null,
            'closes_at' => $data['closes_at'] ?? null,
        ];

        // Status and publish date are publishing decisions: only editors with jobs.manage make them.
        if ($canPublish) {
            $status = JobStatus::from($data['status'] ?? ($job?->status === JobStatus::Published ? 'published' : 'draft'));
            $values['status'] = $status;
            $values['published_at'] = $status === JobStatus::Published ? ($data['published_at'] ?? $job?->published_at ?? now()) : ($data['published_at'] ?? null);
            $values['closes_at'] ??= $status === JobStatus::Published ? $this->posting->defaultClosingDate() : null;
        } elseif ($job === null) {
            $values['status'] = JobStatus::Draft;
        }

        return $values;
    }

    /**
     * @return Builder<JobListing>
     */
    private function tabQuery(string $tab): Builder
    {
        $query = JobListing::query();

        return match ($tab) {
            'published' => $query->live(),
            'scheduled' => $query->where('status', JobStatus::Published)->where('published_at', '>', now()),
            'in_review' => $query->where('status', JobStatus::InReview),
            'draft' => $query->where('status', JobStatus::Draft),
            'closed' => $query->where(fn (Builder $query) => $query->where('status', JobStatus::Closed)
                ->orWhere(fn (Builder $query) => $query->where('status', JobStatus::Published)->where('closes_at', '<=', now()))),
            'rejected' => $query->where('status', JobStatus::Rejected),
            'featured' => $query->where('is_featured', true)->where(fn (Builder $query) => $query->whereNull('featured_until')->orWhere('featured_until', '>', now())),
            default => $query,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(JobListing $job): array
    {
        return [
            'job' => $job,
            'companies' => Company::where('is_active', true)->orWhere('id', $job->company_id)->orderBy('name')->get(),
            'categories' => JobCategory::orderBy('sort_order')->get(),
            'latestReview' => $job->exists ? $job->approvalRequests->first() : null,
        ];
    }
}
