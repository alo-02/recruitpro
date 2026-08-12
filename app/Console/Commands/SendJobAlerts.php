<?php

namespace App\Console\Commands;

use App\Models\JobAlert;
use App\Models\JobPosting;
use App\Notifications\JobAlertDigest;
use Illuminate\Console\Command;

class SendJobAlerts extends Command
{
    protected $signature = 'recruitpro:send-job-alerts';

    protected $description = 'Send digest emails for active job alerts';

    public function handle(): int
    {
        $alerts = JobAlert::with('user')->where('is_active', true)->get();
        $sent = 0;

        foreach ($alerts as $alert) {
            $query = JobPosting::query()->with('company')->active()
                ->when($alert->last_sent_at, fn ($q) => $q->where('published_at', '>', $alert->last_sent_at))
                ->when(! $alert->last_sent_at, fn ($q) => $q->where('published_at', '>=', now()->subDay()));

            if ($alert->keywords) {
                $kw = $alert->keywords;
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'like', "%{$kw}%")
                        ->orWhere('description', 'like', "%{$kw}%");
                });
            }

            if ($alert->location) {
                $query->where('location', 'like', '%'.$alert->location.'%');
            }

            if ($alert->job_type) {
                $query->where('type', $alert->job_type);
            }

            $jobs = $query->latest('published_at')->take(10)->get();

            if ($jobs->isEmpty() || ! $alert->user) {
                continue;
            }

            $alert->user->notify(new JobAlertDigest($jobs));
            $alert->update(['last_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} job alert digests.");

        return self::SUCCESS;
    }
}
