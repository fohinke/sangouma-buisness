<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $suppliers = Supplier::query()
            ->when($q, function ($builder) use ($q) {
                $builder->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")->orWhere('phone', 'like', "%$q%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'q'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        Supplier::create($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Fournisseur créé.');
    }

    public function show(Supplier $supplier)
    {
        $orders = $supplier->purchaseOrders()
            ->withSum('payments as paid_amount', 'amount')
            ->latest('ordered_at')
            ->paginate(10);

        return view('suppliers.show', compact('supplier', 'orders'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Fournisseur supprimé.');
    }
}

