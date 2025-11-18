<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

/**
 * Service Facturation: génère les PDF (factures & bons de livraison).
 */
class InvoiceService
{
    public function generatePurchaseInvoice(PurchaseOrder $order)
    {
        $html = View::make('pdf.purchase_invoice', compact('order'))->render();
        return Pdf::loadHTML($html)->setPaper('a4');
    }

    public function generateSaleInvoice(Sale $sale)
    {
        $html = View::make('pdf.sale_invoice', compact('sale'))->render();
        return Pdf::loadHTML($html)->setPaper('a4');
    }

    public function generateDeliveryNote(Sale $sale)
    {
        $html = View::make('pdf.delivery_note', compact('sale'))->render();
        return Pdf::loadHTML($html)->setPaper('a4');
    }
}

