<?php

namespace App\Notifications;

use App\Models\UserViolation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a user they received a warning, strike, suspension or ban.
 */
class ViolationIssued extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public UserViolation $violation) {}

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
        $message = (new MailMessage)
            ->subject($this->violation->type->label().' on your '.config('app.name').' account')
            ->line('Your account received a '.mb_strtolower($this->violation->type->label()).' for breaking the platform rules.')
            ->line('Reason: '.$this->violation->reason);

        if ($this->violation->expires_at) {
            $message->line('Your access will be restored on '.$this->violation->expires_at->toDayDateTimeString().'.');
        }

        return $message->line('If you believe this is a mistake, you can appeal this decision.');
    }
}
