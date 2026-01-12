@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 800px; margin: auto;">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Editar Férias - {{ $vacation->user->name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('vacations.update', $vacation->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Ano Referência</label>
                    <input type="number" name="year" class="form-control" value="{{ $vacation->year }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Modalidade</label>
                    <select name="mode" id="modeSelector" class="form-select" required onchange="updateForm()">
                        <option value="30_dias" {{ $vacation->mode == '30_dias' ? 'selected' : '' }}>30 Dias Corridos</option>
                        <option value="15_15" {{ $vacation->mode == '15_15' ? 'selected' : '' }}>2 Períodos de 15 Dias (15/15)</option>
                        <option value="20_10" {{ $vacation->mode == '20_10' ? 'selected' : '' }}>2 Períodos de 20 e 10 Dias (20/10)</option>
                        <option value="20_venda" {{ $vacation->mode == '20_venda' ? 'selected' : '' }}>20 Dias + Venda</option>
                    </select>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title text-primary">Primeiro Período</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Início</label>
                            <input type="date" name="period_1_start" class="form-control" value="{{ $vacation->period_1_start }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fim</label>
                            <input type="date" name="period_1_end" class="form-control" value="{{ $vacation->period_1_end }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 d-none" id="period2_box">
                <div class="card-body">
                    <h6 class="card-title text-primary">Segundo Período</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Início</label>
                            <input type="date" name="period_2_start" class="form-control" value="{{ $vacation->period_2_start }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fim</label>
                            <input type="date" name="period_2_end" class="form-control" value="{{ $vacation->period_2_end }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('vacations.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Atualizar</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('js/vacation.js') }}"></script>
@endpush
@endsection