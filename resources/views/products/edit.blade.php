<x-app-layout>
    <div class="py-12 bg-eunoia-bg min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">

                {{-- Header con indicador de status --}}
                <div class="bg-eunoia-crema/40 p-8 border-b border-gray-100 text-center relative">
                    <h2 class="text-3xl font-bold text-eunoia-text tracking-tighter">
                        Editar Producto
                    </h2>
                    @if($product->status === 0)
                        <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full bg-red-50 border border-red-100 text-red-500 text-[10px] font-bold uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span>
                            Producto pausado &mdash; no disponible para venta
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                            Producto activo
                        </span>
                    @endif
                </div>

                <div class="p-10">

                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-100 rounded-xl px-5 py-4">
                            @foreach($errors->all() as $error)
                                <p class="text-xs text-red-600 font-bold">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form id="edit-form"
                          action="{{ route('products.update', $product) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-8">
                        @csrf
                        @method('PATCH')

                        {{-- Nombre --}}
                        <div>
                            <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Nombre del Producto</label>
                            <input type="text" name="name" id="name-input" maxlength="40"
                                value="{{ old('name', $product->name) }}"
                                class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 text-sm shadow-sm transition"
                                placeholder="Ej: Labial Matte Rose">
                            <p id="name-error" class="hidden text-red-500 text-[10px] mt-1.5 font-bold tracking-wide uppercase">
                                &#9888; El nombre del producto es obligatorio
                            </p>
                        </div>

                        {{-- Categoría + Precio --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Categor&iacute;a</label>
                                <select name="category" required
                                    class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 text-sm shadow-sm transition">
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ $product->category == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Precio de Venta ($)</label>
                                <input type="text" name="price" id="price-input" required
                                    value="{{ old('price', number_format($product->price, 2, ',', '.')) }}$"
                                    class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 font-semibold text-gray-700 text-sm shadow-sm transition"
                                    placeholder="0,00$"
                                    {{ $product->status === 0 ? 'disabled' : '' }}>
                            </div>
                        </div>

                        {{-- Inventario --}}
                        <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-6 space-y-5">
                            <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Inventario</p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                {{-- Stock actual (solo lectura) --}}
                                <div>
                                    <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Stock Actual</label>
                                    <input type="text"
                                        value="{{ number_format($product->stock, 0, ',', '.') }}"
                                        disabled
                                        class="w-full p-3.5 rounded-xl border-gray-200 bg-gray-100 text-gray-400 font-semibold text-sm shadow-sm cursor-not-allowed text-center">
                                    <p class="text-[9px] text-gray-400 mt-1.5 text-center uppercase tracking-widest">Solo lectura</p>
                                </div>

                                {{-- Unidades a añadir --}}
                                <div>
                                    <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Stock Nuevo</label>
                                    <input type="text" name="stock" id="stock-add-input"
                                        value="{{ old('stock') }}"
                                        class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 font-semibold text-gray-700 text-sm shadow-sm transition text-center
                                               {{ $product->status === 0 ? 'opacity-50 cursor-not-allowed bg-gray-100' : '' }}"
                                        placeholder="Ej: 10"
                                        {{ $product->status === 0 ? 'disabled' : '' }}>
                                    <p class="text-[9px] text-gray-400 mt-1.5 text-center uppercase tracking-widest">Se suman al stock actual</p>
                                    <p id="stock-add-preview" class="hidden text-emerald-600 text-[10px] mt-1 font-bold tracking-wide uppercase text-center"></p>
                                </div>

                                {{-- Costo del nuevo lote --}}
                                <div>
                                    <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Costo del Lote ($)</label>
                                    <input type="text" name="cost_usd" id="cost-input"
                                        value="{{ old('cost_usd', '0,00$') }}"
                                        class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 font-semibold text-gray-700 text-sm shadow-sm transition
                                               {{ $product->status === 0 ? 'opacity-50 cursor-not-allowed bg-gray-100' : '' }}"
                                        placeholder="0,00$"
                                        {{ $product->status === 0 ? 'disabled' : '' }}>
                                    <p class="text-[9px] text-gray-400 mt-1.5 text-center uppercase tracking-widest">Solo si hay unidades nuevas</p>
                                </div>
                            </div>

                            {{-- Feedback dinámico stock --}}
                            <div id="stock-success"
                                 class="hidden rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-xs text-emerald-700 font-medium">
                                &#10003; Se crear&aacute; un nuevo lote de <strong id="diff-label">0</strong> unidades.
                                Stock nuevo total: <strong id="total-label">0</strong>.
                            </div>
                        </div>

                        {{-- Imagen --}}
                        <div>
                            <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Imagen del Producto</label>
                            <div id="dropzone" class="border-2 border-dashed border-gray-200 rounded-2xl p-8 bg-gray-50 hover:border-eunoia-coral transition duration-300 relative text-center">
                                <label class="cursor-pointer">
                                    <div id="preview-container" class="mb-4 {{ $product->image_path ? '' : 'hidden' }}">
                                        <img id="image-preview"
                                             src="{{ $product->image_path ? asset('storage/' . $product->image_path) : '#' }}"
                                             alt="Vista previa"
                                             class="h-32 w-32 object-cover rounded-xl border-2 border-eunoia-coral shadow-md mx-auto">
                                        <p class="text-[10px] text-eunoia-coral font-bold mt-2 uppercase tracking-widest">Imagen actual / seleccionada &#10003;</p>
                                    </div>
                                    <div id="upload-instructions" class="{{ $product->image_path ? 'hidden' : 'flex flex-col items-center space-y-3' }}">
                                        <svg class="h-10 w-10 text-gray-400 mx-auto" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="text-sm text-gray-600 font-semibold">Haz click para cambiar imagen</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-tighter">JPG o PNG hasta 10MB</p>
                                    </div>
                                    <input id="image-input" name="image" type="file" class="sr-only" accept="image/jpeg,image/png">
                                </label>
                            </div>
                            <p class="text-[9px] text-gray-400 mt-2 text-center uppercase tracking-widest italic">Si no seleccionas una nueva, se mantendr&aacute; la actual</p>
                        </div>

                        {{-- Botón guardar --}}
                        <div class="pt-2">
                            <button type="button" id="btn-save"
                                class="w-full bg-eunoia-coral hover:bg-[#E87A7A] text-white font-bold p-4 rounded-xl shadow-lg transition duration-200 text-xs uppercase tracking-[0.2em]">
                                Actualizar Producto
                            </button>
                        </div>

                    </form>

                    {{-- Pausar / Reactivar --}}
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        @if($product->status === 1)
                            <button type="button" id="btn-pause"
                                class="w-full border-2 border-red-200 text-red-400 hover:bg-red-50 hover:border-red-300 hover:text-red-500 font-bold p-3.5 rounded-xl transition duration-200 text-xs uppercase tracking-[0.2em]">
                                No vender este producto
                            </button>
                            <p class="text-[9px] text-gray-400 mt-2 text-center uppercase tracking-widest">
                                El producto no se elimina. Puedes reactivarlo en cualquier momento.
                            </p>
                            <form id="form-pause" action="{{ route('products.toggle-status', $product) }}" method="POST" class="hidden">
                                @csrf
                                @method('PATCH')
                            </form>
                        @else
                            <button type="button" id="btn-reactivate"
                                class="w-full border-2 border-emerald-200 text-emerald-500 hover:bg-emerald-50 hover:border-emerald-300 font-bold p-3.5 rounded-xl transition duration-200 text-xs uppercase tracking-[0.2em]">
                                &#9679; Reactivar producto
                            </button>
                            <p class="text-[9px] text-gray-400 mt-2 text-center uppercase tracking-widest">
                                Este producto est&aacute; pausado. Reactivar para volver a venderlo.
                            </p>
                            <form id="form-reactivate" action="{{ route('products.toggle-status', $product) }}" method="POST" class="hidden">
                                @csrf
                                @method('PATCH')
                            </form>
                        @endif
                    </div>

                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('dashboard') }}"
                   class="text-[11px] font-bold text-gray-400 hover:text-eunoia-coral transition uppercase tracking-widest">
                    &larr; Cancelar y Volver
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL DE CONFIRMACIÓN — estilo Sales
    ═══════════════════════════════════════════════════ --}}
    <div id="custom-modal"
         class="fixed inset-0 z-50 items-center justify-center hidden"
         style="backdrop-filter: blur(4px); background: rgba(0,0,0,0.35);">
        <div id="modal-box"
             class="bg-white rounded-3xl shadow-2xl border border-gray-100 p-8 max-w-sm w-full mx-4
                    transform scale-90 opacity-0 transition-all duration-300">

            {{-- Icono dinámico --}}
            <div class="flex items-center justify-center mb-5">
                <div id="modal-icon-wrap" class="w-14 h-14 rounded-full flex items-center justify-center" style="background: #EEF0FF;">
                    <svg id="modal-icon-svg" class="w-7 h-7" fill="none" stroke="#6C63FF" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <h3 id="modal-title" class="text-base font-bold text-eunoia-text tracking-tight mb-1 text-center"></h3>
            <p id="modal-subtitle" class="text-[10px] text-gray-400 uppercase tracking-widest text-center mb-5"></p>

            <div class="w-full h-px bg-gray-100 mb-4"></div>

            {{-- Resumen dinámico (solo visible en modal de guardar) --}}
            <div id="modal-summary" class="space-y-2 mb-5 hidden">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Nombre</span>
                    <span id="sum-name" class="text-xs font-bold text-eunoia-text text-right max-w-[55%] truncate"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Categor&iacute;a</span>
                    <span id="sum-category" class="text-xs font-bold text-eunoia-text"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Precio</span>
                    <span id="sum-price" class="text-xs font-bold text-eunoia-text"></span>
                </div>
                <div id="sum-stock-row" class="flex justify-between items-center hidden">
                    <span class="text-xs text-gray-500">Stock añadido</span>
                    <span id="sum-stock" class="text-xs font-bold text-emerald-600"></span>
                </div>
                <div id="sum-cost-row" class="flex justify-between items-center hidden">
                    <span class="text-xs text-gray-500">Costo lote</span>
                    <span id="sum-cost" class="text-xs font-bold" style="color:#6C63FF;"></span>
                </div>
            </div>

            <div id="modal-body-simple" class="text-xs text-gray-500 mb-5 text-center leading-relaxed"></div>

            <div class="w-full h-px bg-gray-100 mb-5"></div>

            <button id="modal-confirm"
                class="w-full font-bold py-3.5 rounded-xl text-xs uppercase tracking-widest transition text-white mb-3">
                Confirmar
            </button>
            <button id="modal-cancel"
                class="w-full border border-gray-200 text-gray-400 hover:border-gray-300 font-bold py-3 rounded-xl text-xs uppercase tracking-widest transition bg-gray-50">
                REVISAR DE NUEVO
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         ANIMACIÓN DE ÉXITO
    ═══════════════════════════════════════════════════ --}}
    <div id="confirm-overlay"
         class="fixed inset-0 z-50 flex items-center justify-center hidden pointer-events-none"
         style="backdrop-filter: blur(6px); background: rgba(255,255,255,0.6);">
        <div id="confirm-card"
             class="bg-white rounded-3xl shadow-2xl border border-gray-100 px-12 py-10 text-center
                    transform scale-50 opacity-0 transition-all duration-500">
            <div id="confirm-icon-wrap"
                 class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 transition-all duration-700"
                 style="background: #FFF0F0;">
                <svg id="confirm-check" class="w-10 h-10 opacity-0 transition-all duration-500" fill="none" stroke="#F28B82" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p id="confirm-text" class="text-sm font-bold text-eunoia-text tracking-tight"></p>
            <p id="confirm-sub" class="text-[10px] text-gray-400 uppercase tracking-widest mt-1"></p>
        </div>
    </div>

    <style>
        #confirm-overlay.show { display: flex !important; pointer-events: auto; }
        #confirm-overlay.show #confirm-card { transform: scale(1); opacity: 1; }
        #confirm-overlay.show #confirm-check { opacity: 1; }
        #custom-modal.show { display: flex !important; }
        #custom-modal.hidden { display: none !important; }
        #custom-modal.show #modal-box { transform: scale(1) !important; opacity: 1 !important; }
        .field-error { border-color: #f87171 !important; background-color: #fff5f5 !important; }
    </style>

    <script>
    (function () {
        var currentStock = {{ (int) $product->stock }};
        var isActive     = {{ (int) $product->status === 1 ? 'true' : 'false' }};

        // ── Imagen ────────────────────────────────────────────────────────────
        var imageInput         = document.getElementById('image-input');
        var imagePreview       = document.getElementById('image-preview');
        var previewContainer   = document.getElementById('preview-container');
        var uploadInstructions = document.getElementById('upload-instructions');

        imageInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.setAttribute('src', e.target.result);
                previewContainer.classList.remove('hidden');
                uploadInstructions.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });

        // ── Precio ────────────────────────────────────────────────────────────
        var priceInput = document.getElementById('price-input');
        if (priceInput && isActive) {
            priceInput.addEventListener('input', function (e) {
                var pos = e.target.selectionStart, len = e.target.value.length;
                var val = e.target.value.replace(/[^0-9,]/g, '');
                var parts = val.split(',');
                if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                var intPart = (parts[0] || '0').replace(/^0+/, '') || '0';
                var decPart = parts.length > 1 ? parts[1].substring(0, 2) : null;
                var numInt  = parseInt(intPart, 10);
                if (numInt >= 10000) { numInt = 10000; decPart = decPart !== null ? '00' : null; }
                var fi = new Intl.NumberFormat('es-VE').format(numInt) + (decPart !== null ? ',' + decPart : '') + '$';
                e.target.value = fi;
                if (pos >= len - 1) e.target.setSelectionRange(fi.length - 1, fi.length - 1);
            });
            priceInput.addEventListener('blur', function (e) {
                var val = e.target.value.replace(/[^0-9,]/g, '');
                if (val === '' || val === '0') { e.target.value = '0,10$'; return; }
                var parts = val.split(','), intPart = parts[0] || '0', decPart = parts[1] || '';
                if (!decPart.length) decPart = '00'; else if (decPart.length === 1) decPart += '0';
                var numInt = parseInt(intPart, 10);
                if (numInt === 0 && parseInt(decPart, 10) < 10) decPart = '10';
                if (numInt >= 10000) { numInt = 10000; decPart = '00'; }
                e.target.value = new Intl.NumberFormat('es-VE').format(numInt) + ',' + decPart + '$';
            });
        }

        // ── Stock adicional ───────────────────────────────────────────────────
        var stockAddInput   = document.getElementById('stock-add-input');
        var stockAddPreview = document.getElementById('stock-add-preview');
        var stockSuccess    = document.getElementById('stock-success');
        var diffLabel       = document.getElementById('diff-label');
        var totalLabel      = document.getElementById('total-label');
        var costInput       = document.getElementById('cost-input');

        function updateStockFeedback() {
            if (!stockAddInput) return;
            var raw = stockAddInput.value.replace(/\./g, '').replace(/[^0-9]/g, '');
            if (raw === '') {
                stockSuccess.classList.add('hidden');
                stockAddPreview.classList.add('hidden');
                return;
            }
            var add   = parseInt(raw, 10);
            var total = currentStock + add;
            diffLabel.textContent  = add;
            totalLabel.textContent = total;
            stockSuccess.classList.remove('hidden');
            stockAddPreview.textContent = 'Nuevo total: ' + total + ' unidades';
            stockAddPreview.classList.remove('hidden');
        }

        if (stockAddInput && isActive) {
            stockAddInput.addEventListener('input', function (e) {
                var val = e.target.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
                if (val === '') { e.target.value = ''; updateStockFeedback(); return; }
                var num = parseInt(val, 10);
                if (num > 10000) num = 10000;
                e.target.value = new Intl.NumberFormat('es-VE').format(num);
                updateStockFeedback();
            });
        }

        // ── Costo lote ────────────────────────────────────────────────────────
        if (costInput && isActive) {
            costInput.addEventListener('input', function (e) {
                var pos = e.target.selectionStart, len = e.target.value.length;
                var val = e.target.value.replace(/[^0-9,]/g, '');
                var parts = val.split(',');
                if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                var intPart = (parts[0] || '0').replace(/^0+/, '') || '0';
                var decPart = parts.length > 1 ? parts[1].substring(0, 2) : null;
                var numInt  = parseInt(intPart, 10);
                if (numInt >= 10000) { numInt = 10000; decPart = decPart !== null ? '00' : null; }
                var fi = new Intl.NumberFormat('es-VE').format(numInt) + (decPart !== null ? ',' + decPart : '') + '$';
                e.target.value = fi;
                if (pos >= len - 1) e.target.setSelectionRange(fi.length - 1, fi.length - 1);
            });
            costInput.addEventListener('blur', function (e) {
                var val = e.target.value.replace(/[^0-9,]/g, '');
                if (val === '' || val === '0') { e.target.value = '0,00$'; return; }
                var parts = val.split(','), intPart = parts[0] || '0', decPart = parts[1] || '';
                if (!decPart.length) decPart = '00'; else if (decPart.length === 1) decPart += '0';
                var numInt = parseInt(intPart, 10);
                if (numInt >= 10000) { numInt = 10000; decPart = '00'; }
                e.target.value = new Intl.NumberFormat('es-VE').format(numInt) + ',' + decPart + '$';
            });
        }

        // ── Modal genérico ────────────────────────────────────────────────────
        var modal           = document.getElementById('custom-modal');
        var modalBox        = document.getElementById('modal-box');
        var modalTitle      = document.getElementById('modal-title');
        var modalSubtitle   = document.getElementById('modal-subtitle');
        var modalConfirm    = document.getElementById('modal-confirm');
        var modalCancel     = document.getElementById('modal-cancel');
        var modalSummary    = document.getElementById('modal-summary');
        var modalBodySimple = document.getElementById('modal-body-simple');
        var modalIconWrap   = document.getElementById('modal-icon-wrap');
        var modalIconSvg    = document.getElementById('modal-icon-svg');
        var pendingAction   = null;

        function showModal(opts) {
            modalTitle.textContent    = opts.title    || '';
            modalSubtitle.textContent = opts.subtitle || '';
            modalConfirm.textContent  = opts.confirm  || 'Confirmar';
            modalConfirm.style.background = opts.confirmBg || '#6C63FF';
            pendingAction = opts.action;

            // Icono
            modalIconWrap.style.background = opts.iconBg || '#EEF0FF';
            modalIconSvg.style.stroke      = opts.iconColor || '#6C63FF';
            if (opts.iconPath) {
                modalIconSvg.querySelector('path').setAttribute('d', opts.iconPath);
            } else {
                modalIconSvg.querySelector('path').setAttribute('d', 'M5 13l4 4L19 7');
            }

            // Resumen vs cuerpo simple
            if (opts.summary) {
                modalSummary.classList.remove('hidden');
                modalBodySimple.classList.add('hidden');
                // Llenar resumen
                document.getElementById('sum-name').textContent     = opts.summary.name;
                document.getElementById('sum-category').textContent = opts.summary.category;
                document.getElementById('sum-price').textContent    = opts.summary.price;
                var stockRow = document.getElementById('sum-stock-row');
                var costRow  = document.getElementById('sum-cost-row');
                if (opts.summary.stock) {
                    document.getElementById('sum-stock').textContent = opts.summary.stock;
                    stockRow.classList.remove('hidden');
                } else {
                    stockRow.classList.add('hidden');
                }
                if (opts.summary.cost) {
                    document.getElementById('sum-cost').textContent = opts.summary.cost;
                    costRow.classList.remove('hidden');
                } else {
                    costRow.classList.add('hidden');
                }
            } else {
                modalSummary.classList.add('hidden');
                modalBodySimple.innerHTML  = opts.body || '';
                modalBodySimple.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('show');
            setTimeout(function () {
                modalBox.style.transform = 'scale(1)';
                modalBox.style.opacity   = '1';
            }, 10);
        }

        function hideModal() {
            modalBox.style.transform = 'scale(0.9)';
            modalBox.style.opacity   = '0';
            setTimeout(function () {
                modal.classList.remove('show');
                modal.classList.add('hidden');
            }, 300);
        }

        modalCancel.addEventListener('click', hideModal);
        modal.addEventListener('click', function (e) { if (e.target === modal) hideModal(); });
        modalConfirm.addEventListener('click', function () {
            hideModal();
            if (pendingAction) pendingAction();
        });

        // ── Animación éxito ───────────────────────────────────────────────────
        var confirmOverlay = document.getElementById('confirm-overlay');
        var confirmCard    = document.getElementById('confirm-card');
        var confirmIconWrap= document.getElementById('confirm-icon-wrap');
        var confirmCheck   = document.getElementById('confirm-check');
        var confirmText    = document.getElementById('confirm-text');
        var confirmSub     = document.getElementById('confirm-sub');

        function showSuccess(text, sub, color, onDone) {
            confirmText.textContent = text;
            confirmSub.textContent  = sub || '';
            confirmIconWrap.style.background = color + '22';
            confirmCheck.style.stroke = color;
            confirmOverlay.classList.add('show');
            setTimeout(function () {
                confirmCard.style.transform = 'scale(1)';
                confirmCard.style.opacity   = '1';
            }, 50);
            setTimeout(function () { confirmCheck.style.opacity = '1'; }, 300);
            setTimeout(function () { if (onDone) onDone(); }, 1400);
        }

        // ── Validación nombre ─────────────────────────────────────────────────
        function validateName() {
            var nameInput = document.getElementById('name-input');
            var nameError = document.getElementById('name-error');
            var nombre    = nameInput.value.trim();
            if (nombre === '') {
                nameInput.classList.add('field-error');
                nameError.classList.remove('hidden');
                nameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            nameInput.classList.remove('field-error');
            nameError.classList.add('hidden');
            return true;
        }

        document.getElementById('name-input').addEventListener('input', function () {
            if (this.value.trim() !== '') {
                this.classList.remove('field-error');
                document.getElementById('name-error').classList.add('hidden');
            }
        });

        // ── Botón Actualizar ──────────────────────────────────────────────────
        document.getElementById('btn-save').addEventListener('click', function () {
            if (!validateName()) return;

            var addRaw = stockAddInput ? stockAddInput.value.replace(/\./g, '').replace(/[^0-9]/g, '') : '';
            var addNum = addRaw !== '' ? parseInt(addRaw, 10) : 0;

            var priceVal    = priceInput ? priceInput.value : '—';
            var categoryVal = document.querySelector('select[name="category"]').value;
            var nameVal     = document.getElementById('name-input').value.trim();

            var summary = {
                name:     nameVal,
                category: categoryVal,
                price:    priceVal,
            };
            if (addNum > 0) {
                summary.stock = '+' + addNum + ' uds. → Total: ' + (currentStock + addNum);
                summary.cost  = costInput ? costInput.value : '—';
            }

            showModal({
                title:     '¿CONFIRMAR ACTUALIZACIÓN?',
                subtitle:  'Resumen de los cambios',
                confirm:   'CONFIRMAR Y GUARDAR',
                confirmBg: '#6C63FF',
                summary:   summary,
                action: function () {
                    // Limpiar antes de submit
                    if (priceInput && !priceInput.disabled) {
                        priceInput.value = parseFloat(priceInput.value.replace(/\$/g, '').replace(/\./g, '').replace(',', '.')) || 0;
                    }
                    if (stockAddInput && !stockAddInput.disabled && stockAddInput.value !== '') {
                        stockAddInput.value = parseInt(stockAddInput.value.replace(/\./g, ''), 10) || '';
                    }
                    if (costInput && !costInput.disabled) {
                        costInput.value = parseFloat(costInput.value.replace(/\$/g, '').replace(/\./g, '').replace(',', '.')) || 0;
                    }
                    showSuccess('Actualizando producto…', 'Un momento', '#F28B82',
                        function () { document.getElementById('edit-form').submit(); }
                    );
                }
            });
        });

        // ── Pausar ────────────────────────────────────────────────────────────
        var btnPause = document.getElementById('btn-pause');
        if (btnPause) {
            btnPause.addEventListener('click', function () {
                showModal({
                    title:     '¿PAUSAR ESTE PRODUCTO?',
                    subtitle:  '',
                    body:      'Ya no aparecer&aacute; disponible para venta. <strong>No se elimina</strong>, puedes reactivarlo cuando quieras.',
                    confirm:   'Pausar',
                    confirmBg: '#f87171',
                    iconBg:    '#FEF2F2',
                    iconColor: '#f87171',
                    iconPath:  'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    action: function () {
                        showSuccess('Pausando producto…', 'Un momento', '#F87171',
                            function () { document.getElementById('form-pause').submit(); }
                        );
                    }
                });
            });
        }

        // ── Reactivar ─────────────────────────────────────────────────────────
        var btnReactivate = document.getElementById('btn-reactivate');
        if (btnReactivate) {
            btnReactivate.addEventListener('click', function () {
                showModal({
                    title:     '¿REACTIVAR ESTE PRODUCTO?',
                    subtitle:  '',
                    body:      'Volver&aacute; a estar disponible para venta.',
                    confirm:   'Reactivar',
                    confirmBg: '#34D399',
                    iconBg:    '#ECFDF5',
                    iconColor: '#34D399',
                    iconPath:  'M5 13l4 4L19 7',
                    action: function () {
                        showSuccess('Reactivando producto…', 'Un momento', '#34D399',
                            function () { document.getElementById('form-reactivate').submit(); }
                        );
                    }
                });
            });
        }

    })();
    </script>
</x-app-layout>