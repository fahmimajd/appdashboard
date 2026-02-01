<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use App\Models\PetugasKecamatan;
use App\Models\PetugasDinas;
use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\Pendamping;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PetugasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Combine results from all 3 tables or filter based on request
        
        $status_aktif = $request->status_aktif ?? 'all';
        $level_akses = $request->level_akses ?? 'all';
        $search = $request->search;
        $kode_kecamatan = $request->kode_kecamatan ?? 'all';

        // Load kecamatans for filter
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();

        $petugasDesa = collect();
        $petugasKecamatan = collect();
        $petugasDinas = collect();

        // 1. Petugas Desa
        $queryDesa = Petugas::with(['desa', 'kecamatan', 'kabupaten'])
            ->select('petugas.*'); // Select main table columns
        
        // Enforce role filtering
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSupervisor()) {
             // Pendamping or Desa - Get all assigned desa codes
             $desaCodes = Pendamping::where('nik', $user->nik)
                 ->where('status_aktif', 'Aktif')
                 ->pluck('kode_desa')
                 ->filter()
                 ->toArray();
             
             if (!empty($desaCodes)) {
                 $queryDesa->whereIn('petugas.kode_desa', $desaCodes);
             }
        }

        if ($status_aktif !== 'all') {
            $queryDesa->where('petugas.status_aktif', $status_aktif);
        }
        if ($level_akses !== 'all') {
            $queryDesa->where('petugas.level_akses', $level_akses);
        }
        if ($kode_kecamatan !== 'all') {
            $queryDesa->where('petugas.kode_kecamatan', $kode_kecamatan);
        }
        if ($search) {
            $queryDesa->leftJoin('wilayah_desa', 'petugas.kode_desa', '=', 'wilayah_desa.kode_desa')
                      ->where(function($q) use ($search) {
                          $q->where('petugas.nama', 'like', "%{$search}%")
                            ->orWhere('petugas.nik', 'like', "%{$search}%")
                            ->orWhere('wilayah_desa.nama_desa', 'like', "%{$search}%");
                      });
        }
        $petugasDesa = $queryDesa->get()->map(function($item) {
            $item->jenis_label = 'Desa';
            return $item;
        });

        // 2. Petugas Kecamatan & 3. Petugas Dinas
        if (($user->isAdmin() || $user->isSupervisor()) && $level_akses == 'all') {
            // Petugas Kecamatan
            $queryKec = PetugasKecamatan::with(['kecamatan'])
                ->select('petugas_kecamatan.*');
                
            if ($status_aktif !== 'all') {
                $queryKec->where('petugas_kecamatan.status_aktif', $status_aktif);
            }
            if ($kode_kecamatan !== 'all') {
                $queryKec->where('petugas_kecamatan.kode_kecamatan', $kode_kecamatan);
            }
            if ($search) {
                $queryKec->leftJoin('wilayah_kecamatan', 'petugas_kecamatan.kode_kecamatan', '=', 'wilayah_kecamatan.kode_kecamatan')
                         ->where(function($q) use ($search) {
                             $q->where('petugas_kecamatan.nama', 'like', "%{$search}%")
                               ->orWhere('petugas_kecamatan.nik', 'like', "%{$search}%")
                               ->orWhere('wilayah_kecamatan.nama_kecamatan', 'like', "%{$search}%");
                         });
            }
            $petugasKecamatan = $queryKec->get()->map(function($item) {
                $item->jenis_label = 'Kecamatan';
                $item->level_akses = 'Kecamatan'; // Visual helper
                return $item;
            });

            // 3. Petugas Dinas
            $queryDinas = PetugasDinas::with(['kabupaten'])
                ->select('petugas_dinas.*');
                
            if ($status_aktif !== 'all') {
                $queryDinas->where('petugas_dinas.status_aktif', $status_aktif);
            }
            if ($kode_kecamatan !== 'all') {
                // Petugas Dinas does not belong to a specific kecamatan, so we exclude them if filtering by kecamatan
                $queryDinas->whereRaw('1 = 0'); 
            }
            if ($search) {
                $queryDinas->where(function($q) use ($search) {
                    $q->where('petugas_dinas.nama', 'like', "%{$search}%")
                      ->orWhere('petugas_dinas.nik', 'like', "%{$search}%");
                });
            }
            $petugasDinas = $queryDinas->get()->map(function($item) {
                $item->jenis_label = 'Dinas';
                $item->level_akses = 'Dinas'; // Visual helper
                return $item;
            });
        }
        
        // Merge results
        $petugas = $petugasDesa->concat($petugasKecamatan)->concat($petugasDinas);
        
        // Manual Pagination
        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $items = $petugas instanceof Collection ? $petugas : Collection::make($petugas);
        
        $currentItems = $items->slice(($page - 1) * $perPage, $perPage)->all();
        
        $paginatedPetugas = new LengthAwarePaginator(
            $currentItems, 
            $items->count(), 
            $perPage, 
            $page, 
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('petugas.index', ['petugas' => $paginatedPetugas, 'kecamatans' => $kecamatans]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        $kabupatens = WilayahKabupaten::orderBy('nama_kabupaten')->get();
        
        return view('petugas.create', compact('desas', 'kecamatans', 'kabupatens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:16', // Unique check depends on table
            'nama' => 'required|string|max:255',
            'nomor_ponsel' => 'nullable|string|max:20',
            'jenis_petugas' => 'required|in:1,2,3',
            'wilayah_desa_id' => 'nullable|required_if:jenis_petugas,1|exists:wilayah_desa,kode_desa',
            'wilayah_kecamatan_id' => 'nullable|required_if:jenis_petugas,2|exists:wilayah_kecamatan,kode_kecamatan',
            'wilayah_kabupaten_id' => 'nullable|required_if:jenis_petugas,3|exists:wilayah_kabupaten,kode_kabupaten',
            'status_petugas' => 'required|in:1,0',
            'level_akses' => 'nullable|required_if:jenis_petugas,1|in:0,1,2,3,4,5',
        ]);

        $status = $request->status_petugas == '1' ? 'Aktif' : 'Tidak Aktif';

        if ($request->jenis_petugas == '1') {
            $request->validate(['nik' => 'unique:petugas,nik']);
            
            $desa = WilayahDesa::where('kode_desa', $request->wilayah_desa_id)->first();
            $kecamatan = WilayahKecamatan::where('kode_kecamatan', $desa->kode_kecamatan)->first();
            
            Petugas::create([
                'nik' => $request->nik,
                'nama' => $request->nama,
                'nomor_ponsel' => $request->nomor_ponsel,
                'jenis_kelamin' => $request->jenis_kelamin ?? '-', // Fallback if missing
                'level_akses' => $request->level_akses,
                'status_aktif' => $status,
                'kode_desa' => $request->wilayah_desa_id,
                'kode_kecamatan' => $kecamatan->kode_kecamatan,
                'kode_kabupaten' => $kecamatan->kode_kabupaten,
            ]);
        } elseif ($request->jenis_petugas == '2') {
            $request->validate(['nik' => 'unique:petugas_kecamatan,nik']);
            PetugasKecamatan::create([
                 'nik' => $request->nik,
                'nama' => $request->nama,
                'nomor_ponsel' => $request->nomor_ponsel,
                'status_aktif' => $status,
                'kode_kecamatan' => $request->wilayah_kecamatan_id,
            ]);
        } elseif ($request->jenis_petugas == '3') {
            $request->validate(['nik' => 'unique:petugas_dinas,nik']);
            PetugasDinas::create([
                 'nik' => $request->nik,
                'nama' => $request->nama,
                'nomor_ponsel' => $request->nomor_ponsel,
                'status_aktif' => $status,
                'kode_kabupaten' => $request->wilayah_kabupaten_id,
            ]);
        }

        return redirect()->route('petugas.index')->with('success', 'Petugas berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // ID is NIK. We search in all 3.
        $petugas = Petugas::with(['desa.kecamatan'])->find($id);
        
        if ($petugas) {
            // Visualize as 'Desa' for the view logic
            $petugas->level_akses = 'Desa';
        } else {
            $petugas = PetugasKecamatan::with('kecamatan')->find($id);
            if (!$petugas) {
                $petugas = PetugasDinas::with('kabupaten')->find($id);
            }
        }
        
        if (!$petugas) abort(404);

        return view('petugas.show', compact('petugas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $petugas = Petugas::find($id);
        $jenis = 1;
        if (!$petugas) {
            $petugas = PetugasKecamatan::find($id);
            $jenis = 2;
        }
        if (!$petugas) {
            $petugas = PetugasDinas::find($id);
            $jenis = 3;
        }
        
        if (!$petugas) abort(404);

        $desas = WilayahDesa::orderBy('nama_desa')->get();
        $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        $kabupatens = WilayahKabupaten::orderBy('nama_kabupaten')->get();
        
        return view('petugas.edit', compact('petugas', 'desas', 'kecamatans', 'kabupatens', 'jenis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Determine type first to find record
        // Simplified update logic
        
        $petugas = Petugas::find($id);
        if ($petugas) {
             // Validate level for Petugas
             $request->validate([
                 'level_akses' => 'required|in:0,1,2,3,4,5'
             ]);

             $petugas->update([
                'nama' => $request->nama,
                'nomor_ponsel' => $request->nomor_ponsel,
                'level_akses' => $request->level_akses,
                'status_aktif' => $request->status_petugas == '1' ? 'Aktif' : 'Tidak Aktif',
             ]);
             
             // Update desa if changed? logic not fully requested but good measure:
             if ($request->wilayah_desa_id && $request->wilayah_desa_id != $petugas->kode_desa) {
                 $desa = WilayahDesa::where('kode_desa', $request->wilayah_desa_id)->first();
                 $kecamatan = WilayahKecamatan::where('kode_kecamatan', $desa->kode_kecamatan)->first();
                 $petugas->update([
                     'kode_desa' => $request->wilayah_desa_id,
                     'kode_kecamatan' => $kecamatan->kode_kecamatan,
                     'kode_kabupaten' => $kecamatan->kode_kabupaten,
                 ]);
             }
             
        } else {
            $petugas = PetugasKecamatan::find($id);
            if ($petugas) {
                $petugas->update([
                    'nama' => $request->nama,
                    'nomor_ponsel' => $request->nomor_ponsel,
                    'status_aktif' => $request->status_petugas == '1' ? 'Aktif' : 'Tidak Aktif',
                 ]);
            } else {
                $petugas = PetugasDinas::find($id);
                if ($petugas) {
                    $petugas->update([
                        'nama' => $request->nama,
                        'nomor_ponsel' => $request->nomor_ponsel,
                        'status_aktif' => $request->status_petugas == '1' ? 'Aktif' : 'Tidak Aktif',
                     ]);
                }
            }
        }

        return redirect()->route('petugas.index')->with('success', 'Data petugas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $petugas = Petugas::find($id);
        if ($petugas) {
            $petugas->delete();
        } else {
            $petugas = PetugasKecamatan::find($id);
            if ($petugas) {
                $petugas->delete();
            } else {
                $petugas = PetugasDinas::find($id);
                if ($petugas) {
                    $petugas->delete();
                }
            }
        }

        return redirect()->route('petugas.index')->with('success', 'Petugas berhasil dihapus');
    }
}
