<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Approvable;
use App\Enums\ApprovalStatus;
use App\Enums\PlatformModule;
use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvals) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ApprovalRequest::class);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([...array_column(ApprovalStatus::cases(), 'value'), 'all'])],
            'module' => ['nullable', Rule::enum(PlatformModule::class)],
            'assigned' => ['nullable', Rule::in(['me', 'unassigned'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $status = $filters['status'] ?? ApprovalStatus::Pending->value;
        $visibleModules = PlatformModule::visibleTo($request->user());

        $baseQuery = ApprovalRequest::query()->whereIn('module', $visibleModules);
        $statusCounts = (clone $baseQuery)->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $approvalRequests = $baseQuery
            ->with(['submitter', 'assignee'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($filters['module'] ?? null, fn ($query, string $module) => $query->where('module', $module))
            ->when(($filters['assigned'] ?? null) === 'me', fn ($query) => $query->where('assigned_to', $request->user()->id))
            ->when(($filters['assigned'] ?? null) === 'unassigned', fn ($query) => $query->whereNull('assigned_to'))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy($status === ApprovalStatus::Pending->value ? 'created_at' : 'updated_at', $status === ApprovalStatus::Pending->value ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.approvals.index', [
            'approvalRequests' => $approvalRequests,
            'statusCounts' => $statusCounts,
            'activeStatus' => $status,
            'filters' => $filters,
            'modules' => $visibleModules,
        ]);
    }

    public function show(ApprovalRequest $approvalRequest): View
    {
        Gate::authorize('view', $approvalRequest);
        $approvalRequest->load(['submitter', 'assignee', 'reviewer', 'events.actor', 'appeals.user', 'appeals.decider']);

        return view('admin.approvals.show', [
            'approvalRequest' => $approvalRequest,
            'reviewers' => Gate::allows('review', $approvalRequest) ? $this->approvals->reviewersFor($approvalRequest) : collect(),
            'previewUrl' => $approvalRequest->approvable instanceof Approvable ? $approvalRequest->approvable->approvalPreviewUrl() : null,
        ]);
    }

    public function assign(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        Gate::authorize('review', $approvalRequest);
        $data = $request->validate(['assignee' => ['required', 'integer', Rule::exists('users', 'id')]]);

        $this->approvals->assign($approvalRequest, User::findOrFail($data['assignee']), $request->user());

        return back()->with('status', 'Reviewer assigned.');
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        Gate::authorize('review', $approvalRequest);
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        $this->approvals->approve($approvalRequest, $request->user(), $data['note'] ?? null);

        return redirect()->route('admin.approvals.index')->with('status', "\"{$approvalRequest->title}\" approved.");
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        Gate::authorize('review', $approvalRequest);
        $data = $request->validateWithBag('rejection', ['reason' => ['required', 'string', 'min:5', 'max:1000']]);

        $this->approvals->reject($approvalRequest, $request->user(), $data['reason']);

        return redirect()->route('admin.approvals.index')->with('status', "\"{$approvalRequest->title}\" rejected.");
    }
}
