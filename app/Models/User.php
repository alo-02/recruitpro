<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isRecruiter(): bool
    {
        return $this->role === UserRole::Recruiter;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function dashboardRoute(): string
    {
        return $this->role->dashboardRoute();
    }
}
