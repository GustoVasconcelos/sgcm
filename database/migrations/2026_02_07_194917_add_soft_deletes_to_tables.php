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
        // Users já tem deleted_at, pular
        // Schema::table('users', function (Blueprint $table) {
        //     $table->softDeletes();
        // });

        Schema::table('vacations', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('scale_shifts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('scale_shifts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
