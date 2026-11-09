<?php

namespace Tests\Fixtures;

use App\Contracts\Approvable;
use App\Contracts\Reportable;
use App\Enums\PlatformModule;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal content model used to test the approval and moderation engine
 * before real content modules exist.
 */
class TestArticle extends Model implements Approvable, Reportable
{
    protected $table = 'test_articles';

    protected $guarded = [];

    public static function createTable(): void
    {
        Schema::create('test_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvalTitle(): string
    {
        return $this->title;
    }

    public function approvalModule(): PlatformModule
    {
        return PlatformModule::News;
    }

    public function approvalPreviewUrl(): ?string
    {
        return null;
    }

    public function markApproved(): void
    {
        $this->update(['status' => 'published']);
    }

    public function markRejected(string $reason): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function moderationTitle(): string
    {
        return $this->title;
    }

    public function moderationOwner(): ?User
    {
        return $this->author;
    }

    public function moderationModule(): ?PlatformModule
    {
        return PlatformModule::News;
    }

    public function takeDown(): void
    {
        $this->update(['status' => 'removed']);
    }
}
