<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StudioTimerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configura permissões
        $permView = Permission::create(['name' => 'ver_regressiva']);
        $permOperate = Permission::create(['name' => 'operar_regressiva']);
        
        $role = Role::create(['name' => 'Operador']);
        $role->givePermissionTo($permView);
        $role->givePermissionTo($permOperate);

        $this->operatorUser = User::factory()->create();
        $this->operatorUser->assignRole('Operador');
    }

    public function test_operator_can_access_timer_page(): void
    {
        $response = $this->actingAs($this->operatorUser)
                         ->get(route('timers.operator'));

        $response->assertStatus(200);
        $response->assertViewIs('timers.operator');
    }

    public function test_status_api_returns_json_structure(): void
    {
        $response = $this->actingAs($this->operatorUser)
                         ->get('/timers/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'server_time',
            'target_time',
            'bk_target_time',
            'mode_label',
            'status_color',
            'stopwatch' => [
                'status',
                'started_at',
                'accumulated'
            ]
        ]);
    }

    public function test_update_regressive_api(): void
    {
        $response = $this->actingAs($this->operatorUser)
                         ->postJson('/timers/update-regressive', [
                             'target_hour' => '20:00:00',
                             'mode_label' => 'AOVIVO'
                         ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_stopwatch_api(): void
    {
        $response = $this->actingAs($this->operatorUser)
                         ->postJson('/timers/update-stopwatch', [
                             'action' => 'start'
                         ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
