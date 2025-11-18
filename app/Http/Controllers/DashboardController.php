<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $salesMonth = (float) Sale::whereMonth('sold_at', now()->month)->sum('total_ttc');
        $purchasesMonth = (float) PurchaseOrder::whereMonth('ordered_at', now()->month)->sum('total_ttc');
        $paymentsReceived = (float) Payment::where('payable_type', Sale::class)->sum('amount');
        $lowStock = (int) Product::whereColumn('stock', '<=', 'min_stock')->count();

        $salesSeries = Sale::selectRaw('DATE(sold_at) as d, SUM(total_ttc) as t')
            ->where('sold_at', '>=', now()->subDays(30))
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->selectRaw('products.name as n, SUM(sale_items.qty) as q')
            ->groupBy('products.name')
            ->orderByDesc('q')
            ->limit(5)
            ->get();

        $kpis = [
            'sales_month' => $salesMonth,
            'purchases_month' => $purchasesMonth,
            'payments_received' => $paymentsReceived,
            'low_stock_count' => $lowStock,
        ];

        return view('dashboard', compact('kpis', 'salesSeries', 'topProducts'));
    }
}

