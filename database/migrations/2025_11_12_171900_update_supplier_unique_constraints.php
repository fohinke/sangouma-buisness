<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Supprime l'unique simple sur email si présent, puis ajoute des index uniques composés avec deleted_at
            try {
                $table->dropUnique('suppliers_email_unique');
            } catch (\Throwable $e) {
                // index peut ne pas exister (selon l'environnement)
            }
            $table->unique(['email','deleted_at']);
            $table->unique(['phone','deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            try { $table->dropUnique('suppliers_email_deleted_at_unique'); } catch (\Throwable $e) {}
            try { $table->dropUnique('suppliers_phone_deleted_at_unique'); } catch (\Throwable $e) {}
            // Recrée l'ancien unique simple sur email
            $table->unique('email');
        });
    }
};

