<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewReminder extends Notification implements ShouldQueue
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
            ->subject('Interview tomorrow')
            ->line('You have an interview scheduled for '.$this->interview->scheduled_at->format('M d, Y g:i A'))
            ->line('Meeting: '.($this->interview->meeting_url ?: $this->interview->location_or_link))
            ->action('Open interviews', route('interviews.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'interview_reminder',
            'interview_id' => $this->interview->id,
            'scheduled_at' => $this->interview->scheduled_at?->toIso8601String(),
        ];
    }
}
