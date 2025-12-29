@extends('layout')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h2 class="text-danger fw-bold"><i class="bi bi-shield-lock"></i> Painel Administrativo</h2>
        <p class="text-muted">Bem-vindo ao centro de comando do SGCM.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3 shadow">
            <div class="card-header">Usuários</div>
            <div class="card-body">
                <h5 class="card-title">{{ \App\Models\User::count() }} Cadastrados</h5>
                <p class="card-text">Gerencie acessos, senhas e perfis.</p>
                <a href="{{ route('users.index') }}" class="btn btn-light text-primary fw-bold">Gerenciar Usuários</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-white bg-secondary mb-3 shadow">
            <div class="card-header">Programas</div>
            <div class="card-body">
                <h5 class="card-title">{{ \App\Models\Program::count() }} Cadastrados</h5>
                <p class="card-text">Gerencie programas.</p>
                <a href="{{ route('programs.index') }}" class="btn btn-light text-primary fw-bold">Gerenciar Programas</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-white bg-success mb-3 shadow">
            <div class="card-header">Sistema</div>
            <div class="card-body">
                <h5 class="card-title">Status: Online</h5>
                <p class="card-text">Verifique logs e atividades recentes.</p>
                <button class="btn btn-light text-success fw-bold">Ver Logs</button>
            </div>
        </div>
    </div>
</div>
@endsection