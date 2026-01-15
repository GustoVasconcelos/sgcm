<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
        // Gera lista de anos permitidos (Ano Atual + 2 anos para frente)
        $currentYear = date('Y');
        $allowedYears = range($currentYear, $currentYear + 2);

        return view('vacations.create', compact('allowedYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => [
                'required', 
                'integer', 
                'min:' . date('Y'), // Bloqueia anos passados
                // Regra de Unicidade Complexa:
                // Único na tabela 'vacations', coluna 'year', MAS...
                // ...apenas onde 'user_id' for igual ao ID do usuário atual.
                Rule::unique('vacations')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'mode' => 'required',
            'period_1_start' => 'required|date',
            'period_1_end' => 'required|date|after_or_equal:period_1_start',
        ], [
            'year.unique' => 'Você já possui um cadastro de férias para este ano.',
            'year.min' => 'Não é possível cadastrar férias para anos passados.'
        ]);

        $data = $request->all();
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
        if (Auth::user()->profile !== 'admin' && Auth::id() !== $vacation->user_id) {
            return redirect()->route('vacations.index')->with('error', 'Sem permissão.');
        }

        // Gera lista de anos permitidos
        $currentYear = date('Y');
        $allowedYears = range($currentYear, $currentYear + 2);

        // Se o ano da férias que é editado for antigo (ex: 2024) e não estiver na lista,
        // adicionamos ele manualmente para não quebrar o select da View.
        if (!in_array($vacation->year, $allowedYears)) {
            array_unshift($allowedYears, $vacation->year);
        }

        return view('vacations.edit', compact('vacation', 'allowedYears'));
    }

    public function update(Request $request, Vacation $vacation)
    {
        if (Auth::user()->profile !== 'admin' && Auth::id() !== $vacation->user_id) {
            abort(403);
        }

        $request->validate([
            'year' => [
                'required', 
                'integer',
                // Na edição, validamos a unicidade ignorando o próprio ID do registro
                Rule::unique('vacations')->where(function ($query) use ($vacation) {
                    return $query->where('user_id', $vacation->user_id);
                })->ignore($vacation->id),
            ],
            // ... outras validações se necessário futuramente
        ], [
            'year.unique' => 'Este usuário já possui outro cadastro de férias para este ano.'
        ]);

        $vacation->fill($request->all());

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
        if (Auth::user()->profile !== 'admin' && Auth::id() !== $vacation->user_id) {
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