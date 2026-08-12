<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your RecruitPro verification code')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your two-factor authentication code is:')
            ->line('**'.$this->code.'**')
            ->line('This code expires in 10 minutes.')
            ->line('If you did not attempt to log in, secure your account immediately.');
    }
}
