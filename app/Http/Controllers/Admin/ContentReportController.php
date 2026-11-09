<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Reportable;
use App\Enums\PlatformModule;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\ViolationType;
use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use App\Services\ModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentReportController extends Controller
{
    public function __construct(private ModerationService $moderation) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ContentReport::class);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([...array_column(ReportStatus::cases(), 'value'), 'all'])],
            'reason' => ['nullable', Rule::enum(ReportReason::class)],
            'module' => ['nullable', Rule::enum(PlatformModule::class)],
        ]);
        $status = $filters['status'] ?? ReportStatus::Open->value;
        $visibleModules = PlatformModule::visibleTo($request->user());

        $baseQuery = ContentReport::query()->where(fn ($query) => $query->whereNull('module')->orWhereIn('module', $visibleModules));
        $statusCounts = (clone $baseQuery)->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $reports = $baseQuery
            ->with(['reporter', 'handler'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($filters['reason'] ?? null, fn ($query, string $reason) => $query->where('reason', $reason))
            ->when($filters['module'] ?? null, fn ($query, string $module) => $query->where('module', $module))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'statusCounts' => $statusCounts,
            'activeStatus' => $status,
            'filters' => $filters,
            'modules' => $visibleModules,
        ]);
    }

    public function show(ContentReport $report): View
    {
        Gate::authorize('view', $report);
        $report->load(['reporter', 'handler', 'violation']);

        $owner = $report->reportable instanceof Reportable ? $report->reportable->moderationOwner() : null;

        return view('admin.reports.show', [
            'report' => $report,
            'owner' => $owner,
            'ownerViolations' => $owner?->violations()->with('issuer')->latest('id')->limit(10)->get() ?? collect(),
            'otherReportCount' => ContentReport::query()
                ->where('reportable_type', $report->reportable_type)
                ->where('reportable_id', $report->reportable_id)
                ->whereKeyNot($report->id)
                ->count(),
        ]);
    }

    public function resolve(Request $request, ContentReport $report): RedirectResponse
    {
        Gate::authorize('handle', $report);
        $data = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
            'take_down' => ['boolean'],
            'violation_type' => ['nullable', Rule::enum(ViolationType::class)],
            'suspension_days' => ['nullable', 'required_if:violation_type,'.ViolationType::Suspension->value, 'integer', 'min:1', 'max:365'],
        ]);

        $this->moderation->resolveReport(
            $report,
            $request->user(),
            $data['note'],
            $request->boolean('take_down'),
            isset($data['violation_type']) ? ViolationType::from($data['violation_type']) : null,
            isset($data['suspension_days']) ? (int) $data['suspension_days'] : null,
        );

        return redirect()->route('admin.reports.index')->with('status', "Report #{$report->id} resolved.");
    }

    public function dismiss(Request $request, ContentReport $report): RedirectResponse
    {
        Gate::authorize('handle', $report);
        $data = $request->validateWithBag('dismissal', ['note' => ['required', 'string', 'max:1000']]);

        $this->moderation->dismissReport($report, $request->user(), $data['note']);

        return redirect()->route('admin.reports.index')->with('status', "Report #{$report->id} dismissed.");
    }
}
