<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;

class AuthenticationsController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLoginPage(): View
    {
        Log::info('[CivicUtopia] Displaying login page.');
        return view('content.authentications.auth-login-basic');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function storeLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        Log::info('[CivicUtopia] Attempting to authenticate user: ' . $request->email);

        if (Auth::attempt($credentials, $request->boolean('remember-me'))) {
            $request->session()->regenerate();
            Log::info('[CivicUtopia] User authenticated successfully. Redirecting to dashboard.');
            return redirect()->intended(route('dashboard'));
        }

        Log::warning('[CivicUtopia] Authentication failed for user: ' . $request->email);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session (logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::info('[CivicUtopia] User logging out: ' . Auth::user()->name);
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // ---------- NEW METHODS FOR REGISTRATION ---------- //

    /**
     * Display the registration view.
     */
    public function showRegistrationPage(): View
    {
        Log::info('[CivicUtopia] Displaying registration page.');
        return view('content.authentications.auth-register-basic');
    }

    /**
     * Handle an incoming registration request.
     */
    public function storeRegistration(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'], // This validates the "I agree" checkbox
        ]);

        Log::info('[CivicUtopia] New user registration validation passed for: ' . $request->email);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // You can dispatch an event here if you want (e.g., for sending a welcome email)
        // event(new Registered($user));

        Auth::login($user);

        Log::info('[CivicUtopia] New user created and logged in. Redirecting to dashboard.');

        return redirect(route('dashboard'));
    }
}
