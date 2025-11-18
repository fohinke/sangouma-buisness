<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => [
                'required','string','max:100',
                Rule::unique('products','sku')->ignore($this->product?->id),
            ],
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'sku.required' => 'La référence est obligatoire.',
            'sku.unique' => 'Cette référence est déjà utilisée.',
            'sku.max' => 'La référence ne doit pas dépasser :max caractères.',
            'purchase_price.required' => "Le prix d'achat est obligatoire.",
            'purchase_price.numeric' => "Le prix d'achat doit être un nombre.",
            'purchase_price.min' => "Le prix d'achat doit être au moins 0.",
            'sale_price.required' => 'Le prix de vente est obligatoire.',
            'sale_price.numeric' => 'Le prix de vente doit être un nombre.',
            'sale_price.min' => 'Le prix de vente doit être au moins 0.',
            'stock.required' => 'Le stock est obligatoire.',
            'stock.integer' => 'Le stock doit être un entier.',
            'stock.min' => 'Le stock doit être au moins 0.',
            'min_stock.integer' => 'Le stock minimum doit être un entier.',
            'min_stock.min' => 'Le stock minimum doit être au moins 0.',
            'supplier_id.required' => 'Le fournisseur est obligatoire.',
            'supplier_id.exists' => 'Le fournisseur sélectionné est invalide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'sku' => 'référence',
            'purchase_price' => "prix d'achat",
            'sale_price' => 'prix de vente',
            'stock' => 'stock',
            'min_stock' => 'stock minimum',
            'supplier_id' => 'fournisseur',
        ];
    }
}
