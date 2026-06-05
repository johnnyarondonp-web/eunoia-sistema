<div class="md:hidden divide-y divide-gray-100">
    @foreach($lotes as $lote)
    @php
        $gananciaLote = max(0, $lote->total_recaudado - $lote->cost_usd);
        $recuperado   = $lote->cost_usd > 0
            ? min(100, round(($lote->total_recaudado / $lote->cost_usd) * 100))
            : 0;
        $enPositivo   = $lote->total_recaudado >= $lote->cost_usd;
    @endphp
    <div class="p-4 space-y-3"
         data-search="{{ strtolower(($lote->product->name ?? '') . ' ' . ($lote->product->category ?? '')) }}"
         data-ganancia="{{ $gananciaLote }}">

        <div class="flex items-start gap-3">
            <img src="{{ isset($lote->product) && $lote->product->image_path
                        ? asset('storage/' . $lote->product->image_path)
                        : asset('img/default.png') }}"
                 class="w-12 h-12 rounded-xl object-cover border border-gray-100 flex-shrink-0"
                 alt="{{ $lote->product->name ?? 'Producto' }}">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-gray-800 leading-snug">
                    {{ $lote->product->name ?? 'Producto Eliminado' }}
                </div>
                <div class="text-[10px] text-gray-400 uppercase tracking-tighter mt-0.5">
                    Lote #{{ $lote->id }} &middot; {{ $lote->created_at?->format('d/m/Y') ?? 'Sin fecha' }}
                </div>
                @if(isset($lote->product->category))
                    <div class="text-[10px] text-indigo-400 uppercase tracking-tighter mt-0.5">
                        {{ $lote->product->category }}
                    </div>
                @endif
            </div>
            <div class="text-right flex-shrink-0">
                @if($enPositivo)
                    <div class="text-sm font-bold text-emerald-600 leading-tight">
                        +${{ number_format($gananciaLote, 2) }}
                    </div>
                    <div class="text-[9px] text-gray-400 uppercase tracking-widest mt-0.5">
                        Ganancia
                    </div>
                @else
                    <div class="text-xs font-bold text-amber-500 leading-tight">
                        -${{ number_format($lote->cost_usd - $lote->total_recaudado, 2) }}
                    </div>
                    <div class="text-[9px] text-gray-400 uppercase tracking-widest mt-0.5">
                        Faltante
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-gray-50 rounded-xl px-3 py-2">
                <div class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">Inversi&oacute;n</div>
                <div class="text-sm font-semibold text-gray-700 mt-0.5">
                    ${{ number_format($lote->cost_usd, 2) }}
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl px-3 py-2">
                <div class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">Stock</div>
                <div class="text-sm font-semibold text-gray-700 mt-0.5 whitespace-nowrap">
                    {{ $lote->remaining_quantity }}<span class="text-gray-400">/{{ $lote->quantity }}</span>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl px-3 py-2">
                <div class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">Ventas</div>
                <div class="text-sm font-bold text-gray-800 mt-0.5">
                    ${{ number_format($lote->total_recaudado, 2) }}
                </div>
            </div>
        </div>

        <div>
            <div class="flex justify-between items-center mb-1.5">
                <span class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">Progreso</span>
                <span class="text-[10px] font-bold {{ $enPositivo ? 'text-emerald-600' : 'text-amber-500' }}">
                    {{ $recuperado }}% recuperado
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full transition-all {{ $enPositivo ? 'bg-emerald-400' : 'bg-amber-400' }}"
                     style="width: {{ $recuperado }}%">
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="hidden md:block overflow-x-auto">
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
    </table>
</div>
