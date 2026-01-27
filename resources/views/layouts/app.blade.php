<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Dashboard Pelayanan') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50">
    <div x-data="sidebar()" class="min-h-screen flex">
        <!-- Sidebar -->
        <aside :class="open ? 'w-64' : 'w-20'" class="bg-white shadow-lg transition-all duration-300 fixed h-full z-10">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 x-show="open" class="text-xl font-bold text-primary-600">
                        Dashboard
                    </h1>
                    <button @click="toggle()" class="p-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="mt-4 px-2">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Dashboard</span>
                </a>

                {{--
                <a href="{{ route('wilayah.index') }}" class="sidebar-link {{ request()->routeIs('wilayah.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Wilayah</span>
                </a>
                --}}

                @if(auth()->user()->isAdmin())
                <a href="{{ route('pendamping.index') }}" class="sidebar-link {{ request()->routeIs('pendamping.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Pendampingan</span>
                </a>
                
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">User Management</span>
                </a>
                @endif

                @if(!auth()->user()->isPetugas())
                <a href="{{ route('petugas.index') }}" class="sidebar-link {{ request()->routeIs('petugas.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Petugas</span>
                </a>
                @endif

                <a href="{{ route('kinerja.index') }}" class="sidebar-link {{ request()->routeIs('kinerja.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0h2a2 2 0 012 2v6a2 2 0 002 2h2a2 2 0 002-2v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Kinerja</span>
                    @if(isset($pendingApprovalCount) && $pendingApprovalCount > 0 && !auth()->user()->isPetugas())
                    <span x-show="open" class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                        {{ $pendingApprovalCount }}
                    </span>
                    @endif
                </a>

                @if(!auth()->user()->isPetugas())
                <a href="{{ route('kependudukan.index') }}" class="sidebar-link {{ request()->routeIs('kependudukan.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Kependudukan</span>
                </a>
                @endif

                <!-- Sasaran Menu -->
                <div x-data="{ sasaranOpen: {{ request()->routeIs('belum_rekam.*') || request()->routeIs('belum_akte.*') ? 'true' : 'false' }} }">
                    <button @click="sasaranOpen = !sasaranOpen" class="sidebar-link w-full flex justify-between items-center {{ request()->routeIs('belum_rekam.*') || request()->routeIs('belum_akte.*') ? 'active' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span x-show="open" class="ml-3">Sasaran</span>
                        </div>
                        <svg x-show="open" :class="sasaranOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open && sasaranOpen" class="pl-4 mt-2 space-y-1 bg-gray-50 rounded-lg p-2">
                        <a href="{{ route('sasaran.rekapitulasi') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('sasaran.rekapitulasi') ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                            Rekapitulasi
                        </a>
                        <a href="{{ route('belum_rekam.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('belum_rekam.*') ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                            Belum Rekam KTP-EL
                        </a>
                        <a href="{{ route('belum_akte.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('belum_akte.*') ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                            Belum Akte Kelahiran
                        </a>
                    </div>
                </div>

                {{--
                <a href="{{ route('pelayanan.index') }}" class="sidebar-link {{ request()->routeIs('pelayanan.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Pelayanan</span>
                </a>
                --}}

                {{--
                <a href="{{ route('sarpras.index') }}" class="sidebar-link {{ request()->routeIs('sarpras.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">Sarpras</span>
                </a>
                --}}

                @if(auth()->user()->isAdmin())
                <a href="{{ route('vpn.index') }}" class="sidebar-link {{ request()->routeIs('vpn.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                    <span x-show="open" class="ml-3">VPN</span>
                </a>
                @endif
            </nav>

            <div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-link w-full text-red-600 hover:bg-red-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span x-show="open" class="ml-3">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main :class="open ? 'ml-64' : 'ml-20'" class="flex-1 transition-all duration-300">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-9">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                            <p class="text-sm text-gray-600">@yield('subtitle', 'Selamat datang di Dashboard Pelayanan')</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-700">{{ auth()->user()->nama }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->akses ?? 'Operator' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
