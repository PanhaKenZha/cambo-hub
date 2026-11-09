<?php

namespace App\Notifications;

use App\Models\LoginHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspiciousLoginDetected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LoginHistory $loginHistory) {}

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
            ->subject('New sign-in to your '.config('app.name').' account')
            ->line('Your account was just signed in to from a new IP address.')
            ->line('IP address: '.$this->loginHistory->ip_address)
            ->line('Time: '.$this->loginHistory->created_at?->toDayDateTimeString())
            ->line('If this was not you, reset your password immediately and contact a Super Admin.')
            ->action('Reset password', route('password.request'));
    }
}
