<?php

namespace App\Notifications;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the submitter their content was approved or rejected.
 */
class ApprovalDecided extends Notification implements ShouldQueue
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
        $request = $this->approvalRequest;

        if ($request->status === ApprovalStatus::Approved) {
            return (new MailMessage)
                ->subject("Approved: {$request->title}")
                ->line("Good news — your submission \"{$request->title}\" has been approved and is now live.");
        }

        return (new MailMessage)
            ->subject("Not approved: {$request->title}")
            ->line("Your submission \"{$request->title}\" was not approved.")
            ->line('Reason: '.$request->rejection_reason)
            ->line('You can update it and submit again, or appeal this decision if you believe it was a mistake.');
    }
}
