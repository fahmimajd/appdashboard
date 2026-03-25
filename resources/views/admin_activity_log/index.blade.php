@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('subtitle', 'Riwayat aktivitas seluruh pengguna sistem')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="card">
        <form method="GET" action="{{ route('admin-log.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Module Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modul</label>
                    <select name="module" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $key => $label)
                            <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aksi</label>
                    <select name="action" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $key => $label)
                            <option value="{{ $key }}" {{ request('action') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- User Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama User</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama user..." 
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin-log.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Riwayat Aktivitas</h3>
                <p class="text-sm text-gray-600">Total: {{ number_format($logs->total()) }} log</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Deskripsi</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="text-sm text-gray-600 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <span class="font-medium">{{ $log->user_name }}</span>
                            @if($log->user)
                                <span class="block text-xs text-gray-500">{{ $log->user->akses ?? '' }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $actionColors = [
                                    'create' => 'bg-green-100 text-green-800',
                                    'update' => 'bg-blue-100 text-blue-800',
                                    'delete' => 'bg-red-100 text-red-800',
                                    'approve' => 'bg-emerald-100 text-emerald-800',
                                    'reject' => 'bg-orange-100 text-orange-800',
                                    'export' => 'bg-purple-100 text-purple-800',
                                    'login' => 'bg-cyan-100 text-cyan-800',
                                    'logout' => 'bg-gray-100 text-gray-800',
                                    'upload' => 'bg-indigo-100 text-indigo-800',
                                    'reset-password' => 'bg-yellow-100 text-yellow-800',
                                    'toggle-status' => 'bg-teal-100 text-teal-800',
                                ];
                                $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full font-medium {{ $colorClass }}">
                                {{ $log->getActionLabel() }}
                            </span>
                        </td>
                        <td>
                            <span class="text-sm">{{ $log->getModuleLabel() }}</span>
                        </td>
                        <td class="text-sm text-gray-600">
                            @php
                                $descParts = explode(', ', $log->description);
                            @endphp
                            @foreach($descParts as $part)
                                <div class="mb-1">{{ $part }}</div>
                            @endforeach
                        </td>
                        <td class="text-sm text-gray-500">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Belum ada log aktivitas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
