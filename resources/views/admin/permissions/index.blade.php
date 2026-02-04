@extends('layout')

@section('content')
<div class="card shadow-sm border-danger">
    {{-- CABEÇALHO --}}
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Catálogo de Permissões (Sistema)</h5>
        <a href="{{ route('permissions.create') }}" class="btn btn-light btn-sm fw-bold text-danger">Nova Permissão</a>
    </div>
    
    <div class="card-body">
        
        {{-- AVISO TÉCNICO FIXO (Específico desta tela) --}}
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-cone-striped fs-3 me-3"></i>
            <div>
                <strong>ZONA DE PERIGO:</strong><br>
                Estas são as chaves usadas no código-fonte. Renomear ou excluir itens aqui pode quebrar o sistema.
            </div>
        </div>

        {{-- TABELA --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table">
                    <tr>
                        <th>ID</th>
                        <th>Chave (Slug)</th>
                        <th>Guard</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $perm)
                    <tr>
                        <td class="text-muted small">#{{ $perm->id }}</td>
                        <td class="font-monospace fw-bold">{{ $perm->name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $perm->guard_name }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('permissions.edit', $perm->id) }}" class="btn btn-sm btn-outline-secondary" title="Renomear">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            <form action="{{ route('permissions.destroy', $perm->id) }}" method="POST" class="d-inline">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza? Isso pode quebrar o sistema se o código usar essa permissão.')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                            Nenhuma permissão técnica cadastrada ainda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $permissions->links() }}
        </div>

    </div>
    
    <div class="card-footer">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar ao Painel</a>
    </div>

</div>
@endsection