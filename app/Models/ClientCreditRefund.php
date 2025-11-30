<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCreditRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_credit_id',
        'amount',
        'refunded_at',
        'method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'refunded_at' => 'datetime',
    ];

    public function credit()
    {
        return $this->belongsTo(ClientCredit::class, 'client_credit_id');
    }
}
