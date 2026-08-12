<?php

namespace App\Services;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityEventLogger
{
    public static function log(
        string $eventType,
        string $severity = 'warning',
        ?string $email = null,
        ?User $user = null,
        array $context = [],
        ?Request $request = null,
    ): SecurityEvent {
        $request ??= request();

        $event = SecurityEvent::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'severity' => $severity,
            'email' => $email ?? $user?->email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'context' => $context ?: null,
            'resolved' => false,
        ]);

        ActivityLogger::log('security.'.$eventType, $user, [
            'severity' => $severity,
            'email' => $email ?? $user?->email,
            'security_event_id' => $event->id,
        ]);

        return $event;
    }
}
