@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 600px; margin: auto;">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Editar Programa</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('programs.update', $program->id) }}" method="POST">
            @csrf @method('PUT')
            
            <div class="mb-3">
                <label class="form-label">Nome do Programa</label>
                <input type="text" name="name" class="form-control" value="{{ $program->name }}" required>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Duração Padrão (Minutos)</label>
                    <input type="number" name="default_duration" class="form-control" value="{{ $program->default_duration }}" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cor de Identificação</label>
                    <input type="color" name="color" class="form-control form-control-color w-100" value="{{ $program->color ?? '#cccccc' }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('programs.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection