<?php

namespace App\Models;

use App\Enums\ScreeningDecisionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningDecision extends Model
{
    protected $fillable = [
        'screening_session_id',
        'screening_result_id',
        'application_id',
        'recruiter_id',
        'decision',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'decision' => ScreeningDecisionType::class,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ScreeningSession::class, 'screening_session_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(ScreeningResult::class, 'screening_result_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }
}
