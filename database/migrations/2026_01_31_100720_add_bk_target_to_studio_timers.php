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
        Schema::table('studio_timers', function (Blueprint $table) {
            // Horário alvo do retorno do Comercial
            $table->dateTime('bk_target_time')->nullable()->after('target_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('studio_timers', function (Blueprint $table) {
            $table->dropColumn('bk_target_time');
        });
    }
};
