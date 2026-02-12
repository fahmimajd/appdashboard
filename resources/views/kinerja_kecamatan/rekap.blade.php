@extends('layouts.app')

@section('title', 'Rekapitulasi Kinerja Kecamatan')
@section('subtitle', 'Laporan agregat kinerja pelayanan')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 h-full flex flex-col">
    
    <!-- Header Tools -->
    <div class="flex flex-col lg:flex-row md:items-center justify-between gap-4 mb-6 flex-shrink-0">
        <!-- Filter Form -->
        <form action="{{ route('kinerja-kecamatan.rekap') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1 flex-wrap items-end md:items-center">
            <!-- Date Range -->
            <div class="flex gap-2 items-center">
                <div class="w-36">
                    <label class="block text-xs text-gray-500 mb-1">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200" onchange="this.form.submit()">
                </div>
                <span class="text-gray-400">-</span>
                <div class="w-36">
                    <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200" onchange="this.form.submit()">
                </div>
            </div>

            <!-- Month/Year Fallback if no date range -->
            @if(!request('start_date'))
            <div class="flex gap-2">
                 <div class="w-32">
                    <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                    <select name="bulan" onchange="this.form.submit()" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                 <div class="w-24">
                    <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                    <select name="tahun" onchange="this.form.submit()" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            @endif

            <!-- Kecamatan Filter (Admin/Supervisor) -->
            @if(auth()->user()->isAdmin() || (auth()->user()->isSupervisor() && !auth()->user()->kode_kecamatan))
            <div class="w-48">
                <label class="block text-xs text-gray-500 mb-1">Kecamatan</label>
                <select name="kode_kecamatan" onchange="this.form.submit()" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Kecamatan</option>
                    @foreach($kecamatans as $k)
                        <option value="{{ $k->kode_kecamatan }}" {{ request('kode_kecamatan') == $k->kode_kecamatan ? 'selected' : '' }}>
                            {{ $k->nama_kecamatan }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
        </form>

        <!-- Actions -->
        <div class="flex gap-2">
             <a href="{{ route('kinerja-kecamatan.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 flex items-center gap-2 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
            <!-- Export button could be added here later -->
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-4" x-data="{ tab: 'petugas' }">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs" id="rekapTabs">
            <button onclick="switchTab('petugas')" id="tab-btn-petugas" class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                Rekap Per Petugas
            </button>
            <button onclick="switchTab('kecamatan')" id="tab-btn-kecamatan" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                Rekap Per Kecamatan
            </button>
        </nav>
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-auto min-h-0 relative">
        
        <!-- Tab: Petugas -->
        <div id="tab-content-petugas" class="absolute inset-0 overflow-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="sticky top-0 bg-gray-50 z-10 shadow-sm">
                    <tr class="text-xs font-semibold text-gray-600 uppercase tracking-wider bg-blue-50">
                        <th class="px-4 py-3 border-b">Petugas</th>
                        <th class="px-4 py-3 border-b text-center">Rekam KTP</th>
                        <th class="px-4 py-3 border-b text-center">Cetak KTP</th>
                        <th class="px-4 py-3 border-b text-center">KK</th>
                        <th class="px-4 py-3 border-b text-center">KIA</th>
                        <th class="px-4 py-3 border-b text-center">Pindah</th>
                        <th class="px-4 py-3 border-b text-center">Datang</th>
                        <th class="px-4 py-3 border-b text-center">Lahir</th>
                        <th class="px-4 py-3 border-b text-center">Mati</th>
                        <th class="px-4 py-3 border-b text-center bg-green-100">Total IKD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($rekapPetugas as $p)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-gray-900">{{ $p->petugas->nama ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $p->petugas_id }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_rekam }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_cetak }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_kk }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_kia }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_pindah }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_kedatangan }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_akta_lahir }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $p->total_akta_mati }}</td>
                            <td class="px-4 py-3 text-sm text-center font-bold text-green-700 bg-green-50">{{ $p->total_ikd }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data rekapitulasi petugas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tab: Kecamatan -->
        <div id="tab-content-kecamatan" class="absolute inset-0 overflow-auto hidden">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="sticky top-0 bg-gray-50 z-10 shadow-sm">
                    <tr class="text-xs font-semibold text-gray-600 uppercase tracking-wider bg-purple-50">
                        <th class="px-4 py-3 border-b">Kecamatan</th>
                        <th class="px-4 py-3 border-b text-center">Rekam KTP</th>
                        <th class="px-4 py-3 border-b text-center">Cetak KTP</th>
                        <th class="px-4 py-3 border-b text-center">KK</th>
                        <th class="px-4 py-3 border-b text-center">KIA</th>
                        <th class="px-4 py-3 border-b text-center">Pindah</th>
                        <th class="px-4 py-3 border-b text-center">Datang</th>
                        <th class="px-4 py-3 border-b text-center">Lahir</th>
                        <th class="px-4 py-3 border-b text-center">Mati</th>
                        <th class="px-4 py-3 border-b text-center bg-green-100">Total IKD</th>
                        <th class="px-4 py-3 border-b text-center">Avg Ribbon%</th>
                        <th class="px-4 py-3 border-b text-center">Avg Film%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($rekapKecamatan as $k)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $k->kecamatan->nama_kecamatan ?? 'Unknown' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_rekam }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_cetak }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_kk }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_kia }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_pindah }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_kedatangan }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_akta_lahir }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $k->total_akta_mati }}</td>
                            <td class="px-4 py-3 text-sm text-center font-bold text-green-700 bg-green-50">{{ $k->total_ikd }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ number_format($k->avg_ribbon, 2) }}%</td>
                            <td class="px-4 py-3 text-sm text-center">{{ number_format($k->avg_film, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data rekapitulasi kecamatan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Build IDs
        const btnPetugas = document.getElementById('tab-btn-petugas');
        const btnKecamatan = document.getElementById('tab-btn-kecamatan');
        const contentPetugas = document.getElementById('tab-content-petugas');
        const contentKecamatan = document.getElementById('tab-content-kecamatan');

        // Reset classes
        const activeBtnClass = ['border-blue-500', 'text-blue-600'];
        const inactiveBtnClass = ['border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300'];

        if (tabName === 'petugas') {
            btnPetugas.classList.add(...activeBtnClass);
            btnPetugas.classList.remove(...inactiveBtnClass);
            
            btnKecamatan.classList.remove(...activeBtnClass);
            btnKecamatan.classList.add(...inactiveBtnClass);
            
            contentPetugas.classList.remove('hidden');
            contentKecamatan.classList.add('hidden');
        } else {
            btnKecamatan.classList.add(...activeBtnClass);
            btnKecamatan.classList.remove(...inactiveBtnClass);
            
            btnPetugas.classList.remove(...activeBtnClass);
            btnPetugas.classList.add(...inactiveBtnClass);
            
            contentKecamatan.classList.remove('hidden');
            contentPetugas.classList.add('hidden');
        }
    }
</script>
@endsection
