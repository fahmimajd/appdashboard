<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WilayahKecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::with(['kecamatan', 'desa']);

        // Search by name, NIK
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('akses') && $request->akses) {
            $query->where('akses', $request->akses);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status_aktif', $request->status);
        }

        $users = $query->orderBy('nama')->paginate(15)->withQueryString();
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        $roles = ['Admin', 'Pendamping', 'Supervisor', 'Petugas'];
        
        return view('users.create', compact('kecamatans', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => [
                'required',
                'numeric',
                'digits:16',
                'unique:users,nik',
            ],
            'nama' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'akses' => 'required|in:Admin,Pendamping,Supervisor,Desa,Petugas',
            'status_aktif' => 'required|in:Aktif,Tidak Aktif',
            'kecamatan_id' => 'nullable|exists:wilayah_kecamatan,kode_kecamatan',
        ]);

        $kodeKecamatan = $request->filled('kecamatan_id') ? $request->kecamatan_id : null;

        User::create([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'password' => Hash::make($validated['password']),
            'akses' => $validated['akses'],
            'status_aktif' => $validated['status_aktif'],
            'kode_kecamatan' => $kodeKecamatan,
        ]);

        return redirect()->route('users.index')
                         ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::with('kecamatan', 'pendamping')->findOrFail($id);
        
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        $roles = ['Admin', 'Pendamping', 'Supervisor', 'Petugas'];
        
        return view('users.edit', compact('user', 'kecamatans', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nik' => [
                'required',
                'numeric',
                'digits:16',
                Rule::unique('users', 'nik')->ignore($user->id),
            ],
            'nama' => 'required|string|max:255',
            'akses' => 'required|in:Admin,Pendamping,Supervisor,Desa,Petugas',
            'status_aktif' => 'required|in:Aktif,Tidak Aktif',
            'kecamatan_id' => 'nullable|exists:wilayah_kecamatan,kode_kecamatan',
        ]);

        $kodeKecamatan = $user->kode_kecamatan;

        if ($request->filled('kecamatan_id')) {
            $kodeKecamatan = $request->kecamatan_id;
        } elseif ($request->has('kecamatan_id') && !$request->filled('kecamatan_id')) {
            // User cleared the kecamatan selection
            $kodeKecamatan = null;
        }

        $updateData = [
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'akses' => $validated['akses'],
            'status_aktif' => $validated['status_aktif'],
            'kode_kecamatan' => $kodeKecamatan,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
                         ->with('success', 'Data user berhasil diperbarui');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if (auth()->user()->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri');
        }

        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'User berhasil dihapus');
    }

    /**
     * Reset user password.
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // Default password
        $defaultPassword = 'password123';
        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Password berhasil direset ke: {$defaultPassword}");
    }
}
