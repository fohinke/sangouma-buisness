<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Client;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::factory()->count(10)->create();

        // Produits simples
        $suppliers = Supplier::all();
        foreach (range(1, 30) as $i) {
            Product::create([
                'name' => 'Produit '.$i,
                'sku' => 'SKU'.str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'purchase_price' => rand(100, 1000) / 10,
                'sale_price' => rand(120, 1500) / 10,
                'stock' => rand(0, 50),
                'min_stock' => rand(0, 10),
                'supplier_id' => $suppliers->random()->id,
            ]);
        }

        foreach (range(1, 10) as $i) {
            Client::create([
                'name' => 'Client '.$i,
                'phone' => '060000000'.$i,
                'email' => 'client'.$i.'@example.com',
                'address' => 'Adresse '.$i,
            ]);
        }
    }
}

