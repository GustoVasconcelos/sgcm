<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\ScaleShift;
use App\Services\ScaleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScaleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ScaleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ScaleService::class);
    }

    // =========================================================================
    // TESTES DE getScaleData
    // =========================================================================

    public function test_get_scale_data_returns_empty_days_when_no_shifts_exist(): void
    {
        $start = Carbon::parse('2026-02-01');
        $end = Carbon::parse('2026-02-03');

        $result = $this->service->getScaleData($start, $end);

        $this->assertCount(3, $result['days']); // 3 dias

        // Cada dia deve ter 5 turnos "fake"
        foreach ($result['days'] as $date => $shifts) {
            $this->assertCount(5, $shifts);
            // Verifica que são fake (exists = false)
            $this->assertFalse($shifts->first()->exists);
        }
    }

    public function test_get_scale_data_returns_existing_shifts(): void
    {
        $operator = User::factory()->operator()->create();

        ScaleShift::create([
            'date' => '2026-02-01',
            'order' => 1,
            'name' => '06:00 - 12:00',
            'user_id' => $operator->id,
        ]);

        $start = Carbon::parse('2026-02-01');
        $end = Carbon::parse('2026-02-01');

        $result = $this->service->getScaleData($start, $end);

        $shifts = $result['days']['2026-02-01'];
        $this->assertCount(1, $shifts);
        $this->assertEquals($operator->id, $shifts->first()->user_id);
    }

    // =========================================================================
    // TESTES DE getOperators
    // =========================================================================

    public function test_get_operators_returns_only_operators(): void
    {
        User::factory()->operator()->create(['name' => 'João Operador']);
        User::factory()->create(['name' => 'Maria Não Operadora']);

        $result = $this->service->getOperators();

        $this->assertCount(1, $result);
        $this->assertEquals('João Operador', $result->first()->name);
    }

    public function test_get_operators_excludes_nao_ha_user(): void
    {
        User::factory()->operator()->create(['name' => 'NÃO HÁ']);
        User::factory()->operator()->create(['name' => 'Operador Real']);

        $result = $this->service->getOperators();

        $this->assertCount(1, $result);
        $this->assertEquals('Operador Real', $result->first()->name);
    }

    public function test_get_operators_handles_duplicate_first_names(): void
    {
        User::factory()->operator()->create(['name' => 'João Silva']);
        User::factory()->operator()->create(['name' => 'João Santos']);
        User::factory()->operator()->create(['name' => 'Maria Oliveira']);

        $result = $this->service->getOperators();

        // Os dois "João" devem mostrar nome completo
        $joaoSilva = $result->firstWhere('name', 'João Silva');
        $joaoSantos = $result->firstWhere('name', 'João Santos');
        $maria = $result->firstWhere('name', 'Maria Oliveira');

        $this->assertEquals('João Silva', $joaoSilva->display_name);
        $this->assertEquals('João Santos', $joaoSantos->display_name);
        $this->assertEquals('MARIA', $maria->display_name); // Único, então uppercase
    }

    // =========================================================================
    // TESTES DE updateShifts
    // =========================================================================

    public function test_update_shifts_creates_new_records(): void
    {
        $operator = User::factory()->operator()->create();

        $slots = ['2026-02-01_1' => $operator->id];
        $names = ['2026-02-01_1' => '06:00 - 12:00'];

        $changedDays = $this->service->updateShifts($slots, $names);

        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-01',
            'order' => 1,
            'user_id' => $operator->id,
        ]);
    }

    public function test_update_shifts_returns_changed_days(): void
    {
        $operator = User::factory()->operator()->create();

        // Cria turno inicial sem operador
        ScaleShift::create([
            'date' => '2026-02-01',
            'order' => 1,
            'name' => '06:00 - 12:00',
            'user_id' => null,
        ]);

        // Atualiza com operador
        $slots = ['2026-02-01_1' => $operator->id];
        $names = ['2026-02-01_1' => '06:00 - 12:00'];

        $changedDays = $this->service->updateShifts($slots, $names);

        $this->assertContains('01/02', $changedDays);
    }

    public function test_update_shifts_returns_empty_when_no_changes(): void
    {
        $operator = User::factory()->operator()->create();

        // Cria turno com operador
        ScaleShift::create([
            'date' => '2026-02-01',
            'order' => 1,
            'name' => '06:00 - 12:00',
            'user_id' => $operator->id,
        ]);

        // "Atualiza" com o mesmo operador (sem mudança real)
        $slots = ['2026-02-01_1' => $operator->id];
        $names = ['2026-02-01_1' => '06:00 - 12:00'];

        $changedDays = $this->service->updateShifts($slots, $names);

        $this->assertEmpty($changedDays);
    }

    // =========================================================================
    // TESTES DE resetDay
    // =========================================================================

    public function test_reset_day_creates_6h_layout(): void
    {
        $this->service->resetDay('2026-02-01', '6h');

        $shifts = ScaleShift::where('date', '2026-02-01')->orderBy('order')->get();

        $this->assertCount(5, $shifts);
        $this->assertEquals('06:00 - 12:00', $shifts[0]->name);
        $this->assertEquals('12:00 - 18:00', $shifts[1]->name);
        $this->assertEquals('18:00 - 00:00', $shifts[2]->name);
        $this->assertEquals('00:00 - 06:00', $shifts[3]->name);
        $this->assertEquals('FOLGA', $shifts[4]->name);
    }

    public function test_reset_day_creates_8h_layout(): void
    {
        $this->service->resetDay('2026-02-01', '8h');

        $shifts = ScaleShift::where('date', '2026-02-01')->orderBy('order')->get();

        $this->assertCount(4, $shifts);
        $this->assertEquals('06:00 - 14:00', $shifts[0]->name);
        $this->assertEquals('14:00 - 22:00', $shifts[1]->name);
        $this->assertEquals('22:00 - 06:00', $shifts[2]->name);
        $this->assertEquals('FOLGA', $shifts[3]->name);
    }

    public function test_reset_day_deletes_existing_shifts_first(): void
    {
        // Cria turnos existentes
        ScaleShift::create(['date' => '2026-02-01', 'order' => 1, 'name' => 'Turno Antigo', 'user_id' => null]);
        ScaleShift::create(['date' => '2026-02-01', 'order' => 2, 'name' => 'Outro Turno', 'user_id' => null]);

        $this->service->resetDay('2026-02-01', '6h');

        // Não deve ter "Turno Antigo" mais
        $this->assertDatabaseMissing('scale_shifts', ['name' => 'Turno Antigo']);
        $this->assertDatabaseMissing('scale_shifts', ['name' => 'Outro Turno']);

        // Deve ter os novos turnos
        $this->assertEquals(5, ScaleShift::where('date', '2026-02-01')->count());
    }
}
