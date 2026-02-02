@extends('layout')

@section('content')

<div class="card shadow-sm" style="max-width: 800px; margin: auto;">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Novo Grupo</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="form-label fw-bold">Nome do Grupo</label>
                <input type="text" name="name" class="form-control form-control-lg" placeholder="Ex: Estagiário, Editor..." required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold mb-3">Selecione os Módulos Permitidos:</label>
                
                <div class="row g-3">
                    @foreach($permissions as $perm)
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded h-100">
                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                       value="{{ $perm->id }}" id="perm_{{ $perm->id }}">
                                
                                <label class="form-check-label w-100 fw-bold text-primary" for="perm_{{ $perm->id }}" style="cursor: pointer;">
                                    {{ $perm->name }}
                                </label>
                                <small class="d-block text-muted mt-1">
                                    {{-- Pequena descrição amigável baseada no nome técnico --}}
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
                <button type="submit" class="btn btn-success px-4">Salvar Grupo</button>
            </div>
        </form>
    </div>
</div>
@endsection