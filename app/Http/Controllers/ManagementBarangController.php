<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\StokBarang;
use App\Models\MutasiBarang;
use App\Models\WilayahKecamatan;
use App\Models\KinerjaKecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagementBarangController extends Controller
{
    /**
     * Dashboard: Stock Overview & Alerts
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $kecamatans = WilayahKecamatan::orderBy('kode_kecamatan')->get();
        $masterBarang = MasterBarang::orderBy('nama')->get();

        // Filter Logic
        $kodeKecamatan = $request->kode_kecamatan;
        if ($user->kode_kecamatan) {
            $kodeKecamatan = $user->kode_kecamatan;
        }

        // Get Stock Data
        $stokQuery = StokBarang::with(['barang', 'kecamatan']);
        
        if ($kodeKecamatan && $kodeKecamatan != 'all') {
            $stokQuery->where('kode_kecamatan', $kodeKecamatan);
        } elseif ($kodeKecamatan != 'all') {
            // If admin viewing all, maybe group by barang or show huge list? 
            // Let's show list of kecamatans with low stock priority
        }

        $stoks = $stokQuery->get();

        // Get latest Kinerja for reconciliation
        // We need the latest 'stok_blangko_ktp' and 'stok_blangko_kia' reported by each kecamatan
        // Efficient way: Subquery or iterate
        
        $stockComparison = [];

        // Prepare data structure for view
        // If specific kecamatan selected (or user is kecamatan)
        if ($kodeKecamatan && $kodeKecamatan != 'all') {
            $kecamatan = WilayahKecamatan::where('kode_kecamatan', $kodeKecamatan)->first();
            
            foreach ($masterBarang as $barang) {
                $sysStock = $stoks->where('barang_id', $barang->id)->first();
                $sysStockVal = $sysStock ? $sysStock->jumlah : 0;
                
                $comparison = [
                    'barang' => $barang,
                    'stok_sistem' => $sysStockVal,
                    'stok_laporan' => null,
                    'selisih' => null,
                    'last_report_date' => null,
                ];

                if ($barang->field_stok_laporan) {
                     $lastKinerja = KinerjaKecamatan::where('kode_kecamatan', $kodeKecamatan)
                        ->orderBy('tanggal', 'desc')
                        ->first();
                     
                     if ($lastKinerja) {
                         $comparison['stok_laporan'] = $lastKinerja->{$barang->field_stok_laporan};
                         $comparison['selisih'] = $sysStockVal - $comparison['stok_laporan'];
                         $comparison['last_report_date'] = $lastKinerja->tanggal;
                     }
                }

                $stockComparison[] = $comparison;
            }
            
            return view('management_barang.dashboard_kecamatan', compact('stockComparison', 'kecamatan', 'kecamatans'));
        } 
        
        // Admin View: Table of all kecamatans
        // We need a summary table: Kecamatan | Blangko KTP (Sis/Lap/Sel) | Blangko KIA (Sis/Lap/Sel) | Ribbon %
        
        $dashboardData = [];
        // Pre-fetch latest kinerja for all kecamatans to minimize queries
        // This subquery gets the ID of latest kinerja per kecamatan
        $latestKinerjaIds = KinerjaKecamatan::selectRaw('MAX(id) as id')
            ->groupBy('kode_kecamatan')
            ->pluck('id');
            
        $latestKinerjas = KinerjaKecamatan::whereIn('id', $latestKinerjaIds)
            ->get()
            ->keyBy('kode_kecamatan');

        // Pre-fetch stocks
        $allStocks = StokBarang::whereNotNull('kode_kecamatan')->get();
        // Index stocks by [kode_kecamatan][barang_kode]
        $stockMap = [];
        foreach ($allStocks as $s) {
            $stockMap[$s->kode_kecamatan][$s->barang->kode] = $s->jumlah;
        }
        
        // Dinas Stocks
        $dinasStocks = StokBarang::whereNull('kode_kecamatan')->with('barang')->get();

        foreach ($kecamatans as $kec) {
            $data = [
                'kecamatan' => $kec,
                'ktp' => [
                    'sistem' => $stockMap[$kec->kode_kecamatan]['BLK_KTP'] ?? 0,
                    'laporan' => $latestKinerjas[$kec->kode_kecamatan]->stok_blangko_ktp ?? null,
                    'date' => $latestKinerjas[$kec->kode_kecamatan]->tanggal ?? null,
                ],
                'kia' => [
                    'sistem' => $stockMap[$kec->kode_kecamatan]['BLK_KIA'] ?? 0,
                    'laporan' => $latestKinerjas[$kec->kode_kecamatan]->stok_blangko_kia ?? null,
                    'date' => $latestKinerjas[$kec->kode_kecamatan]->tanggal ?? null,
                ],
                // Ribbon/Film from Percentage
                'ribbon_persen' => $latestKinerjas[$kec->kode_kecamatan]->persentase_ribbon ?? null,
                'film_persen' => $latestKinerjas[$kec->kode_kecamatan]->persentase_film ?? null,
            ];
            
            // Calc selisih
            if ($data['ktp']['laporan'] !== null) {
                $data['ktp']['selisih'] = $data['ktp']['sistem'] - $data['ktp']['laporan'];
            }
            if ($data['kia']['laporan'] !== null) {
                $data['kia']['selisih'] = $data['kia']['sistem'] - $data['kia']['laporan'];
            }

            $dashboardData[] = $data;
        }

        return view('management_barang.dashboard_admin', compact('dashboardData', 'dinasStocks', 'kecamatans'));
    }

    /**
     * Master Barang CRUD (Admin)
     */
    public function masterIndex()
    {
        $barangs = MasterBarang::all();
        return view('management_barang.master', compact('barangs'));
    }

    public function masterStore(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:master_barang,kode',
            'nama' => 'required',
            'satuan' => 'required',
            'kategori' => 'required',
            'auto_kurang' => 'boolean',
            'field_kinerja' => 'nullable|string',
            'field_stok_laporan' => 'nullable|string',
        ]);

        MasterBarang::create($validated);
        return back()->with('success', 'Master barang berhasil ditambahkan');
    }

    public function masterUpdate(Request $request, $id)
    {
        $barang = MasterBarang::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'required',
            'satuan' => 'required',
            'kategori' => 'required',
            'auto_kurang' => 'boolean',
            'field_kinerja' => 'nullable|string',
            'field_stok_laporan' => 'nullable|string',
        ]);
        
        $barang->update($validated);
        return back()->with('success', 'Master barang berhasil diperbarui');
    }

    public function masterDestroy($id)
    {
        MasterBarang::findOrFail($id)->delete();
        return back()->with('success', 'Master barang berhasil dihapus');
    }

    /**
     * Stok Masuk (Dinas)
     */
    public function stokMasuk()
    {
        $barangs = MasterBarang::all();
        return view('management_barang.stok_masuk', compact('barangs'));
    }

    public function storeStokMasuk(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:master_barang,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function() use ($validated) {
            // Update/Create Stok Dinas
            $stok = StokBarang::firstOrNew([
                'barang_id' => $validated['barang_id'],
                'lokasi_tipe' => 'dinas',
                'kode_kecamatan' => null,
            ]);
            $stok->jumlah = ($stok->exists ? $stok->jumlah : 0) + $validated['jumlah'];
            $stok->save();

            // Log Mutasi
            MutasiBarang::create([
                'barang_id' => $validated['barang_id'],
                'tanggal' => $validated['tanggal'],
                'tipe_mutasi' => 'masuk',
                'jumlah' => $validated['jumlah'],
                'lokasi_tujuan_tipe' => 'dinas',
                'keterangan' => $validated['keterangan'],
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('management-barang.dashboard')->with('success', 'Stok masuk berhasil dicatat');
    }

    /**
     * Distribusi Barang (Dinas to Kecamatan)
     */
    public function distribusi()
    {
        $barangs = MasterBarang::all();
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        
        // Stok dinas available
        $stokDinas = StokBarang::where('lokasi_tipe', 'dinas')->get()->keyBy('barang_id');

        return view('management_barang.distribusi', compact('barangs', 'kecamatans', 'stokDinas'));
    }

    public function storeDistribusi(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:master_barang,id',
            'kode_kecamatan' => 'required|exists:wilayah_kecamatan,kode_kecamatan',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function() use ($validated) {
            // Check Stok Dinas
            $stokDinas = StokBarang::where('barang_id', $validated['barang_id'])
                ->where('lokasi_tipe', 'dinas')
                ->first();

            if (!$stokDinas || $stokDinas->jumlah < $validated['jumlah']) {
                throw new \Exception('Stok Dinas tidak mencukupi untuk distribusi ini.');
            }

            // Kurangi Stok Dinas
            $stokDinas->decrement('jumlah', $validated['jumlah']);

            // Tambah Stok Kecamatan
            $stokKec = StokBarang::firstOrNew([
                'barang_id' => $validated['barang_id'],
                'lokasi_tipe' => 'kecamatan',
                'kode_kecamatan' => $validated['kode_kecamatan'],
            ]);
            $stokKec->jumlah = ($stokKec->exists ? $stokKec->jumlah : 0) + $validated['jumlah'];
            $stokKec->save();

            // Log Mutasi
            MutasiBarang::create([
                'barang_id' => $validated['barang_id'],
                'tanggal' => $validated['tanggal'],
                'tipe_mutasi' => 'distribusi',
                'jumlah' => $validated['jumlah'],
                'lokasi_asal_tipe' => 'dinas',
                'lokasi_tujuan_tipe' => 'kecamatan',
                'lokasi_tujuan_kecamatan' => $validated['kode_kecamatan'],
                'keterangan' => $validated['keterangan'],
                'user_id' => auth()->id(),
            ]);
        });
        
        return redirect()->route('management-barang.dashboard')->with('success', 'Distribusi barang berhasil dicatat');
    }

    /**
     * Riwayat Mutasi
     */
    public function riwayat(Request $request)
    {
        $query = MutasiBarang::with(['barang', 'user', 'asalKecamatan', 'tujuanKecamatan']);
        
        if ($request->barang_id) {
            $query->where('barang_id', $request->barang_id);
        }
        if ($request->start_date) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('tanggal', '<=', $request->end_date);
        }
        
        $mutasis = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->paginate(20);
        $barangs = MasterBarang::all();

        return view('management_barang.riwayat', compact('mutasis', 'barangs'));
    }
}
