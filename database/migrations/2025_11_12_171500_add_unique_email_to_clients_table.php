<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Unicité de l'email parmi les enregistrements non supprimés
            $table->unique(['email', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_email_deleted_at_unique');
        });
    }
};

