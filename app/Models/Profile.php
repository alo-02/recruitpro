<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'phone',
        'location',
        'skills',
        'education',
        'resume_path',
        'completeness',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'education' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculateCompleteness(): int
    {
        $fields = [
            $this->headline,
            $this->bio,
            $this->phone,
            $this->location,
            $this->skills,
            $this->education,
            $this->resume_path,
        ];

        $filled = collect($fields)->filter(fn ($v) => ! empty($v))->count();

        return (int) round(($filled / count($fields)) * 100);
    }
}
