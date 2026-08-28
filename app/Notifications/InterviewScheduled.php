<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification
{
    public function __construct(public Interview $interview) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->interview->application?->jobPosting?->title ?? 'your application';
        $when = $this->interview->scheduled_at?->format('l, M d, Y g:i A') ?? 'TBD';
        $mode = ucfirst((string) $this->interview->mode);

        $mail = (new MailMessage)
            ->subject('Interview scheduled: '.$jobTitle)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An interview has been scheduled for '.$jobTitle.'.')
            ->line('When: '.$when)
            ->line('Mode: '.$mode);

        if ($this->interview->location_or_link) {
            $mail->line('Location / meeting link: '.$this->interview->location_or_link);
        }

        if ($this->interview->notes) {
            $mail->line('Notes: '.$this->interview->notes);
        }

        return $mail
            ->action('View Interviews', url('/interviews'))
            ->line('Please reply if you need to reschedule. Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'interview_scheduled',
            'interview_id' => $this->interview->id,
            'scheduled_at' => $this->interview->scheduled_at?->toIso8601String(),
            'message' => 'Interview scheduled for '.$this->interview->scheduled_at?->format('M d, Y g:i A'),
        ];
    }
}
