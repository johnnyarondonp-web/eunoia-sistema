<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleItem extends Model
{
    use HasFactory;

protected $fillable = ['sale_id', 'product_id', 'expense_id', 'quantity', 'price_at_sale', 'profit'];

    /**
     * El detalle pertenece a una venta principal
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * El detalle pertenece a un producto específico
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}