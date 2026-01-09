@extends('layouts.app')

@section('title', 'Edit Akun VPN')
@section('subtitle', 'Update informasi akun VPN desa')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('vpn.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Edit Akun VPN: {{ $vpn->desa->nama_desa }}</h2>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('vpn.update', $vpn->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="md:col-span-2">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username VPN <span class="text-red-500">*</span></label>
                <input type="text" id="username" name="username" value="{{ old('username', $vpn->username) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                @error('username')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2 bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                <label for="password" class="block text-sm font-bold text-gray-700 mb-2">Ganti Password</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password"
                       class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring focus:ring-yellow-200 transition duration-200">
                <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter jika diisi.</p>
                @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="jenis_vpn" class="block text-sm font-medium text-gray-700 mb-2">Jenis Koneksi</label>
                <select id="jenis_vpn" name="jenis_vpn" class="w-full rounded-lg border-gray-300 focus:border-blue-500 transition">
                    <option value="OpenVPN" {{ old('jenis_vpn', $vpn->jenis_vpn) == 'OpenVPN' ? 'selected' : '' }}>OpenVPN</option>
                    <option value="WireGuard" {{ old('jenis_vpn', $vpn->jenis_vpn) == 'WireGuard' ? 'selected' : '' }}>WireGuard</option>
                    <option value="L2TP" {{ old('jenis_vpn', $vpn->jenis_vpn) == 'L2TP' ? 'selected' : '' }}>L2TP</option>
                    <option value="PPTP" {{ old('jenis_vpn', $vpn->jenis_vpn) == 'PPTP' ? 'selected' : '' }}>PPTP</option>
                </select>
                @error('jenis_vpn')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
            <a href="{{ route('vpn.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
