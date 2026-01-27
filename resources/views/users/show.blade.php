@extends('layouts.app')

@section('title', 'Detail User')
@section('subtitle', 'Informasi lengkap user')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <!-- User Avatar and Name -->
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-200">
            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl">
                {{ substr($user->nama, 0, 2) }}
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ $user->nama }}</h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                        @if($user->akses == 'Admin') bg-purple-100 text-purple-700
                        @elseif($user->akses == 'Supervisor') bg-blue-100 text-blue-700
                        @elseif($user->akses == 'Pendamping') bg-green-100 text-green-700
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ $user->akses }}
                    </span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->status_aktif == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->status_aktif }}
                    </span>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">NIK</span>
                <span class="text-sm font-medium text-gray-900 font-mono">{{ $user->nik }}</span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Role</span>
                <span class="text-sm font-medium text-gray-900">{{ $user->akses }}</span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Status</span>
                <span class="text-sm font-medium text-gray-900">{{ $user->status_aktif }}</span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Kecamatan</span>
                <span class="text-sm font-medium text-gray-900">{{ $user->kecamatan->nama_kecamatan ?? '-' }}</span>
            </div>

            @if($user->pendamping)
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-500">Terhubung ke Pendamping</span>
                <span class="text-sm font-medium text-green-600">Ya</span>
            </div>
            @endif

            <div class="flex items-center justify-between py-3">
                <span class="text-sm text-gray-500">Dibuat</span>
                <span class="text-sm font-medium text-gray-900">{{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                Kembali
            </a>
            <a href="{{ route('users.edit', $user->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200 shadow-md">
                Edit User
            </a>
        </div>
    </div>
</div>
@endsection
