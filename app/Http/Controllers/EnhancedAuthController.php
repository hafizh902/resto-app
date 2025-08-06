<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnhancedAuthController extends Controller
{
    /**
     * Maximum login attempts allowed
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Lockout duration in minutes
     */
    private const LOCKOUT_DURATION = 15;

    /**
     * Show the enhanced login form
     */
    public function showLoginForm()
    {
        return view('auth.enhanced-login');
    }

    /**
     * Handle enhanced login with security features
     */
    public function login(Request $request)
    {
        // Rate limiting check
        $key = 'login-attempts-' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.'
                ]);
        }

        // Validate input
        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
            'remember' => 'boolean'
        ]);

        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);
            $this->logLoginAttempt($request, $credentials['email'], 'user_not_found');
            
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        // Check if account is active
        if (isset($user->status) && $user->status !== 'active') {
            $this->logLoginAttempt($request, $credentials['email'], 'account_inactive');
            
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.']);

        }

        // Attempt authentication
        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password']
        ], $request->boolean('remember'))) {

            // Clear rate limiter on successful login
            RateLimiter::clear($key);
            
            // Regenerate session
            $request->session()->regenerate();
            
            // Log successful login
            $this->logLoginAttempt($request, $credentials['email'], 'success');
            
            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        // Failed login attempt
        RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);
        $this->logLoginAttempt($request, $credentials['email'], 'invalid_credentials');

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    /**
     * Handle logout with security cleanup
     */
    public function logout(Request $request)
    {
        // Log logout activity
        if (Auth::check()) {
            LoginAttempt::create([
                'email' => Auth::user()->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'logout',
                'attempted_at' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Log login attempts for security monitoring
     */
    private function logLoginAttempt(Request $request, $email, $status)
    {
        LoginAttempt::create([
            'email' => $email,
            'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => $status,
                'attempted_at' => now(),
            ]);
    }

    /**
     * Redirect user based on role
     */
    private function redirectBasedOnRole($user)
    {
        $intended = session()->pull('url.intended', null);
        
        if ($intended) {
            return redirect($intended);
        }

        switch ($user->role_id) {
            case 1: // Admin
                return redirect()->route('admin.dashboard');
            case 2: // Customer
                return redirect()->route('home');
            default:
                return redirect()->route('home');
        }
    }
}
