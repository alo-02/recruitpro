<?php

namespace App\Services;

use App\Models\Application;
use App\Models\JobPosting;
use App\Models\Profile;
use App\Models\User;

class AtsCvChecker
{
    public function __construct(
        protected ResumeTextExtractor $extractor,
        protected GeminiCvMatcher $gemini,
    ) {}

    /**
     * @return array{
     *     score:int,
     *     matched:list<string>,
     *     missing:list<string>,
     *     corpus:string,
     *     summary:?string,
     *     source:string
     * }
     */
    public function evaluate(User $candidate, JobPosting $job, ?Profile $profile = null): array
    {
        $profile ??= $candidate->profile;
        $corpus = $this->buildCorpus($candidate, $profile);
        $jobText = $this->gemini->jobDescriptionFrom($job);

        $ai = $this->gemini->match($corpus, $jobText);
        if ($ai) {
            return [
                'score' => $ai['match_score'],
                'matched' => $ai['matched_skills'],
                'missing' => $ai['missing_skills'],
                'corpus' => $corpus,
                'summary' => $ai['summary'],
                'source' => 'gemini',
            ];
        }

        return $this->keywordEvaluate($job, $corpus);
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
            'ai_summary' => $result['summary'],
            'ai_match_source' => $result['source'],
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

    /**
     * @return array{
     *     score:int,
     *     matched:list<string>,
     *     missing:list<string>,
     *     corpus:string,
     *     summary:?string,
     *     source:string
     * }
     */
    protected function keywordEvaluate(JobPosting $job, string $corpus): array
    {
        $keywords = $this->keywordsForJob($job);

        if ($keywords === []) {
            return [
                'score' => 0,
                'matched' => [],
                'missing' => [],
                'corpus' => $corpus,
                'summary' => 'No job keywords were set, so a match score could not be calculated.',
                'source' => 'fallback',
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
            'summary' => $score >= 70
                ? "Keyword screening scored {$score}%. Core listed skills appear in the CV/profile."
                : "Keyword screening scored {$score}%. Several listed requirements were not found in the CV/profile.",
            'source' => 'fallback',
        ];
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

        return (bool) preg_match('/(^|[^a-z0-9.+#])'.preg_quote($needle, '/').'([^a-z0-9.+#]|$)/u', $haystack);
    }
}
