@extends('layout')

@section('content')
<div class="card shadow-sm border-warning" style="max-width: 600px; margin: auto;">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Renomear Permissão (Cuidado!)</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-circle-fill"></i> Se você mudar este nome, lembre-se de dar Find/Replace no código do projeto.
        </div>
        <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-bold">Chave da Permissão</label>
                <input type="text" name="name" value="{{ $permission->name }}" class="form-control font-monospace" required>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Salvar Alteração</button>
            </div>
        </form>
    </div>
</div>
@endsection