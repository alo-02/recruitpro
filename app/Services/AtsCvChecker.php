<?php

namespace App\Services;

use App\Models\Application;
use App\Models\JobPosting;
use App\Models\Profile;
use App\Models\User;

class AtsCvChecker
{
    public function __construct(
        protected ResumeTextExtractor $extractor = new ResumeTextExtractor
    ) {}

    /**
     * @param  list<string>  $keywords
     * @return array{score:int, matched:list<string>, missing:list<string>, corpus:string}
     */
    public function evaluate(User $candidate, JobPosting $job, ?Profile $profile = null): array
    {
        $profile ??= $candidate->profile;
        $keywords = $this->keywordsForJob($job);

        $corpus = $this->buildCorpus($candidate, $profile);

        if ($keywords === []) {
            return [
                'score' => 0,
                'matched' => [],
                'missing' => [],
                'corpus' => $corpus,
            ];
        }

        $haystack = mb_strtolower($corpus);
        $matched = [];
        $missing = [];

        foreach ($keywords as $keyword) {
            $needle = mb_strtolower(trim($keyword));
            if ($needle === '') {
                continue;
            }

            if ($this->containsKeyword($haystack, $needle)) {
                $matched[] = $keyword;
            } else {
                $missing[] = $keyword;
            }
        }

        $total = count($matched) + count($missing);
        $score = $total > 0 ? (int) round((count($matched) / $total) * 100) : 0;

        return [
            'score' => $score,
            'matched' => $matched,
            'missing' => $missing,
            'corpus' => $corpus,
        ];
    }

    public function applyToApplication(Application $application, bool $refreshResumeText = true): Application
    {
        $application->loadMissing(['applicant.profile', 'jobPosting']);
        $profile = $application->applicant->profile;

        if ($refreshResumeText && $profile?->resume_path) {
            $text = $this->extractor->fromStoragePath($profile->resume_path);
            if ($text !== '') {
                $profile->resume_text = $text;
                $profile->save();
            }
        }

        $result = $this->evaluate($application->applicant, $application->jobPosting, $profile);

        $application->update([
            'match_score' => $result['score'],
            'ats_matched_keywords' => $result['matched'],
            'ats_missing_keywords' => $result['missing'],
            'ats_checked_at' => now(),
        ]);

        return $application->fresh();
    }

    /**
     * @return list<string>
     */
    public function keywordsForJob(JobPosting $job): array
    {
        $keywords = $job->ats_keywords ?: ($job->skills ?: []);

        return JobPosting::parseKeywordList($keywords);
    }

    protected function buildCorpus(User $candidate, ?Profile $profile): string
    {
        $parts = [
            $candidate->name,
            $candidate->email,
            $profile?->headline,
            $profile?->bio,
            $profile?->location,
            implode(' ', $profile?->skills ?? []),
            collect($profile?->educationEntries() ?? [])->flatten()->implode(' '),
            collect($profile?->experienceEntries() ?? [])->flatten()->implode(' '),
            $profile?->resume_text,
        ];

        return trim(implode("\n", array_filter($parts)));
    }

    protected function containsKeyword(string $haystack, string $needle): bool
    {
        if (str_contains($haystack, $needle)) {
            return true;
        }

        // Word-boundary-ish match for short tokens
        return (bool) preg_match('/(^|[^a-z0-9.+#])'.preg_quote($needle, '/').'([^a-z0-9.+#]|$)/u', $haystack);
    }
}
