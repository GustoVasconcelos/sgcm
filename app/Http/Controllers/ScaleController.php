<?php

namespace App\Http\Controllers;

use App\Models\Scale;
use App\Models\ScaleShift;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Importante para o PDF

class ScaleController extends Controller
{
    public function index()
    {
        $scales = Scale::orderBy('start_date', 'desc')->paginate(10);
        return view('scales.index', compact('scales'));
    }

    public function create()
    {
        return view('scales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'type' => 'required'
        ]);

        // CORREÇÃO: Força a semana a começar na Segunda-Feira
        $start = Carbon::parse($request->start_date)->startOfWeek(\Carbon\Carbon::MONDAY);
        $end = $start->copy()->addDays(6);

        // 1. Cria a Escala (Capa)
        $scale = Scale::create([
            'start_date' => $start,
            'end_date' => $end,
            'type' => $request->type,
            'user_id' => Auth::id()
        ]);

        // 2. Gera os Slots para os 7 dias (Segunda até Domingo)
        for ($i = 0; $i < 7; $i++) {
            $currentDate = $start->copy()->addDays($i);
            
            // Definição dos Turnos (Padrão Normal)
            $shifts = [
                ['name' => '06:00 - 12:00', 'order' => 1],
                ['name' => '12:00 - 18:00', 'order' => 2],
                ['name' => '18:00 - 00:00', 'order' => 3],
                ['name' => '00:00 - 06:00', 'order' => 4],
                ['name' => 'FOLGA',         'order' => 5],
            ];

            // Se for modo Férias E o dia for entre Domingo e Quinta (Regra de 8h)
            // Nota: Domingo é dia 0, Segunda 1... Quinta 4, Sexta 5, Sábado 6 no Carbon
            // Mas sua regra é: Turnos de 8h "geralmente de domingo a quinta".
            // Como a escala começa na SEGUNDA, vamos aplicar a regra nos dias corretos.
            if ($request->type == 'ferias') {
                // Verifica se é Sexta ou Sábado (mantém 6h). Se não, usa 8h.
                // isFriday() ou isSaturday() mantém normal. O resto muda.
                if (!$currentDate->isFriday() && !$currentDate->isSaturday()) {
                    $shifts = [
                        ['name' => '06:00 - 14:00', 'order' => 1],
                        ['name' => '14:00 - 22:00', 'order' => 2],
                        ['name' => '22:00 - 06:00', 'order' => 3], // Madrugada
                        ['name' => 'FOLGA',         'order' => 4],
                    ];
                }
            }

            foreach ($shifts as $shift) {
                ScaleShift::create([
                    'scale_id' => $scale->id,
                    'date' => $currentDate,
                    'name' => $shift['name'],
                    'order' => $shift['order'],
                    'user_id' => null
                ]);
            }
        }

        return redirect()->route('scales.edit', $scale->id)->with('success', 'Escala criada! Agora defina os operadores.');
    }

    public function edit(Scale $scale)
    {
        $days = $scale->shifts()
            ->orderBy('date', 'asc')
            ->orderBy('order', 'asc')
            ->get()
            ->groupBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // Usa a nossa função inteligente em vez de User::all()
        $users = $this->getOperators();

        return view('scales.edit', compact('scale', 'days', 'users'));
    }

    public function update(Request $request, Scale $scale)
    {
        // Verifica se existem slots para atualizar
        $data = $request->input('slots');

        if (is_array($data)) {
            foreach ($data as $shiftId => $userId) {
                $shift = ScaleShift::find($shiftId);
                if ($shift) {
                    $shift->user_id = $userId; // Se vier null, salva null
                    $shift->save();
                }
            }
            return redirect()->route('scales.index')->with('success', 'Escala salva com sucesso!');
        }

        // Se não tiver dados (ex: formulário vazio), apenas redireciona sem erro
        return redirect()->route('scales.edit', $scale->id)->with('warning', 'Nenhuma alteração detectada.');
    }

    public function destroy(Scale $scale)
    {
        $scale->delete();
        return back()->with('success', 'Escala excluída.');
    }

    // --- GERAÇÃO DO PDF ---
    public function pdf(Scale $scale)
    {
        $days = $scale->shifts->groupBy(function($item) {
            return $item->date->format('Y-m-d');
        });

        $users = $this->getOperators();

        // Passamos explicitamente startDate e endDate para padronizar com o relatório RH
        $startDate = $scale->start_date;
        $endDate = $scale->end_date;
        $reportTitle = 'ESCALA EXIBIÇÃO'; // Título Padrão

        $pdf = Pdf::loadView('scales.pdf', compact('days', 'users', 'startDate', 'endDate', 'reportTitle'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('escala_semana.pdf');
    }

    // Alteração dos slots
    public function regenerateDay(Request $request, Scale $scale)
    {
        $request->validate([
            'date' => 'required|date',
            'mode' => 'required|in:6h,8h' // 6h = Normal, 8h = Férias
        ]);

        $targetDate = $request->date;
        $mode = $request->mode;

        // 1. Apaga os turnos existentes APENAS naquele dia e naquela escala
        ScaleShift::where('scale_id', $scale->id)
                ->where('date', $targetDate)
                ->delete();

        // 2. Define os novos turnos baseados no modo escolhido
        if ($mode == '8h') {
            // Modo Férias (3 turnos de 8h + Folga)
            $newShifts = [
                ['name' => '06:00 - 14:00', 'order' => 1],
                ['name' => '14:00 - 22:00', 'order' => 2],
                ['name' => '22:00 - 06:00', 'order' => 3], // Madrugada
                ['name' => 'FOLGA',         'order' => 4],
            ];
        } else {
            // Modo Normal (4 turnos de 6h + Folga)
            $newShifts = [
                ['name' => '06:00 - 12:00', 'order' => 1],
                ['name' => '12:00 - 18:00', 'order' => 2],
                ['name' => '18:00 - 00:00', 'order' => 3],
                ['name' => '00:00 - 06:00', 'order' => 4],
                ['name' => 'FOLGA',         'order' => 5],
            ];
        }

        // 3. Cria os novos slots no banco
        foreach ($newShifts as $shift) {
            ScaleShift::create([
                'scale_id' => $scale->id,
                'date' => $targetDate,
                'name' => $shift['name'],
                'order' => $shift['order'],
                'user_id' => null // Reinicia vazio para o usuário preencher
            ]);
        }

        return back()->with('success', 'Layout do dia ' . date('d/m', strtotime($targetDate)) . ' atualizado com sucesso!');
    }

    private function getOperators()
    {
        // 1. Busca apenas quem NÃO é admin (profile != 'admin')
        // Ajuste 'profile' para o nome exato da sua coluna se for diferente (ex: 'role', 'tipo')
        $users = User::where('profile', '!=', 'admin')->orderBy('name')->get();

        // 2. Conta quantas vezes cada primeiro nome aparece
        $firstNameCounts = $users->map(function ($user) {
            return explode(' ', trim($user->name))[0]; // Pega só o primeiro nome
        })->countBy(); // Retorna ex: ['Gabriel' => 2, 'Augusto' => 1]

        // 3. Adiciona o atributo 'display_name' em cada usuário
        $users->transform(function ($user) use ($firstNameCounts) {
            $firstName = explode(' ', trim($user->name))[0];
            
            // Se existir mais de 1 pessoa com esse primeiro nome, usa o Nome Completo
            // Senão, usa só o Primeiro Nome
            if (isset($firstNameCounts[$firstName]) && $firstNameCounts[$firstName] > 1) {
                $user->display_name = $user->name; // Completo
            } else {
                $user->display_name = strtoupper($firstName); // Curto (em Maiúsculo fica bonito na escala)
            }
            
            return $user;
        });

        return $users;
    }

    // Exibe o formulário
    public function rhForm()
    {
        return view('reports.rh');
    }

    // Gera o Relatório Customizado
    public function rhGenerate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        // Busca TODOS os turnos nesse intervalo, independente da escala
        $shifts = ScaleShift::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        // Agrupa por dia para o layout funcionar
        $days = $shifts->groupBy(function($item) {
            return $item->date->format('Y-m-d');
        });

        // Pega os usuários inteligentes
        $users = $this->getOperators();

        // Variáveis para a View
        $startDate = $start;
        $endDate = $end;
        $reportTitle = 'RELATÓRIO DE ESCALAS (RH)';

        $pdf = Pdf::loadView('scales.pdf', compact('days', 'users', 'startDate', 'endDate', 'reportTitle'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('relatorio_rh.pdf');
    }
}