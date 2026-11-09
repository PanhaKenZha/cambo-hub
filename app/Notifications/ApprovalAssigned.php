<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a reviewer that a submission was assigned to them.
 */
class ApprovalAssigned extends Notification implements ShouldQueue
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
            ->subject("Review assigned: {$this->approvalRequest->title}")
            ->line("You have been asked to review \"{$this->approvalRequest->title}\" ({$this->approvalRequest->module->label()}).")
            ->action('Open review', route('admin.approvals.show', $this->approvalRequest));
    }
}
