@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 600px; margin: auto;">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Novo Programa</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('programs.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Nome do Programa</label>
                <input type="text" name="name" class="form-control" placeholder="Ex: AGRO BAND" required autofocus>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Duração Padrão (Minutos)</label>
                    <input type="number" name="default_duration" class="form-control" value="30" required>
                    <small class="text-muted">Isso preencherá automaticamente na grade.</small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cor de Identificação</label>
                    <input type="color" name="color" class="form-control form-control-color w-100" value="#563d7c" title="Escolha uma cor">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('programs.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Salvar Programa</button>
            </div>
        </form>
    </div>
</div>
@endsection