@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 500px; margin: auto;">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="bi bi-file-earmark-spreadsheet"></i> Relatório de Escalas (RH)</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.rh.generate') }}" method="POST" target="_blank">
            @csrf
            
            <div class="alert alert-info">
                Selecione o período desejado. O relatório buscará todos os turnos cadastrados nestas datas.
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Data Inicial</label>
                <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-13', strtotime('last month')) }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Data Final</label>
                <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-12') }}">
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-printer"></i> Gerar PDF
            </button>
        </form>
    </div>
</div>
@endsection