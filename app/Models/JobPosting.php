<?php

namespace App\Models;

use App\Enums\JobCategory;
use App\Enums\JobStatus;
use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class JobPosting extends Model
{
    use Searchable, SoftDeletes;
    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'slug',
        'type',
        'category',
        'location_type',
        'location',
        'salary_min',
        'salary_max',
        'description',
        'requirements',
        'skills',
        'ats_keywords',
        'ats_min_score',
        'status',
        'moderation_status',
        'is_featured',
        'published_at',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'ats_keywords' => 'array',
            'ats_min_score' => 'integer',
            'category' => JobCategory::class,
            'status' => JobStatus::class,
            'moderation_status' => ModerationStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'deadline' => 'date',
        ];
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', JobStatus::Active)
            ->where('moderation_status', ModerationStatus::Approved)
            ->where(function ($q) {
                $q->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isOpen(): bool
    {
        return $this->status === JobStatus::Active
            && $this->moderation_status === ModerationStatus::Approved
            && ($this->deadline === null || $this->deadline->isFuture() || $this->deadline->isToday());
    }

    public function salaryDisplay(): string
    {
        if ($this->salary_min && $this->salary_max) {
            return "{$this->salary_min} - {$this->salary_max}";
        }

        return $this->salary_min ?? $this->salary_max ?? 'Competitive';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function searchableAs(): string
    {
        return 'job_postings';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === JobStatus::Active
            && $this->moderation_status === ModerationStatus::Approved;
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('company');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'type' => $this->type,
            'category' => $this->category?->value ?? $this->category,
            'location' => $this->location,
            'location_type' => $this->location_type,
            'skills' => implode(' ', $this->skills ?? []),
            'company' => $this->company?->name,
        ];
    }
}
