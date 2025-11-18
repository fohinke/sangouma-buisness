<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // Ajoute payable_type (string) + payable_id (unsignedBigInteger) + index composite
            $table->morphs('payable');
            $table->decimal('amount', 12, 2);
            $table->timestamp('paid_at');
            $table->string('method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
