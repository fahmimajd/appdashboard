@extends('layouts.app')

@section('title', 'Pending Approval Belum Rekam')
@section('subtitle', 'Daftar data belum rekam yang menunggu approval')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
    <!-- Notifications -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('rejected'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('rejected') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('belum_rekam.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Belum Rekam
            </a>
            <h2 class="text-xl font-bold text-gray-800">Data Menunggu Approval</h2>
            <p class="text-sm text-gray-500">{{ $pendingData->total() }} data menunggu approval</p>
        </div>
    </div>

    @if($pendingData->isEmpty())
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-500 text-lg">Tidak ada data yang menunggu approval</p>
            <a href="{{ route('belum_rekam.index') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                Kembali ke Daftar
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($pendingData as $item)
            <div class="border border-yellow-200 rounded-xl bg-yellow-50 p-6">
                <!-- Header Info -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 pb-4 border-b border-yellow-200">
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $item->nama_lgkp }}</h3>
                        <p class="text-sm text-gray-500 font-mono">NIK: {{ $item->nik }}</p>
                        <p class="text-sm text-gray-500">{{ $item->desa->nama_desa ?? '-' }} • {{ $item->kecamatan->nama_kecamatan ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Diajukan: {{ $item->last_proposed_at ? $item->last_proposed_at->diffForHumans() : '-' }}
                            @if($item->proposer)
                                oleh {{ $item->proposer->nama ?? $item->proposer->nik }}
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-2 mt-4 md:mt-0">
                        <form action="{{ route('belum_rekam.approve-all', $item->nik) }}" method="POST" class="inline-block" onsubmit="return confirm('Approve semua perubahan untuk data ini?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve Semua
                            </button>
                        </form>
                        <form action="{{ route('belum_rekam.reject-all', $item->nik) }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak semua perubahan untuk data ini?')">
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
                                <th class="py-2 px-3">Nilai Lama</th>
                                <th class="py-2 px-3">Nilai Baru</th>
                                <th class="py-2 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-yellow-200">
                            @php
                                $fieldLabels = \App\Models\BelumRekamApprovalLog::$fieldLabels;
                            @endphp
                            @foreach($item->getPendingFields() as $fieldName)
                                @php
                                    [$currentValue, $proposedValue] = $item->getFieldWithProposed($fieldName);
                                @endphp
                                <tr class="hover:bg-yellow-100 transition">
                                    <td class="py-3 px-3 font-medium text-gray-700">
                                        {{ $fieldLabels[$fieldName] ?? $fieldName }}
                                    </td>
                                    <td class="py-3 px-3 text-gray-500">
                                        {{ $currentValue ?: '-' }}
                                    </td>
                                    <td class="py-3 px-3 font-bold text-yellow-700">
                                        {{ $proposedValue }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('belum_rekam.approve-field', $item->nik) }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="field_name" value="{{ $fieldName }}">
                                                <button type="submit" class="p-1.5 text-green-600 hover:text-green-800 bg-green-100 hover:bg-green-200 rounded transition duration-200" title="Approve">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('belum_rekam.reject-field', $item->nik) }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak perubahan ini?')">
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
            {{ $pendingData->links() }}
        </div>
    @endif
</div>
@endsection
