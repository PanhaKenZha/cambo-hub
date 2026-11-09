<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells reviewers that new content is waiting for approval.
 */
class SubmissionAwaitingReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ApprovalRequest $approvalRequest) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Waiting for approval: {$this->approvalRequest->title}")
            ->line("A new {$this->approvalRequest->module->label()} submission is waiting for review.")
            ->action('Review now', route('admin.approvals.show', $this->approvalRequest));
    }
}
