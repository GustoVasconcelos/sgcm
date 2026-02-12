<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ScaleShift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria permissão necessária
        $permission = Permission::create(['name' => 'ver_escalas']);
        $role = Role::create(['name' => 'Operador']);
        $role->givePermissionTo($permission);

        $this->operatorUser = User::factory()->create(['is_operator' => true]);
        $this->operatorUser->assignRole('Operador');
    }

    public function test_dashboard_loads_without_error(): void
    {
        $response = $this->actingAs($this->operatorUser)
                         ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_dashboard_displays_today_shift(): void
    {
        $today = Carbon::today();
        
        // Cria turno de hoje (hora futura para aparecer como atual)
        ScaleShift::factory()->create([
            'user_id' => $this->operatorUser->id,
            'date' => $today,
            'name' => '14:00 - 20:00',
        ]);

        // Simula hora antes do turno (ex: 10:00)
        Carbon::setTestNow($today->copy()->setTime(10, 0));

        $response = $this->actingAs($this->operatorUser)
                         ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('14:00 - 20:00');
    }

    public function test_dashboard_displays_next_shift_when_today_is_over(): void
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // Turno de hoje que já passou
        ScaleShift::factory()->create([
            'user_id' => $this->operatorUser->id,
            'date' => $today,
            'name' => '08:00 - 14:00',
        ]);

        // Turno de amanhã
        ScaleShift::factory()->create([
            'user_id' => $this->operatorUser->id,
            'date' => $tomorrow,
            'name' => '14:00 - 20:00',
        ]);

        // Simula hora depois do turno de hoje (ex: 15:00)
        Carbon::setTestNow($today->copy()->setTime(15, 0));

        $response = $this->actingAs($this->operatorUser)
                         ->get(route('dashboard'));

        $response->assertStatus(200);
        // A lógica do blade mostra 'AMANHÃ' se for amanhã
        $response->assertSee('AMANHÃ');
    }

    public function test_dashboard_displays_return_shift_when_on_scale_off(): void
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        
        // Hoje é FOLGA
        ScaleShift::factory()->create([
            'user_id' => $this->operatorUser->id,
            'date' => $today,
            'name' => 'FOLGA',
        ]);

        // Amanhã tem turno
        ScaleShift::factory()->create([
            'user_id' => $this->operatorUser->id,
            'date' => $tomorrow,
            'name' => '08:00 - 14:00',
        ]);

        $response = $this->actingAs($this->operatorUser)
                         ->get(route('dashboard'));

        $response->assertStatus(200);
        // Deve mostrar retorno (data de amanhã)
        // Formato na blade: Date (DIA)
        // Como "Amanhã" é o retorno, o blade exibe "Amanhã" ao invés da data
        // Blade logic: {{ $isRetTomorrow ? 'Amanhã' : $dateReturn->format('d/m') }}
        $response->assertSee('Amanhã');
    }
}
