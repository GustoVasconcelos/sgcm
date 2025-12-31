@extends('layout')

@section('content')
<form action="{{ route('scales.update', $scale->id) }}" method="POST" id="mainForm">
    @csrf @method('PUT')
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Preencher Escala: {{ $scale->start_date->format('d/m') }} a {{ $scale->end_date->format('d/m') }}</h4>
        <div>
            <a href="{{ route('scales.index') }}" class="btn btn-secondary me-2">Voltar</a>
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
        </div>
    </div>

    <div class="row">
        @foreach($days as $dateString => $shifts)
            @php $date = \Carbon\Carbon::parse($dateString); @endphp
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    
                    <div class="card-header d-flex justify-content-between align-items-center text-white 
                        {{ $date->isToday() ? 'bg-primary' : 'bg-dark' }}">
                        
                        <span class="fw-bold">
                            {{ $date->format('d/m') }} - {{ mb_strtoupper($date->locale('pt_BR')->dayName, 'UTF-8') }}
                        </span>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-white text-decoration-none p-0" type="button" data-bs-toggle="dropdown" title="Alterar Layout do Dia">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><h6 class="dropdown-header">Layout do Turno</h6></li>
                                
                                <li>
                                    <button type="button" class="dropdown-item d-flex justify-content-between align-items-center" 
                                            onclick="regenerateDay('{{ $dateString }}', '6h')">
                                        <span>Padrão (6h)</span>
                                        @if($shifts->count() >= 5) <i class="bi bi-check text-success"></i> @endif
                                    </button>
                                </li>

                                <li>
                                    <button type="button" class="dropdown-item d-flex justify-content-between align-items-center"
                                            onclick="regenerateDay('{{ $dateString }}', '8h')">
                                        <span>Férias (8h)</span>
                                        @if($shifts->count() < 5) <i class="bi bi-check text-success"></i> @endif
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body p-2">
                        <table class="table table-sm table-borderless mb-0">
                            @foreach($shifts as $shift)
                            <tr>
                                <td style="width: 40%; vertical-align: middle;">
                                    <span class="badge {{ $shift->name == 'FOLGA' ? 'bg-warning text-dark' : 'bg-secondary' }} w-100">
                                        {{ $shift->name }}
                                    </span>
                                </td>
                                <td>
                                    <select name="slots[{{ $shift->id }}]" class="form-select form-select-sm 
                                        {{ $shift->user_id ? 'border-success fw-bold' : '' }}">
                                        <option value="" selected disabled>-- Selecione --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ $shift->user_id == $u->id ? 'selected' : '' }}>
                                                {{ $u->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</form>

<form id="regenerateForm" action="{{ route('scales.day.regenerate', $scale->id) }}" method="POST">
    @csrf
    <input type="hidden" name="date" id="regDate">
    <input type="hidden" name="mode" id="regMode">
</form>

<script>
    function regenerateDay(date, mode) {
        if(confirm('Tem certeza? Isso apagará os operadores definidos neste dia.')) {
            document.getElementById('regDate').value = date;
            document.getElementById('regMode').value = mode;
            document.getElementById('regenerateForm').submit();
        }
    }
</script>
@endsection