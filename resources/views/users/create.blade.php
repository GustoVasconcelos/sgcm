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
            
            {{-- SELEÇÃO DE GRUPOS (ROLES) --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Grupos de Acesso</label>
                <div class="card p-3 border-0">
                    @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}">
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ $role->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">Selecione pelo menos um grupo.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold d-block">Configuração de Escala</label>
                <div class="form-check form-switch p-0 m-0">
                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_operator" value="1" id="isOperatorSwitch"
                        {{ (old('is_operator', true)) ? 'checked' : '' }}>
                        
                    <label class="form-check-label" for="isOperatorSwitch">
                        Este usuário participa da escala?
                        <div class="form-text mt-0">
                            Se marcado, o nome dele aparecerá nas opções para escalar turnos.
                        </div>
                    </label>
                </div>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-success">Salvar</button>
        </form>
    </div>
</div>
@endsection