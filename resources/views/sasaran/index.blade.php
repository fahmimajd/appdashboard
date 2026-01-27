@extends('layouts.app')

@section('title', 'Data Sasaran')
@section('subtitle', $title)

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Rekapitulasi Sasaran Per Desa</h3>
    <div class="overflow-x-auto max-h-64">
        <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-2 w-16">No</th>
                    <th class="px-4 py-2">Nama Desa</th>
                    <th class="px-4 py-2 text-center w-32">Belum Rekam KTP-EL</th>
                    <th class="px-4 py-2 text-center w-32">Belum Akte Kelahiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($summaryData as $index => $desa)
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-4 py-2 text-sm text-gray-500 text-center">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $desa->nama_desa }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700 text-center">{{ number_format($desa->belum_rekam_count) }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700 text-center">{{ number_format($desa->belum_akte_count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Header Tools -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <!-- Search -->
        <form action="" method="GET" class="flex-1">
            <div class="relative max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIK..." 
                       class="w-full pl-10 pr-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </form>

        <!-- Actions -->
        <div>
            @if(request('search') || request('desa_id'))
                <a href="{{ url()->current() }}" class="mr-2 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset Filter
                </a>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">NIK</th>
                    <th class="px-4 py-3">Desa</th>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $item)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $item->nama }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->nik }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->desa->nama_desa ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->kecamatan->nama_kecamatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                {{ $item->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data sasaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $data->links() }}
    </div>
</div>
@endsection
