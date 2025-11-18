<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('period');
            $table->unsignedInteger('value');
            $table->unique(['key','period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counters');
    }
};

