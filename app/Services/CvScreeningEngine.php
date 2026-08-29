<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\ScreeningResult;
use App\Models\ScreeningSession;
use App\Models\User;
use Illuminate\Support\Collection;

class CvScreeningEngine
{
    /**
     * @var array<string, list<string>>
     */
    protected array $aliases = [
        'javascript' => ['js', 'ecmascript'],
        'typescript' => ['ts'],
        'react' => ['react.js', 'reactjs'],
        'node.js' => ['nodejs', 'node'],
        'postgresql' => ['postgres', 'psql'],
        'c++' => ['cpp'],
        'c#' => ['csharp', 'c sharp'],
        'html' => ['html5'],
        'css' => ['css3'],
        'aws' => ['amazon web services'],
        'gcp' => ['google cloud'],
        'kubernetes' => ['k8s'],
        'machine learning' => ['ml'],
        'artificial intelligence' => ['ai'],
    ];

    public function __construct(
        protected ResumeTextExtractor $extractor,
        protected CandidateProfileExtractor $profileExtractor,
        protected JobRequirementAnalyzer $jobAnalyzer,
    ) {}

    public function run(JobPosting $job, User $recruiter, int $topN = 10): ScreeningSession
    {
        $topN = in_array($topN, [5, 10, 15], true) ? $topN : 10;
        $requirements = $this->jobAnalyzer->analyze($job);

        $session = ScreeningSession::create([
            'job_posting_id' => $job->id,
            'recruiter_id' => $recruiter->id,
            'top_n' => $topN,
            'job_requirements' => $requirements,
            'started_at' => now(),
        ]);

        $applications = $job->applications()
            ->with('applicant.profile')
            ->where('status', '!=', ApplicationStatus::Withdrawn)
            ->get();

        $evaluated = [];
        foreach ($applications as $application) {
            try {
                $evaluated[] = $this->scoreApplication($application, $job, $requirements);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        usort($evaluated, fn ($a, $b) => $b['overall_score'] <=> $a['overall_score']);

        foreach ($evaluated as $index => $row) {
            $rank = $index + 1;
            ScreeningResult::create([
                'screening_session_id' => $session->id,
                'application_id' => $row['application']->id,
                'candidate_id' => $row['application']->user_id,
                'rank' => $rank,
                'overall_score' => $row['overall_score'],
                'ai_recommended' => $rank <= $topN,
                'extracted_profile' => $row['extracted_profile'],
                'matched_points' => $row['matched_points'],
                'partial_matches' => $row['partial_matches'],
                'missing_points' => $row['missing_points'],
                'score_breakdown' => $row['score_breakdown'],
                'ai_explanation' => $row['ai_explanation'],
                'ai_recommendation' => $row['ai_recommendation'],
            ]);

            $row['application']->update([
                'match_score' => $row['overall_score'],
                'ats_matched_keywords' => array_column($row['matched_points'], 'label'),
                'ats_missing_keywords' => array_column($row['missing_points'], 'label'),
                'ats_checked_at' => now(),
            ]);
        }

        $session->update([
            'candidates_evaluated' => count($evaluated),
            'completed_at' => now(),
        ]);

        return $session->fresh(['results.candidate.profile', 'results.application', 'jobPosting.company']);
    }

    /**
     * @param  array<string, mixed>  $requirements
     * @return array<string, mixed>
     */
    public function scoreApplication(Application $application, JobPosting $job, array $requirements): array
    {
        $candidate = $application->applicant;
        $profile = $candidate->profile;
        $resumeText = '';

        if ($profile?->resume_path) {
            $extracted = $this->extractor->fromStoragePath($profile->resume_path);
            if ($extracted !== '') {
                $resumeText = $extracted;
                if ($profile->resume_text !== $extracted) {
                    $profile->resume_text = $extracted;
                    $profile->save();
                }
            }
        }

        $extractedProfile = $this->profileExtractor->extract($candidate, $profile, $resumeText);
        $haystack = mb_strtolower($extractedProfile['raw_text'] ?? '');

        $required = $this->compareList($requirements['required_skills'] ?? [], $haystack);
        $preferred = $this->compareList($requirements['preferred_skills'] ?? [], $haystack);

        $education = $this->compareEducation($requirements['education_level'] ?? null, $extractedProfile, $haystack);
        $experience = $this->compareExperience($requirements['experience'] ?? null, $extractedProfile, $haystack);
        $location = $this->compareLocation($job, $profile?->location, $haystack);

        $matched = array_merge($required['matched'], $preferred['matched'], $education['matched'], $experience['matched'], $location['matched']);
        $partial = array_merge($required['partial'], $preferred['partial'], $education['partial'], $experience['partial'], $location['partial']);
        $missing = array_merge($required['missing'], $preferred['missing'], $education['missing'], $experience['missing'], $location['missing']);

        $breakdown = [
            'required_skills' => $required['score'],
            'preferred_skills' => $preferred['score'],
            'education' => $education['score'],
            'experience' => $experience['score'],
            'location' => $location['score'],
        ];

        $overall = (int) round(
            $breakdown['required_skills'] * 0.40
            + $breakdown['preferred_skills'] * 0.15
            + $breakdown['education'] * 0.15
            + $breakdown['experience'] * 0.20
            + $breakdown['location'] * 0.10
        );

        $explanation = $this->explain($candidate->name, $overall, $matched, $partial, $missing);
        $recommendation = $this->recommend($overall, $matched, $missing);

        unset($extractedProfile['raw_text']);

        return [
            'application' => $application,
            'overall_score' => max(0, min(100, $overall)),
            'extracted_profile' => $extractedProfile,
            'matched_points' => $matched,
            'partial_matches' => $partial,
            'missing_points' => $missing,
            'score_breakdown' => $breakdown,
            'ai_explanation' => $explanation,
            'ai_recommendation' => $recommendation,
        ];
    }

    /**
     * @param  list<string>  $items
     * @return array{score:int,matched:list<array{label:string,detail:string}>,partial:list<array{label:string,detail:string}>,missing:list<array{label:string,detail:string}>}
     */
    protected function compareList(array $items, string $haystack): array
    {
        $matched = [];
        $partial = [];
        $missing = [];

        $clean = collect($items)->map(fn ($i) => trim((string) $i))->filter()->unique()->values();
        if ($clean->isEmpty()) {
            return ['score' => 70, 'matched' => [], 'partial' => [], 'missing' => []];
        }

        foreach ($clean as $item) {
            $status = $this->matchTerm($haystack, $item);
            if ($status === 'matched') {
                $matched[] = ['label' => $item, 'detail' => 'Found in CV / profile'];
            } elseif ($status === 'partial') {
                $partial[] = ['label' => $item, 'detail' => 'Related or partial mention found'];
            } else {
                $missing[] = ['label' => $item, 'detail' => 'Not found in submitted materials'];
            }
        }

        $score = (int) round(((count($matched) + 0.5 * count($partial)) / max(1, $clean->count())) * 100);

        return compact('score', 'matched', 'partial', 'missing');
    }

    /**
     * @param  array<string, mixed>  $extracted
     * @return array{score:int,matched:list<array{label:string,detail:string}>,partial:list<array{label:string,detail:string}>,missing:list<array{label:string,detail:string}>}
     */
    protected function compareEducation(?string $required, array $extracted, string $haystack): array
    {
        if (! $required) {
            return ['score' => 70, 'matched' => [], 'partial' => [], 'missing' => []];
        }

        $eduText = mb_strtolower(implode(' ', $extracted['education'] ?? []).' '.$haystack);
        $levels = [
            'PhD' => ['phd', 'doctorate', 'ph.d'],
            "Master's" => ['master', 'msc', 'm.s', 'mba'],
            "Bachelor's" => ['bachelor', 'bsc', 'b.s', 'b.eng', 'undergraduate', 'computer science', 'mbbs', 'mbchb', 'bmdc'],
            'Diploma / Associate' => ['associate', 'diploma', 'a.s'],
        ];

        $needles = $levels[$required] ?? [mb_strtolower($required)];
        foreach ($needles as $needle) {
            if (str_contains($eduText, $needle)) {
                return [
                    'score' => 100,
                    'matched' => [['label' => 'Education: '.$required, 'detail' => 'Education requirement appears to be met']],
                    'partial' => [],
                    'missing' => [],
                ];
            }
        }

        if ($extracted['education'] ?? []) {
            return [
                'score' => 50,
                'matched' => [],
                'partial' => [['label' => 'Education: '.$required, 'detail' => 'Education listed, level not clearly matched']],
                'missing' => [],
            ];
        }

        return [
            'score' => 20,
            'matched' => [],
            'partial' => [],
            'missing' => [['label' => 'Education: '.$required, 'detail' => 'Required education not identified']],
        ];
    }

    /**
     * @param  array<string, mixed>  $extracted
     * @return array{score:int,matched:list<array{label:string,detail:string}>,partial:list<array{label:string,detail:string}>,missing:list<array{label:string,detail:string}>}
     */
    protected function compareExperience(?string $required, array $extracted, string $haystack): array
    {
        if (! $required) {
            $hasExp = ($extracted['work_experience'] ?? []) !== [];

            return [
                'score' => $hasExp ? 80 : 55,
                'matched' => $hasExp ? [['label' => 'Work experience', 'detail' => 'Experience section present']] : [],
                'partial' => $hasExp ? [] : [['label' => 'Work experience', 'detail' => 'Limited or no experience listed']],
                'missing' => [],
            ];
        }

        if (str_contains(mb_strtolower($required), 'intern') || str_contains(mb_strtolower($required), 'entry')) {
            return [
                'score' => 90,
                'matched' => [['label' => 'Experience: '.$required, 'detail' => 'Role is suitable for internship / entry-level candidates']],
                'partial' => [],
                'missing' => [],
            ];
        }

        preg_match('/(\d+)/', $required, $m);
        $needed = isset($m[1]) ? (int) $m[1] : 0;
        $yearsFound = 0;
        if (preg_match_all('/(\d+)\s*\+?\s*years?/iu', $haystack, $hits)) {
            $yearsFound = max(array_map('intval', $hits[1]));
        }

        if ($needed > 0 && $yearsFound >= $needed) {
            return [
                'score' => 100,
                'matched' => [['label' => 'Experience: '.$required, 'detail' => "Candidate reports {$yearsFound}+ years"]],
                'partial' => [],
                'missing' => [],
            ];
        }

        if (($extracted['work_experience'] ?? []) !== []) {
            return [
                'score' => 55,
                'matched' => [],
                'partial' => [['label' => 'Experience: '.$required, 'detail' => 'Experience present but years may be below the stated requirement']],
                'missing' => [],
            ];
        }

        return [
            'score' => 25,
            'matched' => [],
            'partial' => [],
            'missing' => [['label' => 'Experience: '.$required, 'detail' => 'Required experience not identified']],
        ];
    }

    /**
     * @return array{score:int,matched:list<array{label:string,detail:string}>,partial:list<array{label:string,detail:string}>,missing:list<array{label:string,detail:string}>}
     */
    protected function compareLocation(JobPosting $job, ?string $candidateLocation, string $haystack): array
    {
        $mode = mb_strtolower((string) $job->location_type);
        if ($mode === 'remote') {
            return [
                'score' => 100,
                'matched' => [['label' => 'Location: Remote', 'detail' => 'Remote role — location is not a blocker']],
                'partial' => [],
                'missing' => [],
            ];
        }

        $jobLoc = mb_strtolower((string) $job->location);
        $candLoc = mb_strtolower((string) $candidateLocation);
        if ($jobLoc !== '' && $candLoc !== '' && (str_contains($candLoc, $jobLoc) || str_contains($jobLoc, $candLoc) || str_contains($haystack, $jobLoc))) {
            return [
                'score' => 100,
                'matched' => [['label' => 'Location: '.$job->location, 'detail' => 'Candidate location aligns with the role']],
                'partial' => [],
                'missing' => [],
            ];
        }

        if ($mode === 'hybrid') {
            return [
                'score' => 60,
                'matched' => [],
                'partial' => [['label' => 'Location: '.$job->location, 'detail' => 'Hybrid role — location only partially confirmed']],
                'missing' => [],
            ];
        }

        return [
            'score' => $candLoc ? 40 : 30,
            'matched' => [],
            'partial' => $candLoc ? [['label' => 'Location: '.$job->location, 'detail' => 'Location listed but does not clearly match']] : [],
            'missing' => $candLoc ? [] : [['label' => 'Location: '.$job->location, 'detail' => 'Candidate location not provided']],
        ];
    }

    protected function matchTerm(string $haystack, string $term): string
    {
        $needle = mb_strtolower(trim($term));
        if ($needle === '') {
            return 'missing';
        }

        if ($this->containsTerm($haystack, $needle)) {
            return 'matched';
        }

        foreach ($this->aliases[$needle] ?? [] as $alias) {
            if ($this->containsTerm($haystack, $alias)) {
                return 'matched';
            }
        }

        foreach ($this->aliases as $canonical => $aliasList) {
            if (in_array($needle, $aliasList, true) && $this->containsTerm($haystack, $canonical)) {
                return 'matched';
            }
        }

        $tokens = preg_split('/[\s\/,]+/', $needle) ?: [];
        if (count($tokens) > 1) {
            $hits = 0;
            foreach ($tokens as $token) {
                if (mb_strlen($token) >= 3 && $this->containsTerm($haystack, $token)) {
                    $hits++;
                }
            }
            if ($hits > 0) {
                return 'partial';
            }
        }

        similar_text($needle, $haystack, $percent);
        if ($percent > 8 && mb_strlen($needle) >= 4 && preg_match('/'.preg_quote(mb_substr($needle, 0, 4), '/').'/u', $haystack)) {
            return 'partial';
        }

        return 'missing';
    }

    protected function containsTerm(string $haystack, string $needle): bool
    {
        if (str_contains($haystack, $needle)) {
            return true;
        }

        return (bool) preg_match('/(^|[^a-z0-9.+#])'.preg_quote($needle, '/').'([^a-z0-9.+#]|$)/u', $haystack);
    }

    /**
     * @param  list<array{label:string,detail:string}>  $matched
     * @param  list<array{label:string,detail:string}>  $partial
     * @param  list<array{label:string,detail:string}>  $missing
     */
    protected function explain(string $name, int $score, array $matched, array $partial, array $missing): string
    {
        $matchedLabels = implode(', ', array_column($matched, 'label')) ?: 'none recorded';
        $partialLabels = implode(', ', array_column($partial, 'label')) ?: 'none';
        $missingLabels = implode(', ', array_column($missing, 'label')) ?: 'none';

        return "{$name} scored {$score}% against this role. "
            ."Matched: {$matchedLabels}. "
            ."Partially matched: {$partialLabels}. "
            ."Missing: {$missingLabels}. "
            .'This assessment is advisory only — the recruiter makes the final decision.';
    }

    /**
     * @param  list<array{label:string,detail:string}>  $matched
     * @param  list<array{label:string,detail:string}>  $missing
     */
    protected function recommend(int $score, array $matched, array $missing): string
    {
        if ($score >= 75) {
            return 'Recommended for shortlist. Strong alignment with required qualifications. Review the CV and consider advancing to interview.';
        }

        if ($score >= 50) {
            $gap = $missing[0]['label'] ?? 'a few stated requirements';

            return "Possible shortlist after manual review. Core overlap exists ({$score}%), but {$gap} may need recruiter judgement.";
        }

        return 'Not auto-recommended. Several requirements appear missing. Do not treat this as a rejection — inspect the CV before deciding.';
    }

    /**
     * @return Collection<int, ScreeningResult>
     */
    public function visibleResults(ScreeningSession $session, int $topN): Collection
    {
        return $session->results->sortBy('rank')->values()->take($topN);
    }
}
