<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BelumRekam;
use App\Models\WilayahDesa;
use App\Models\WilayahKecamatan;
use App\Models\Pendamping;


class BelumRekamController extends Controller
{
    public function index(Request $request)
    {
        $query = BelumRekam::with(['desa', 'kecamatan']);
        $user = auth()->user();
        $allowedDesaCodes = null;

        // Access Control Logic
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            if ($user->isPetugas()) {
                // Petugas: use their assigned kode_desa from users table
                if ($user->kode_desa) {
                    $allowedDesaCodes = [trim($user->kode_desa)];
                    $paddedCodes = [str_pad(trim($user->kode_desa), 20, ' ')];
                    $query->whereIn('kode_desa', $paddedCodes);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Pendamping: get allowed desa from pendamping table
                $allowedDesaCodes = Pendamping::where('nik', $user->nik)
                    ->where('status_aktif', 'Aktif')
                    ->pluck('kode_desa')
                    ->map(fn($code) => trim($code))
                    ->filter()
                    ->toArray();
                
                if (!empty($allowedDesaCodes)) {
                    $paddedCodes = array_map(fn($code) => str_pad($code, 20, ' '), $allowedDesaCodes);
                    $query->whereIn('kode_desa', $paddedCodes);
                } else {
                    $query->whereRaw('1 = 0'); 
                }
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

        $data = $query->paginate(10)->withQueryString();
        
        // Filter Dropdowns
        if ($allowedDesaCodes !== null) {
            // Restricted User: Show only relevant Kecamatans and Desas
            $validKecCodes = \App\Models\WilayahDesa::whereIn('kode_desa', $allowedDesaCodes)
                ->pluck('kode_kecamatan')
                ->unique();
            
            $kecamatans = \App\Models\WilayahKecamatan::whereIn('kode_kecamatan', $validKecCodes)
                ->orderBy('nama_kecamatan')
                ->get();
            
            $desas = [];
            if ($request->kode_kecamatan) {
                $desas = \App\Models\WilayahDesa::where('kode_kecamatan', $request->kode_kecamatan)
                    ->whereIn('kode_desa', $allowedDesaCodes)
                    ->orderBy('nama_desa')
                    ->get();
            }
        } else {
            // Admin/Supervisor: Show all
            $kecamatans = \App\Models\WilayahKecamatan::orderBy('nama_kecamatan')->get();
            $desas = [];
            if($request->kode_kecamatan) {
                $desas = \App\Models\WilayahDesa::where('kode_kecamatan', $request->kode_kecamatan)
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
}
