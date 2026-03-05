@extends('layout')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold"><i class="bi bi-gear"></i> Configuração de Logs</h3>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-start border-2">
                <div class="card-body d-flex justify-content-around align-items-center gap-3">
                    <div class="text-center">
                        <h2 class="fw-bold text-primary">{{ number_format($totalLogs, 0, ',', '.') }}</h2>
                        <small class="text-muted text-uppercase fw-bold">Total de Registros</small>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center">
                        <h2 class="fw-bold text-secondary">{{ $oldestDate }}</h2>
                        <small class="text-muted text-uppercase fw-bold">Registro Mais Antigo</small>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <a href="{{ route('logs.settings.export') }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Baixar Backup (.CSV)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header fw-bold">Preferências de Retenção</div>
                <div class="card-body">
                    <form action="{{ route('logs.settings.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tempo de Retenção (Dias)</label>
                            <div class="input-group">
                                <input type="number" name="log_retention_days" class="form-control"
                                    value="{{ $retentionDays }}" min="1">
                                <span class="input-group-text">dias</span>
                            </div>
                            <div class="form-text">
                                Logs mais antigos que isso serão apagados na limpeza automática.<br>
                                <i>(365 dias = 1 ano | 730 dias = 2 anos)</i>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Itens por Página</label>
                            <select name="log_pagination" class="form-select">
                                <option value="15" {{ $pagination == 15 ? 'selected' : '' }}>15 itens</option>
                                <option value="20" {{ $pagination == 20 ? 'selected' : '' }}>20 itens</option>
                                <option value="50" {{ $pagination == 50 ? 'selected' : '' }}>50 itens</option>
                                <option value="100" {{ $pagination == 100 ? 'selected' : '' }}>100 itens</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Salvar Preferências
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-danger">
                <div class="card-header bg-danger text-white fw-bold">
                    <i class="bi bi-exclamation-triangle-fill"></i> Zona de Perigo
                </div>
                <div class="card-body">

                    <div class="mb-4">
                        <h6 class="fw-bold text-danger">Limpeza de Antigos</h6>
                        <p class="small text-muted mb-2">
                            Remove apenas os logs anteriores a <strong>{{ $retentionDays }} dias</strong>.
                            Recomendado fazer periodicamente.
                        </p>
                        <form action="{{ route('logs.settings.clean') }}" method="POST"
                            onsubmit="return confirm('Confirma a exclusão dos logs antigos?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                Executar Limpeza Automática
                            </button>
                        </form>
                    </div>

                    <hr>

                    <div>
                        <h6 class="fw-bold text-danger">Zerar Banco de Dados</h6>
                        <p class="small text-muted mb-2">
                            Remove <strong>TODOS</strong> os registros. Ação irreversível.
                            Faça um backup (CSV) antes.
                        </p>

                        {{-- Formulário sem onsubmit — submetido apenas pelo modal --}}
                        <form id="formClearAll" action="{{ route('logs.settings.clear_all') }}" method="POST">
                            @csrf
                            <input type="hidden" name="password" id="clearAllPassword">
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                                data-bs-target="#modalClearAll">
                                <i class="bi bi-trash"></i> APAGAR TODOS OS LOGS
                            </button>
                        </form>
                    </div>

                    {{-- Modal de confirmação --}}
                    <div class="modal fade" id="modalClearAll" tabindex="-1" aria-labelledby="modalClearAllLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-danger">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="modalClearAllLabel">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Confirmar Exclusão Total
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-danger py-2 small">
                                        <strong>Atenção!</strong> Esta ação irá apagar <strong>TODOS</strong> os registros
                                        de log do sistema e não pode ser desfeita.
                                    </div>
                                    <label for="modalPasswordInput" class="form-label fw-bold">Digite sua senha para
                                        confirmar:</label>
                                    <input type="password" id="modalPasswordInput" class="form-control"
                                        placeholder="Sua senha atual" autocomplete="current-password">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" id="btnConfirmClearAll" class="btn btn-danger" disabled>
                                        <i class="bi bi-trash"></i> Confirmar e Apagar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/logs-settings.js') }}"></script>

    {{-- Reabre o modal automaticamente se voltou com erro de senha --}}
    @if ($errors->has('password'))
        <script>
            new bootstrap.Modal(document.getElementById('modalClearAll')).show();
        </script>
    @endif
@endpush