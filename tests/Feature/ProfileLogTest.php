<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ActionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileLogTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_logs_name_change(): void
    {
        $this->actingAs($this->user)
             ->put(route('profile.update'), [
                 'name' => 'New Name',
                 'email' => 'original@example.com',
             ]);

        $this->assertDatabaseHas('users', ['name' => 'New Name']);
        
        $this->assertDatabaseHas('action_logs', [
            'user_id' => $this->user->id,
            'module' => 'Perfil',
            'action' => 'Atualização',
        ]);

        $log = ActionLog::latest()->first();
        $this->assertStringContainsString("De 'Original Name' para 'New Name'", json_encode($log->details));
    }

    public function test_does_not_log_if_no_changes(): void
    {
        $this->actingAs($this->user)
             ->put(route('profile.update'), [
                 'name' => 'Original Name',
                 'email' => 'original@example.com',
             ]);

        $this->assertDatabaseCount('action_logs', 0);
    }

    public function test_logs_password_change_without_revealing_values(): void
    {
        $this->actingAs($this->user)
             ->put(route('profile.update'), [
                 'name' => 'Original Name',
                 'email' => 'original@example.com',
                 'password' => 'newpassword',
                 'password_confirmation' => 'newpassword',
             ]);

        $log = ActionLog::latest()->first();
        $this->assertStringContainsString('Senha alterada', json_encode($log->details));
        $this->assertStringNotContainsString('newpassword', json_encode($log->details));
    }
}
