<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ScaleShift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ScaleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $operatorUser;
    protected User $guestUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar permissão necessária
        Permission::create(['name' => 'ver_escalas', 'guard_name' => 'web']);
        
        // Criar roles
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $operatorRole = Role::create(['name' => 'Operador', 'guard_name' => 'web']);
        $guestRole = Role::create(['name' => 'Visitante', 'guard_name' => 'web']);
        
        // Vincular permissão ao Admin e Operador
        $adminRole->givePermissionTo('ver_escalas');
        $operatorRole->givePermissionTo('ver_escalas');

        // Criar usuários
        $this->adminUser = User::factory()->create(['name' => 'Admin User']);
        $this->adminUser->assignRole('Admin');

        $this->operatorUser = User::factory()->operator()->create(['name' => 'Operador User']);
        $this->operatorUser->assignRole('Operador');

        $this->guestUser = User::factory()->create(['name' => 'Guest User']);
        $this->guestUser->assignRole('Visitante');
    }

    // =========================================================================
    // TESTES DE AUTENTICAÇÃO E AUTORIZAÇÃO
    // =========================================================================

    public function test_scale_index_requires_authentication(): void
    {
        $response = $this->get(route('scales.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_scale_index_requires_permission(): void
    {
        $response = $this->actingAs($this->guestUser)
                         ->get(route('scales.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_scale_index(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('scales.index'));

        $response->assertStatus(200);
        $response->assertViewIs('scales.index');
    }

    public function test_operator_can_access_scale_index(): void
    {
        $response = $this->actingAs($this->operatorUser)
                         ->get(route('scales.index'));

        $response->assertStatus(200);
    }

    // =========================================================================
    // TESTES DE VISUALIZAÇÃO DE ESCALA
    // =========================================================================

    public function test_scale_manage_validates_date_range(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('scales.manage', [
                             'start_date' => '2026-01-10',
                             'end_date' => '2026-01-05', // Data final ANTES da inicial
                         ]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Data inicial maior que final.');
    }

    public function test_scale_manage_limits_to_40_days(): void
    {
        // Simula que veio de uma página anterior (scales.index)
        $response = $this->actingAs($this->adminUser)
                         ->from(route('scales.index'))
                         ->get(route('scales.manage', [
                             'start_date' => '2026-01-01',
                             'end_date' => '2026-03-01', // Mais de 40 dias
                         ]));

        $response->assertRedirect(route('scales.index'));
        $response->assertSessionHas('error', 'Máximo 40 dias.');
    }

    public function test_scale_manage_shows_calendar_with_valid_dates(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('scales.manage', [
                             'start_date' => '2026-02-01',
                             'end_date' => '2026-02-07',
                         ]));

        $response->assertStatus(200);
        $response->assertViewIs('scales.edit');
        $response->assertViewHas('days');
        $response->assertViewHas('users');
    }

    // =========================================================================
    // TESTES DE SALVAMENTO DE ESCALA
    // =========================================================================

    public function test_scale_store_creates_shifts(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($this->adminUser)
                         ->post(route('scales.store'), [
                             'start_date' => '2026-02-01',
                             'end_date' => '2026-02-01',
                             'slots' => [
                                 '2026-02-01_1' => $operator->id,
                                 '2026-02-01_2' => $operator->id,
                             ],
                             'names' => [
                                 '2026-02-01_1' => '06:00 - 12:00',
                                 '2026-02-01_2' => '12:00 - 18:00',
                             ],
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-01',
            'order' => 1,
            'user_id' => $operator->id,
        ]);
    }

    public function test_scale_store_logs_changes(): void
    {
        $operator = User::factory()->operator()->create();

        // Primeiro, cria um turno
        ScaleShift::create([
            'date' => '2026-02-01',
            'order' => 1,
            'name' => '06:00 - 12:00',
            'user_id' => null,
        ]);

        // Agora atualiza (mudando de null para operator)
        $this->actingAs($this->adminUser)
             ->post(route('scales.store'), [
                 'start_date' => '2026-02-01',
                 'end_date' => '2026-02-01',
                 'slots' => [
                     '2026-02-01_1' => $operator->id,
                 ],
                 'names' => [
                     '2026-02-01_1' => '06:00 - 12:00',
                 ],
             ]);

        $this->assertDatabaseHas('action_logs', [
            'module' => 'Escalas',
            'action' => 'Salvar Alterações',
        ]);
    }

    // =========================================================================
    // TESTES DE REGENERAÇÃO DE DIA
    // =========================================================================

    public function test_regenerate_day_resets_to_6h_layout(): void
    {
        // Cria turnos de 8h
        ScaleShift::create(['date' => '2026-02-01', 'order' => 1, 'name' => '06:00 - 14:00', 'user_id' => null]);
        ScaleShift::create(['date' => '2026-02-01', 'order' => 2, 'name' => '14:00 - 22:00', 'user_id' => null]);

        $response = $this->actingAs($this->adminUser)
                         ->post(route('scales.day.regenerate'), [
                             'date' => '2026-02-01',
                             'mode' => '6h',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifica que agora tem 5 turnos (layout 6h)
        $this->assertEquals(5, ScaleShift::where('date', '2026-02-01')->count());
        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-01',
            'name' => '06:00 - 12:00',
        ]);
    }

    public function test_regenerate_day_resets_to_8h_layout(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->post(route('scales.day.regenerate'), [
                             'date' => '2026-02-01',
                             'mode' => '8h',
                         ]);

        $response->assertRedirect();

        // Verifica que tem 4 turnos (layout 8h)
        $this->assertEquals(4, ScaleShift::where('date', '2026-02-01')->count());
        $this->assertDatabaseHas('scale_shifts', [
            'date' => '2026-02-01',
            'name' => '06:00 - 14:00',
        ]);
    }
}
