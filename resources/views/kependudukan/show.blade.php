@extends('layouts.app')

@section('title', 'Detail Kependudukan')
@section('subtitle', 'Rincian statistik kependudukan')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header / Actions -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('kependudukan.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>

    </div>

    @php
        $tahun = substr($data->kode_semester, 0, 4);
        $sem = substr($data->kode_semester, 4, 1);
    @endphp

    <!-- Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100 bg-purple-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ $data->desa->nama_desa }}</h3>
                <p class="text-sm text-gray-600">Semester {{ $sem }} - Tahun {{ $tahun }}</p>
            </div>
            <div class="text-right">
                <span class="block text-2xl font-bold text-purple-700">{{ number_format($data->jumlah_penduduk, 0, ',', '.') }}</span>
                <span class="text-xs text-gray-500 uppercase">Total Penduduk</span>
            </div>
        </div>

        <div class="p-6">
            <!-- Main Stats -->
            <h4 class="text-sm font-bold text-gray-800 border-b pb-2 mb-4">Populasi Utama</h4>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <span class="block text-xs uppercase text-blue-600 font-bold mb-1">Laki-Laki</span>
                    <span class="text-xl font-bold text-gray-800">{{ number_format($data->jumlah_laki, 0, ',', '.') }}</span>
                </div>
                <div class="p-4 bg-pink-50 rounded-lg">
                    <span class="block text-xs uppercase text-pink-600 font-bold mb-1">Perempuan</span>
                    <span class="text-xl font-bold text-gray-800">{{ number_format($data->jumlah_perempuan, 0, ',', '.') }}</span>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <span class="block text-xs uppercase text-gray-600 font-bold mb-1">Kartu Keluarga</span>
                    <span class="text-xl font-bold text-gray-800">{{ number_format($data->kartu_keluarga, 0, ',', '.') }}</span>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <span class="block text-xs uppercase text-gray-600 font-bold mb-1">Wajib KTP</span>
                    <span class="text-xl font-bold text-gray-800">{{ number_format($data->wajib_ktp, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Coverage Stats -->
            <h4 class="text-sm font-bold text-gray-800 border-b pb-2 mb-4">Capaian Dokumen</h4>
            <div class="space-y-4 mb-8">
                <!-- Akta Lahir -->
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-gray-700">Akta Kelahiran</span>
                        <span class="text-sm font-bold {{ $data->akta_kelahiran_persen >= 90 ? 'text-green-600' : 'text-yellow-600' }}">{{ $data->akta_kelahiran_persen }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min($data->akta_kelahiran_persen, 100) }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 flex justify-between">
                        <span>Kepemilikan: {{ number_format($data->akta_kelahiran_jml, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- KTP -->
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-gray-700">Perekaman E-KTP</span>
                        <span class="text-sm font-bold {{ $data->kepemilikan_ktp_persen >= 95 ? 'text-green-600' : 'text-yellow-600' }}">{{ $data->kepemilikan_ktp_persen }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ min($data->kepemilikan_ktp_persen, 100) }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 flex justify-between">
                        <span>Kepemilikan: {{ number_format($data->kepemilikan_ktp_jml, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                 <!-- Akta Kematian -->
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-gray-700">Akta Kematian (dari total kematian)</span>
                        <span class="text-sm font-bold text-gray-700">{{ $data->akta_kematian_persen }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-gray-600 h-2.5 rounded-full" style="width: {{ min($data->akta_kematian_persen, 100) }}%"></div>
                    </div>
                     <div class="text-xs text-gray-500 mt-1 flex justify-between">
                        <span>Terbit: {{ number_format($data->akta_kematian_jml, 0, ',', '.') }}</span>
                        <span>Total Kematian: {{ number_format($data->jumlah_kematian, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Other Stats -->
            <h4 class="text-sm font-bold text-gray-800 border-b pb-2 mb-4">Dinamika & Lainnya</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                 <div class="bg-gray-50 p-3 rounded text-center">
                    <span class="block text-2xl font-bold text-gray-700">{{ number_format($data->jumlah_kematian, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-500 uppercase">Kematian</span>
                 </div>
                 <div class="bg-gray-50 p-3 rounded text-center">
                    <span class="block text-2xl font-bold text-gray-700">{{ number_format($data->pindah_keluar, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-500 uppercase">Pindah Keluar</span>
                 </div>
                 <div class="bg-gray-50 p-3 rounded text-center">
                    <span class="block text-2xl font-bold text-gray-700">{{ number_format($data->kepemilikan_kia_jml, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-500 uppercase">Kepemilikan KIA</span>
                 </div>
                 <div class="bg-gray-50 p-3 rounded text-center">
                    <span class="block text-2xl font-bold text-gray-700">{{ number_format($data->status_kawin_jml, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-500 uppercase">Status Kawin</span>
                 </div>
            </div>
        </div>
    </div>
</div>
@endsection
