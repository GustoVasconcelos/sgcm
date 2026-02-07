<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Schedule;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Carbon\Carbon;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $program;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria permissão e role
        $permission = Permission::create(['name' => 'ver_pgm_fds']);
        $role = Role::create(['name' => 'Admin']);
        $role->givePermissionTo($permission);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Admin');

        // Cria programa para testes
        $this->program = Program::factory()->create(['name' => 'Programa Teste']);
    }

    public function test_schedule_index_requires_authentication(): void
    {
        $response = $this->get(route('schedules.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_schedule_index_requires_permission(): void
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)
                         ->get(route('schedules.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_access_schedule_index(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('schedules.index'));

        $response->assertStatus(200);
        $response->assertViewIs('schedules.index');
    }

    public function test_can_store_new_schedule(): void
    {
        $saturday = Carbon::now()->next(Carbon::SATURDAY)->format('Y-m-d');

        $response = $this->actingAs($this->adminUser)
                         ->post(route('schedules.store'), [
                             'program_id' => $this->program->id,
                             'date' => $saturday,
                             'start_time' => '08:00',
                             'duration' => 60,
                             'custom_info' => 'Bloco 1',
                             'notes' => 'Observação teste',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'program_id' => $this->program->id,
            'date' => $saturday,
            'start_time' => '08:00',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->post(route('schedules.store'), []);

        $response->assertSessionHasErrors(['program_id', 'date', 'start_time', 'duration']);
    }

    public function test_can_update_schedule(): void
    {
        $schedule = Schedule::factory()->create([
            'program_id' => $this->program->id,
            'start_time' => '08:00',
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->put(route('schedules.update', $schedule), [
                             'program_id' => $this->program->id,
                             'date' => $schedule->date->format('Y-m-d'),
                             'start_time' => '10:00',
                             'duration' => 90,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'start_time' => '10:00',
            'duration' => 90,
        ]);
    }

    public function test_can_delete_schedule(): void
    {
        $schedule = Schedule::factory()->create([
            'program_id' => $this->program->id,
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->delete(route('schedules.destroy', $schedule));

        $response->assertRedirect();
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_can_toggle_mago_status(): void
    {
        $schedule = Schedule::factory()->create([
            'program_id' => $this->program->id,
            'status_mago' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->post(route('schedules.toggle', [$schedule->id, 'mago']));

        $response->assertJson(['success' => true, 'new_status' => true]);
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'status_mago' => true,
        ]);
    }

    public function test_can_toggle_verification_status(): void
    {
        $schedule = Schedule::factory()->create([
            'program_id' => $this->program->id,
            'status_verification' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->post(route('schedules.toggle', [$schedule->id, 'verification']));

        $response->assertJson(['success' => true, 'new_status' => true]);
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'status_verification' => true,
        ]);
    }

    public function test_clone_fails_when_target_date_has_schedules(): void
    {
        $saturday = Carbon::now()->next(Carbon::SATURDAY);
        
        // Cria schedule no sábado alvo
        Schedule::factory()->create([
            'program_id' => $this->program->id,
            'date' => $saturday->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->from(route('schedules.index'))
                         ->post(route('schedules.clone'), [
                             'target_date' => $saturday->format('Y-m-d'),
                         ]);

        $response->assertRedirect(route('schedules.index'));
        $response->assertSessionHas('error');
    }
}
