<?php

namespace App\Services;

use App\Models\ScaleShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScaleAutoGenerator
{
    protected $scaleService;

    public function __construct(ScaleService $scaleService)
    {
        $this->scaleService = $scaleService;
    }

    /**
     * Executa a geração automática de escalas para o período.
     * Retorna um array com status e dados para log.
     */
    public function execute($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // 1. Validação do número de operadores
        $requiredOperators = config('scale.required_operators');
        $allOperators = $this->scaleService->getOperators()->pluck('id')->toArray();
        if (count($allOperators) !== $requiredOperators) {
            throw new \Exception("A geração automática exige EXATAMENTE {$requiredOperators} operadores ativos.");
        }

        $current = $start->copy();
        $daysFilled = 0;
        $generatedDates = [];

        while ($current <= $end) {
            $currentDateStr = $current->format('Y-m-d');
            $currentDateBr = $current->format('d/m/Y');

            // 2. Pula se já estiver preenchido
            if ($this->isDayFilled($currentDateStr)) {
                $current->addDay();
                continue; 
            }

            // 3. Busca e Valida o Dia Anterior
            $yesterday = $current->copy()->subDay();
            $mapYesterday = $this->getYesterdayMap($yesterday);

            $this->validateYesterday($mapYesterday, $yesterday);

            // 4. Calcula Rotação
            $newRotation = $this->calculateRotation($mapYesterday, $allOperators, $yesterday);

            // 5. Salva no Banco
            $this->saveRotation($currentDateStr, $newRotation);

            $daysFilled++;
            $generatedDates[] = $currentDateBr;
            $current->addDay();
        }

        return [
            'success' => true,
            'days_filled' => $daysFilled,
            'message' => $daysFilled > 0 
                ? "$daysFilled dias foram preenchidos automaticamente." 
                : "Nenhum dia precisou ser preenchido.",
            'log_data' => [
                'periodo_solicitado' => $start->format('d/m') . ' a ' . $end->format('d/m'),
                'total_dias_criados' => $daysFilled,
                'dias_gerados' => implode(', ', $generatedDates)
            ]
        ];
    }

    private function isDayFilled($dateStr)
    {
        // Verifica se existem os turnos fundamentais (1 a 4)
        return ScaleShift::where('date', $dateStr)
            ->whereIn('order', [
                \App\Enums\ShiftOrder::MORNING->value, 
                \App\Enums\ShiftOrder::AFTERNOON->value, 
                \App\Enums\ShiftOrder::NIGHT->value, 
                \App\Enums\ShiftOrder::DAWN->value
            ])
            ->whereNotNull('user_id')
            ->exists();
    }

    private function getYesterdayMap($yesterdayDate)
    {
        $shifts = ScaleShift::where('date', $yesterdayDate->format('Y-m-d'))
            ->whereNotNull('user_id')
            ->get();

        $map = [];
        foreach ($shifts as $shift) {
            $map[$shift->order] = $shift->user_id;
        }
        return $map;
    }

    private function validateYesterday($map, $dateObj)
    {
        $dateBr = $dateObj->format('d/m/Y');
        
        // Mínimo de 3 turnos preenchidos para inferir algo
        if (count($map) < 3) {
            throw new \Exception("O dia anterior ($dateBr) está vazio ou incompleto. Preencha-o primeiro.");
        }
        
        // Verifica se falta a madrugada (Indica escala de 8h ou erro)
        if (!isset($map[\App\Enums\ShiftOrder::DAWN->value])) {
            throw new \Exception("O dia anterior ($dateBr) não tem o turno da madrugada (00h-06h). A automação só funciona em escalas de 6h.");
        }
    }

    private function calculateRotation($mapYesterday, $allOperators, $dateObj)
    {
        $morning   = \App\Enums\ShiftOrder::MORNING->value;
        $afternoon = \App\Enums\ShiftOrder::AFTERNOON->value;
        $night     = \App\Enums\ShiftOrder::NIGHT->value;
        $dawn      = \App\Enums\ShiftOrder::DAWN->value;
        $off       = \App\Enums\ShiftOrder::OFF->value;

        // Descobre quem trabalhou ontem (Turnos 1, 2, 3, 4)
        $workedYesterdayIds = [];
        foreach ([$morning, $afternoon, $night, $dawn] as $ord) {
            if (isset($mapYesterday[$ord])) $workedYesterdayIds[] = $mapYesterday[$ord];
        }
        
        // Quem sobrou estava de folga
        $folgaYesterdayArr = array_diff($allOperators, $workedYesterdayIds);
        
        if (empty($folgaYesterdayArr)) {
            throw new \Exception("Erro de lógica no dia " . $dateObj->format('d/m') . ": Não foi possível identificar quem estava de folga ontem.");
        }
        
        $userFolgaYesterday = reset($folgaYesterdayArr); 

        // ROTAÇÃO PADRÃO:
        // Turno 1 (06h) <- Quem fez Turno 2 ontem (Tarde)
        // Turno 2 (12h) <- Quem fez Turno 3 ontem (Noite)
        // Turno 3 (18h) <- Quem fez Turno 4 ontem (Madruga)
        // Turno 4 (00h) <- Quem estava de FOLGA ontem
        // FOLGA         <- Quem fez Turno 1 ontem (Manhã)
        
        return [
            $morning   => $mapYesterday[$afternoon] ?? null,
            $afternoon => $mapYesterday[$night] ?? null,
            $night     => $mapYesterday[$dawn] ?? null,
            $dawn      => $userFolgaYesterday,
            $off       => $mapYesterday[$morning] ?? null // Vai para folga
        ];
    }

    private function saveRotation($dateStr, $rotation)
    {
        // Monta mapa order => name a partir da config
        $shifts = config('scale.shifts_6h');
        $names = collect($shifts)->pluck('name', 'order')->toArray();

        foreach ($rotation as $order => $userId) {
            if ($userId) {
                ScaleShift::updateOrCreate(
                    ['date' => $dateStr, 'order' => $order],
                    ['user_id' => $userId, 'name' => $names[$order]]
                );
            }
        }
    }
}