<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', Rule::exists('roles', 'name')],
            'status' => ['nullable', Rule::in(['active', 'suspended'])],
        ]);

        $admins = User::query()
            ->when(
                $filters['role'] ?? null,
                fn ($query, string $roleName) => $query->role($roleName),
                fn ($query) => $query->whereHas('roles'),
            )
            ->with('roles')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"),
            ))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->whereNull('suspended_at'))
            ->when(($filters['status'] ?? null) === 'suspended', fn ($query) => $query->whereNotNull('suspended_at'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['admins' => $admins, 'roles' => $this->allRoles(), 'filters' => $filters]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create', ['roles' => $this->assignableRoles($request->user())]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'role' => ['required', Rule::in($this->assignableRoles($request->user())->pluck('name'))],
        ]);

        $admin = DB::transaction(function () use ($data): User {
            $admin = new User;
            $admin->forceFill([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Str::password(40),
                'email_verified_at' => now(),
            ])->save();
            $admin->assignRole($data['role']);

            $this->auditLogger->log('admin.created', $admin, newValues: $data);

            return $admin;
        });

        Password::sendResetLink(['email' => $admin->email]);

        return redirect()->route('admin.users.show', $admin)
            ->with('status', 'Administrator created. A link to set their password has been emailed to them.');
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        return view('admin.users.show', [
            'admin' => $user->load('roles'),
            'loginHistories' => $user->loginHistories()->latest('id')->limit(25)->get(),
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.users.edit', ['admin' => $user, 'roles' => $this->assignableRoles($request->user())]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in($this->assignableRoles($request->user())->pluck('name'))],
        ]);
        $oldValues = ['name' => $user->name, 'email' => $user->email, 'role' => $user->primaryRole()?->name];

        if ($request->user()->is($user) && $data['role'] !== $oldValues['role']) {
            return back()->withErrors(['role' => 'You cannot change your own role.'])->withInput();
        }

        DB::transaction(function () use ($user, $data, $oldValues): void {
            $user->forceFill(['name' => $data['name'], 'email' => $data['email']])->save();
            $user->syncRoles([$data['role']]);

            $this->auditLogger->log('admin.updated', $user, $oldValues, $data);
        });

        return redirect()->route('admin.users.show', $user)->with('status', 'Administrator updated.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('suspend', $user);
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $user->forceFill(['suspended_at' => now(), 'suspension_reason' => $data['reason']])->save();
        $this->endSessionsFor($user);
        $this->auditLogger->log('admin.suspended', $user, newValues: $data);

        return back()->with('status', 'Administrator suspended and signed out.');
    }

    public function unsuspend(User $user): RedirectResponse
    {
        Gate::authorize('suspend', $user);

        $oldValues = ['reason' => $user->suspension_reason];
        $user->forceFill(['suspended_at' => null, 'suspension_reason' => null])->save();
        $this->auditLogger->log('admin.unsuspended', $user, $oldValues);

        return back()->with('status', 'Administrator reactivated.');
    }

    public function resetTwoFactor(User $user): RedirectResponse
    {
        Gate::authorize('suspend', $user);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $this->endSessionsFor($user);
        $this->auditLogger->log('admin.two_factor_reset', $user);

        return back()->with('status', 'Two-factor authentication reset. They must set it up again at their next sign-in.');
    }

    /**
     * @return Collection<int, Role>
     */
    private function allRoles(): Collection
    {
        return Role::sortForDisplay(Role::all());
    }

    /**
     * @return Collection<int, Role>
     */
    private function assignableRoles(User $actor): Collection
    {
        return $this->allRoles()->filter(fn (Role $role): bool => $actor->can('assignRole', [User::class, $role]))->values();
    }

    private function endSessionsFor(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }
}
