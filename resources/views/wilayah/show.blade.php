@extends('layouts.app')

@section('title', 'Detail Desa')
@section('subtitle', 'Informasi lengkap desa')

@section('content')
<div class="space-y-6">
    <!-- Header / Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('wilayah.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <div class="flex gap-3">
            @if(!auth()->user()->isSupervisor())
            <a href="{{ route('wilayah.edit', $desa->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Data
            </a>
            @endif
        </div>
    </div>

    <!-- Main Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Profil Desa: {{ $desa->nama_desa }}</h3>
            <p class="text-sm text-gray-500">{{ $desa->kecamatan->nama_kecamatan ?? '-' }} - Kabupaten Mandailing Natal</p>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Kode Desa</span>
                <span class="text-gray-800 font-medium">{{ $desa->kode_desa }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Kepala Desa</span>
                <span class="text-gray-800 font-medium">{{ $desa->kepala_desa ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Status</span>
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $desa->status_desa == 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $desa->status_desa == 1 ? 'Aktif' : 'Non-Aktif' }}
                </span>
            </div>
            <div>
                <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Email</span>
                <span class="text-gray-800">{{ $desa->email_desa ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Telepon</span>
                <span class="text-gray-800">{{ $desa->telepon_desa ?? '-' }}</span>
            </div>
            <div class="md:col-span-3">
                <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Alamat Kantor</span>
                <span class="text-gray-800">{{ $desa->alamat_kantor ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- VPN Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Informasi VPN</h4>
            @if($desa->vpn)
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">IP Address</span>
                        <span class="font-medium font-mono">{{ $desa->vpn->ip_vpn }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Akun VPN</span>
                        <span class="font-medium">{{ $desa->vpn->akun_vpn }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $desa->vpn->status_vpn == 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $desa->vpn->status_vpn == 1 ? 'Connected' : 'Disconnected' }}
                        </span>
                    </div>
                </div>
            @else
                <div class="text-center py-4 text-gray-400 bg-gray-50 rounded-lg">
                    Belum ada data VPN terdaftar
                </div>
            @endif
        </div>

        <!-- Sarpras Stats -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Sarana Prasarana</h4>
            @if($desa->sarpras->count() > 0)
                <ul class="space-y-2">
                    @foreach($desa->sarpras->take(5) as $item)
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-700">{{ $item->nama_sarpras }}</span>
                            <span class="text-xs px-2 py-0.5 rounded {{ $item->kondisi == 'baik' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ ucfirst($item->kondisi) }} ({{ $item->jumlah }})
                            </span>
                        </li>
                    @endforeach
                </ul>
                @if($desa->sarpras->count() > 5)
                    <div class="mt-3 text-right">
                        <a href="{{ route('sarpras.index', ['desa_id' => $desa->id]) }}" class="text-xs text-blue-600 hover:underline">Lihat Semua Sarpras &rarr;</a>
                    </div>
                @endif
            @else
                <div class="text-center py-4 text-gray-400 bg-gray-50 rounded-lg">
                    Belum ada data sarpras
                </div>
            @endif
        </div>
    </div>

    <!-- Petugas & Pendamping -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Petugas List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h4 class="text-lg font-bold text-gray-800">Daftar Petugas</h4>
                <a href="{{ route('petugas.create') }}" class="text-xs text-blue-600 hover:underline">+ Tambah</a>
            </div>
            
            @if($desa->petugas->count() > 0)
                <div class="space-y-4">
                    @foreach($desa->petugas as $p)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                {{ substr($p->nama_petugas, 0, 2) }}
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-800 text-sm">{{ $p->nama_petugas }}</h5>
                                <p class="text-xs text-gray-500">{{ $p->jabatan_petugas }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-gray-400 bg-gray-50 rounded-lg">
                    Belum ada petugas ditugaskan
                </div>
            @endif
        </div>

        <!-- Pendamping Account -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h4 class="text-lg font-bold text-gray-800">Akun Pendamping</h4>
                <a href="{{ route('pendamping.create') }}" class="text-xs text-blue-600 hover:underline">+ Tambah</a>
            </div>

            @if($desa->pendamping->count() > 0)
                <div class="space-y-4">
                    @foreach($desa->pendamping as $u)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs">
                                {{ substr($u->nama, 0, 2) }}
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-800 text-sm">{{ $u->nama }}</h5>
                                <p class="text-xs text-gray-500">{{ $u->akses }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-gray-400 bg-gray-50 rounded-lg">
                    Belum ada akun pendamping
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
