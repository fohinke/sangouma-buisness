<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->timestamp('credited_at')->useCurrent();
            $table->string('method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'credited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_credits');
    }
};
