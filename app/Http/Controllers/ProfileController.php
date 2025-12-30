<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            // O 'unique' aqui ignora o ID do próprio usuário para não dar erro se ele mantiver o mesmo email
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            // Senha é opcional (nullable), só valida se o usuário digitar algo
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Atualiza dados básicos
        $user->name = $request->name;
        $user->email = $request->email;

        // Se digitou senha nova, atualiza o hash
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    }
}