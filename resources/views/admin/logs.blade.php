@extends('layout')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-activity"></i> Logs de Atividades</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('logs.index') }}" method="GET" class="row g-3 mb-4 border-bottom pb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold">Usuário</label>
                <select name="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">Módulo</label>
                <select name="module" class="form-select">
                    <option value="">Todos</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>
                            {{ $mod }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">Data</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 me-2">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
                <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                    Limpar
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Módulo</th>
                        <th>Ação</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td style="width: 150px;">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $log->user->name }}</div>
                            <small class="text-muted">{{ $log->user->email }}</small>
                        </td>
                        <td><span class="badge bg-secondary">{{ $log->module }}</span></td>
                        <td class="fw-bold text-primary">{{ $log->action }}</td>
                        <td>
                            @if($log->details)
                                <ul class="mb-0 small ps-3">
                                @foreach($log->details as $key => $value)
                                    <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                                @endforeach
                                </ul>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Nenhum registro encontrado com esses filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection