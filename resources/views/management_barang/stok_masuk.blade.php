@extends('layouts.app')

@section('title', 'Tambah Stok Dinas')
@section('subtitle', 'Input barang baru / stok masuk ke Dinas')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <a href="{{ route('management-barang.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="{{ route('management-barang.stok-masuk.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Masuk</label>
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            </div>

            <!-- Barang -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Pilih Barang</label>
                <select name="barang_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white">
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->id }}">{{ $b->kode }} - {{ $b->nama }} ({{ $b->satuan }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Jumlah -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Jumlah Masuk</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="number" name="jumlah" min="1" required class="block w-full rounded-md border-gray-300 pl-4 pr-12 focus:border-green-500 focus:ring-green-500 sm:text-sm" placeholder="0">
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Keterangan / Sumber</label>
                <textarea name="keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="Contoh: Pengadaan APBD 2026 / Terima dari Pusat"></textarea>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium shadow-md">
                    Simpan Stok Masuk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
