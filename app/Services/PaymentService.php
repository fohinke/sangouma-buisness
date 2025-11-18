<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service Paiement: enregistrement paiements et mise à jour statuts.
 */
class PaymentService
{
    /**
     * Ajoute un paiement à un payable (Sale ou PurchaseOrder).
     */
    public function addPayment(Model $payable, float $amount, \Carbon\Carbon $paidAt, string $method = 'cash', ?string $notes = null): Payment
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Le montant doit être > 0');
        }

        return DB::transaction(function () use ($payable, $amount, $paidAt, $method, $notes) {
            $total = (float) ($payable->total_ttc ?? $payable->total_ht ?? 0);
            $paid = (float) $payable->payments()->sum('amount');
            $due = max(0.0, $total - $paid);
            if ($amount > $due + 1e-6) {
                throw new InvalidArgumentException('Le montant dépasse le reste à payer. Reste: '.number_format($due, 2, ',', ' '));
            }

            $payment = $payable->payments()->create([
                'amount' => $amount,
                'paid_at' => $paidAt,
                'method' => $method,
                'notes' => $notes,
            ]);

            // Recalcule le statut de paiement après enregistrement
            $paid = (float) $payable->payments()->sum('amount');

            if ($paid <= 0) {
                $status = 'en_attente';
            } elseif ($paid + 0.00001 < $total) {
                $status = 'partiellement_payee';
            } else {
                $status = 'payee';
            }

            $payable->forceFill(['status' => $status])->save();

            return $payment;
        });
    }
}
