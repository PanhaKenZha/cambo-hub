<?php

namespace App\Contracts;

use App\Enums\PlatformModule;
use App\Models\User;

/**
 * Content users can report to moderators (posts, listings, comments, profiles, ...).
 *
 * Report with ModerationService::report().
 */
interface Reportable
{
    /**
     * Short title shown in the reports queue.
     */
    public function moderationTitle(): string;

    /**
     * Who is responsible for the content, so a violation can be issued to them.
     */
    public function moderationOwner(): ?User;

    /**
     * Module the content belongs to, or null for platform-wide content such as profiles.
     */
    public function moderationModule(): ?PlatformModule;

    /**
     * Hide or remove the content after a moderator upholds a report.
     */
    public function takeDown(): void;
}
