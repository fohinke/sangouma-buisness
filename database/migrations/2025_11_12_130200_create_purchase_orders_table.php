<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->enum('status', ['en_attente','partiellement_payee','payee','livree'])->default('en_attente');
            $table->timestamp('ordered_at');
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};

