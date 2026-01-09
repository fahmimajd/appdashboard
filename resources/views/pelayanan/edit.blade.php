@extends('layouts.app')

@section('title', 'Edit Data Pelayanan')
@section('subtitle', 'Update nomor pelayanan atau pengaduan')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('pelayanan.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Edit Data #{{ $pelayanan->id }}</h2>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('pelayanan.update', $pelayanan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                 <label for="nomor_pelayanan" class="block text-sm font-bold text-gray-700 mb-2">Nomor Pelayanan</label>
                 <input type="text" id="nomor_pelayanan" name="nomor_pelayanan" value="{{ old('nomor_pelayanan', $pelayanan->nomor_pelayanan) }}" placeholder="Contoh: PL-2023-001"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                 @error('nomor_pelayanan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                 <label for="nomor_pengaduan" class="block text-sm font-bold text-gray-700 mb-2">Nomor Pengaduan</label>
                 <input type="text" id="nomor_pengaduan" name="nomor_pengaduan" value="{{ old('nomor_pengaduan', $pelayanan->nomor_pengaduan) }}" placeholder="Contoh: ADU-2023-001"
                        class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring focus:ring-yellow-200 transition duration-200">
                 @error('nomor_pengaduan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            
             <div class="text-xs text-gray-500 pt-2">
                Dibuat pada: {{ $pelayanan->getTanggalFormatted() }}
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
            <a href="{{ route('pelayanan.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
