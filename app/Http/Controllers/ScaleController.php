<?php

namespace App\Http\Controllers;

use App\Models\ScaleShift;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ScaleController extends Controller
{
    // Tela Inicial: Seleção de Período
    public function index(Request $request)
    {
        if ($request->has(['start_date', 'end_date'])) {
            return redirect()->route('scales.manage', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);
        }

        return view('scales.index');
    }

    // Tela de Edição/Visualização (O "Calendário")
    public function manage(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $end = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today()->addDays(6);
        
        if ($end->diffInDays($start) > 40) {
            return back()->with('error', 'Selecione um período de no máximo 40 dias.');
        }

        // 1. Busca os turnos JÁ EXISTENTES
        $existingShifts = ScaleShift::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('order')
            ->get()
            ->groupBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // 2. Monta a estrutura final
        $days = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');

            // Tenta pegar os turnos do dia
            $shiftsForDay = $existingShifts->get($dateStr);

            if ($shiftsForDay && $shiftsForDay->isNotEmpty()) {
                $days[$dateStr] = $shiftsForDay;
            } else {
                // Gera estrutura vazia na memória se não existir
                $fakeShifts = collect([]);
                $defaults = [
                    ['name' => '06:00 - 12:00', 'order' => 1],
                    ['name' => '12:00 - 18:00', 'order' => 2],
                    ['name' => '18:00 - 00:00', 'order' => 3],
                    ['name' => '00:00 - 06:00', 'order' => 4],
                    ['name' => 'FOLGA',         'order' => 5],
                ];

                foreach ($defaults as $def) {
                    $fakeShift = new ScaleShift([
                        'date' => $dateStr,
                        'name' => $def['name'],
                        'order' => $def['order'],
                        'user_id' => null
                    ]);
                    $fakeShift->exists = false; 
                    $fakeShifts->push($fakeShift);
                }
                $days[$dateStr] = $fakeShifts;
            }

            $current->addDay();
        }

        // AQUI ESTAVA O PROBLEMA: Chamamos a função restaurada abaixo
        $users = $this->getOperators(); 

        return view('scales.edit', compact('days', 'users', 'start', 'end'));
    }

    // Salva as alterações
    public function store(Request $request)
    {
        $slots = $request->input('slots', []);
        $names = $request->input('names', []);

        foreach ($slots as $key => $userId) {
            [$date, $order] = explode('_', $key);

            ScaleShift::updateOrCreate(
                [
                    'date' => $date,
                    'order' => $order
                ],
                [
                    'user_id' => $userId,
                    'name' => $names[$key] ?? 'Turno Padrão',
                ]
            );
        }

        return redirect()->route('scales.manage', [
            'start_date' => $request->start_date, 
            'end_date' => $request->end_date
        ])->with('success', 'Escala atualizada com sucesso!');
    }

    // Regenerar Dia
    public function regenerateDay(Request $request)
    {
        $date = $request->date;
        ScaleShift::where('date', $date)->delete();

        $newShifts = ($request->mode == '8h') ? 
            [
                ['name' => '06:00 - 14:00', 'order' => 1],
                ['name' => '14:00 - 22:00', 'order' => 2],
                ['name' => '22:00 - 06:00', 'order' => 3],
                ['name' => 'FOLGA',         'order' => 4],
            ] : 
            [
                ['name' => '06:00 - 12:00', 'order' => 1],
                ['name' => '12:00 - 18:00', 'order' => 2],
                ['name' => '18:00 - 00:00', 'order' => 3],
                ['name' => '00:00 - 06:00', 'order' => 4],
                ['name' => 'FOLGA',         'order' => 5],
            ];

        foreach ($newShifts as $shift) {
            ScaleShift::create([
                'date' => $date,
                'name' => $shift['name'],
                'order' => $shift['order'],
                'user_id' => null
            ]);
        }

        return back()->with('success', 'Layout atualizado!');
    }

    // --- FUNÇÃO PRIVADA RESTAURADA ---
    private function getOperators()
    {
        // 1. Busca quem NÃO é admin
        $users = User::where('profile', '!=', 'admin')->orderBy('name')->get();

        // 2. Lógica do Nome Repetido
        $firstNameCounts = $users->map(function ($user) {
            return explode(' ', trim($user->name))[0];
        })->countBy();

        $users->transform(function ($user) use ($firstNameCounts) {
            $firstName = explode(' ', trim($user->name))[0];
            
            if (isset($firstNameCounts[$firstName]) && $firstNameCounts[$firstName] > 1) {
                $user->display_name = $user->name; 
            } else {
                $user->display_name = strtoupper($firstName);
            }
            
            return $user;
        });

        return $users;
    }
    
    // Métodos de PDF/RH antigos (Mantenha se estiver usando)
    public function rhForm() { return view('reports.rh'); }
    public function rhGenerate(Request $request) { /* ... lógica do PDF ... */ }

    public function print(Request $request)
    {
        // 1. Define as datas
        $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $end = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today()->addDays(6);

        // 2. Busca o que já existe no banco
        $existingShifts = ScaleShift::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('order')
            ->get()
            ->groupBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // 3. Monta a grade (Preenchendo dias vazios com "fakes")
        $days = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $shiftsForDay = $existingShifts->get($dateStr);

            if ($shiftsForDay && $shiftsForDay->isNotEmpty()) {
                $days[$dateStr] = $shiftsForDay;
            } else {
                // Gera estrutura vazia na memória
                $fakeShifts = collect([]);
                $defaults = [
                    ['name' => '06:00 - 12:00', 'order' => 1],
                    ['name' => '12:00 - 18:00', 'order' => 2],
                    ['name' => '18:00 - 00:00', 'order' => 3],
                    ['name' => '00:00 - 06:00', 'order' => 4],
                    ['name' => 'FOLGA',         'order' => 5],
                ];

                foreach ($defaults as $def) {
                    $fakeShift = new ScaleShift([
                        'date' => $dateStr,
                        'name' => $def['name'],
                        'order' => $def['order'],
                        'user_id' => null
                    ]);
                    $fakeShifts->push($fakeShift);
                }
                $days[$dateStr] = $fakeShifts;
            }
            $current->addDay();
        }

        // 4. Pega os usuários
        $users = $this->getOperators();

        // --- CORREÇÃO AQUI ---
        // Convertemos o array $days para uma Collection do Laravel
        // para que a função ->chunk(2) funcione no PDF
        $days = collect($days); 

        // 5. Configurações do PDF
        $startDate = $start;
        $endDate = $end;
        $reportTitle = 'ESCALA DE TRABALHO'; 

        $pdf = Pdf::loadView('scales.pdf', compact('days', 'users', 'startDate', 'endDate', 'reportTitle'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('escala_' . $start->format('dm') . '_ate_' . $end->format('dm') . '.pdf');
    }
}