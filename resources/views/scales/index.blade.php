@extends('layout')

@section('content')
<div class="card shadow" style="max-width: 800px; margin: 50px auto;">
    <div class="card-header text-white">
        <h4 class="mb-0"><i class="bi bi-calendar-range"></i> Gerenciador de Escalas</h4>
    </div>
    <div class="card-body">
        <p>Selecione o intervalo de datas que deseja visualizar ou editar.</p>
        
        <form action="{{ route('scales.index') }}" method="GET">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Data Inicial</label>
                    <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Data Final</label>
                    <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-d', strtotime('+6 days')) }}">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2">
                Carregar Agenda <i class="bi bi-arrow-right"></i>
            </button>
        </form>
    </div>
</div>
@endsection