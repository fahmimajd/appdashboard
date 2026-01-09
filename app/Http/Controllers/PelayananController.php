<?php

namespace App\Http\Controllers;

use App\Models\HeaderPelayanan;
use App\Models\WilayahDesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = HeaderPelayanan::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nomor_pelayanan', 'like', "%{$search}%")
                  ->orWhere('nomor_pengaduan', 'like', "%{$search}%");
        }

        $pelayanans = $query->orderBy('tanggal_dibuat', 'desc')->paginate(15);

        return view('pelayanan.index', compact('pelayanans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pelayanan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_pelayanan' => 'nullable|string|max:50',
            'nomor_pengaduan' => 'nullable|string|max:50',
            // At least one should be present? Or maybe separate types?
        ]);
        
        $data = $validated;
        // tanggal_dibuat handles by DB trigger or default, but Eloqueznt might want it
        // data['tanggal_dibuat'] = now(); 
        
        HeaderPelayanan::create($data);

        return redirect()->route('pelayanan.index')
                         ->with('success', 'Data pelayanan berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pelayanan = HeaderPelayanan::findOrFail($id);
        return view('pelayanan.show', compact('pelayanan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pelayanan = HeaderPelayanan::findOrFail($id);
        return view('pelayanan.edit', compact('pelayanan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pelayanan = HeaderPelayanan::findOrFail($id);

        $validated = $request->validate([
            'nomor_pelayanan' => 'nullable|string|max:50',
            'nomor_pengaduan' => 'nullable|string|max:50',
        ]);
        
        $pelayanan->update($validated);

        return redirect()->route('pelayanan.index')
                         ->with('success', 'Data pelayanan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pelayanan = HeaderPelayanan::findOrFail($id);
        $pelayanan->delete();

        return redirect()->route('pelayanan.index')
                         ->with('success', 'Data pelayanan berhasil dihapus');
    }
}
