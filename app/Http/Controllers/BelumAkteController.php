<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BelumAkte;
use App\Models\WilayahDesa;
use App\Models\WilayahKecamatan;
use App\Models\Pendamping;


class BelumAkteController extends Controller
{
    public function index(Request $request)
    {
        $query = BelumAkte::with(['desa', 'kecamatan']);
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

        return view('belum_akte.index', compact('data', 'kecamatans', 'desas'));
    }
}
