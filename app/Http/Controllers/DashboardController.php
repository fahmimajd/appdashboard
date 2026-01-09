<?php

namespace App\Http\Controllers;

use App\Models\WilayahDesa;
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
            'total_pendamping_aktif' => Pendamping::where('status_aktif', 'Aktif')->distinct('nama')->count('nama'),
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

        // Get monthly kinerja trend for chart (last 6 months)
        $kinerjaChart = $this->getKinerjaChartData();

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
            'kinerjaChart',
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
            ->sum(DB::raw('aktivasi_ikd + ikd_desa + akta_kelahiran + akta_kematian + pengajuan_kk + pengajuan_pindah + pengajuan_kia'));
    }

    /**
     * Get top performing desa
     */
    private function getTopPerformingDesa($limit = 10)
    {
        $currentYear = now()->year;

        return WilayahDesa::withCount([
            'kinerja as total_pelayanan' => function ($query) use ($currentYear) {
                $query->where('tahun', $currentYear)
                    ->select(DB::raw('SUM(aktivasi_ikd + ikd_desa + akta_kelahiran + akta_kematian + pengajuan_kk + pengajuan_pindah + pengajuan_kia)'));
            }
        ])
        ->with('kecamatan.kabupaten')
        ->orderBy('total_pelayanan', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Get kinerja chart data for last 6 months
     */
    private function getKinerjaChartData()
    {
        $data = [];
        $currentDate = now();

        for ($i = 5; $i >= 0; $i--) {
            $date = $currentDate->copy()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $totalPelayanan = KinerjaPetugas::where('tahun', $year)
                ->where('bulan', $month)
                ->sum(DB::raw('aktivasi_ikd + ikd_desa + akta_kelahiran + akta_kematian + pengajuan_kk + pengajuan_pindah + pengajuan_kia'));

            $data[] = [
                'month' => $date->format('M Y'),
                'total' => $totalPelayanan
            ];
        }

        return $data;
    }

    /**
     * API endpoint for dashboard stats
     */
    public function apiStats()
    {
        $stats = [
            'total_desa' => WilayahDesa::count(),
            'total_petugas_aktif' => Petugas::where('status_aktif', 'Aktif')->count(),
            'total_pendamping_aktif' => Pendamping::where('status_aktif', 'Aktif')->count(),
            'total_pelayanan_bulan_ini' => $this->getPelayananBulanIni(),
        ];

        return response()->json($stats);
    }
}
