<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use App\Models\Concerns\HasTranslations;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A company on Cambo Hub: an employer in Jobs, and later a listing in the Business directory.
 */
#[Fillable(['owner_id', 'name', 'slug', 'description', 'logo_path', 'website', 'email', 'phone', 'city', 'address', 'verification_status', 'verification_document_path', 'verification_note', 'verified_by', 'verified_at', 'is_active'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasTranslations;

    /**
     * The account that manages the company and posts its jobs.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * @return HasMany<JobListing, $this>
     */
    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === VerificationStatus::Verified;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'description' => 'array',
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
