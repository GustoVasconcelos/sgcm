<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Excluir o "NAO HA" da busca
        $users = User::where('name', '!=', 'NAO HA')
                     ->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile' => 'required|in:admin,user,viewer',
        ]);

        $isOperator = $request->has('is_operator');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile' => $request->profile,
            'is_operator' => $isOperator,
        ]);

        // --- LOG: Criar Usuário ---
        ActionLog::register('Usuários', 'Criar Usuário', [
            'nome_criado' => $user->name,
            'perfil' => $user->profile,
            'participa_escala' => $isOperator ? 'Sim' : 'Não'
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile' => 'required|in:admin,user,viewer',
        ]);

        // Prepara os dados para atualização
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'profile' => $request->profile,
            'is_operator' => $request->has('is_operator'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Preenche o model com os novos dados (sem salvar ainda) para comparar
        $user->fill($data);

        // --- LOG: Alterar Usuário (Dirty Checking) ---
        if ($user->isDirty()) {
            
            // Pega o que mudou
            $changes = $user->getDirty();

            // TRATAMENTO DE SEGURANÇA E LEGIBILIDADE:
            
            // 1. Se a senha mudou, não salva o Hash, salva apenas um aviso
            if (isset($changes['password'])) {
                $changes['password'] = '*** Senha Alterada ***';
            }

            // 2. Se o status de operador mudou, converte para texto
            if (isset($changes['is_operator'])) {
                $changes['is_operator'] = $changes['is_operator'] ? 'Sim' : 'Não';
            }

            ActionLog::register('Usuários', 'Alterar Usuário', [
                'usuario_alvo' => $user->name, // Nome atual (ou novo se tiver mudado)
                'campos_alterados' => $changes
            ]);

            $user->save(); // Salva efetivamente
            
            return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
        }

        // Se não houve mudança, apenas redireciona
        return redirect()->route('users.index')->with('info', 'Nenhuma alteração realizada.');
    }

    public function destroy(User $user)
    {
        // --- LOG: Excluir Usuário (Captura antes de deletar) ---
        ActionLog::register('Usuários', 'Excluir Usuário', [
            'usuario_excluido' => $user->name,
            'email' => $user->email,
            'perfil' => $user->profile,
            'participava_escala' => $user->is_operator ? 'Sim' : 'Não'
        ]);

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}