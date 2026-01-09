@extends('layout')

@section('content')
<form action="{{ route('scales.store') }}" method="POST" id="mainForm">
    @csrf
    
    <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
    <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">

    <div class="d-flex justify-content-between align-items-center mb-3 py-3 border-bottom" style="z-index: 1000;">
        <h4 class="m-0">
            <i class="bi bi-calendar3"></i> 
            Escalas de {{ $start->format('d/m') }} a {{ $end->format('d/m') }}
        </h4>
        <div>
            <button type="submit" form="form-auto-generate" class="btn btn-warning text-dark fw-bold me-2" title="Preencher dias vazios automaticamente">
                <i class="bi bi-magic"></i> Preencher Escala Rotativa
            </button>
            <a href="{{ route('scales.print', ['start_date' => $start->format('Y-m-d'), 'end_date' => $end->format('Y-m-d')]) }}" 
            target="_blank" 
            class="btn btn-danger me-2">
                <i class="bi bi-file-pdf"></i> Baixar PDF
            </a>

            <a href="{{ route('scales.index') }}" class="btn btn-secondary me-2">Trocar Datas</a>
            
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
        </div>
    </div>

    <div class="row">
        @foreach($days as $dateString => $shifts)
            @php $date = \Carbon\Carbon::parse($dateString); @endphp
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-1">
                    
                    <div class="card-header d-flex justify-content-between align-items-center">
                        
                        <span class="fw-bold">
                            {{ $date->format('d/m') }} - {{ mb_strtoupper($date->locale('pt_BR')->dayName, 'UTF-8') }}
                        </span>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><h6 class="dropdown-header">Layout</h6></li>
                                <li><button type="button" class="dropdown-item" onclick="regenerateDay('{{ $dateString }}', '6h')">Padrão (6h)</button></li>
                                <li><button type="button" class="dropdown-item" onclick="regenerateDay('{{ $dateString }}', '8h')">Férias (8h)</button></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body p-2">
                        @if($shifts)
                            <table class="table table-sm table-borderless mb-0">
                                @foreach($shifts as $shift)
                                @php 
                                    $uniqueKey = $dateString . '_' . $shift->order; 
                                @endphp
                                
                                <tr>
                                    <td style="width: 40%; vertical-align: middle;">
                                        <span class="badge {{ $shift->name == 'FOLGA' ? 'bg-warning text-dark' : 'bg-secondary' }} w-100">
                                            {{ $shift->name }}
                                        </span>
                                        <input type="hidden" name="names[{{ $uniqueKey }}]" value="{{ $shift->name }}">
                                    </td>
                                    <td>
                                        <select name="slots[{{ $uniqueKey }}]" class="form-select form-select-sm {{ $shift->user_id ? 'border-success fw-bold' : '' }}">
                                            <option value="">-- Selecione --</option>
                                            
                                            @if($shift->name == 'FOLGA' && isset($idNaoHa))
                                                <option value="{{ $idNaoHa }}" class="fw-bold text-danger" {{ $shift->user_id == $idNaoHa ? 'selected' : '' }}>
                                                    NÃO HÁ
                                                </option>
                                            @endif

                                            @if(isset($users) && count($users) > 0)
                                                @foreach($users as $u)
                                                    <option value="{{ $u->id }}" {{ $shift->user_id == $u->id ? 'selected' : '' }}>
                                                        {{ $u->display_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        @else
                            <div class="alert alert-warning m-0 p-2 text-center small">
                                <i class="bi bi-exclamation-triangle"></i> Erro ao carregar turnos.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</form>

<form id="regenerateForm" action="{{ route('scales.day.regenerate') }}" method="POST">
    @csrf
    <input type="hidden" name="date" id="regDate">
    <input type="hidden" name="mode" id="regMode">
</form>

<form id="form-auto-generate" action="{{ route('scales.auto') }}" method="POST" style="display: none;" onsubmit="return confirm('Deseja preencher automaticamente os dias vazios seguindo a rotação?');">
    @csrf
    <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
    <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">
</form>

<script>
    function regenerateDay(date, mode) {
        if(confirm('Isso resetará os operadores deste dia. Confirmar?')) {
            document.getElementById('regDate').value = date;
            document.getElementById('regMode').value = mode;
            document.getElementById('regenerateForm').submit();
        }
    }
</script>
@endsection