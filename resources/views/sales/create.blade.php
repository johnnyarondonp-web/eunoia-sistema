<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .blur-content { filter: blur(5px); pointer-events: none; user-select: none; }
        .select2-container--default .select2-selection--single { border-radius: 0; border-color: #e5e7eb; height: 45px; display: flex; align-items: center; }
        .product-img { width: 30px; height: 30px; border-radius: 4px; object-fit: cover; margin-right: 10px; border: 1px solid #eee; }
        .product-option { display: flex; align-items: center; font-size: 13px; }
        .select2-container--default .select2-results__option[aria-disabled=true] { display: none; } 
        /* Transición suave para la alerta */
        #ui-error-alert { transition: all 0.3s ease; }
    </style>

    <x-slot name="header">
        <h2 class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-800 leading-tight">
            {{ __('Registrar Venta') }}
        </h2>
    </x-slot>

    <div id="main-content" class="py-12 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-8 border border-gray-100">
                
                <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400 block">Tasa BCV</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="number" step="0.01" name="bcv_rate" id="current-bcv-rate" value="{{ $bcvRate }}" min="1" max="9999" class="w-24 sm:w-32 text-lg font-mono font-bold text-indigo-600 border-gray-200 rounded focus:ring-0 focus:border-indigo-500 py-1 px-2 cursor-not-allowed bg-gray-50" form="sales-form" readonly>
                            <span class="text-lg font-mono font-bold text-indigo-600">Bs.</span>
                            <button type="button" class="text-[9px] uppercase tracking-widest font-bold text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded-full transition" onclick="
                                const input = document.getElementById('current-bcv-rate');
                                input.readOnly = false;
                                input.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-gray-50');
                                this.style.display = 'none';
                            ">
                                ✏ Usar tasa personalizada
                            </button>
                        </div>
                    </div>
                    <button type="button" id="add-item" class="w-full sm:w-auto border border-black text-black px-4 py-2 text-[10px] uppercase tracking-widest font-bold hover:bg-black hover:text-white transition">
                        + Añadir Producto
                    </button>
                </div>

                @if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm" id="ui-error-alert">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                     <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-red-800 uppercase tracking-wider">No se pudo procesar la venta:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
                <form action="{{ route('sales.store') }}" method="POST" id="sales-form">
                    @csrf
                    
                    <div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
                        <table class="w-full mb-8 min-w-[320px]" id="items-table">
                            <thead>
                                <tr class="text-left border-b border-gray-100">
                                    <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400">Producto</th>
                                    <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400 w-24 sm:w-32">Cantidad</th>
                                    <th class="pb-4 text-[10px] uppercase tracking-widest text-gray-400 w-20 sm:w-32 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="items-body"></tbody>
                        </table>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col items-end space-y-2">
                        <div class="flex space-x-4 items-center">
                            <span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Total Dólares:</span>
                            <span class="text-xl font-bold text-gray-800">$<span id="total-usd">0.00</span></span>
                        </div>
                        <div class="flex space-x-4 items-center">
                            <span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Total Bolívares:</span>
                            <span class="text-2xl font-bold text-indigo-600"><span id="total-bs">0.00</span> Bs.</span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 flex flex-col items-end w-full">
                        <div id="ui-error-alert" class="hidden mb-4 bg-red-50 border border-red-100 text-red-600 px-4 py-2 rounded-lg flex items-center shadow-sm w-full sm:w-auto">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span id="error-alert-text" class="text-[10px] uppercase font-bold tracking-widest"></span>
                        </div>

                        <button type="button" onclick="showSaleModal()" class="w-full sm:w-auto bg-[#e98585] text-white px-12 py-4 rounded-full text-[10px] uppercase tracking-[0.2em] font-bold hover:bg-[#d47474] transition shadow-lg text-center">
                            Finalizar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-[2px]">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full mx-4 border border-gray-100">
            <div class="text-center">
                <div class="bg-indigo-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 uppercase tracking-tighter">¿Confirmar Venta?</h3>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-1 mb-6">Resumen de la transacción</p>
                
                <div class="bg-gray-50 rounded-2xl p-5 mb-8 text-left border border-gray-100 max-h-60 overflow-y-auto">
                    <div id="resumen-lista" class="space-y-3 mb-4"></div>
                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total USD:</span>
                            <span id="modal-total-usd" class="text-sm font-bold text-gray-800"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total BS:</span>
                            <span id="modal-total-bs" class="text-lg font-black text-indigo-600"></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-3">
                    <button onclick="submitSaleForm()" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold text-[11px] uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                        Confirmar y Finalizar
                    </button>
                    <button onclick="closeSaleModal()" class="w-full bg-gray-100 text-gray-500 py-3 rounded-xl font-bold text-[11px] uppercase tracking-widest hover:bg-gray-200 transition">
                        Revisar de nuevo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let rowIdx = 0;
        let bcvRate = parseFloat($('#current-bcv-rate').val());
        
        $('#current-bcv-rate').on('input', function() {
            bcvRate = parseFloat($(this).val()) || 1;
            calculateTotals();
        });

        function formatProduct(opt) {
            if (!opt.id) return opt.text;
            const img = $(opt.element).data('img');
            const stock = $(opt.element).data('stock');
            return $(`
                <span class="product-option">
                    <img src="${img}" class="product-img" onerror="this.src='https://placehold.co/50x50?text=?'" /> 
                    <div class="flex flex-col">
                        <b class="text-sm">${opt.text}</b>
                        <span class="text-[10px] text-gray-500 uppercase tracking-tighter">Stock disponible: ${stock}</span>
                    </div>
                </span>
            `);
        }

        function updateAvailableProducts() {
            const selectedIds = [];
            $('.product-select').each(function() {
                const val = $(this).val();
                if (val) selectedIds.push(val);
            });

            $('.product-select').each(function() {
                const currentSelect = $(this);
                const currentVal = currentSelect.val();
                currentSelect.find('option').each(function() {
                    const optionVal = $(this).val();
                    if (!optionVal) return;
                    if (selectedIds.includes(optionVal) && optionVal !== currentVal) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
        }

        function calculateTotals() {
            let totalUsd = 0;
            $('.item-row').each(function() {
                const select = $(this).find('.product-select');
                const quantity = $(this).find('.qty-input').val();
                const price = select.find(':selected').data('price');
                if (price && quantity) {
                    totalUsd += parseFloat(price) * parseInt(quantity);
                }
            });
            $('#total-usd').text(totalUsd.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#total-bs').text((totalUsd * bcvRate).toLocaleString('es-VE', {minimumFractionDigits: 2}));
            
            // Si el total cambia, ocultamos la alerta de error general
            if (totalUsd > 0) $('#ui-error-alert').addClass('hidden');
        }

        function addItem() {
            const html = `
                <tr class="border-b border-gray-50 item-row" id="row-${rowIdx}">
                    <td class="py-4">
                        <select name="items[${rowIdx}][product_id]" class="product-select w-full" required>
                            <option value="">Buscar producto...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-img="{{ asset('storage/' . $product->image_path) }}" 
                                        data-price="{{ $product->price }}"
                                        data-stock="{{ $product->stock }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="py-4 px-2 sm:px-4 relative w-24 sm:w-32">
                        <input type="number" name="items[${rowIdx}][quantity]" min="1" value="1" 
                               class="qty-input w-full border-gray-200 text-sm focus:ring-0 focus:border-black">
                        <span class="qty-min-error hidden text-[8px] text-red-500 font-bold uppercase absolute bottom-0 left-2 sm:left-4">Mínimo 1</span>
                    </td>
                    <td class="py-4 text-right w-20 sm:w-32">
                        <button type="button" onclick="removeRow(${rowIdx})" class="text-red-400 hover:text-red-600 text-[10px] uppercase font-bold tracking-widest">Eliminar</button>
                    </td>
                </tr>
            `;
            $('#items-body').append(html);
            
            const currentRow = $(`#row-${rowIdx}`);
            const newSelect = currentRow.find('.product-select');
            const qtyInput = currentRow.find('.qty-input');
            const qtyError = currentRow.find('.qty-min-error');

            newSelect.select2({
                templateResult: formatProduct,
                templateSelection: formatProduct,
                placeholder: "Seleccionar...",
            });

            newSelect.on('change', function() {
                const selectedStock = $(this).find(':selected').data('stock');
                if (selectedStock !== undefined) {
                    qtyInput.attr('max', selectedStock);
                    if (parseInt(qtyInput.val()) > selectedStock) qtyInput.val(selectedStock);
                }
                updateAvailableProducts();
                calculateTotals();
            });

            // --- LÓGICA DE CANTIDAD CORREGIDA ---
            qtyInput.on('input', function() {
                const valStr = $(this).val();
                
                // 1. Permitimos que el campo esté temporalmente vacío mientras el usuario teclea
                if (valStr === '') {
                    calculateTotals();
                    return;
                }

                const val = parseInt(valStr);
                const max = parseInt($(this).attr('max'));
                
                // 2. Si intenta poner 0 o negativo, lo regresamos a 1
                if (val < 1) {
                    $(this).val(1);
                    qtyError.removeClass('hidden');
                    setTimeout(() => qtyError.addClass('hidden'), 2000);
                } 
                // 3. Si supera el stock máximo, lo limitamos
                else if (!isNaN(max) && val > max) {
                    $(this).val(max);
                }
                
                calculateTotals();
            });

            // 4. NUEVO: Si el usuario se sale del campo (blur) y lo dejó vacío, le ponemos 1
            qtyInput.on('blur', function() {
                if ($(this).val() === '' || isNaN(parseInt($(this).val()))) {
                    $(this).val(1);
                    calculateTotals();
                }
            });
            // -------------------------------------

            updateAvailableProducts();
            rowIdx++;
        }

        function removeRow(id) {
            $(`#row-${id}`).remove();
            updateAvailableProducts();
            calculateTotals();
        }


        function showSaleModal() {
            const rows = $('.item-row');
            const totalUsd = parseFloat($('#total-usd').text().replace(',', ''));

            // 1. Alerta si no hay productos añadidos
            if (rows.length === 0) {
                $('#error-alert-text').text("¡Añade al menos un producto!");
                $('#ui-error-alert').removeClass('hidden');
                return;
            }

            // 2. Alerta si no se ha seleccionado producto en el select
            if (!$('.product-select').first().val()) {
                $('#error-alert-text').text("Por favor, selecciona un producto.");
                $('#ui-error-alert').removeClass('hidden');
                return;
            }

            // 3. Alerta si el total es cero (por si acaso)
            if (totalUsd <= 0) {
                $('#error-alert-text').text("La venta debe ser mayor a $0.00.");
                $('#ui-error-alert').removeClass('hidden');
                return;
            }

            // Si todo está bien, preparamos el resumen
            let listaHtml = '';
            rows.each(function() {
                const nombre = $(this).find('.product-select option:selected').text().trim();
                const qty = $(this).find('.qty-input').val();
                if (nombre && qty && nombre !== "Buscar producto...") {
                    listaHtml += `
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600 font-medium">${nombre}</span>
                            <span class="text-gray-800 font-bold">x${qty}</span>
                        </div>
                    `;
                }
            });

            $('#resumen-lista').html(listaHtml);
            $('#modal-total-usd').text('$' + $('#total-usd').text());
            $('#modal-total-bs').text($('#total-bs').text() + ' Bs.');

            $('#main-content').addClass('blur-content');
            $('#confirm-modal').removeClass('hidden');
        }

        function closeSaleModal() {
            $('#main-content').removeClass('blur-content');
            $('#confirm-modal').addClass('hidden');
        }

        function submitSaleForm() {
            $('#sales-form').submit();
        }

        $(document).ready(function() {
            addItem();
            $('#add-item').on('click', addItem);
        });
    </script>
</x-app-layout>