<?php

namespace App\Http\Controllers;

use App\Models\SarprasDesa;
use App\Models\WilayahDesa;
use Illuminate\Http\Request;

class SarprasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SarprasDesa::with('desa.kecamatan');

        if ($request->has('desa_id') && $request->desa_id != 'all') {
            $query->where('kode_desa', function($q) use ($request) {
                return WilayahDesa::where('id', $request->desa_id)->value('kode_desa');
            });
        }
        
        if ($request->has('search')) {
            $query->whereHas('desa', function($q) use ($request) {
                $q->where('nama_desa', 'like', "%{$request->search}%");
            });
        }

        $sarprases = $query->paginate(15);
        $desas = WilayahDesa::orderBy('nama_desa')->get();

        return view('sarpras.index', compact('sarprases', 'desas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Filter desa that doesn't have sarpras yet? 
        // Or just show all.
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('sarpras.create', compact('desas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'desa_id' => 'required|exists:wilayah_desa,id',
            'komputer' => 'required|integer|min:0',
            'printer' => 'required|integer|min:0',
            'internet' => 'required|integer|min:0', // assuming this is speed or count? DB says NUMBER default 0. Let's assume count or bool (1/0). Model says 'hasInternet' > 0.
            'ruang_pelayanan' => 'required|in:Ada,Tidak',
            'provider' => 'nullable|string|max:100',
        ]);

        $desa = WilayahDesa::findOrFail($request->desa_id);
        
        // Check uniqueness for 1:1 relationship
        if (SarprasDesa::where('kode_desa', $desa->kode_desa)->exists()) {
            return back()->with('error', 'Data sarpras untuk desa ini sudah ada.')->withInput();
        }
        
        $data = $validated;
        unset($data['desa_id']);
        $data['kode_desa'] = $desa->kode_desa;
        
        SarprasDesa::create($data);

        return redirect()->route('sarpras.index')
                         ->with('success', 'Data sarpras berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sarpras = SarprasDesa::with('desa')->findOrFail($id);
        return view('sarpras.show', compact('sarpras'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $sarpras = SarprasDesa::with('desa')->findOrFail($id);
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        
        return view('sarpras.edit', compact('sarpras', 'desas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sarpras = SarprasDesa::findOrFail($id);

        $validated = $request->validate([
            // 'desa_id' => 'required|exists:wilayah_desa,id', // Allow change desa? usually no.
            'komputer' => 'required|integer|min:0',
            'printer' => 'required|integer|min:0',
            'internet' => 'required|integer|min:0',
            'ruang_pelayanan' => 'required|in:Ada,Tidak',
            'provider' => 'nullable|string|max:100',
        ]);
        
        $sarpras->update($validated);

        return redirect()->route('sarpras.index')
                         ->with('success', 'Data sarpras berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sarpras = SarprasDesa::findOrFail($id);
        $sarpras->delete();

        return redirect()->route('sarpras.index')
                         ->with('success', 'Data sarpras berhasil dihapus');
    }
}
