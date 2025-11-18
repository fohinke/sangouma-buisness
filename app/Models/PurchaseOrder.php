<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Commande Fournisseur.
 */
class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'code', 'status', 'ordered_at', 'delivered_at', 'total_ht', 'total_ttc', 'notes',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'delivered_at' => 'datetime',
        'total_ht' => 'float',
        'total_ttc' => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}

