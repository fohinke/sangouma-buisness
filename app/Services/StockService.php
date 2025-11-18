<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service Stock: centralise l'augmentation/diminution des stocks.
 */
class StockService
{
    /**
     * Incrémente le stock d'un produit.
     */
    public function increaseStock(Product $product, int $qty, string $reason = 'reception'): void
    {
        if ($qty < 0) {
            throw new InvalidArgumentException('La quantité doit être positive.');
        }

        DB::transaction(function () use ($product, $qty) {
            $product->increment('stock', $qty);
        });
    }

    /**
     * Décrémente le stock d'un produit (empêche stock négatif).
     */
    public function decreaseStock(Product $product, int $qty, string $reason = 'delivery'): void
    {
        if ($qty < 0) {
            throw new InvalidArgumentException('La quantité doit être positive.');
        }

        DB::transaction(function () use ($product, $qty) {
            $product->refresh();
            if ($product->stock < $qty) {
                throw new InvalidArgumentException('Stock insuffisant pour la livraison.');
            }
            $product->decrement('stock', $qty);
        });
    }
}

