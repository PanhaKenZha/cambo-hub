<?php

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\User;
use App\Notifications\SuspiciousLoginDetected;
use Illuminate\Http\Request;

/**
 * Records login attempts and flags logins from previously unseen IP addresses.
 */
class LoginRecorder
{
    public function __construct(private Request $request, private AuditLogger $auditLogger, private PlatformSettings $settings) {}

    public function recordSuccess(User $user): LoginHistory
    {
        $ipAddress = $this->request->ip();
        $hasPreviousLogins = $user->loginHistories()->where('successful', true)->exists();
        $isKnownIp = $user->loginHistories()->where('successful', true)->where('ip_address', $ipAddress)->exists();
        $isSuspicious = $hasPreviousLogins && ! $isKnownIp;

        $loginHistory = $user->loginHistories()->create([
            'email' => $user->email,
            'ip_address' => $ipAddress,
            'user_agent' => $this->request->userAgent(),
            'successful' => true,
            'is_suspicious' => $isSuspicious,
        ]);

        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $ipAddress])->saveQuietly();

        if ($isSuspicious) {
            if ($this->settings->get('notifications.login_alerts')) {
                $user->notify(new SuspiciousLoginDetected($loginHistory));
            }
            $this->auditLogger->log('auth.suspicious_login', $user, newValues: ['ip_address' => $ipAddress], actor: $user);
        }

        return $loginHistory;
    }

    public function recordFailure(string $email, string $reason, ?User $user = null): LoginHistory
    {
        return LoginHistory::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'successful' => false,
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Count recent failed attempts for an email, used to lock accounts under attack.
     */
    public function recentFailureCount(string $email, int $minutes): int
    {
        return LoginHistory::query()
            ->where('email', $email)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}
