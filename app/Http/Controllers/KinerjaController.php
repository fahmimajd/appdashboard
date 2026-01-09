<?php

namespace App\Http\Controllers;

use App\Models\KinerjaPetugas;
use App\Models\Petugas;
use App\Models\WilayahDesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KinerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KinerjaPetugas::with(['petugas', 'desa']);

        if ($request->has('bulan') && $request->bulan != 'all') {
            $query->where('bulan', $request->bulan);
        }

        if ($request->has('tahun') && $request->tahun != 'all') {
            $query->where('tahun', $request->tahun);
        }

        if ($request->has('desa_id') && $request->desa_id != 'all') {
            $query->where('desa_id', $request->desa_id);
        }

        $kinerjas = $query->orderBy('tahun', 'desc')
                          ->orderBy('bulan', 'desc')
                          ->paginate(15);
                          
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        
        return view('kinerja.index', compact('kinerjas', 'desas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $petugas = Petugas::with('desa')->where('status_aktif', 'Aktif')->orderBy('nama')->get();
        // Desa list not strictly needed for the new locked logic, but kept if fallback needed
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        
        return view('kinerja.create', compact('petugas', 'desas'));
    }
    
    /**
     * Alias for create (as user requested 'input')
     */
    public function input()
    {
        return $this->create();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'petugas_id' => 'required|exists:petugas,nik',
            'desa_id' => 'required|exists:wilayah_desa,kode_desa',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:'.(date('Y')+1),
            // Specific columns
            'aktivasi_ikd' => 'nullable|integer|min:0',
            'ikd_desa' => 'nullable|integer|min:0',
            'akta_kelahiran' => 'nullable|integer|min:0',
            'akta_kematian' => 'nullable|integer|min:0',
            'pengajuan_kk' => 'nullable|integer|min:0',
            'pengajuan_pindah' => 'nullable|integer|min:0',
            'pengajuan_kia' => 'nullable|integer|min:0',
            'jumlah_login' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Calculate total if not provided (though form should ideally provide it, backend calculation is safer)
        $total = 0;
        $fields = ['aktivasi_ikd', 'ikd_desa', 'akta_kelahiran', 'akta_kematian', 'pengajuan_kk', 'pengajuan_pindah', 'pengajuan_kia'];
        foreach ($fields as $field) {
            $total += $validated[$field] ?? 0;
        }
        $validated['total_pelayanan'] = $total;

        // Check if data already exists for this petugas+bulan+tahun
        $exists = KinerjaPetugas::where('nik_petugas', $request->petugas_id)
                                ->where('bulan', $request->bulan)
                                ->where('tahun', $request->tahun)
                                ->exists();
        
        if ($exists) {
            return back()->with('error', 'Data kinerja untuk petugas ini pada periode tersebut sudah ada.')
                         ->withInput();
        }

        // Rename petugas_id to nik_petugas for generic Create if model uses different name
        // Checking model KinerjaPetugas... db.md says 'nik_petugas', 'kode_desa'
        // Map request fields to DB columns
        $data = $validated;
        $data['nik_petugas'] = $validated['petugas_id'];
        $data['kode_desa'] = $validated['desa_id'];
        unset($data['petugas_id'], $data['desa_id']); // Remove alias keys

        KinerjaPetugas::create($data);

        return redirect()->route('kinerja.index')
                         ->with('success', 'Data kinerja berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kinerja = KinerjaPetugas::with(['petugas', 'desa'])->findOrFail($id);
        return view('kinerja.show', compact('kinerja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kinerja = KinerjaPetugas::findOrFail($id);
        $petugas = Petugas::orderBy('nama')->get();
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        
        return view('kinerja.edit', compact('kinerja', 'petugas', 'desas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kinerja = KinerjaPetugas::findOrFail($id);

        $validated = $request->validate([
            'petugas_id' => 'required|exists:petugas,nik',
            'desa_id' => 'required|exists:wilayah_desa,kode_desa',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:'.(date('Y')+1),
            // Specific columns
            'aktivasi_ikd' => 'nullable|integer|min:0',
            'ikd_desa' => 'nullable|integer|min:0',
            'akta_kelahiran' => 'nullable|integer|min:0',
            'akta_kematian' => 'nullable|integer|min:0',
            'pengajuan_kk' => 'nullable|integer|min:0',
            'pengajuan_pindah' => 'nullable|integer|min:0',
            'pengajuan_kia' => 'nullable|integer|min:0',
            'jumlah_login' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Calculate total
        $total = 0;
        $fields = ['aktivasi_ikd', 'ikd_desa', 'akta_kelahiran', 'akta_kematian', 'pengajuan_kk', 'pengajuan_pindah', 'pengajuan_kia'];
        foreach ($fields as $field) {
            $total += $validated[$field] ?? 0;
        }
        $validated['total_pelayanan'] = $total;

        // Duplicate check logic
        $exists = KinerjaPetugas::where('petugas_id', $request->petugas_id)
                                ->where('bulan', $request->bulan)
                                ->where('tahun', $request->tahun)
                                ->where('id', '!=', $id)
                                ->exists();

        if ($exists) {
            return back()->with('error', 'Data kinerja untuk petugas ini pada periode tersebut sudah ada.')
                         ->withInput();
        }

        $kinerja->update($validated);

        return redirect()->route('kinerja.index')
                         ->with('success', 'Data kinerja berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kinerja = KinerjaPetugas::findOrFail($id);
        $kinerja->delete();

        return redirect()->route('kinerja.index')
                         ->with('success', 'Data kinerja berhasil dihapus');
    }

    /**
     * Generate report/statistics
     */
    public function report(Request $request)
    {
        // Example report logic
        $year = $request->get('tahun', date('Y'));
        
        $stats = KinerjaPetugas::selectRaw('bulan, SUM(total_pelayanan) as total')
                               ->where('tahun', $year)
                               ->groupBy('bulan')
                               ->orderBy('bulan')
                               ->get();
                               
        return view('kinerja.report', compact('stats', 'year'));
    }
}
