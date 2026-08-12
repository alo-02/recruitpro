<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New message from '.$this->message->sender->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->message->sender->name.' sent you a message:')
            ->line('"'.Str::limit($this->message->body, 120).'"')
            ->action('Open Messages', url('/messages'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'from' => $this->message->sender->name,
            'message' => Str::limit($this->message->body, 100),
        ];
    }
}
