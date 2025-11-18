<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Compteur séquentiel par clé/période (pour numérotation).
 */
class Counter extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'key', 'period', 'value',
    ];
}

