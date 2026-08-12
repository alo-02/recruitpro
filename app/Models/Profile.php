<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar_path',
        'headline',
        'bio',
        'phone',
        'location',
        'skills',
        'education',
        'experience',
        'resume_path',
        'resume_text',
        'completeness',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'education' => 'array',
            'experience' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avatarUrl(): string
    {
        if ($this->avatar_path) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->user->name ?? 'User').'&background=0051d5&color=fff';
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function educationEntries(): array
    {
        return $this->normalizeEntries($this->education, ['school', 'degree']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function experienceEntries(): array
    {
        return $this->normalizeEntries($this->experience, ['title', 'company']);
    }

    public function calculateCompleteness(): int
    {
        $fields = [
            $this->headline,
            $this->bio,
            $this->phone,
            $this->location,
            $this->skills,
            $this->educationEntries(),
            $this->experienceEntries(),
            $this->resume_path,
            $this->avatar_path,
        ];

        $filled = collect($fields)->filter(fn ($v) => ! empty($v))->count();

        return (int) round(($filled / count($fields)) * 100);
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>|null  $value
     * @param  list<string>  $keys
     * @return list<array<string, mixed>>
     */
    protected function normalizeEntries(?array $value, array $keys): array
    {
        if (! is_array($value) || $value === []) {
            return [];
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $value)) {
                return [$value];
            }
        }

        return array_values(array_filter($value, fn ($row) => is_array($row) && filled(array_filter($row))));
    }
}
