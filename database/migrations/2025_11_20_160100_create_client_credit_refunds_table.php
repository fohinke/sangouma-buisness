<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_credit_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_credit_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamp('refunded_at')->nullable();
            $table->string('method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['client_credit_id', 'refunded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_credit_refunds');
    }
};
