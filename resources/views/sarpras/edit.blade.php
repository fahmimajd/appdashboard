@extends('layouts.app')

@section('title', 'Edit Data Sarpras')
@section('subtitle', 'Update data infrastruktur desa')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('sarpras.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Edit Sarpras: {{ $sarpras->desa->nama_desa }}</h2>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('sarpras.update', $sarpras->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Hidden Desa ID, as we don't change desa assignment usually -->

        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">Aset Hardware</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="komputer" class="block text-sm font-medium text-gray-700 mb-2">Jumlah PC / Laptop <span class="text-red-500">*</span></label>
                <input type="number" min="0" id="komputer" name="komputer" value="{{ old('komputer', $sarpras->komputer) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                @error('komputer')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="printer" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Printer <span class="text-red-500">*</span></label>
                <input type="number" min="0" id="printer" name="printer" value="{{ old('printer', $sarpras->printer) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 transition">
                @error('printer')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">Infrastruktur & Jaringan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                 <label for="ruang_pelayanan" class="block text-sm font-medium text-gray-700 mb-2">Ruang Pelayanan Khusus <span class="text-red-500">*</span></label>
                 <select id="ruang_pelayanan" name="ruang_pelayanan" class="w-full rounded-lg border-gray-300">
                     <option value="Ada" {{ old('ruang_pelayanan', $sarpras->ruang_pelayanan) == 'Ada' ? 'selected' : '' }}>Ada</option>
                     <option value="Tidak" {{ old('ruang_pelayanan', $sarpras->ruang_pelayanan) == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                 </select>
            </div>
             <div>
                <label for="internet" class="block text-sm font-medium text-gray-700 mb-2">Jaringan Internet (Unit/Titik) <span class="text-red-500">*</span></label>
                <input type="number" min="0" id="internet" name="internet" value="{{ old('internet', $sarpras->internet) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500">
                @error('internet')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">Provider Internet / ISP</label>
                <input type="text" id="provider" name="provider" value="{{ old('provider', $sarpras->provider) }}" placeholder="Contoh: Telkom Indihome, Biznet..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500">
                 @error('provider')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
            <a href="{{ route('sarpras.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
