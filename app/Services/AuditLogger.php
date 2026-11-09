<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Writes append-only audit records for administrative actions.
 */
class AuditLogger
{
    /**
     * Attribute names that must never be written to the audit log.
     *
     * @var list<string>
     */
    private const array REDACTED_ATTRIBUTES = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    public function __construct(private Request $request) {}

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(string $action, ?Model $subject = null, array $oldValues = [], array $newValues = [], ?User $actor = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => ($actor ?? $this->request->user())?->getKey(),
            'action' => $action,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'old_values' => $this->redact($oldValues),
            'new_values' => $this->redact($newValues),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>|null
     */
    private function redact(array $values): ?array
    {
        if ($values === []) {
            return null;
        }

        return array_diff_key($values, array_flip(self::REDACTED_ATTRIBUTES));
    }
}
