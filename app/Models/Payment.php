<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Paiement polymorphe (achats et ventes).
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_type', 'payable_id', 'amount', 'paid_at', 'method', 'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}

