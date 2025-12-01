<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'amount',
        'remaining_amount',
        'credited_at',
        'method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'remaining_amount' => 'float',
        'credited_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function refunds()
    {
        return $this->hasMany(ClientCreditRefund::class);
    }
}
