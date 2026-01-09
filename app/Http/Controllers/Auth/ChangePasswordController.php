<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    /**
     * Show the change password form
     */
    public function show()
    {
        return view('auth.change-password');
    }

    /**
     * Update the password
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required', // Removed 'current_password' rule for manual check scenario
            'password' => ['required', 'confirmed', Password::min(6)->letters()->numbers()],
        ], [
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.letters' => 'Password harus mengandung huruf.',
            'password.numbers' => 'Password harus mengandung angka.',
        ]);
        
        $user = Auth::user();

        // Custom current password check to support plain text
        // If session flag is set, it might be plain text
        $currentPass = $user->getAttributes()['password'];
        $inputCurrent = $request->current_password;
        
        $isMatch = false;
        
        try {
            if (Hash::check($inputCurrent, $user->password)) {
                $isMatch = true;
            }
        } catch (\Exception $e) {
            // Ignore bcrypt error, proceed to fallback
        }

        if (!$isMatch && $currentPass === $inputCurrent) {
            $isMatch = true;
        }

        if (!$isMatch) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }
        
        // Ensure new password is different
        if ($inputCurrent === $request->password) {
             return back()->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            // last_password_change removed
        ]);

        // Clear session flag
        session()->forget('force_password_change');

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah.');
    }
}
