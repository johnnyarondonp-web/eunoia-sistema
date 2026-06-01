<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-800 leading-tight">
            {{ __('Balance y Rentabilidad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── CARDS DE RESUMEN ─────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="balance-kpis">
                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Inversi&oacute;n del Mes</span>
                    <span id="kpi-investment" class="text-xl sm:text-2xl font-bold text-gray-800">${{ number_format($gastoMensual, 2) }}</span>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Ventas Mensuales</span>
                    <span id="kpi-sales" class="text-xl sm:text-2xl font-bold text-gray-800">${{ number_format($ventasMensuales, 2) }}</span>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Ganancia Neta</span>
                    <span id="kpi-profit" class="text-xl sm:text-2xl font-bold {{ $gananciaMensual >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $gananciaMensual >= 0 ? '+' : '' }}${{ number_format($gananciaMensual, 2) }}
                    </span>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block mb-1">Retorno (ROI)</span>
                    <span id="kpi-roi" class="text-xl sm:text-2xl font-bold {{ $roi >= 0 ? 'text-indigo-600' : 'text-red-500' }}">
                        {{ $roi >= 0 ? '+' : '' }}{{ $roi }}%
                    </span>
                </div>
            </div>

            {{-- ── FILTROS ──────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
                <form id="balance-form" method="GET" action="{{ route('expenses.balance') }}"
                      class="grid grid-cols-2 sm:flex sm:flex-wrap items-end gap-4">

                    {{-- Mes --}}
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Mes</label>
                        <select id="filter-month" name="month"
                                class="border border-gray-200 rounded-xl text-sm px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-gray-50 w-full sm:w-32">
                            @foreach($meses as $num => $nombre)
                                <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Año --}}
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

                    {{-- Buscador --}}
                    <div class="flex flex-col gap-1 col-span-2 sm:flex-1 w-full">
                        <label class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Buscar producto</label>
                        <input id="filter-search" type="text" value="{{ $search }}"
                               placeholder="Nombre o categor&iacute;a..."
                               autocomplete="off"
                               class="border border-gray-200 rounded-xl text-sm px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-gray-50 w-full">
                    </div>

                    {{-- Ordenamiento --}}
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

                    <button type="submit" class="hidden" aria-hidden="true"></button>
                </form>
            </div>

            {{-- ── TABLA DE LOTES ───────────────────────────────────────────── --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100" id="balance-content">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest">
                        Desglose por Lotes de Compra
                    </h3>
                    <span id="lotes-count" class="text-[10px] text-gray-400 uppercase tracking-widest">
                        {{ $lotes->count() }} {{ $lotes->count() === 1 ? 'lote' : 'lotes' }}
                    </span>
                </div>

                    <div class="p-12 text-center" id="empty-state" style="{{ $lotes->isEmpty() ? '' : 'display: none;' }}">
                        <p class="text-sm text-gray-400">No hay lotes registrados para este per&iacute;odo.</p>
                    </div>

                    <div id="table-container" style="{{ $lotes->isEmpty() ? 'display: none;' : '' }}">
                        @include('expenses.partials.balance_table')
                    </div>

                    <div class="px-6 py-4 border-t border-gray-50" id="pagination-container" style="{{ $lotes->isEmpty() ? 'display: none;' : '' }}">
                        {{ $lotes->links() }}
                    </div>
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

        // ── AJAX Filter Update ────────────────────────────────────────────────
        function updateBalance() {
            var m = month.value;
            var y = year.value;
            var s = sort.value;
            var q = search.value;

            // Visual feedback: fade out content
            var content = document.getElementById('balance-content');
            var kpis = document.getElementById('balance-kpis');
            if (content) content.style.opacity = '0.5';
            if (kpis) kpis.style.opacity = '0.5';

            fetch(`{{ route('expenses.balance') }}?month=${m}&year=${y}&sort=${s}&search=${q}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response:', text.substring(0, 500));
                var data = JSON.parse(text);
                // Update KPIs
                document.getElementById('kpi-investment').textContent = data.kpis.gastoMensual;
                document.getElementById('kpi-sales').textContent = data.kpis.ventasMensuales;
                
                var profitEl = document.getElementById('kpi-profit');
                profitEl.textContent = data.kpis.gananciaMensual;
                profitEl.className = 'text-xl sm:text-2xl font-bold ' + data.kpis.ganancia_color;

                var roiEl = document.getElementById('kpi-roi');
                roiEl.textContent = data.kpis.roi;
                roiEl.className = 'text-xl sm:text-2xl font-bold ' + data.kpis.roi_color;

                // Update Table
                var container = document.getElementById('table-container');
                var emptyState = document.getElementById('empty-state');
                var pagination = document.getElementById('pagination-container');

                if (data.lotes_count > 0) {
                    if (container) {
                        container.innerHTML = data.table_html;
                        container.style.display = 'block';
                    }
                    if (emptyState) emptyState.style.display = 'none';
                    if (pagination) {
                        pagination.innerHTML = data.pagination_html;
                        pagination.style.display = 'block';
                    }
                } else {
                    if (container) container.style.display = 'none';
                    if (emptyState) emptyState.style.display = 'block';
                    if (pagination) pagination.style.display = 'none';
                }

                if (countEl) {
                    countEl.textContent = data.count_text;
                }

                // Update URL without reloading
                window.history.pushState({}, '', `?month=${m}&year=${y}&sort=${s}&search=${q}`);
                
                // Restore opacity
                if (content) content.style.opacity = '1';
                if (kpis) kpis.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error updating balance:', error);
                if (content) content.style.opacity = '1';
                if (kpis) kpis.style.opacity = '1';
            });
        }

        // Trigger AJAX update on change
        [month, year, sort].forEach(function (el) {
            el.addEventListener('change', updateBalance);
        });

        // Search: debounce to avoid too many requests
        var searchTimeout;
        search.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(updateBalance, 300);
        });

        // We keep form.submit() only for the "Export CSV" button (which uses formaction)
    })();
    </script>
</x-app-layout>