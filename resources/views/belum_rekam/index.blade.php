@extends('layouts.app')

@section('title', 'Data Belum Rekam')
@section('subtitle', 'Daftar penduduk yang belum melakukan perekaman E-KTP')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 h-full flex flex-col">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            {{ session('info') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
    <!-- Header Tools - Fixed -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 flex-shrink-0">
        <!-- Filter & Search -->
        <!-- Filter & Search -->
        <form action="{{ route('belum_rekam.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="w-full md:w-64">
                <select name="kode_kecamatan" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->kode_kecamatan }}" {{ request('kode_kecamatan') == $kec->kode_kecamatan ? 'selected' : '' }}>
                            {{ $kec->nama_kecamatan }}
                        </option>
                    @endforeach
                </select>
            </div>
             @if(!empty($desas) || request('kode_desa'))
            <div class="w-full md:w-64">
                <select name="kode_desa" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Semua Desa</option>
                    @foreach($desas as $desa)
                        <option value="{{ $desa->kode_desa }}" {{ request('kode_desa') == $desa->kode_desa ? 'selected' : '' }}>
                            {{ $desa->nama_desa }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="w-full md:w-64">
                <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Semua Status</option>
                    <option value="WKTP PEMULA 2027" {{ request('status') == 'WKTP PEMULA 2027' ? 'selected' : '' }}>WKTP PEMULA 2027</option>
                    <option value="WKTP PEMULA 2026" {{ request('status') == 'WKTP PEMULA 2026' ? 'selected' : '' }}>WKTP PEMULA 2026</option>
                    <option value="WKTP S/D 31-12-2025" {{ request('status') == 'WKTP S/D 31-12-2025' ? 'selected' : '' }}>WKTP S/D 31-12-2025</option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <select name="sort_tahun" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Urut Tahun</option>
                    <option value="desc" {{ request('sort_tahun') == 'desc' ? 'selected' : '' }}>Tahun Terbesar</option>
                    <option value="asc" {{ request('sort_tahun') == 'asc' ? 'selected' : '' }}>Tahun Terkecil</option>
                </select>
            </div>

            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIK atau Nama..." 
                       class="w-full pl-10 pr-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </form>

        <!-- Actions -->
        <div class="flex gap-2">
            @if(!auth()->user()->isPetugas())
                @php
                    $pendingCount = \App\Http\Controllers\BelumRekamController::getPendingCount();
                @endphp
                @if($pendingCount > 0)
                    <a href="{{ route('belum_rekam.pending') }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pending ({{ $pendingCount }})
                    </a>
                @endif
                <a href="{{ route('belum_rekam.export', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Excel
                </a>
            @endif
            @if(request('search') || request('kode_kecamatan') || request('kode_desa') || request('status') || request('sort_tahun'))
                <a href="{{ route('belum_rekam.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
        </div>
    </div>

    <!-- Table Container - Scrollable -->
    <div class="flex-1 overflow-auto min-h-0">
        <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3 w-16">No</th>
                    <th class="px-4 py-3">NIK</th>
                    <th class="px-4 py-3">Nama Lengkap</th>
                    <th class="px-4 py-3">L/P</th>
                    <th class="px-4 py-3">Tgl Lahir</th>
                    <th class="px-4 py-3">Desa</th>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Keterangan</th>
                    @if(!auth()->user()->isSupervisor())
                        <th class="px-4 py-3 text-center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $item)
                    <tr class="hover:bg-gray-50 transition duration-150 {{ $item->hasPendingApproval() ? 'bg-yellow-50' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $item->nik }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $item->nama_lgkp }}
                            @if($item->hasPendingApproval())
                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-yellow-200 text-yellow-800 rounded-full">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->jenis_klm }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->tgl_lhr }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->desa->nama_desa ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->kecamatan->nama_kecamatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->wktp_ket }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->keterangan ?? 'BLM_RKM_KTP' }}</td>
                        @if(!auth()->user()->isSupervisor())
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('belum_rekam.edit', $item->nik) }}" 
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data belum rekam.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination - Fixed at bottom -->
    <div class="mt-4 flex-shrink-0">
        {{ $data->links() }}
    </div>
</div>
@endsection
