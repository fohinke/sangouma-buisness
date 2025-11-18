<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Produit.
 *
 * Champs: name, sku, purchase_price, sale_price, stock, min_stock, supplier_id
 * Relations: supplier, purchaseOrderItems, saleItems
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'purchase_price', 'sale_price', 'stock', 'min_stock', 'supplier_id', 'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'sale_price' => 'float',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
