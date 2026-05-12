<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;

class LotDeductionService
{
    /**
     * Descuenta unidades de los lotes FIFO del producto y crea los SaleItems.
     * Debe llamarse dentro de una transacción activa — este método no abre ni cierra la suya.
     *
     * @throws \RuntimeException si hay inconsistencia de stock (stock validado pero lotes insuficientes)
     */
    public function deduct(Product $product, int $quantity, Sale $sale, float $priceAtSale): void
    {
        $remaining = $quantity;

        // Lotes con stock disponible, del más antiguo al más nuevo (FIFO).
        // lockForUpdate previene condiciones de carrera si hay requests concurrentes.
        $lots = Expense::where('product_id', $product->id)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $lotesUsados = [];

        foreach ($lots as $lot) {
            if ($remaining <= 0) break;

            $take = min($lot->remaining_quantity, $remaining);
            $lot->decrement('remaining_quantity', $take);
            $remaining -= $take;

            $lotesUsados[] = ['lot' => $lot, 'quantity' => $take];
        }

        // Si remaining > 0 aquí, products.stock y expenses.remaining_quantity están
        // desincronizados. El controlador ya validó stock antes de llamar este método,
        // así que esto no debería ocurrir en condiciones normales.
        if ($remaining > 0) {
            throw new \RuntimeException(
                "Stock inconsistente en producto #{$product->id}: {$remaining} unidades sin lote asignado."
            );
        }

        // Un SaleItem por lote para preservar trazabilidad de costos por lote.
        foreach ($lotesUsados as $usado) {
            $lot      = $usado['lot'];
            $qty      = $usado['quantity'];
            $unitCost = $lot->cost_usd / max($lot->quantity, 1);
            $profit   = round(($priceAtSale - $unitCost) * $qty, 2);

            $sale->items()->create([
                'product_id'    => $product->id,
                'expense_id'    => $lot->id,
                'quantity'      => $qty,
                'price_at_sale' => $priceAtSale,
                'profit'        => $profit,
            ]);
        }
    }
}
