@extends('layout')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <h3 class="mb-0 fw-bold"><i class="bi bi-shield-lock"></i> Gerenciar Grupos de Acesso</h3>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-end align-items-center bg-dark text-white">
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">Novo Grupo</a>
    </div>
    <div class="card-body">
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
                    <td class="text-end">
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
@endsection