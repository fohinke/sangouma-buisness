<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Depot bancaire.
 */
class BankDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'bank_name',
        'account_number',
        'amount',
        'deposited_at',
        'method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'deposited_at' => 'datetime',
    ];
}

