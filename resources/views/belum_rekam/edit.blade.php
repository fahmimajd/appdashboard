@extends('layouts.app')

@section('title', 'Edit Data Belum Rekam')
@section('subtitle', 'Ubah data belum rekam KTP')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('belum_rekam.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($isPetugas) && $isPetugas)
        <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>Perubahan yang Anda buat akan memerlukan <strong>approval dari Admin/Pendamping</strong> sebelum diterapkan.</span>
        </div>
    @endif

    <!-- Pending Changes Info -->
    @if($data->hasPendingApproval())
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-800 rounded-lg">
            <div class="flex items-center gap-2 font-medium mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Data ini memiliki perubahan yang menunggu approval
            </div>
            <ul class="text-sm ml-7 space-y-1">
                @foreach($data->getPendingFields() as $field)
                    @php
                        [$current, $proposed] = $data->getFieldWithProposed($field);
                        $label = \App\Models\BelumRekamApprovalLog::$fieldLabels[$field] ?? $field;
                    @endphp
                    <li><strong>{{ $label }}:</strong> "{{ $current ?: '-' }}" → "{{ $proposed }}"</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('belum_rekam.update', $data->nik) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Read-only Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">NIK</label>
                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono">
                    {{ $data->nik }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    {{ $data->nama_lgkp }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    {{ $data->tgl_lhr }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    {{ $data->jenis_klm == 'L' ? 'Laki-laki' : 'Perempuan' }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Desa</label>
                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    {{ $data->desa->nama_desa ?? '-' }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    {{ $data->kecamatan->nama_kecamatan ?? '-' }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status (WKTP)</label>
                <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    {{ $data->wktp_ket ?? '-' }}
                </div>
            </div>
        </div>

        <hr class="border-gray-200">

        <!-- Editable Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <select name="keterangan" id="keterangan" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 @error('keterangan') border-red-500 @enderror">
                    @foreach($keteranganOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('keterangan', $data->keterangan) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('keterangan')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('belum_rekam.index') }}" 
                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                Batal
            </a>
            <button type="submit" 
                class="px-6 py-3 {{ isset($isPetugas) && $isPetugas ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded-lg transition duration-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ isset($isPetugas) && $isPetugas ? 'Ajukan Perubahan' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>
</div>
@endsection
