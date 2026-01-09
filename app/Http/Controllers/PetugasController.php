<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use App\Models\PetugasKecamatan;
use App\Models\PetugasDinas;
use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
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
        // For simplicity and typical use, we might return a merged collection or just one type if filtered.
        // Given the UI likely expects a list, we'll try to merge or handle 'jenis_petugas' filter strictly.
        
        $jenis_petugas = $request->jenis_petugas ?? 'all';
        $level_akses = $request->level_akses ?? 'all';
        $search = $request->search;

        $petugasDesa = collect();
        $petugasKecamatan = collect();
        $petugasDinas = collect();

        if ($jenis_petugas == 'all' || $jenis_petugas == '1') {
            $query = Petugas::with(['desa', 'kecamatan', 'kabupaten']);
            
            if ($level_akses !== 'all') {
                $query->where('level_akses', $level_akses);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%")
                      ->orWhereHas('desa', function($qDesa) use ($search) {
                          $qDesa->where('nama_desa', 'like', "%{$search}%");
                      });
                });
            }
            $petugasDesa = $query->get()->map(function($item) {
                $item->jenis_label = 'Desa';
                return $item;
            });
        }

        // Kecamatan and Dinas don't have level_akses column, so we exclude them if a level filter is active
        if (($jenis_petugas == 'all' || $jenis_petugas == '2') && $level_akses == 'all') {
            $query = PetugasKecamatan::with(['kecamatan']);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%")
                      ->orWhereHas('kecamatan', function($qKec) use ($search) {
                          $qKec->where('nama_kecamatan', 'like', "%{$search}%");
                      });
                });
            }
            $petugasKecamatan = $query->get()->map(function($item) {
                $item->jenis_label = 'Kecamatan';
                // Mock missing relations for uniform view if needed
                return $item;
            });
        }

        if (($jenis_petugas == 'all' || $jenis_petugas == '3') && $level_akses == 'all') {
            $query = PetugasDinas::with(['kabupaten']);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }
            $petugasDinas = $query->get()->map(function($item) {
                $item->jenis_label = 'Dinas';
                return $item;
            });
        }

        // Merge and paginate manual? Or just return view with separate collections?
        // Let's merge for now, but pagination will be tricky. 
        // Real implementation should probably refine this, but for now we sync structure.
        $petugas = $petugasDesa->concat($petugasKecamatan)->concat($petugasDinas);
        
        // Manual pagination wrapper if view expects it, or just pass collection if view handles it.
        // Assuming view uses $petugas->links(), we need a Paginator.
        // For this task, I will stick to basic functionality.
        
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

        return view('petugas.index', ['petugas' => $paginatedPetugas]);
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
        $petugas = Petugas::find($id);
        if (!$petugas) $petugas = PetugasKecamatan::find($id);
        if (!$petugas) $petugas = PetugasDinas::find($id);
        
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
