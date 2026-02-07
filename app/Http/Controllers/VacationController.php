<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\ActionLog;
use App\Http\Requests\StoreVacationRequest;
use App\Http\Requests\UpdateVacationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $years = range($currentYear + 1, $currentYear - 2);

        return view('vacations.index', compact('vacations', 'year', 'years'));
    }

    public function create()
    {
        $currentYear = date('Y');
        $allowedYears = range($currentYear, $currentYear + 2);

        return view('vacations.create', compact('allowedYears'));
    }

    public function store(StoreVacationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['status'] = 'aprovado';

        $vacation = Vacation::create($data);

        ActionLog::register('Férias', 'Cadastrar Férias', [
            'periodo' => date('d/m/Y', strtotime($request->period_1_start)) . ' a ' . date('d/m/Y', strtotime($request->period_1_end)),
            'modalidade' => $request->mode,
            'ano_referencia' => $request->year
        ]);
        
        return redirect()->route('vacations.index', ['year' => $request->year])
                         ->with('success', 'Férias cadastradas!');
    }

    public function edit(Vacation $vacation)
    {
        if (!Auth::user()->hasRole('Admin') && Auth::id() !== $vacation->user_id) {
            return redirect()->route('vacations.index')->with('error', 'Sem permissão.');
        }

        $currentYear = date('Y');
        $allowedYears = range($currentYear, $currentYear + 2);

        if (!in_array($vacation->year, $allowedYears)) {
            array_unshift($allowedYears, $vacation->year);
        }

        return view('vacations.edit', compact('vacation', 'allowedYears'));
    }

    public function update(UpdateVacationRequest $request, Vacation $vacation)
    {
        // Autorização já feita no Form Request

        $vacation->fill($request->validated());

        if ($vacation->isDirty()) {
            $changes = $vacation->getDirty();
            $ownerName = $vacation->user->name ?? 'Usuário ID ' . $vacation->user_id;

            $formattedChanges = [];
            foreach ($changes as $key => $value) {
                if (str_contains($key, 'date') || str_contains($key, 'start') || str_contains($key, 'end')) {
                    $formattedChanges[$key] = date('d/m/Y', strtotime($value));
                } else {
                    $formattedChanges[$key] = $value;
                }
            }

            ActionLog::register('Férias', 'Alterar Férias', [
                'funcionario' => $ownerName,
                'alteracoes' => $formattedChanges
            ]);

            $vacation->save();
            
            return redirect()->route('vacations.index', ['year' => $vacation->year])
                             ->with('success', 'Férias atualizadas!');
        }

        return redirect()->route('vacations.index', ['year' => $vacation->year])
                         ->with('info', 'Nenhuma alteração realizada.');
    }

    public function destroy(Vacation $vacation)
    {
        if (!Auth::user()->hasRole('Admin') && Auth::id() !== $vacation->user_id) {
            abort(403);
        }
        
        $ownerName = $vacation->user->name ?? 'Desconhecido';
        
        $periodo = 'N/D';
        if ($vacation->period_1_start && $vacation->period_1_end) {
            $periodo = date('d/m/Y', strtotime($vacation->period_1_start)) . ' a ' . date('d/m/Y', strtotime($vacation->period_1_end));
        }

        ActionLog::register('Férias', 'Excluir Férias', [
            'funcionario' => $ownerName,
            'periodo_excluido' => $periodo,
            'ano_referencia' => $vacation->year
        ]);

        $vacation->delete();
        return back()->with('success', 'Registro excluído.');
    }
}