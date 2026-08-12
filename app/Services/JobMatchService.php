<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Support\Collection;

class JobMatchService
{
    /**
     * @return Collection<int, array{job:JobPosting,score:int,reasons:list<string>,missing:list<string>}>
     */
    public function recommendFor(User $user, int $limit = 8): Collection
    {
        $profile = $user->profile;
        $skills = collect($profile?->skills ?? [])->map(fn ($s) => mb_strtolower(trim((string) $s)))->filter();
        $appliedIds = $user->applications()->pluck('job_posting_id');

        return JobPosting::query()
            ->with('company')
            ->active()
            ->when($appliedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $appliedIds))
            ->latest('published_at')
            ->limit(40)
            ->get()
            ->map(function (JobPosting $job) use ($skills, $profile) {
                $result = $this->explain($job, $skills->all(), $profile?->resume_text, $profile?->headline, $profile?->bio);

                return [
                    'job' => $job,
                    'score' => $result['score'],
                    'reasons' => $result['reasons'],
                    'missing' => $result['missing'],
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /**
     * @param  list<string>  $candidateSkills
     * @return array{score:int,reasons:list<string>,missing:list<string>}
     */
    public function explain(JobPosting $job, array $candidateSkills = [], ?string $resumeText = null, ?string $headline = null, ?string $bio = null): array
    {
        $keywords = collect($job->ats_keywords ?: $job->skills ?: [])
            ->map(fn ($k) => trim((string) $k))
            ->filter()
            ->values();

        if ($keywords->isEmpty()) {
            return ['score' => 40, 'reasons' => ['General open role'], 'missing' => []];
        }

        $haystack = mb_strtolower(implode(' ', array_filter([
            implode(' ', $candidateSkills),
            $resumeText,
            $headline,
            $bio,
        ])));

        $matched = [];
        $missing = [];
        foreach ($keywords as $keyword) {
            $needle = mb_strtolower($keyword);
            if ($needle !== '' && str_contains($haystack, $needle)) {
                $matched[] = $keyword;
            } else {
                $missing[] = $keyword;
            }
        }

        $total = max(1, count($matched) + count($missing));
        $score = (int) round((count($matched) / $total) * 100);

        $reasons = [];
        if ($matched !== []) {
            $reasons[] = 'Matched skills: '.implode(', ', array_slice($matched, 0, 5));
        }
        if ($job->category) {
            $reasons[] = 'Category: '.$job->category->label();
        }
        if ($job->location_type) {
            $reasons[] = 'Work mode: '.$job->location_type;
        }

        return [
            'score' => $score,
            'reasons' => $reasons,
            'missing' => $missing,
        ];
    }

    public function draftCoverLetter(User $user, JobPosting $job): string
    {
        $skills = implode(', ', array_slice($user->profile?->skills ?? [], 0, 6)) ?: 'relevant coursework and projects';
        $name = $user->name;
        $company = $job->company?->name ?? 'your company';

        return "Dear Hiring Manager at {$company},\n\n".
            "I am writing to apply for the {$job->title} role. My background includes {$skills}, which align with your requirements.\n\n".
            "I am motivated to contribute to {$company} and grow through this opportunity. I would welcome the chance to discuss how I can help your team.\n\n".
            "Sincerely,\n{$name}";
    }
}
