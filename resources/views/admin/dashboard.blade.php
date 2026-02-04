@extends('layout')

@section('content')

{{-- 1. TÍTULO E BOAS VINDAS --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold"><i class="bi bi-speedometer2"></i> Centro de Comando</h2>
        <p class="text-muted m-0">Visão geral do sistema e monitoramento em tempo real.</p>
    </div>
    <div class="text-end">
        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2">
            <i class="bi bi-circle-fill small me-1"></i> SISTEMA ONLINE
        </span>
    </div>
</div>

{{-- 2. CAMADA DE MÉTRICAS (MINI STATS) --}}
<div class="row mb-4 g-3">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small fw-bold">Usuários Ativos</h6>
                <h2 class="mb-0 text-primary">{{ $stats['users'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small fw-bold">Grupos (Roles)</h6>
                <h2 class="mb-0 text-info">{{ $stats['roles'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small fw-bold">Programas</h6>
                <h2 class="mb-0 text-success">{{ $stats['programs'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small fw-bold">Logs Hoje</h6>
                <h2 class="mb-0 text-warning">{{ $stats['logs_today'] }}</h2>
            </div>
        </div>
    </div>
</div>

{{-- 3. CAMADA DE AÇÕES (MENU VISUAL) --}}
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-shield-lock-fill me-2"></i> Segurança & Acesso
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-people text-primary fs-5 me-2"></i> Gerenciar Usuários
                            <div class="small text-muted ms-4 ps-1">Criar, editar e remover acessos.</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="{{ route('roles.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-person-gear text-info fs-5 me-2"></i> Gerenciar Grupos
                            <div class="small text-muted ms-4 ps-1">Definir permissões e módulos.</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    
                    <a href="{{ route('permissions.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 text-danger mt-2">
                        <div>
                            <i class="bi bi-code-slash fs-6 me-2"></i> 
                            <span class="small fw-bold text-uppercase">Catálogo de Permissões (Dev)</span>
                            <div class="small text-muted ms-4 ps-1">Regras usadas no backend.</div>
                        </div>
                        <i class="bi bi-chevron-right small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-hdd-network-fill me-2"></i> Operacional
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="{{ route('programs.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-collection-play text-success fs-5 me-2"></i> Catálogo de Programas
                            <div class="small text-muted ms-4 ps-1">Adicionar ou remover atrações.</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>

                    <a href="{{ route('logs.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-journal-text text-warning fs-5 me-2"></i> Logs do Sistema
                            <div class="small text-muted ms-4 ps-1">Auditoria de ações realizadas.</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>

                    <a href="{{ route('logs.settings.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-sliders text-secondary fs-5 me-2"></i> Configurações
                            <div class="small text-muted ms-4 ps-1">Configurações dos logs</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. CAMADA "AO VIVO" (LOGS RECENTES) --}}
<div class="card shadow-sm">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-bold text-muted"><i class="bi bi-activity me-2"></i> Atividade Recente do Sistema</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table">
                <tr>
                    <th class="ps-4">Usuário</th>
                    <th>Módulo</th>
                    <th>Ação</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                <tr>
                    <td class="ps-4 fw-bold">
                        @if($log->user)
                            {{ $log->user->name }}
                        @else
                            <span class="text-muted fst-italic">Sistema</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary border">{{ $log->module }}</span></td>
                    <td class="text-primary">{{ $log->action }}</td>
                    <td class="small text-muted text-truncate" style="max-width: 300px;">
                        {{ Str::limit(json_encode($log->details), 60) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Nenhuma atividade registrada hoje.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-center py-2">
        <a href="{{ route('logs.index') }}" class="text-decoration-none small fw-bold">VER TODO O HISTÓRICO <i class="bi bi-arrow-right"></i></a>
    </div>
</div>

@endsection