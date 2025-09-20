<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required','string'],
            'password' => ['required','string'],
        ]);

        $login = $request->input('login');
        $creds = [
            filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username' => $login,
            'password' => $request->input('password'),
            'is_active' => 1,
        ];

        if (Auth::attempt($creds, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $intended = session('intended');
            if ($intended) {
                session()->forget('intended');
                return redirect()->to($intended);
            }
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Invalid credentials'])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

