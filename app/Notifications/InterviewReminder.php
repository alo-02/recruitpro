<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewReminder extends Notification
{
    public function __construct(public Interview $interview) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->interview->application?->jobPosting?->title ?? 'your interview';

        $mail = (new MailMessage)
            ->subject('Interview reminder: '.$jobTitle)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('This is a reminder that you have an interview for '.$jobTitle.'.')
            ->line('When: '.$this->interview->scheduled_at?->format('l, M d, Y g:i A'))
            ->line('Mode: '.ucfirst((string) $this->interview->mode));

        if ($this->interview->location_or_link) {
            $mail->line('Location / meeting link: '.$this->interview->location_or_link);
        }

        return $mail
            ->action('Open interviews', url('/interviews'))
            ->line('We look forward to speaking with you.');
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
