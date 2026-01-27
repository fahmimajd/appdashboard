<?php

namespace App\Http\Controllers;

use App\Models\Sasaran;
use App\Models\WilayahDesa;
use Illuminate\Http\Request;

class SasaranController extends Controller
{
    /**
     * Display Belum Rekam list
     */
    public function belumRekam(Request $request)
    {
        return $this->index($request, 'Belum Rekam', 'sasaran.index', 'Belum Rekam KTP-el');
    }

    /**
     * Display Belum Akte list
     */
    public function belumAkte(Request $request)
    {
        return $this->index($request, 'Belum Akte', 'sasaran.index', 'Belum Memiliki Akta Kelahiran');
    }

    /**
     * Main index logic
     */
    private function index(Request $request, $status, $view, $title)
    {
        $query = Sasaran::with(['desa', 'kecamatan'])
            ->where('status', $status);

        // Access Control
        $user = auth()->user();
        $desaCodes = [];

        if (!$user->isAdmin() && !$user->isSupervisor()) {
            if ($user->isPetugas()) {
                if ($user->kode_desa) {
                    $query->whereRaw('TRIM(kode_desa) = ?', [trim($user->kode_desa)]);
                }
            } else {
                // Get all desa codes assigned to this user's NIK
                $desaCodes = \App\Models\Pendamping::where('nik', $user->nik)
                    ->where('status_aktif', 'Aktif')
                    ->pluck('kode_desa')
                    ->filter()
                    ->toArray();
                
                if (!empty($desaCodes)) {
                    $query->whereIn('kode_desa', $desaCodes);
                }
            }
        } elseif ($request->has('desa_id') && $request->desa_id != 'all') {
            // Filter by desa for Admin/Supervisor
            // check if desa_id is ID or Code
            // Assuming form sends ID, we need code.
            $desa = WilayahDesa::find($request->desa_id);
            if ($desa) {
                $query->where('kode_desa', $desa->kode_desa);
            }
        }

        if ($request->has('search') && $request->search) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('nama', 'like', "%{$search}%")
                   ->orWhere('nik', 'like', "%{$search}%");
             });
        }

        // Summary Data Logic
        $summaryQuery = WilayahDesa::query();
        
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            if ($user->isPetugas()) {
                if ($user->kode_desa) {
                    $summaryQuery->whereRaw('TRIM(kode_desa) = ?', [trim($user->kode_desa)]);
                }
            } elseif (!empty($desaCodes)) {
                $summaryQuery->whereIn('kode_desa', $desaCodes);
            }
        }

        $summaryData = $summaryQuery->withCount([
            'sasaran as belum_rekam_count' => function($q) {
                $q->where('status', 'Belum Rekam');
            },
            'sasaran as belum_akte_count' => function($q) {
                $q->where('status', 'Belum Akte');
            }
        ])->orderBy('nama_desa')->get();

        $data = $query->paginate(15)->withQueryString();
        $desas = WilayahDesa::orderBy('nama_desa')->get(); // For filter if needed

        return view($view, compact('data', 'desas', 'title', 'summaryData'));
    }
}
