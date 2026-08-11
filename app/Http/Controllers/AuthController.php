<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Company;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->dashboardRoute()));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        if ($user->isStudent()) {
            Profile::create(['user_id' => $user->id]);
        }

        if ($user->isRecruiter()) {
            Company::create([
                'user_id' => $user->id,
                'name' => $user->name.' Company',
                'slug' => Str::slug($user->name.'-company-'.$user->id),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->dashboardRoute())
            ->with('success', 'Welcome to RecruitPro! Your account has been created.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }

    public function showPasswordReset(): View
    {
        return view('auth.password-reset');
    }
}
