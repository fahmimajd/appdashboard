@extends('layouts.app')

@section('title', 'Detail Kinerja')
@section('subtitle', 'Rincian laporan kinerja petugas')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header / Actions -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('kinerja.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <div class="flex gap-3">
            <a href="{{ route('kinerja.edit', $kinerja->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Data
            </a>
            <form action="{{ route('kinerja.destroy', $kinerja->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100 bg-blue-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::create()->month($kinerja->bulan)->translatedFormat('F') }} {{ $kinerja->tahun }}</h3>
                <p class="text-sm text-gray-500">Laporan Kinerja Bulanan</p>
            </div>
            <div class="flex flex-col items-end">
                <span class="text-2xl font-bold text-blue-600">{{ $kinerja->total_pelayanan }}</span>
                <span class="text-xs text-gray-500 uppercase">Total Pelayanan</span>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Petugas</span>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-xs">
                             {{ substr($kinerja->petugas->nama ?? '?', 0, 2) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $kinerja->petugas->nama ?? 'Petugas Tidak Ditemukan' }}</p>
                            <p class="text-xs text-gray-500">{{ $kinerja->petugas->nik ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Wilayah</span>
                    <p class="font-medium text-gray-800">{{ $kinerja->desa->nama_desa ?? '-' }}</p>
                    <p class="text-xs text-gray-500">Kec. {{ $kinerja->desa->kecamatan->nama_kecamatan ?? '-' }}</p>
                </div>
                
                 @if($kinerja->tanggal_lapor)
                <div>
                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tanggal Input</span>
                    <p class="text-gray-800">{{ \Carbon\Carbon::parse($kinerja->created_at)->translatedFormat('d F Y H:i') }}</p>
                </div>
                @endif
            </div>

            <h4 class="text-sm font-bold text-gray-800 border-b pb-2 mb-4">Rincian Pelayanan</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-8">
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Aktivasi IKD</span>
                    <span class="font-medium font-mono">{{ $kinerja->aktivasi_ikd }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Total IKD Desa</span>
                    <span class="font-medium font-mono">{{ $kinerja->ikd_desa }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Akta Kelahiran</span>
                    <span class="font-medium font-mono">{{ $kinerja->akta_kelahiran }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Akta Kematian</span>
                    <span class="font-medium font-mono">{{ $kinerja->akta_kematian }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Pengajuan KK</span>
                    <span class="font-medium font-mono">{{ $kinerja->pengajuan_kk }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Pengajuan Pindah</span>
                    <span class="font-medium font-mono">{{ $kinerja->pengajuan_pindah }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Pengajuan KIA</span>
                    <span class="font-medium font-mono">{{ $kinerja->pengajuan_kia }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Login Aplikasi</span>
                    <span class="font-medium font-mono">{{ $kinerja->jumlah_login }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
