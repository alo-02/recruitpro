<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['key', 'label', 'enabled', 'meta'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'meta' => 'array',
        ];
    }

    public static function isEnabled(string $key, bool $default = false): bool
    {
        $flag = static::query()->where('key', $key)->first();

        return $flag ? (bool) $flag->enabled : $default;
    }
}
