@extends('layouts.app')

@section('title', 'Rekapitulasi Sasaran')
@section('subtitle', 'Ringkasan data belum rekam dan belum akte per desa')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 h-full flex flex-col">
    <!-- Header & Filter - Fixed -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 flex-shrink-0">
        <h3 class="text-lg font-semibold text-gray-800">Data Sasaran Per Desa</h3>
        
        <form action="{{ route('sasaran.rekapitulasi') }}" method="GET" class="flex flex-col md:flex-row gap-4 w-full">
            @if(!empty($kecamatans) && count($kecamatans) > 0)
            <div class="w-full md:w-64">
                <select name="kode_kecamatan" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ trim($kec->kode_kecamatan) }}" {{ request('kode_kecamatan') == trim($kec->kode_kecamatan) ? 'selected' : '' }}>
                            {{ $kec->nama_kecamatan }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="w-full md:w-64">
                <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Semua Status</option>
                    <option value="WKTP PEMULA 2027" {{ request('status') == 'WKTP PEMULA 2027' ? 'selected' : '' }}>WKTP PEMULA 2027</option>
                    <option value="WKTP PEMULA 2026" {{ request('status') == 'WKTP PEMULA 2026' ? 'selected' : '' }}>WKTP PEMULA 2026</option>
                    <option value="WKTP S/D 31-12-2025" {{ request('status') == 'WKTP S/D 31-12-2025' ? 'selected' : '' }}>WKTP S/D 31-12-2025</option>
                </select>
            </div>
            
            @if(request('kode_kecamatan') || request('status'))
                <a href="{{ route('sasaran.rekapitulasi') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>
    
    <!-- Table Container - Scrollable -->
    <div class="flex-1 overflow-auto min-h-0">
        <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-2 w-16">No</th>
                    <th class="px-4 py-2">Nama Kecamatan</th>
                    <th class="px-4 py-2">Nama Desa</th>
                    <th class="px-4 py-2 text-center w-32">Belum Rekam KTP-EL</th>
                    <th class="px-4 py-2 text-center w-32">Belum Akte Kelahiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($summaryData as $index => $desa)
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-4 py-2 text-sm text-gray-500 text-center">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $desa->kecamatan->nama_kecamatan ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $desa->nama_desa }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700 text-center">{{ number_format($desa->belum_rekam_count) }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700 text-center">{{ number_format($desa->belum_akte_count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
