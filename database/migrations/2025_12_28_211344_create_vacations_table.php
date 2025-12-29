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
        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // O tipo define a regra: '30_dias', '15_15', '10_10_10', '20_venda'
            $table->string('mode'); 
            
            // Período 1 (Sempre obrigatório)
            $table->date('period_1_start');
            $table->date('period_1_end');

            // Período 2 (Pode ser nulo se for férias de 30 dias)
            $table->date('period_2_start')->nullable();
            $table->date('period_2_end')->nullable();

            // Período 3 (Só usado no modo 10/10/10)
            $table->date('period_3_start')->nullable();
            $table->date('period_3_end')->nullable();
            
            // Um status para controle (opcional, mas recomendado)
            $table->string('status')->default('pendente'); // pendente, aprovado, recusado

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacations');
    }
};
