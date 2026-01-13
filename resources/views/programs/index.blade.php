@extends('layout')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/programs.css') }}">
@endpush
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-collection-play"></i> Catálogo de Programas</h3>
    <div>
        <a href="{{ route('schedules.index') }}" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left"></i> Voltar para Grade</a>
        <a href="{{ route('programs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Programa</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="5%">Cor</th>
                    <th>Nome do Programa</th>
                    <th>Duração Padrão</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($programs as $program)
                <tr>
                    <td>
                        <div class="color-swatch" style="background-color: {{ $program->color ?? '#cccccc' }};"></div>
                    </td>
                    <td class="fw-bold">{{ $program->name }}</td>
                    <td>{{ $program->default_duration }} minutos</td>
                    <td class="text-end">
                        <a href="{{ route('programs.edit', $program->id) }}" class="btn btn-sm btn-primary me-2">
                            <i class="bi bi-pencil"></i>Editar
                        </a>
                        
                        <form action="{{ route('programs.destroy', $program->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir {{ $program->name }}?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i>Excluir</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $programs->links() }}
        </div>
    </div>
</div>
@endsection