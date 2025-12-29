<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        // Pega o ano da URL ou usa o ano atual como padrão
        $year = $request->input('year', date('Y'));

        // Busca TODAS as férias daquele ano, ordenado por nome do funcionário
        $vacations = Vacation::with('user')
            ->where('year', $year)
            ->join('users', 'users.id', '=', 'vacations.user_id') // Join para ordenar por nome
            ->orderBy('users.name')
            ->select('vacations.*') // Garante que pega os dados das férias
            ->get(); // Usei get() ao invés de paginate() para ficar parecendo listão de Excel

        // Cria uma lista de anos para o filtro (do ano atual + 1 até 5 anos atrás)
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
            'mode' => 'required',
            'period_1_start' => 'required|date',
            'period_1_end' => 'required|date|after_or_equal:period_1_start',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['status'] = 'aprovado'; // Como não tem aprovação, já nasce aprovado

        Vacation::create($data);

        return redirect()->route('vacations.index', ['year' => $request->year])
                         ->with('success', 'Férias cadastradas!');
    }

    public function edit(Vacation $vacation)
    {
        // Permite editar se for Admin OU se for o Dono das férias
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

        // Removemos validações complexas para simplificar, mas idealmente mantenha as de data
        $vacation->update($request->all());

        return redirect()->route('vacations.index', ['year' => $vacation->year])
                         ->with('success', 'Férias atualizadas!');
    }

    public function destroy(Vacation $vacation)
    {
        if (Auth::user()->profile !== 'admin' && Auth::id() !== $vacation->user_id) {
            abort(403);
        }
        
        $vacation->delete();
        return back()->with('success', 'Registro excluído.');
    }
}