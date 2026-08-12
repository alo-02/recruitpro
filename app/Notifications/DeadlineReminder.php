<?php

namespace App\Notifications;

use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobPosting $job) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Deadline tomorrow: '.$this->job->title)
            ->line('The application deadline for '.$this->job->title.' is tomorrow.')
            ->action('View job', route('jobs.show', $this->job));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'deadline_reminder',
            'job_id' => $this->job->id,
            'title' => $this->job->title,
        ];
    }
}
