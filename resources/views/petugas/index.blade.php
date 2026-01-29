@extends('layouts.app')

@section('title', 'Data Petugas')
@section('subtitle', 'Daftar petugas lapangan (Desa, Kecamatan, Dinas)')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 h-full flex flex-col">
    <!-- Header Tools - Fixed -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 flex-shrink-0">
        <!-- Filter & Search -->
        <form action="{{ route('petugas.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="w-full md:w-64">
                <select name="status_aktif" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Status</option>
                    <option value="Aktif" {{ request('status_aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Tidak Aktif" {{ request('status_aktif') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="w-full md:w-32">
                <select name="level_akses" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Level</option>
                    @foreach(range(0, 5) as $lvl)
                        <option value="{{ $lvl }}" {{ request('level_akses') == (string)$lvl ? 'selected' : '' }}>Level {{ $lvl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIK/NIP..." 
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
            @if(request('search') || request('status_aktif') != 'all' || request('level_akses') != 'all')
                <a href="{{ route('petugas.index') }}" class="mr-2 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
            <a href="{{ route('petugas.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Petugas
            </a>
        </div>
    </div>

    <!-- Table Container - Scrollable -->
    <div class="flex-1 overflow-auto min-h-0">
        <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3">NIK/NIP</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">No. HP</th>
                    <th class="px-4 py-3">Level Akses</th>
                    <th class="px-4 py-3">Desa</th>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3">Tgl Mulai Akses</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($petugas as $p)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $p->nik }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-xs">
                                    {{ substr($p->nama, 0, 2) }}
                                </div>
                                <div class="text-sm font-medium text-gray-900">{{ $p->nama }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $p->nomor_ponsel ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $colorClass = 'bg-purple-100 text-purple-700'; // Default / Dinas
                                if ($p->level_akses == 'Desa') $colorClass = 'bg-blue-100 text-blue-700';
                                elseif ($p->level_akses == 'Kecamatan') $colorClass = 'bg-orange-100 text-orange-700';
                                elseif (is_numeric($p->level_akses)) {
                                     // Quick visual distinction for levels
                                     $levelColors = [
                                        '0' => 'bg-gray-100 text-gray-700',
                                        '1' => 'bg-red-100 text-red-700',
                                        '2' => 'bg-orange-100 text-orange-700',
                                        '3' => 'bg-yellow-100 text-yellow-700', 
                                        '4' => 'bg-green-100 text-green-700',
                                        '5' => 'bg-blue-100 text-blue-700'
                                     ];
                                     $colorClass = $levelColors[$p->level_akses] ?? 'bg-indigo-100 text-indigo-700';
                                }
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $colorClass }}">
                                {{ $p->level_akses }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            @if(isset($p->jenis_label) && $p->jenis_label == 'Desa')
                                {{ $p->desa->nama_desa ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                             {{ $p->kecamatan->nama_kecamatan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $p->tanggal_mulai_aktif ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $p->status_aktif == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $p->status_aktif }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('petugas.show', $p->nik) }}" class="p-1 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('petugas.edit', $p->nik) }}" class="p-1 text-yellow-600 hover:text-yellow-800 bg-yellow-50 hover:bg-yellow-100 rounded transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('petugas.destroy', $p->nik) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data petugas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded transition duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data petugas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination - Fixed at bottom -->
    <div class="mt-4 flex-shrink-0">
        {{ $petugas->links() }}
    </div>
</div>
@endsection
