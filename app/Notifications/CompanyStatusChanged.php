<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Company $company) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Company registration '.$this->company->status->label())
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your company "'.$this->company->name.'" is now: '.$this->company->status->label().'.');

        if ($this->company->rejection_reason) {
            $mail->line('Reason: '.$this->company->rejection_reason);
        }

        return $mail->action('Company Profile', url('/company/profile'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'company_status',
            'company_id' => $this->company->id,
            'status' => $this->company->status->value,
            'message' => 'Company status: '.$this->company->status->label(),
        ];
    }
}
