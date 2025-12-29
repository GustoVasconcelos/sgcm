<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Program;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // 1. Define a data base (Sábado). Se não vier na URL, pega o próximo sábado ou hoje.
        // Lógica: Se hoje é Sábado ou Domingo, mostra este fim de semana.
        // Se é dia de semana, mostra o próximo.
        $today = Carbon::now();
        
        if ($request->has('date')) {
            $baseDate = Carbon::parse($request->date);
        } else {
            if ($today->isWeekend()) {
                $baseDate = $today->isSaturday() ? $today : $today->copy()->subDay();
            } else {
                $baseDate = $today->copy()->next(Carbon::SATURDAY);
            }
        }

        $saturday = $baseDate->copy()->startOfDay(); // Sábado
        $sunday = $baseDate->copy()->addDay()->startOfDay(); // Domingo

        // 2. Busca os dados
        $schedules = Schedule::with('program')
            ->whereBetween('date', [$saturday->format('Y-m-d'), $sunday->format('Y-m-d')])
            ->orderBy('start_time')
            ->get();

        // Separa para as abas
        $saturdayGrade = $schedules->where('date', $saturday);
        $sundayGrade = $schedules->where('date', $sunday);

        // Lista de Programas para o Modal de Novo Item
        $programs = Program::orderBy('name')->get();

        return view('schedules.index', compact('saturday', 'sunday', 'saturdayGrade', 'sundayGrade', 'programs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'custom_info' => 'nullable|string',
            'duration' => 'required|integer',
            'notes' => 'nullable|string'
        ]);

        Schedule::create($data);
        return back()->with('success', 'Programa agendado!');
    }
    
    // Método Ajax rápido para alternar status (Mago/Verificação)
    public function toggleStatus($id, $type)
    {
        $schedule = Schedule::findOrFail($id);
        
        if ($type == 'mago') $schedule->status_mago = !$schedule->status_mago;
        if ($type == 'verification') $schedule->status_verification = !$schedule->status_verification;
        
        $schedule->save();
        
        return response()->json(['success' => true, 'new_status' => $type == 'mago' ? $schedule->status_mago : $schedule->status_verification]);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Removido da grade.');
    }

    // A MÁGICA: Clonar fim de semana anterior
    public function clone(Request $request)
    {
        $targetSaturday = Carbon::parse($request->target_date);
        $targetSunday = $targetSaturday->copy()->addDay();

        // Verifica se já tem dados pra não duplicar sem querer
        if (Schedule::where('date', $targetSaturday)->exists() || Schedule::where('date', $targetSunday)->exists()) {
             return back()->with('error', 'Já existe grade cadastrada para esta data! Limpe antes de clonar.');
        }

        // Busca o ÚLTIMO fim de semana que teve dados antes desse
        $lastEntry = Schedule::where('date', '<', $targetSaturday)->orderBy('date', 'desc')->first();

        if (!$lastEntry) {
            return back()->with('error', 'Nenhuma grade anterior encontrada para clonar.');
        }

        // Define as datas de origem (Sábado e Domingo passados)
        $sourceDate = Carbon::parse($lastEntry->date);
        $sourceSaturday = $sourceDate->isSaturday() ? $sourceDate : $sourceDate->copy()->subDay();
        $sourceSunday = $sourceSaturday->copy()->addDay();

        // Busca tudo da origem
        $sourceSchedules = Schedule::whereBetween('date', [$sourceSaturday->format('Y-m-d'), $sourceSunday->format('Y-m-d')])->get();

        foreach ($sourceSchedules as $item) {
            $newDate = $item->date == $sourceSaturday ? $targetSaturday : $targetSunday;

            Schedule::create([
                'program_id' => $item->program_id,
                'date' => $newDate,
                'start_time' => $item->start_time,
                'duration' => $item->duration,
                'custom_info' => $item->custom_info, // Copia os blocos (como rascunho)
                'notes' => $item->notes,
                'status_mago' => false, // Reseta
                'status_verification' => false, // Reseta
            ]);
        }

        return back()->with('success', 'Grade clonada com sucesso do dia ' . $sourceSaturday->format('d/m'));
    }
}