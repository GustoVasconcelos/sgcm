@extends('layout')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="bi bi-calendar-event"></i> Controle de Férias</h3>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('vacations.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Cadastrar Minhas Férias
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form action="{{ route('vacations.index') }}" method="GET" class="row align-items-center">
            <label class="col-auto fw-bold">Ano de Referência:</label>
            <div class="col-auto">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-bordered table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Operador</th>
                    <th>Modalidade</th>
                    <th>1º Período</th>
                    <th>2º Período</th>
                    <th style="width: 100px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacations as $v)
                <tr>
                    <td class="fw-bold text-uppercase">{{ $v->user->name }}</td>
                    <td><small>{{ $v->mode_label }}</small></td>
                    <td>
                        {{ \Carbon\Carbon::parse($v->period_1_start)->format('d/m/Y') }} a 
                        {{ \Carbon\Carbon::parse($v->period_1_end)->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($v->period_2_start)
                            {{ \Carbon\Carbon::parse($v->period_2_start)->format('d/m/Y') }} a 
                            {{ \Carbon\Carbon::parse($v->period_2_end)->format('d/m/Y') }}
                        @else - @endif
                    </td>
                    <td class="text-center">
                        {{-- Só mostra botões se for Admin OU Dono das férias --}}
                        @if(Auth::user()->profile === 'admin' || Auth::id() === $v->user_id)
                            <div class="btn-group btn-group-sm">
                                {{-- <a href="{{ route('vacations.edit', $v->id) }}" class="btn btn-primary" title="Editar">Editar</a> --}}
                                <form class="px-1" action="{{ route('vacations.edit', $v->id) }}" method="POST">
                                    @csrf @method('GET')
                                    <button class="btn btn-primary" title="Editar">Editar</button>
                                </form>
                                <form class="px-1" action="{{ route('vacations.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Apagar registro?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger" title="Excluir">Excluir</button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted"><small>Somente leitura</small></span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Nenhuma férias cadastrada para {{ $year }}.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection