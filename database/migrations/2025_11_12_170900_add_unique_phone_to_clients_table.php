<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Unicité du téléphone parmi les enregistrements non supprimés
            $table->unique(['phone', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_phone_deleted_at_unique');
        });
    }
};

