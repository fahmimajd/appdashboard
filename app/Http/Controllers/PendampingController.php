<?php

namespace App\Http\Controllers;

use App\Models\Pendamping;
use App\Models\User;
use App\Models\WilayahDesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PendampingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pendamping::with('desa.kecamatan')
            ->select('pendamping.*'); // Ensure we select only pendamping fields to avoid collisions

        if ($request->has('search')) {
            $search = $request->search;
            $query->leftJoin('wilayah_desa', 'pendamping.kode_desa', '=', 'wilayah_desa.kode_desa')
                  ->where(function ($q) use ($search) {
                      $q->where('pendamping.nama', 'like', "%{$search}%")
                        ->orWhere('pendamping.nik', 'like', "%{$search}%")
                        ->orWhere('wilayah_desa.nama_desa', 'like', "%{$search}%");
                  });
        }

        $pendampings = $query->paginate(15)->withQueryString();
        return view('pendamping.index', compact('pendampings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('pendamping.create', compact('desas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => [
                'required', 
                'numeric', 
                'digits:16', 
                'unique:users,nik', // NIK must be unique in users table
            ],
            'nama' => 'required|string|max:255',
            'nomor_ponsel' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'desa_id' => 'nullable|exists:wilayah_desa,id',
            'role' => 'required|in:admin,pendamping,supervisor,desa',
            'is_active' => 'boolean',
        ]);

        $kodeDesa = null;
        $kodeKecamatan = null;
        
        if ($request->filled('desa_id')) {
            $desa = WilayahDesa::find($request->desa_id);
            if ($desa) {
                $kodeDesa = $desa->kode_desa;
                $kodeKecamatan = $desa->kode_kecamatan;
            }
        }

        $akses = ucfirst($request->role);
        $statusAktif = $request->has('is_active') ? 'Aktif' : 'Tidak Aktif';
        $hashedPassword = Hash::make($validated['password']);

        DB::transaction(function () use ($validated, $kodeDesa, $kodeKecamatan, $akses, $statusAktif, $hashedPassword) {
            // Create User account first
            User::create([
                'nik' => $validated['nik'],
                'nama' => $validated['nama'],
                'password' => $hashedPassword,
                'akses' => $akses,
                'status_aktif' => $statusAktif,
                'kode_desa' => $kodeDesa,
                'kode_kecamatan' => $kodeKecamatan,
            ]);

            // Create Pendamping record
            Pendamping::create([
                'nik' => $validated['nik'],
                'nama' => $validated['nama'],
                'nomor_ponsel' => $validated['nomor_ponsel'] ?? null,
                'kode_desa' => $kodeDesa,
                'kode_kecamatan' => $kodeKecamatan,
                'password' => $hashedPassword, // Keep for backward compatibility
                'akses' => $akses,
                'status_aktif' => $statusAktif,
            ]);
        });

        return redirect()->route('pendamping.index')
                         ->with('success', 'Pendamping berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pendamping = Pendamping::with('desa')->findOrFail($id);
        return view('pendamping.show', compact('pendamping'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pendamping = Pendamping::findOrFail($id);
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('pendamping.edit', compact('pendamping', 'desas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pendamping = Pendamping::findOrFail($id);
        $user = User::where('nik', $pendamping->nik)->first();

        $validated = $request->validate([
            'nik' => [
                'required', 
                'numeric', 
                'digits:16',
                Rule::unique('users', 'nik')->ignore($user?->id),
            ],
            'nama' => 'required|string|max:255',
            'nomor_ponsel' => 'nullable|string|max:20',
            'desa_id' => 'nullable|exists:wilayah_desa,id',
            'role' => 'required|in:admin,pendamping,supervisor,desa',
            'is_active' => 'boolean',
        ]);

        $kodeDesa = $pendamping->kode_desa;
        $kodeKecamatan = $pendamping->kode_kecamatan;
        
        if ($request->filled('desa_id')) {
            $desa = WilayahDesa::find($request->desa_id);
            if ($desa) {
                $kodeDesa = $desa->kode_desa;
                $kodeKecamatan = $desa->kode_kecamatan;
            }
        }

        $akses = ucfirst($request->role);
        $statusAktif = $request->has('is_active') ? 'Aktif' : 'Tidak Aktif';

        $updateData = [
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'nomor_ponsel' => $validated['nomor_ponsel'] ?? null,
            'kode_desa' => $kodeDesa,
            'kode_kecamatan' => $kodeKecamatan,
            'akses' => $akses,
            'status_aktif' => $statusAktif,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed'
            ]);
            $updateData['password'] = Hash::make($request->password);
        }

        DB::transaction(function () use ($pendamping, $user, $updateData, $validated, $kodeDesa, $kodeKecamatan, $akses, $statusAktif) {
            // Update Pendamping
            $pendamping->update($updateData);

            // Update or create User
            $userData = [
                'nik' => $validated['nik'],
                'nama' => $validated['nama'],
                'akses' => $akses,
                'status_aktif' => $statusAktif,
                'kode_desa' => $kodeDesa,
                'kode_kecamatan' => $kodeKecamatan,
            ];
            
            if (isset($updateData['password'])) {
                $userData['password'] = $updateData['password'];
            }

            if ($user) {
                $user->update($userData);
            } else {
                // Create user if doesn't exist (for legacy data)
                if (!isset($userData['password'])) {
                    $userData['password'] = Hash::make('password123'); // Default password
                }
                User::create($userData);
            }
        });

        return redirect()->route('pendamping.index')
                         ->with('success', 'Data pendamping berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pendamping = Pendamping::findOrFail($id);
        
        // Prevent deleting yourself
        if (auth()->user()->nik === $pendamping->nik) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri');
        }

        DB::transaction(function () use ($pendamping) {
            // Delete associated user
            User::where('nik', $pendamping->nik)->delete();
            
            // Delete pendamping
            $pendamping->delete();
        });

        return redirect()->route('pendamping.index')
                         ->with('success', 'Pendamping berhasil dihapus');
    }
}

