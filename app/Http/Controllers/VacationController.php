<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $vacations = Vacation::with('user')
            ->where('year', $year)
            ->join('users', 'users.id', '=', 'vacations.user_id')
            ->orderBy('users.name')
            ->select('vacations.*')
            ->get();

        $currentYear = date('Y');
        $years = range($currentYear + 1, $currentYear - 5);

        return view('vacations.index', compact('vacations', 'year', 'years'));
    }

    public function create()
    {
        return view('vacations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2099',
            'mode' => 'required', // ex: 30 dias, 20 dias, etc
            'period_1_start' => 'required|date',
            'period_1_end' => 'required|date|after_or_equal:period_1_start',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['status'] = 'aprovado';

        $vacation = Vacation::create($data);

        // --- LOG: Cadastrar ---
        // Ajustei as variáveis para bater com a validação (period_1_start)
        ActionLog::register('Férias', 'Cadastrar Férias', [
            'periodo' => date('d/m/Y', strtotime($request->period_1_start)) . ' a ' . date('d/m/Y', strtotime($request->period_1_end)),
            'modalidade' => $request->mode,
            'ano_referencia' => $request->year
        ]);
        // ----------------------
        
        return redirect()->route('vacations.index', ['year' => $request->year])
                         ->with('success', 'Férias cadastradas!');
    }

    public function edit(Vacation $vacation)
    {
        if (Auth::user()->profile !== 'admin' && Auth::id() !== $vacation->user_id) {
            return redirect()->route('vacations.index')->with('error', 'Sem permissão.');
        }

        return view('vacations.edit', compact('vacation'));
    }

    public function update(Request $request, Vacation $vacation)
    {
        if (Auth::user()->profile !== 'admin' && Auth::id() !== $vacation->user_id) {
            abort(403);
        }

        // 1. Preenche os dados novos no modelo (sem salvar ainda)
        $vacation->fill($request->all());

        // 2. Verifica se algo mudou (Dirty Checking)
        if ($vacation->isDirty()) {
            
            // Pega o que mudou
            $changes = $vacation->getDirty();
            
            // Pega o nome do dono das férias para o log
            $ownerName = $vacation->user->name ?? 'Usuário ID ' . $vacation->user_id;

            // Formata datas para ficar bonito no log (opcional, mas recomendado)
            $formattedChanges = [];
            foreach ($changes as $key => $value) {
                if (str_contains($key, 'date') || str_contains($key, 'start') || str_contains($key, 'end')) {
                    $formattedChanges[$key] = date('d/m/Y', strtotime($value));
                } else {
                    $formattedChanges[$key] = $value;
                }
            }

            // --- LOG: Alterar Férias ---
            ActionLog::register('Férias', 'Alterar Férias', [
                'funcionario' => $ownerName,
                'alteracoes' => $formattedChanges
            ]);
            // ---------------------------

            $vacation->save(); // Salva efetivamente
            
            return redirect()->route('vacations.index', ['year' => $vacation->year])
                             ->with('success', 'Férias atualizadas!');
        }

        // Se não houve mudança, apenas redireciona
        return redirect()->route('vacations.index', ['year' => $vacation->year])
                         ->with('info', 'Nenhuma alteração realizada.');
    }

    public function destroy(Vacation $vacation)
    {
        if (Auth::user()->profile !== 'admin' && Auth::id() !== $vacation->user_id) {
            abort(403);
        }
        
        // --- LOG: Excluir Férias (Captura dados ANTES de deletar) ---
        $ownerName = $vacation->user->name ?? 'Desconhecido';
        
        // Monta string do período principal
        $periodo = 'N/D';
        if ($vacation->period_1_start && $vacation->period_1_end) {
            $periodo = date('d/m/Y', strtotime($vacation->period_1_start)) . ' a ' . date('d/m/Y', strtotime($vacation->period_1_end));
        }

        ActionLog::register('Férias', 'Excluir Férias', [
            'funcionario' => $ownerName,
            'periodo_excluido' => $periodo,
            'ano_referencia' => $vacation->year
        ]);
        // -----------------------------------------------------------

        $vacation->delete();
        return back()->with('success', 'Registro excluído.');
    }
}