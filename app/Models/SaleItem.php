<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ligne de vente.
 */
class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'product_id', 'qty', 'unit_price', 'delivered_qty', 'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'delivered_qty' => 'integer',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

