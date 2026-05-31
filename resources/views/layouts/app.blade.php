<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

<title>{{ config('app.name') }} | Sistema</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
    </head>
<body class="font-sans antialiased bg-white text-eunoia-text pb-20 sm:pb-0">
    <div class="min-h-screen">
        @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-3 sm:py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

        <!-- Mobile Bottom Navigation -->
        <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-2 py-2 z-50 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
            <div class="flex justify-around items-center max-w-md mx-auto">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('dashboard') ? 'text-eunoia-coral' : 'text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span class="text-[10px] font-bold uppercase tracking-tighter">Inicio</span>
                </a>
                <a href="{{ route('sales.create') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('sales.create') ? 'text-eunoia-coral' : 'text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-[10px] font-bold uppercase tracking-tighter">Venta</span>
                </a>
                <a href="{{ route('sales.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('sales.index') ? 'text-eunoia-coral' : 'text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="text-[10px] font-bold uppercase tracking-tighter">Registro</span>
                </a>
                <a href="{{ route('expenses.balance') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('expenses.*') ? 'text-eunoia-coral' : 'text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="text-[10px] font-bold uppercase tracking-tighter">Balance</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('profile.*') ? 'text-eunoia-coral' : 'text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-[10px] font-bold uppercase tracking-tighter">Perfil</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
