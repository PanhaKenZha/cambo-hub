<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ApplyMethod;
use App\Models\JobListing;
use App\Services\PlatformSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public job page and on-site applications. Developer 2 may restyle the views.
 */
class JobController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $job = JobListing::live()->with(['company', 'category'])->where('slug', $slug)->firstOrFail();
        $job->increment('views_count');

        return view('jobs.show', [
            'job' => $job,
            'locale' => app()->getLocale(),
            'hasApplied' => $request->user() !== null && $job->applications()->where('user_id', $request->user()->id)->exists(),
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
