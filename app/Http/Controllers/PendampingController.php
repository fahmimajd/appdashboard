<?php

namespace App\Http\Controllers;

use App\Models\Pendamping;
use App\Models\WilayahDesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PendampingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pendamping::with('desa.kecamatan');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
        }

        $pendampings = $query->paginate(15);
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
            'nik' => 'required|numeric|unique:pendamping,nik|digits:16',
            'nama' => 'required|string|max:255',
            'nomor_ponsel' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'desa_id' => 'nullable|exists:wilayah_desa,id',
            'role' => 'required|in:admin,operator,user',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('desa_id')) {
            $desa = WilayahDesa::find($request->desa_id);
            if ($desa) {
                $validated['kode_desa'] = $desa->kode_desa;
            }
        }
        unset($validated['desa_id']);

        $validated['akses'] = ucfirst($request->role); // Admin, Operator, User
        unset($validated['role']);

        $validated['password'] = Hash::make($validated['password']);
        
        $validated['status_aktif'] = $request->has('is_active') ? 'Aktif' : 'Tidak Aktif';
        unset($validated['is_active']);

        Pendamping::create($validated);

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

        $validated = $request->validate([
            'nik' => ['required', 'numeric', 'digits:16', Rule::unique('pendamping')->ignore($pendamping->nik)],
            'nama' => 'required|string|max:255',
            'nomor_ponsel' => 'nullable|string|max:20',
            'desa_id' => 'nullable|exists:wilayah_desa,id',
            'role' => 'required|in:admin,operator,user',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('desa_id')) {
            $desa = WilayahDesa::find($request->desa_id);
            if ($desa) {
                $validated['kode_desa'] = $desa->kode_desa;
            }
        }
        unset($validated['desa_id']);

        $validated['akses'] = ucfirst($request->role);
        unset($validated['role']);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed'
            ]);
            $validated['password'] = Hash::make($request->password);
        }
        
        // Don't unset password if it wasn't set in validated array from first validation
        // But here we are manually adding it to $validated array.
        // Wait, $validated only contains fields from first validate call. 
        // We need to be careful not to overwrite if empty.
        
        $validated['status_aktif'] = $request->has('is_active') ? 'Aktif' : 'Tidak Aktif';
        unset($validated['is_active']);

        $pendamping->update($validated);

        return redirect()->route('pendamping.index')
                         ->with('success', 'Data pendamping berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pendamping = Pendamping::findOrFail($id);
        
        // Prevent deleting yourself? Maybe check Auth::id()
        if (auth()->id() == $pendamping->nik) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri');
        }

        $pendamping->delete();

        return redirect()->route('pendamping.index')
                         ->with('success', 'Pendamping berhasil dihapus');
    }
}
