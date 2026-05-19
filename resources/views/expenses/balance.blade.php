<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-800 leading-tight">
            {{ __('Balance y Rentabilidad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── CARDS DE RESUMEN ─────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Inversi&oacute;n del Mes</span>
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">${{ number_format($gastoMensual, 2) }}</span>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Ventas Mensuales</span>
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">${{ number_format($ventasMensuales, 2) }}</span>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Ganancia Neta</span>
                    <span class="text-xl sm:text-2xl font-bold {{ $gananciaMensual >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $gananciaMensual >= 0 ? '+' : '' }}${{ number_format($gananciaMensual, 2) }}
                    </span>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Retorno (ROI)</span>
                    <span class="text-xl sm:text-2xl font-bold {{ $roi >= 0 ? 'text-indigo-600' : 'text-red-500' }}">
                        {{ $roi >= 0 ? '+' : '' }}{{ $roi }}%
                    </span>
                </div>
            </div>

            {{-- ── FILTROS ──────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
                <form id="balance-form" method="GET" action="{{ route('expenses.balance') }}"
                      class="grid grid-cols-2 sm:flex sm:flex-wrap items-end gap-4">

                    {{-- Mes: recarga página (datos vienen del servidor) --}}
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Mes</label>
                        <select id="filter-month" name="month"
                                class="border border-gray-200 rounded-xl text-sm px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-gray-50 w-full sm:w-32">
                            @foreach($meses as $num => $nombre)
                                <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Año: recarga página (datos vienen del servidor) --}}
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label class="text-[10px] uppercase tracking-widest font-bold text-gray-400">A&ntilde;o</label>
                        <select id="filter-year" name="year"
                                style="padding-right: 2rem;"
                                class="border border-gray-200 rounded-xl text-sm px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-gray-50 w-full sm:w-28">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buscador: filtrado instantáneo client-side --}}
                    <div class="flex flex-col gap-1 col-span-2 sm:flex-1 w-full">
                        <label class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Buscar producto</label>
                        <input id="filter-search" type="text" value="{{ $search }}"
                               placeholder="Nombre o categor&iacute;a..."
                               autocomplete="off"
                               class="border border-gray-200 rounded-xl text-sm px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-gray-50 w-full">
                    </div>

                    {{-- Ordenamiento: instantáneo client-side --}}
                    <div class="flex flex-col gap-1 col-span-2 sm:w-auto w-full">
                        <label class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Ordenar por</label>
                        <select id="filter-sort"
                                class="border border-gray-200 rounded-xl text-sm px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-gray-50 w-full sm:w-52">
                            <option value=""     {{ $sort === ''      ? 'selected' : '' }}>Seleccionar</option>
                            <option value="best" {{ $sort === 'best'  ? 'selected' : '' }}>&#8593; Mejor desempe&ntilde;o</option>
                            <option value="worst"{{ $sort === 'worst' ? 'selected' : '' }}>&#8595; Peor desempe&ntilde;o</option>
                        </select>
                    </div>

                    {{-- Limpiar --}}
                    <a href="{{ route('expenses.balance') }}"
                       class="px-5 py-2 border border-gray-200 text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-gray-700 hover:border-gray-300 rounded-xl transition-colors whitespace-nowrap text-center col-span-1 w-full sm:w-auto">
                        Limpiar
                    </a>

                    {{-- Exportar CSV --}}
                    <button type="submit" formaction="{{ route('expenses.export') }}" formmethod="GET"
                       class="px-5 py-2 border border-emerald-200 bg-emerald-50 text-xs font-bold uppercase tracking-widest text-emerald-600 hover:text-emerald-700 hover:bg-emerald-100 rounded-xl transition-colors whitespace-nowrap text-center col-span-1 w-full sm:w-auto sm:ml-auto">
                        Exportar CSV
                    </button>

                    {{-- Submit oculto (fallback sin JS) --}}
                    <button type="submit" class="hidden" aria-hidden="true"></button>

                </form>
            </div>

            {{-- ── TABLA DE LOTES ───────────────────────────────────────────── --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">

                <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest">
                        Desglose por Lotes de Compra
                    </h3>
                    <span id="lotes-count" class="text-[10px] text-gray-400 uppercase tracking-widest">
                        {{ $lotes->count() }} {{ $lotes->count() === 1 ? 'lote' : 'lotes' }}
                    </span>
                </div>

                @if($lotes->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-sm text-gray-400">No hay lotes registrados para este per&iacute;odo.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="lotes-table">
                            <thead>
                                <tr class="bg-gray-50/70">
                                    <th class="p-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Producto / Fecha</th>
                                    <th class="p-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold text-center">Inversi&oacute;n</th>
                                    <th class="p-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold text-center">Stock</th>
                                    <th class="p-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold text-center">Ventas</th>
                                    <th class="p-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold text-center">Progreso</th>
                                    <th class="p-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold text-right">Ganancia Generada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" id="lotes-tbody">
                                @foreach($lotes as $lote)
                                @php
                                    $gananciaLote = max(0, $lote->total_recaudado - $lote->cost_usd);
                                    $recuperado   = $lote->cost_usd > 0
                                        ? min(100, round(($lote->total_recaudado / $lote->cost_usd) * 100))
                                        : 0;
                                    $enPositivo   = $lote->total_recaudado >= $lote->cost_usd;
                                @endphp
                                {{-- data-search: texto para búsqueda | data-ganancia: para ordenar --}}
                                <tr class="hover:bg-gray-50/60 transition-colors"
                                    data-search="{{ strtolower(($lote->product->name ?? '') . ' ' . ($lote->product->category ?? '')) }}"
                                    data-ganancia="{{ $gananciaLote }}">

                                    <td class="p-4">
                                        <div class="flex items-center space-x-3">
                                            <img src="{{ isset($lote->product) && $lote->product->image_path
                                                            ? asset('storage/' . $lote->product->image_path)
                                                            : asset('img/default.png') }}"
                                                 class="w-10 h-10 rounded-lg object-cover border border-gray-100"
                                                 alt="{{ $lote->product->name ?? 'Producto' }}">
                                            <div>
                                                <span class="text-sm font-bold text-gray-800">
                                                    {{ $lote->product->name ?? 'Producto Eliminado' }}
                                                </span>
                                                <span class="text-[10px] text-gray-400 uppercase tracking-tighter block">
                                                    Lote #{{ $lote->id }} &middot; {{ $lote->created_at?->format('d/m/Y') ?? 'Sin fecha' }}
                                                </span>
                                                @if(isset($lote->product->category))
                                                    <span class="text-[10px] text-indigo-400 uppercase tracking-tighter">
                                                        {{ $lote->product->category }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="p-4 text-center">
                                        <span class="text-sm font-medium text-gray-600">
                                            ${{ number_format($lote->cost_usd, 2) }}
                                        </span>
                                    </td>

                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $lote->remaining_quantity > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $lote->remaining_quantity }} de {{ $lote->quantity }}
                                        </span>
                                    </td>

                                    <td class="p-4 text-center">
                                        <span class="text-sm font-bold text-gray-700">
                                            ${{ number_format($lote->total_recaudado, 2) }}
                                        </span>
                                    </td>

                                    <td class="p-4">
                                        <div class="w-full bg-gray-100 rounded-full h-1.5 min-w-[80px]">
                                            <div class="h-1.5 rounded-full transition-all
                                                {{ $enPositivo ? 'bg-emerald-400' : 'bg-amber-400' }}"
                                                 style="width: {{ $recuperado }}%">
                                            </div>
                                        </div>
                                        <span class="text-[10px] text-gray-400 mt-0.5 block text-center">
                                            {{ $recuperado }}% recuperado
                                        </span>
                                    </td>

                                    <td class="p-4 text-right">
                                        @if($enPositivo)
                                            <span class="text-sm font-bold text-emerald-600">
                                                +${{ number_format($gananciaLote, 2) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-amber-500 font-medium">
                                                Faltan ${{ number_format($lote->cost_usd - $lote->total_recaudado, 2) }}
                                            </span>
                                        @endif
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="bg-gray-50/70 border-t border-gray-200">
                                    <td class="p-4 text-xs font-bold text-gray-500 uppercase tracking-widest">
                                        Totales del per&iacute;odo
                                    </td>
                                    <td class="p-4 text-center text-sm font-bold text-gray-700">
                                        ${{ number_format($gastoMensual, 2) }}
                                    </td>
                                    <td class="p-4"></td>
                                    <td class="p-4 text-center text-sm font-bold text-gray-700">
                                        ${{ number_format($ventasMensuales, 2) }}
                                    </td>
                                    <td class="p-4"></td>
                                    <td class="p-4 text-right text-sm font-bold {{ $gananciaMensual >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                        {{ $gananciaMensual >= 0 ? '+' : '' }}${{ number_format($gananciaMensual, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        {{-- Mensaje cuando no hay resultados tras búsqueda --}}
                        <div id="no-results" class="p-12 text-center hidden">
                            <p class="text-sm text-gray-400">Ningún lote coincide con tu búsqueda.</p>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 border-t border-gray-50">
                        {{ $lotes->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ── JAVASCRIPT ────────────────────────────────────────────────────────── --}}
    <script>
    (function () {
        var form   = document.getElementById('balance-form');
        var month  = document.getElementById('filter-month');
        var year   = document.getElementById('filter-year');
        var sort   = document.getElementById('filter-sort');
        var search = document.getElementById('filter-search');
        var tbody  = document.getElementById('lotes-tbody');
        var noResults = document.getElementById('no-results');
        var countEl   = document.getElementById('lotes-count');

        if (!tbody) return; // tabla vacía, nada que hacer

        // ── Mes y Año: siguen haciendo submit (necesitan datos del servidor) ──
        [month, year].forEach(function (el) {
            el.addEventListener('change', function () { form.submit(); });
        });

        // ── Buscador + Ordenamiento: client-side instantáneo ─────────────────

        function applyFilters() {
            var query   = search.value.toLowerCase().trim();
            var sortVal = sort.value;

            // 1. Obtener todas las filas y filtrar por búsqueda
            var rows = Array.from(tbody.querySelectorAll('tr'));

            rows.forEach(function (row) {
                var text = (row.dataset.search || '').toLowerCase();
                var match = query === '' || text.indexOf(query) !== -1;
                row.style.display = match ? '' : 'none';
            });

            // 2. Ordenar las filas visibles según ganancia
            if (sortVal === 'best' || sortVal === 'worst') {
                var visibles = rows.filter(function (r) { return r.style.display !== 'none'; });

                visibles.sort(function (a, b) {
                    var ga = parseFloat(a.dataset.ganancia) || 0;
                    var gb = parseFloat(b.dataset.ganancia) || 0;
                    return sortVal === 'best' ? gb - ga : ga - gb;
                });

                // Re-insertar en el DOM en el nuevo orden
                visibles.forEach(function (row) { tbody.appendChild(row); });
            }

            // 3. Actualizar contador y mensaje vacío
            var visibleCount = rows.filter(function (r) { return r.style.display !== 'none'; }).length;

            if (countEl) {
                countEl.textContent = visibleCount + (visibleCount === 1 ? ' lote' : ' lotes');
            }
            if (noResults) {
                noResults.classList.toggle('hidden', visibleCount > 0);
            }
        }

        // Buscador: instantáneo al escribir
        search.addEventListener('input', applyFilters);

        // Ordenamiento: instantáneo al cambiar
        sort.addEventListener('change', applyFilters);

        // Aplicar filtros iniciales si vienen valores del servidor
        applyFilters();
    })();
    </script>

</x-app-layout>