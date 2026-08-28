<?php

namespace App\Services;

use App\Models\JobPosting;

class JobRequirementAnalyzer
{
    /**
     * @return array{
     *     required_skills:list<string>,
     *     preferred_skills:list<string>,
     *     education_level:?string,
     *     experience:?string,
     *     location:?string,
     *     location_type:?string,
     *     criteria:list<string>
     * }
     */
    public function analyze(JobPosting $job): array
    {
        $text = trim(implode("\n", array_filter([
            $job->title,
            $job->description,
            $job->requirements,
            implode(', ', $job->skills ?? []),
            implode(', ', $job->ats_keywords ?? []),
        ])));

        $required = $this->uniqueList(array_merge(
            $job->ats_keywords ?: [],
            $job->skills ?: [],
            $this->extractListAfter($text, ['requirements', 'required', 'must have', 'must-have'])
        ));

        $preferred = $this->uniqueList($this->extractListAfter($text, [
            'preferred', 'nice to have', 'nice-to-have', 'bonus', 'plus',
        ]));

        $preferred = array_values(array_diff($preferred, array_map('mb_strtolower', $required)));

        return [
            'required_skills' => $required,
            'preferred_skills' => array_map([$this, 'titleCase'], $preferred),
            'education_level' => $this->detectEducation($text),
            'experience' => $this->detectExperience($text),
            'location' => $job->location,
            'location_type' => $job->location_type,
            'criteria' => $this->criteria($job, $text),
        ];
    }

    /**
     * @param  list<string>  $headings
     * @return list<string>
     */
    protected function extractListAfter(string $text, array $headings): array
    {
        $pattern = '/(?:'.implode('|', array_map(fn ($h) => preg_quote($h, '/'), $headings)).')\s*:?\s*([\s\S]{0,800}?)(?=\n[A-Z][A-Za-z ]{3,30}\s*:|\z)/iu';
        if (! preg_match($pattern, $text, $match)) {
            return [];
        }

        $chunk = $match[1] ?? '';
        $parts = preg_split('/[\n,;•\-\*]+/', $chunk) ?: [];

        return collect($parts)
            ->map(fn ($part) => trim($part))
            ->filter(fn ($part) => mb_strlen($part) >= 2 && mb_strlen($part) <= 60)
            ->take(20)
            ->all();
    }

    protected function detectEducation(string $text): ?string
    {
        if (preg_match('/\b(ph\.?d|doctorate)\b/iu', $text)) {
            return 'PhD';
        }
        if (preg_match('/\b(master\'?s|msc|m\.s\.|mba)\b/iu', $text)) {
            return "Master's";
        }
        if (preg_match('/\b(bachelor\'?s|bsc|b\.s\.|b\.eng|undergraduate)\b/iu', $text)) {
            return "Bachelor's";
        }
        if (preg_match('/\b(associate|diploma|high school)\b/iu', $text)) {
            return 'Diploma / Associate';
        }

        return null;
    }

    protected function detectExperience(string $text): ?string
    {
        if (preg_match('/(\d+)\+?\s*\-?\s*(\d+)?\s*\+?\s*years?/iu', $text, $m)) {
            $min = $m[1];
            $max = $m[2] ?? null;

            return $max ? "{$min}-{$max} years" : "{$min}+ years";
        }

        if (preg_match('/\b(intern|internship|entry[\s-]?level|fresher|graduate)\b/iu', $text)) {
            return 'Internship / entry level';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function criteria(JobPosting $job, string $text): array
    {
        $items = array_filter([
            $job->type ? 'Job type: '.str_replace('-', ' ', $job->type) : null,
            $job->location_type ? 'Work mode: '.$job->location_type : null,
            $job->location ? 'Location: '.$job->location : null,
            $this->detectEducation($text) ? 'Education: '.$this->detectEducation($text) : null,
            $this->detectExperience($text) ? 'Experience: '.$this->detectExperience($text) : null,
        ]);

        return array_values($items);
    }

    /**
     * @param  list<mixed>  $items
     * @return list<string>
     */
    protected function uniqueList(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => mb_strtolower($item))
            ->values()
            ->all();
    }

    protected function titleCase(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}
