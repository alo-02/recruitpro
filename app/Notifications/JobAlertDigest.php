<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class JobAlertDigest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Collection $jobs) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('New jobs matching your alerts')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We found '.$this->jobs->count().' new opportunities for you:');

        foreach ($this->jobs->take(5) as $job) {
            $mail->line('• '.$job->title.' at '.$job->company->name);
        }

        return $mail->action('Browse Jobs', url('/jobs'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_alert',
            'count' => $this->jobs->count(),
            'message' => $this->jobs->count().' new jobs match your alerts',
        ];
    }
}
