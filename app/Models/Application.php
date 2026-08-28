<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'job_posting_id',
        'status',
        'hiring_stage_id',
        'cover_letter',
        'recruiter_notes',
        'recruiter_tags',
        'match_score',
        'ats_matched_keywords',
        'ats_missing_keywords',
        'ats_checked_at',
        'applied_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'ats_matched_keywords' => 'array',
            'ats_missing_keywords' => 'array',
            'recruiter_tags' => 'array',
            'ats_checked_at' => 'datetime',
            'applied_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function hiringStage(): BelongsTo
    {
        return $this->belongsTo(HiringStage::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    public function screeningResults(): HasMany
    {
        return $this->hasMany(ScreeningResult::class);
    }

    public function isWithdrawn(): bool
    {
        return $this->status === ApplicationStatus::Withdrawn;
    }
}
