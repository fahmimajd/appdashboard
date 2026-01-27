@extends('layouts.app')

@section('title', 'Detail Kinerja')
@section('subtitle', 'Rincian laporan kinerja petugas')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Rejected notification -->
    @if(session('rejected'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('rejected') }}
        </div>
    @endif

    <!-- Header / Actions -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('kinerja.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <div class="flex gap-3">
            <a href="{{ route('kinerja.edit', $kinerja->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Data
            </a>
            @if(!auth()->user()->isPetugas())
            <form action="{{ route('kinerja.destroy', $kinerja->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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

    <!-- Status Alert -->
    @if($kinerja->hasPendingApproval())
    <div class="mb-6 p-4 bg-yellow-100 border border-yellow-300 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-yellow-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">{{ count($kinerja->getPendingFields()) }} field menunggu approval</span>
                @if($kinerja->last_proposed_at)
                    <span class="text-sm text-yellow-600">• Diajukan {{ $kinerja->last_proposed_at->diffForHumans() }}</span>
                @endif
            </div>
            @if(!auth()->user()->isPetugas())
            <div class="flex gap-2">
                <form action="{{ route('kinerja.approve-all', $kinerja->id) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                        Approve Semua
                    </button>
                </form>
                <form action="{{ route('kinerja.reject-all', $kinerja->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak semua perubahan?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                        Tolak Semua
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100 bg-blue-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::create()->month($kinerja->bulan)->translatedFormat('F') }} {{ $kinerja->tahun }}</h3>
                <p class="text-sm text-gray-500">Laporan Kinerja Bulanan</p>
            </div>
            <div class="flex flex-col items-end">
                <span class="text-2xl font-bold text-blue-600">{{ $kinerja->getTotalPelayanan() }}</span>
                <span class="text-xs text-gray-500 uppercase">Total Pelayanan</span>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Petugas</span>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-xs">
                             {{ substr($kinerja->petugas->nama ?? '?', 0, 2) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $kinerja->petugas->nama ?? 'Petugas Tidak Ditemukan' }}</p>
                            <p class="text-xs text-gray-500">{{ $kinerja->petugas->nik ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Wilayah</span>
                    <p class="font-medium text-gray-800">{{ $kinerja->desa->nama_desa ?? '-' }}</p>
                    <p class="text-xs text-gray-500">Kec. {{ $kinerja->desa->kecamatan->nama_kecamatan ?? '-' }}</p>
                </div>
            </div>

            <h4 class="text-sm font-bold text-gray-800 border-b pb-2 mb-4">Rincian Pelayanan</h4>
            
            @php
                $fieldLabels = \App\Models\KinerjaApprovalLog::$fieldLabels;
                $isPendamping = !auth()->user()->isPetugas();
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b">
                            <th class="py-2">Field</th>
                            <th class="py-2 text-center">Nilai Saat Ini</th>
                            <th class="py-2 text-center">Nilai Diajukan</th>
                            @if($isPendamping && $kinerja->hasPendingApproval())
                            <th class="py-2 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach(\App\Models\KinerjaPetugas::$approvableFields as $fieldName)
                            @php
                                [$currentValue, $proposedValue] = $kinerja->getFieldWithProposed($fieldName);
                                $hasPending = $proposedValue !== null;
                            @endphp
                            <tr class="{{ $hasPending ? 'bg-yellow-50' : '' }}">
                                <td class="py-3 font-medium text-gray-700">
                                    {{ $fieldLabels[$fieldName] ?? $fieldName }}
                                </td>
                                <td class="py-3 text-center font-mono {{ $hasPending ? 'text-gray-500' : 'text-gray-800' }}">
                                    {{ $currentValue }}
                                </td>
                                <td class="py-3 text-center font-mono">
                                    @if($hasPending)
                                        <span class="text-yellow-700 font-bold">{{ $proposedValue }}</span>
                                        <span class="ml-1 px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded">Pending</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                @if($isPendamping && $kinerja->hasPendingApproval())
                                <td class="py-3 text-right">
                                    @if($hasPending)
                                    <div class="flex items-center justify-end gap-1">
                                        <form action="{{ route('kinerja.approve-field', $kinerja->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="field_name" value="{{ $fieldName }}">
                                            <button type="submit" class="p-1.5 text-green-600 hover:text-green-800 bg-green-100 hover:bg-green-200 rounded transition" title="Approve">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('kinerja.reject-field', $kinerja->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak perubahan ini?')">
                                            @csrf
                                            <input type="hidden" name="field_name" value="{{ $fieldName }}">
                                            <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 bg-red-100 hover:bg-red-200 rounded transition" title="Reject">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Approval History -->
    @php
        $logs = $kinerja->approvalLogs()->with(['proposer', 'actor'])->orderBy('created_at', 'desc')->limit(20)->get();
    @endphp
    @if($logs->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <h4 class="font-bold text-gray-800">Riwayat Approval</h4>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                @foreach($logs as $log)
                <div class="flex items-start gap-3 text-sm {{ $log->isRejected() ? 'bg-red-50 p-3 rounded-lg border border-red-100' : '' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        @if($log->isApproved())
                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        @else
                        <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800">
                            <span class="font-medium">{{ $log->getFieldLabel() }}</span>
                            @if($log->isApproved())
                                diubah dari <span class="font-mono">{{ $log->old_value }}</span> menjadi <span class="font-mono font-bold">{{ $log->final_value }}</span>
                            @else
                                <span class="text-red-600 font-medium">ditolak</span> (tetap <span class="font-mono">{{ $log->old_value }}</span>)
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Diajukan oleh {{ $log->proposer->nama ?? 'Unknown' }} • 
                            {{ $log->isApproved() ? 'Di-approve' : 'Ditolak' }} oleh {{ $log->actor->nama ?? 'Unknown' }} • 
                            {{ $log->created_at->diffForHumans() }}
                        </p>
                        @if($log->rejection_reason)
                        <p class="text-xs text-red-600 mt-1">Alasan: {{ $log->rejection_reason }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
