<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('scale_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scale_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained(); // Pode ser nulo no início
            $table->date('date');
            $table->string('name'); // Ex: "06:00 - 12:00" ou "FOLGA"
            $table->integer('order'); // 1, 2, 3, 4, 5 (Para ordenar na tela)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scale_shifts');
    }
};
