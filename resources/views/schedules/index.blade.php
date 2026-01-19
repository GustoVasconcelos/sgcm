@extends('layout')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/schedules.css') }}">
@endpush

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold"><i class="bi bi-broadcast"></i> Grade Fim de Semana</h3>
    </div>
    <div class="btn-group">
        <a href="{{ route('schedules.index', ['date' => $saturday->copy()->subWeek()->format('Y-m-d')]) }}" class="btn btn-outline-secondary">
            &laquo; Anterior
        </a>
        <a href="{{ route('schedules.index') }}" class="btn btn-outline-primary z-3">Atual</a>
        <a href="{{ route('schedules.index', ['date' => $saturday->copy()->addWeek()->format('Y-m-d')]) }}" class="btn btn-outline-secondary">
            Próximo &raquo;
        </a>
    </div>
    <div>
        <a href="{{ route('programs.index') }}" class="btn btn-secondary me-1" title="Gerenciar Lista de Programas">
            <i class="bi bi-list-ul"></i> Programas
        </a>
        <form action="{{ route('schedules.clone') }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza? Isso vai copiar a grade do fim de semana anterior para este.');">
            @csrf
            <input type="hidden" name="target_date" value="{{ $saturday->format('Y-m-d') }}">
            <button class="btn btn-warning me-1"><i class="bi bi-layers"></i> Clonar Anterior</button>
        </form>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newScheduleModal">
            <i class="bi bi-plus-lg"></i> Adicionar
        </button>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="weekendTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-bold" id="sat-tab" data-bs-toggle="tab" data-bs-target="#sat-content" type="button">
            SÁBADO ({{ $saturday->format('d/m') }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-bold" id="sun-tab" data-bs-toggle="tab" data-bs-target="#sun-content" type="button">
            DOMINGO ({{ $sunday->format('d/m') }})
        </button>
    </li>
</ul>

<div class="tab-content" id="weekendTabContent">
    <div class="tab-pane fade show active" id="sat-content">
        @include('schedules.partials.table', ['grade' => $saturdayGrade])
    </div>
    <div class="tab-pane fade" id="sun-content">
        @include('schedules.partials.table', ['grade' => $sundayGrade])
    </div>
</div>

<div class="modal fade" id="newScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Programa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Dia</label>
                        <select name="date" class="form-select" required>
                            <option value="{{ $saturday->format('Y-m-d') }}">Sábado ({{ $saturday->format('d/m') }})</option>
                            <option value="{{ $sunday->format('Y-m-d') }}">Domingo ({{ $sunday->format('d/m') }})</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Programa</label>
                        <select name="program_id" class="form-select" required id="programSelect">
                             <option value="">Selecione...</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}" data-duration="{{ $p->default_duration }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Horário</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Duração (min)</label>
                            <input type="number" name="duration" id="durationInput" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Blocos (ID)</label>
                        <input type="text" name="custom_info" class="form-control" placeholder="Ex: AGROBL1, AGROBL2">
                    </div>
                    <div class="mb-3">
                        <label>Observações</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Programa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Dia</label>
                        <select name="date" id="edit_date" class="form-select" required>
                            <option value="{{ $saturday->format('Y-m-d') }}">Sábado ({{ $saturday->format('d/m') }})</option>
                            <option value="{{ $sunday->format('Y-m-d') }}">Domingo ({{ $sunday->format('d/m') }})</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Programa</label>
                        <select name="program_id" id="edit_program_id" class="form-select" required>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}" data-duration="{{ $p->default_duration }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Horário</label>
                            <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Duração (min)</label>
                            <input type="number" name="duration" id="edit_duration" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Blocos (ID)</label>
                        <input type="text" name="custom_info" id="edit_custom_info" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Observações</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/schedules.js') }}"></script>
@endpush
@endsection