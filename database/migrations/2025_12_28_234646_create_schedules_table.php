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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Data de exibição
            $table->time('start_time'); // Horário: 06:30
            $table->integer('duration'); // Minutos
            
            // Dados específicos da edição
            $table->string('custom_info')->nullable(); // O campo "ID" (AGROBL1...)
            $table->boolean('status_mago')->default(false); // Chegou no Mago?
            $table->boolean('status_verification')->default(false); // Foi conferido?
            $table->text('notes')->nullable(); // Observações

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
