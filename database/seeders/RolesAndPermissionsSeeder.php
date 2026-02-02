<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'acessar_painel_admin',
            'gerenciar_usuarios',
            'ver_logs',
            'operar_regressiva',
            'ver_regressiva',
            'ver_escalas',
            'ver_ferias',
            'ver_pgm_fds',
            'usar_afinacao'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleOperador = Role::firstOrCreate(['name' => 'Operador']);
        $roleOperador->givePermissionTo([
            'operar_regressiva',
            'ver_regressiva',
            'ver_escalas',
            'ver_ferias',
            'ver_pgm_fds',
            'usar_afinacao'
        ]);

        $roleViewer = Role::firstOrCreate(['name' => 'Viewer']);
        $roleViewer->givePermissionTo(['ver_regressiva']);

        $users = User::all();
        foreach ($users as $user) {
            $user->roles()->detach();

            if ($user->profile === 'admin') {
                $user->assignRole('Admin');
            } 
            elseif ($user->profile === 'viewer') {
                $user->assignRole('Viewer');
            } 
            elseif ($user->is_operator) {
                $user->assignRole('Operador');
            }
        }
    }
}