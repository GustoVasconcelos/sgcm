<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ActionLog;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
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
            'color' => 'nullable|string'
        ]);

        $program = Program::create($request->all());

        // --- LOG: Criar Programa ---
        ActionLog::register('Programas', 'Criar Programa', [
            'nome' => $program->name,
            'duracao_padrao' => $program->default_duration . ' min'
        ]);
        // ---------------------------

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

        // Preenche os dados novos no objeto
        $program->fill($request->all());

        // --- LOG INTELIGENTE: Verificar o que mudou ---
        if ($program->isDirty()) {
            $changes  = $program->getDirty();
            $logDetails = ['nome' => $program->name];

            // Mapa de rótulos legíveis para cada campo
            $labels = [
                'name'             => 'nome',
                'default_duration' => 'duração padrão',
                'color'            => 'cor',
            ];

            foreach ($changes as $field => $newValue) {
                $oldValue = $program->getOriginal($field);
                $label    = $labels[$field] ?? $field;

                if ($field === 'default_duration') {
                    // default_duration é número de minutos — formata diretamente
                    $logDetails[$label] = "{$oldValue}min ➜ {$newValue}min";
                } else {
                    $logDetails[$label] = "$oldValue ➜ $newValue";
                }
            }

            ActionLog::register('Programas', 'Editar Programa', $logDetails);
            
            // Salva efetivamente
            $program->save();
            return redirect()->route('programs.index')->with('success', 'Programa atualizado!');
        }

        // Se não mudou nada, apenas volta sem logar
        return redirect()->route('programs.index')->with('info', 'Nenhuma alteração realizada.');
    }

    public function destroy(Program $program)
    {
        if($program->schedules()->count() > 0) {
            return back()->with('error', 'Não é possível excluir: este programa já está agendado na grade.');
        }

        // --- LOG: Excluir Programa (Antes de apagar) ---
        ActionLog::register('Programas', 'Excluir Programa', [
            'nome' => $program->name,
            'id_original' => $program->id
        ]);
        // ----------------------------------------------

        $program->delete();
        return back()->with('success', 'Programa excluído.');
    }
}