<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserViolation;
use App\Services\ModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserViolationController extends Controller
{
    public function __construct(private ModerationService $moderation) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', UserViolation::class);
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(ViolationStatus::class)],
            'type' => ['nullable', Rule::enum(ViolationType::class)],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $violations = UserViolation::query()
            ->with(['user', 'issuer'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->whereHas('user', fn ($query) => $query
                ->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.violations.index', [
            'violations' => $violations,
            'filters' => $filters,
            'activeCount' => UserViolation::where('status', ViolationStatus::Active)->count(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', UserViolation::class);

        return view('admin.violations.create', ['email' => $request->query('email')]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', UserViolation::class);
        $data = $request->validate([
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'type' => ['required', Rule::enum(ViolationType::class)],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'suspension_days' => ['nullable', 'required_if:type,'.ViolationType::Suspension->value, 'integer', 'min:1', 'max:365'],
        ], ['email.exists' => 'No user has this email address.']);

        $user = User::where('email', $data['email'])->firstOrFail();
        $violation = $this->moderation->issueViolation(
            $user,
            $request->user(),
            ViolationType::from($data['type']),
            $data['reason'],
            isset($data['suspension_days']) ? (int) $data['suspension_days'] : null,
        );

        return redirect()->route('admin.violations.index')->with('status', "{$violation->type->label()} issued to {$user->name}.");
    }

    public function overturn(Request $request, UserViolation $violation): RedirectResponse
    {
        Gate::authorize('overturn', $violation);
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        $this->moderation->overturnViolation($violation, $request->user(), $data['note'] ?? null);

        return back()->with('status', "{$violation->type->label()} for {$violation->user->name} overturned.");
    }
}
