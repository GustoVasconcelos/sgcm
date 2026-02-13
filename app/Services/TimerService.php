<?php

namespace App\Services;

use App\Models\StudioTimer;
use Carbon\Carbon;

class TimerService
{
    /**
     * Retorna o timer atual (Singleton Lógico).
     * Cria se não existir.
     */
    public function getTimer(): StudioTimer
    {
        $timer = StudioTimer::first();

        if (!$timer) {
            $timer = StudioTimer::create([
                'id' => 1, // Tenta manter ID 1 por compatibilidade, mas não é estrito
                'mode_label' => 'AGUARDANDO',
                'status_color' => 'text-white',
                'stopwatch_status' => 'stopped',
                'stopwatch_accumulated_seconds' => 0
            ]);
        }

        return $timer;
    }

    /**
     * Atualiza o alvo da contagem regressiva.
     */
    public function updateRegressive(?string $targetHour, ?string $modeLabel): void
    {
        $timer = $this->getTimer();
        
        if ($targetHour) {
            $target = Carbon::createFromFormat('H:i:s', $targetHour);
            
            // Lógica de virada de dia: se a hora alvo já passou hoje, assume amanhã.
            if ($target->isPast()) { 
                $target->addDay(); 
            }
            
            $timer->target_time = $target;
        } else {
            $timer->target_time = null; // ZERAR
        }

        $timer->mode_label = $modeLabel ?? 'AGUARDANDO';
        $timer->save();
    }

    /**
     * Controla o cronômetro progressivo.
     * Actions: start, pause, reset
     */
    public function updateStopwatch(string $action): void
    {
        $timer = $this->getTimer();

        if ($action === 'start') {
            if ($timer->stopwatch_status !== 'running') {
                $timer->stopwatch_started_at = Carbon::now();
                $timer->stopwatch_status = 'running';
            }
        } 
        elseif ($action === 'pause') {
            if ($timer->stopwatch_status === 'running') {
                $start = Carbon::parse($timer->stopwatch_started_at);
                $diffInSeconds = $start->diffInSeconds(Carbon::now());
                
                $timer->stopwatch_accumulated_seconds += $diffInSeconds;
                $timer->stopwatch_started_at = null;
                $timer->stopwatch_status = 'paused';
            }
        } 
        elseif ($action === 'reset') {
            $timer->stopwatch_started_at = null;
            $timer->stopwatch_accumulated_seconds = 0;
            $timer->stopwatch_status = 'stopped';
        }

        $timer->save();
    }

    /**
     * Atualiza o horário do Break (BK).
     */
    public function updateBk(?string $bkHour): void
    {
        $timer = $this->getTimer();
        
        if ($bkHour) {
            $target = Carbon::createFromFormat('H:i:s', $bkHour);
            
            // Lógica de virada de dia para BK (se > 12h de diferença, provavelmente é dia seguinte)
            // Ex: BK 00:05 e agora é 23:50
            if ($target->isPast() && $target->diffInHours(Carbon::now()) > 12) {
                 $target->addDay(); 
            }
            
            $timer->bk_target_time = $target;
        } else {
            $timer->bk_target_time = null;
        }

        $timer->save();
    }
}
