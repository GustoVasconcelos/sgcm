@extends('layout')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <h3 class="mb-0 fw-bold"><i class="bi bi-shield-lock"></i> Gerenciar Usuários</h3>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-end align-items-center">
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Novo Usuário</a>
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Grupos</th> <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        {{ $user->name }}
                        @if(!$user->is_operator)
                            <small class="d-block text-muted" style="font-size: 0.7em;">(Fora da Escala)</small>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        {{-- Loop para exibir todos os grupos --}}
                        @foreach($user->roles as $role)
                            @if($role->name === 'Admin')
                                <span class="badge bg-danger">Admin</span>
                            @elseif($role->name === 'Viewer')
                                <span class="badge bg-info text-dark">Viewer</span>
                            @else
                                <span class="badge bg-secondary">{{ $role->name }}</span>
                            @endif
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection