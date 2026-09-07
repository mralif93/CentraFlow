<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CentraFlowAuthController extends Controller
{
    /**
     * Show login view.
     */
    public function showLogin(Request $request): View
    {
        return view('auth.login', [
            'returnTo' => $request->query('return_to'),
        ]);
    }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // 1. Explicit return_to parameter
            if ($request->filled('return_to')) {
                return redirect()->away($request->input('return_to'));
            }

            // 2. Check if user came from an OAuth authorization request (e.g. /oauth/authorize)
            if ($request->session()->has('url.intended')) {
                return redirect()->intended();
            }

            // 3. Fallback: Admins and officers redirect to admin dashboard
            $user = Auth::user();
            if (in_array($user->role, ['superadmin', 'hr_manager', 'payroll_officer', 'finance_officer'])) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our identity records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration view.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['nullable', 'string', 'in:superadmin,hr_manager,payroll_officer,finance_officer,employee'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'employee',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Terminate active session across all systems (Federated Single Logout).
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // If a specific OAuth token ID or Bearer token was provided, revoke it
        if ($tokenId = $request->query('token_id')) {
            \Laravel\Passport\Token::where('id', $tokenId)->update(['revoked' => true]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect back to caller sub-system or fallback to home
        if ($redirect = $request->query('redirect_uri')) {
            return redirect()->away($redirect);
        }

        return redirect('/');
    }
}
