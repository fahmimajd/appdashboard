<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi Petugas - {{ config('app.name') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-primary-500 to-primary-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Logo/Title -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Registrasi Petugas Kecamatan</h1>
                <p class="text-gray-600 mt-2">Daftar untuk mengakses sistem pelaporan kinerja</p>
            </div>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Register Form -->
            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <!-- NIK -->
                <div class="mb-4">
                    <label for="nik" class="label text-sm">NIK (16 Digit)</label>
                    <input type="text" 
                           id="nik" 
                           name="nik" 
                           class="input w-full" 
                           placeholder="Masukkan NIK"
                           value="{{ old('nik') }}"
                           maxlength="16"
                           required autofocus>
                </div>

                <!-- Nama Lengkap -->
                <div class="mb-4">
                    <label for="nama" class="label text-sm">Nama Lengkap</label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           class="input w-full" 
                           placeholder="Nama Lengkap Petugas"
                           value="{{ old('nama') }}"
                           required>
                </div>

                <!-- Nomor Ponsel -->
                <div class="mb-4">
                    <label for="nomor_ponsel" class="label text-sm">Nomor Ponsel (WhatsApp)</label>
                    <input type="tel" 
                           id="nomor_ponsel" 
                           name="nomor_ponsel" 
                           class="input w-full" 
                           placeholder="08xxxxxxxxxx"
                           value="{{ old('nomor_ponsel') }}"
                           required>
                </div>

                <!-- Kecamatan -->
                <div class="mb-4">
                    <label for="kode_kecamatan" class="label text-sm">Wilayah Kecamatan</label>
                    <select name="kode_kecamatan" id="kode_kecamatan" class="input w-full" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach($kecamatans as $kec)
                            <option value="{{ $kec->kode_kecamatan }}" {{ old('kode_kecamatan') == $kec->kode_kecamatan ? 'selected' : '' }}>
                                {{ $kec->nama_kecamatan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Password -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="password" class="label text-sm">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="input w-full" 
                               placeholder="Min. 8 karakter"
                               required>
                    </div>
                    <div>
                        <label for="password_confirmation" class="label text-sm">Konfirmasi Password</label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="input w-full" 
                               placeholder="Ulangi password"
                               required>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-full py-3 text-lg mb-4">
                    Daftar Sekarang
                </button>

                <!-- Login Link -->
                <div class="text-center text-sm">
                    <span class="text-gray-600">Sudah punya akun?</span>
                    <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-800 font-medium ml-1">
                        Login di sini
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
