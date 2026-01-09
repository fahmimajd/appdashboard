@extends('layouts.app')

@section('title', 'Edit Data Desa')
@section('subtitle', 'Form perubahan data wilayah desa')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('wilayah.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Edit Data Desa: {{ $desa->nama_desa }}</h2>
    </div>

    <form action="{{ route('wilayah.update', $desa->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kode Desa (Disabled usually) -->
            <div>
                <label for="kode_desa" class="block text-sm font-medium text-gray-700 mb-2">Kode Desa</label>
                <input type="text" id="kode_desa" name="kode_desa" value="{{ old('kode_desa', $desa->kode_desa) }}" readonly
                       class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">Kode desa tidak dapat diubah</p>
            </div>

            <!-- Nama Desa -->
            <div>
                <label for="nama_desa" class="block text-sm font-medium text-gray-700 mb-2">Nama Desa <span class="text-red-500">*</span></label>
                <input type="text" id="nama_desa" name="nama_desa" value="{{ old('nama_desa', $desa->nama_desa) }}" placeholder="Contoh: Desa Suka Maju" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('nama_desa') border-red-500 @enderror">
                @error('nama_desa')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kecamatan -->
            <div class="md:col-span-2">
                <label for="kecamatan_id" class="block text-sm font-medium text-gray-700 mb-2">Kecamatan <span class="text-red-500">*</span></label>
                <select id="kecamatan_id" name="kecamatan_id" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('kecamatan_id') border-red-500 @enderror">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}" {{ old('kecamatan_id', $desa->kecamatan_id) == $kec->id ? 'selected' : '' }}>
                            {{ $kec->nama_kecamatan }}
                        </option>
                    @endforeach
                </select>
                @error('kecamatan_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kepala Desa -->
            <div>
                <label for="kepala_desa" class="block text-sm font-medium text-gray-700 mb-2">Nama Kepala Desa</label>
                <input type="text" id="kepala_desa" name="kepala_desa" value="{{ old('kepala_desa', $desa->kepala_desa) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
            </div>

            <!-- Telepon Desa -->
            <div>
                <label for="telepon_desa" class="block text-sm font-medium text-gray-700 mb-2">Telepon Kantor</label>
                <input type="text" id="telepon_desa" name="telepon_desa" value="{{ old('telepon_desa', $desa->telepon_desa) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
            </div>

            <!-- Email Desa -->
            <div class="md:col-span-2">
                <label for="email_desa" class="block text-sm font-medium text-gray-700 mb-2">Email Desa</label>
                <input type="email" id="email_desa" name="email_desa" value="{{ old('email_desa', $desa->email_desa) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('email_desa') border-red-500 @enderror">
                @error('email_desa')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alamat Kantor -->
            <div class="md:col-span-2">
                <label for="alamat_kantor" class="block text-sm font-medium text-gray-700 mb-2">Alamat Kantor Desa</label>
                <textarea id="alamat_kantor" name="alamat_kantor" rows="3" 
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">{{ old('alamat_kantor', $desa->alamat_kantor) }}</textarea>
            </div>

            <!-- Status -->
            <div class="md:col-span-2">
                <span class="block text-sm font-medium text-gray-700 mb-2">Status Desa <span class="text-red-500">*</span></span>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_desa" value="1" {{ old('status_desa', $desa->status_desa) == '1' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_desa" value="0" {{ old('status_desa', $desa->status_desa) == '0' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Non-Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('wilayah.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
