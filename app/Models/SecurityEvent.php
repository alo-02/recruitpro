<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'severity',
        'email',
        'ip_address',
        'user_agent',
        'context',
        'resolved',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'resolved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function severityBadge(): string
    {
        return match ($this->severity) {
            'critical' => 'bg-danger',
            'warning' => 'bg-warning text-dark',
            'info' => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }
}
