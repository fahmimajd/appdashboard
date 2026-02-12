<?php

namespace App\Http\Controllers;

use App\Models\KinerjaKecamatan;
use App\Models\WilayahKecamatan;
use App\Models\PetugasKecamatan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KinerjaKecamatanController extends Controller
{
    /**
     * Get accessible kecamatans for current user
     */
    private function getAccessibleKecamatans()
    {
        $user = auth()->user();

        if ($user->isAdmin() || ($user->isSupervisor() && !$user->kode_kecamatan)) {
            return WilayahKecamatan::orderBy('nama_kecamatan')->get();
        }

        if ($user->kode_kecamatan) {
            return WilayahKecamatan::where('kode_kecamatan', $user->kode_kecamatan)
                ->orderBy('nama_kecamatan')
                ->get();
        }

        return collect([]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default to current month if no filter
        if (!$request->has('bulan') && !$request->has('tahun') && !$request->has('start_date') && !$request->has('end_date')) {
            $request->merge([
                'bulan' => date('n'),
                'tahun' => date('Y'),
            ]);
        }

        $query = KinerjaKecamatan::with(['kecamatan', 'petugas']);

        // Date Filtering
        if ($request->has('start_date') && $request->start_date) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('tanggal', '<=', $request->end_date);
        }
        // Month/Year filtering if range not provided
        if ((!$request->has('start_date') || !$request->start_date) && $request->has('bulan') && $request->has('tahun')) {
            $query->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
        }

        // Access Control
        $user = auth()->user();
        if ($user->kode_kecamatan) {
            $query->where('kode_kecamatan', $user->kode_kecamatan);
        } elseif ($request->has('kode_kecamatan') && $request->kode_kecamatan != 'all') {
            $query->where('kode_kecamatan', $request->kode_kecamatan);
        }

        // Petugas Filter
        if ($request->has('petugas_id') && $request->petugas_id != 'all') {
            $query->where('petugas_id', $request->petugas_id);
        }

        $kinerjas = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();
        $kecamatans = $this->getAccessibleKecamatans();

        // Get petugas list for filter (based on accessible kecamatan)
        $petugasQuery = PetugasKecamatan::query();
        if ($user->kode_kecamatan) {
            $petugasQuery->where('kode_kecamatan', $user->kode_kecamatan);
        } elseif ($request->has('kode_kecamatan') && $request->kode_kecamatan != 'all') {
            $petugasQuery->where('kode_kecamatan', $request->kode_kecamatan);
        }
        $daft_petugas = $petugasQuery->orderBy('nama')->get();

        return view('kinerja_kecamatan.index', compact('kinerjas', 'kecamatans', 'daft_petugas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kecamatans = $this->getAccessibleKecamatans();
        $today = date('Y-m-d');
        
        $user = auth()->user();
        $petugasQuery = PetugasKecamatan::query()->where('status_aktif', 'Aktif');
        
        if ($user->kode_kecamatan) {
            $petugasQuery->where('kode_kecamatan', $user->kode_kecamatan);
        }
        
        $daft_petugas = $petugasQuery->orderBy('nama')->get();

        return view('kinerja_kecamatan.create', compact('kecamatans', 'daft_petugas', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kecamatan' => 'required|exists:wilayah_kecamatan,kode_kecamatan',
            'petugas_id' => 'required|exists:petugas_kecamatan,nik',
            'tanggal' => 'required|date',
            'rekam_ktp_el' => 'nullable|integer|min:0',
            'cetak_ktp_el' => 'nullable|integer|min:0',
            'kartu_keluarga' => 'nullable|integer|min:0',
            'kia' => 'nullable|integer|min:0',
            'pindah' => 'nullable|integer|min:0',
            'kedatangan' => 'nullable|integer|min:0',
            'akta_kelahiran' => 'nullable|integer|min:0',
            'akta_kematian' => 'nullable|integer|min:0',
            'stok_blangko_ktp' => 'nullable|integer|min:0',
            'stok_blangko_kia' => 'nullable|integer|min:0',
            'persentase_ribbon' => 'nullable|numeric|between:0,100',
            'persentase_film' => 'nullable|numeric|between:0,100',
            'ikd_hari_ini' => 'nullable|integer|min:0',
        ]);

        // Check for duplicate entry for same petugas on same day
        $exists = KinerjaKecamatan::where('petugas_id', $validated['petugas_id'])
            ->where('tanggal', $validated['tanggal'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Laporan kinerja untuk petugas ini pada tanggal tersebut sudah ada.')
                ->withInput();
        }

        // Fill defaults with 0 if null
        $fields = [
            'rekam_ktp_el', 'cetak_ktp_el', 'kartu_keluarga', 'kia', 'pindah', 'kedatangan',
            'akta_kelahiran', 'akta_kematian', 'stok_blangko_ktp', 'stok_blangko_kia',
            'persentase_ribbon', 'persentase_film', 'ikd_hari_ini'
        ];
        foreach ($fields as $field) {
            $validated[$field] = $validated[$field] ?? 0;
        }


        DB::transaction(function() use ($validated) {
            $kinerja = KinerjaKecamatan::create($validated);

            // Automatic Stock Reduction
            $autoItems = \App\Models\MasterBarang::where('auto_kurang', true)->get();
            foreach ($autoItems as $item) {
                if ($item->field_kinerja && isset($validated[$item->field_kinerja]) && $validated[$item->field_kinerja] > 0) {
                    $qty = $validated[$item->field_kinerja];
                    
                    // Update Stock
                    $stok = \App\Models\StokBarang::firstOrNew([
                        'barang_id' => $item->id,
                        'lokasi_tipe' => 'kecamatan',
                        'kode_kecamatan' => $validated['kode_kecamatan'],
                    ]);
                    $stok->jumlah = ($stok->exists ? $stok->jumlah : 0) - $qty;
                    $stok->save();

                    // Log Mutasi
                    \App\Models\MutasiBarang::create([
                        'barang_id' => $item->id,
                        'tanggal' => $validated['tanggal'],
                        'tipe_mutasi' => 'pemakaian',
                        'jumlah' => -$qty,
                        'lokasi_asal_tipe' => 'kecamatan',
                        'lokasi_asal_kecamatan' => $validated['kode_kecamatan'],
                        'referensi_id' => $kinerja->id,
                        'keterangan' => 'Otomatis dari Laporan Kinerja',
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('kinerja-kecamatan.index')
            ->with('success', 'Laporan kinerja berhasil disimpan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kinerja = KinerjaKecamatan::findOrFail($id);
        $kecamatans = $this->getAccessibleKecamatans();
        
        // Ensure user can edit this
        $user = auth()->user();
        if ($user->kode_kecamatan && $user->kode_kecamatan != $kinerja->kode_kecamatan) {
            return redirect()->route('kinerja-kecamatan.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit data ini.');
        }

        $petugasQuery = PetugasKecamatan::query(); // In case inactive petugas needs to be shown
        if ($user->kode_kecamatan) {
            $petugasQuery->where('kode_kecamatan', $user->kode_kecamatan);
        }
        $daft_petugas = $petugasQuery->orderBy('nama')->get();

        return view('kinerja_kecamatan.edit', compact('kinerja', 'kecamatans', 'daft_petugas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kinerja = KinerjaKecamatan::findOrFail($id);
        
        $validated = $request->validate([
            'kode_kecamatan' => 'required|exists:wilayah_kecamatan,kode_kecamatan',
            'petugas_id' => 'required|exists:petugas_kecamatan,nik',
            'tanggal' => 'required|date',
            'rekam_ktp_el' => 'nullable|integer|min:0',
            'cetak_ktp_el' => 'nullable|integer|min:0',
            'kartu_keluarga' => 'nullable|integer|min:0',
            'kia' => 'nullable|integer|min:0',
            'pindah' => 'nullable|integer|min:0',
            'kedatangan' => 'nullable|integer|min:0',
            'akta_kelahiran' => 'nullable|integer|min:0',
            'akta_kematian' => 'nullable|integer|min:0',
            'stok_blangko_ktp' => 'nullable|integer|min:0',
            'stok_blangko_kia' => 'nullable|integer|min:0',
            'persentase_ribbon' => 'nullable|numeric|between:0,100',
            'persentase_film' => 'nullable|numeric|between:0,100',
            'ikd_hari_ini' => 'nullable|integer|min:0',
        ]);

        // Access check
        $user = auth()->user();
        if ($user->kode_kecamatan && $user->kode_kecamatan != $validated['kode_kecamatan']) {
             return back()->with('error', 'Anda mengubah kecamatan ke wilayah yang tidak Anda miliki.');
        }

        // Duplicate check (exclude current id)
        $exists = KinerjaKecamatan::where('petugas_id', $validated['petugas_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Laporan kinerja untuk petugas ini pada tanggal tersebut sudah ada.')
                ->withInput();
        }


        DB::transaction(function() use ($kinerja, $validated) {
            $original = $kinerja->replicate();
            $kinerja->update($validated);

            // Automatic Stock Adjustment
            $autoItems = \App\Models\MasterBarang::where('auto_kurang', true)->get();
            foreach ($autoItems as $item) {
                $field = $item->field_kinerja;
                if ($field && isset($validated[$field])) {
                    $oldVal = $original->$field ?? 0;
                    $newVal = $validated[$field];
                    $diff = $newVal - $oldVal;

                    if ($diff != 0) {
                        // Update Stock
                        $stok = \App\Models\StokBarang::firstOrNew([
                            'barang_id' => $item->id,
                            'lokasi_tipe' => 'kecamatan',
                            'kode_kecamatan' => $kinerja->kode_kecamatan,
                        ]);
                        $stok->jumlah = ($stok->exists ? $stok->jumlah : 0) - $diff;
                        $stok->save();

                        // Log Mutasi
                        \App\Models\MutasiBarang::create([
                            'barang_id' => $item->id,
                            'tanggal' => $validated['tanggal'],
                            'tipe_mutasi' => 'penyesuaian',
                            'jumlah' => -$diff,
                            'lokasi_asal_tipe' => 'kecamatan',
                            'lokasi_asal_kecamatan' => $kinerja->kode_kecamatan,
                            'referensi_id' => $kinerja->id,
                            'keterangan' => 'Koreksi Laporan Kinerja',
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('kinerja-kecamatan.index')
            ->with('success', 'Laporan kinerja berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kinerja = KinerjaKecamatan::findOrFail($id);
        
        // Access Check
        $user = auth()->user();
        if ($user->kode_kecamatan && $user->kode_kecamatan != $kinerja->kode_kecamatan) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus data ini.');
        }


        DB::transaction(function() use ($kinerja) {
            // Restore Stock
            $autoItems = \App\Models\MasterBarang::where('auto_kurang', true)->get();
            foreach ($autoItems as $item) {
                $field = $item->field_kinerja;
                if ($field && $kinerja->$field > 0) {
                    $qty = $kinerja->$field;

                    // Update Stock
                    $stok = \App\Models\StokBarang::firstOrNew([
                        'barang_id' => $item->id,
                        'lokasi_tipe' => 'kecamatan',
                        'kode_kecamatan' => $kinerja->kode_kecamatan,
                    ]);
                    $stok->jumlah = ($stok->exists ? $stok->jumlah : 0) + $qty;
                    $stok->save();

                    // Log Mutasi
                    \App\Models\MutasiBarang::create([
                        'barang_id' => $item->id,
                        'tanggal' => now(), // Use current date for deletion log
                        'tipe_mutasi' => 'koreksi',
                        'jumlah' => $qty,
                        'lokasi_asal_tipe' => 'kecamatan',
                        'lokasi_asal_kecamatan' => $kinerja->kode_kecamatan,
                        'referensi_id' => $kinerja->id,
                        'keterangan' => 'Hapus Laporan Kinerja',
                        'user_id' => auth()->id(),
                    ]);
                }
            }
            
            $kinerja->delete();
        });

        return redirect()->route('kinerja-kecamatan.index')
            ->with('success', 'Laporan kinerja berhasil dihapus.');
    }

    /**
     * Display aggregated report (Recapitulation)
     */
    public function rekap(Request $request)
    {
        // Default to current month
        if (!$request->has('bulan') && !$request->has('tahun') && !$request->has('start_date') && !$request->has('end_date')) {
             $request->merge([
                'bulan' => date('n'),
                'tahun' => date('Y'),
            ]);
        }

        $user = auth()->user();
        $kecamatans = $this->getAccessibleKecamatans();

        // Determine Kecamatan Filter
        $kodeKecamatan = $request->kode_kecamatan;
        if ($user->kode_kecamatan) {
            $kodeKecamatan = $user->kode_kecamatan;
        }

        // --- FILTER LOGIC ---
        $queryBase = KinerjaKecamatan::query();
        if ($kodeKecamatan && $kodeKecamatan != 'all') {
             $queryBase->where('kode_kecamatan', $kodeKecamatan);
        }

        if ($request->start_date && $request->end_date) {
            $queryBase->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->bulan && $request->tahun) {
            $queryBase->whereMonth('tanggal', $request->bulan)
                      ->whereYear('tanggal', $request->tahun);
        }

        // --- REKAP PER PETUGAS (TAB 1) ---
        // Sum fields grouped by petugas
        $rekapPetugas = (clone $queryBase)
            ->with('petugas')
            ->selectRaw('
                petugas_id,
                SUM(rekam_ktp_el) as total_rekam,
                SUM(cetak_ktp_el) as total_cetak,
                SUM(kartu_keluarga) as total_kk,
                SUM(kia) as total_kia,
                SUM(pindah) as total_pindah,
                SUM(kedatangan) as total_kedatangan,
                SUM(akta_kelahiran) as total_akta_lahir,
                SUM(akta_kematian) as total_akta_mati,
                SUM(ikd_hari_ini) as total_ikd
            ')
            ->groupBy('petugas_id')
            ->get();

        // --- REKAP PER KECAMATAN (TAB 2) ---
        // Sum fields grouped by kecamatan
        $rekapKecamatan = (clone $queryBase)
            ->with('kecamatan')
            ->selectRaw('
                kode_kecamatan,
                SUM(rekam_ktp_el) as total_rekam,
                SUM(cetak_ktp_el) as total_cetak,
                SUM(kartu_keluarga) as total_kk,
                SUM(kia) as total_kia,
                SUM(pindah) as total_pindah,
                SUM(kedatangan) as total_kedatangan,
                SUM(akta_kelahiran) as total_akta_lahir,
                SUM(akta_kematian) as total_akta_mati,
                SUM(ikd_hari_ini) as total_ikd,
                AVG(persentase_ribbon) as avg_ribbon,
                AVG(persentase_film) as avg_film
            ')
            ->groupBy('kode_kecamatan')
            ->get();

        return view('kinerja_kecamatan.rekap', compact('rekapPetugas', 'rekapKecamatan', 'kecamatans'));
    }
}
