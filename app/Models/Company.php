<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'website',
        'description',
        'logo_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'plan',
        'plan_expires_at',
        'max_team_seats',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
            'approved_at' => 'datetime',
            'plan_expires_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CompanyMember::class);
    }

    public function hiringStages(): HasMany
    {
        return $this->hasMany(HiringStage::class)->orderBy('position');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CompanyReview::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_follows')->withTimestamps();
    }

    public function blacklists(): HasMany
    {
        return $this->hasMany(CandidateBlacklist::class);
    }

    public function isApproved(): bool
    {
        return $this->status === CompanyStatus::Approved;
    }

    public function planLabel(): string
    {
        return match ($this->plan) {
            'pro' => 'Pro',
            'enterprise' => 'Enterprise',
            default => 'Free',
        };
    }

    public function remainingSeats(): int
    {
        return max(0, ($this->max_team_seats ?? 3) - $this->members()->count());
    }
}
