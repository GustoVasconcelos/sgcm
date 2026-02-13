<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\TimerService;

class StudioTimerController extends Controller
{
    protected $timerService;

    public function __construct(TimerService $timerService)
    {
        $this->timerService = $timerService;
    }

    // Tela do Operador (Controle)
    public function operator()
    {
        $timer = $this->timerService->getTimer();
        return view('timers.operator', compact('timer'));
    }

    // Tela da Produção (Visualização)
    public function viewer()
    {
        return view('timers.viewer');
    }

    // API de Sincronização
    public function status()
    {
        $timer = $this->timerService->getTimer();
        
        return response()->json([
            'server_time' => Carbon::now()->getPreciseTimestamp(3), 
            'target_time' => $timer->target_time ? Carbon::parse($timer->target_time)->getPreciseTimestamp(3) : null,
            'bk_target_time' => $timer->bk_target_time ? Carbon::parse($timer->bk_target_time)->getPreciseTimestamp(3) : null,
            'mode_label'  => $timer->mode_label,
            'status_color'=> $timer->status_color,
            'stopwatch' => [
                'status' => $timer->stopwatch_status,
                'started_at' => $timer->stopwatch_started_at ? Carbon::parse($timer->stopwatch_started_at)->getPreciseTimestamp(3) : null,
                'accumulated' => $timer->stopwatch_accumulated_seconds,
            ]
        ]);
    }

    // Atualiza a Regressiva
    public function updateRegressive(Request $request)
    {
        $request->validate([
            'target_hour' => 'nullable|date_format:H:i:s',
            'mode_label' => 'nullable|string'
        ]);

        $this->timerService->updateRegressive($request->target_hour, $request->mode_label);

        return response()->json(['success' => true]);
    }

    // Controla o Cronômetro Progressivo
    public function updateStopwatch(Request $request)
    {
        $request->validate(['action' => 'required|in:start,pause,reset']);

        $this->timerService->updateStopwatch($request->action);
        
        return response()->json(['success' => true]);
    }

    // --- BK ---
    public function updateBk(Request $request)
    {
        $request->validate(['bk_hour' => 'nullable|date_format:H:i:s']);

        $this->timerService->updateBk($request->bk_hour);

        return response()->json(['success' => true]);
    }
}