<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pendamping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'nik' => 'required|size:16',
            'password' => 'required',
        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus 16 digit',
            'password.required' => 'Password wajib diisi',
        ]);

        // Rate limiting by NIK
        $throttleKey = Str::transliterate(Str::lower($request->nik).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            throw ValidationException::withMessages([
                'nik' => trans('Too many login attempts. Please try again in :seconds seconds.', [
                    'seconds' => $seconds,
                ]),
            ]);
        }

        // Attempt login
        $credentials = [
            'nik' => $request->nik,
            'password' => $request->password,
        ];

        $remember = $request->has('remember');

        try {
            if (Auth::attempt($credentials, $remember)) {
                $request->session()->regenerate();
                RateLimiter::clear($throttleKey);
                return $this->authenticated($request, Auth::user());
            }
        } catch (\Exception $e) {
            // Ignore exception (likely "This password does not use the Bcrypt algorithm")
            // and proceed to manual fallback
        }

        // Fallback: Check manual password (plain text)
        // Only if Auth::attempt failed (which fails for plain text if DB expects bcrypt)
        $user = Pendamping::where('nik', $request->nik)->first();
        
        // Use getAttributes to get raw password value without casting checks if possible,
        // but Model casts 'password' => 'hashed', so accessing $user->password might try to hash/compare incorrectly?
        // Actually, $user->password access on a model with 'hashed' cast usually returns nothing useful for direct comparison 
        // OR it might return the hashed object.
        // Better to query raw or rely on the fact that if it WAS hashed properly, Auth::attempt would adhere.
        // If it failed, it might be plain text.
        
        if ($user && $user->getAttributes()['password'] === $request->password) {
            // It matches plain text!
            Auth::login($user, $remember);
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);
            
            // Flag session to force password change
            session(['force_password_change' => true]);

            return $this->authenticated($request, $user);
        }

        // Failed login attempt
        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'nik' => 'NIK atau password salah.',
        ]);
    }

    /**
     * Handle post-authentication checks
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if user is active
        if (!$user->isActive()) {
            Auth::logout();
            return back()->withErrors([
                'nik' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
