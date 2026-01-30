@extends('layouts.app')

@section('title', 'Sarpras Desa')
@section('subtitle', 'Data infrastruktur dan sarana prasarana desa')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Header Tools -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <!-- Search & Filter -->
        <form action="{{ route('sarpras.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="w-full md:w-64">
                <select name="desa_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="all">Semua Desa</option>
                    @foreach($desas as $d)
                        <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->nama_desa }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari desa..." 
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
                <a href="{{ route('sarpras.index') }}" class="mr-2 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
            <a href="{{ route('sarpras.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah Data
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3">Wilayah</th>
                    <th class="px-4 py-3 text-center">Komputer</th>
                    <th class="px-4 py-3 text-center">Printer</th>
                    <th class="px-4 py-3 text-center">Internet</th>
                    <th class="px-4 py-3 text-center">Ruang Pelayanan</th>
                    <th class="px-4 py-3 text-center">Skor Kelayakan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sarprases as $s)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $s->desa->nama_desa ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $s->desa->kecamatan->nama_kecamatan ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($s->komputer > 0)
                                <span class="text-blue-600 font-bold">{{ $s->komputer }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($s->printer > 0)
                                <span class="text-blue-600 font-bold">{{ $s->printer }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($s->internet > 0)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Ada</span>
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($s->provider, 15) }}</div>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Tidak</span>
                            @endif
                        </td>
                         <td class="px-4 py-3 text-sm text-center">
                            @if($s->ruang_pelayanan == 'Ada')
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Ada</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Tidak</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            @php $score = $s->getCompletenessScore(); @endphp
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $score >= 75 ? 'bg-green-500' : ($score >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $score }}%"></div>
                                </div>
                                <span class="text-xs font-semibold">{{ $score }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(!auth()->user()->isSupervisor())
                                <a href="{{ route('sarpras.edit', $s->id) }}" class="p-1 text-yellow-600 hover:text-yellow-800 bg-yellow-50 hover:bg-yellow-100 rounded transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('sarpras.destroy', $s->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data sarpras ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded transition duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data sarpras.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $sarprases->links() }}
    </div>
</div>
@endsection
