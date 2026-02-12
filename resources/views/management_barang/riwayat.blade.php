@extends('layouts.app')

@section('title', 'Riwayat Mutasi Barang')
@section('subtitle', 'Log pergerakan stok masuk, keluar, dan penyesuaian')

@section('content')
<div class="space-y-6">
     <div class="flex justify-between items-center">
        <a href="{{ route('management-barang.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form action="{{ route('management-barang.riwayat') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Barang</label>
                <select name="barang_id" class="w-full text-sm rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Barang</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->id }}" {{ request('barang_id') == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full text-sm rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full text-sm rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">Filter</button>
            </div>
        </form>
    </div>

    <!-- Timeline / Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan / Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oleh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($mutasis as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $m->tanggal->format('d/m/Y') }}
                            <div class="text-xs text-gray-400">{{ $m->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $m->barang->nama }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            @php
                                $badges = [
                                    'masuk' => 'bg-green-100 text-green-800',
                                    'distribusi' => 'bg-blue-100 text-blue-800',
                                    'pemakaian' => 'bg-orange-100 text-orange-800',
                                    'penyesuaian' => 'bg-gray-100 text-gray-800',
                                    'koreksi' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badges[$m->tipe_mutasi] ?? 'bg-gray-100' }}">
                                {{ ucfirst($m->tipe_mutasi) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold {{ $m->jumlah > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $m->jumlah > 0 ? '+' : '' }}{{ number_format($m->jumlah) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="mb-1">{{ $m->keterangan }}</div>
                            @if($m->tipe_mutasi == 'distribusi')
                                <div class="text-xs text-blue-600">
                                    Dinas <span class="text-gray-400">-></span> {{ $m->tujuanKecamatan->nama_kecamatan }}
                                </div>
                            @elseif($m->lokasi_asal_tipe == 'kecamatan')
                                <div class="text-xs text-gray-400">
                                    {{ $m->asalKecamatan->nama_kecamatan }}
                                </div>
                            @elseif($m->lokasi_tujuan_tipe == 'dinas')
                                <div class="text-xs text-green-600">Stok Dinas</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $m->user->nama ?? 'System' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data mutasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $mutasis->links() }}
        </div>
    </div>
</div>
@endsection
