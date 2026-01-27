@extends('layouts.app')

@section('title', 'Detail VPN ' . ($vpn->desa->nama_desa ?? 'Desa'))
@section('subtitle', 'Informasi detail akun VPN desa')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Detail Akun VPN</h3>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('vpn.index') }}" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali
                    </a>
                    
                    <a href="{{ route('vpn.edit', $vpn->id) }}" class="px-3 py-2 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition duration-200 flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>

                    <form action="{{ route('vpn.destroy', $vpn->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus akun VPN ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition duration-200 flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Location Info -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider border-b pb-2">Informasi Wilayah</h4>
                    
                    <div>
                        <p class="text-xs text-gray-500">Desa</p>
                        <p class="text-base font-medium text-gray-900">{{ $vpn->desa->nama_desa ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Kecamatan</p>
                        <p class="text-base font-medium text-gray-900">{{ $vpn->desa->kecamatan->nama_kecamatan ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Kabupaten</p>
                        <p class="text-base font-medium text-gray-900">{{ $vpn->desa->kecamatan->kabupaten->nama_kabupaten ?? 'Bondowoso' }}</p>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider border-b pb-2">Detail Akun</h4>
                    
                    <div>
                        <p class="text-xs text-gray-500">Username VPN</p>
                        <p class="font-mono text-base bg-gray-50 p-2 rounded border border-gray-200 inline-block text-gray-800">
                            {{ $vpn->username }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Password</p>
                        <div class="flex items-center gap-2">
                             <p class="font-mono text-base bg-gray-50 p-2 rounded border border-gray-200 inline-block text-gray-800">
                                {{ $vpn->password }}
                            </p>
                            <span class="text-xs text-gray-400 italic">(Ditampilkan untuk admin)</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Jenis Koneksi</p>
                        <div class="mt-1">
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $vpn->getVpnTypeBadge() }}">
                                {{ $vpn->jenis_vpn }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Instructions or Notes could go here -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-100">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Informasi Penggunaan</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>
                                Pastikan username dan password ini diberikan secara aman kepada operator desa yang bersangkutan. 
                                Akun ini digunakan untuk mengakses jaringan intranet kabupaten.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
