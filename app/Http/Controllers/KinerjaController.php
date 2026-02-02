<?php

namespace App\Http\Controllers;

use App\Models\KinerjaPetugas;
use App\Models\KinerjaApprovalLog;
use App\Models\Petugas;
use App\Models\WilayahDesa;
use App\Models\Pendamping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KinerjaController extends Controller
{
    /**
     * Helper to get accessible desas based on user role
     */
    private function getAccessibleDesas()
    {
        $user = auth()->user();
        
        if ($user->isAdmin() || $user->isSupervisor()) {
            return WilayahDesa::orderBy('nama_desa')->get();
        }
        
        // Petugas: only sees their assigned desa
        if ($user->isPetugas()) {
            if ($user->kode_desa) {
                return WilayahDesa::where('kode_desa', $user->kode_desa)
                    ->orderBy('nama_desa')
                    ->get();
            }
            return collect([]);
        }
        
        // Pendamping: sees all desa assigned via pendamping table
        $desaCodes = Pendamping::where('nik', $user->nik)
            ->where('status_aktif', 'Aktif')
            ->pluck('kode_desa')
            ->filter()
            ->toArray();
            
        if (empty($desaCodes)) {
             return collect([]);
        }

        return WilayahDesa::whereIn('kode_desa', $desaCodes)
            ->orderBy('nama_desa')
            ->get();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default filter: Previous month if no filter is applied
        if (!$request->has('bulan') && !$request->has('tahun') && !$request->has('desa_id')) {
            $prev = Carbon::now()->subMonth();
            $request->merge([
                'bulan' => $prev->month,
                'tahun' => $prev->year
            ]);
        }

        $query = KinerjaPetugas::with(['petugas', 'desa.kecamatan']);

        if ($request->has('bulan') && $request->bulan != 'all') {
            $query->where('bulan', $request->bulan);
        }

        if ($request->has('tahun') && $request->tahun != 'all') {
            $query->where('tahun', $request->tahun);
        }

        // Access Control
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            // Petugas: filter by their single assigned desa
            if ($user->isPetugas()) {
                if ($user->kode_desa) {
                    $query->where('kode_desa', $user->kode_desa);
                }
            } else {
                // Pendamping: filter by all desa codes assigned to this user's NIK
                $desaCodes = Pendamping::where('nik', $user->nik)
                    ->where('status_aktif', 'Aktif')
                    ->pluck('kode_desa')
                    ->filter()
                    ->toArray();
                
                if (!empty($desaCodes)) {
                    $query->whereIn('kode_desa', $desaCodes);
                }
            }
        } elseif ($request->has('desa_id') && $request->desa_id != 'all') {
             // Admin/Supervisor can filter by desa
            $query->where('kode_desa', $request->desa_id);
        }

        $kinerjas = $query->orderBy('tahun', 'desc')
                          ->orderBy('bulan', 'desc')
                          ->select('kinerja_petugas.*') // Ensure clean selection
                          ->paginate(15)
                          ->withQueryString();
                          
        $desas = $this->getAccessibleDesas();
        
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
        $desas = $this->getAccessibleDesas();
        
        // Filter petugas based on accessible desas
        $user = auth()->user();
        $petugasQuery = Petugas::with('desa')->where('status_aktif', 'Aktif');

        if (!$user->isAdmin() && !$user->isSupervisor()) {
            $desaCodes = $desas->pluck('kode_desa')->toArray();
            $petugasQuery->whereIn('kode_desa', $desaCodes);
        }

        $petugas = $petugasQuery->orderBy('nama')->get();
        
        // Default to previous month
        $prev = Carbon::now()->subMonth();
        $defaultBulan = $prev->month;
        $defaultTahun = $prev->year;

        return view('kinerja.create', compact('petugas', 'desas', 'defaultBulan', 'defaultTahun'));
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
            'total_aktivasi_ikd' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Calculate total if not provided (though form should ideally provide it, backend calculation is safer)
        // Calculate total
        // Rule: total_pelayanan excludes ikd_desa (which is now part of total_aktivasi with aktivasi_ikd)
        // and excludes jumlah_login
        $total = 0;
        $fields = ['aktivasi_ikd', 'akta_kelahiran', 'akta_kematian', 'pengajuan_kk', 'pengajuan_pindah', 'pengajuan_kia'];
        foreach ($fields as $field) {
            $total += $validated[$field] ?? 0;
        }
        $validated['total_pelayanan'] = $total;

        // Calculate total_aktivasi_ikd - MANUAL INPUT
        if (!isset($validated['total_aktivasi_ikd'])) {
            $validated['total_aktivasi_ikd'] = 0;
        }

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
        unset($data['total_pelayanan']); // Remove total_pelayanan as it is not a column in DB

        KinerjaPetugas::create($data);

        return redirect()->route('kinerja.index')
                         ->with('success', 'Data kinerja berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kinerja = KinerjaPetugas::with(['petugas', 'desa.kecamatan'])->findOrFail($id);
        return view('kinerja.show', compact('kinerja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kinerja = KinerjaPetugas::findOrFail($id);
        $desas = $this->getAccessibleDesas();

        // Filter petugas based on accessible desas (for cases where we might change petugas, though edit UI seems locked)
        // But good to be consistent
        $user = auth()->user();
        $petugasQuery = Petugas::orderBy('nama');

        if (!$user->isAdmin() && !$user->isSupervisor()) {
            $desaCodes = $desas->pluck('kode_desa')->toArray();
            $petugasQuery->whereIn('kode_desa', $desaCodes);
        }

        $petugas = $petugasQuery->get();
        
        return view('kinerja.edit', compact('kinerja', 'petugas', 'desas'));
    }

    /**
     * Update the specified resource in storage.
     * If user is Petugas, changes go to proposed fields (pending approval).
     * If user is Pendamping/Admin/Supervisor, changes are applied directly.
     */
    public function update(Request $request, $id)
    {
        $kinerja = KinerjaPetugas::findOrFail($id);
        $user = auth()->user();

        $validated = $request->validate([
            'petugas_id' => 'required|exists:petugas,nik',
            'desa_id' => 'required|exists:wilayah_desa,kode_desa',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:'.(date('Y')+1),
            'aktivasi_ikd' => 'nullable|integer|min:0',
            'ikd_desa' => 'nullable|integer|min:0',
            'akta_kelahiran' => 'nullable|integer|min:0',
            'akta_kematian' => 'nullable|integer|min:0',
            'pengajuan_kk' => 'nullable|integer|min:0',
            'pengajuan_pindah' => 'nullable|integer|min:0',
            'pengajuan_kia' => 'nullable|integer|min:0',
            'jumlah_login' => 'nullable|integer|min:0',
            'total_aktivasi_ikd' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Duplicate check logic
        $exists = KinerjaPetugas::where('nik_petugas', $request->petugas_id)
                                ->where('bulan', $request->bulan)
                                ->where('tahun', $request->tahun)
                                ->where('id', '!=', $id)
                                ->exists();

        if ($exists) {
            return back()->with('error', 'Data kinerja untuk petugas ini pada periode tersebut sudah ada.')
                         ->withInput();
        }

        // If user is Petugas, save to proposed fields (pending approval)
        if ($user->isPetugas()) {
            $hasChanges = false;
            $approvableFields = KinerjaPetugas::$approvableFields;
            
            foreach ($approvableFields as $field) {
                $newValue = $validated[$field] ?? 0;
                $currentValue = $kinerja->$field ?? 0;
                
                // Only set proposed if value is different from current
                if ($newValue != $currentValue) {
                    $proposedField = $field . '_proposed';
                    $kinerja->$proposedField = $newValue;
                    $hasChanges = true;
                }
            }
            
            if ($hasChanges) {
                $kinerja->has_pending_approval = true;
                $kinerja->last_proposed_at = Carbon::now();
                $kinerja->last_proposed_by = $user->nik;
                $kinerja->save();
                
                return redirect()->route('kinerja.index')
                    ->with('success', 'Perubahan kinerja berhasil diajukan. Menunggu approval dari Pendamping.');
            }
            
            return redirect()->route('kinerja.index')
                ->with('info', 'Tidak ada perubahan yang diajukan.');
        }

        // Pendamping/Admin/Supervisor: Apply changes directly
        $dataToUpdate = [];
        foreach (KinerjaPetugas::$approvableFields as $field) {
            if (isset($validated[$field])) {
                $dataToUpdate[$field] = $validated[$field];
            }
        }
        
        $kinerja->update($dataToUpdate);

        return redirect()->route('kinerja.index')
                         ->with('success', 'Data kinerja berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Restriction: Petugas cannot delete kinerja
        $user = auth()->user();
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus data kinerja.');
        }

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
        $year = $request->get('tahun', date('Y'));
        
        $stats = KinerjaPetugas::selectRaw('bulan, SUM(total_pelayanan) as total')
                               ->where('tahun', $year)
                               ->groupBy('bulan')
                               ->orderBy('bulan')
                               ->get();
                               
        return view('kinerja.report', compact('stats', 'year'));
    }

    /**
     * Approve a specific field's proposed value
     */
    public function approveField(Request $request, $id)
    {
        $user = auth()->user();
        
        // Only Pendamping, Admin, Supervisor can approve
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan approval.');
        }

        $validated = $request->validate([
            'field_name' => 'required|string|in:' . implode(',', KinerjaPetugas::$approvableFields),
        ]);

        $kinerja = KinerjaPetugas::findOrFail($id);
        $fieldName = $validated['field_name'];
        $proposedField = $fieldName . '_proposed';
        
        if ($kinerja->$proposedField === null) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval untuk field ini.');
        }

        $oldValue = $kinerja->$fieldName;
        $proposedValue = $kinerja->$proposedField;

        // Create log entry
        KinerjaApprovalLog::create([
            'kinerja_id' => $kinerja->id,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'proposed_value' => $proposedValue,
            'final_value' => $proposedValue,
            'action' => 'approved',
            'proposed_by' => $kinerja->last_proposed_by,
            'action_by' => $user->nik,
        ]);

        // Apply the change
        $kinerja->$fieldName = $proposedValue;
        $kinerja->$proposedField = null;
        $kinerja->updatePendingFlag();

        $fieldLabel = KinerjaApprovalLog::$fieldLabels[$fieldName] ?? $fieldName;
        return back()->with('success', "Field {$fieldLabel} berhasil di-approve.");
    }

    /**
     * Reject a specific field's proposed value
     */
    public function rejectField(Request $request, $id)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan rejection.');
        }

        $validated = $request->validate([
            'field_name' => 'required|string|in:' . implode(',', KinerjaPetugas::$approvableFields),
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $kinerja = KinerjaPetugas::findOrFail($id);
        $fieldName = $validated['field_name'];
        $proposedField = $fieldName . '_proposed';
        
        if ($kinerja->$proposedField === null) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval untuk field ini.');
        }

        $oldValue = $kinerja->$fieldName;
        $proposedValue = $kinerja->$proposedField;

        // Create log entry for rejection
        KinerjaApprovalLog::create([
            'kinerja_id' => $kinerja->id,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'proposed_value' => $proposedValue,
            'final_value' => null, // null indicates rejected
            'action' => 'rejected',
            'proposed_by' => $kinerja->last_proposed_by,
            'action_by' => $user->nik,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        // Clear the proposed value (keep original)
        $kinerja->$proposedField = null;
        $kinerja->updatePendingFlag();

        $fieldLabel = KinerjaApprovalLog::$fieldLabels[$fieldName] ?? $fieldName;
        return back()->with('rejected', "Perubahan {$fieldLabel} ditolak. Nilai tetap menggunakan nilai lama.");
    }

    /**
     * Approve all pending fields at once
     */
    public function approveAll($id)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan approval.');
        }

        $kinerja = KinerjaPetugas::findOrFail($id);
        $pendingFields = $kinerja->getPendingFields();
        
        if (empty($pendingFields)) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval.');
        }

        foreach ($pendingFields as $fieldName) {
            $proposedField = $fieldName . '_proposed';
            $oldValue = $kinerja->$fieldName;
            $proposedValue = $kinerja->$proposedField;

            // Create log entry
            KinerjaApprovalLog::create([
                'kinerja_id' => $kinerja->id,
                'field_name' => $fieldName,
                'old_value' => $oldValue,
                'proposed_value' => $proposedValue,
                'final_value' => $proposedValue,
                'action' => 'approved',
                'proposed_by' => $kinerja->last_proposed_by,
                'action_by' => $user->nik,
            ]);

            // Apply the change
            $kinerja->$fieldName = $proposedValue;
            $kinerja->$proposedField = null;
        }

        $kinerja->has_pending_approval = false;
        $kinerja->save();

        return back()->with('success', 'Semua perubahan berhasil di-approve.');
    }

    /**
     * Reject all pending fields at once
     */
    public function rejectAll(Request $request, $id)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan rejection.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $kinerja = KinerjaPetugas::findOrFail($id);
        $pendingFields = $kinerja->getPendingFields();
        
        if (empty($pendingFields)) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval.');
        }

        foreach ($pendingFields as $fieldName) {
            $proposedField = $fieldName . '_proposed';
            $oldValue = $kinerja->$fieldName;
            $proposedValue = $kinerja->$proposedField;

            // Create log entry for rejection
            KinerjaApprovalLog::create([
                'kinerja_id' => $kinerja->id,
                'field_name' => $fieldName,
                'old_value' => $oldValue,
                'proposed_value' => $proposedValue,
                'final_value' => null,
                'action' => 'rejected',
                'proposed_by' => $kinerja->last_proposed_by,
                'action_by' => $user->nik,
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

            // Clear the proposed value
            $kinerja->$proposedField = null;
        }

        $kinerja->has_pending_approval = false;
        $kinerja->save();

        return back()->with('rejected', 'Semua perubahan ditolak. Nilai tetap menggunakan nilai lama.');
    }

    /**
     * Show page with all pending approvals for current user's desas
     */
    public function pending(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return redirect()->route('kinerja.index')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $query = KinerjaPetugas::with(['petugas', 'desa'])
            ->where('has_pending_approval', true);

        // Filter by accessible desas for Pendamping
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            $desaCodes = Pendamping::where('nik', $user->nik)
                ->where('status_aktif', 'Aktif')
                ->pluck('kode_desa')
                ->filter()
                ->toArray();
            
            if (!empty($desaCodes)) {
                $query->whereIn('kode_desa', $desaCodes);
            }
        }

        $pendingKinerjas = $query->orderBy('last_proposed_at', 'desc')
                                  ->paginate(15);

        return view('kinerja.pending', compact('pendingKinerjas'));
    }

    /**
     * Get count of pending approvals (for badge/notification)
     */
    public static function getPendingCount(): int
    {
        $user = auth()->user();
        
        if (!$user || $user->isPetugas()) {
            return 0;
        }

        $query = KinerjaPetugas::where('has_pending_approval', true);

        // Filter by accessible desas for Pendamping
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            $desaCodes = Pendamping::where('nik', $user->nik)
                ->where('status_aktif', 'Aktif')
                ->pluck('kode_desa')
                ->filter()
                ->toArray();
            
            if (!empty($desaCodes)) {
                $query->whereIn('kode_desa', $desaCodes);
            } else {
                return 0;
            }
        }

        return $query->count();
    }

    public function export(Request $request)
    {
        // Access Control: Petugas cannot export
        $user = auth()->user();
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk export data.');
        }

        $query = KinerjaPetugas::with(['petugas', 'desa.kecamatan']);

        // Apply filters (same as index)
        if ($request->has('bulan') && $request->bulan != 'all') {
            $query->where('bulan', $request->bulan);
        }

        if ($request->has('tahun') && $request->tahun != 'all') {
            $query->where('tahun', $request->tahun);
        }

        // Role-based filtering
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            // Pendamping
            $desaCodes = Pendamping::where('nik', $user->nik)
                ->where('status_aktif', 'Aktif')
                ->pluck('kode_desa')
                ->filter()
                ->toArray();
            
            if (!empty($desaCodes)) {
                $query->whereIn('kode_desa', $desaCodes);
            }
        } elseif ($request->has('desa_id') && $request->desa_id != 'all') {
            $query->where('kode_desa', $request->desa_id);
        }

        $kinerjas = $query->orderBy('tahun', 'desc')
                          ->orderBy('bulan', 'desc')
                          ->get();

        $filename = "laporan-kinerja-" . date('Y-m-d-H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($kinerjas) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Periode', 
                'Petugas', 
                'NIK Petugas', 
                'Kecamatan',
                'Desa', 
                'Aktivasi IKD', 
                'Total IKD Desa', 
                'Total Aktivasi IKD',
                'Akta Kelahiran',
                'Akta Kematian',
                'Pengajuan KK',
                'Pengajuan Pindah',
                'Pengajuan KIA',
                'Jumlah Login',
                'Total Pelayanan',
                'Status'
            ]);

            foreach ($kinerjas as $k) {
                fputcsv($file, [
                    \Carbon\Carbon::create()->month($k->bulan)->translatedFormat('F') . ' ' . $k->tahun,
                    $k->petugas->nama ?? 'Unknown',
                    '="' . ($k->petugas->nik ?? '') . '"',
                    $k->desa->kecamatan->nama_kecamatan ?? '-',
                    $k->desa->nama_desa ?? '-',
                    $k->aktivasi_ikd,
                    $k->ikd_desa,
                    $k->total_aktivasi_ikd,
                    $k->akta_kelahiran,
                    $k->akta_kematian,
                    $k->pengajuan_kk,
                    $k->pengajuan_pindah,
                    $k->pengajuan_kia,
                    $k->jumlah_login,
                    $k->total_pelayanan,
                    $k->hasPendingApproval() ? 'Pending' : 'Approved'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
