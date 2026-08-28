<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;

class CandidateProfileExtractor
{
    /**
     * @var list<string>
     */
    protected array $languages = [
        'python', 'javascript', 'typescript', 'java', 'c++', 'c#', 'php', 'go', 'golang',
        'ruby', 'kotlin', 'swift', 'rust', 'sql', 'html', 'css', 'r', 'scala', 'dart',
        'matlab', 'bash', 'shell', 'perl',
    ];

    /**
     * @var list<string>
     */
    protected array $tools = [
        'git', 'github', 'gitlab', 'docker', 'kubernetes', 'jenkins', 'jira', 'figma',
        'aws', 'azure', 'gcp', 'linux', 'nginx', 'redis', 'mysql', 'postgresql', 'mongodb',
        'postman', 'webpack', 'vite', 'npm', 'composer', 'laravel', 'react', 'vue',
        'angular', 'node.js', 'nodejs', 'django', 'flask', 'spring', 'tensorflow',
        'pytorch', 'excel', 'tableau', 'power bi',
    ];

    /**
     * @return array{
     *     education:list<string>,
     *     technical_skills:list<string>,
     *     work_experience:list<string>,
     *     projects:list<string>,
     *     certifications:list<string>,
     *     tools:list<string>,
     *     programming_languages:list<string>,
     *     raw_text:string
     * }
     */
    public function extract(User $candidate, ?Profile $profile = null, string $resumeText = ''): array
    {
        $profile ??= $candidate->profile;
        $corpus = $this->buildCorpus($candidate, $profile, $resumeText);
        $lower = mb_strtolower($corpus);

        $education = $this->fromProfileEducation($profile);
        $education = array_values(array_unique(array_merge(
            $education,
            $this->sectionLines($corpus, ['education', 'academic', 'qualification'])
        )));

        $experience = $this->fromProfileExperience($profile);
        $experience = array_values(array_unique(array_merge(
            $experience,
            $this->sectionLines($corpus, ['experience', 'employment', 'work history', 'professional experience'])
        )));

        $skills = collect($profile?->skills ?? [])
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values()
            ->all();
        $skills = array_values(array_unique(array_merge(
            $skills,
            $this->sectionLines($corpus, ['skills', 'technical skills', 'core competencies'])
        )));

        $projects = $this->sectionLines($corpus, ['projects', 'personal projects', 'academic projects']);
        $certs = $this->sectionLines($corpus, ['certifications', 'certificates', 'licenses']);
        if (preg_match_all('/\b(aws certified|pmp|scrum master|comptia|google certified|azure certified|oracle certified)[^\n,]{0,60}/iu', $corpus, $m)) {
            $certs = array_values(array_unique(array_merge($certs, array_map('trim', $m[0]))));
        }

        return [
            'education' => array_slice($education, 0, 12),
            'technical_skills' => array_slice($this->normalizeList($skills), 0, 30),
            'work_experience' => array_slice($experience, 0, 12),
            'projects' => array_slice($projects, 0, 12),
            'certifications' => array_slice($this->normalizeList($certs), 0, 12),
            'tools' => $this->detectTerms($lower, $this->tools),
            'programming_languages' => $this->detectTerms($lower, $this->languages),
            'raw_text' => $corpus,
        ];
    }

    protected function buildCorpus(User $candidate, ?Profile $profile, string $resumeText): string
    {
        $parts = [
            $candidate->name,
            $profile?->headline,
            $profile?->bio,
            $profile?->location,
            implode(', ', $profile?->skills ?? []),
            collect($profile?->educationEntries() ?? [])->flatten()->implode(' '),
            collect($profile?->experienceEntries() ?? [])->flatten()->implode(' '),
            $profile?->resume_text,
            $resumeText,
        ];

        return trim(preg_replace("/[ \t]+/u", ' ', implode("\n", array_filter($parts))) ?? '');
    }

    /**
     * @return list<string>
     */
    protected function fromProfileEducation(?Profile $profile): array
    {
        return collect($profile?->educationEntries() ?? [])
            ->map(function ($row) {
                return trim(implode(' · ', array_filter([
                    $row['degree'] ?? null,
                    $row['school'] ?? null,
                    isset($row['graduation_year']) ? (string) $row['graduation_year'] : null,
                    isset($row['gpa']) ? 'GPA '.$row['gpa'] : null,
                ])));
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    protected function fromProfileExperience(?Profile $profile): array
    {
        return collect($profile?->experienceEntries() ?? [])
            ->map(function ($row) {
                return trim(implode(' · ', array_filter([
                    $row['title'] ?? null,
                    $row['company'] ?? null,
                    $row['years'] ?? null,
                    $row['details'] ?? null,
                ])));
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $headings
     * @return list<string>
     */
    protected function sectionLines(string $text, array $headings): array
    {
        $pattern = '/(?:^|\n)\s*(?:'.implode('|', array_map(fn ($h) => preg_quote($h, '/'), $headings)).')\s*:?\s*\n([\s\S]{0,1200}?)(?=\n\s*[A-Z][A-Za-z ]{2,30}\s*:?\s*\n|$)/iu';
        if (! preg_match($pattern, $text, $match)) {
            return [];
        }

        $lines = preg_split('/\n+/', $match[1] ?? '') ?: [];

        return collect($lines)
            ->map(fn ($line) => trim(preg_replace('/^[\-\*\u2022•]+\s*/u', '', $line) ?? ''))
            ->filter(fn ($line) => mb_strlen($line) >= 2 && mb_strlen($line) <= 220)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $terms
     * @return list<string>
     */
    protected function detectTerms(string $haystack, array $terms): array
    {
        $found = [];
        foreach ($terms as $term) {
            $needle = preg_quote($term, '/');
            if (preg_match('/(^|[^a-z0-9.+#])'.$needle.'([^a-z0-9.+#]|$)/u', $haystack)) {
                $found[] = $this->titleCase($term);
            }
        }

        return $found;
    }

    /**
     * @param  list<string>  $items
     * @return list<string>
     */
    protected function normalizeList(array $items): array
    {
        return collect($items)
            ->flatMap(fn ($item) => preg_split('/[,;|]/', (string) $item) ?: [])
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => mb_strlen($item) >= 2)
            ->unique(fn ($item) => mb_strtolower($item))
            ->values()
            ->all();
    }

    protected function titleCase(string $value): string
    {
        $map = [
            'c++' => 'C++',
            'c#' => 'C#',
            'php' => 'PHP',
            'sql' => 'SQL',
            'html' => 'HTML',
            'css' => 'CSS',
            'aws' => 'AWS',
            'gcp' => 'GCP',
            'node.js' => 'Node.js',
            'nodejs' => 'Node.js',
            'golang' => 'Go',
        ];

        $key = mb_strtolower($value);

        return $map[$key] ?? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}
