<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->enum('status', ['en_attente','partiellement_payee','payee','livree'])->default('en_attente');
            $table->timestamp('sold_at');
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('carrier')->nullable();
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

