<x-app-layout>
    {{-- Google Fonts: Cormorant Garamond for premium heading --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&display=swap" rel="stylesheet">

    <style>
        .font-cormorant { font-family: 'Cormorant Garamond', serif; }

        .filter-btn {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .filter-btn.active {
            background-color: #E88C8C;
            color: white;
            border-color: #E88C8C;
        }
        .filter-btn:not(.active):hover {
            background-color: #f5f5f5;
        }

        .product-card {
            transition: all 0.5s ease;
        }

        /* Sub-sort arrow toggle button */
        .sort-dir-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #E88C8C;
            border: 1px solid #E88C8C;
            border-radius: 999px;
            padding: 4px 14px;
            cursor: pointer;
            background: white;
            transition: all 0.2s ease;
        }
        .sort-dir-btn:hover {
            background: #fdf0f0;
        }
        .sort-dir-btn svg {
            width: 11px;
            height: 11px;
            transition: transform 0.25s ease;
        }
        .sort-dir-btn.flipped svg {
            transform: rotate(180deg);
        }

        /* ── VENTAS ROW ── */
        #ventasRow {
            margin-top: 10px;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        #ventasRow:not(.hidden) {
            display: flex;
        }

        .ventas-chip {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 5px 14px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.18s ease;
            white-space: nowrap;
        }
        .ventas-chip:hover {
            background: #fdf0f0;
            border-color: #E88C8C;
            color: #E88C8C;
        }
        .ventas-chip.active {
            background: #E88C8C;
            border-color: #E88C8C;
            color: white;
        }

        /* Sort direction inside ventas panel */
        .ventas-dir-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #E88C8C;
            border: 1px solid #E88C8C;
            border-radius: 999px;
            padding: 5px 14px;
            cursor: pointer;
            background: white;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .ventas-dir-btn:hover { background: #fdf0f0; }
        .ventas-dir-btn svg {
            width: 11px; height: 11px;
            transition: transform 0.25s ease;
        }
        .ventas-dir-btn.flipped svg { transform: rotate(180deg); }

        /* Divider dot */
        .panel-dot {
            width: 4px; height: 4px;
            border-radius: 50%;
            background: #e5c9c9;
            flex-shrink: 0;
        }
        .ventas-select {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #E88C8C;
            border: 1px solid #E88C8C;
            border-radius: 999px;
            padding: 5px 28px 5px 14px;
            background: white;
            cursor: pointer;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23E88C8C' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            transition: all 0.18s ease;
        }
        .ventas-select:hover { background-color: #fdf0f0; }

        /* Label inside panel */
        .panel-label {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #c9a3a3;
        }
    </style>

    <div class="py-12 bg-[#F9F9F9] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1 flex-wrap">
                        <h2 class="font-cormorant text-4xl font-light tracking-wide text-eunoia-text" style="letter-spacing: 0.04em;">
                            Panel de Inventario
                        </h2>
                        <div class="bg-white border border-gray-200 px-4 py-1.5 rounded-full shadow-sm flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse flex-shrink-0"></span>
                            <span class="text-xs font-black text-gray-600 uppercase tracking-widest whitespace-nowrap">
                                BCV: {{ number_format($bcvRate, 2, ',', '.') }} Bs.
                            </span>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-[0.3em]">Gestión exclusiva • Eunoia Cosmetics</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input
                            type="text"
                            id="searchInput"
                            placeholder="Buscar producto o categoría..."
                            class="border-none bg-white rounded-full px-6 py-2 text-xs shadow-sm focus:ring-1 focus:ring-eunoia-coral w-72"
                            autocomplete="off"
                        >
                    </div>
                    <a href="{{ route('products.create') }}"
                       class="bg-eunoia-coral text-white text-[10px] font-bold py-3 px-8 rounded-full shadow-lg transition-all duration-200 uppercase tracking-widest hover:bg-[#d97777] hover:shadow-md active:scale-95">
                        + Agregar
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-8 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-xl shadow-sm">
                    <p class="text-green-700 text-xs font-bold uppercase tracking-widest">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Filter Buttons --}}
            <div class="mb-4" id="filterArea">
                <div class="flex flex-wrap gap-2" id="filterButtons">
                    <button onclick="setFilter('all', event)" id="btn-all"
                            class="filter-btn text-[9px] font-black uppercase tracking-widest px-5 py-2 rounded-full border border-gray-200 bg-white shadow-sm">
                        Todos
                    </button>
                    <button onclick="setFilter('stock', event)" id="btn-stock"
                            class="filter-btn text-[9px] font-black uppercase tracking-widest px-5 py-2 rounded-full border border-gray-200 bg-white shadow-sm">
                        Stock
                    </button>

                    {{-- ★ VENTAS: botón unificado (reemplaza Más Vendido + Menos Vendido) --}}
                    <button onclick="setFilter('ventas', event)" id="btn-ventas"
                            class="filter-btn text-[9px] font-black uppercase tracking-widest px-5 py-2 rounded-full border border-gray-200 bg-white shadow-sm">
                        Ventas
                    </button>

                    <button onclick="setFilter('price', event)" id="btn-price"
                            class="filter-btn text-[9px] font-black uppercase tracking-widest px-5 py-2 rounded-full border border-gray-200 bg-white shadow-sm">
                        Por Precio
                    </button>
                    <button onclick="setFilter('paused', event)" id="btn-paused"
                            class="filter-btn text-[9px] font-black uppercase tracking-widest px-5 py-2 rounded-full border border-gray-200 bg-white shadow-sm">
                        Pausados
                    </button>
                </div>

                {{-- Sub-sort row: only shown for 'all', 'price', 'stock' --}}
                <div id="subSortRow" class="mt-3 hidden">
                    <button class="sort-dir-btn" id="subSortBtn" onclick="toggleSortDir()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12l7-7 7 7"/>
                        </svg>
                        <span id="subSortLabel">Más nuevo primero</span>
                    </button>
                </div>

                {{-- ★ VENTAS ROW (sin contenedor, igual que subSortRow) --}}
                <div id="ventasRow" class="hidden">

                    {{-- Dirección --}}
                    <button class="ventas-dir-btn" id="ventasDirBtn" onclick="toggleVentasDir()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12l7-7 7 7"/>
                        </svg>
                        <span id="ventasDirLabel">Más vendido primero</span>
                    </button>

                    <button class="ventas-chip active" id="chip-lifetime" onclick="setVentasPeriod('lifetime')">
                        Historial total
                    </button>
                    <button class="ventas-chip" id="chip-weekly" onclick="setVentasPeriod('weekly')">
                        Esta semana
                    </button>
                    <button class="ventas-chip" id="chip-monthly" onclick="setVentasPeriod('monthly')">
                        Mensual
                    </button>

                    {{-- Month selector --}}
                    <div id="monthSelector" class="hidden flex items-center gap-2">
                        <select id="ventasMonth" class="ventas-select" onchange="applyVentasFilter()">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                        <select id="ventasYear" class="ventas-select" onchange="applyVentasFilter()">
                            {{-- Populated by JS --}}
                        </select>
                    </div>

                </div>
            </div>

            {{-- TOP SELLERS SECTION (shown on default/home only) --}}
            <div id="topSection">
                <div class="flex items-center gap-3 mb-5">
                    <h3 class="font-cormorant text-2xl font-light text-eunoia-text tracking-wide">Lo Más Vendido</h3>
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-[9px] font-black text-eunoia-coral uppercase tracking-widest">Top 5</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-10" id="topGrid">
                    @php
                        $topProducts = $products->sortByDesc(fn($p) => $p->totalSold ?? 0)->take(5);
                    @endphp
                    @forelse($topProducts as $product)
                        <div class="product-card bg-white rounded-[2rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-50 group relative"
                             data-name="{{ strtolower($product->name) }}"
                             data-category="{{ strtolower($product->category) }}"
                             data-status="{{ $product->status }}"
                             data-sold="{{ $product->totalSold ?? 0 }}">

                            <div class="absolute top-4 left-4 z-10 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="{{ route('products.edit', $product) }}"
                                   class="bg-white/90 backdrop-blur-md p-2 rounded-full shadow-sm text-gray-600 hover:text-eunoia-coral transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>
                            </div>

                            {{-- TOP badge --}}
                            <div class="absolute top-3 right-3 z-10 bg-eunoia-coral text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest shadow">
                                Top
                            </div>

                            <div class="h-48 overflow-hidden relative">
                                @if($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}"
                                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                                @else
                                    <div class="w-full h-full bg-gray-50 flex items-center justify-center">
                                        <span class="text-[10px] text-gray-300 font-bold uppercase tracking-tighter">Sin imagen</span>
                                    </div>
                                @endif
                                @if($product->stock <= 0)
                                    <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] flex items-center justify-center">
                                        <span class="bg-red-500 text-white text-[10px] font-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Agotado</span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-4 text-center">
                                <span class="text-[9px] font-black text-eunoia-coral uppercase tracking-[0.2em] mb-1 block">{{ $product->category }}</span>
                                <h3 class="text-xs font-bold text-eunoia-text truncate mb-1">{{ $product->name }}</h3>
                                <p class="text-base font-serif text-eunoia-text opacity-90">${{ number_format($product->price, 2) }}</p>
                                <p class="text-[9px] font-bold text-gray-400">≈ {{ number_format($product->price * $bcvRate, 2, ',', '.') }} Bs.</p>
                                <div class="mt-2 pt-2 border-t border-gray-50">
                                    <span class="text-[9px] font-bold text-eunoia-coral uppercase tracking-widest">
                                        {{ $product->totalSold ?? 0 }} vendidos
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-10 text-center">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Sin datos de ventas aún</p>
                        </div>
                    @endforelse
                </div>

                {{-- Divider before the rest --}}
                <div class="flex items-center gap-3 mb-5">
                    <h3 class="font-cormorant text-2xl font-light text-eunoia-text tracking-wide">Todos los Productos</h3>
                    <div class="flex-1 h-px bg-gray-100"></div>
                </div>
            </div>

            {{-- MAIN PRODUCT GRID --}}
            {{--
                data-sold-YYYY-MM  → units sold in that specific month
                data-sold-week     → units sold this ISO week
                These attributes must be populated by the controller / blade.
                Example:  data-sold-2026-04="12"  data-sold-week="3"
                If not present the JS falls back to 0.
            --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="productGrid">
                @forelse($products as $product)
                    <div class="product-card bg-white rounded-[2rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-50 group relative"
                         data-name="{{ strtolower($product->name) }}"
                         data-category="{{ strtolower($product->category) }}"
                         data-status="{{ $product->status }}"
                         data-sold="{{ $product->totalSold ?? 0 }}"
                         data-price="{{ $product->price }}"
                         data-stock="{{ $product->stock }}"
                         data-created="{{ $product->created_at->timestamp }}"
                         {{-- Monthly sales: format  data-sold-YYYY-MM="N"  populated per product --}}
                         @foreach($product->monthlySales ?? [] as $ym => $qty)
                             data-sold-{{ $ym }}="{{ $qty }}"
                         @endforeach
                         data-sold-week="{{ $product->weeklySold ?? 0 }}"
                         >

                        <div class="absolute top-4 left-4 z-10 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <a href="{{ route('products.edit', $product) }}"
                               class="bg-white/90 backdrop-blur-md p-2 rounded-full shadow-sm text-gray-600 hover:text-eunoia-coral transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </a>
                        </div>

                        @if($product->status == 0)
                            <div class="absolute top-3 right-3 z-10 bg-gray-400 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest shadow">
                                Pausado
                            </div>
                        @endif

                        <div class="h-64 overflow-hidden relative">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                     class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 {{ $product->status == 0 ? 'opacity-50 grayscale' : '' }}">
                            @else
                                <div class="w-full h-full bg-gray-50 flex items-center justify-center">
                                    <span class="text-[10px] text-gray-300 font-bold uppercase tracking-tighter">Sin imagen</span>
                                </div>
                            @endif

                            @if($product->stock <= 0)
                                <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] flex items-center justify-center">
                                    <span class="bg-red-500 text-white text-[10px] font-black px-4 py-1 rounded-full uppercase tracking-widest shadow-lg">Agotado</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-6 text-center">
                            <span class="text-[9px] font-black text-eunoia-coral uppercase tracking-[0.2em] mb-1 block">{{ $product->category }}</span>
                            <h3 class="text-sm font-bold text-eunoia-text truncate mb-1">{{ $product->name }}</h3>
                            <div class="mb-3">
                                <p class="text-lg font-serif text-eunoia-text opacity-90 leading-tight">${{ number_format($product->price, 2) }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                    ≈ {{ number_format($product->price * $bcvRate, 2, ',', '.') }} Bs.
                                </p>
                            </div>
                            <div class="pt-2 border-t border-gray-50">
                                @if($product->stock <= 0)
                                    <span class="text-[9px] font-bold text-red-400 uppercase tracking-widest">Sin existencias</span>
                                @elseif($product->stock <= 5)
                                    <span class="text-[9px] font-bold text-amber-500 uppercase tracking-widest animate-pulse">
                                        Solo {{ $product->stock }} disponibles!
                                    </span>
                                @else
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">
                                        Stock: {{ $product->stock }} uds.
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-white rounded-[2rem] border border-dashed border-gray-200">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">No hay productos en el inventario</p>
                    </div>
                @endforelse
            </div>

            {{-- No results message --}}
            <div id="noResults" class="hidden col-span-full py-20 text-center bg-white rounded-[2rem] border border-dashed border-gray-200 mt-4">
                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">No se encontraron productos</p>
            </div>

        </div>
    </div>

    <script>
        // ─── STATE ───────────────────────────────────────────────────────────────
        let currentFilter  = null;   // null = default home view
        let sortAsc        = false;  // used by 'all', 'price', 'stock'

        // Ventas-specific state
        let ventasAsc      = false;  // false = más vendido primero
        let ventasPeriod   = 'lifetime'; // 'lifetime' | 'weekly' | 'monthly'

        // ─── DATE HELPERS ────────────────────────────────────────────────────────
        const NOW         = new Date();
        const CURRENT_YEAR  = NOW.getFullYear();
        const CURRENT_MONTH = NOW.getMonth() + 1; // 1-based
        const LAUNCH_YEAR   = 2026;

        // Sub-sort label config per filter
        const sortLabels = {
            all:   { asc: 'Mas antiguo primero',  desc: 'Mas nuevo primero'  },
            price: { asc: 'Menor precio primero',  desc: 'Mayor precio primero'  },
            stock: { asc: 'Mayor stock primero',   desc: 'Menor stock primero' },
        };

        // ─── ELEMENTS ────────────────────────────────────────────────────────────
        const searchInput   = document.getElementById('searchInput');
        const topSection    = document.getElementById('topSection');
        const subSortRow    = document.getElementById('subSortRow');
        const subSortBtn    = document.getElementById('subSortBtn');
        const subSortLabel  = document.getElementById('subSortLabel');
        const noResults     = document.getElementById('noResults');
        const productGrid   = document.getElementById('productGrid');
        const ventasPanel   = document.getElementById('ventasRow');
        const ventasDirBtn  = document.getElementById('ventasDirBtn');
        const ventasDirLabel= document.getElementById('ventasDirLabel');
        const monthSelector = document.getElementById('monthSelector');
        const ventasMonth   = document.getElementById('ventasMonth');
        const ventasYear    = document.getElementById('ventasYear');

        // ─── INIT YEAR SELECTOR ──────────────────────────────────────────────────
        (function buildYearSelect() {
            for (let y = LAUNCH_YEAR; y <= CURRENT_YEAR; y++) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                if (y === CURRENT_YEAR) opt.selected = true;
                ventasYear.appendChild(opt);
            }
            // Pre-select current month
            ventasMonth.value = CURRENT_MONTH;
        })();

        // ─── SEARCH ──────────────────────────────────────────────────────────────
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query !== '') {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                topSection.style.display = 'none';
                subSortRow.classList.add('hidden');
                ventasPanel.classList.add('hidden');
            } else {
                currentFilter = null;
                showDefaultHome();
            }

            applySearch(query);
        });

        // ─── FILTER BUTTON ───────────────────────────────────────────────────────
        function setFilter(filter, event) {
            searchInput.value = '';
            currentFilter = filter;
            sortAsc = false;

            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            if (event && event.currentTarget) event.currentTarget.classList.add('active');

            // Sub-sort row: show for 'all', 'price', 'stock'
            const hasSub = ['all', 'price', 'stock'].includes(filter);
            subSortRow.classList.toggle('hidden', !hasSub);
            if (hasSub) {
                sortAsc = (filter === 'stock');
                updateSubSortLabel();
            }

            // Ventas panel
            const isVentas = filter === 'ventas';
            ventasPanel.classList.toggle('hidden', !isVentas);

            // Top section: always hide when any filter is active
            topSection.style.display = 'none';

            if (isVentas) {
                applyVentasFilter();
            } else {
                applyFilter();
            }
        }

        // ─── SUB-SORT (all / price / stock) ──────────────────────────────────────
        function toggleSortDir() {
            sortAsc = !sortAsc;
            subSortBtn.classList.toggle('flipped', sortAsc);
            updateSubSortLabel();
            applyFilter();
        }

        function updateSubSortLabel() {
            if (!currentFilter || !sortLabels[currentFilter]) return;
            subSortLabel.textContent = sortAsc
                ? sortLabels[currentFilter].asc
                : sortLabels[currentFilter].desc;
            subSortBtn.classList.toggle('flipped', sortAsc);
        }

        // ─── VENTAS: direction toggle ─────────────────────────────────────────────
        function toggleVentasDir() {
            ventasAsc = !ventasAsc;
            ventasDirBtn.classList.toggle('flipped', ventasAsc);
            ventasDirLabel.textContent = ventasAsc ? 'Menos vendido primero' : 'Más vendido primero';
            applyVentasFilter();
        }

        // ─── VENTAS: period chips ─────────────────────────────────────────────────
        function setVentasPeriod(period) {
            ventasPeriod = period;

            // Update chips
            ['lifetime','weekly','monthly'].forEach(p => {
                document.getElementById('chip-' + p).classList.toggle('active', p === period);
            });

            // Month/year selectors visibility
            monthSelector.classList.toggle('hidden', period !== 'monthly');

            applyVentasFilter();
        }

        // ─── VENTAS: get sold qty for current period ──────────────────────────────
        function getSoldForPeriod(card) {
            if (ventasPeriod === 'lifetime') {
                return parseInt(card.dataset.sold) || 0;
            }
            if (ventasPeriod === 'weekly') {
                return parseInt(card.dataset.soldWeek) || 0;
            }
            if (ventasPeriod === 'monthly') {
                const m  = String(ventasMonth.value).padStart(2, '0');
                const y  = ventasYear.value;
                const key = `soldWeek`; // fallback key name
                // data attribute format: data-sold-2026-04
                const attr = card.dataset['sold' + y + m.replace(/^0/, '')] ?? // try without leading zero
                             card.getAttribute(`data-sold-${y}-${m}`) ?? 0;
                return parseInt(attr) || 0;
            }
            return 0;
        }

        // ─── VENTAS: apply ────────────────────────────────────────────────────────
        function applyVentasFilter() {
            const cards = Array.from(productGrid.querySelectorAll('.product-card'));

            let visible = cards.filter(card => {
                if (parseInt(card.dataset.status) !== 1) return false;
                // Para lifetime mostramos todos (aunque sean 0)
                // Para weekly y monthly solo mostramos los que tienen ventas en ese período
                if (ventasPeriod !== 'lifetime') {
                    return getSoldForPeriod(card) > 0;
                }
                return true;
            });

            visible.sort((a, b) => {
                const qa = getSoldForPeriod(a);
                const qb = getSoldForPeriod(b);
                return ventasAsc ? (qa - qb) : (qb - qa);
            });

            // Mensaje contextual según período
            if (visible.length === 0) {
                let msg = 'Sin ventas registradas en este período';
                if (ventasPeriod === 'weekly') msg = 'Sin ventas registradas esta semana';
                if (ventasPeriod === 'monthly') {
                    const monthNames = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    msg = `Sin ventas registradas en ${monthNames[parseInt(ventasMonth.value)]} ${ventasYear.value}`;
                }
                noResults.querySelector('p').textContent = msg;
            }

            renderCards(cards, visible);
        }

        // ─── APPLY FILTER (non-ventas) ────────────────────────────────────────────
        function applyFilter() {
            const cards = Array.from(productGrid.querySelectorAll('.product-card'));

            let visible = cards.filter(card => {
                const status = parseInt(card.dataset.status);
                if (currentFilter === 'paused') return status === 0;
                return status === 1;
            });

            switch (currentFilter) {
                case 'all':
                    visible.sort((a, b) => sortAsc
                        ? parseInt(a.dataset.created) - parseInt(b.dataset.created)
                        : parseInt(b.dataset.created) - parseInt(a.dataset.created));
                    break;
                case 'price':
                    visible.sort((a, b) => sortAsc
                        ? parseFloat(a.dataset.price) - parseFloat(b.dataset.price)
                        : parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
                    break;
                case 'stock':
                    visible.sort((a, b) => sortAsc
                        ? parseInt(b.dataset.stock) - parseInt(a.dataset.stock)
                        : parseInt(a.dataset.stock) - parseInt(b.dataset.stock));
                    break;
                case 'paused':
                    visible.sort((a, b) => parseInt(b.dataset.created) - parseInt(a.dataset.created));
                    break;
            }

            renderCards(cards, visible, 'No se encontraron productos');
        }

        // ─── APPLY SEARCH ─────────────────────────────────────────────────────────
        function applySearch(query) {
            const cards = Array.from(productGrid.querySelectorAll('.product-card'));

            const visible = cards.filter(card => {
                if (parseInt(card.dataset.status) === 0) return false;
                return card.dataset.name.includes(query) || card.dataset.category.includes(query);
            });

            renderCards(cards, visible, 'No se encontraron productos');
        }

        // ─── RENDER CARDS ─────────────────────────────────────────────────────────
        function renderCards(all, visible, defaultMsg) {
            all.forEach(card => card.style.display = 'none');
            visible.forEach(card => {
                card.style.display = 'block';
                productGrid.appendChild(card);
            });
            if (visible.length === 0 && defaultMsg) {
                noResults.querySelector('p').textContent = defaultMsg;
            }
            noResults.classList.toggle('hidden', visible.length > 0);
        }

        // ─── DEFAULT HOME VIEW ────────────────────────────────────────────────────
        function showDefaultHome() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            subSortRow.classList.add('hidden');
            ventasPanel.classList.add('hidden');
            topSection.style.display = 'block';

            const cards = Array.from(productGrid.querySelectorAll('.product-card'));
            const visible = cards.filter(c => parseInt(c.dataset.status) === 1);
            visible.sort((a, b) => parseInt(b.dataset.created) - parseInt(a.dataset.created));
            renderCards(cards, visible);
        }

        // ─── INIT ─────────────────────────────────────────────────────────────────
        (function init() {
            showDefaultHome();
        })();
    </script>
</x-app-layout>