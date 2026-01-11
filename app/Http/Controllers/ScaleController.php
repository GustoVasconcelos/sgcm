<?php

namespace App\Http\Controllers;

use App\Models\ScaleShift;
use App\Models\ActionLog;
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

        if ($start > $end) {
            return back()->with('error', 'Data inicial maior que a Data final.');
        }
        
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

        // Pega a lista de operadores (que agora NÃO traz o "NÃO HÁ")
        $users = $this->getOperators(); 

        // Busca o ID do usuário Coringa separadamente
        $userNaoHa = User::where('name', 'NÃO HÁ')->first();
        $idNaoHa = $userNaoHa ? $userNaoHa->id : null;

        // Passa o $idNaoHa para a view
        return view('scales.edit', compact('days', 'users', 'start', 'end', 'idNaoHa'));
    }

    // Salva as alterações
    public function store(Request $request)
    {
        $slots = $request->input('slots', []);
        $names = $request->input('names', []);
        
        $changedDays = []; // Lista para guardar os dias alterados

        foreach ($slots as $key => $userId) {
            [$date, $order] = explode('_', $key);

            // Busca o turno atual no banco para comparar
            $currentShift = ScaleShift::where('date', $date)->where('order', $order)->first();
            
            // Verifica se houve mudança (Se não existia e agora tem ID, ou se o ID mudou)
            $oldUserId = $currentShift ? $currentShift->user_id : null;
            
            // Se o valor novo for diferente do antigo
            $StringUserId = (string)$userId;
            if ((string)$oldUserId !== $StringUserId) {
                // Formata a data para salvar no log de forma legível
                $formattedDate = Carbon::parse($date)->format('d/m');
                if (!in_array($formattedDate, $changedDays)) {
                    $changedDays[] = $formattedDate;
                }
            }

            // Salva normalmente
            ScaleShift::updateOrCreate(
                ['date' => $date, 'order' => $order],
                ['user_id' => $userId, 'name' => $names[$key] ?? 'Turno Padrão']
            );
        }

        // SÓ REGISTRA SE HOUVE MUDANÇA
        if (count($changedDays) > 0) {
            ActionLog::register('Escalas', 'Salvar Alterações', [
                'dias_alterados' => implode(', ', $changedDays)
            ]);
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
        // Adicionei ->where('name', '!=', 'NÃO HÁ')
        $users = User::where('is_operator', true) 
            ->where('name', '!=', 'NÃO HÁ') 
            ->orderBy('name')
            ->get();

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

    // --- NOVO: Método para Enviar Email ---
    public function sendEmail(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $periodoTxt = $start->format('d/m/Y') . ' a ' . $end->format('d/m/Y');

        // 1. Gera o PDF
        $pdf = $this->generatePdfObject($start, $end);
        $pdfContent = $pdf->output();

        // 2. Prepara variáveis de envio
        $recipients = User::whereIn('id', $request->recipients)->get();
        $senderName = Auth::user()->name; // Quem está clicando no botão
        $sentNames = [];
        $failedNames = []; // Para logar quem deu erro

        // 3. Loop de Envio
        foreach ($recipients as $user) {
            try {
                // Passamos o $senderName para o Email
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\ScaleShipped($pdfContent, $periodoTxt, $senderName));
                
                $sentNames[] = $user->name;
            } catch (\Exception $e) {
                // Se der erro, guarda o nome e o motivo para o Log
                $failedNames[] = "{$user->name} ({$e->getMessage()})";
            }
        }

        // 4. Log de Sucesso (se houve envios)
        if (count($sentNames) > 0) {
            ActionLog::register('Escalas', 'Enviar por Email', [
                'periodo' => $periodoTxt,
                'enviado_por' => $senderName,
                'destinatarios' => implode(', ', $sentNames),
                'total_sucesso' => count($sentNames)
            ]);
        }

        // 5. Log de ERRO (se houve falhas)
        if (count($failedNames) > 0) {
            ActionLog::register('Escalas', 'Erro no Envio de Email', [
                'periodo' => $periodoTxt,
                'tentativa_de' => $senderName,
                'falhas_detalhadas' => $failedNames // Salva array com erros técnicos
            ]);
            
            // Retorna com aviso amarelo (warning) se houve falhas parciais
            return back()->with('warning', 'Envio finalizado com ressalvas. Falha ao enviar para: ' . count($failedNames) . ' operadores. Verifique os logs.');
        }

        return back()->with('success', 'Escala enviada com sucesso para ' . count($sentNames) . ' operadores!');
    }

    // --- REFATORADO: Método Imprimir ---
    public function print(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $end = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today()->addDays(6);

        // Gera o objeto PDF usando o método auxiliar
        $pdf = $this->generatePdfObject($start, $end);

        // Log
        ActionLog::register('Escalas', 'Baixar PDF', [
            'periodo' => $start->format('d/m/Y') . ' a ' . $end->format('d/m/Y'),
            'arquivo' => 'ESCALA_' . $start->format('d-m') . '_a_' . $end->format('d-m') . '.pdf'
        ]);

        return $pdf->stream('ESCALA_' . $start->format('d-m') . '_a_' . $end->format('d-m') . '.pdf');
    }

    // --- NOVO: Método Privado Auxiliar ---
    private function generatePdfObject($start, $end)
    {
        // 1. Busca dados
        $existingShifts = ScaleShift::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date')->orderBy('order')->get()
            ->groupBy(function($item) { return $item->date->format('Y-m-d'); });

        // 2. Monta Grade (Preenche vazios)
        $days = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $shiftsForDay = $existingShifts->get($dateStr);

            if ($shiftsForDay && $shiftsForDay->isNotEmpty()) {
                $days[$dateStr] = $shiftsForDay;
            } else {
                $fakeShifts = collect([]);
                $defaults = [
                    ['name' => '06:00 - 12:00', 'order' => 1],
                    ['name' => '12:00 - 18:00', 'order' => 2],
                    ['name' => '18:00 - 00:00', 'order' => 3],
                    ['name' => '00:00 - 06:00', 'order' => 4],
                    ['name' => 'FOLGA',         'order' => 5],
                ];
                foreach ($defaults as $def) {
                    $fakeShifts->push(new ScaleShift([
                        'date' => $dateStr, 'name' => $def['name'], 'order' => $def['order'], 'user_id' => null
                    ]));
                }
                $days[$dateStr] = $fakeShifts;
            }
            $current->addDay();
        }

        // 3. Prepara Usuários
        $users = $this->getOperators();
        $days = collect($days);
        
        $userNaoHa = User::where('name', 'NÃO HÁ')->first();
        if ($userNaoHa) {
            $userNaoHa->display_name = 'NÃO HÁ';
            $users->push($userNaoHa);
        }

        // 4. Configura PDF
        $startDate = $start;
        $endDate = $end;
        $reportTitle = 'ESCALA DE TRABALHO'; 

        $pdf = Pdf::loadView('scales.pdf', compact('days', 'users', 'startDate', 'endDate', 'reportTitle'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function autoGenerate(Request $request)
    {
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        
        // 1. Validação dos 5 Operadores
        $allOperators = $this->getOperators()->pluck('id')->toArray();
        if (count($allOperators) !== 5) {
            return back()->with('error', 'A geração automática exige EXATAMENTE 5 operadores ativos (check "Participa da Escala" no cadastro).');
        }

        $current = $start->copy();
        $daysFilled = 0;
        $generatedDates = []; // --- LOG: Array para guardar as datas que foram preenchidas

        while ($current <= $end) {
            $currentDateStr = $current->format('Y-m-d');
            $currentDateBr = $current->format('d/m/Y');

            // 2. Verifica se o dia ATUAL já tem preenchimento válido
            $hasShiftsToday = ScaleShift::where('date', $currentDateStr)
                ->whereIn('order', [1, 2, 3, 4])
                ->whereNotNull('user_id')
                ->exists();

            if ($hasShiftsToday) {
                $current->addDay();
                continue; 
            }

            // 3. Busca dados do Dia ANTERIOR
            $yesterday = $current->copy()->subDay();
            $yesterdayShifts = ScaleShift::where('date', $yesterday->format('Y-m-d'))
                ->whereNotNull('user_id')
                ->get();

            // Mapeia: [Order => UserID]
            $mapYesterday = [];
            foreach ($yesterdayShifts as $shift) {
                $mapYesterday[$shift->order] = $shift->user_id;
            }

            // --- VALIDAÇÕES ---
            if (count($mapYesterday) < 3) {
                return back()->with('error', "Erro ao tentar gerar o dia {$currentDateBr}: O dia anterior (" . $yesterday->format('d/m/Y') . ") está vazio ou muito incompleto. Preencha-o primeiro.");
            }

            $hasOrder1 = isset($mapYesterday[1]);
            $hasOrder2 = isset($mapYesterday[2]);
            $hasOrder3 = isset($mapYesterday[3]);
            $hasOrder4 = isset($mapYesterday[4]);

            if ($hasOrder1 && $hasOrder2 && $hasOrder3 && !$hasOrder4) {
                return back()->with('error', "Erro ao tentar gerar o dia {$currentDateBr}: O dia anterior (" . $yesterday->format('d/m/Y') . ") parece ser uma escala de 8h (Férias/Reduzida), pois não tem o turno da madrugada (00h-06h). A rotação automática só funciona em escalas normais de 6h.");
            }

            if (!$hasOrder4) {
                return back()->with('error', "Erro ao tentar gerar o dia {$currentDateBr}: O dia anterior (" . $yesterday->format('d/m/Y') . ") está incompleto (falta o turno da madrugada 00h-06h).");
            }

            // --- LÓGICA DE ROTAÇÃO ---
            $workedYesterdayIds = [];
            foreach ([1, 2, 3, 4] as $ord) {
                if (isset($mapYesterday[$ord])) $workedYesterdayIds[] = $mapYesterday[$ord];
            }
            
            $folgaYesterdayArr = array_diff($allOperators, $workedYesterdayIds);
            
            if (empty($folgaYesterdayArr)) {
                return back()->with('error', "Erro de lógica no dia " . $yesterday->format('d/m') . ": Não foi possível identificar quem estava de folga ontem. Verifique se há operadores repetidos.");
            }
            
            $userFolgaYesterday = reset($folgaYesterdayArr); 

            $newRotation = [
                1 => $mapYesterday[2] ?? null,
                2 => $mapYesterday[3] ?? null,
                3 => $mapYesterday[4] ?? null,
                4 => $userFolgaYesterday,
            ];

            // Salva Turnos
            foreach ($newRotation as $order => $userId) {
                if ($userId) {
                    ScaleShift::updateOrCreate(
                        ['date' => $currentDateStr, 'order' => $order],
                        ['user_id' => $userId, 'name' => $this->getShiftName($order)]
                    );
                }
            }

            // Salva Folga
            if (isset($mapYesterday[1])) {
                ScaleShift::updateOrCreate(
                    ['date' => $currentDateStr, 'order' => 5],
                    ['user_id' => $mapYesterday[1], 'name' => 'FOLGA']
                );
            }

            $daysFilled++;
            $generatedDates[] = $currentDateBr; // --- LOG: Adiciona data na lista
            $current->addDay();
        }

        if ($daysFilled == 0) {
            return back()->with('info', 'Nenhum dia precisou ser preenchido (os dias selecionados já estavam completos).');
        }

        // --- LOG: Registra a ação no final ---
        ActionLog::register('Escalas', 'Geração Automática', [
            'periodo_solicitado' => $start->format('d/m') . ' a ' . $end->format('d/m'),
            'total_dias_criados' => $daysFilled,
            'dias_gerados' => implode(', ', $generatedDates)
        ]);
        // -------------------------------------

        return back()->with('success', "$daysFilled dias foram preenchidos automaticamente com a rotação.");
    }

    // Helper para nomes dos turnos
    private function getShiftName($order) {
        $names = [
            1 => '06:00 - 12:00',
            2 => '12:00 - 18:00',
            3 => '18:00 - 00:00',
            4 => '00:00 - 06:00',
            5 => 'FOLGA'
        ];
        return $names[$order] ?? 'Turno';
    }
}