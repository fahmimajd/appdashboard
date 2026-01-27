@extends('layouts.app')

@section('title', 'Pending Approval Kinerja')
@section('subtitle', 'Daftar kinerja yang menunggu approval')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Rejected notification -->
    @if(session('rejected'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('rejected') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('kinerja.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Kinerja
            </a>
            <h2 class="text-xl font-bold text-gray-800">Kinerja Menunggu Approval</h2>
            <p class="text-sm text-gray-500">{{ $pendingKinerjas->total() }} data kinerja menunggu approval</p>
        </div>
    </div>

    @if($pendingKinerjas->isEmpty())
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-500 text-lg">Tidak ada kinerja yang menunggu approval</p>
            <a href="{{ route('kinerja.index') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                Kembali ke Daftar
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($pendingKinerjas as $k)
            <div class="border border-yellow-200 rounded-xl bg-yellow-50 p-6">
                <!-- Header Info -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 pb-4 border-b border-yellow-200">
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $k->petugas->nama ?? 'Unknown' }}</h3>
                        <p class="text-sm text-gray-500">{{ $k->desa->nama_desa ?? '-' }} • {{ \Carbon\Carbon::create()->month($k->bulan)->translatedFormat('F') }} {{ $k->tahun }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Diajukan: {{ $k->last_proposed_at ? $k->last_proposed_at->diffForHumans() : '-' }}
                            @if($k->proposer)
                                oleh {{ $k->proposer->nama }}
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-2 mt-4 md:mt-0">
                        <form action="{{ route('kinerja.approve-all', $k->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Approve semua perubahan untuk kinerja ini?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve Semua
                            </button>
                        </form>
                        <form action="{{ route('kinerja.reject-all', $k->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak semua perubahan untuk kinerja ini?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Tolak Semua
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Pending Fields Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-600 uppercase">
                                <th class="py-2 px-3">Field</th>
                                <th class="py-2 px-3 text-center">Nilai Lama</th>
                                <th class="py-2 px-3 text-center">Nilai Baru</th>
                                <th class="py-2 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-yellow-200">
                            @php
                                $fieldLabels = \App\Models\KinerjaApprovalLog::$fieldLabels;
                            @endphp
                            @foreach($k->getPendingFields() as $fieldName)
                                @php
                                    [$currentValue, $proposedValue] = $k->getFieldWithProposed($fieldName);
                                @endphp
                                <tr class="hover:bg-yellow-100 transition">
                                    <td class="py-3 px-3 font-medium text-gray-700">
                                        {{ $fieldLabels[$fieldName] ?? $fieldName }}
                                    </td>
                                    <td class="py-3 px-3 text-center text-gray-500">
                                        {{ $currentValue }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-yellow-700">
                                        {{ $proposedValue }}
                                        <span class="text-xs text-gray-400 ml-1">(+{{ $proposedValue - $currentValue }})</span>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('kinerja.approve-field', $k->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="field_name" value="{{ $fieldName }}">
                                                <button type="submit" class="p-1.5 text-green-600 hover:text-green-800 bg-green-100 hover:bg-green-200 rounded transition duration-200" title="Approve">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('kinerja.reject-field', $k->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak perubahan ini?')">
                                                @csrf
                                                <input type="hidden" name="field_name" value="{{ $fieldName }}">
                                                <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 bg-red-100 hover:bg-red-200 rounded transition duration-200" title="Reject">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $pendingKinerjas->links() }}
        </div>
    @endif
</div>
@endsection
