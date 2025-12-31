@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 500px; margin: auto;">
    <div class="card-header">Nova Escala Semanal</div>
    <div class="card-body">
        <form action="{{ route('scales.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Data de Início (Segunda-Feira)</label>
                <input type="date" name="start_date" class="form-control" required>
                <small class="text-muted">O sistema calculará até Domingo automaticamente.</small>
            </div>
            
            <div class="mb-3">
                <label>Tipo de Escala</label>
                <select name="type" class="form-select">
                    <option value="normal">Normal (Turnos de 6h)</option>
                    <option value="ferias">Férias (Turnos Híbridos)</option>
                </select>
            </div>

            <button class="btn btn-primary w-100">Gerar Escala Vazia</button>
        </form>
    </div>
</div>
@endsection