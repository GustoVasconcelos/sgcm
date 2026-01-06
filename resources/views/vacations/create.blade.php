@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 800px; margin: 50px auto;">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Cadastrar Férias</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('vacations.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Ano de Referência</label>
                <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Modalidade das Férias</label>
                <select name="mode" id="modeSelector" class="form-select" required onchange="updateForm()">
                    <option value="" selected disabled>Selecione uma opção...</option>
                    <option value="30_dias">30 Dias Corridos</option>
                    <option value="15_15">2 Períodos de 15 Dias (15/15)</option>
                    <option value="20_10">2 Períodos de 20 e 10 Dias (20/10)</option>
                    <option value="20_venda">1 Período de 20 Dias + Venda</option>
                </select>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title text-primary">Primeiro Período</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Início</label>
                            <input type="date" name="period_1_start" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fim</label>
                            <input type="date" name="period_1_end" class="form-control" required>
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
                            <input type="date" name="period_2_start" id="p2_start" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fim</label>
                            <input type="date" name="period_2_end" id="p2_end" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('vacations.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Cadastrar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateForm() {
        const mode = document.getElementById('modeSelector').value;
        const box2 = document.getElementById('period2_box');
        
        // Pega inputs do período 2 para controlar o 'required'
        const p2Inputs = box2.querySelectorAll('input');

        // Modos que usam APENAS 1 período
        if (mode === '30_dias' || mode === '20_venda') {
            box2.classList.add('d-none'); // Esconde o 2º
            removeRequired(p2Inputs);
        } 
        // Modos que usam 2 períodos (15/15 OU 20/10)
        else if (mode === '15_15' || mode === '20_10') {
            box2.classList.remove('d-none'); // Mostra o 2º
            setRequired(p2Inputs);
        }
    }

    function setRequired(inputs) {
        inputs.forEach(input => input.setAttribute('required', 'required'));
    }

    function removeRequired(inputs) {
        inputs.forEach(input => {
            input.removeAttribute('required');
            input.value = ''; // Limpa o valor se esconder
        });
    }
</script>
@endsection