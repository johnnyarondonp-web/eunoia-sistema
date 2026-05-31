<nav x-data="{ open: false }" class="bg-white border-b border-gray-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 sm:h-20"> <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="transition-opacity hover:opacity-80">
                        <x-application-logo class="block h-12 w-auto" />
                    </a>
                </div>
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-[10px] uppercase tracking-[0.2em] font-bold">
        {{ __('Dashboard') }}
    </x-nav-link>

    <x-nav-link :href="route('sales.create')" :active="request()->routeIs('sales.create')" class="text-[10px] uppercase tracking-[0.2em] font-bold">
        {{ __('Ventas') }}
    </x-nav-link>
<x-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.index')" class="text-[10px] uppercase tracking-[0.2em] font-bold">
    {{ __('Registro de Ventas') }}
</x-nav-link>
<x-nav-link :href="route('expenses.balance')" :active="request()->routeIs('expenses.*')" class="text-[10px] uppercase tracking-[0.2em] font-bold">
    {{ __('Balance') }}
</x-nav-link>
</div>
                
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-gray-100 text-[11px] uppercase tracking-widest font-black rounded-full text-gray-400 bg-white hover:text-eunoia-coral hover:border-eunoia-coral focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-[10px] text-gray-400 uppercase tracking-widest border-b border-gray-50">Configuración</div>
                        
                        <x-dropdown-link :href="route('profile.edit')" class="text-xs">
                            {{ __('Mi Perfil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    class="text-xs text-red-400 hover:text-red-600"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-eunoia-coral hover:bg-gray-50 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-gray-50">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-[10px] uppercase tracking-widest font-bold">
                {{ __('Inventario') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('sales.create')" :active="request()->routeIs('sales.create')" class="text-[10px] uppercase tracking-widest font-bold">
                {{ __('Ventas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.index')" class="text-[10px] uppercase tracking-widest font-bold">
                {{ __('Registro de Ventas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('expenses.balance')" :active="request()->routeIs('expenses.*')" class="text-[10px] uppercase tracking-widest font-bold">
                {{ __('Balance') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-100">
            <div class="px-4">
                <div class="font-bold text-xs text-eunoia-text uppercase tracking-widest">{{ Auth::user()->name }}</div>
                <div class="font-medium text-[10px] text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-xs">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            class="text-xs text-red-400"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>