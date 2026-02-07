<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\ScaleShift;
use App\Services\ScaleAutoGenerator;
use App\Services\ScaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScaleAutoGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected ScaleAutoGenerator $generator;
    protected array $operators;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = app(ScaleAutoGenerator::class);
    }

    /**
     * Helper para criar exatamente 5 operadores.
     */
    protected function createOperators(int $count = 5): array
    {
        $operators = [];
        for ($i = 1; $i <= $count; $i++) {
            $operators[] = User::factory()->operator()->create(['name' => "Operador $i"]);
        }
        return $operators;
    }

    /**
     * Preenche um dia completo com escala de 6h.
     */
    protected function fillDayWith6hScale(string $date, array $operators): void
    {
        $turnos = [
            1 => '06:00 - 12:00',
            2 => '12:00 - 18:00',
            3 => '18:00 - 00:00',
            4 => '00:00 - 06:00',
            5 => 'FOLGA',
        ];

        foreach ($turnos as $order => $name) {
            ScaleShift::create([
                'date' => $date,
                'order' => $order,
                'name' => $name,
                'user_id' => $operators[$order - 1]->id,
            ]);
        }
    }

    // =========================================================================
    // TESTES DE VALIDAÇÃO
    // =========================================================================

    public function test_requires_exactly_5_operators(): void
    {
        // Cria apenas 3 operadores
        $this->createOperators(3);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exige EXATAMENTE 5 operadores');

        $this->generator->execute('2026-02-02', '2026-02-02');
    }

    public function test_requires_previous_day_to_be_filled(): void
    {
        $operators = $this->createOperators(5);

        // Não preenche o dia anterior (2026-02-01)

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('está vazio ou incompleto');

        $this->generator->execute('2026-02-02', '2026-02-02');
    }

    public function test_requires_6h_scale_not_8h(): void
    {
        $operators = $this->createOperators(5);

        // Preenche com escala de 8h (sem turno 4 - madrugada)
        ScaleShift::create(['date' => '2026-02-01', 'order' => 1, 'name' => '06:00 - 14:00', 'user_id' => $operators[0]->id]);
        ScaleShift::create(['date' => '2026-02-01', 'order' => 2, 'name' => '14:00 - 22:00', 'user_id' => $operators[1]->id]);
        ScaleShift::create(['date' => '2026-02-01', 'order' => 3, 'name' => '22:00 - 06:00', 'user_id' => $operators[2]->id]);
        // Falta o order 4 (madrugada)!

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não tem o turno da madrugada');

        $this->generator->execute('2026-02-02', '2026-02-02');
    }

    // =========================================================================
    // TESTES DE ROTAÇÃO
    // =========================================================================

    public function test_generates_correct_rotation(): void
    {
        $operators = $this->createOperators(5);

        // Dia anterior: Op1=T1, Op2=T2, Op3=T3, Op4=T4(noite), Op5=FOLGA
        $this->fillDayWith6hScale('2026-02-01', $operators);

        $result = $this->generator->execute('2026-02-02', '2026-02-02');

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['days_filled']);

        // Verifica a rotação esperada:
        // Turno 1 (06h) <- quem fez T2 ontem = Op2
        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-02',
            'order' => 1,
            'user_id' => $operators[1]->id,
        ]);

        // Turno 2 (12h) <- quem fez T3 ontem = Op3
        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-02',
            'order' => 2,
            'user_id' => $operators[2]->id,
        ]);

        // Turno 3 (18h) <- quem fez T4 ontem = Op4
        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-02',
            'order' => 3,
            'user_id' => $operators[3]->id,
        ]);

        // Turno 4 (00h) <- quem estava de FOLGA ontem = Op5
        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-02',
            'order' => 4,
            'user_id' => $operators[4]->id,
        ]);

        // FOLGA <- quem fez T1 ontem = Op1
        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-02',
            'order' => 5,
            'user_id' => $operators[0]->id,
        ]);
    }

    public function test_skips_already_filled_days(): void
    {
        $operators = $this->createOperators(5);

        // Preenche dia 01 e dia 02
        $this->fillDayWith6hScale('2026-02-01', $operators);
        $this->fillDayWith6hScale('2026-02-02', $operators);

        // Tenta gerar do dia 01 ao 03
        $result = $this->generator->execute('2026-02-01', '2026-02-03');

        // Só deve preencher o dia 03 (01 e 02 já existem)
        $this->assertEquals(1, $result['days_filled']);
    }

    public function test_generates_multiple_days_in_sequence(): void
    {
        $operators = $this->createOperators(5);

        $this->fillDayWith6hScale('2026-02-01', $operators);

        // Gera 3 dias de uma vez
        $result = $this->generator->execute('2026-02-02', '2026-02-04');

        $this->assertEquals(3, $result['days_filled']);

        // Verifica que todos os dias foram criados
        $this->assertEquals(5, ScaleShift::where('date', '2026-02-02')->count());
        $this->assertEquals(5, ScaleShift::where('date', '2026-02-03')->count());
        $this->assertEquals(5, ScaleShift::where('date', '2026-02-04')->count());
    }

    public function test_returns_zero_when_no_days_need_filling(): void
    {
        $operators = $this->createOperators(5);

        $this->fillDayWith6hScale('2026-02-01', $operators);
        $this->fillDayWith6hScale('2026-02-02', $operators);
        $this->fillDayWith6hScale('2026-02-03', $operators);

        $result = $this->generator->execute('2026-02-01', '2026-02-03');

        $this->assertEquals(0, $result['days_filled']);
        $this->assertStringContainsString('Nenhum dia precisou ser preenchido', $result['message']);
    }
}
