<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProject extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'url', 'repo_url', 'tech_stack', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['tech_stack' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
