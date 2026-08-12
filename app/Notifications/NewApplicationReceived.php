<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = $this->application;

        return (new MailMessage)
            ->subject('New application: '.$app->jobPosting->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($app->applicant->name.' applied for '.$app->jobPosting->title.'.')
            ->action('Review Application', url('/applications/'.$app->id))
            ->line('Respond promptly to improve candidate experience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_application',
            'application_id' => $this->application->id,
            'candidate' => $this->application->applicant->name,
            'job_title' => $this->application->jobPosting->title,
            'message' => 'New application from '.$this->application->applicant->name,
        ];
    }
}
