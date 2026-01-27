<?php

namespace App\Http\Controllers;

use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WilayahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WilayahDesa::with(['kecamatan.kabupaten']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama_desa', 'like', "%{$search}%")
                  ->orWhereHas('kecamatan', function($q) use ($search) {
                      $q->where('nama_kecamatan', 'like', "%{$search}%");
                  });
        }

        if ($request->has('kecamatan_id') && $request->kecamatan_id != 'all') {
            $query->where('kecamatan_id', $request->kecamatan_id);
        }

        $desas = $query->paginate(15)->withQueryString();
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();

        return view('wilayah.index', compact('desas', 'kecamatans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        return view('wilayah.create', compact('kecamatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation handled here for simplicity, can be moved to FormRequest
        $validated = $request->validate([
            'kode_desa' => 'required|unique:wilayah_desa,kode_desa|max:20',
            'nama_desa' => 'required|string|max:255',
            'kecamatan_id' => 'required|exists:wilayah_kecamatan,id',
            'alamat_kantor' => 'nullable|string',
            'email_desa' => 'nullable|email',
            'telepon_desa' => 'nullable|string|max:50',
            'kepala_desa' => 'nullable|string|max:255',
            'status_desa' => 'required|in:1,0',
        ]);

        try {
            DB::beginTransaction();
            WilayahDesa::create($validated);
            DB::commit();

            return redirect()->route('wilayah.index')
                             ->with('success', 'Data desa berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $desa = WilayahDesa::with(['kecamatan.kabupaten', 'pendamping', 'petugas', 'sarpras', 'vpn', 'kinerja'])
                           ->findOrFail($id);
        
        return view('wilayah.show', compact('desa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $desa = WilayahDesa::findOrFail($id);
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        
        return view('wilayah.edit', compact('desa', 'kecamatans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $desa = WilayahDesa::findOrFail($id);
        
        $validated = $request->validate([
            // 'kode_desa' => ['required', 'max:20', Rule::unique('wilayah_desa')->ignore($desa->id)],
            // Assuming kode_desa is not editable as it might be primary key or linked
            'nama_desa' => 'required|string|max:255',
            'kecamatan_id' => 'required|exists:wilayah_kecamatan,id',
            'alamat_kantor' => 'nullable|string',
            'email_desa' => 'nullable|email',
            'telepon_desa' => 'nullable|string|max:50',
            'kepala_desa' => 'nullable|string|max:255',
            'status_desa' => 'required|in:1,0',
        ]);

        try {
            DB::beginTransaction();
            $desa->update($validated);
            DB::commit();

            return redirect()->route('wilayah.index')
                             ->with('success', 'Data desa berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $desa = WilayahDesa::findOrFail($id);
        
        try {
            DB::beginTransaction();
            // Check dependencies before delete?
            // For now simple delete
            $desa->delete();
            DB::commit();

            return redirect()->route('wilayah.index')
                             ->with('success', 'Data desa berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
