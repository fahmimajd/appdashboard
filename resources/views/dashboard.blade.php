@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan data dan statistik pelayanan')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Desa -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Desa</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['total_desa']) }}</h3>
                </div>
                <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            </div>
        </div>

        <!-- Total Petugas Aktif -->
        <div class="bg-gradient-to-br from-green-500 to-green-700 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Petugas Aktif</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['total_petugas_aktif']) }}</h3>
                </div>
                <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Total Pendamping Aktif -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pendamping Aktif</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['total_pendamping_aktif']) }}</h3>
                </div>
                <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Pelayanan Bulan Ini -->
        <a href="{{ route('dashboard.pelayanan-detail') }}" class="bg-gradient-to-br from-orange-500 to-orange-700 text-white rounded-xl p-6 shadow-lg transform transition hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pelayanan Bulan {{ $bulanLalu }}</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['total_pelayanan_bulan_ini']) }}</h3>
                </div>
                <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </a>
    </div>

    <!-- Kependudukan Statistics -->
    @if(!empty($kependudukanStats))
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-800">Statistik Kependudukan</h3>
                <p class="text-sm text-gray-500">Update Terakhir: Semester {{ substr($kependudukanStats['kode_semester'], 4, 2) }} Tahun {{ substr($kependudukanStats['kode_semester'], 0, 4) }} (Kode: {{ $kependudukanStats['kode_semester'] }})</p>
            </div>
            
            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Penduduk</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($kependudukanStats['total_penduduk']) }}</p>
                </div>
                <div class="p-4 bg-cyan-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Laki-laki</p>
                    <p class="text-2xl font-bold text-cyan-700">{{ $kependudukanStats['percent_laki'] }}%</p>
                </div>
                <div class="p-4 bg-pink-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Perempuan</p>
                    <p class="text-2xl font-bold text-pink-700">{{ $kependudukanStats['percent_perempuan'] }}%</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 Perkecamatan Pendampingan -->
        <div class="card">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Top 5 Perkecamatan Pendampingan</h3>
                <p class="text-sm text-gray-600">Berdasarkan total pelayanan tahun ini</p>
            </div>
            <div class="overflow-auto" style="max-height: 300px;">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Kecamatan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topKecamatan as $index => $kecamatan)
                        <tr>
                            <td class="text-center">
                                @if($index == 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-400 text-white font-bold">1</span>
                                @elseif($index == 1)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-400 text-white font-bold">2</span>
                                @elseif($index == 2)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-600 text-white font-bold">3</span>
                                @else
                                    <span class="text-gray-600">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($kecamatan->nama_desa))
                                    {{ $kecamatan->nama_desa }}
                                @else
                                    {{ $kecamatan->nama_kecamatan }}
                                @endif
                            </td>
                            <td class="font-semibold">{{ number_format($kecamatan->total_pelayanan ?? 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Performing Desa -->
        <div class="card">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Top 10 Desa</h3>
                <p class="text-sm text-gray-600">Berdasarkan total pelayanan tahun ini</p>
            </div>
            <div class="overflow-auto" style="max-height: 300px;">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Desa</th>
                            <th>Kecamatan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDesa as $index => $desa)
                        <tr>
                            <td class="text-center">
                                @if($index == 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-400 text-white font-bold">1</span>
                                @elseif($index == 1)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-400 text-white font-bold">2</span>
                                @elseif($index == 2)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-600 text-white font-bold">3</span>
                                @else
                                    <span class="text-gray-600">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>{{ $desa->nama_desa }}</td>
                            <td>{{ $desa->kecamatan->nama_kecamatan }}</td>
                            <td class="font-semibold">{{ number_format($desa->total_pelayanan ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="card">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h3>
            <p class="text-sm text-gray-600">10 pelayanan terakhir</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th>Nomor Pelayanan</th>
                        <th>Nomor Pengaduan</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPelayanan as $pelayanan)
                    <tr>
                        <td>{{ $pelayanan->nomor_pelayanan ?? '-' }}</td>
                        <td>{{ $pelayanan->nomor_pengaduan ?? '-' }}</td>
                        <td>{{ $pelayanan->getTanggalFormatted() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray-500 py-4">Belum ada data pelayanan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Activity Logs (Admin Only) -->
    @if(auth()->user()->isAdmin() && count($exportLogs) > 0)
    <div class="card">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Aktivitas Export</h3>
            <p class="text-sm text-gray-600">10 aktivitas export terakhir</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Jenis Export</th>
                        <th>Jumlah Data</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exportLogs as $log)
                    <tr>
                        <td class="text-sm">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->user_name }}</td>
                        <td>
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($log->user_role === 'Admin') bg-red-100 text-red-800
                                @elseif($log->user_role === 'Supervisor') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ $log->user_role }}
                            </span>
                        </td>
                        <td>
                            @if($log->export_type === 'belum_rekam')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Belum Rekam</span>
                            @elseif($log->export_type === 'belum_akte')
                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">Belum Akte</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">{{ $log->export_type }}</span>
                            @endif
                        </td>
                        <td class="font-semibold">{{ number_format($log->record_count) }}</td>
                        <td class="text-sm text-gray-500">{{ $log->ip_address }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
{{-- No scripts needed for now --}}
@endpush
