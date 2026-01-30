@extends('layouts.app')

@section('title', 'Detail Pendamping')
@section('subtitle', 'Informasi akun pendamping')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header / Actions -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('pendamping.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <div class="flex gap-3">
            @if(!auth()->user()->isSupervisor())
            <a href="{{ route('pendamping.edit', $pendamping->nik) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Akun
            </a>
            <form action="{{ route('pendamping.destroy', $pendamping->nik) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ $pendamping->nama }}</h3>
                <p class="text-sm text-gray-500">{{ $pendamping->akses }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                {{ substr($pendamping->nama, 0, 2) }}
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">NIK</span>
                    <span class="text-gray-800 font-medium font-mono text-lg">{{ $pendamping->nik }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Status Akun</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $pendamping->status_aktif == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $pendamping->status_aktif }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Nomor Ponsel</span>
                    <span class="text-gray-800">{{ $pendamping->nomor_ponsel ?? '-' }}</span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Wilayah Tugas (Desa)</span>
                    @if($pendamping->desa)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-800 font-medium">{{ $pendamping->desa->nama_desa }}</span>
                            <a href="{{ route('wilayah.desa.detail', $pendamping->desa->kode_desa) }}" class="text-xs text-blue-600 hover:underline border border-blue-200 px-2 py-0.5 rounded-full bg-blue-50">
                                Lihat Desa
                            </a>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Kec. {{ $pendamping->desa->kecamatan->nama_kecamatan ?? '-' }}</p>
                    @else
                        <span class="text-gray-400 italic">Tidak ada wilayah tugas spesifik (Global/Kabupaten)</span>
                    @endif
                </div>
                
                @if($pendamping->jenis_kelamin)
                <div>
                    <span class="block text-xs uppercase text-gray-400 tracking-wider font-semibold mb-1">Jenis Kelamin</span>
                    <span class="text-gray-800">{{ $pendamping->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
