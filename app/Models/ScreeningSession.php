<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreeningSession extends Model
{
    protected $fillable = [
        'job_posting_id',
        'recruiter_id',
        'top_n',
        'candidates_evaluated',
        'job_requirements',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'top_n' => 'integer',
            'candidates_evaluated' => 'integer',
            'job_requirements' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ScreeningResult::class)->orderBy('rank');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ScreeningDecision::class)->latest();
    }
}
