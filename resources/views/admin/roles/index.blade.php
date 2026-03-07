@extends('layout')

@section('content')
<div class="d-flex flex-column flex-md-row mb-4 align-items-center gap-3 justify-content-between">
    <div>
        <h3 class="mb-0 fw-bold"><i class="bi bi-people-fill"></i> Gerenciar Grupos de Acesso</h3>
    </div>
    <div>
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">Novo Grupo</a>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nome do Grupo</th>
                        <th>Permissões (Módulos)</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td class="fw-bold">{{ $role->name }}</td>
                        <td>
                            @if($role->name === 'Admin')
                                <span class="badge bg-danger">Acesso Total (Super Admin)</span>
                            @else
                                @foreach($role->permissions as $perm)
                                    <span class="badge bg-secondary mb-1">{{ $perm->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="d-flex flex-column flex-md-row justify-content-md-end text-end gap-1">
                            @if($role->name !== 'Admin')
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza? Isso removerá o grupo.')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled><i class="bi bi-lock-fill"></i></button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection