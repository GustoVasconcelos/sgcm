@extends('layout')

@section('content')
<div class="container mt-2">
    
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-light">Olá, <span class="fw-bold">{{ explode(' ', Auth::user()->name)[0] }}</span>!</h2>
        </div>
    </div>

    @if(Auth::user()->is_operator)
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            {{-- LÓGICA DE CORES: Se for folga hoje, usa verde (Success). Se for trabalho, usa azul (Primary) --}}
            @php
                $isFolgaHoje = $todayShift && ($todayShift->name === 'FOLGA');
                $cardClass = $isFolgaHoje ? 'border-success' : 'border-primary';
                $textClass = $isFolgaHoje ? 'text-success' : 'text-primary';
                $icon = $isFolgaHoje ? 'bi-cup-hot' : 'bi-clock-history';
            @endphp

            <div class="card {{ $cardClass }} shadow-lg">
                <div class="card-body p-3">
                    
                    {{-- CABEÇALHO --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-uppercase {{ $textClass }} mb-0">
                            <i class="bi {{ $icon }} me-2"></i> 
                            {{ $isFolgaHoje ? 'Bom descanso!' : 'Seu Próximo Turno' }}
                        </h5>
                        
                        <a href="{{ route('scales.index') }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm">
                            Ver Escala Completa
                        </a>
                    </div>

                    {{-- CONTEÚDO PRINCIPAL --}}
                    <div class="d-flex justify-content-between align-items-center">
                        {{-- CENÁRIO 1: É HOJE E É FOLGA --}}
                        @if($isFolgaHoje)
                            <h3 class="display-6 fw-bold mb-2 text-success">
                                HOJE VOCÊ ESTÁ DE FOLGA
                            </h3>
                            
                            {{-- Se tiver um próximo trabalho agendado, mostra quando volta --}}
                            @if($nextWorkShift)
                                @php 
                                    $dateWork = \Carbon\Carbon::parse($nextWorkShift->date); 
                                    $isTomorrow = $dateWork->isTomorrow();
                                @endphp
                                <div>
                                    <div class="fs-5">
                                        {{ $isTomorrow ? 'Amanhã' : $dateWork->format('d/m') }} 
                                        <i class="bi bi-arrow-right-short text-muted mx-1"></i> 
                                        {{ $nextWorkShift->name }}
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Nenhum turno futuro agendado.</p>
                            @endif

                        {{-- CENÁRIO 2: NÃO É FOLGA (É TRABALHO OU NÃO TEM NADA HOJE) --}}
                        @else
                            @if($nextWorkShift)
                                @php 
                                    $date = \Carbon\Carbon::parse($nextWorkShift->date); 
                                    $isToday = $date->isToday();
                                    $isTomorrow = $date->isTomorrow();
                                @endphp

                                <h3 class="display-6 fw-bold mb-0">
                                    {{ $isToday ? 'HOJE' : ($isTomorrow ? 'AMANHÃ' : $date->format('d/m')) }} 
                                    
                                    <i class="bi bi-arrow-right-short text-muted mx-2"></i> 
                                    
                                    {{ mb_strtoupper($date->locale('pt_BR')->dayName, 'UTF-8') }} • {{ $nextWorkShift->name }}
                                </h3>
                            @else
                                <h3 class="fs-4 fw-bold mb-0">Sem escalas futuras</h3>
                                <p class="mb-0 small text-muted">Você não tem turnos agendados nos próximos dias.</p>
                            @endif
                        @endif
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
                        <h5 class="card-title">Configurações</h5>
                        <p class="card-text small text-white-50">Definir configurações do sistema.</p>
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