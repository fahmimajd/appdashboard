@extends('layouts.app')

@section('title', 'Edit Data Kependudukan')
@section('subtitle', 'Form perubahan data statistik kependudukan')

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('kependudukan.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Edit Data Kependudukan</h2>
    </div>

    @php
        $tahun = substr($data->kode_semester, 0, 4);
        $sem = substr($data->kode_semester, 4, 1);
    @endphp

    <form action="{{ route('kependudukan.update', $data->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Wilayah & Periode (Read Only) -->
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">1. Wilayah & Periode</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
            <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Wilayah Desa</span>
                <span class="font-medium text-gray-800">{{ $data->desa->nama_desa ?? 'Unknown' }}</span>
                <input type="hidden" name="desa_id" value="{{ $data->desa->id ?? '' }}">
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tahun</span>
                <span class="font-medium text-gray-800">{{ $tahun }}</span>
                <input type="hidden" name="tahun" value="{{ $tahun }}">
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Semester</span>
                <span class="font-medium text-gray-800">Semester {{ $sem }}</span>
                <input type="hidden" name="semester" value="{{ $sem }}">
            </div>
        </div>

         <!-- Data Populasi Utama -->
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">2. Data Populasi Utama</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-50 p-4 rounded-lg">
                <label for="jumlah_penduduk" class="block text-sm font-bold text-gray-800 mb-2">Total Penduduk <span class="text-red-500">*</span></label>
                <input type="number" min="0" id="jumlah_penduduk" name="jumlah_penduduk" value="{{ old('jumlah_penduduk', $data->jumlah_penduduk) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                @error('jumlah_penduduk')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                 <label for="jumlah_laki" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Laki-Laki <span class="text-red-500">*</span></label>
                 <input type="number" min="0" id="jumlah_laki" name="jumlah_laki" value="{{ old('jumlah_laki', $data->jumlah_laki) }}" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 transition">
                 @error('jumlah_laki')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                 <label for="jumlah_perempuan" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Perempuan <span class="text-red-500">*</span></label>
                 <input type="number" min="0" id="jumlah_perempuan" name="jumlah_perempuan" value="{{ old('jumlah_perempuan', $data->jumlah_perempuan) }}" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 transition">
                 @error('jumlah_perempuan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                 <label for="kartu_keluarga" class="block text-sm font-medium text-gray-700 mb-2">Jumlah KK</label>
                 <input type="number" min="0" id="kartu_keluarga" name="kartu_keluarga" value="{{ old('kartu_keluarga', $data->kartu_keluarga) }}" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 transition">
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                 <label for="wajib_ktp" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Wajib KTP</label>
                 <input type="number" min="0" id="wajib_ktp" name="wajib_ktp" value="{{ old('wajib_ktp', $data->wajib_ktp) }}" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 transition">
            </div>
        </div>

        <!-- Kepemilikan Dokumen -->
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">3. Kepemilikan Dokumen</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div>
                 <label for="akta_kelahiran_jml" class="block text-sm font-medium text-gray-700 mb-2">Jml Akta Kelahiran</label>
                 <input type="number" min="0" id="akta_kelahiran_jml" name="akta_kelahiran_jml" value="{{ old('akta_kelahiran_jml', $data->akta_kelahiran_jml) }}" 
                        class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                 <label for="kepemilikan_ktp_jml" class="block text-sm font-medium text-gray-700 mb-2">Jml Kepemilikan KTP</label>
                 <input type="number" min="0" id="kepemilikan_ktp_jml" name="kepemilikan_ktp_jml" value="{{ old('kepemilikan_ktp_jml', $data->kepemilikan_ktp_jml) }}" 
                        class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                 <label for="kepemilikan_kia_jml" class="block text-sm font-medium text-gray-700 mb-2">Jml Kepemilikan KIA</label>
                 <input type="number" min="0" id="kepemilikan_kia_jml" name="kepemilikan_kia_jml" value="{{ old('kepemilikan_kia_jml', $data->kepemilikan_kia_jml) }}" 
                        class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                 <label for="akta_kematian_jml" class="block text-sm font-medium text-gray-700 mb-2">Jml Akta Kematian</label>
                 <input type="number" min="0" id="akta_kematian_jml" name="akta_kematian_jml" value="{{ old('akta_kematian_jml', $data->akta_kematian_jml) }}" 
                        class="w-full rounded-lg border-gray-300">
            </div>
        </div>

        <!-- Dinamika Kependudukan -->
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">4. Dinamika penduduk</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                 <label for="jumlah_kematian" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Kematian</label>
                 <input type="number" min="0" id="jumlah_kematian" name="jumlah_kematian" value="{{ old('jumlah_kematian', $data->jumlah_kematian) }}" 
                        class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                 <label for="pindah_keluar" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Pindah</label>
                 <input type="number" min="0" id="pindah_keluar" name="pindah_keluar" value="{{ old('pindah_keluar', $data->pindah_keluar) }}" 
                        class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                 <label for="status_kawin_jml" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Status Kawin</label>
                 <input type="number" min="0" id="status_kawin_jml" name="status_kawin_jml" value="{{ old('status_kawin_jml', $data->status_kawin_jml) }}" 
                        class="w-full rounded-lg border-gray-300">
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
            <a href="{{ route('kependudukan.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
