<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('name', '!=', 'NAO HA')
                     ->with('roles')
                     ->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $isOperator = $request->has('is_operator');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_operator' => $isOperator,
        ]);

        // Busca os objetos Role pelos IDs enviados no formulário
        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);

        // --- LOG: Criar Usuário ---
        ActionLog::register('Usuários', 'Criar Usuário', [
            'nome_criado' => $user->name,
            'grupos' => $user->getRoleNames(),
            'participa_escala' => $isOperator ? 'Sim' : 'Não'
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_operator' => $request->has('is_operator'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->fill($data);

        $basicChanges = $user->isDirty();
        $changes = $user->getDirty();
        
        $user->save();

        // --- CORREÇÃO AQUI ---
        // 1. Pega os nomes atuais
        $currentRoles = $user->getRoleNames()->toArray();
        
        // 2. Transforma os IDs do formulário em NOMES (ex: [1, 2] vira ['Admin', 'Operador'])
        $newRoles = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
        
        // 3. Sincroniza usando os NOMES (Agora funciona!)
        $user->syncRoles($newRoles);

        // Lógica de Log
        $rolesChanged = array_diff($currentRoles, $newRoles) || array_diff($newRoles, $currentRoles);

        if ($basicChanges || $rolesChanged) {
            
            if (isset($changes['password'])) $changes['password'] = '*** Senha Alterada ***';
            if (isset($changes['is_operator'])) $changes['is_operator'] = $changes['is_operator'] ? 'Sim' : 'Não';
            
            if ($rolesChanged) {
                $changes['grupos_anteriores'] = implode(', ', $currentRoles);
                $changes['grupos_novos'] = implode(', ', $newRoles);
            }

            ActionLog::register('Usuários', 'Alterar Usuário', [
                'usuario_alvo' => $user->name,
                'campos_alterados' => $changes
            ]);
            
            return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
        }

        return redirect()->route('users.index')->with('info', 'Nenhuma alteração realizada.');
    }

    public function destroy(User $user)
    {
        ActionLog::register('Usuários', 'Excluir Usuário', [
            'usuario_excluido' => $user->name,
            'grupos' => $user->getRoleNames(),
            'participava_escala' => $user->is_operator ? 'Sim' : 'Não'
        ]);

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}