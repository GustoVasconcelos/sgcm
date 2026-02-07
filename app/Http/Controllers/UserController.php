<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActionLog;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
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

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $isOperator = $request->has('is_operator');

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_operator' => $isOperator,
        ]);

        $roles = Role::whereIn('id', $validated['roles'])->get();
        $user->syncRoles($roles);

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

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_operator' => $request->has('is_operator'),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->fill($data);

        $basicChanges = $user->isDirty();
        $changes = $user->getDirty();
        
        $user->save();

        $currentRoles = $user->getRoleNames()->toArray();
        $newRoles = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        
        $user->syncRoles($newRoles);

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