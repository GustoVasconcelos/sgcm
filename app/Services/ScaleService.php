<?php

namespace App\Services;

use App\Models\ScaleShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScaleService
{
    /**
     * Retorna a estrutura completa da escala (dias + operadores) para um período.
     * Usado tanto pela Tela de Edição quanto pelo PDF.
     */
    public function getScaleData(Carbon $start, Carbon $end): array
    {
        // 1. Busca os turnos JÁ EXISTENTES
        $existingShifts = ScaleShift::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('order')
            ->get()
            ->groupBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // 2. Monta a estrutura final (Preenche dias vazios)
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
                    // Marca como não existente para saber que é 'fake' se precisar
                    $fakeShift->exists = false; 
                    $fakeShifts->push($fakeShift);
                }
                $days[$dateStr] = $fakeShifts;
            }

            $current->addDay();
        }

        // 3. Busca Operadores Formatados
        $users = $this->getOperators();

        // 4. Adiciona o "NÃO HÁ" (Necessário para edição e visualização)
        $userNaoHa = User::where('name', 'NÃO HÁ')->first();
        if ($userNaoHa) {
            // Clonamos ou ajustamos para garantir que apareça certo
            $userNaoHa->display_name = 'NÃO HÁ';
            // Se já não estiver na lista (getOperators filtra 'NÃO HÁ')
            $users->push($userNaoHa);
        }

        return [
            'days' => collect($days),
            'users' => $users
        ];
    }

    /**
     * Lógica centralizada para buscar operadores e tratar nomes repetidos.
     */
    public function getOperators(): Collection
    {
        // 1. Busca quem é operador e não é o usuário "dummy"
        $users = User::where('is_operator', true) 
            ->where('name', '!=', 'NÃO HÁ') 
            ->orderBy('name')
            ->get();

        // 2. Lógica do Nome Repetido (Cálculo de duplicidade)
        $firstNameCounts = $users->map(function ($user) {
            return explode(' ', trim($user->name))[0];
        })->countBy();

        $users->transform(function ($user) use ($firstNameCounts) {
            $firstName = explode(' ', trim($user->name))[0];
            
            // Se tiver mais de um "João", mostra o nome completo. Se não, só "JOÃO".
            if (isset($firstNameCounts[$firstName]) && $firstNameCounts[$firstName] > 1) {
                $user->display_name = $user->name; 
            } else {
                $user->display_name = strtoupper($firstName);
            }
            
            return $user;
        });

        return $users;
    }

    /**
     * Processa a atualização manual dos turnos vinda do formulário.
     * Retorna dados para o Log se houver mudanças.
     */
    public function updateShifts(array $slots, array $names): array
    {
        $changedDays = [];

        foreach ($slots as $key => $userId) {
            [$date, $order] = explode('_', $key);

            // Busca o turno atual para comparar mudança
            $currentShift = ScaleShift::where('date', $date)->where('order', $order)->first();
            $oldUserId = $currentShift ? $currentShift->user_id : null;
            
            // Se mudou o operador
            if ((string)$oldUserId !== (string)$userId) {
                $formattedDate = Carbon::parse($date)->format('d/m');
                if (!in_array($formattedDate, $changedDays)) {
                    $changedDays[] = $formattedDate;
                }
            }

            // Salva
            ScaleShift::updateOrCreate(
                ['date' => $date, 'order' => $order],
                ['user_id' => $userId, 'name' => $names[$key] ?? 'Turno Padrão']
            );
        }

        return $changedDays; // Retorna dias alterados para o Log
    }

    /**
     * Reseta um dia para o padrão (6h ou 8h).
     */
    public function resetDay($date, $mode)
    {
        ScaleShift::where('date', $date)->delete();

        $newShifts = ($mode == '8h') ? 
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
    }
}