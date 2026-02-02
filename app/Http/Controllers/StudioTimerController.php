<?php

namespace App\Http\Controllers;

use App\Models\StudioTimer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudioTimerController extends Controller
{
    // Tela do Operador (Controle)
    public function operator()
    {
        $timer = StudioTimer::find(1);
        return view('timers.operator', compact('timer'));
    }

    // Tela da Produção (Visualização)
    public function viewer()
    {
        return view('timers.viewer');
    }

    // API de Sincronização (O JS vai ler isso a cada 2 segundos)
    public function status()
    {
        $timer = StudioTimer::find(1);
        
        return response()->json([
            // Precisão Atômica: Mandamos o timestamp exato do servidor agora
            'server_time' => Carbon::now()->getPreciseTimestamp(3), 
            'target_time' => $timer->target_time ? Carbon::parse($timer->target_time)->getPreciseTimestamp(3) : null,
            'bk_target_time' => $timer->bk_target_time ? Carbon::parse($timer->bk_target_time)->getPreciseTimestamp(3) : null,
            'mode_label'  => $timer->mode_label,
            'status_color'=> $timer->status_color,
            // Dados do Cronômetro Progressivo
            'stopwatch' => [
                'status' => $timer->stopwatch_status,
                'started_at' => $timer->stopwatch_started_at ? Carbon::parse($timer->stopwatch_started_at)->getPreciseTimestamp(3) : null,
                'accumulated' => $timer->stopwatch_accumulated_seconds,
            ]
        ]);
    }

    // Atualiza a Regressiva (chamado via AJAX pelo Operador)
    public function updateRegressive(Request $request)
    {
        $timer = StudioTimer::find(1);
        
        // Se o operador mandar uma hora (ex: 08:00), montamos o objeto DateTime para hoje
        if ($request->target_hour) {
            $target = Carbon::createFromFormat('H:i:s', $request->target_hour);
            // Se a hora já passou, assume que é para amanhã (comum em viradas de turno)
            if ($target->isPast()) { $target->addDay(); }
            $timer->target_time = $target;
        } else {
            $timer->target_time = null; // Parar/Zerar
        }

        $timer->mode_label = $request->mode_label ?? 'AGUARDANDO';
        $timer->save();

        return response()->json(['success' => true]);
    }

    // Controla o Cronômetro Progressivo (Stopwatch)
    public function updateStopwatch(Request $request)
    {
        $timer = StudioTimer::find(1);
        $action = $request->action; // 'start', 'pause', 'reset'

        if ($action === 'start') {
            if ($timer->stopwatch_status !== 'running') {
                $timer->stopwatch_started_at = Carbon::now();
                $timer->stopwatch_status = 'running';
            }
        } 
        elseif ($action === 'pause') {
            if ($timer->stopwatch_status === 'running') {
                // Calcula quanto tempo passou desde o start até agora
                $start = Carbon::parse($timer->stopwatch_started_at);
                $diffInSeconds = $start->diffInSeconds(Carbon::now());
                
                // Adiciona ao acumulado e limpa o start
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
        return response()->json(['success' => true]);
    }

    // --- BK ---
    public function updateBk(Request $request)
    {
        $timer = StudioTimer::find(1);
        
        if ($request->bk_hour) {
            // Cria a data de hoje com o horário fornecido
            $target = Carbon::createFromFormat('H:i:s', $request->bk_hour);
            
            // Lógica de virada de dia (se for 00:05 e agora é 23:50, é dia seguinte)
            if ($target->isPast() && $target->diffInHours(Carbon::now()) > 12) {
                 $target->addDay(); 
            }
            
            $timer->bk_target_time = $target;
        } else {
            $timer->bk_target_time = null; // ZERAR
        }

        $timer->save();
        return response()->json(['success' => true]);
    }
}