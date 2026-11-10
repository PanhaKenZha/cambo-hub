<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStatus;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Models\ApprovalRequest;
use App\Models\ModerationAppeal;
use App\Models\User;
use App\Models\UserViolation;
use App\Services\ModerationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Users see moderation decisions about them and appeal them.
 *
 * Suspended users cannot sign in, so after a correct password the sign-in form
 * remembers them for a short time and sends them to the suspended page instead.
 */
class AppealController extends Controller
{
    public const string SUSPENDED_SESSION_KEY = 'moderation.suspended_user';

    private const int SUSPENDED_SESSION_MINUTES = 30;

    public function __construct(private ModerationService $moderation) {}

    /**
     * Account standing: active violations, rejected submissions and past appeals.
     */
    public function standing(Request $request): View
    {
        return view('account.standing', $this->standingData($request->user()));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->fileAppeal($request, $request->user());

        return back()->with('status', __('Your appeal was sent. A different moderator will review it.'));
    }

    public function suspended(Request $request): View|RedirectResponse
    {
        $user = $this->suspendedUser($request);

        if ($user === null) {
            return redirect()->route('login');
        }

        return view('account.suspended', [...$this->standingData($user), 'user' => $user]);
    }

    public function storeSuspended(Request $request): RedirectResponse
    {
        $user = $this->suspendedUser($request);

        if ($user === null) {
            return redirect()->route('login');
        }

        $this->fileAppeal($request, $user);

        return back()->with('status', __('Your appeal was sent. We will email you when it has been reviewed.'));
    }

    /**
     * Remember a suspended user who entered the correct password, so they can read the reason and appeal.
     */
    public static function rememberSuspendedUser(Request $request, User $user): void
    {
        $request->session()->put(self::SUSPENDED_SESSION_KEY, ['id' => $user->id, 'until' => now()->addMinutes(self::SUSPENDED_SESSION_MINUTES)->timestamp]);
    }

    private function suspendedUser(Request $request): ?User
    {
        $remembered = $request->session()->get(self::SUSPENDED_SESSION_KEY);

        if (! is_array($remembered) || ($remembered['until'] ?? 0) < now()->timestamp) {
            $request->session()->forget(self::SUSPENDED_SESSION_KEY);

            return null;
        }

        $user = User::find($remembered['id'] ?? null);

        return $user?->isSuspended() ? $user : null;
    }

    private function fileAppeal(Request $request, User $user): void
    {
        $data = $request->validateWithBag('appeal', [
            'subject' => ['required', Rule::in(['violation', 'submission'])],
            'id' => ['required', 'integer'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ], ['message.min' => __('Please explain in at least 20 characters why the decision is wrong.')]);

        $subject = $data['subject'] === 'violation'
            ? UserViolation::where('user_id', $user->id)->findOrFail($data['id'])
            : ApprovalRequest::where('submitted_by', $user->id)->findOrFail($data['id']);

        $this->moderation->fileAppeal($subject, $user, $data['message']);
    }

    /**
     * @return array{violations: Collection<int, UserViolation>, rejectedSubmissions: Collection<int, ApprovalRequest>, appeals: Collection<int, ModerationAppeal>, blockingTypes: list<ViolationType>}
     */
    private function standingData(User $user): array
    {
        return [
            'violations' => $user->violations()->with('appeals')->where('status', ViolationStatus::Active)->latest()->get(),
            'rejectedSubmissions' => ApprovalRequest::with('appeals')->where('submitted_by', $user->id)->where('status', ApprovalStatus::Rejected)->latest('reviewed_at')->get(),
            'appeals' => ModerationAppeal::with('appealable')->where('user_id', $user->id)->latest()->limit(10)->get(),
            'blockingTypes' => [ViolationType::Suspension, ViolationType::Ban],
        ];
    }
}
