<x-app-layout>
    <div class="py-3 sm:py-12 bg-white min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">

                <div class="bg-eunoia-crema/40 p-6 sm:p-8 border-b border-gray-100 text-center">
                    <h2 class="text-3xl font-bold text-eunoia-text tracking-tighter">
                        Nuevo Producto
                    </h2>
                    <p class="text-[9px] text-gray-400 uppercase tracking-[0.3em] mt-2">Registro de Inventario &bull; Eunoia</p>
                </div>

                <div class="p-4 sm:p-10">
                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="product-form">
                        @csrf

                        {{-- Nombre --}}
                        <div>
                            <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Nombre del Producto</label>
                            <input type="text" name="name" id="name-input" maxlength="40" value="{{ old('name') }}"
                                class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 text-sm shadow-sm transition"
                                placeholder="Ej: Labial Matte Rose">
                            <p id="name-error" class="hidden text-red-500 text-[10px] mt-1.5 font-bold tracking-wide uppercase">
                                &#9888; El nombre del producto es obligatorio
                            </p>
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Categor&iacute;a</label>
                            <select name="category" id="category-input"
                                class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 text-sm shadow-sm transition">
                                <option value="" disabled selected>Seleccionar...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                            <p id="category-error" class="hidden text-red-500 text-[10px] mt-1.5 font-bold tracking-wide uppercase">
                                &#9888; Debes seleccionar una categor&iacute;a
                            </p>
                        </div>

                        {{-- Precio / Stock / Costo --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Precio Unitario($)</label>
                                <input type="text" name="price" id="price-input" value="0,00$"
                                    class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 font-semibold text-gray-700 text-sm shadow-sm transition"
                                    placeholder="0,10$">
                                <p id="price-error" class="hidden text-red-500 text-[10px] mt-1.5 font-bold tracking-wide uppercase">
                                    &#9888; M&iacute;nimo 0,10$
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Cantidad en Stock</label>
                                <input type="text" name="stock" id="stock-input" value="0"
                                    class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 font-semibold text-gray-700 text-sm shadow-sm transition"
                                    placeholder="1">
                                <p id="stock-error" class="hidden text-red-500 text-[10px] mt-1.5 font-bold tracking-wide uppercase">
                                    &#9888; Stock m&iacute;nimo es 1 unidad
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Costo Total Lote($)</label>
                                <input type="text" name="total_cost_usd" id="cost-input" value="0,00$"
                                    class="w-full p-3.5 rounded-xl border border-gray-200 focus:border-eunoia-coral focus:ring-0 bg-gray-50/50 font-semibold text-gray-700 text-sm shadow-sm transition"
                                    placeholder="1,00$">
                                <p id="cost-error" class="hidden text-red-500 text-[10px] mt-1.5 font-bold tracking-wide uppercase">
                                    &#9888; El costo del lote m&iacute;nimo es 1,00$
                                </p>
                            </div>
                        </div>

                        {{-- Imagen --}}
                        <div>
                            <label class="block text-xs font-bold text-eunoia-text uppercase tracking-widest mb-3 ml-1">Imagen del Producto</label>
                            <div id="dropzone" class="border-2 border-dashed border-gray-200 rounded-2xl p-8 bg-gray-50 hover:border-eunoia-coral transition duration-300 relative">
                                <label class="flex flex-col items-center justify-center space-y-3 cursor-pointer">
                                    <div id="preview-container" class="hidden mb-2 text-center">
                                        <img id="image-preview" src="#" alt="Vista previa" class="h-32 w-32 object-cover rounded-xl border-2 border-eunoia-coral shadow-md mx-auto">
                                        <p class="text-[10px] text-eunoia-coral font-bold mt-2 uppercase tracking-widest">Imagen seleccionada &#10003;</p>
                                    </div>
                                    <div id="upload-instructions" class="flex flex-col items-center space-y-3">
                                        <svg class="h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="text-center">
                                            <p class="text-sm text-gray-600 font-semibold">Haz click para subir imagen</p>
                                            <p class="text-[10px] text-gray-400 uppercase tracking-tighter">Solo JPG o PNG hasta 10MB</p>
                                        </div>
                                    </div>
                                    <input id="image-input" name="image" type="file" class="sr-only" accept="image/jpeg,image/png">
                                </label>
                            </div>
                            <p id="image-error" class="hidden text-red-500 text-[10px] mt-1.5 font-bold tracking-wide uppercase">
                                &#9888; Debes subir una imagen del producto
                            </p>
                        </div>

                        {{-- Botón guardar --}}
                        <div class="pt-6">
                            <button type="button" id="btn-save"
                                class="w-full bg-eunoia-coral hover:bg-[#E87A7A] text-white font-bold p-4 rounded-xl shadow-lg transition duration-200 text-xs uppercase tracking-[0.2em]">
                                Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('dashboard') }}" class="text-[11px] font-bold text-gray-400 hover:text-eunoia-coral transition uppercase tracking-widest">
                    &larr; Volver al Panel
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL DE CONFIRMACIÓN — estilo Sales
    ═══════════════════════════════════════════════════ --}}
    <div id="custom-modal"
         class="fixed inset-0 z-50 flex items-center justify-center hidden"
         style="backdrop-filter: blur(4px); background: rgba(0,0,0,0.35);">
        <div id="modal-box"
             class="bg-white rounded-3xl shadow-2xl border border-gray-100 p-8 max-w-sm w-full mx-4
                    transform scale-90 opacity-0 transition-all duration-300">

            {{-- Icono checkmark al top --}}
            <div class="flex items-center justify-center mb-5">
                <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background: #EEF0FF;">
                    <svg class="w-7 h-7" fill="none" stroke="#6C63FF" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <h3 class="text-base font-bold text-eunoia-text tracking-tight mb-1 text-center">¿GUARDAR ESTE PRODUCTO?</h3>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest text-center mb-5">Resumen del Registro</p>

            {{-- Línea separadora --}}
            <div class="w-full h-px bg-gray-100 mb-4"></div>

            {{-- Resumen dinámico --}}
            <div class="space-y-2 mb-5">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Nombre</span>
                    <span id="sum-name" class="text-xs font-bold text-eunoia-text text-right max-w-[55%] truncate"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Categor&iacute;a</span>
                    <span id="sum-category" class="text-xs font-bold text-eunoia-text"></span>
                </div>
                <div class="w-full h-px bg-gray-100 my-1"></div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Precio unitario</span>
                    <span id="sum-price" class="text-xs font-bold text-eunoia-text"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Unidades</span>
                    <span id="sum-stock" class="text-xs font-bold text-eunoia-text"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Costo del lote</span>
                    <span id="sum-cost" class="text-xs font-bold" style="color:#6C63FF;"></span>
                </div>
            </div>

            <div class="w-full h-px bg-gray-100 mb-5"></div>

            {{-- Botones --}}
            <button id="modal-confirm"
                class="w-full font-bold py-3.5 rounded-xl text-xs uppercase tracking-widest transition text-white mb-3"
                style="background:#6C63FF;">
                CONFIRMAR Y GUARDAR
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
                 class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5"
                 style="background: #FFF0F0;">
                <svg id="confirm-check" class="w-10 h-10 opacity-0 transition-all duration-500"
                     fill="none" stroke="#F28B82" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p id="confirm-text" class="text-sm font-bold text-eunoia-text tracking-tight"></p>
            <p id="confirm-sub"  class="text-[10px] text-gray-400 uppercase tracking-widest mt-1"></p>
        </div>
    </div>

    <style>
        #confirm-overlay.show { display: flex !important; }
        #confirm-overlay.show #confirm-card  { transform: scale(1); opacity: 1; }
        #confirm-overlay.show #confirm-check { opacity: 1; }
        #custom-modal.show { display: flex !important; }
        #custom-modal.show #modal-box { transform: scale(1); opacity: 1; }
        .field-error { border-color: #f87171 !important; background-color: #fff5f5 !important; }
    </style>

    <script>
        // ── Imagen ────────────────────────────────────────────────────────────
        const imageInput         = document.getElementById('image-input');
        const imagePreview       = document.getElementById('image-preview');
        const previewContainer   = document.getElementById('preview-container');
        const uploadInstructions = document.getElementById('upload-instructions');
        const imageError         = document.getElementById('image-error');

        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            imageError.classList.add('hidden');
            if (!file) return;
            const validTypes = ['image/jpeg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                imageError.textContent = '⚠ Error: Solo se permiten archivos JPG o PNG.';
                imageError.classList.remove('hidden');
                this.value = '';
                previewContainer.classList.add('hidden');
                uploadInstructions.classList.remove('hidden');
                return;
            }
            const reader = new FileReader();
            reader.onload = e => {
                imagePreview.setAttribute('src', e.target.result);
                previewContainer.classList.remove('hidden');
                uploadInstructions.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });

        // ── Precio ───────────────────────────────────────────────────────────
        // Comportamiento:
        // - Al cargar la página: muestra 0,00$ visualmente
        // - Mientras escribe: formato libre permitiendo 0,00$
        // - Al salir (blur): si está vacío, es 0,00$ o menor a 0,10$ → corrige a mínimo 0,10$
        const priceInput = document.getElementById('price-input');

        priceInput.addEventListener('input', function (e) {
            let pos = e.target.selectionStart, len = e.target.value.length;
            let val = e.target.value.replace(/[^0-9,]/g, '');
            let parts = val.split(',');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let intPart = (parts[0] || '0').replace(/^0+/, '') || '0';
            let decPart = parts.length > 1 ? parts[1].substring(0, 2) : null;
            let numInt  = parseInt(intPart, 10);
            if (numInt >= 10000) { numInt = 10000; decPart = decPart !== null ? '00' : null; }
            let fi = new Intl.NumberFormat('es-VE').format(numInt) + (decPart !== null ? ',' + decPart : '') + '$';
            e.target.value = fi;
            if (pos >= len - 1) e.target.setSelectionRange(fi.length - 1, fi.length - 1);
        });

        priceInput.addEventListener('blur', function (e) {
            let val = e.target.value.replace(/[^0-9,]/g, '');
            // Vacío → mínimo
            if (val === '' || val === '0') { e.target.value = '0,10$'; return; }
            let parts = val.split(',');
            let intPart = parts[0] || '0';
            let decPart = parts.length > 1 ? parts[1] : '';
            // Completar decimales
            if (!decPart.length) decPart = '00';
            else if (decPart.length === 1) decPart += '0';
            let numInt = parseInt(intPart, 10);
            if (numInt >= 10000) { numInt = 10000; decPart = '00'; }
            // Si el valor total es menor a 0,10 → forzar mínimo
            let totalCents = numInt * 100 + parseInt(decPart, 10);
            if (totalCents < 10) { numInt = 0; decPart = '10'; }
            e.target.value = new Intl.NumberFormat('es-VE').format(numInt) + ',' + decPart + '$';
        });

        // ── Stock ─────────────────────────────────────────────────────────────
        // Comportamiento:
        // - Al cargar: muestra 0
        // - Mientras escribe: permite 0 (no bloquea)
        // - Al salir (blur): si es 0 o vacío → corrige a 1
        const stockInput = document.getElementById('stock-input');

        stockInput.addEventListener('input', function (e) {
            let val = e.target.value.replace(/[^0-9]/g, '');
            // Permitir vacío temporalmente mientras escribe
            if (val === '') { e.target.value = ''; return; }
            // Quitar ceros a la izquierda excepto si el valor ES cero
            let num = parseInt(val, 10);
            if (num > 10000) num = 10000;
            e.target.value = new Intl.NumberFormat('es-VE').format(num);
        });

        stockInput.addEventListener('blur', function (e) {
            let val = e.target.value.replace(/[^0-9]/g, '');
            let num = parseInt(val, 10);
            // Si está vacío, es NaN o es 0 → poner mínimo 1
            if (isNaN(num) || num < 1) e.target.value = '1';
        });

        // ── Costo lote ────────────────────────────────────────────────────────
        // Comportamiento:
        // - Al cargar la página: muestra 0,00$ visualmente
        // - Mientras escribe: formato libre permitiendo 0,00$
        // - Al salir (blur): si está vacío, es 0,00$ o menor a 1,00$ → corrige a mínimo 1,00$
        const costInput = document.getElementById('cost-input');

        costInput.addEventListener('input', function (e) {
            let pos = e.target.selectionStart, len = e.target.value.length;
            let val = e.target.value.replace(/[^0-9,]/g, '');
            let parts = val.split(',');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let intPart = (parts[0] || '0').replace(/^0+/, '') || '0';
            let decPart = parts.length > 1 ? parts[1].substring(0, 2) : null;
            let numInt  = parseInt(intPart, 10);
            if (numInt >= 10000) { numInt = 10000; decPart = decPart !== null ? '00' : null; }
            let fi = new Intl.NumberFormat('es-VE').format(numInt) + (decPart !== null ? ',' + decPart : '') + '$';
            e.target.value = fi;
            if (pos >= len - 1) e.target.setSelectionRange(fi.length - 1, fi.length - 1);
        });

        costInput.addEventListener('blur', function (e) {
            let val = e.target.value.replace(/[^0-9,]/g, '');
            // Vacío → mínimo
            if (val === '' || val === '0') { e.target.value = '1,00$'; return; }
            let parts = val.split(',');
            let intPart = parts[0] || '0';
            let decPart = parts.length > 1 ? parts[1] : '';
            // Completar decimales
            if (!decPart.length) decPart = '00';
            else if (decPart.length === 1) decPart += '0';
            let numInt = parseInt(intPart, 10);
            if (numInt >= 10000) { numInt = 10000; decPart = '00'; }
            // Si el valor total es menor a 1,00 → forzar mínimo
            let totalCents = numInt * 100 + parseInt(decPart, 10);
            if (totalCents < 100) { numInt = 1; decPart = '00'; }
            e.target.value = new Intl.NumberFormat('es-VE').format(numInt) + ',' + decPart + '$';
        });

        // ── Helpers de validación ─────────────────────────────────────────────
        function setError(fieldId, errorId, show) {
            const field = document.getElementById(fieldId);
            const error = document.getElementById(errorId);
            if (show) {
                field && field.classList.add('field-error');
                error && error.classList.remove('hidden');
            } else {
                field && field.classList.remove('field-error');
                error && error.classList.add('hidden');
            }
            return show;
        }

        function getPriceValue() {
            let v = priceInput.value.replace(/\$/g, '').replace(/\./g, '').replace(',', '.');
            return parseFloat(v) || 0;
        }
        function getCostValue() {
            let v = costInput.value.replace(/\$/g, '').replace(/\./g, '').replace(',', '.');
            return parseFloat(v) || 0;
        }
        function getStockValue() {
            let v = stockInput.value.replace(/\./g, '');
            return parseInt(v, 10) || 0;
        }

        function validateAll() {
            let ok = true;
            const nombre = document.getElementById('name-input').value.trim();
            if (setError('name-input', 'name-error', nombre === '')) ok = false;

            const cat = document.getElementById('category-input').value;
            if (setError('category-input', 'category-error', !cat)) ok = false;

            const price = getPriceValue();
            if (setError('price-input', 'price-error', price < 0.10)) ok = false;

            const stock = getStockValue();
            if (setError('stock-input', 'stock-error', stock < 1)) ok = false;

            const cost = getCostValue();
            if (setError('cost-input', 'cost-error', cost < 1)) ok = false;

            const hasImage = imageInput.files && imageInput.files.length > 0;
            if (setError('image-input', 'image-error', !hasImage)) ok = false;

            return ok;
        }

        // Limpiar errores al corregir
        document.getElementById('name-input').addEventListener('input', () => setError('name-input','name-error', false));
        document.getElementById('category-input').addEventListener('change', () => setError('category-input','category-error', false));
        priceInput.addEventListener('input', () => setError('price-input','price-error', false));
        stockInput.addEventListener('input', () => setError('stock-input','stock-error', false));
        costInput.addEventListener('input', () => setError('cost-input','cost-error', false));
        imageInput.addEventListener('change', () => setError('image-input','image-error', false));

        // ── Modal ─────────────────────────────────────────────────────────────
        const modal        = document.getElementById('custom-modal');
        const modalBox     = document.getElementById('modal-box');
        const modalConfirm = document.getElementById('modal-confirm');
        const modalCancel  = document.getElementById('modal-cancel');

        function showModal() {
            // Rellenar resumen
            document.getElementById('sum-name').textContent     = document.getElementById('name-input').value.trim();
            document.getElementById('sum-category').textContent = document.getElementById('category-input').value;
            document.getElementById('sum-price').textContent    = priceInput.value;
            document.getElementById('sum-stock').textContent    = stockInput.value + ' uds.';
            document.getElementById('sum-cost').textContent     = costInput.value;

            modal.classList.add('show');
            setTimeout(() => { modalBox.style.transform = 'scale(1)'; modalBox.style.opacity = '1'; }, 10);
        }
        function hideModal() {
            modalBox.style.transform = 'scale(0.9)';
            modalBox.style.opacity   = '0';
            setTimeout(() => modal.classList.remove('show'), 300);
        }

        modalCancel.addEventListener('click', hideModal);
        modal.addEventListener('click', e => { if (e.target === modal) hideModal(); });

        // ── Animación de éxito ────────────────────────────────────────────────
        const confirmOverlay = document.getElementById('confirm-overlay');
        const confirmCard    = document.getElementById('confirm-card');
        const confirmCheck   = document.getElementById('confirm-check');
        const confirmText    = document.getElementById('confirm-text');
        const confirmSub     = document.getElementById('confirm-sub');

        function showSuccess(text, sub, onDone) {
            confirmText.textContent = text;
            confirmSub.textContent  = sub || '';
            confirmOverlay.classList.add('show');
            setTimeout(() => { confirmCard.style.transform = 'scale(1)'; confirmCard.style.opacity = '1'; }, 50);
            setTimeout(() => { confirmCheck.style.opacity = '1'; }, 300);
            setTimeout(() => { if (onDone) onDone(); }, 1400);
        }

        // ── Confirmar → limpiar → submit ──────────────────────────────────────
        modalConfirm.addEventListener('click', function () {
            hideModal();
            // Convertir valores formateados a numéricos antes del submit
            priceInput.value = getPriceValue();
            stockInput.value = getStockValue();
            costInput.value  = getCostValue();

            showSuccess(
                '¡Producto registrado!',
                'Guardando en inventario…',
                function () { document.getElementById('product-form').submit(); }
            );
        });

        // ── Botón guardar: validar primero, luego modal ───────────────────────
        document.getElementById('btn-save').addEventListener('click', function () {
            if (!validateAll()) {
                // Scroll al primer error
                const firstErr = document.querySelector('.field-error');
                if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            showModal();
        });
    </script>
</x-app-layout>