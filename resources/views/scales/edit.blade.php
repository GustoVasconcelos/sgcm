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
                <i class="bi bi-magic"></i> Escala Rotativa
            </button>
            <button type="button" class="btn btn-primary fw-bold me-2" data-bs-toggle="modal" data-bs-target="#emailModal">
                <i class="bi bi-envelope-at"></i> Enviar por Email
            </button>
            <a href="{{ route('scales.print', ['start_date' => $start->format('Y-m-d'), 'end_date' => $end->format('Y-m-d')]) }}" target="_blank" class="btn btn-danger me-2">
                <i class="bi bi-file-pdf"></i> Baixar PDF
            </a>
            <a href="{{ route('scales.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-calendar3"></i> Trocar Datas
            </a>            
            <button type="submit" class="btn btn-success">
                <i class="bi bi-floppy"></i> Salvar Escala
            </button>
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
                                    <td class="fs-5">
                                        <span class="badge {{ $shift->name == 'FOLGA' ? 'text-bg-warning' : 'text-bg-secondary' }} w-100">
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

<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('scales.email') }}" method="POST">
            @csrf
            <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-envelope-at"></i> Enviar Escala por Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Selecione os operadores que receberão o arquivo PDF desta escala.</p>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" onclick="toggleCheckboxes(true)">Marcar Todos</button>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-danger" onclick="toggleCheckboxes(false)">Desmarcar Todos</button>
                    </div>

                    <div class="list-group">
                        @foreach($users as $user)
                            @if($user->name != 'NÃO HÁ' && $user->email) <label class="list-group-item d-flex gap-3 align-items-center cursor-pointer">
                                    <input class="form-check-input flex-shrink-0 recipient-checkbox" type="checkbox" name="recipients[]" value="{{ $user->id }}" checked>
                                    <span class="pt-1 form-checked-content">
                                        <strong>{{ $user->name }}</strong>
                                        <small class="d-block text-muted">{{ $user->email }}</small>
                                    </span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Enviar Agora
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
</style>

<script>
    function toggleCheckboxes(state) {
        document.querySelectorAll('.recipient-checkbox').forEach(el => el.checked = state);
    }

    function regenerateDay(date, mode) {
        if(confirm('Isso resetará os operadores deste dia. Confirmar?')) {
            document.getElementById('regDate').value = date;
            document.getElementById('regMode').value = mode;
            document.getElementById('regenerateForm').submit();
        }
    }
</script>
@endsection