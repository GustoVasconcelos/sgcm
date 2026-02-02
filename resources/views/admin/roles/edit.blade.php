@extends('layout')

@section('content')
<div class="card shadow-sm" style="max-width: 800px; margin: auto;">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Editar Grupo: {{ $role->name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="form-label fw-bold">Nome do Grupo</label>
                <input type="text" name="name" value="{{ $role->name }}" class="form-control form-control-lg" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold mb-3">Módulos Permitidos:</label>
                
                <div class="row g-3">
                    @foreach($permissions as $perm)
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded h-100">
                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                       value="{{ $perm->id }}" id="perm_{{ $perm->id }}"
                                       {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}>
                                
                                <label class="form-check-label w-100 fw-bold text-primary" for="perm_{{ $perm->id }}" style="cursor: pointer;">
                                    {{ $perm->name }}
                                </label>
                                <small class="d-block text-muted mt-1">
                                    @if($perm->name == 'ver_escalas') Acesso à visualização de Escalas
                                    @elseif($perm->name == 'ver_regressiva') Acesso à tela de Visualização (TV)
                                    @elseif($perm->name == 'operar_regressiva') Controle total dos Timers/BK
                                    @elseif($perm->name == 'ver_ferias') Gestão de Férias
                                    @elseif($perm->name == 'ver_pgm_fds') Gestão de Programas Locais
                                    @elseif($perm->name == 'usar_afinacao') Ferramenta de Afinação
                                    @else Permissão administrativa @endif
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success px-4">Atualizar Grupo</button>
            </div>
        </form>
    </div>
</div>
@endsection