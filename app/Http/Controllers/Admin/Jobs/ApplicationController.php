<?php

namespace App\Http\Controllers\Admin\Jobs;

use App\Enums\ApplicationStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Applications received through on-site applying. CVs are downloaded through the admin panel only.
 */
class ApplicationController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(ApplicationStatus::class)],
            'job' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        return view('admin.jobs.applications.index', [
            'applications' => JobApplication::query()
                ->with(['jobListing.company'])
                ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
                ->when($filters['job'] ?? null, fn ($query, int $job) => $query->where('job_listing_id', $job))
                ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                    ->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'filters' => $filters,
            'filteredJob' => isset($filters['job']) ? JobListing::with('company')->find($filters['job']) : null,
            'statusCounts' => JobApplication::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(JobApplication $application): View
    {
        return view('admin.jobs.applications.show', ['application' => $application->load(['jobListing.company', 'user', 'reviewer'])]);
    }

    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);
        $data = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $application->status;
        $application->update([...$data, 'reviewed_by' => $request->user()->id]);
        $this->auditLogger->log('jobs.application.updated', $application, ['status' => $oldStatus->value], ['status' => $application->status->value]);

        return back()->with('status', 'Application updated.');
    }

    public function cv(JobApplication $application): StreamedResponse
    {
        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);
        $this->auditLogger->log('jobs.application.cv_downloaded', $application);

        return Storage::disk('local')->download($application->cv_path, $application->cv_original_name ?? 'cv.pdf');
    }
}
