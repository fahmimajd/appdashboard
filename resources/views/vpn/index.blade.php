@extends('layouts.app')

@section('title', 'Data VPN Desa')
@section('subtitle', 'Daftar akun VPN untuk koneksi intranet desa')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Header Tools -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <!-- Search & Filter -->
        <form action="{{ route('vpn.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="w-full md:w-64">
                <select name="desa_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Desa</option>
                    @foreach($desas as $d)
                        <option value="{{ $d->kode_desa }}" {{ request('desa_id') == $d->kode_desa ? 'selected' : '' }}>
                            {{ $d->nama_desa }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari username atau desa..." 
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
                <a href="{{ route('vpn.index') }}" class="mr-2 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
            <a href="{{ route('vpn.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Buat Akun VPN
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3">Wilayah</th>
                    <th class="px-4 py-3">Username VPN</th>
                    <th class="px-4 py-3 text-center">Jenis Koneksi</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($vpns as $v)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $v->desa->nama_desa ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $v->desa->kecamatan->nama_kecamatan ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-800">
                            {{ $v->username }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $v->getVpnTypeBadge() }}">
                                {{ $v->jenis_vpn }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(!auth()->user()->isSupervisor())
                                <a href="{{ route('vpn.edit', $v->id) }}" class="p-1 text-yellow-600 hover:text-yellow-800 bg-yellow-50 hover:bg-yellow-100 rounded transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                @endif
                                <a href="{{ route('vpn.show', $v->id) }}" class="p-1 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data VPN.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $vpns->links() }}
    </div>
</div>
@endsection
