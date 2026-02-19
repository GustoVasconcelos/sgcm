<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpa o cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Criar Permissões
        $permissions = [
            'acessar_painel_admin', 'gerenciar_usuarios', 'ver_logs',
            'operar_regressiva', 'ver_regressiva',
            'ver_escalas', 'ver_ferias', 'ver_pgm_fds', 'usar_afinacao'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Criar Roles
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleOperador = Role::firstOrCreate(['name' => 'Operador']);
        $roleOperador->givePermissionTo([
            'operar_regressiva', 'ver_regressiva', 'ver_escalas', 
            'ver_ferias', 'ver_pgm_fds', 'usar_afinacao'
        ]);

        $roleViewer = Role::firstOrCreate(['name' => 'Viewer']);
        $roleViewer->givePermissionTo(['ver_regressiva']);

        // 4. RESTAURAÇÃO DE USUÁRIOS (Backup)
        $backupPath = database_path('users_backup.json');

        if (File::exists($backupPath)) {
            $this->command->info('Arquivo de backup encontrado! Restaurando usuários...');
            
            $oldUsers = json_decode(File::get($backupPath), true);

            foreach ($oldUsers as $oldUser) {
                
                // CORREÇÃO: Define uma senha padrão caso não exista no backup
                $password = isset($oldUser['password']) ? $oldUser['password'] : Hash::make('12345678');
                
                // Previne erro caso o campo profile não exista no JSON
                $oldProfile = $oldUser['profile'] ?? 'user'; 

                // Cria o usuário na estrutura nova
                $user = User::create([
                    'id' => $oldUser['id'], 
                    'name' => $oldUser['name'],
                    'email' => $oldUser['email'],
                    'password' => $password, // Usa a senha tratada acima
                    'is_operator' => ($oldProfile !== 'viewer'),
                    'created_at' => $oldUser['created_at'] ?? now(),
                    'updated_at' => $oldUser['updated_at'] ?? now(),
                ]);

                // Converte o Perfil antigo em Role novo
                if ($oldProfile === 'admin') {
                    $user->assignRole($roleAdmin);
                } elseif ($oldProfile === 'viewer') {
                    $user->assignRole($roleViewer);
                } else {
                    $user->assignRole($roleOperador);
                }
            }
            $this->command->info(count($oldUsers) . ' usuários restaurados.');
            $this->command->warn('ATENÇÃO: Como o backup não continha as senhas (segurança do Laravel), todos os usuários recuperados estão com a senha "12345678". Peça para eles alterarem.');
        } else {
            // Se não tiver backup, cria um Admin padrão
            $this->command->warn('Nenhum backup encontrado. Criando usuário Admin padrão.');
            
            $admin = User::create([
                'name' => 'Administrador',
                'email' => 'admin@band.com.br',
                'password' => Hash::make('12345678'),
                'is_operator' => false,
            ]);
            $admin->assignRole($roleAdmin);
            
            $this->command->warn('Criando usuário NAO HA. Esse usuario é de uso interno do sistema, logo, sua senha é gerada de maneira aleatoria.');
            // Cria o Usuario NÃO HÁ
            User::create([
                'name' => 'NÃO HÁ',
                'email' => 'naoha@sistema.com.br',
                'password' => Hash::make(Str::password()),
                'is_operator' => true,
            ]);
        }
    }
}