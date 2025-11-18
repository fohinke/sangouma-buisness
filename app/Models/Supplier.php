<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Fournisseur.
 *
 * Champs: name, phone, email, address, notes, status
 * Relations: products, purchaseOrders
 */
class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'notes', 'status',
    ];

    /**
     * Produits liés au fournisseur.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Commandes fournisseurs associées.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}

