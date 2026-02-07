<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class VacationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria permissão e role
        $permission = Permission::create(['name' => 'ver_ferias']);
        $role = Role::create(['name' => 'Admin']);
        $role->givePermissionTo($permission);

        // Usuário Admin
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Admin');

        // Usuário normal (com permissão de ver férias)
        $normalRole = Role::create(['name' => 'Funcionario']);
        $normalRole->givePermissionTo($permission);
        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole('Funcionario');
    }

    public function test_vacation_index_requires_authentication(): void
    {
        $response = $this->get(route('vacations.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_vacation_index_requires_permission(): void
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)
                         ->get(route('vacations.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_access_vacation_index(): void
    {
        $response = $this->actingAs($this->normalUser)
                         ->get(route('vacations.index'));

        $response->assertStatus(200);
        $response->assertViewIs('vacations.index');
    }

    public function test_user_can_create_vacation(): void
    {
        $response = $this->actingAs($this->normalUser)
                         ->post(route('vacations.store'), [
                             'year' => date('Y'),
                             'mode' => '30_dias',
                             'period_1_start' => date('Y') . '-06-01',
                             'period_1_end' => date('Y') . '-06-30',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vacations', [
            'user_id' => $this->normalUser->id,
            'year' => date('Y'),
            'mode' => '30_dias',
        ]);
    }

    public function test_user_cannot_create_duplicate_vacation_for_same_year(): void
    {
        // Cria primeira férias
        Vacation::factory()->create([
            'user_id' => $this->normalUser->id,
            'year' => date('Y'),
        ]);

        // Tenta criar outra para o mesmo ano
        $response = $this->actingAs($this->normalUser)
                         ->post(route('vacations.store'), [
                             'year' => date('Y'),
                             'mode' => '30_dias',
                             'period_1_start' => date('Y') . '-07-01',
                             'period_1_end' => date('Y') . '-07-30',
                         ]);

        $response->assertSessionHasErrors('year');
    }

    public function test_user_can_edit_own_vacation(): void
    {
        $vacation = Vacation::factory()->create([
            'user_id' => $this->normalUser->id,
            'year' => date('Y'),
        ]);

        $response = $this->actingAs($this->normalUser)
                         ->get(route('vacations.edit', $vacation));

        $response->assertStatus(200);
        $response->assertViewIs('vacations.edit');
    }

    public function test_user_cannot_edit_other_user_vacation(): void
    {
        $otherUser = User::factory()->create();
        $vacation = Vacation::factory()->create([
            'user_id' => $otherUser->id,
            'year' => date('Y'),
        ]);

        $response = $this->actingAs($this->normalUser)
                         ->get(route('vacations.edit', $vacation));

        $response->assertRedirect(route('vacations.index'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_edit_any_vacation(): void
    {
        $vacation = Vacation::factory()->create([
            'user_id' => $this->normalUser->id,
            'year' => date('Y'),
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get(route('vacations.edit', $vacation));

        $response->assertStatus(200);
    }

    public function test_user_can_update_own_vacation(): void
    {
        $vacation = Vacation::factory()->create([
            'user_id' => $this->normalUser->id,
            'year' => date('Y'),
            'mode' => '30_dias',
        ]);

        $response = $this->actingAs($this->normalUser)
                         ->put(route('vacations.update', $vacation), [
                             'year' => date('Y'),
                             'mode' => '15_15',
                             'period_1_start' => date('Y') . '-06-01',
                             'period_1_end' => date('Y') . '-06-15',
                             'period_2_start' => date('Y') . '-12-01',
                             'period_2_end' => date('Y') . '-12-15',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vacations', [
            'id' => $vacation->id,
            'mode' => '15_15',
        ]);
    }

    public function test_user_can_delete_own_vacation(): void
    {
        $vacation = Vacation::factory()->create([
            'user_id' => $this->normalUser->id,
            'year' => date('Y'),
        ]);

        $response = $this->actingAs($this->normalUser)
                         ->delete(route('vacations.destroy', $vacation));

        $response->assertRedirect();
        $this->assertSoftDeleted('vacations', ['id' => $vacation->id]);
    }

    public function test_user_cannot_delete_other_user_vacation(): void
    {
        $otherUser = User::factory()->create();
        $vacation = Vacation::factory()->create([
            'user_id' => $otherUser->id,
            'year' => date('Y'),
        ]);

        $response = $this->actingAs($this->normalUser)
                         ->delete(route('vacations.destroy', $vacation));

        $response->assertStatus(403);
        $this->assertDatabaseHas('vacations', ['id' => $vacation->id]);
    }
}
