@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 600px; margin: auto;">
    <div class="card-header">
        <h5 class="mb-0">Novo Usuário</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar Senha</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Perfil</label>
                <select name="profile" class="form-select" required>
                    <option value="user">Operador</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-success">Salvar</button>
        </form>
    </div>
</div>
@endsection