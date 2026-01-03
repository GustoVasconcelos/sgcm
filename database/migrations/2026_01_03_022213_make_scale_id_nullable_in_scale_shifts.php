<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scale_shifts', function (Blueprint $table) {
            // Permite que o campo scale_id seja nulo
            $table->foreignId('scale_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scale_shifts', function (Blueprint $table) {
            // Volta a ser obrigatório (caso precise reverter)
            $table->foreignId('scale_id')->nullable(false)->change();
        });
    }
};
