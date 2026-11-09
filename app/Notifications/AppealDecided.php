<?php

namespace App\Notifications;

use App\Enums\AppealStatus;
use App\Models\ModerationAppeal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a user the outcome of their appeal.
 */
class AppealDecided extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ModerationAppeal $appeal) {}

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
        $wasOverturned = $this->appeal->status === AppealStatus::Overturned;

        return (new MailMessage)
            ->subject($wasOverturned ? 'Your appeal was successful' : 'Your appeal was reviewed')
            ->line($wasOverturned
                ? 'We reviewed your appeal and reversed the original decision.'
                : 'We reviewed your appeal and the original decision stands.')
            ->lineIf(filled($this->appeal->decision_note), 'Moderator note: '.$this->appeal->decision_note);
    }
}
