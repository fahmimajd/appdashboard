@extends('layouts.app')

@section('title', 'Detail Stok Kecamatan')
@section('subtitle', 'Monitoring stok: ' . $kecamatan->nama_kecamatan)

@section('content')
<div class="space-y-6">
    <!-- Header Tools -->
    <div class="flex justify-between items-center">
        <a href="{{ route('management-barang.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
        
        <form action="{{ route('management-barang.dashboard') }}" method="GET" class="flex gap-2">
            <select name="kode_kecamatan" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                @foreach($kecamatans as $k)
                    <option value="{{ $k->kode_kecamatan }}" {{ $k->kode_kecamatan == $kecamatan->kode_kecamatan ? 'selected' : '' }}>
                        {{ $k->nama_kecamatan }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Stock Comparison Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Rekonsiliasi Stok</h3>
            <p class="text-sm text-gray-500">Perbandingan antara stok tercatat di sistem (hasil distribusi - pemakaian otomatis) dengan stok fisik yang dilaporkan petugas.</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-blue-600 uppercase tracking-wider bg-blue-50">Stok Sistem</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-purple-600 uppercase tracking-wider bg-purple-50">Stok Terlapor</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Selisih</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($stockComparison as $item)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item['barang']->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $item['barang']->kode }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item['barang']->satuan }}</td>
                        
                        <!-- Sistem -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center font-semibold bg-blue-50">
                            {{ number_format($item['stok_sistem']) }}
                        </td>

                        <!-- Laporan -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center bg-purple-50">
                            @if($item['stok_laporan'] !== null)
                                {{ number_format($item['stok_laporan']) }}
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ \Carbon\Carbon::parse($item['last_report_date'])->format('d M Y') }}
                                </div>
                            @elseif($item['barang']->auto_kurang)
                                <span class="text-gray-400 italic">Belum ada laporan</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- Selisih -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($item['selisih'] !== null)
                                <span class="text-base font-bold {{ $item['selisih'] != 0 ? 'text-red-500' : 'text-gray-400' }}">
                                    {{ $item['selisih'] > 0 ? '+' : '' }}{{ $item['selisih'] }}
                                </span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($item['stok_laporan'] !== null)
                                @if($item['selisih'] == 0)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Sinkron
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Selisih
                                    </span>
                                @endif
                            @elseif(!$item['barang']->auto_kurang)
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Manual / Persentase
                                </span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Menunggu Laporan
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
