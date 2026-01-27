<?php

namespace App\Http\Controllers;

use App\Models\KependudukanSemester;
use App\Models\WilayahDesa;
use App\Models\Pendamping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KependudukanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KependudukanSemester::with('desa.kecamatan');

        if ($request->has('tahun') && $request->tahun != 'all') {
            $query->forYear($request->tahun);
        }

        if ($request->has('semester') && $request->semester != 'all') {
            $query->whereRaw('SUBSTR(kode_semester, 5, 2) = ?', [sprintf('%02d', $request->semester)]);
        }

        if ($request->has('search') && $request->search) {
             $query->leftJoin('wilayah_desa', 'kependudukan_semester.kode_desa', '=', 'wilayah_desa.kode_desa')
                   ->where('wilayah_desa.nama_desa', 'like', '%' . $request->search . '%')
                   ->select('kependudukan_semester.*');
        }

        // Access Control
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            // Get all desa codes assigned to this user's NIK
            $desaCodes = Pendamping::where('nik', $user->nik)
                ->where('status_aktif', 'Aktif')
                ->pluck('kode_desa')
                ->filter()
                ->toArray();
            
            if (!empty($desaCodes)) {
                $query->whereIn('kode_desa', $desaCodes);
            }
        } elseif ($request->has('desa_id') && $request->desa_id != 'all') {
            $query->where('kode_desa', function($q) use ($request) {
                $desa = WilayahDesa::find($request->desa_id);
                if ($desa) return $desa->kode_desa;
                return '0';
            });
        }

        $data = $query->orderBy('kode_semester', 'desc')
                      ->paginate(15)
                      ->withQueryString();
                      
        // Get list of unique years for filter
        // In Oracle we might use different syntax for distinct year
        $years = []; 
        // Simple range for now or query DB
        $currentYear = date('Y');
        for ($y = $currentYear; $y >= 2020; $y--) {
            $years[] = $y;
        }

        return view('kependudukan.index', compact('data', 'years'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('kependudukan.create', compact('desas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'desa_id' => 'required|exists:wilayah_desa,id',
            'tahun' => 'required|integer|min:2020|max:'.(date('Y')+1),
            'semester' => 'required|in:1,2',
            'jumlah_penduduk' => 'required|integer|min:0',
            'jumlah_laki' => 'required|integer|min:0',
            'jumlah_perempuan' => 'required|integer|min:0',
            'wajib_ktp' => 'nullable|integer|min:0',
            'kartu_keluarga' => 'nullable|integer|min:0',
            'akta_kelahiran_jml' => 'nullable|integer|min:0',
            'akta_kematian_jml' => 'nullable|integer|min:0',
            'kepemilikan_ktp_jml' => 'nullable|integer|min:0',
            'kepemilikan_kia_jml' => 'nullable|integer|min:0',
            'jumlah_kematian' => 'nullable|integer|min:0',
            'pindah_keluar' => 'nullable|integer|min:0',
            'status_kawin_jml' => 'nullable|integer|min:0',
        ]);

        // Construct kode_semester
        $kodeSemester = $request->tahun . sprintf('%02d', $request->semester);
        
        // Get kode_desa
        $desa = WilayahDesa::findOrFail($request->desa_id);
        
        // Check duplicate
        $exists = KependudukanSemester::where('kode_desa', $desa->kode_desa)
                                      ->where('kode_semester', $kodeSemester)
                                      ->exists();
        
        if ($exists) {
            return back()->with('error', 'Data kependudukan untuk desa dan semester ini sudah ada.')
                         ->withInput();
        }

        // Prepare data
        $data = $validated;
        unset($data['desa_id'], $data['tahun'], $data['semester']);
        $data['kode_desa'] = $desa->kode_desa;
        $data['kode_semester'] = $kodeSemester;

        // Calculate percentages
        // Calculate percentages
        if ($data['jumlah_penduduk'] > 0) {
            $data['akta_kelahiran_persen'] = isset($data['akta_kelahiran_jml']) ? round(($data['akta_kelahiran_jml'] / $data['jumlah_penduduk']) * 100, 2) : 0;
            $data['kepemilikan_kia_persen'] = isset($data['kepemilikan_kia_jml']) ? round(($data['kepemilikan_kia_jml'] / $data['jumlah_penduduk']) * 100, 2) : 0; // Assumption: based on total population or child population? Using total for now or logic needs refinement.
            $data['status_kawin_persen'] = isset($data['status_kawin_jml']) ? round(($data['status_kawin_jml'] / $data['jumlah_penduduk']) * 100, 2) : 0;
        }

        if (isset($data['jumlah_kematian']) && $data['jumlah_kematian'] > 0) {
            $data['akta_kematian_persen'] = isset($data['akta_kematian_jml']) ? round(($data['akta_kematian_jml'] / $data['jumlah_kematian']) * 100, 2) : 0;
        }

        if (isset($data['wajib_ktp']) && $data['wajib_ktp'] > 0) {
             $data['kepemilikan_ktp_persen'] = isset($data['kepemilikan_ktp_jml']) ? round(($data['kepemilikan_ktp_jml'] / $data['wajib_ktp']) * 100, 2) : 0;
        }

        KependudukanSemester::create($data);

        return redirect()->route('kependudukan.index')
                         ->with('success', 'Data kependudukan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = KependudukanSemester::with('desa.kecamatan')->findOrFail($id);
        return view('kependudukan.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = KependudukanSemester::with('desa')->findOrFail($id);
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('kependudukan.edit', compact('data', 'desas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kependudukan = KependudukanSemester::findOrFail($id);

        $validated = $request->validate([
            // 'desa_id' => 'required', // Usually allow editing data but not changing desa/semester key?
            // If we allow, we must re-check duplicates. For now assume keys are locked.
            'jumlah_penduduk' => 'required|integer|min:0',
            'jumlah_laki' => 'required|integer|min:0',
            'jumlah_perempuan' => 'required|integer|min:0',
            'wajib_ktp' => 'nullable|integer|min:0',
            'kartu_keluarga' => 'nullable|integer|min:0',
            'akta_kelahiran_jml' => 'nullable|integer|min:0',
            'akta_kematian_jml' => 'nullable|integer|min:0',
            'kepemilikan_ktp_jml' => 'nullable|integer|min:0',
            'kepemilikan_kia_jml' => 'nullable|integer|min:0',
            'jumlah_kematian' => 'nullable|integer|min:0',
            'pindah_keluar' => 'nullable|integer|min:0',
            'status_kawin_jml' => 'nullable|integer|min:0',
        ]);

        $data = $validated;
        
        // Recalculate percentages
        // Recalculate percentages
        if ($data['jumlah_penduduk'] > 0) {
            $data['akta_kelahiran_persen'] = isset($data['akta_kelahiran_jml']) ? round(($data['akta_kelahiran_jml'] / $data['jumlah_penduduk']) * 100, 2) : 0;
            $data['kepemilikan_kia_persen'] = isset($data['kepemilikan_kia_jml']) ? round(($data['kepemilikan_kia_jml'] / $data['jumlah_penduduk']) * 100, 2) : 0;
            $data['status_kawin_persen'] = isset($data['status_kawin_jml']) ? round(($data['status_kawin_jml'] / $data['jumlah_penduduk']) * 100, 2) : 0;
        }

        if (isset($data['jumlah_kematian']) && $data['jumlah_kematian'] > 0) {
            $data['akta_kematian_persen'] = isset($data['akta_kematian_jml']) ? round(($data['akta_kematian_jml'] / $data['jumlah_kematian']) * 100, 2) : 0;
        }

        if (isset($data['wajib_ktp']) && $data['wajib_ktp'] > 0) {
             $data['kepemilikan_ktp_persen'] = isset($data['kepemilikan_ktp_jml']) ? round(($data['kepemilikan_ktp_jml'] / $data['wajib_ktp']) * 100, 2) : 0;
        }

        $kependudukan->update($data);

        return redirect()->route('kependudukan.index')
                         ->with('success', 'Data kependudukan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = KependudukanSemester::findOrFail($id);
        $data->delete();

        return redirect()->route('kependudukan.index')
                         ->with('success', 'Data berhasil dihapus');
    }
}
