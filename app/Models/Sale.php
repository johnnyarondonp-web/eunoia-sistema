<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_usd', 
        'bcv_rate', 
        'total_bs',
        'user_id',
        'cancelled_at',
        'cancel_reason',
    ];

    /**
     * Una venta tiene muchos productos vendidos (detalles)
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}