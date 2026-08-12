<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $job = $this->application->jobPosting;

        return (new MailMessage)
            ->subject('Application status updated: '.$job->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your application for '.$job->title.' at '.$job->company->name.' has been updated.')
            ->line('New status: '.$this->application->status->label())
            ->action('View Applications', url('/applications'))
            ->line('Thank you for using RecruitPro.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_status',
            'application_id' => $this->application->id,
            'job_title' => $this->application->jobPosting->title,
            'status' => $this->application->status->value,
            'message' => 'Application status updated to '.$this->application->status->label(),
        ];
    }
}
