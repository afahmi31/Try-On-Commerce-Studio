<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\InitialSetup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (! InitialSetup::isCompleted()) {
            return redirect()->route('setup');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (! InitialSetup::isCompleted()) {
            return redirect()->route('setup');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('seller.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
