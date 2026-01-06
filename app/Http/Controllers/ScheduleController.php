<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Program;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // ... (código original inalterado) ...
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

        $saturday = $baseDate->copy()->startOfDay();
        $sunday = $baseDate->copy()->addDay()->startOfDay();

        $schedules = Schedule::with('program')
            ->whereBetween('date', [$saturday->format('Y-m-d'), $sunday->format('Y-m-d')])
            ->orderBy('start_time')
            ->get();

        $saturdayGrade = $schedules->where('date', $saturday);
        $sundayGrade = $schedules->where('date', $sunday);

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

        $schedule = Schedule::create($data);

        // --- LOG: Adicionar Programa ---
        // Buscamos o nome do programa para ficar legível no log
        $programName = $schedule->program ? $schedule->program->name : 'ID ' . $data['program_id'];
        
        ActionLog::register('PGMs FDS', 'Adicionar Programa na Grade', [
            'programa' => $programName,
            'data_exibicao' => date('d/m/Y', strtotime($data['date'])),
            'horario' => $data['start_time']
        ]);
        // ------------------------------

        return back()->with('success', 'Programa agendado!');
    }
    
    public function toggleStatus($id, $type)
    {
        $schedule = Schedule::findOrFail($id);
        
        // Guarda o estado anterior para logar
        $statusLabel = ($type == 'mago') ? 'Servidor (Mago)' : 'Verificação Técnica';
        
        if ($type == 'mago') $schedule->status_mago = !$schedule->status_mago;
        if ($type == 'verification') $schedule->status_verification = !$schedule->status_verification;
        
        $schedule->save();

        // --- LOG: Checagem/Verificação ---
        ActionLog::register('PGMs FDS', 'Checagem de Mídia', [
            'programa' => $schedule->program->name ?? 'Desconhecido',
            'data_exibicao' => $schedule->date->format('d/m/Y'),
            'tipo_checagem' => $statusLabel,
            'novo_status' => ($type == 'mago' ? $schedule->status_mago : $schedule->status_verification) ? 'OK' : 'Pendente'
        ]);
        // --------------------------------
        
        return response()->json(['success' => true, 'new_status' => $type == 'mago' ? $schedule->status_mago : $schedule->status_verification]);
    }

    public function destroy(Schedule $schedule)
    {
        // --- LOG: Excluir Programa (Antes de deletar para pegar os dados) ---
        ActionLog::register('PGMs FDS', 'Excluir Programa da Grade', [
            'programa' => $schedule->program->name ?? 'Desconhecido',
            'data_exibicao' => $schedule->date->format('d/m/Y'),
            'horario_previsto' => $schedule->start_time
        ]);
        // ---------------------------------------------------------------

        $schedule->delete();
        return back()->with('success', 'Removido da grade.');
    }

    public function clone(Request $request)
    {
        $targetSaturday = Carbon::parse($request->target_date);
        $targetSunday = $targetSaturday->copy()->addDay();

        if (Schedule::where('date', $targetSaturday)->exists() || Schedule::where('date', $targetSunday)->exists()) {
             return back()->with('error', 'Já existe grade cadastrada para esta data! Limpe antes de clonar.');
        }

        $lastEntry = Schedule::where('date', '<', $targetSaturday)->orderBy('date', 'desc')->first();

        if (!$lastEntry) {
            return back()->with('error', 'Nenhuma grade anterior encontrada para clonar.');
        }

        $sourceDate = Carbon::parse($lastEntry->date);
        $sourceSaturday = $sourceDate->isSaturday() ? $sourceDate : $sourceDate->copy()->subDay();
        $sourceSunday = $sourceSaturday->copy()->addDay();

        $sourceSchedules = Schedule::whereBetween('date', [$sourceSaturday->format('Y-m-d'), $sourceSunday->format('Y-m-d')])->get();

        foreach ($sourceSchedules as $item) {
            $newDate = $item->date == $sourceSaturday ? $targetSaturday : $targetSunday;

            Schedule::create([
                'program_id' => $item->program_id,
                'date' => $newDate,
                'start_time' => $item->start_time,
                'duration' => $item->duration,
                'custom_info' => $item->custom_info,
                'notes' => $item->notes,
                'status_mago' => false,
                'status_verification' => false,
            ]);
        }

        // --- LOG: Clonar Grade ---
        ActionLog::register('PGMs FDS', 'Clonar Grade Anterior', [
            'de_data' => $sourceSaturday->format('d/m/Y'),
            'para_data' => $targetSaturday->format('d/m/Y'),
            'itens_clonados' => $sourceSchedules->count()
        ]);
        // -------------------------

        return back()->with('success', 'Grade clonada com sucesso do dia ' . $sourceSaturday->format('d/m'));
    }
}