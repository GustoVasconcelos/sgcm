@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 600px; margin: auto;">
    <div class="card-header">
        <h5 class="mb-0">Editar Usuário</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nova Senha (deixe em branco para manter)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar Nova Senha</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Perfil</label>
                <select name="profile" class="form-select" required>
                    <option value="user" {{ $user->profile == 'user' ? 'selected' : '' }}>Operador</option>
                    <option value="admin" {{ $user->profile == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-success">Atualizar</button>
        </form>
    </div>
</div>
@endsection