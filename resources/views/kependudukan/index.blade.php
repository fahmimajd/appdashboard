@extends('layouts.app')

@section('title', 'Data Kependudukan')
@section('subtitle', 'Statistik kependudukan per semester')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Header Tools -->
    <div class="flex flex-col lg:flex-row md:items-center justify-between gap-4 mb-6">
        <!-- Filter Form -->
        <form action="{{ route('kependudukan.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1 flex-wrap">
            <div class="w-full md:w-32">
                <select name="tahun" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Tahun</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-40">
                <select name="semester" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Sem.</option>
                    <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>Semester 2</option>
                </select>
            </div>

            <!-- Search by Name -->
            <div class="w-full md:w-48">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Desa..." class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
            </div>

            <!-- Submit Button (optional if reliance on enter key or auto-submit is not enough, but distinct button helps) -->
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </form>

        <!-- Actions -->
        <div>
            @if(request('tahun') || request('semester'))
                <a href="{{ route('kependudukan.index') }}" class="mr-2 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
            <a href="{{ route('kependudukan.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Input Data
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Wilayah</th>
                    <th class="px-4 py-3 text-center">Penduduk (L/P)</th>
                    <th class="px-4 py-3 text-center">KK / Wajib KTP</th>
                    <th class="px-4 py-3 text-center">% Akta Lahir</th>
                    <th class="px-4 py-3 text-center">% E-KTP</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $d)
                    @php
                        $tahun = substr($d->kode_semester, 0, 4);
                        $sem = substr($d->kode_semester, 4, 2);
                    @endphp
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                            <span class="font-medium">Sem. {{ $sem }} - {{ $tahun }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $d->desa->nama_desa ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $d->desa->kecamatan->nama_kecamatan ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <div class="font-bold text-gray-800">{{ number_format($d->jumlah_penduduk, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">L: {{ number_format($d->jumlah_laki, 0, ',', '.') }} | P: {{ number_format($d->jumlah_perempuan, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                            <div>KK: {{ number_format($d->kartu_keluarga, 0, ',', '.') }}</div>
                            <div class="text-xs">WKTP: {{ number_format($d->wajib_ktp, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $d->akta_kelahiran_persen >= 90 ? 'bg-green-100 text-green-700' : ($d->akta_kelahiran_persen >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $d->akta_kelahiran_persen }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $d->kepemilikan_ktp_persen >= 95 ? 'bg-green-100 text-green-700' : ($d->kepemilikan_ktp_persen >= 80 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $d->kepemilikan_ktp_persen }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kependudukan.show', $d->id) }}" class="p-1 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('kependudukan.edit', $d->id) }}" class="p-1 text-yellow-600 hover:text-yellow-800 bg-yellow-50 hover:bg-yellow-100 rounded transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('kependudukan.destroy', $d->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data ini?')">
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
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data kependudukan.
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
