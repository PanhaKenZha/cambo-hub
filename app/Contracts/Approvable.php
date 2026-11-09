<?php

namespace App\Contracts;

use App\Enums\PlatformModule;

/**
 * Content that must be approved by a moderator before it is published
 * (news submissions, job posts, business registrations, ...).
 *
 * Submit with ApprovalService::submit(). The model is told the outcome
 * through markApproved() / markRejected().
 */
interface Approvable
{
    /**
     * Short title shown in the approval queue.
     */
    public function approvalTitle(): string;

    /**
     * Module the content belongs to. Reviewers need that module's "manage" permission.
     */
    public function approvalModule(): PlatformModule;

    /**
     * Where a reviewer can preview the content, if anywhere.
     */
    public function approvalPreviewUrl(): ?string;

    /**
     * Called once the content is approved, e.g. to publish it.
     */
    public function markApproved(): void;

    /**
     * Called once the content is rejected.
     */
    public function markRejected(string $reason): void;
}
