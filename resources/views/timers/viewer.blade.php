<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regressiva de Estúdio - SGCM</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="{{ asset('logotipo-band.ico') }}">

    <style>
        :root {
            --bg-color: #121212;
            --text-main: #ffffff;
            --text-red: #ff4444;
            --text-yellow: #ffbb33;
            --text-green: #00C851;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Roboto Mono', monospace;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            /* evita scroll lateral, mas permite respirar verticalmente */
        }

        /* --- BOTÃO FANTASMA (VOLTAR) --- */
        .ghost-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            color: rgba(255, 255, 255, 0.5); /* Começa apagadinho */
            text-decoration: none;
            font-family: sans-serif; /* Fonte simples para leitura */
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 30px;
            transition: all 0.3s ease;
            opacity: 0.15;
            z-index: 1000;
        }

        .ghost-btn:hover {
            opacity: 1; /* Totalmente visível no hover */
            background-color: rgba(255, 255, 255, 0.1); /* Fundo leve */
            color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        /* --- LAYOUT DA REGRESSIVA (PRINCIPAL) --- */
        .regressive-container {
            text-align: center;
            margin-bottom: 4vh;
            padding: 0 16px;
        }

        .label-mode {
            font-size: clamp(0.85rem, 4vw, 4rem);
            background-color: #333;
            padding: 5px 24px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .big-timer {
            font-size: clamp(3.5rem, 20vw, 14rem);
            line-height: 1;
            font-weight: 700;
            text-shadow: 4px 4px 10px rgba(0, 0, 0, 0.5);
        }

        /* Cores de status */
        .status-normal {
            color: var(--text-main);
        }

        .status-warning {
            color: var(--text-yellow);
        }

        .status-critical {
            color: var(--text-red);
        }

        .bottom-area {
            width: 100%;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            /* quebra linha se apertar */
            gap: 16px;
            border-top: 3px solid #333;
            padding: 16px 16px 20px;
            box-sizing: border-box;
        }

        /* CARD COMUM PARA PROGRESSIVA E BK */
        .sub-timer-box {
            flex: 1;
            min-width: 140px;
            /* evita ficar muito apertado lado a lado */
            text-align: center;
            opacity: 0.3;
            transition: all 0.3s;
            display: none;
        }

        .sub-timer-box.active {
            opacity: 1;
            display: block;
        }

        /* Cores específicas */
        .timer-green {
            color: var(--text-green);
        }

        .timer-yellow {
            color: var(--text-yellow);
        }

        .sub-timer-digits {
            font-size: clamp(2rem, 8vw, 6rem);
            line-height: 1;
        }

        .sub-timer-label {
            font-size: clamp(0.7rem, 3vw, 1.5rem);
            background-color: #333;
            padding: 5px 30px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
            display: inline-block;
        }

        /* ============================================================
           PORTRAIT (celular em pé): tela estreita, muito espaço vertical
           ============================================================ */
        @media (max-width: 600px) and (orientation: portrait) {
            .big-timer {
                font-size: clamp(4rem, 26vw, 9rem);
            }

            .label-mode {
                font-size: clamp(0.8rem, 5vw, 1.4rem);
                padding: 5px 16px;
                letter-spacing: 1px;
            }

            .sub-timer-digits {
                font-size: clamp(2.2rem, 13vw, 5rem);
            }

            .sub-timer-label {
                font-size: clamp(0.65rem, 4vw, 1rem);
                padding: 4px 12px;
            }

            .bottom-area {
                flex-direction: column;
                /* empilha os cards verticalmente */
                align-items: center;
                gap: 12px;
                padding: 14px 16px 16px;
            }

            .sub-timer-box {
                min-width: unset;
                width: 100%;
            }
        }

        /* ============================================================
           LANDSCAPE em celular (altura pequena): comprime tudo
           ============================================================ */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                justify-content: flex-start;
                padding-top: 8px;
            }

            .regressive-container {
                margin-bottom: 2vh;
            }

            .big-timer {
                font-size: clamp(2.5rem, 16vw, 7rem);
            }

            .label-mode {
                font-size: clamp(0.65rem, 3vw, 1rem);
                margin-bottom: 6px;
            }

            .bottom-area {
                padding: 10px 16px;
                border-top-width: 2px;
            }

            .sub-timer-digits {
                font-size: clamp(1.5rem, 7vw, 4rem);
            }

            .sub-timer-label {
                font-size: clamp(0.6rem, 2.5vw, 0.9rem);
                padding: 3px 12px;
            }
        }
    </style>
</head>

<body>

    <a href="{{ route('dashboard') }}" class="ghost-btn">
        <i class="bi bi-arrow-left-circle-fill fs-4"></i>
        <span>Voltar</span>
    </a>

    <div class="regressive-container">
        <div id="modeLabel" class="label-mode">AGUARDANDO...</div>
        <div id="mainTimer" class="big-timer status-normal">--:--:--</div>
    </div>

    <div class="bottom-area">

        <div id="progressiveBox" class="sub-timer-box active" style="display: block;">
            <div class="sub-timer-label">Bloco / Mochilink</div>
            <div id="stopwatchTimer" class="sub-timer-digits timer-green">00:00:00</div>
        </div>

        <div id="boxBk" class="sub-timer-box">
            <div class="sub-timer-label text-warning">VOLTA DO BK</div>
            <div id="timerBk" class="sub-timer-digits timer-yellow">00:00:00</div>
        </div>

    </div>

    <script>
        // --- ESTADO LOCAL ---
        let serverOffset = 0; // A diferença entre o relógio deste PC e do Servidor
        let targetTime = null; // O alvo da regressiva (timestamp)
        let bkTargetTime = null; // o tempo do bk
        let stopwatchStart = null; // O inicio da progressiva
        let stopwatchAccumulated = 0; // Tempo acumulado da progressiva
        let stopwatchStatus = 'stopped';

        // --- FUNÇÃO 1: SINCRONIZAR (A Mágica da Precisão) ---
        async function syncState() {
            try {
                // Marca a hora que saiu o pedido
                const requestStart = Date.now();

                const response = await fetch('/timers/status');
                const data = await response.json();

                // Marca a hora que chegou
                const now = Date.now();

                // Calcula o tempo de viagem (ping)
                const latency = (now - requestStart) / 2;

                // O horário real do servidor é: HoraQueEleDisse + Latência
                const serverTimeExact = data.server_time + latency;

                // Calculamos o offset: Quanto eu devo somar ao meu relógio pra bater com o servidor?
                serverOffset = serverTimeExact - now;

                // Atualiza dados da Regressiva
                targetTime = data.target_time;
                bkTargetTime = data.bk_target_time;
                document.getElementById('modeLabel').innerText = data.mode_label || 'LIVRE';

                // Atualiza dados da Progressiva
                stopwatchStart = data.stopwatch.started_at;
                stopwatchAccumulated = data.stopwatch.accumulated;
                stopwatchStatus = data.stopwatch.status;

            } catch (error) {
                console.error("Erro ao sincronizar:", error);
            }
        }

        // --- FUNÇÃO 2: RENDERIZAR (Roda 60x por segundo) ---
        function updateDisplay() {
            // Hora atual "Corrigida" (Hora deste PC + Diferença pro Servidor)
            const nowSynced = Date.now() + serverOffset;

            // 1. Lógica da REGRESSIVA
            const mainTimerEl = document.getElementById('mainTimer');

            if (targetTime) {
                // Cálculo Delta: Alvo - Agora
                let diff = targetTime - nowSynced;

                // Se estourou o tempo, mostra negativo ou zero?
                if (diff <= 0) {
                    diff = 0;
                    mainTimerEl.classList.remove('status-normal');
                    mainTimerEl.classList.add('status-critical');
                } else {
                    mainTimerEl.classList.remove('status-critical');
                    mainTimerEl.classList.add('status-normal');
                }

                mainTimerEl.innerText = formatMs(diff);
            } else {
                mainTimerEl.innerText = "--:--:--";
                mainTimerEl.classList.remove('status-critical');
                mainTimerEl.classList.add('status-normal');
            }

            // 2. Lógica da PROGRESSIVA (Stopwatch)
            const progBox = document.getElementById('progressiveBox');
            const progTimer = document.getElementById('stopwatchTimer');

            if (stopwatchStatus === 'running' && stopwatchStart) {
                progBox.classList.add('active');
                // Cálculo: (Agora - Inicio) + O que já tinha acumulado antes
                const elapsed = (nowSynced - stopwatchStart) + (stopwatchAccumulated * 1000);
                progTimer.innerText = formatMs(elapsed);
            } else if (stopwatchStatus === 'paused') {
                progBox.classList.add('active');
                // Mostra só o acumulado estático
                progTimer.innerText = formatMs(stopwatchAccumulated * 1000);
            } else {
                progBox.classList.remove('active');
                progTimer.innerText = "00:00:00";
            }

            // 3. REGRESSIVA BK (Lado Direito)
            const boxBk = document.getElementById('boxBk');
            const timerBk = document.getElementById('timerBk');

            if (bkTargetTime) {
                // Se tem BK configurado, mostra a caixa
                boxBk.style.display = 'block';
                boxBk.classList.add('active'); // Brilhante

                let diffBk = bkTargetTime - nowSynced;
                if (diffBk < 0) diffBk = 0;

                // Formata o texto padrão (HH:MM:SS)
                let textBk = formatMs(diffBk);

                // Se for menos de 1 hora (3600000 ms), remove o "00:" do começo
                if (diffBk < 3600000) {
                    textBk = textBk.substring(3); // "00:02:30" vira "02:30"
                }

                timerBk.innerText = textBk;
            } else {
                // Se não tem BK, esconde a caixa (Progressiva centraliza ou expande se usar flex-grow)
                boxBk.style.display = 'none';
                boxBk.classList.remove('active');
            }

            // Chama o próximo quadro de animação (suave, sem travar)
            requestAnimationFrame(updateDisplay);
        }

        // --- HELPER: Formata milissegundos em HH:MM:SS ---
        function formatMs(ms) {
            const totalSeconds = Math.floor(ms / 1000);
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;

            const hh = String(h).padStart(2, '0');
            const mm = String(m).padStart(2, '0');
            const ss = String(s).padStart(2, '0');

            return `${hh}:${mm}:${ss}`;
        }

        // --- INICIALIZAÇÃO ---
        setInterval(syncState, 2000);
        syncState().then(() => requestAnimationFrame(updateDisplay));

    </script>
</body>

</html>