<x-app-layout>
    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-3xl shadow-xl">
            <h1 class="text-2xl font-bold mb-6">Laboratorio de API BCV</h1>
            
            <div class="p-6 rounded-2xl {{ $rate ? 'bg-green-50' : 'bg-red-50' }} border">
                <p class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-2">Resultado de la API:</p>
                
                @if($rate)
                    <div class="flex items-center gap-4">
                        <span class="text-5xl font-serif text-green-600 font-bold">{{ $rate }}</span>
                        <span class="text-xl text-green-800">Bs / USD</span>
                    </div>
                    <p class="mt-4 text-xs text-green-600">✅ Conexión exitosa con ve.dolarapi.com</p>
                @else
                    <div class="text-red-500 font-bold">
                        <p class="text-3xl">API OFFLINE</p>
                        <p class="text-xs mt-2 uppercase">Revisa los logs de Laravel o tu conexión a internet.</p>
                    </div>
                @endif
            </div>

            <div class="mt-8">
                <a href="/dashboard" class="text-sm text-blue-500 hover:underline">← Volver al inventario seguro</a>
            </div>
        </div>
    </div>
</x-app-layout>