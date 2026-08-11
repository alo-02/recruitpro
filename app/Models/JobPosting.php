<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'slug',
        'type',
        'location_type',
        'location',
        'salary_min',
        'salary_max',
        'description',
        'skills',
        'status',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'status' => JobStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
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
        return $query->where('status', JobStatus::Active);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
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
}
