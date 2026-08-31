<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiCvMatcher
{
    /**
     * @return array{
     *     match_score:int,
     *     matched_skills:list<string>,
     *     missing_skills:list<string>,
     *     summary:string,
     *     source:string
     * }|null
     */
    public function match(string $cvText, string $jobDescription): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        $cvText = $this->clip($cvText, 14000);
        $jobDescription = $this->clip($jobDescription, 8000);

        if (trim($cvText) === '' || trim($jobDescription) === '') {
            return null;
        }

        $url = rtrim((string) config('services.gemini.base_url'), '/').'/models/'.$this->model().':generateContent';

        try {
            $request = Http::timeout(45)
                ->withHeaders(['x-goog-api-key' => $this->apiKey()])
                ->acceptJson()
                ->asJson();

            if (! app()->environment('testing')) {
                $request = $request->retry(3, 1500, function ($exception) {
                    if (! $exception instanceof RequestException || ! $exception->response) {
                        return false;
                    }

                    return in_array($exception->response->status(), [429, 502, 503], true);
                });
            }

            $response = $request->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $this->systemPrompt()]],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [[
                                'text' => "JOB DESCRIPTION:\n{$jobDescription}\n\nCANDIDATE CV:\n{$cvText}",
                            ]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'match_score' => ['type' => 'INTEGER'],
                                'matched_skills' => [
                                    'type' => 'ARRAY',
                                    'items' => ['type' => 'STRING'],
                                ],
                                'missing_skills' => [
                                    'type' => 'ARRAY',
                                    'items' => ['type' => 'STRING'],
                                ],
                                'summary' => ['type' => 'STRING'],
                            ],
                            'required' => ['match_score', 'matched_skills', 'missing_skills', 'summary'],
                        ],
                    ],
                ]);
        } catch (Throwable $e) {
            Log::warning('Gemini CV match request failed.', ['error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Gemini CV match HTTP error.', [
                'status' => $response->status(),
            ]);

            return null;
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        $parsed = $this->parsePayload(is_string($text) ? $text : '');

        return $parsed;
    }

    public function enabled(): bool
    {
        return $this->apiKey() !== '';
    }

    public function jobDescriptionFrom(object $job): string
    {
        $skills = [];
        if (isset($job->skills) && is_array($job->skills)) {
            $skills = $job->skills;
        }
        $keywords = [];
        if (isset($job->ats_keywords) && is_array($job->ats_keywords)) {
            $keywords = $job->ats_keywords;
        }

        return trim(implode("\n\n", array_filter([
            'Title: '.($job->title ?? ''),
            'Type: '.($job->type ?? ''),
            'Location: '.trim(($job->location_type ?? '').' '.($job->location ?? '')),
            $job->description ?? '',
            $job->requirements ? "Requirements:\n".$job->requirements : null,
            $skills ? 'Skills: '.implode(', ', $skills) : null,
            $keywords ? 'ATS keywords: '.implode(', ', $keywords) : null,
        ])));
    }

    protected function parsePayload(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/\{.*\}/s', $raw, $match)) {
            $raw = $match[0];
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return null;
        }

        $score = (int) ($data['match_score'] ?? -1);
        if ($score < 0 || $score > 100) {
            return null;
        }

        $matched = $this->stringList($data['matched_skills'] ?? []);
        $missing = $this->stringList($data['missing_skills'] ?? []);
        $summary = trim((string) ($data['summary'] ?? ''));
        if ($summary === '') {
            return null;
        }

        return [
            'match_score' => $score,
            'matched_skills' => $matched,
            'missing_skills' => $missing,
            'summary' => mb_substr($summary, 0, 1200),
            'source' => 'gemini',
        ];
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    protected function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => mb_strtolower($item))
            ->take(20)
            ->values()
            ->all();
    }

    protected function clip(string $text, int $max): string
    {
        $text = trim(preg_replace("/[ \t]+/u", ' ', $text) ?? $text);

        return mb_strlen($text) > $max ? mb_substr($text, 0, $max)."\n[truncated]" : $text;
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an objective HR screening assistant for an internship and job portal.
Compare the candidate CV against the job description.
Score only from evidence in the CV text. Do not invent employers, degrees, or skills.
Be fair to internships and early-career candidates: relevant coursework, projects, and internships count.
Return JSON only with:
- match_score: integer 0-100 overall fit
- matched_skills: skills/requirements clearly present in the CV
- missing_skills: important JD requirements not evidenced in the CV
- summary: 2-3 sentences for a recruiter (strengths, gaps, hire recommendation tone). Do not auto-reject.
PROMPT;
    }

    protected function apiKey(): string
    {
        return trim((string) config('services.gemini.key'));
    }

    protected function model(): string
    {
        return trim((string) config('services.gemini.model', 'gemini-2.0-flash')) ?: 'gemini-2.0-flash';
    }
}
