<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'name', 
        'category', 
        'price', 
        'image_path', 
        'stock',
        'unregistered_stock' // <--- ¡ESTA ES LA LÍNEA QUE FALTABA!
    ];

    
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

  
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Relación con los gastos/costos registrados.
     * Me sirve para saber cuántas veces he registrado lotes de este producto.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}