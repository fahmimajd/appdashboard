<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BelumRekam;
use App\Models\BelumRekamApprovalLog;
use App\Models\WilayahDesa;
use App\Models\WilayahKecamatan;
use App\Models\Pendamping;
use App\Models\ExportLog;
use Carbon\Carbon;


class BelumRekamController extends Controller
{
    /**
     * Helper to get allowed desa codes for current user
     */
    private function getAllowedDesaCodes()
    {
        $user = auth()->user();
        
        if ($user->isAdmin() || $user->isSupervisor()) {
            return null; // null means all desas allowed
        }
        
        if ($user->isPetugas()) {
            if ($user->kode_desa) {
                return [trim($user->kode_desa)];
            }
            return [];
        }
        
        // Pendamping
        return Pendamping::where('nik', $user->nik)
            ->where('status_aktif', 'Aktif')
            ->pluck('kode_desa')
            ->map(fn($code) => trim($code))
            ->filter()
            ->toArray();
    }

    public function index(Request $request)
    {
        $query = BelumRekam::with(['desa', 'kecamatan']);
        $user = auth()->user();
        $allowedDesaCodes = $this->getAllowedDesaCodes();

        // Access Control Logic
        if ($allowedDesaCodes !== null) {
            if (!empty($allowedDesaCodes)) {
                $paddedCodes = array_map(fn($code) => str_pad($code, 20, ' '), $allowedDesaCodes);
                $query->whereIn('kode_desa', $paddedCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lgkp', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by Kecamatan/Desa
        if ($request->kode_kecamatan) {
            $query->where('kode_kecamatan', str_pad($request->kode_kecamatan, 20, ' '));
        }
        if ($request->kode_desa) {
            $query->where('kode_desa', str_pad($request->kode_desa, 20, ' '));
        }

        // Filter by Status (WKTP)
        if ($request->status) {
            $query->where('wktp_ket', $request->status);
        }

        // Sorting by Year
        $sortTahun = $request->sort_tahun;
        if ($sortTahun === 'asc') {
            $query->orderBy('tgl_lhr', 'asc');
        } elseif ($sortTahun === 'desc') {
            $query->orderBy('tgl_lhr', 'desc');
        }

        $data = $query->paginate(10)->withQueryString();
        
        // Filter Dropdowns
        if ($allowedDesaCodes !== null) {
            // Restricted User: Show only relevant Kecamatans and Desas
            $validKecCodes = WilayahDesa::whereIn('kode_desa', $allowedDesaCodes)
                ->pluck('kode_kecamatan')
                ->unique();
            
            $kecamatans = WilayahKecamatan::whereIn('kode_kecamatan', $validKecCodes)
                ->orderBy('nama_kecamatan')
                ->get();
            
            $desas = [];
            if ($request->kode_kecamatan) {
                $desas = WilayahDesa::where('kode_kecamatan', $request->kode_kecamatan)
                    ->whereIn('kode_desa', $allowedDesaCodes)
                    ->orderBy('nama_desa')
                    ->get();
            }
        } else {
            // Admin/Supervisor: Show all
            $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
            $desas = [];
            if($request->kode_kecamatan) {
                $desas = WilayahDesa::where('kode_kecamatan', $request->kode_kecamatan)
                    ->orderBy('nama_desa')
                    ->get();
            }
        }

        return view('belum_rekam.index', compact('data', 'kecamatans', 'desas'));
    }

    public function rekapitulasi(Request $request)
    {
        $user = auth()->user();
        $allowedDesaCodes = null;
        $kecamatans = [];

        if (!$user->isAdmin() && !$user->isSupervisor()) {
            if ($user->isPetugas()) {
                // Petugas: use their assigned kode_desa from users table
                if ($user->kode_desa) {
                    $allowedDesaCodes = [trim($user->kode_desa)];
                }
            } else {
                // Pendamping: get allowed desa from pendamping table
                $allowedDesaCodes = Pendamping::where('nik', $user->nik)
                    ->where('status_aktif', 'Aktif')
                    ->pluck('kode_desa')
                    ->map(fn($code) => trim($code))
                    ->filter()
                    ->toArray();
            }
        } else {
            // Only fetch kecamatans for Admin/Supervisor
             $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
        }

        $summaryQuery = WilayahDesa::query();
        
        if ($allowedDesaCodes !== null) {
            $summaryQuery->whereIn('kode_desa', $allowedDesaCodes);
        }

        // Kecamatan Filter
        if ($request->kode_kecamatan) {
            $kodeKec = trim($request->kode_kecamatan);
            $summaryQuery->whereRaw("TRIM(kode_kecamatan) = ?", [$kodeKec]);
        }

        $summaryData = $summaryQuery->with('kecamatan')->withCount([
            'belumRekam as belum_rekam_count' => function ($query) use ($request) {
                if ($request->status) {
                    $query->where('wktp_ket', $request->status);
                }
            },
            'belumAkte as belum_akte_count'
        ])->orderBy('nama_desa')->get();

        return view('sasaran.rekapitulasi', compact('summaryData', 'kecamatans'));
    }

    public function export(Request $request)
    {
        // Access Control: Petugas cannot export
        $user = auth()->user();
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk export data.');
        }

        $query = BelumRekam::with(['desa', 'kecamatan']);
        $allowedDesaCodes = $this->getAllowedDesaCodes();

        // Role-based filtering
        if ($allowedDesaCodes !== null) {
            if (!empty($allowedDesaCodes)) {
                $paddedCodes = array_map(fn($code) => str_pad($code, 20, ' '), $allowedDesaCodes);
                $query->whereIn('kode_desa', $paddedCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply filters
        if ($request->kode_kecamatan) {
            $query->where('kode_kecamatan', str_pad($request->kode_kecamatan, 20, ' '));
        }
        if ($request->kode_desa) {
            $query->where('kode_desa', str_pad($request->kode_desa, 20, ' '));
        }
        if ($request->status) {
            $query->where('wktp_ket', $request->status);
        }

        // Sorting
        $sortTahun = $request->sort_tahun;
        if ($sortTahun === 'asc') {
            $query->orderBy('tgl_lhr', 'asc');
        } elseif ($sortTahun === 'desc') {
            $query->orderBy('tgl_lhr', 'desc');
        }

        $data = $query->get();

        // Log export activity
        ExportLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nama ?? $user->nik,
            'user_role' => $user->akses,
            'export_type' => 'belum_rekam',
            'filters' => [
                'kode_kecamatan' => $request->kode_kecamatan,
                'kode_desa' => $request->kode_desa,
                'status' => $request->status,
                'sort_tahun' => $request->sort_tahun,
            ],
            'record_count' => $data->count(),
            'ip_address' => $request->ip(),
        ]);

        $filename = "data-belum-rekam-" . date('Y-m-d-H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'No',
                'NIK',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Tempat Lahir',
                'Tanggal Lahir',
                'Kecamatan',
                'Desa',
                'Status',
                'Keterangan'
            ]);

            $no = 1;
            foreach ($data as $item) {
                fputcsv($file, [
                    $no++,
                    '="' . $item->nik . '"',
                    $item->nama_lgkp,
                    $item->jenis_klm,
                    $item->tmpt_lhr,
                    $item->tgl_lhr,
                    $item->kecamatan->nama_kecamatan ?? '-',
                    $item->desa->nama_desa ?? '-',
                    $item->wktp_ket,
                    $item->keterangan
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function edit($nik)
    {
        $user = auth()->user();
        
        // Allow Admin and Petugas to edit (Petugas changes require approval)
        if ($user->isSupervisor()) {
            return back()->with('error', 'Supervisor tidak dapat mengedit data.');
        }

        $data = BelumRekam::with(['desa', 'kecamatan'])->where('nik', $nik)->firstOrFail();
        
        // Petugas can only edit data in their desa
        if ($user->isPetugas()) {
            $userDesaPadded = str_pad(trim($user->kode_desa), 20, ' ');
            if ($data->getOriginal('kode_desa') !== $userDesaPadded) {
                return back()->with('error', 'Anda hanya dapat mengedit data di desa Anda.');
            }
        }
        
        $keteranganOptions = [
            'BLM_RKM_KTP' => 'Belum Rekam KTP',
            'SDH_RKM_KTP' => 'Sudah Rekam KTP',
        ];

        $isPetugas = $user->isPetugas();

        return view('belum_rekam.edit', compact('data', 'keteranganOptions', 'isPetugas'));
    }

    public function update(Request $request, $nik)
    {
        $user = auth()->user();
        
        if ($user->isSupervisor()) {
            return back()->with('error', 'Supervisor tidak dapat mengupdate data.');
        }

        $request->validate([
            'keterangan' => 'required|in:BLM_RKM_KTP,SDH_RKM_KTP',
        ]);

        $data = BelumRekam::where('nik', $nik)->firstOrFail();
        
        // Petugas: save to proposed fields (pending approval)
        if ($user->isPetugas()) {
            $hasChanges = false;
            
            // Check keterangan
            if ($request->keterangan !== $data->keterangan) {
                $data->keterangan_proposed = $request->keterangan;
                $hasChanges = true;
            }
            
            if ($hasChanges) {
                $data->has_pending_approval = true;
                $data->last_proposed_at = Carbon::now();
                $data->last_proposed_by = $user->nik;
                $data->save();
                
                return redirect()->route('belum_rekam.index')
                    ->with('success', 'Perubahan berhasil diajukan. Menunggu approval dari Admin/Pendamping.');
            }
            
            return redirect()->route('belum_rekam.index')
                ->with('info', 'Tidak ada perubahan yang diajukan.');
        }

        // Admin/Pendamping: Apply changes directly
        $data->update([
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('belum_rekam.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Show pending approvals page
     */
    public function pending(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return redirect()->route('belum_rekam.index')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $query = BelumRekam::with(['desa', 'kecamatan', 'proposer'])
            ->where('has_pending_approval', true);

        // Filter by accessible desas for Pendamping
        $allowedDesaCodes = $this->getAllowedDesaCodes();
        if ($allowedDesaCodes !== null) {
            if (!empty($allowedDesaCodes)) {
                $paddedCodes = array_map(fn($code) => str_pad($code, 20, ' '), $allowedDesaCodes);
                $query->whereIn('kode_desa', $paddedCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $pendingData = $query->orderBy('last_proposed_at', 'desc')
                             ->paginate(15);

        return view('belum_rekam.pending', compact('pendingData'));
    }

    /**
     * Approve a specific field
     */
    public function approveField(Request $request, $nik)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan approval.');
        }

        $validated = $request->validate([
            'field_name' => 'required|string|in:' . implode(',', BelumRekam::$approvableFields),
        ]);

        $data = BelumRekam::where('nik', $nik)->firstOrFail();
        $fieldName = $validated['field_name'];
        $proposedField = $fieldName . '_proposed';
        
        if ($data->$proposedField === null) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval untuk field ini.');
        }

        $oldValue = $data->$fieldName;
        $proposedValue = $data->$proposedField;

        // Create log entry
        BelumRekamApprovalLog::create([
            'belum_rekam_nik' => $data->nik,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'proposed_value' => $proposedValue,
            'final_value' => $proposedValue,
            'action' => 'approved',
            'proposed_by' => $data->last_proposed_by,
            'action_by' => $user->nik,
        ]);

        // Apply the change
        $data->$fieldName = $proposedValue;
        $data->$proposedField = null;
        $data->updatePendingFlag();

        $fieldLabel = BelumRekamApprovalLog::$fieldLabels[$fieldName] ?? $fieldName;
        return back()->with('success', "Field {$fieldLabel} berhasil di-approve.");
    }

    /**
     * Reject a specific field
     */
    public function rejectField(Request $request, $nik)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan rejection.');
        }

        $validated = $request->validate([
            'field_name' => 'required|string|in:' . implode(',', BelumRekam::$approvableFields),
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $data = BelumRekam::where('nik', $nik)->firstOrFail();
        $fieldName = $validated['field_name'];
        $proposedField = $fieldName . '_proposed';
        
        if ($data->$proposedField === null) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval untuk field ini.');
        }

        $oldValue = $data->$fieldName;
        $proposedValue = $data->$proposedField;

        // Create log entry for rejection
        BelumRekamApprovalLog::create([
            'belum_rekam_nik' => $data->nik,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'proposed_value' => $proposedValue,
            'final_value' => null,
            'action' => 'rejected',
            'proposed_by' => $data->last_proposed_by,
            'action_by' => $user->nik,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        // Clear the proposed value (keep original)
        $data->$proposedField = null;
        $data->updatePendingFlag();

        $fieldLabel = BelumRekamApprovalLog::$fieldLabels[$fieldName] ?? $fieldName;
        return back()->with('rejected', "Perubahan {$fieldLabel} ditolak. Nilai tetap menggunakan nilai lama.");
    }

    /**
     * Approve all pending fields
     */
    public function approveAll($nik)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan approval.');
        }

        $data = BelumRekam::where('nik', $nik)->firstOrFail();
        $pendingFields = $data->getPendingFields();
        
        if (empty($pendingFields)) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval.');
        }

        foreach ($pendingFields as $fieldName) {
            $proposedField = $fieldName . '_proposed';
            $oldValue = $data->$fieldName;
            $proposedValue = $data->$proposedField;

            BelumRekamApprovalLog::create([
                'belum_rekam_nik' => $data->nik,
                'field_name' => $fieldName,
                'old_value' => $oldValue,
                'proposed_value' => $proposedValue,
                'final_value' => $proposedValue,
                'action' => 'approved',
                'proposed_by' => $data->last_proposed_by,
                'action_by' => $user->nik,
            ]);

            $data->$fieldName = $proposedValue;
            $data->$proposedField = null;
        }

        $data->has_pending_approval = false;
        $data->save();

        return back()->with('success', 'Semua perubahan berhasil di-approve.');
    }

    /**
     * Reject all pending fields
     */
    public function rejectAll(Request $request, $nik)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan rejection.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $data = BelumRekam::where('nik', $nik)->firstOrFail();
        $pendingFields = $data->getPendingFields();
        
        if (empty($pendingFields)) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval.');
        }

        foreach ($pendingFields as $fieldName) {
            $proposedField = $fieldName . '_proposed';
            $oldValue = $data->$fieldName;
            $proposedValue = $data->$proposedField;

            BelumRekamApprovalLog::create([
                'belum_rekam_nik' => $data->nik,
                'field_name' => $fieldName,
                'old_value' => $oldValue,
                'proposed_value' => $proposedValue,
                'final_value' => null,
                'action' => 'rejected',
                'proposed_by' => $data->last_proposed_by,
                'action_by' => $user->nik,
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

            $data->$proposedField = null;
        }

        $data->has_pending_approval = false;
        $data->save();

        return back()->with('rejected', 'Semua perubahan ditolak. Nilai tetap menggunakan nilai lama.');
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

        $query = BelumRekam::where('has_pending_approval', true);

        // Filter by accessible desas for Pendamping
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            $desaCodes = Pendamping::where('nik', $user->nik)
                ->where('status_aktif', 'Aktif')
                ->pluck('kode_desa')
                ->map(fn($code) => trim($code))
                ->filter()
                ->toArray();
            
            if (!empty($desaCodes)) {
                $paddedCodes = array_map(fn($code) => str_pad($code, 20, ' '), $desaCodes);
                $query->whereIn('kode_desa', $paddedCodes);
            } else {
                return 0;
            }
        }

        return $query->count();
    }
}
