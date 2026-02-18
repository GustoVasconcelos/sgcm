<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // Exibe o formulário com os dados do usuário logado
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // Processa a atualização
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validação
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::min(8) // Define um comprimento mínimo de 8 caracteres
                    ->mixedCase()    // Exige pelo menos uma letra maiúscula e uma minúscula
                    ->numbers()      // Exige pelo menos um número
                    ->symbols(),     // Exige pelo menos um caractere especial
            ],
        ]);

        $changes = [];

        // Verifica mudança de Nome
        if ($user->name !== $request->name) {
            $changes['nome'] = "De '{$user->name}' para '{$request->name}'";
            $user->name = $request->name;
        }

        // Verifica mudança de Email
        if ($user->email !== $request->email) {
            $changes['email'] = "De '{$user->email}' para '{$request->email}'";
            $user->email = $request->email;
        }

        // Verifica mudança de Senha
        if ($request->filled('password')) {
            $changes['senha'] = 'Senha alterada';
            $user->password = Hash::make($request->password);
        }

        // Se houve alguma alteração, salva e loga
        if (!empty($changes)) {
            $user->save();
            \App\Models\ActionLog::register('Perfil', 'Atualização', $changes);
            $message = 'Perfil atualizado com sucesso!';
        } else {
            $message = 'Nenhuma alteração realizada.';
        }

        return redirect()->route('profile.edit')->with('success', $message);
    }
}