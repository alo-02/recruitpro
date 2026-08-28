<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Enums\CompanyStatus;
use App\Models\Company;
use App\Models\Profile;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SecurityEventLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            $existing = User::where('email', $credentials['email'] ?? '')->first();
            SecurityEventLogger::log(
                'login_failed',
                'warning',
                $credentials['email'] ?? null,
                $existing,
                ['reason' => 'invalid_credentials'],
                $request
            );

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::user();

        if (! $user->canAccessPlatform()) {
            Auth::logout();
            SecurityEventLogger::log(
                'blocked_login_attempt',
                'critical',
                $user->email,
                $user,
                [
                    'is_blocked' => $user->is_blocked,
                    'is_active' => $user->is_active,
                ],
                $request
            );

            return back()->withErrors(['email' => 'Your account has been blocked or deactivated. Contact support.']);
        }

        if ($user->two_factor_enabled) {
            Auth::logout();

            $code = (string) random_int(100000, 999999);
            \Illuminate\Support\Facades\Cache::put('2fa:'.$user->id, $code, now()->addMinutes(10));
            $user->notify(new \App\Notifications\TwoFactorCode($code));

            $request->session()->put('login.id', $user->id);
            $request->session()->put('login.remember', $remember);

            return redirect()->route('two-factor.challenge')
                ->with('success', 'A verification code was sent to your email.');
        }

        $request->session()->regenerate();
        ActivityLogger::log('user.login', $user);

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'is_active' => true,
            'is_blocked' => false,
        ]);

        if ($user->isStudent()) {
            Profile::create(['user_id' => $user->id]);
        }

        if ($user->isRecruiter()) {
            $companyName = $request->company_name ?: ($user->name.' Company');
            Company::create([
                'user_id' => $user->id,
                'name' => $companyName,
                'slug' => Str::slug($companyName.'-'.$user->id),
                'status' => CompanyStatus::Pending,
            ]);
        }

        event(new Registered($user));
        \Spatie\Permission\Models\Role::findOrCreate($user->role->value);
        $user->assignRole($user->role->value);
        ActivityLogger::log('user.register', $user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('success', 'Account created! Please verify your email to continue.');
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLogger::log('user.logout', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }

    public function showPasswordReset(): View
    {
        return view('auth.password-reset');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function showVerificationNotice(): View
    {
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();
        ActivityLogger::log('user.email_verified', $request->user());

        return redirect()->route($request->user()->dashboardRoute())
            ->with('success', 'Email verified successfully!');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->dashboardRoute());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link and 6-digit code sent. Check your Gmail inbox and spam folder.');
    }

    public function verifyWithCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->dashboardRoute());
        }

        $expected = Cache::get('email-verify-code:'.$user->id);
        if (! is_string($expected) || ! hash_equals($expected, $request->code)) {
            return back()->withErrors(['code' => 'That verification code is invalid or has expired.']);
        }

        $user->markEmailAsVerified();
        Cache::forget('email-verify-code:'.$user->id);
        ActivityLogger::log('user.email_verified', $user);

        return redirect()->route($user->dashboardRoute())
            ->with('success', 'Email verified successfully!');
    }
}
