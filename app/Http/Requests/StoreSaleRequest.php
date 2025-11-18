<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné est invalide.',
            'items.required' => 'Veuillez ajouter au moins un article.',
            'items.array' => 'Le format des articles est invalide.',
            'items.min' => 'Veuillez ajouter au moins un article.',
            'items.*.product_id.required' => 'Le produit est obligatoire.',
            'items.*.product_id.exists' => 'Le produit sélectionné est invalide.',
            'items.*.qty.required' => 'La quantité est obligatoire.',
            'items.*.qty.integer' => 'La quantité doit être un entier.',
            'items.*.qty.min' => 'La quantité doit être au moins 1.',
            'items.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
            'items.*.unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'items.*.unit_price.min' => 'Le prix unitaire doit être au moins 0.',
        ];
    }

    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'items' => 'articles',
            'items.*.product_id' => 'produit',
            'items.*.qty' => 'quantité',
            'items.*.unit_price' => 'prix unitaire',
        ];
    }

    /**
     * Validation supplémentaire: empêche de vendre plus que le stock disponible.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $items = (array) $this->input('items', []);
            if (!count($items)) {
                return;
            }
            $requested = [];
            foreach ($items as $row) {
                if (!isset($row['product_id'])) continue;
                $pid = (int) $row['product_id'];
                $qty = max(0, (int) ($row['qty'] ?? 0));
                $requested[$pid] = ($requested[$pid] ?? 0) + $qty;
            }
            if (!count($requested)) {
                return;
            }
            $products = Product::whereIn('id', array_keys($requested))->get(['id','name','stock']);
            foreach ($products as $p) {
                if ($requested[$p->id] > $p->stock) {
                    $v->errors()->add('items', "Le produit {$p->name} n'a que {$p->stock} en stock.");
                }
            }
        });
    }
}
