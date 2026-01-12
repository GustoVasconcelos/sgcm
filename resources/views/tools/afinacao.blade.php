@extends('layout')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/afinacao.css') }}">
@endpush

<div class="row mb-3">
    <div class="col-12">
        <h3 class="fw-bold"><i class="bi bi-stopwatch"></i> Afinação de Jornal</h3>
        <small class="text-muted">Atalhos: <b>F1</b> Topo | <b>F2</b> Próximo | <b>F3</b> Anterior | <b>F4</b> Excluir Linha | <b>F5</b> Inserir Linha</small>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Tempos das Laudas</span>
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <input type="number" id="bulkCount" class="form-control" placeholder="Qtd Linhas" value="20">
                    <button class="btn btn-primary" onclick="generateRows()">Gerar Linhas</button>
                </div>
            </div>
            <div class="card-body">
                <div id="rowsContainer"></div>
                
                <div class="mt-3 text-center">
                    <button class="btn btn-outline-secondary btn-sm" onclick="addRow()">+ Adicionar 1 Linha</button>
                    <button class="btn btn-outline-danger btn-sm ms-2" onclick="clearAll()">Limpar Tudo</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="totals-panel">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold text-center">SOMA TOTAL</div>
                <div class="card-body text-center p-2">
                    <div id="displaySum" class="total-display">00:00:00</div>
                </div>
            </div>

            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-secondary text-white fw-bold text-center">TEMPO LIMITE</div>
                <div class="card-body p-2">
                    <input type="text" id="targetInput" class="form-control form-control-lg text-center fw-bold" 
                           placeholder="00:00:00" oninput="formatInput(this);"
                           style="font-size: 1.5rem;">
                </div>
            </div>

            <div class="card shadow-lg" id="resultCard">
                <div class="card-header fw-bold text-center" id="resultTitle">DEFINA O TEMPO LIMITE</div>
                <div class="card-body text-center p-3">
                    <div id="displayDiff" class="total-display">--:--:--</div>
                    <small id="resultMessage" class="fw-bold text-uppercase mt-2 d-block">Defina o tempo limite</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/afinacao.js') }}"></script>
@endpush
@endsection