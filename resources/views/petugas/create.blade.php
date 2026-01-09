@extends('layouts.app')

@section('title', 'Tambah Petugas')
@section('subtitle', 'Form penambahan petugas lapangan baru')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('petugas.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Data Petugas Baru</h2>
    </div>

    <form action="{{ route('petugas.store') }}" method="POST" x-data="{ jenis: '{{ old('jenis_petugas', '') }}' }">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- NIK -->
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">NIK / NIP <span class="text-red-500">*</span></label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16" placeholder="NIK atau NIP Petugas" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('nik') border-red-500 @enderror">
                @error('nik')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama -->
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Nama Lengkap" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('nama') border-red-500 @enderror">
                @error('nama')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nomor Ponsel -->
            <div>
                <label for="nomor_ponsel" class="block text-sm font-medium text-gray-700 mb-2">Nomor Ponsel</label>
                <input type="text" id="nomor_ponsel" name="nomor_ponsel" value="{{ old('nomor_ponsel') }}" placeholder="08..." 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
            </div>

            <!-- Jenis / Level Petugas -->
            <div>
                <label for="jenis_petugas" class="block text-sm font-medium text-gray-700 mb-2">Tingkat Penugasan <span class="text-red-500">*</span></label>
                <select id="jenis_petugas" name="jenis_petugas" x-model="jenis"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('jenis_petugas') border-red-500 @enderror">
                    <option value="">-- Pilih Tingkat --</option>
                    <option value="1">Antar Desa (Petugas Desa)</option>
                    <option value="2">Antar Kecamatan (Petugas Kecamatan)</option>
                    <option value="3">Dinas (Kabupaten)</option>
                </select>
                @error('jenis_petugas')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Level Akses (Only for Petugas Desa) -->
            <div x-show="jenis == '1'" style="display: none;">
                <label for="level_akses" class="block text-sm font-medium text-gray-700 mb-2">Level Akses <span class="text-red-500">*</span></label>
                <select id="level_akses" name="level_akses" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('level_akses') border-red-500 @enderror">
                    @foreach(range(0, 5) as $lvl)
                        <option value="{{ $lvl }}" {{ old('level_akses') == (string)$lvl ? 'selected' : '' }}>Level {{ $lvl }}</option>
                    @endforeach
                </select>
                @error('level_akses')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Wilayah Selection (Dynamic) -->
            <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-200" x-show="jenis !== ''">
                <!-- Case 1: Desa -->
                <div x-show="jenis == '1'">
                    <label for="wilayah_desa_id" class="block text-sm font-medium text-gray-700 mb-2">Wilayah Desa <span class="text-red-500">*</span></label>
                    <select id="wilayah_desa_id" name="wilayah_desa_id" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                        <option value="">-- Pilih Desa --</option>
                        @foreach($desas as $desa)
                            <option value="{{ $desa->kode_desa }}" {{ old('wilayah_desa_id') == $desa->kode_desa ? 'selected' : '' }}>
                                {{ $desa->nama_desa }} (Kec. {{ $desa->kecamatan->nama_kecamatan ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Case 2: Kecamatan -->
                <div x-show="jenis == '2'">
                    <label for="wilayah_kecamatan_id" class="block text-sm font-medium text-gray-700 mb-2">Wilayah Kecamatan <span class="text-red-500">*</span></label>
                    <select id="wilayah_kecamatan_id" name="wilayah_kecamatan_id" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach($kecamatans as $kec)
                            <option value="{{ $kec->kode_kecamatan }}" {{ old('wilayah_kecamatan_id') == $kec->kode_kecamatan ? 'selected' : '' }}>
                                {{ $kec->nama_kecamatan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Case 3: Kabupaten -->
                <div x-show="jenis == '3'">
                    <label for="wilayah_kabupaten_id" class="block text-sm font-medium text-gray-700 mb-2">Wilayah Kabupaten <span class="text-red-500">*</span></label>
                    <select id="wilayah_kabupaten_id" name="wilayah_kabupaten_id" 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                        <option value="">-- Pilih Kabupaten --</option>
                        @foreach($kabupatens as $kab)
                            <option value="{{ $kab->kode_kabupaten }}" {{ old('wilayah_kabupaten_id') == $kab->kode_kabupaten ? 'selected' : '' }}>
                                {{ $kab->nama_kabupaten }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="md:col-span-2">
                <span class="block text-sm font-medium text-gray-700 mb-2">Status Petugas <span class="text-red-500">*</span></span>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_petugas" value="1" {{ old('status_petugas', '1') == '1' ? 'checked' : '' }} class="text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_petugas" value="0" {{ old('status_petugas') == '0' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-700">Non-Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('petugas.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Petugas
            </button>
        </div>
    </form>
</div>
@endsection
