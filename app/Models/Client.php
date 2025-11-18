<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Client.
 */
class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'notes',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}

