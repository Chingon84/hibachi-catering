<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    private const GENERIC_STATUS = 'If your email exists in our system, you will receive a password reset link.';

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower(trim((string) $request->input('email')));
        $rateLimitKey = $this->rateLimitKey($request, $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Too many reset attempts. Please wait a minute and try again.']);
        }

        RateLimiter::hit($rateLimitKey, 60);

        Password::broker()->sendResetLink(['email' => $email]);

        return back()->with('status', self::GENERIC_STATUS);
    }

    private function rateLimitKey(Request $request, string $email): string
    {
        return 'password-reset:' . sha1($email . '|' . $request->ip());
    }
}
