<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_timers', function (Blueprint $table) {
            $table->id();
            
            // REGRESSIVA (Do programa/bloco)
            // Se estiver NULL, a regressiva está desligada/zerada
            $table->dateTime('target_time')->nullable(); 
            
            // Texto de apoio (ex: "AO VIVO", "ENCERRAMENTO", "BLOCO 1")
            $table->string('mode_label')->nullable(); 
            
            // Cor do alerta (ex: normal, warning, critical) - opcional, bom pra TV
            $table->string('status_color')->default('normal'); 

            // CRONÔMETRO PROGRESSIVO (Para links/matérias)
            // Hora que deu o play. Se NULL, está parado/zerado.
            $table->dateTime('stopwatch_started_at')->nullable();
            
            // Tempo acumulado em segundos (caso pause e continue)
            $table->integer('stopwatch_accumulated_seconds')->default(0);
            
            // Estado do cronômetro: 'running', 'paused', 'stopped'
            $table->enum('stopwatch_status', ['running', 'paused', 'stopped'])->default('stopped');

            $table->timestamps();
        });

        // Insere a linha inicial (ID 1) para não precisarmos criar no controller
        DB::table('studio_timers')->insert([
            'mode_label' => 'AGUARDANDO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_timers');
    }
};