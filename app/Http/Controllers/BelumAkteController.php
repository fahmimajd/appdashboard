<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BelumAkte;
use App\Models\BelumAkteApprovalLog;
use App\Models\WilayahDesa;
use App\Models\WilayahKecamatan;
use App\Models\Pendamping;
use App\Models\ExportLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class BelumAkteController extends Controller
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
            ->filter()
            ->toArray();
    }

    public function index(Request $request)
    {
        $query = BelumAkte::with(['desa', 'kecamatan']);
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
            $kecamatans = WilayahKecamatan::orderBy('nama_kecamatan')->get();
            $desas = [];
            if($request->kode_kecamatan) {
                $desas = WilayahDesa::where('kode_kecamatan', $request->kode_kecamatan)
                    ->orderBy('nama_desa')
                    ->get();
            }
        }

        return view('belum_akte.index', compact('data', 'kecamatans', 'desas'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        if ($user->isPetugas()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk export data.');
        }

        $query = BelumAkte::with(['desa', 'kecamatan']);
        $allowedDesaCodes = $this->getAllowedDesaCodes();

        if ($allowedDesaCodes !== null) {
            if (!empty($allowedDesaCodes)) {
                $paddedCodes = array_map(fn($code) => str_pad($code, 20, ' '), $allowedDesaCodes);
                $query->whereIn('kode_desa', $paddedCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->kode_kecamatan) {
            $query->where('kode_kecamatan', str_pad($request->kode_kecamatan, 20, ' '));
        }
        if ($request->kode_desa) {
            $query->where('kode_desa', str_pad($request->kode_desa, 20, ' '));
        }

        $sortTahun = $request->sort_tahun;
        if ($sortTahun === 'asc') {
            $query->orderBy('tgl_lhr', 'asc');
        } elseif ($sortTahun === 'desc') {
            $query->orderBy('tgl_lhr', 'desc');
        }

        $data = $query->get();

        ExportLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nama ?? $user->nik,
            'user_role' => $user->akses,
            'export_type' => 'belum_akte',
            'filters' => [
                'kode_kecamatan' => $request->kode_kecamatan,
                'kode_desa' => $request->kode_desa,
                'sort_tahun' => $request->sort_tahun,
            ],
            'record_count' => $data->count(),
            'ip_address' => $request->ip(),
        ]);

        $filename = "data-belum-akte-" . date('Y-m-d-H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'No', 'NIK', 'Nama Lengkap', 'Jenis Kelamin', 'Tanggal Lahir',
                'Kecamatan', 'Desa', 'Keterangan', 'No Akta Kelahiran'
            ]);

            $no = 1;
            foreach ($data as $item) {
                fputcsv($file, [
                    $no++,
                    '="' . $item->nik . '"',
                    $item->nama_lgkp,
                    $item->jenis_klmin,
                    $item->tgl_lhr,
                    $item->kecamatan->nama_kecamatan ?? '-',
                    $item->desa->nama_desa ?? '-',
                    $item->keterangan,
                    $item->no_akta_kelahiran ?? '-'
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

        $data = BelumAkte::with(['desa', 'kecamatan'])->where('nik', $nik)->firstOrFail();
        
        // Petugas can only edit data in their desa
        if ($user->isPetugas()) {
            $userDesa = trim($user->kode_desa);
            $dataDesa = trim($data->getOriginal('kode_desa'));
            if ($dataDesa !== $userDesa) {
                return back()->with('error', 'Anda hanya dapat mengedit data di desa Anda.');
            }
        }
        
        $keteranganOptions = [
            'BLM_MMLK_AKTA_LHR' => 'Belum Memiliki Akta Lahir',
            'SUDAH MEMILIKI_AKTA_LHR' => 'Sudah Memiliki Akta Lahir',
        ];

        $isPetugas = $user->isPetugas();

        return view('belum_akte.edit', compact('data', 'keteranganOptions', 'isPetugas'));
    }

    public function update(Request $request, $nik)
    {
        $user = auth()->user();
        
        if ($user->isSupervisor()) {
            return back()->with('error', 'Supervisor tidak dapat mengupdate data.');
        }

        $request->validate([
            'keterangan' => 'required|in:BLM_MMLK_AKTA_LHR,SUDAH MEMILIKI_AKTA_LHR',
            'no_akta_kelahiran' => 'nullable|string|max:50',
        ]);

        $data = BelumAkte::where('nik', $nik)->firstOrFail();
        
        // Petugas: save to proposed fields (pending approval)
        if ($user->isPetugas()) {
            $hasChanges = false;
            
            // Check keterangan
            if ($request->keterangan !== $data->keterangan) {
                $data->keterangan_proposed = $request->keterangan;
                $hasChanges = true;
            }
            
            // Check no_akta_kelahiran
            if ($request->no_akta_kelahiran !== $data->no_akta_kelahiran) {
                $data->no_akta_kelahiran_proposed = $request->no_akta_kelahiran;
                $hasChanges = true;
            }
            
            if ($hasChanges) {
                $data->has_pending_approval = true;
                $data->last_proposed_at = Carbon::now();
                $data->last_proposed_by = $user->nik;
                $data->save();
                
                return redirect()->route('belum_akte.index')
                    ->with('success', 'Perubahan berhasil diajukan. Menunggu approval dari Admin/Pendamping.');
            }
            
            return redirect()->route('belum_akte.index')
                ->with('info', 'Tidak ada perubahan yang diajukan.');
        }

        // Admin/Pendamping: Apply changes directly
        $data->update([
            'keterangan' => $request->keterangan,
            'no_akta_kelahiran' => $request->no_akta_kelahiran,
        ]);

        return redirect()->route('belum_akte.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Show pending approvals page
     */
    public function pending(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isPetugas()) {
            return redirect()->route('belum_akte.index')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $query = BelumAkte::with(['desa', 'kecamatan', 'proposer'])
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

        return view('belum_akte.pending', compact('pendingData'));
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
            'field_name' => 'required|string|in:' . implode(',', BelumAkte::$approvableFields),
        ]);

        $data = BelumAkte::where('nik', $nik)->firstOrFail();
        $fieldName = $validated['field_name'];
        $proposedField = $fieldName . '_proposed';
        
        if ($data->$proposedField === null) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval untuk field ini.');
        }

        $oldValue = $data->$fieldName;
        $proposedValue = $data->$proposedField;

        // Create log entry
        BelumAkteApprovalLog::create([
            'belum_akte_nik' => $data->nik,
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

        $fieldLabel = BelumAkteApprovalLog::$fieldLabels[$fieldName] ?? $fieldName;
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
            'field_name' => 'required|string|in:' . implode(',', BelumAkte::$approvableFields),
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $data = BelumAkte::where('nik', $nik)->firstOrFail();
        $fieldName = $validated['field_name'];
        $proposedField = $fieldName . '_proposed';
        
        if ($data->$proposedField === null) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval untuk field ini.');
        }

        $oldValue = $data->$fieldName;
        $proposedValue = $data->$proposedField;

        // Create log entry for rejection
        BelumAkteApprovalLog::create([
            'belum_akte_nik' => $data->nik,
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

        $fieldLabel = BelumAkteApprovalLog::$fieldLabels[$fieldName] ?? $fieldName;
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

        $data = BelumAkte::where('nik', $nik)->firstOrFail();
        $pendingFields = $data->getPendingFields();
        
        if (empty($pendingFields)) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval.');
        }

        foreach ($pendingFields as $fieldName) {
            $proposedField = $fieldName . '_proposed';
            $oldValue = $data->$fieldName;
            $proposedValue = $data->$proposedField;

            BelumAkteApprovalLog::create([
                'belum_akte_nik' => $data->nik,
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

        $data = BelumAkte::where('nik', $nik)->firstOrFail();
        $pendingFields = $data->getPendingFields();
        
        if (empty($pendingFields)) {
            return back()->with('error', 'Tidak ada perubahan yang menunggu approval.');
        }

        foreach ($pendingFields as $fieldName) {
            $proposedField = $fieldName . '_proposed';
            $oldValue = $data->$fieldName;
            $proposedValue = $data->$proposedField;

            BelumAkteApprovalLog::create([
                'belum_akte_nik' => $data->nik,
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
     * Upload dokumen for a record
     */
    public function uploadDokumen(Request $request, $nik)
    {
        $user = auth()->user();

        if ($user->isSupervisor()) {
            return back()->with('error', 'Supervisor tidak dapat mengupload dokumen.');
        }

        $request->validate([
            'dokumen' => 'required|file|mimes:jpg,jpeg|max:1024',
        ], [
            'dokumen.required' => 'File dokumen wajib diupload.',
            'dokumen.mimes' => 'Format file harus JPG/JPEG.',
            'dokumen.max' => 'Ukuran file maksimal 1MB.',
        ]);

        $data = BelumAkte::where('nik', $nik)->firstOrFail();

        // Delete old file if exists
        if ($data->dokumen_path && Storage::disk('public')->exists($data->dokumen_path)) {
            Storage::disk('public')->delete($data->dokumen_path);
        }

        $file = $request->file('dokumen');
        $filename = trim($nik) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('belum_akte_dokumen', $filename, 'public');

        $data->update(['dokumen_path' => $path]);

        return back()->with('success', 'Dokumen berhasil diupload.');
    }

    /**
     * Download dokumen for a record
     */
    public function downloadDokumen($nik)
    {
        $data = BelumAkte::where('nik', $nik)->firstOrFail();

        if (!$data->dokumen_path || !Storage::disk('public')->exists($data->dokumen_path)) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }

        return Storage::disk('public')->download($data->dokumen_path);
    }

    /**
     * Delete dokumen for a record
     */
    public function deleteDokumen($nik)
    {
        $user = auth()->user();

        if ($user->isSupervisor()) {
            return back()->with('error', 'Supervisor tidak dapat menghapus dokumen.');
        }

        $data = BelumAkte::where('nik', $nik)->firstOrFail();

        if ($data->dokumen_path && Storage::disk('public')->exists($data->dokumen_path)) {
            Storage::disk('public')->delete($data->dokumen_path);
        }

        $data->update(['dokumen_path' => null]);

        return back()->with('success', 'Dokumen berhasil dihapus.');
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

        $query = BelumAkte::where('has_pending_approval', true);

        // Filter by accessible desas for Pendamping
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            $desaCodes = Pendamping::where('nik', $user->nik)
                ->where('status_aktif', 'Aktif')
                ->pluck('kode_desa')
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
