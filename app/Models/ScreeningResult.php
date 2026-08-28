<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreeningResult extends Model
{
    protected $fillable = [
        'screening_session_id',
        'application_id',
        'candidate_id',
        'rank',
        'overall_score',
        'ai_recommended',
        'extracted_profile',
        'matched_points',
        'partial_matches',
        'missing_points',
        'score_breakdown',
        'ai_explanation',
        'ai_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'overall_score' => 'integer',
            'ai_recommended' => 'boolean',
            'extracted_profile' => 'array',
            'matched_points' => 'array',
            'partial_matches' => 'array',
            'missing_points' => 'array',
            'score_breakdown' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ScreeningSession::class, 'screening_session_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ScreeningDecision::class)->latest();
    }

    public function latestDecision(): ?ScreeningDecision
    {
        return $this->decisions->first();
    }

    public function isShortlisted(): bool
    {
        $decision = $this->latestDecision();

        if (! $decision) {
            return false;
        }

        return in_array($decision->decision, [
            \App\Enums\ScreeningDecisionType::Shortlist,
            \App\Enums\ScreeningDecisionType::AcceptRecommendation,
            \App\Enums\ScreeningDecisionType::Interview,
        ], true);
    }
}
