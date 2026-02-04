<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\ActionLog;

class PermissionController extends Controller
{
    public function index()
    {
        // Lista as permissões ordenadas
        $permissions = Permission::orderBy('name')->paginate(15);
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name|min:3',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        ActionLog::register('Sistema', 'Criou Permissão Técnica', [
            'nome' => $permission->name
        ]);

        return redirect()->route('permissions.index')
                         ->with('success', 'Permissão criada! Lembre-se de implementá-la no código.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$permission->id,
        ]);

        $oldName = $permission->name;
        $permission->update(['name' => $request->name]);

        ActionLog::register('Sistema', 'Renomeou Permissão', [
            'de' => $oldName,
            'para' => $permission->name
        ]);

        return redirect()->route('permissions.index')
                         ->with('warning', 'Permissão renomeada. Verifique se o código-fonte foi atualizado!');
    }

    public function destroy(Permission $permission)
    {
        // Proteção: Não deixar apagar permissões críticas do sistema (opcional, mas recomendado)
        $criticals = ['acessar_painel_admin', 'gerenciar_usuarios'];
        
        if(in_array($permission->name, $criticals)) {
            return back()->with('error', 'Esta permissão é crítica para o sistema e não pode ser excluída.');
        }

        ActionLog::register('Sistema', 'Apagou Permissão', ['nome' => $permission->name]);
        
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permissão removida.');
    }
}