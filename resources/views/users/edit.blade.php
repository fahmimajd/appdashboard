@extends('layouts.app')

@section('title', 'Edit User')
@section('subtitle', 'Ubah data user')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- NIK -->
            <div class="mb-4">
                <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">NIK <span class="text-red-500">*</span></label>
                <input type="text" name="nik" id="nik" value="{{ old('nik', $user->nik) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('nik') border-red-500 @enderror"
                       placeholder="Masukkan 16 digit NIK" maxlength="16" required>
                @error('nik')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama -->
            <div class="mb-4">
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $user->nama) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('nama') border-red-500 @enderror"
                       placeholder="Masukkan nama lengkap" required>
                @error('nama')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label for="akses" class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                <select name="akses" id="akses" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('akses') border-red-500 @enderror" required>
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('akses', $user->akses) == $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
                @error('akses')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-4">
                <label for="status_aktif" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status_aktif" id="status_aktif" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('status_aktif') border-red-500 @enderror" required>
                    <option value="Aktif" {{ old('status_aktif', $user->status_aktif) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Tidak Aktif" {{ old('status_aktif', $user->status_aktif) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                @error('status_aktif')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kecamatan (Optional) -->
            <div class="mb-4">
                <label for="kecamatan_id" class="block text-sm font-medium text-gray-700 mb-1">Kecamatan (Opsional)</label>
                <select name="kecamatan_id" id="kecamatan_id" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Pilih Kecamatan</option>
                    @foreach($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->kode_kecamatan }}" {{ old('kecamatan_id', $user->kode_kecamatan) == $kecamatan->kode_kecamatan ? 'selected' : '' }}>{{ $kecamatan->nama_kecamatan }}</option>
                    @endforeach
                </select>
                @error('kecamatan_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password (Optional) -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                <input type="password" name="password" id="password" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 @error('password') border-red-500 @enderror"
                       placeholder="Kosongkan jika tidak ingin mengubah">
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
            </div>

            <!-- Password Confirmation -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200"
                       placeholder="Ulangi password baru">
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
