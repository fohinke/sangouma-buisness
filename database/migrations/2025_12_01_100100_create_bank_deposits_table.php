<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable()->unique();
            $table->string('bank_name');
            $table->string('account_number')->nullable();
            $table->decimal('amount', 12, 2);
            $table->timestamp('deposited_at')->useCurrent();
            $table->string('method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['bank_name', 'deposited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
    }
};
