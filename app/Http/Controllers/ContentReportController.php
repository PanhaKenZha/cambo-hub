<?php

namespace App\Http\Controllers;

use App\Enums\ReportReason;
use App\Models\JobListing;
use App\Models\NewsArticle;
use App\Services\ModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Signed-in visitors report live content. Reports appear in Admin › Moderation › Reports.
 */
class ContentReportController extends Controller
{
    /**
     * Content that visitors can report, keyed by the type used in the URL.
     *
     * @var array<string, class-string<NewsArticle|JobListing>>
     */
    public const array REPORTABLE_TYPES = [
        'news' => NewsArticle::class,
        'job' => JobListing::class,
    ];

    public function store(Request $request, string $type, int $id, ModerationService $moderation): RedirectResponse
    {
        abort_unless(isset(self::REPORTABLE_TYPES[$type]), 404);
        $content = self::REPORTABLE_TYPES[$type]::live()->findOrFail($id);

        $data = $request->validateWithBag('report', [
            'reason' => ['required', Rule::enum(ReportReason::class)],
            'details' => ['nullable', 'required_if:reason,'.ReportReason::Other->value, 'string', 'max:1000'],
        ], ['details.required_if' => __('Please tell us what is wrong.')]);

        $moderation->report($content, $request->user(), ReportReason::from($data['reason']), $data['details'] ?? null);

        return back()->with('report_status', __('Thank you. Our team will review your report.'));
    }
}
