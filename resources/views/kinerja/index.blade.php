@extends('layouts.app')

@section('title', 'Laporan Kinerja')
@section('subtitle', 'Data kinerja pelayanan petugas lapangan')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 h-full flex flex-col">
    <!-- Rejected notification -->
    @if(session('rejected'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('rejected') }}
        </div>
    @endif

    @if(session('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg flex-shrink-0">
            {{ session('info') }}
        </div>
    @endif

    <!-- Header Tools - Fixed -->
    <div class="flex flex-col lg:flex-row md:items-center justify-between gap-4 mb-6 flex-shrink-0">
        <!-- Filter Form -->
        <form action="{{ route('kinerja.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1 flex-wrap">
            <div class="w-full md:w-32">
                <select name="tahun" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Tahun</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="w-full md:w-40">
                <select name="bulan" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Bulan</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-64">
                <select name="desa_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Desa</option>
                    @foreach($desas as $d)
                        <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->nama_desa }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Actions -->
        <div class="flex gap-2">
            @if(request('tahun') || request('bulan') || request('desa_id'))
                <a href="{{ route('kinerja.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
            @if(!auth()->user()->isPetugas())
            <a href="{{ route('kinerja.export', request()->all()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('kinerja.pending') }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Pending Approval
            </a>
            @endif
            <a href="{{ route('kinerja.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Input Kinerja
            </a>
        </div>
    </div>

    <!-- Pending Approval Info for Petugas -->
    @if(auth()->user()->isPetugas())
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-sm flex-shrink-0">
        <strong>Info:</strong> Perubahan data kinerja yang Anda lakukan akan menunggu approval dari Pendamping. Nilai yang diajukan akan ditampilkan dengan warna kuning.
    </div>
    @endif

    <!-- Table Container - Scrollable -->
    <div class="flex-1 overflow-auto min-h-0">
        <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Petugas</th>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3">Desa</th>
                    <th class="px-4 py-3 text-center">Aktivasi IKD (bulan ini)</th>
                    <th class="px-4 py-3 text-center">Total IKD Desa (saat ini)</th>
                    <th class="px-4 py-3 text-center">Total Aktivasi IKD (sampai saat ini)</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($kinerjas as $k)
                    <tr class="hover:bg-gray-50 transition duration-150 {{ $k->hasPendingApproval() ? 'bg-yellow-50' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                            <span class="font-medium">{{ \Carbon\Carbon::create()->month($k->bulan)->translatedFormat('F') }} {{ $k->tahun }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $k->petugas->nama ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $k->petugas->nik ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $k->desa->kecamatan->nama_kecamatan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $k->desa->nama_desa ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-700">
                            @php [$current, $proposed] = $k->getFieldWithProposed('aktivasi_ikd'); @endphp
                            {{ $current }}
                            @if($proposed !== null)
                                <span class="ml-1 text-yellow-600 font-semibold">→ {{ $proposed }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-700">
                            @php [$current, $proposed] = $k->getFieldWithProposed('ikd_desa'); @endphp
                            {{ $current }}
                            @if($proposed !== null)
                                <span class="ml-1 text-yellow-600 font-semibold">→ {{ $proposed }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-blue-700">
                            @php [$current, $proposed] = $k->getFieldWithProposed('total_aktivasi_ikd'); @endphp
                            {{ $current }}
                            @if($proposed !== null)
                                <span class="ml-1 text-yellow-600 font-semibold">→ {{ $proposed }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($k->hasPendingApproval())
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium">
                                    Pending ({{ count($k->getPendingFields()) }})
                                </span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">
                                    Approved
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kinerja.show', $k->id) }}" class="p-1 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded transition duration-200" title="Lihat Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                @if(!auth()->user()->isSupervisor())
                                <a href="{{ route('kinerja.edit', $k->id) }}" class="p-1 text-yellow-600 hover:text-yellow-800 bg-yellow-50 hover:bg-yellow-100 rounded transition duration-200" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                @endif
                                @if(!auth()->user()->isPetugas() && !auth()->user()->isSupervisor())
                                @if($k->hasPendingApproval())
                                <form action="{{ route('kinerja.approve-all', $k->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Approve semua perubahan?')">
                                    @csrf
                                    <button type="submit" class="p-1 text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 rounded transition duration-200" title="Approve Semua">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('kinerja.destroy', $k->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data kinerja ini?')">
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
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data kinerja.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination - Fixed at bottom -->
    <div class="mt-4 flex-shrink-0">
        {{ $kinerjas->links() }}
    </div>
</div>
@endsection
