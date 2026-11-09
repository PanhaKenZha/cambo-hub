<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppealStatus;
use App\Http\Controllers\Controller;
use App\Models\ModerationAppeal;
use App\Services\ModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppealController extends Controller
{
    public function __construct(private ModerationService $moderation) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ModerationAppeal::class);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([...array_column(AppealStatus::cases(), 'value'), 'all'])],
        ]);
        $status = $filters['status'] ?? AppealStatus::Pending->value;

        $appeals = ModerationAppeal::query()
            ->with(['user', 'decider', 'appealable'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderBy('created_at', $status === AppealStatus::Pending->value ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.appeals.index', [
            'appeals' => $appeals,
            'activeStatus' => $status,
            'statusCounts' => ModerationAppeal::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(ModerationAppeal $appeal): View
    {
        Gate::authorize('view', $appeal);
        $appeal->load(['user', 'decider', 'appealable']);

        return view('admin.appeals.show', ['appeal' => $appeal]);
    }

    public function decide(Request $request, ModerationAppeal $appeal): RedirectResponse
    {
        Gate::authorize('decide', $appeal);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['overturn', 'uphold'])],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $this->moderation->decideAppeal($appeal, $request->user(), $data['decision'] === 'overturn', $data['note']);

        return redirect()->route('admin.appeals.index')
            ->with('status', $data['decision'] === 'overturn' ? 'Appeal accepted and the decision reversed.' : 'Appeal declined. The original decision stands.');
    }
}
