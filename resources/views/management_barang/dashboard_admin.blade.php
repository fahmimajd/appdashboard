@extends('layouts.app')

@section('title', 'Management Barang')
@section('subtitle', 'Monitoring stok dan distribusi barang')

@section('content')
<div class="space-y-6">
    <!-- Header Tools -->
    <div class="flex flex-col lg:flex-row justify-between gap-4">
        <div class="flex gap-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('management-barang.stok-masuk') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 shadow-md flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Stok Masuk (Dinas)
                </a>
                <a href="{{ route('management-barang.distribusi') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Distribusi ke Kecamatan
                </a>
                <a href="{{ route('management-barang.master') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200 shadow-md flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Master Barang
                </a>
            @endif
             <a href="{{ route('management-barang.riwayat') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200 shadow-md flex items-center gap-2 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Riwayat Mutasi
            </a>
        </div>
    </div>

    <!-- Stok Dinas -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Stok Dinas
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($dinasStocks as $stok)
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 text-center">
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">{{ $stok->barang->nama }}</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($stok->jumlah) }}</div>
                    <div class="text-xs text-gray-400">{{ $stok->barang->satuan }}</div>
                </div>
            @endforeach
            @if($dinasStocks->isEmpty())
                <div class="col-span-full text-center text-gray-500 py-4 text-sm">Belum ada data stok di Dinas.</div>
            @endif
        </div>
    </div>

    <!-- Monitoring Kecamatan -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 flex-1 overflow-hidden flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                Monitoring Stok Kecamatan
            </h3>
        </div>

        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Kecamatan</th>
                        <th colspan="3" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l bg-blue-50">Blangko KTP</th>
                        <th colspan="3" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l bg-purple-50">Blangko KIA</th>
                        <th colspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l bg-yellow-50">Logistik (Sisa %)</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-blue-50 border-l">Sistem</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-blue-50">Laporan</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-blue-50">Selisih</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-purple-50 border-l">Sistem</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-purple-50">Laporan</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-purple-50">Selisih</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-yellow-50 border-l">Ribbon</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 border-b bg-yellow-50">Film</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dashboardData as $d)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $d['kecamatan']->nama_kecamatan }}</td>
                        
                        <!-- KTP -->
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center border-l text-gray-600">{{ number_format($d['ktp']['sistem']) }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                            @if($d['ktp']['laporan'] !== null)
                                {{ number_format($d['ktp']['laporan']) }}
                                <div class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($d['ktp']['date'])->format('d/m') }}
                                </div>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-bold">
                            @if(isset($d['ktp']['selisih']))
                                @if($d['ktp']['selisih'] == 0)
                                    <span class="text-green-500">OK</span>
                                @else
                                    <span class="text-red-600 bg-red-100 px-2 py-0.5 rounded-full">{{ $d['ktp']['selisih'] }}</span>
                                @endif
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>

                        <!-- KIA -->
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center border-l text-gray-600">{{ number_format($d['kia']['sistem']) }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                            @if($d['kia']['laporan'] !== null)
                                {{ number_format($d['kia']['laporan']) }}
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-bold">
                             @if(isset($d['kia']['selisih']))
                                @if($d['kia']['selisih'] == 0)
                                    <span class="text-green-500">OK</span>
                                @else
                                    <span class="text-red-600 bg-red-100 px-2 py-0.5 rounded-full">{{ $d['kia']['selisih'] }}</span>
                                @endif
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>

                        <!-- Logistik -->
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center border-l">
                            @if($d['ribbon_persen'] !== null)
                                <span class="{{ $d['ribbon_persen'] < 20 ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ $d['ribbon_persen'] }}%
                                </span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                            @if($d['film_persen'] !== null)
                                <span class="{{ $d['film_persen'] < 20 ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ $d['film_persen'] }}%
                                </span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Tidak ada data kecamatan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
