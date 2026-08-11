<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  UserRole|array<UserRole|string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = collect($roles)->map(fn ($role) => $role instanceof UserRole ? $role->value : $role);

        if (! $allowed->contains($user->role->value)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
