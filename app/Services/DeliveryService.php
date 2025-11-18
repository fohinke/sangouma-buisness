<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service Livraison/Réception: met à jour quantités reçues/livrées et stock.
 */
class DeliveryService
{
    public function __construct(private readonly StockService $stock)
    {
    }

    /**
     * Réception d'une commande fournisseur (partielle/totale).
     * $items: [ [item_id, qty], ... ]
     */
    public function receivePurchase(PurchaseOrder $order, array $items, ?\Carbon\Carbon $date = null, ?string $by = null): void
    {
        $date = $date ?: now();

        DB::transaction(function () use ($order, $items, $date, $by) {
            foreach ($items as $row) {
                $item = PurchaseOrderItem::lockForUpdate()->findOrFail($row['item_id']);
                $delta = (int) $row['qty'];
                if ($delta < 0) throw new InvalidArgumentException('Quantité invalide');
                $remaining = $item->qty - $item->received_qty;
                if ($delta > $remaining) throw new InvalidArgumentException('Réception dépasse le restant');

                if ($delta > 0) {
                    $item->received_qty += $delta;
                    $item->save();
                    $this->stock->increaseStock($item->product, $delta, 'purchase_reception');
                }
            }

            // Statut livraison
            $allReceived = $order->items()->whereColumn('received_qty', '<', 'qty')->doesntExist();
            if ($allReceived) {
                $order->delivered_at = $date;
                // On conserve le statut de paiement, mais indique livrée
                if ($order->status !== 'payee') {
                    $order->status = 'livree';
                }
                $order->save();
            }
        });
    }

    /**
     * Livraison d'une vente (partielle/totale).
     * $items: [ [item_id, qty], ... ]
     */
    public function deliverSale(Sale $sale, array $items, ?\Carbon\Carbon $date = null, ?string $carrier = null, ?string $notes = null): void
    {
        $date = $date ?: now();

        DB::transaction(function () use ($sale, $items, $date, $carrier) {
            foreach ($items as $row) {
                $item = SaleItem::lockForUpdate()->findOrFail($row['item_id']);
                $delta = (int) $row['qty'];
                if ($delta < 0) throw new InvalidArgumentException('Quantité invalide');
                $remaining = $item->qty - $item->delivered_qty;
                if ($delta > $remaining) throw new InvalidArgumentException('Livraison dépasse le restant');

                if ($delta > 0) {
                    $this->stock->decreaseStock($item->product, $delta, 'sale_delivery');
                    $item->delivered_qty += $delta;
                    $item->save();
                }
            }

            $allDelivered = $sale->items()->whereColumn('delivered_qty', '<', 'qty')->doesntExist();
            if ($allDelivered) {
                $sale->delivery_status = 'livree';
                $sale->delivered_at = $date;
                if ($sale->status !== 'payee') {
                    $sale->status = 'livree';
                }
            } else {
                $sale->delivery_status = 'en_cours';
            }
            if ($carrier) $sale->carrier = $carrier;
            $sale->save();
        });
    }
}

