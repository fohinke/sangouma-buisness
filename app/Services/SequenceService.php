<?php

namespace App\Services;

use App\Models\Counter;
use Illuminate\Support\Facades\DB;

/**
 * Gère la génération de numéros séquentiels thread-safe.
 */
class SequenceService
{
    /**
     * Retourne le prochain numéro formaté pour une clé (ex. INV/PO/BL).
     * Exemple de format: INV-YYYYMM-####
     */
    public function next(string $key, ?string $period = null, int $padding = 4): string
    {
        $period = $period ?: now()->format('Ym');

        return DB::transaction(function () use ($key, $period, $padding) {
            $counter = Counter::lockForUpdate()->firstOrCreate([
                'key' => $key,
                'period' => $period,
            ], [
                'value' => 0,
            ]);

            $counter->value++;
            $counter->save();

            return sprintf('%s-%s-%0'.$padding.'d', $key, $period, $counter->value);
        });
    }
}

