@extends('layouts.app')

@section('title', 'Tambah Pendamping')
@section('subtitle', 'Form tambah akun pendamping baru')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('pendamping.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Akun Pendamping Baru</h2>
    </div>

    <form action="{{ route('pendamping.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- NIK -->
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">NIK <span class="text-red-500">*</span></label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16" placeholder="16 digit NIK" 
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

            <!-- Role / Akses -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Akses / Role <span class="text-red-500">*</span></label>
                <select id="role" name="role" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('role') border-red-500 @enderror">
                    <option value="">-- Pilih Akses --</option>
                    <option value="desa" {{ old('role') == 'desa' ? 'selected' : '' }}>Desa</option>
                    <option value="pendamping" {{ old('role') == 'pendamping' ? 'selected' : '' }}>Pendamping</option>
                    <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Desa (Optional if admin?) -->
            <div class="md:col-span-2">
                <label for="desa_id" class="block text-sm font-medium text-gray-700 mb-2">Wilayah Tugas (Desa)</label>
                <select id="desa_id" name="desa_id" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">-- Tidak Ada / Global --</option>
                    @foreach($desas as $desa)
                        <option value="{{ $desa->id }}" {{ old('desa_id') == $desa->id ? 'selected' : '' }}>
                            {{ $desa->nama_desa }} ({{ $desa->kecamatan->nama_kecamatan ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika Admin Tingkat Kabupaten</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                <input type="password" id="password" name="password" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
            </div>

            <!-- Status Aktif -->
            <div class="md:col-span-2 mt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} 
                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Akun Aktif</span>
                </label>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('pendamping.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Akun
            </button>
        </div>
    </form>
</div>
@endsection
