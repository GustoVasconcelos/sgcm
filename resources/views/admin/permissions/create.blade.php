@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 600px; margin: auto;">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Registrar Nova Permissão Técnica</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('permissions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Chave da Permissão (Slug)</label>
                <input type="text" name="name" class="form-control font-monospace" placeholder="ex: exportar_relatorio_pdf" required>
                <div class="form-text text-danger">
                    Use letras minúsculas e _ (underline). Não use espaços ou acentos.
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>
    </div>
</div>
@endsection