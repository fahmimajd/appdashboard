<?php

namespace App\Http\Controllers;

use App\Models\WilayahDesa;
use App\Models\WilayahKecamatan;
use App\Models\Petugas;
use App\Models\Pendamping;
use App\Models\KinerjaPetugas;
use App\Models\KependudukanSemester;
use App\Models\HeaderPelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        // Get statistics
        $stats = [
            'total_desa' => WilayahDesa::count(),
            'total_petugas_aktif' => Petugas::where('status_aktif', 'Aktif')->count(),
            'total_pendamping_aktif' => Pendamping::where('status_aktif', 'Aktif')->distinct('nik')->count('nik'),
            'total_pelayanan_bulan_ini' => $this->getPelayananBulanIni(),
        ];

        // Get recent activities
        $recentPelayanan = HeaderPelayanan::orderBy('tanggal_dibuat', 'desc')
            ->limit(10)
            ->get();

        // Get kinerja summary for current month
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        $kinerjaBulanIni = KinerjaPetugas::where('tahun', $currentYear)
            ->where('bulan', $currentMonth)
            ->with('petugas', 'desa')
            ->get();

        // Get top performing desa (by total pelayanan)
        $topDesa = $this->getTopPerformingDesa();

        // Get top performing kecamatan (by total pelayanan) - Replaces Chart
        $topKecamatan = $this->getTopPerformingKecamatan();
        
        // Chart data no longer needed as per update1.md point 9
        // $kinerjaChart = $this->getKinerjaChartData();

        // Get latest Kependudukan Stats
        $latestSemester = KependudukanSemester::max('kode_semester');
        $kependudukanStats = [];

        if ($latestSemester) {
            $totalPenduduk = KependudukanSemester::where('kode_semester', $latestSemester)->sum('jumlah_penduduk');
            $totalLaki = KependudukanSemester::where('kode_semester', $latestSemester)->sum('jumlah_laki');
            $totalPerempuan = KependudukanSemester::where('kode_semester', $latestSemester)->sum('jumlah_perempuan');
            
            $kependudukanStats = [
                'kode_semester' => $latestSemester,
                'total_penduduk' => $totalPenduduk,
                'percent_laki' => $totalPenduduk > 0 ? round(($totalLaki / $totalPenduduk) * 100, 2) : 0,
                'percent_perempuan' => $totalPenduduk > 0 ? round(($totalPerempuan / $totalPenduduk) * 100, 2) : 0,
            ];
        }

        return view('dashboard', compact(
            'stats',
            'recentPelayanan',
            'kinerjaBulanIni',
            'topDesa',
            'topKecamatan', // New
            'kependudukanStats'
        ));
    }

    /**
     * Get pelayanan count for current month
     */
    private function getPelayananBulanIni()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        return KinerjaPetugas::where('tahun', $currentYear)
            ->where('bulan', $currentMonth)
            ->sum(DB::raw(KinerjaPetugas::sqlTotalPelayanan()));
    }

    /**
     * Get top performing desa
     */
    private function getTopPerformingDesa($limit = 10)
    {
        $currentYear = now()->year;

        return WilayahDesa::addSelect(['total_pelayanan' => KinerjaPetugas::selectRaw(
                'COALESCE(SUM(' . KinerjaPetugas::sqlTotalPelayanan() . '), 0)'
            )
            ->whereColumn('kode_desa', 'wilayah_desa.kode_desa')
            ->where('tahun', $currentYear)
        ])
        ->with('kecamatan.kabupaten')
        ->orderByDesc('total_pelayanan')
        ->limit($limit)
        ->get();
    }

    /**
     * Get top performing kecamatan
     */
    private function getTopPerformingKecamatan($limit = 5)
    {
        $currentYear = now()->year;
        $user = auth()->user();

        // For Pendamping/Desa/Petugas: show top desa within their kecamatan
        if ($user && !$user->isAdmin() && !$user->isSupervisor()) {
            if ($user->kode_desa) {
                $desa = WilayahDesa::where('kode_desa', $user->kode_desa)->first();
                if ($desa && $desa->kode_kecamatan) {
                    // Return top desa in their kecamatan
                    return WilayahDesa::addSelect(['total_pelayanan' => KinerjaPetugas::selectRaw(
                            'COALESCE(SUM(' . KinerjaPetugas::sqlTotalPelayanan() . '), 0)'
                        )
                        ->whereColumn('kode_desa', 'wilayah_desa.kode_desa')
                        ->where('tahun', $currentYear)
                    ])
                    ->where('kode_kecamatan', $desa->kode_kecamatan)
                    ->with('kecamatan')
                    ->orderByDesc('total_pelayanan')
                    ->limit($limit)
                    ->get();
                }
            }
        }

        // For Admin/Supervisor: show top kecamatan
        return WilayahKecamatan::addSelect(['total_pelayanan' => KinerjaPetugas::selectRaw(
                'COALESCE(SUM(' . KinerjaPetugas::sqlTotalPelayanan() . '), 0)'
            )
            ->whereColumn('kode_kecamatan', 'wilayah_kecamatan.kode_kecamatan')
            ->where('tahun', $currentYear)
        ])
        ->orderByDesc('total_pelayanan')
        ->limit($limit)
        ->get();
    }

    /**
     * API endpoint for dashboard stats
     */
    public function apiStats()
    {
        $stats = [
            'total_desa' => WilayahDesa::count(),
            'total_petugas_aktif' => Petugas::where('status_aktif', 'Aktif')->count(),
            'total_pendamping_aktif' => Pendamping::where('status_aktif', 'Aktif')->distinct('nik')->count('nik'),
            'total_pelayanan_bulan_ini' => $this->getPelayananBulanIni(),
        ];

        return response()->json($stats);
    }
    /**
     * Display detailed monthly service statistics
     */
    public function pelayananDetail()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $pageTitle = 'Detail Pelayanan Bulan Ini';
        $period = now()->locale('id')->isoFormat('MMMM YYYY');

        $data = KinerjaPetugas::select(
                'kode_desa',
                DB::raw('SUM(aktivasi_ikd) as total_aktivasi_ikd'),
                DB::raw('SUM(akta_kelahiran) as total_akta_kelahiran'),
                DB::raw('SUM(akta_kematian) as total_akta_kematian'),
                DB::raw('SUM(pengajuan_kk) as total_pengajuan_kk'),
                DB::raw('SUM(pengajuan_pindah) as total_pengajuan_pindah'),
                DB::raw('SUM(pengajuan_kia) as total_pengajuan_kia'),
                DB::raw('SUM(' . KinerjaPetugas::sqlTotalPelayanan() . ') as total_pelayanan')
            )
            ->where('tahun', $currentYear)
            ->where('bulan', $currentMonth)
            ->with('desa.kecamatan')
            ->groupBy('kode_desa')
            ->orderByDesc('total_pelayanan')
            ->get();

        return view('dashboard.pelayanan-detail', compact('data', 'pageTitle', 'period'));
    }
}
