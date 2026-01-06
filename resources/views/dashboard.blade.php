@extends('layout')

@section('content')
<div class="container mt-4">
    
    <div class="row mb-4">
        <div class="col-12 text-center text-white">
            <h2 class="fw-light">Olá, <span class="fw-bold">{{ explode(' ', Auth::user()->name)[0] }}</span>!</h2>
            <p class="text-white-50">Bem-vindo ao SGCM. Aqui está o resumo das suas atividades.</p>
        </div>
    </div>

    @if(Auth::user()->profile != 'admin')
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <div class="card border-primary shadow-lg" style="border-left: 5px solid #0d6efd;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
                    
                    <div>
                        <h5 class="card-title text-uppercase text-primary mb-1">
                            <i class="bi bi-clock-history me-2"></i> Seu Próximo Turno
                        </h5>
                        
                        @if($nextShift)
                            @php 
                                $date = \Carbon\Carbon::parse($nextShift->date); 
                                $isToday = $date->isToday();
                                $isTomorrow = $date->isTomorrow();
                            @endphp

                            <h3 class="display-6 fw-bold mb-0">
                                {{ $isToday ? 'HOJE' : ($isTomorrow ? 'AMANHÃ' : $date->format('d/m')) }}
                            </h3>
                            <p class="fs-5 mb-0 ">
                                {{ mb_strtoupper($date->locale('pt_BR')->dayName, 'UTF-8') }} • {{ $nextShift->name }}
                            </p>
                        @else
                            <h3 class="fs-4 fw-bold mb-0 t">Sem escalas futuras</h3>
                            <p class="mb-0 small text-muted">Você não tem turnos agendados nos próximos dias.</p>
                        @endif
                    </div>

                    <div class="mt-3 mt-md-0">
                        <a href="{{ route('scales.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                            Ver Escala Completa
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4">
        
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('tools.afinacao') }}" class="text-decoration-none"> <div class="card h-100 bg-secondary bg-opacity-10 border-0 shadow-sm hover-card">
                    <div class="card-body text-center py-4">
                        <div class="icon-box mb-3 text-warning">
                            <i class="bi bi-mic fs-1"></i>
                        </div>
                        <h5 class="card-title">Afinação</h5>
                        <p class="card-text small text-50">Afinação do jornal.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('scales.index') }}" class="text-decoration-none">
                <div class="card h-100 bg-secondary bg-opacity-10 border-0 shadow-sm hover-card ">
                    <div class="card-body text-center py-4">
                        <div class="icon-box mb-3 text-info">
                            <i class="bi bi-calendar-range fs-1"></i>
                        </div>
                        <h5 class="card-title">Escalas</h5>
                        <p class="card-text small text-50">Visualize ou edite os horarios de trabalho.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('schedules.index') }}" class="text-decoration-none"> <div class="card h-100 bg-secondary bg-opacity-10 border-0 shadow-sm hover-card ">
                    <div class="card-body text-center py-4">
                        <div class="icon-box mb-3 text-success">
                            <i class="bi bi-broadcast fs-1"></i>
                        </div>
                        <h5 class="card-title">PGMs FDS</h5>
                        <p class="card-text small text-50">Controle dos programas locais do fim de semana.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('vacations.index') }}" class="text-decoration-none"> <div class="card h-100 bg-secondary bg-opacity-10 border-0 shadow-sm hover-card ">
                    <div class="card-body text-center py-4">
                        <div class="icon-box mb-3 text-danger">
                            <i class="bi bi-airplane fs-1"></i>
                        </div>
                        <h5 class="card-title">Férias</h5>
                        <p class="card-text small text-50">Cadastro e consulta de férias.</p>
                    </div>
                </div>
            </a>
        </div>

        @if(Auth::user()->profile == 'admin')
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('users.index') }}" class="text-decoration-none"> <div class="card h-100 bg-dark border-secondary shadow-sm hover-card ">
                    <div class="card-body text-center py-4">
                        <div class="icon-box mb-3 text-white">
                            <i class="bi bi-people-fill fs-1"></i>
                        </div>
                        <h5 class="card-title">Gerenciar Equipe</h5>
                        <p class="card-text small text-50">Cadastro e controle de usuários.</p>
                        <span class="badge bg-danger">Admin</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('logs.index') }}" class="text-decoration-none"> <div class="card h-100 bg-dark border-secondary shadow-sm hover-card ">
                    <div class="card-body text-center py-4">
                        <div class="icon-box mb-3 text-white">
                            <i class="bi bi-activity fs-1"></i>
                        </div>
                        <h5 class="card-title">Visualizar Logs</h5>
                        <p class="card-text small text-50">Cadastro e controle de usuários.</p>
                        <span class="badge bg-danger">Admin</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('logs.settings.index') }}" class="text-decoration-none"> 
                <div class="card h-100 bg-dark border-secondary shadow-sm hover-card">
                    <div class="card-body text-center py-4">
                        <div class="icon-box mb-3 text-white">
                            <i class="bi bi-gear-fill fs-1"></i>
                        </div>
                        <h5 class="card-title">Configurar Logs</h5>
                        <p class="card-text small text-white-50">Definir tempo de retenção e limpeza.</p>
                        <span class="badge bg-danger">Admin</span>
                    </div>
                </div>
            </a>
        </div>
        @endif

    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.2s ease, background-color 0.2s;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        background-color: rgba(255, 255, 255, 0.15) !important; /* Clareia um pouco ao passar o mouse */
    }
</style>
@endsection