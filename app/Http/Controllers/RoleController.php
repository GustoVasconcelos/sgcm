<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ActionLog;

class RoleController extends Controller
{
    public function index()
    {
        // Lista todos os grupos (menos o Admin se quiser esconder, mas melhor mostrar tudo)
        $roles = Role::with('permissions')->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        // Precisamos de todas as permissões para montar os checkboxes
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            // Converte IDs em objetos/nomes e sincroniza
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        ActionLog::register('Admin', 'Criar Grupo', [
            'grupo' => $role->name,
            'permissoes' => $role->getPermissionNames()
        ]);

        return redirect()->route('roles.index')->with('success', 'Grupo criado com sucesso!');
    }

    public function edit(Role $role)
    {
        // Proteção: Não deixar editar o nome do Admin para não quebrar o sistema
        if($role->name === 'Admin') {
            return redirect()->route('roles.index')->with('warning', 'O grupo Admin não pode ser editado, ele possui acesso total automático.');
        }

        $permissions = Permission::all();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        if($role->name === 'Admin') abort(403);

        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'nullable|array'
        ]);

        $oldPermissions = $role->getPermissionNames();

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            // Se desmarcou tudo
            $role->syncPermissions([]);
        }

        ActionLog::register('Admin', 'Editar Grupo', [
            'grupo' => $role->name,
            'permissoes_anteriores' => $oldPermissions,
            'permissoes_novas' => $role->getPermissionNames()
        ]);

        return redirect()->route('roles.index')->with('success', 'Grupo atualizado!');
    }

    public function destroy(Role $role)
    {
        if($role->name === 'Admin') {
            return back()->with('error', 'Você não pode excluir o Admin!');
        }

        // Verifica se tem usuários nesse grupo
        if($role->users()->count() > 0) {
            return back()->with('error', 'Não é possível excluir este grupo pois existem usuários vinculados a ele.');
        }

        ActionLog::register('Admin', 'Excluir Grupo', ['grupo' => $role->name]);
        
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Grupo excluído!');
    }
}