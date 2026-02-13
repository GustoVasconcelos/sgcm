<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ActionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_register_log(): void
    {
        $logData = [
            'module' => 'Teste',
            'action' => 'Executou algo',
            'details' => ['foo' => 'bar']
        ];

        $response = $this->actingAs($this->user)
                         ->postJson(route('log.register'), $logData);

        $response->assertStatus(201);
        $response->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('action_logs', [
            'user_id' => $this->user->id,
            'module' => 'Teste',
            'action' => 'Executou algo'
        ]);
    }

    public function test_log_registration_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
                         ->postJson(route('log.register'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['module', 'action']);
    }

    public function test_guest_cannot_register_log(): void
    {
        $response = $this->postJson(route('log.register'), [
            'module' => 'Teste',
            'action' => 'Ação Inválida'
        ]);

        $response->assertStatus(401);
    }
}
