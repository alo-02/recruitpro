<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendDeadlineReminders extends Command
{
    protected $signature = 'recruitpro:deadline-reminders';

    protected $description = 'Remind students about upcoming job deadlines and interviews';

    public function handle(): int
    {
        $jobs = JobPosting::query()
            ->active()
            ->whereDate('deadline', now()->addDay()->toDateString())
            ->with(['applications.applicant'])
            ->get();

        $count = 0;
        foreach ($jobs as $job) {
            foreach ($job->applications as $application) {
                if ($application->applicant) {
                    $application->applicant->notify(new \App\Notifications\DeadlineReminder($job));
                    $count++;
                }
            }
        }

        $interviews = Interview::query()
            ->whereDate('scheduled_at', now()->addDay()->toDateString())
            ->with(['candidate', 'application.jobPosting'])
            ->get();

        foreach ($interviews as $interview) {
            if ($interview->candidate) {
                $interview->candidate->notify(new \App\Notifications\InterviewReminder($interview));
                $count++;
            }
        }

        $this->info("Sent {$count} reminder notifications.");

        return self::SUCCESS;
    }
}
