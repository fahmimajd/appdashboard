@extends('layouts.app')

@section('title', 'Detail Pelayanan')
@section('subtitle', 'Rincian pelayanan desa bulan ' . $period)

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold text-gray-800">{{ $pageTitle }}</h3>
            <p class="text-gray-500">{{ $period }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Desa</th>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3 text-center">Aktivasi IKD</th>
                    <th class="px-4 py-3 text-center">Akta Lahir</th>
                    <th class="px-4 py-3 text-center">Akta Mati</th>
                    <th class="px-4 py-3 text-center">KK</th>
                    <th class="px-4 py-3 text-center">Pindah</th>
                    <th class="px-4 py-3 text-center">KIA</th>
                    <th class="px-4 py-3 text-center">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $index => $row)
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-4 py-3 text-sm text-gray-500 text-center w-12">{{ $index + 1 }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        {{ $row->desa->nama_desa ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">
                        {{ $row->desa->kecamatan->nama_kecamatan ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-center">{{ number_format($row->total_aktivasi_ikd) }}</td>
                    <td class="px-4 py-3 text-sm text-center">{{ number_format($row->total_akta_kelahiran) }}</td>
                    <td class="px-4 py-3 text-sm text-center">{{ number_format($row->total_akta_kematian) }}</td>
                    <td class="px-4 py-3 text-sm text-center">{{ number_format($row->total_pengajuan_kk) }}</td>
                    <td class="px-4 py-3 text-sm text-center">{{ number_format($row->total_pengajuan_pindah) }}</td>
                    <td class="px-4 py-3 text-sm text-center">{{ number_format($row->total_pengajuan_kia) }}</td>
                    <td class="px-4 py-3 text-sm text-center font-bold text-blue-600 bg-blue-50">
                        {{ number_format($row->total_pelayanan) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                        Belum ada data pelayanan bulan ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 font-semibold text-gray-900 border-t border-gray-200">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right">Grand Total</td>
                    <td class="px-4 py-3 text-center">{{ number_format($data->sum('total_aktivasi_ikd')) }}</td>
                    <td class="px-4 py-3 text-center">{{ number_format($data->sum('total_akta_kelahiran')) }}</td>
                    <td class="px-4 py-3 text-center">{{ number_format($data->sum('total_akta_kematian')) }}</td>
                    <td class="px-4 py-3 text-center">{{ number_format($data->sum('total_pengajuan_kk')) }}</td>
                    <td class="px-4 py-3 text-center">{{ number_format($data->sum('total_pengajuan_pindah')) }}</td>
                    <td class="px-4 py-3 text-center">{{ number_format($data->sum('total_pengajuan_kia')) }}</td>
                    <td class="px-4 py-3 text-center text-blue-700 bg-blue-100">
                        {{ number_format($data->sum('total_pelayanan')) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
