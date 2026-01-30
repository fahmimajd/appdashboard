@extends('layouts.app')

@section('title', 'Detail Petugas')
@section('subtitle', 'Informasi lengkap petugas lapangan')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header / Actions -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('petugas.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <div class="flex gap-3">
            @if(!auth()->user()->isSupervisor())
            <a href="{{ route('petugas.edit', $petugas->nik) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Petugas
            </a>
            <form action="{{ route('petugas.destroy', $petugas->nik) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus petugas ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ $petugas->nama }}</h3>
                <p class="text-sm text-gray-500">{{ $petugas->level_akses }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-lg">
                {{ substr($petugas->nama, 0, 2) }}
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">NIK / NIP</span>
                    <span class="text-gray-800 font-medium font-mono text-lg">{{ $petugas->nik }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Status</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $petugas->status_aktif == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $petugas->status_aktif }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Nomor Ponsel</span>
                    <span class="text-gray-800">{{ $petugas->nomor_ponsel ?? '-' }}</span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Wilayah Penugasan</span>
                    @if($petugas->level_akses == 'Desa' && $petugas->desa)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-800 font-medium">{{ $petugas->desa->nama_desa }}</span>
                            <a href="{{ route('wilayah.desa.detail', $petugas->desa->kode_desa) }}" class="text-xs text-blue-600 hover:underline border border-blue-200 px-2 py-0.5 rounded-full bg-blue-50">
                                Lihat Desa
                            </a>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Kec. {{ $petugas->desa->kecamatan->nama_kecamatan ?? '-' }}</p>
                        
                    @elseif($petugas->level_akses == 'Kecamatan' && $petugas->kecamatan)
                         <span class="text-gray-800 font-medium">Kecamatan {{ $petugas->kecamatan->nama_kecamatan }}</span>
                         
                    @elseif($petugas->level_akses == 'Dinas' && $petugas->kabupaten)
                        <span class="text-gray-800 font-medium">Kabupaten {{ $petugas->kabupaten->nama_kabupaten }}</span>
                        
                    @else
                        <span class="text-gray-400 italic">Data wilayah tidak lengkap</span>
                    @endif
                </div>
                
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Tanggal Mulai Akses</span>
                    <span class="text-gray-800">{{ $petugas->tanggal_mulai_aktif ?? '-' }}</span>
                </div>
                
                <div x-show="'{{ $petugas->status_aktif }}' == 'Nonaktif'" class="{{ $petugas->status_aktif == 'Nonaktif' ? '' : 'hidden' }}">
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Keterangan Non-Aktif</span>
                    <p class="text-sm text-gray-600 bg-red-50 p-3 rounded border border-red-100">
                        {{ $petugas->keterangan_nonaktif ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kinerja Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
         <div class="flex justify-between items-center mb-4">
            <h4 class="text-lg font-bold text-gray-800">Riwayat Kinerja</h4>
            <a href="{{ route('kinerja.index') }}?petugas_id={{ $petugas->id ?? '' }}" class="text-sm text-blue-600 hover:underline">Lihat Detail &rarr;</a>
        </div>
        
        @if($petugas->kinerja->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase border-b border-gray-100 cursor-default">
                             <th class="py-2">Periode</th>
                             <th class="py-2">Total Pelayanan</th>
                             <th class="py-2">Desa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($petugas->kinerja->sortByDesc('tahun')->sortByDesc('bulan')->take(5) as $kinerja)
                            <tr>
                                <td class="py-2 text-sm">{{ $kinerja->bulan_nama }} {{ $kinerja->tahun }}</td>
                                <td class="py-2 text-sm font-medium">{{ $kinerja->total_pelayanan ?? 0 }}</td>
                                <td class="py-2 text-sm text-gray-500">{{ $kinerja->desa->nama_desa ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-400 py-4">Belum ada data kinerja tercatat.</p>
        @endif
    </div>
</div>
@endsection
