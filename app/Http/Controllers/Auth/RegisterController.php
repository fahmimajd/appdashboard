<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PetugasKecamatan;
use App\Models\WilayahKecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Show the application registration form.
     */
    public function showRegistrationForm()
    {
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        return view('auth.register', compact('kecamatans'));
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|numeric|digits:16|unique:users,nik|unique:petugas_kecamatan,nik',
            'nama' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'nomor_ponsel' => 'required|string|max:20',
            'kode_kecamatan' => 'required|exists:wilayah_kecamatan,kode_kecamatan',
        ]);

        try {
            \DB::transaction(function () use ($validated) {
                // 1. Create User Account
                $user = User::create([
                    'nik' => $validated['nik'],
                    'nama' => strtoupper($validated['nama']),
                    'password' => Hash::make($validated['password']),
                    'akses' => 'Petugas',
                    'status_aktif' => 'Aktif',
                    'kode_kecamatan' => $validated['kode_kecamatan'],
                ]);

                // 2. Create Petugas Kecamatan Data
                PetugasKecamatan::create([
                    'nik' => $validated['nik'],
                    'nama' => strtoupper($validated['nama']),
                    'nomor_ponsel' => $validated['nomor_ponsel'],
                    'kode_kecamatan' => $validated['kode_kecamatan'],
                    'status_aktif' => 'Aktif',
                    'tanggal_mulai_akses' => now(),
                ]);

                // 3. Auto Login (Removed as per request to show success page instead)
                // Auth::login($user); 
            });

            return redirect()->route('register.success');

        } catch (\Exception $e) {
            return back()->withError('Terjadi kesalahan saat registrasi: ' . $e->getMessage())->withInput();
        }
    }
}
