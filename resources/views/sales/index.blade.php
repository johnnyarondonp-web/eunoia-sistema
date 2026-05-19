<x-app-layout>
    <x-slot name="header">
        <h2 id="dynamic-title" class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-8">
                
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
                                <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($sales as $sale)
                            <tr class="hover:bg-gray-50 transition-colors sale-row {{ $sale->cancelled_at ? 'opacity-50 line-through' : '' }}" 
                                data-products="{{ $sale->items->pluck('product.name')->join(' ') }}"
                                data-category="{{ $sale->items->pluck('product.category')->join(' ') }}">
                                <td class="py-4 text-sm text-gray-600 font-mono">
                                    {{ $sale->created_at->format('d/m/Y') }}
                                    <span class="block text-[10px] text-gray-400 uppercase">{{ $sale->created_at->format('h:i A') }}</span>
                                    @if($sale->cancelled_at)
                                        <span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-800 text-[8px] font-bold uppercase rounded no-underline">Cancelada</span>
                                        @if($sale->cancel_reason)
                                            <span class="block text-[9px] text-gray-400 italic mt-0.5">{{ $sale->cancel_reason }}</span>
                                        @endif
                                    @endif
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
                                <td class="py-4 text-right">
                                    @if(is_null($sale->cancelled_at))
                                        <button type="button" 
                                                onclick="openCancelModal('{{ route('sales.cancel', $sale) }}')"
                                                class="text-red-500 hover:text-red-700 text-[10px] uppercase font-bold tracking-widest no-underline">
                                          Cancelar
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-[10px] uppercase font-bold no-underline">N/A</span>
                                    @endif
                                </td>
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
        let dateFilterTimeout;
        document.querySelectorAll('.auto-filter').forEach(input => {
            input.addEventListener('change', () => {
                clearTimeout(dateFilterTimeout);
                dateFilterTimeout = setTimeout(() => {
                    const from = document.getElementById('filter-from').value;
                    const to = document.getElementById('filter-to').value;
                    if (from && to) {
                        window.location.href = `{{ route('sales.index') }}?from=${from}&to=${to}`;
                    }
                }, 300);
            });
        });

        function openCancelModal(url) {
            document.getElementById('cancelForm').action = url;
            document.getElementById('cancel_reason').value = '';
            document.getElementById('cancel-reason-error').classList.add('hidden');
            const modal = document.getElementById('cancelModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeCancelModal() {
            const modal = document.getElementById('cancelModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) closeCancelModal();
        });
        // Validar que el motivo no esté vacío antes de enviar
        document.getElementById('cancelForm').addEventListener('submit', function(e) {
            const reason = document.getElementById('cancel_reason').value.trim();
            if (reason.length < 5) {
                e.preventDefault();
                document.getElementById('cancel-reason-error').classList.remove('hidden');
            }
        });
    </script>

    <div id="cancelModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/20 backdrop-blur-[2px]">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8 max-w-sm w-full mx-4">
            <div class="bg-red-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <h3 class="text-lg font-bold text-gray-800 uppercase tracking-tighter text-center">¿Seguro que deseas cancelar esta venta?</h3>
            <p class="text-xs text-gray-400 uppercase tracking-widest mt-1 mb-4 text-center">Se restaurará el stock automáticamente</p>
            <div class="mb-5">
                <label class="text-[9px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Motivo de cancelación</label>
                <textarea id="cancel_reason" name="cancel_reason" form="cancelForm" rows="2" required
                    placeholder="Ej: cliente arrepentido, error al registrar..."
                    class="border border-gray-200 rounded-xl text-sm px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-red-200 resize-none"></textarea>
                <p id="cancel-reason-error" class="hidden text-[9px] text-red-500 font-bold mt-1">El motivo debe tener al menos 5 caracteres.</p>
            </div>
            <div class="flex gap-3 justify-end">
                <button onclick="closeCancelModal()" class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-gray-700">
                    No cancelar
                </button>
                <form id="cancelForm" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 text-xs font-bold uppercase tracking-widest bg-red-500 text-white rounded-xl hover:bg-red-600">
                        Confirmar cancelación
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>