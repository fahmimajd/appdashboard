@extends('layouts.app')

@section('title', 'Data Belum Rekam')
@section('subtitle', 'Daftar penduduk yang belum melakukan perekaman E-KTP')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Header Tools -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
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
        <div>
            @if(request('search') || request('kode_kecamatan') || request('kode_desa') || request('status'))
                <a href="{{ route('belum_rekam.index') }}" class="mr-2 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3 w-16">No</th>
                    <th class="px-4 py-3">NIK</th>
                    <th class="px-4 py-3">Nama Lengkap</th>
                    <th class="px-4 py-3">L/P</th>
                    <th class="px-4 py-3">Tgl Lahir</th>
                    <th class="px-4 py-3">Desa</th>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3">Status</th>
                     <th class="px-4 py-3">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $item)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $item->nik }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->nama_lgkp }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->jenis_klm }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->tgl_lhr }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->desa->nama_desa ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->kecamatan->nama_kecamatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->wktp_ket }}</td>
                         <td class="px-4 py-3 text-sm text-gray-500">{{ $item->keterangan }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data belum rekam.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $data->links() }}
    </div>
</div>
@endsection
