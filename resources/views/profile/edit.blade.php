@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h3 class="mb-4"><i class="bi bi-person-circle"></i> Meu Perfil</h3>

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    Informações Básicas
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Perfil de Acesso</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->getRoleNames()->join(', ') }}" readonly>
                        <small class="text-muted">O nível de acesso não pode ser alterado por aqui.</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-header bg-warning-subtle fw-bold">
                    <i class="bi bi-lock"></i> Alterar Senha
                </div>
                <div class="card-body">
                    <div class="alert alert-light border mb-3">
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Deixe os campos abaixo em branco caso não queira alterar sua senha atual.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar Nova Senha</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repita a senha">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>
@endsection