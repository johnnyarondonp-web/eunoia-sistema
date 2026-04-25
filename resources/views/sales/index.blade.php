<x-app-layout>
    <x-slot name="header">
        <h2 id="dynamic-title" class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                
                <div class="mb-10 grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div class="flex space-x-2">
                        <div class="flex-1">
                            <label class="text-[9px] uppercase tracking-widest text-gray-400 block mb-1">Desde</label>
                            <input type="date" id="filter-from" value="{{ $from }}"
                                class="w-full border-gray-200 rounded-md text-xs focus:ring-indigo-500 auto-filter">
                        </div>
                        <div class="flex-1">
                            <label class="text-[9px] uppercase tracking-widest text-gray-400 block mb-1">Hasta</label>
                            <input type="date" id="filter-to" value="{{ $to }}"
                                class="w-full border-gray-200 rounded-md text-xs focus:ring-indigo-500 auto-filter">
                        </div>
                    </div>

                    <div class="flex-1 relative">
                        <label class="text-[9px] uppercase tracking-widest text-gray-400 block mb-1">Producto o Categoría</label>
                        <input type="text" id="search-input" placeholder="Ej: Maquillaje, Labial..." 
                            class="w-full border-gray-200 rounded-md text-xs focus:ring-indigo-500">
                    </div>

                    <div>
                        <button onclick="window.location.href='{{ route('sales.index') }}'" 
                            class="bg-gray-100 text-gray-600 px-6 py-2 rounded-md text-[10px] uppercase tracking-widest font-bold hover:bg-gray-200 transition-colors">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full" id="sales-table">
                        <thead>
                            <tr class="text-left border-b border-gray-100">
                                <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400">Fecha y Hora</th>
                                <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400">Productos</th>
                                <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400 text-right">Tasa BCV</th>
                                <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400 text-right">Total ($)</th>
                                <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400 text-right">Total (Bs.)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($sales as $sale)
                            <tr class="hover:bg-gray-50 transition-colors sale-row" 
                                data-products="{{ $sale->items->pluck('product.name')->join(' ') }}"
                                data-category="{{ $sale->items->pluck('product.category')->join(' ') }}">
                                <td class="py-4 text-sm text-gray-600 font-mono">
                                    {{ $sale->created_at->format('d/m/Y') }}
                                    <span class="block text-[10px] text-gray-400 uppercase">{{ $sale->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="py-4">
                                    @foreach($sale->items as $item)
                                        <div class="flex items-center space-x-3 mb-2 product-item">
                                            <img src="{{ asset('storage/' . $item->product->image_path) }}" 
                                                 class="w-8 h-8 rounded-full object-cover border shadow-sm">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-gray-800">{{ $item->quantity }}x {{ $item->product->name }}</span>
                                                <span class="text-[9px] text-gray-400 uppercase tracking-tighter">{{ $item->product->category }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="py-4 text-right text-xs text-gray-400 font-mono">{{ number_format($sale->bcv_rate, 2, ',', '.') }}</td>
                                <td class="py-4 text-right font-bold text-gray-800">${{ number_format($sale->total_usd, 2, ',', '.') }}</td>
                                <td class="py-4 text-right font-bold text-indigo-600">{{ number_format($sale->total_bs, 2, ',', '.') }} Bs.</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filtrado automático por texto (JS)
        document.getElementById('search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.sale-row');

            rows.forEach(row => {
                const text = (row.getAttribute('data-products') + ' ' + row.getAttribute('data-category')).toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Redirección automática al cambiar fechas
        document.querySelectorAll('.auto-filter').forEach(input => {
            input.addEventListener('change', () => {
                const from = document.getElementById('filter-from').value;
                const to = document.getElementById('filter-to').value;
                window.location.href = `{{ route('sales.index') }}?from=${from}&to=${to}`;
            });
        });
    </script>
</x-app-layout>