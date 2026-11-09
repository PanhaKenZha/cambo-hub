<?php

namespace App\Models;

use Database\Factories\LoginHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'email', 'ip_address', 'user_agent', 'successful', 'is_suspicious', 'failure_reason'])]
class LoginHistory extends Model
{
    /** @use HasFactory<LoginHistoryFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'is_suspicious' => 'boolean',
        ];
    }
}
