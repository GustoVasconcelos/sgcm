@extends('layout')

@section('content')
<div class="container mt-2">
    
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-light">Olá, <span class="fw-bold">{{ explode(' ', Auth::user()->name)[0] }}</span>!</h2>
        </div>
    </div>

    @if(Auth::user()->is_operator)
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            
            @if($displayShift)
                {{-- Lógica Visual: Verde para Folga, Azul para Trabalho --}}
                @php
                    $isFolga = ($displayShift->name === 'FOLGA');
                    $date = \Carbon\Carbon::parse($displayShift->date);
                    
                    // Textos dinâmicos baseados na data do card exibido
                    if ($date->isToday()) {
                        $timeText = 'HOJE';
                        $titleText = $isFolga ? 'Hoje: Descanso' : 'Seu Turno de Hoje';
                    } elseif ($date->isTomorrow()) {
                        $timeText = 'AMANHÃ';
                        $titleText = $isFolga ? 'Amanhã: Descanso' : 'Seu Próximo Turno';
                    } else {
                        $timeText = $date->format('d/m');
                        $titleText = 'Próximo Turno';
                    }

                    $cardClass = $isFolga ? 'border-success' : 'border-primary';
                    $textClass = $isFolga ? 'text-success' : 'text-primary';
                    $btnClass = $isFolga ? 'btn-outline-success' : 'btn-outline-primary';
                    $icon = $isFolga ? 'bi-cup-hot' : 'bi-clock-history';
                @endphp

                <div class="card {{ $cardClass }} shadow-lg">
                    <div class="card-body p-4">
                        
                        {{-- CABEÇALHO --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-uppercase {{ $textClass }} mb-0">
                                <i class="bi {{ $icon }} me-2"></i> {{ $titleText }}
                            </h5>
                            
                            <a href="{{ route('scales.index') }}" class="btn {{ $btnClass }} rounded-pill px-4 btn-sm">
                                Ver Escala Completa
                            </a>
                        </div>

                        {{-- CONTEÚDO PRINCIPAL --}}
                        <div class="d-flex justify-content-between">
                            <h3 class="display-6 {{ $textClass }} fw-bold mb-0">
                                {{ $timeText }} 
                                
                                @if(!$date->isToday() && !$date->isTomorrow())
                                    <small class="text-muted fs-6 ms-1">({{ mb_strtoupper($date->locale('pt_BR')->dayName) }})</small>
                                @endif

                                @if(!$isFolga)
                                    <i class="bi bi-arrow-right-short text-muted mx-2"></i> 
                                    {{ $displayShift->name }}
                                @else
                                    <span class="text-success ms-2">- FOLGA</span>
                                @endif
                            </h3>

                            {{-- SE FOR FOLGA, MOSTRA QUANDO VOLTA --}}
                            @if($isFolga && $returnShift)
                                @php 
                                    $dateReturn = \Carbon\Carbon::parse($returnShift->date); 
                                    $isRetTomorrow = $dateReturn->isTomorrow();
                                @endphp
                                
                                <div class="d-flex flex-column align-items-center">
                                    <p class="mb-1 small text-muted text-uppercase fw-bold">
                                        <i class="bi bi-arrow-return-right"></i> <span class="{{ $textClass }}">Retorno ao trabalho</span>
                                    </p>
                                    <span class="fs-5 {{ $textClass }} fw-semibold">
                                        {{ $isRetTomorrow ? 'Amanhã' : $dateReturn->format('d/m') }} 
                                        
                                        @if(!$isRetTomorrow)
                                            <small class="text-muted fw-normal">({{ mb_strtoupper($dateReturn->locale('pt_BR')->shortDayName) }})</small>
                                        @endif
                                        
                                        : {{ $returnShift->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            @else
                {{-- Caso não tenha NENHUM registro futuro no banco --}}
                <div class="card border-secondary shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h3 class="fs-4 fw-bold mb-0 text-muted">Sem escalas futuras</h3>
                        <p class="mb-3 small text-muted">Não há turnos cadastrados para você.</p>
                        <a href="{{ route('scales.index') }}" class="btn {{ $btnClass }} rounded-pill btn-sm">
                            Verificar Escala
                        </a>
                    </div>
                </div>
            @endif

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