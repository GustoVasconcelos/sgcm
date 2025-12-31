@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-calendar-week"></i> Escalas</h3>
    <div>
        <a href="{{ route('reports.rh') }}" class="btn btn-secondary">Relatório RH</a>
        <a href="{{ route('scales.create') }}" class="btn btn-primary">Nova Escala</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Período</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scales as $scale)
                <tr>
                    <td>
                        {{ $scale->start_date->format('d/m/Y') }} a {{ $scale->end_date->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ strtoupper($scale->type) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('scales.edit', $scale->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <a href="{{ route('scales.pdf', $scale->id) }}" target="_blank" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf"></i> PDF</a>
                        
                        <form action="{{ route('scales.destroy', $scale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apagar?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection