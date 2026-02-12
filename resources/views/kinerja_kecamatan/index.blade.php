@extends('layouts.app')

@section('title', 'Laporan Kinerja Kecamatan')
@section('subtitle', 'Data kinerja pelayanan harian di tingkat kecamatan')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 h-full flex flex-col">
    <!-- Notifications -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center gap-2 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Header Tools -->
    <div class="flex flex-col lg:flex-row md:items-center justify-between gap-4 mb-6 flex-shrink-0">
        <!-- Filter Form -->
        <form action="{{ route('kinerja-kecamatan.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1 flex-wrap items-end md:items-center">
            
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

            <!-- Petugas Filter -->
            <div class="w-48">
                <label class="block text-xs text-gray-500 mb-1">Petugas</label>
                <select name="petugas_id" onchange="this.form.submit()" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Petugas</option>
                    @foreach($daft_petugas as $p)
                        <option value="{{ $p->nik }}" {{ request('petugas_id') == $p->nik ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Actions -->
        <div class="flex gap-2 self-end lg:self-center">
            @if(request()->hasAny(['start_date', 'end_date', 'kode_kecamatan', 'petugas_id']))
                <a href="{{ route('kinerja-kecamatan.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 text-sm">
                    Reset
                </a>
            @endif
            
            <a href="{{ route('kinerja-kecamatan.rekap', request()->all()) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200 shadow-md flex items-center gap-2 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Lihat Rekap
            </a>

            <a href="{{ route('kinerja-kecamatan.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Input Harian
            </a>
        </div>
    </div>

    <!-- Table Container - Scrollable -->
    <div class="flex-1 overflow-auto min-h-0 border rounded-lg">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead class="sticky top-0 bg-gray-50 z-10 shadow-sm">
                <tr class="text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3 border-b text-center w-12">No</th>
                    <th class="px-4 py-3 border-b">Tanggal</th>
                    <th class="px-4 py-3 border-b">Kecamatan</th>
                    <th class="px-4 py-3 border-b">Petugas</th>
                    <th class="px-4 py-3 border-b text-center bg-blue-50">Rekam<br>KTP</th>
                    <th class="px-4 py-3 border-b text-center bg-blue-50">Cetak<br>KTP</th>
                    <th class="px-4 py-3 border-b text-center">KK</th>
                    <th class="px-4 py-3 border-b text-center">KIA</th>
                    <th class="px-4 py-3 border-b text-center">Pindah</th>
                    <th class="px-4 py-3 border-b text-center">Datang</th>
                    <th class="px-4 py-3 border-b text-center">Lahir</th>
                    <th class="px-4 py-3 border-b text-center">Mati</th>
                    <th class="px-4 py-3 border-b text-center bg-green-50">IKD</th>
                    <th class="px-4 py-3 border-b text-center bg-green-50">S.KTP</th>
                    <th class="px-4 py-3 border-b text-center bg-green-50">Ribbon%</th>
                    <th class="px-4 py-3 border-b text-center bg-green-50">Film%</th>
                    <th class="px-4 py-3 border-b text-right sticky right-0 bg-gray-50 shadow-l">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($kinerjas as $key => $k)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $kinerjas->firstItem() + $key }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($k->tanggal)->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $k->kecamatan->nama_kecamatan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $k->petugas->nama ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $k->petugas_id }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-semibold bg-blue-50/50">{{ $k->rekam_ktp_el }}</td>
                        <td class="px-4 py-3 text-sm text-center font-semibold bg-blue-50/50">{{ $k->cetak_ktp_el }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $k->kartu_keluarga }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $k->kia }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $k->pindah }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $k->kedatangan }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $k->akta_kelahiran }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $k->akta_kematian }}</td>
                        <td class="px-4 py-3 text-sm text-center font-semibold bg-green-50/50 text-green-700">{{ $k->ikd_hari_ini }}</td>
                        <td class="px-4 py-3 text-sm text-center bg-green-50/50">{{ $k->stok_blangko_ktp }}</td>
                        <td class="px-4 py-3 text-sm text-center bg-green-50/50">{{ $k->persentase_ribbon }}%</td>
                        <td class="px-4 py-3 text-sm text-center bg-green-50/50">{{ $k->persentase_film }}%</td>
                        
                        <td class="px-4 py-3 text-sm text-right sticky right-0 bg-white shadow-l">
                            <div class="flex items-center justify-end gap-2">
                                @if(
                                    auth()->user()->isAdmin() || 
                                    (auth()->user()->kode_kecamatan == $k->kode_kecamatan)
                                )
                                <a href="{{ route('kinerja-kecamatan.edit', $k->id) }}" class="p-1 text-yellow-600 hover:text-yellow-800 bg-yellow-50 hover:bg-yellow-100 rounded transition duration-200" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('kinerja-kecamatan.destroy', $k->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded transition duration-200" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data laporan kinerja pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex-shrink-0">
        {{ $kinerjas->links() }}
    </div>
</div>
@endsection
