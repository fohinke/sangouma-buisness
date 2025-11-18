<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ligne de commande fournisseur.
 */
class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'product_id', 'qty', 'unit_price', 'received_qty', 'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'received_qty' => 'integer',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

