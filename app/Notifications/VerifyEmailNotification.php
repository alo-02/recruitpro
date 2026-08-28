<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $code = Cache::get('email-verify-code:'.$notifiable->getKey())
            ?: static::issueCode($notifiable);
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your RecruitPro email')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Please verify your email address to activate your RecruitPro account.')
            ->action('Verify Email Address', $url)
            ->line('If the button does not open (Gmail sometimes wraps long links), enter this 6-digit code on the verification page:')
            ->line($code)
            ->line('The link and code expire in 60 minutes. If you did not create an account, you can ignore this email.');
    }

    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public static function issueCode(object $notifiable): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            'email-verify-code:'.$notifiable->getKey(),
            $code,
            now()->addMinutes(60)
        );

        return $code;
    }
}
