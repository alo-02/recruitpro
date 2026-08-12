<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Interview $interview) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Interview scheduled')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An interview has been scheduled for '.$this->interview->application->jobPosting->title.'.')
            ->line('When: '.$this->interview->scheduled_at->format('M d, Y g:i A'))
            ->line('Mode: '.ucfirst($this->interview->mode))
            ->when($this->interview->location_or_link, fn ($m) => $m->line('Location / Link: '.$this->interview->location_or_link))
            ->action('View Interviews', url('/interviews'))
            ->line('Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'interview_scheduled',
            'interview_id' => $this->interview->id,
            'scheduled_at' => $this->interview->scheduled_at->toIso8601String(),
            'message' => 'Interview scheduled for '.$this->interview->scheduled_at->format('M d, Y g:i A'),
        ];
    }
}
