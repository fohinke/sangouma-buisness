<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Sale model.
 */
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'code', 'status', 'sold_at', 'delivered_at', 'delivery_status', 'carrier', 'total_ht', 'total_ttc', 'notes',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'delivered_at' => 'datetime',
        'total_ht' => 'float',
        'total_ttc' => 'float',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}



