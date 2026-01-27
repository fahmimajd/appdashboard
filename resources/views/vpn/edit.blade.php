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
                <label for="password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" value="{{ old('password', $vpn->password) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring focus:ring-yellow-200 transition duration-200 pr-10">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                        <!-- Eye Icon -->
                        <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <!-- Eye Off Icon (Hidden) -->
                        <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <script>
                function togglePassword() {
                    const passwordInput = document.getElementById('password');
                    const eyeIcon = document.getElementById('eye-icon');
                    const eyeOffIcon = document.getElementById('eye-off-icon');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        eyeIcon.classList.add('hidden');
                        eyeOffIcon.classList.remove('hidden');
                    } else {
                        passwordInput.type = 'password';
                        eyeIcon.classList.remove('hidden');
                        eyeOffIcon.classList.add('hidden');
                    }
                }
            </script>

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
