<?php

namespace Modules\Security\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\TwoFactorCode;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function showChallenge(): View
    {
        abort_unless(session()->has('login.id'), 403);

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $userId = session('login.id');
        abort_unless($userId, 403);

        $expected = Cache::get('2fa:'.$userId);
        if (! $expected || ! hash_equals((string) $expected, $request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        Cache::forget('2fa:'.$userId);
        session()->forget(['login.id', 'login.remember']);

        $user = \App\Models\User::findOrFail($userId);
        Auth::login($user, (bool) session('login.remember'));
        $request->session()->regenerate();
        ActivityLogger::log('user.2fa_verified', $user);

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => bin2hex(random_bytes(16)),
        ]);

        ActivityLogger::log('user.2fa_enabled', $user);

        return back()->with('success', 'Two-factor authentication enabled. Codes will be emailed on login.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_secret' => null,
        ]);

        ActivityLogger::log('user.2fa_disabled', $user);

        return back()->with('success', 'Two-factor authentication disabled.');
    }
}
