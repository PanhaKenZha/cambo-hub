<?php

namespace App\Models;

use App\Enums\AdminRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @return HasMany<LoginHistory, $this>
     */
    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * Roles are only given to administrators, so any role means admin access.
     */
    public function isAdmin(): bool
    {
        return $this->roles->isNotEmpty();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(AdminRole::SuperAdmin->value);
    }

    /**
     * The administrator's role. Each administrator has exactly one.
     */
    public function primaryRole(): ?Role
    {
        return $this->roles->first();
    }

    public function roleLabel(): ?string
    {
        return $this->primaryRole()?->label();
    }

    /**
     * The built-in role, or null for a custom role.
     */
    public function adminRole(): ?AdminRole
    {
        return $this->primaryRole()?->builtInRole();
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'suspended_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }
}
