@extends('layout')

@section('content')
    {{-- CABEÇALHO COM TÍTULO, RELÓGIO SERVER E BOTÃO --}}
    <div class="row mb-4 align-items-top">
        <div class="col-md-4">
            <h3 class="fw-bold mb-0"><i class="bi bi-stopwatch"></i> Regressiva</h3>
        </div>

        <div class="col-md-4 text-center my-3 my-md-0">
            <div class="d-inline-block bg-dark border border-secondary border-opacity-50 rounded px-4 py-1 shadow-sm">
                <small class="d-block text-secondary fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">HORA SERVIDOR</small>
                <div id="serverClock" class="fs-4 fw-bold font-monospace text-info">--:--:--</div>
            </div>
        </div>

        <div class="col-md-4 text-end">
            <button onclick="openStudioWindow()" class="btn btn-outline-light btn-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-window-stack"></i> 
                <span>Abrir Tela do Estúdio</span>
            </button>
        </div>
    </div>

    <div class="row align-items-stretch"> <div class="col-md-6 mb-4 mt-2">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Regressiva (Término/Início)</h5>
                </div>
                <div class="card-body">
                    
                    <div class="text-center mb-4 p-3 bg-dark rounded text-white">
                        <small class="text-uppercase text-muted" id="previewMode">--</small>
                        <h1 id="previewRegressive" class="display-3 fw-bold m-0">--:--:--</h1>
                    </div>

                    <form id="formRegressive">
                        <div class="mb-3">
                            <label class="form-label">Horário Alvo (Término/Início)</label>
                            <input type="time" step="1" class="form-control form-control-lg text-center" id="targetInput" required>
                            <div class="form-text">Digite a hora exata (Ex: 08:00:00)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Modo / Texto</label>
                            <select class="form-select" id="modeInput">
                                <option value="INICIO DO PGM">INICIO DO PGM</option>
                                <option value="ENCERRAMENTO DO PGM">ENCERRAMENTO DO PGM</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" onclick="sendRegressive()" class="btn btn-primary btn-lg">
                                <i class="bi bi-play-fill"></i> ATUALIZAR TELA
                            </button>
                            <button type="button" onclick="stopRegressive()" class="btn btn-outline-danger btn-lg">
                                <i class="bi bi-stop-fill"></i> DESATIVAR / LIMPAR
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4 d-flex flex-column mt-2">
            
            <div class="card shadow-sm border-success mb-4 flex-grow-1">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-stopwatch"></i> Progressiva (Link/Bloco)</h5>
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-between h-100">
                    
                    <div class="mb-4 p-3 bg-dark rounded text-success mt-auto mb-auto"> <h1 id="previewProgressive" class="display-3 fw-bold m-0">00:00:00</h1>
                        <small class="text-muted">TEMPO DECORRIDO</small>
                    </div>

                    <div class="d-grid gap-2">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="controlStopwatch('start')" class="btn btn-success btn-lg">
                                <i class="bi bi-play-circle"></i> INICIAR
                            </button>
                            <button type="button" onclick="controlStopwatch('pause')" class="btn btn-warning btn-lg">
                                <i class="bi bi-pause-circle"></i> PAUSAR
                            </button>
                        </div>
                        <button type="button" onclick="controlStopwatch('reset')" class="btn btn-danger btn-lg">
                            <i class="bi bi-arrow-counterclockwise"></i> ZERAR
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-hourglass-split"></i> Comercial / Break</h5>
                    <small>Volta do Intervalo</small>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center border-end border-warning">
                            <h2 id="previewBk" class="m-0 fw-bold text-white font-monospace">--:--</h2>
                        </div>
                        
                        <div class="col-8">
                            <div class="input-group">
                                <input type="time" step="1" id="bkInput" class="form-control text-center font-monospace fw-bold">
                                <button class="btn btn-warning border border-warning-subtle" onclick="sendBk()">
                                    <i class="bi bi-play-fill"></i>ENVIAR
                                </button>
                                <button class="btn btn-danger" onclick="stopBk()" title="Limpar">
                                    <i class="bi bi-stop-fill"></i>LIMPAR
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // --- FUNÇÃO NOVA: ABRIR POP-UP ---
    function openStudioWindow() {
        // Abre uma nova janela sem barras de ferramentas, menu ou endereço.
        // Tamanho padrão HD (pode ser maximizado depois)
        window.open(
            "{{ route('timers.viewer') }}", 
            "StudioTimerWindow", 
            "width=1280,height=720,menubar=no,toolbar=no,location=no,status=no"
        );
    }

    // --- ESTADO LOCAL & SINCRONIA ---
    let serverOffset = 0;
    let targetTime = null;
    let bkTargetTime = null;
    let stopwatchStart = null;
    let stopwatchAccumulated = 0;
    let stopwatchStatus = 'stopped';

    // 1. Enviar Regressiva
    async function sendRegressive() {
        const timeVal = document.getElementById('targetInput').value;
        const modeVal = document.getElementById('modeInput').value;

        if (!timeVal) return alert('Selecione um horário!');

        await fetch('/timers/update-regressive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ target_hour: timeVal, mode_label: modeVal })
        });
    }

    // 2. Parar Regressiva
    async function stopRegressive() {
        await fetch('/timers/update-regressive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ target_hour: null }) 
        });
        document.getElementById('targetInput').value = '';
    }

    // 3. Controlar Progressiva
    async function controlStopwatch(action) {
        await fetch('/timers/update-stopwatch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ action: action })
        });
    }

    // --- NOVAS FUNÇÕES DO BK ---
    async function sendBk() {
        const timeVal = document.getElementById('bkInput').value;
        if (!timeVal) return alert('Digite a hora de volta!');

        await fetch('/timers/update-bk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ bk_hour: timeVal })
        });
    }

    async function stopBk() {
        await fetch('/timers/update-bk', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ bk_hour: null })
        });
        document.getElementById('bkInput').value = '';
    }

    // --- LÓGICA DE PREVIEW ---
    async function syncState() {
        try {
            const reqStart = Date.now();
            const res = await fetch('/timers/status');
            const data = await res.json();
            const now = Date.now();
            const latency = (now - reqStart) / 2;
            const serverTimeExact = data.server_time + latency;
            
            serverOffset = serverTimeExact - now;
            targetTime = data.target_time;
            bkTargetTime = data.bk_target_time;
            
            document.getElementById('previewMode').innerText = data.mode_label || '--';

            stopwatchStart = data.stopwatch.started_at;
            stopwatchAccumulated = data.stopwatch.accumulated;
            stopwatchStatus = data.stopwatch.status;

        } catch (e) { console.error(e); }
    }

    function updatePreview() {
        const nowSynced = Date.now() + serverOffset;

        // --- Atualiza o Relógio do Servidor no Cabeçalho ---
        // Cria um objeto Date com a hora sincronizada
        const serverDate = new Date(nowSynced);
        // Formata para HH:MM:SS
        const timeString = serverDate.toLocaleTimeString('pt-BR', { 
            hour: '2-digit', minute: '2-digit', second: '2-digit' 
        });
        document.getElementById('serverClock').innerText = timeString;

        // Regressiva Preview
        const regEl = document.getElementById('previewRegressive');
        if (targetTime) {
            let diff = targetTime - nowSynced;
            if (diff < 0) diff = 0;
            regEl.innerText = formatMs(diff);
            regEl.style.color = (diff === 0) ? '#ff4444' : '#ffffff';
        } else {
            regEl.innerText = "--:--:--";
            regEl.style.color = '#666';
        }

        // Progressiva Preview
        const progEl = document.getElementById('previewProgressive');
        if (stopwatchStatus === 'running' && stopwatchStart) {
            const elapsed = (nowSynced - stopwatchStart) + (stopwatchAccumulated * 1000);
            progEl.innerText = formatMs(elapsed);
        } else if (stopwatchStatus === 'paused') {
            progEl.innerText = formatMs(stopwatchAccumulated * 1000);
        } else {
            progEl.innerText = "00:00:00";
        }

        // NOVO: Preview do BK
        const bkEl = document.getElementById('previewBk');
        if (bkTargetTime) {
            let diff = bkTargetTime - nowSynced;
            if (diff < 0) diff = 0;
            // Mostra apenas MM:SS pra economizar espaço, ou HH:MM:SS se preferir
            bkEl.innerText = formatMs(diff).substring(3); // Tira as horas (mostra 03:00) se for curto
            if(diff > 3600000) bkEl.innerText = formatMs(diff); // Se for mais de 1h, mostra tudo
        } else {
            bkEl.innerText = "--:--";
        }

        requestAnimationFrame(updatePreview);
    }

    function formatMs(ms) {
        const totalSec = Math.floor(ms / 1000);
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        const s = totalSec % 60;
        return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }

    setInterval(syncState, 1000); 
    syncState().then(() => requestAnimationFrame(updatePreview));

</script>
@endsection