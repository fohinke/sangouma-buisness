<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return view('products.index');
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->pluck('name','id');
        return view('products.create', compact('suppliers'));
    }

    public function store(StoreProductRequest $request)
    {
        Product::create($request->validated());
        return redirect()->route('products.index')->with('success', 'Produit créé.');
    }

    public function show(Product $product)
    {
        $product->load('supplier');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $suppliers = Supplier::orderBy('name')->pluck('name','id');
        return view('products.edit', compact('product','suppliers'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        return redirect()->route('products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produit supprimé.');
    }
}
