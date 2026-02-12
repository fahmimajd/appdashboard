@extends('layouts.app')

@section('title', 'Distribusi Barang')
@section('subtitle', 'Kirim barang dari Dinas ke Kecamatan')

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

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <form action="{{ route('management-barang.distribusi.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Distribusi</label>
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Barang -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Pilih Barang (Stok Dinas)</label>
                <select name="barang_id" id="barang_id" required onchange="updateMaxStok()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white">
                    <option value="" data-stok="0" data-satuan="">-- Pilih Barang --</option>
                    @foreach($barangs as $b)
                        @php $stok = $stokDinas[$b->id]->jumlah ?? 0; @endphp
                        @if($stok > 0)
                            <option value="{{ $b->id }}" data-stok="{{ $stok }}" data-satuan="{{ $b->satuan }}">
                                {{ $b->kode }} - {{ $b->nama }} (Sisa: {{ number_format($stok) }} {{ $b->satuan }})
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500" id="stok_info"></p>
            </div>

            <!-- Tujuan -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Kecamatan Tujuan</label>
                <select name="kode_kecamatan" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatans as $k)
                        <option value="{{ $k->kode_kecamatan }}">{{ $k->nama_kecamatan }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Jumlah -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Jumlah Didistribusikan</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="number" name="jumlah" id="jumlah" min="1" required class="block w-full rounded-md border-gray-300 pl-4 pr-12 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="0">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm" id="satuan_label"></span>
                    </div>
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Keterangan / Berita Acara</label>
                <textarea name="keterangan" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Nomor Surat Jalan / BAST"></textarea>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium shadow-md">
                    Proses Distribusi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateMaxStok() {
    const select = document.getElementById('barang_id');
    const option = select.options[select.selectedIndex];
    const stok = option.getAttribute('data-stok');
    const satuan = option.getAttribute('data-satuan');
    const input = document.getElementById('jumlah');
    const label = document.getElementById('satuan_label');
    const info = document.getElementById('stok_info');

    if (stok) {
        input.max = stok;
        label.textContent = satuan;
        info.textContent = `Maksimal distribusi: ${stok} ${satuan}`;
    } else {
        input.removeAttribute('max');
        label.textContent = '';
        info.textContent = '';
    }
}
</script>
@endsection
