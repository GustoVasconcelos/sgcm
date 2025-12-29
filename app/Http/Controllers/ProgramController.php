<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        // Lista em ordem alfabética para facilitar
        $programs = Program::orderBy('name')->paginate(15);
        return view('programs.index', compact('programs'));
    }

    public function create()
    {
        return view('programs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:programs,name',
            'default_duration' => 'required|integer|min:1',
            'color' => 'nullable|string' // Opcional, mas legal visualmente
        ]);

        Program::create($request->all());

        return redirect()->route('programs.index')->with('success', 'Programa cadastrado com sucesso!');
    }

    public function edit(Program $program)
    {
        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:programs,name,' . $program->id,
            'default_duration' => 'required|integer|min:1',
            'color' => 'nullable|string'
        ]);

        $program->update($request->all());

        return redirect()->route('programs.index')->with('success', 'Programa atualizado!');
    }

    public function destroy(Program $program)
    {
        // Verifica se o programa está em uso na grade antes de apagar (opcional, mas seguro)
        if($program->schedules()->count() > 0) {
            return back()->with('error', 'Não é possível excluir: este programa já está agendado na grade.');
        }

        $program->delete();
        return back()->with('success', 'Programa excluído.');
    }
}