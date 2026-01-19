// --- 1. FUNÇÕES AUXILIARES DE TEMPO ---
    function secondsToTime(seconds) {
        const h = Math.floor(Math.abs(seconds) / 3600);
        const m = Math.floor((Math.abs(seconds) % 3600) / 60);
        const s = Math.abs(seconds) % 60;
        return (seconds < 0 ? "-" : "") + [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
    }

    function timeToSeconds(timeStr) {
        if (!timeStr) return 0;
        const parts = timeStr.split(':').reverse();
        let seconds = 0;
        if (parts[0]) seconds += parseInt(parts[0]);
        if (parts[1]) seconds += parseInt(parts[1]) * 60;
        if (parts[2]) seconds += parseInt(parts[2]) * 3600;
        return seconds;
    }

    function formatInput(input) {
        let val = input.value.replace(/\D/g, '');
        if (val === "") { 
            input.value = ""; 
            saveData(); calculate(); return; 
        }
        val = val.slice(-6); 
        const padded = val.padStart(6, '0');
        input.value = `${padded.slice(0, 2)}:${padded.slice(2, 4)}:${padded.slice(4, 6)}`;
        saveData();
        calculate();
    }

    // --- 2. GERENCIAMENTO DE LINHAS ---
    function createRowHtml(index, value = "") {
        return `
            <div class="row mb-2 g-2 align-items-center row-entry" id="row-${index}" name="row-${index}">
                <div class="col-auto">
                    <span class="badge bg-secondary rounded-pill" hidden style="width: 25px;">${index + 1}</span>
                </div>
                <div class="col">
                    <input type="text" class="form-control input-time tuning-input" id="${index}" name="${index}"
                           placeholder="00:00:00" value="${value}"
                           oninput="formatInput(this)" onfocus="this.select()" autocomplete="off">
                </div>
                <div class="col-auto" style="min-width: 80px;">
                    <span class="acc-label" hidden>Acumulado</span>
                    <span class="acc-display">--:--:--</span>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-danger btn-sm" onclick="removeRow('${index}')" tabindex="-1">x</button>
                </div>
            </div>
        `;
    }

    function generateRows() {
        const count = document.getElementById('bulkCount').value || 15;
        const container = document.getElementById('rowsContainer');
        container.innerHTML = ''; 
        for (let i = 0; i < count; i++) {
            container.insertAdjacentHTML('beforeend', createRowHtml(i));
        }
        saveData();
        calculate();
    }

    function addRow() {
        const container = document.getElementById('rowsContainer');
        const uniqueId = Date.now(); 
        container.insertAdjacentHTML('beforeend', createRowHtml(uniqueId));
        reindexBadges();
        saveData();
    }

    function removeRow(id) {
        const row = document.getElementById(`row-${id}`);
        if(row) row.remove();
        reindexBadges();
        calculate();
        saveData();
    }
    
    function reindexBadges() {
        const rows = document.querySelectorAll('.row-entry');
        rows.forEach((row, index) => {
            const badge = row.querySelector('.badge');
            if(badge) badge.innerText = index + 1;
        });
    }

    function clearAll() {
        if(confirm('Tem certeza que deseja zerar tudo?')) {

            // REGISTRO DE LOG
            fetch('/log/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Importante para o Laravel aceitar
                },
                body: JSON.stringify({
                    module: 'Afinação',
                    action: 'Limpar Tudo',
                    details: { timestamp: new Date().toLocaleString() }
                })
            });

            localStorage.removeItem('sgcm_afiacao_data');
            document.getElementById('rowsContainer').innerHTML = '';
            document.getElementById('targetInput').value = '';
            generateRows();
        }
    }

    // --- 3. CÁLCULO E VISUALIZAÇÃO ---
    function calculate() {
        const rows = document.querySelectorAll('.row-entry');
        let totalSeconds = 0;
        let accumulatedSeconds = 0;

        rows.forEach((row, index) => {
            const input = row.querySelector('.input-time');
            const accDisplay = row.querySelector('.acc-display');
            
            const currentVal = timeToSeconds(input.value);
            accumulatedSeconds += currentVal;
            totalSeconds += currentVal;

            if (index === 0) {
                accDisplay.style.visibility = 'hidden'; 
            } else {
                accDisplay.style.visibility = 'visible';
                accDisplay.innerText = secondsToTime(accumulatedSeconds);
            }
        });

        document.getElementById('displaySum').innerText = secondsToTime(totalSeconds);

        const targetStr = document.getElementById('targetInput').value;
        const resultCard = document.getElementById('resultCard');
        const resultTitle = document.getElementById('resultTitle');
        const resultMessage = document.getElementById('resultMessage');
        const displayDiff = document.getElementById('displayDiff');

        if (!targetStr || targetStr === "00:00:00") {
            resultCard.className = 'card shadow-lg';
            resultTitle.innerText = "DEFINA O TEMPO LIMITE";
            displayDiff.innerText = "--:--:--";
            resultMessage.innerText = "Defina o tempo limite";
            return;
        }

        const targetSeconds = timeToSeconds(targetStr);
        const diff = targetSeconds - totalSeconds;
        displayDiff.innerText = secondsToTime(Math.abs(diff));

        if (diff === 0) {
            resultCard.className = 'card shadow-lg status-tuned';
            resultTitle.innerText = "JORNAL AFINADO";
            resultMessage.innerText = "Jornal OK!";
        } else if (diff > 0) {
            resultCard.className = 'card shadow-lg status-ok';
            resultTitle.innerText = "SOBRA DE TEMPO";
            resultMessage.innerText = "Sobrando tempo no jornal";
        } else {
            resultCard.className = 'card shadow-lg status-danger';
            resultTitle.innerText = "ESTOURANDO TEMPO";
            resultMessage.innerText = "Estourando tempo do jornal!";
        }
    }

    // --- 4. PERSISTÊNCIA ---
    function saveData() {
        const inputs = document.querySelectorAll('.row-entry .input-time');
        const values = Array.from(inputs).map(input => input.value);
        const target = document.getElementById('targetInput').value;
        localStorage.setItem('sgcm_afiacao_data', JSON.stringify({ rows: values, target: target }));
    }

    function loadData() {
        const saved = localStorage.getItem('sgcm_afiacao_data');
        if (saved) {
            const data = JSON.parse(saved);
            const container = document.getElementById('rowsContainer');
            container.innerHTML = '';
            if (data.rows && data.rows.length > 0) {
                data.rows.forEach((val, idx) => {
                    container.insertAdjacentHTML('beforeend', createRowHtml(idx + 9999, val));
                });
            } else {
                generateRows();
            }
            reindexBadges();
            if (data.target) document.getElementById('targetInput').value = data.target;
            calculate();
        } else {
            generateRows();
        }
    }

    document.addEventListener('DOMContentLoaded', loadData);

    // --- 5. SISTEMA DE ATALHOS (KEYBOARD SHORTCUTS) ---
    document.addEventListener('keydown', function(event) {
        
        // Pega todos os inputs visíveis naquele momento
        const inputs = Array.from(document.querySelectorAll('.tuning-input'));
        const currentInput = document.activeElement;
        const currentIndex = inputs.indexOf(currentInput);

        // Se o foco não estiver em um input de afinação, ignora (exceto F1 que pode focar vindo do nada)
        if (!inputs.includes(currentInput) && event.key !== 'F1') return;

        // F1: IR PARA O TOPO
        if (event.key === 'F1') {
            event.preventDefault();
            if (inputs.length > 0) {
                inputs[0].focus();
                inputs[0].select(); 
            }
            return;
        }

        // F2: AVANÇAR CAMPO (PRÓXIMO)
        if (event.key === 'F2') {
            event.preventDefault();
            if (currentIndex >= 0 && currentIndex < inputs.length - 1) {
                const nextInput = inputs[currentIndex + 1];
                nextInput.focus();
                nextInput.select();
            }
            return;
        }

        // F3: VOLTAR CAMPO (ANTERIOR)
        if (event.key === 'F3') {
            event.preventDefault();
            if (currentIndex > 0) {
                const prevInput = inputs[currentIndex - 1];
                prevInput.focus();
                prevInput.select();
            }
            return;
        }

        // F4: EXCLUIR LINHA ATUAL
        if (event.key === 'F4') {
            event.preventDefault();
            const currentRow = currentInput.closest('.row-entry');
            if (currentRow) {
                const nextRow = currentRow.nextElementSibling;
                const prevRow = currentRow.previousElementSibling;
                currentRow.remove();

                if (nextRow) {
                    const nextInput = nextRow.querySelector('.tuning-input');
                    if(nextInput) { nextInput.focus(); nextInput.select(); }
                } else if (prevRow) {
                    const prevInput = prevRow.querySelector('.tuning-input');
                    if(prevInput) { prevInput.focus(); prevInput.select(); }
                }
                reindexBadges();
                calculate();
                saveData();
            }
        }

        // F5: INSERIR NOVA LINHA ABAIXO (NOVO)
        if (event.key === 'F5') {
            event.preventDefault(); // IMPORTANTE: Bloqueia o Refresh da página
            
            const currentRow = currentInput.closest('.row-entry');
            if (currentRow) {
                // Gera ID único
                const uniqueId = Date.now();
                // Cria o HTML da nova linha
                const newRowHtml = createRowHtml(uniqueId, "");
                
                // Insere logo APÓS a linha atual
                currentRow.insertAdjacentHTML('afterend', newRowHtml);

                // Pega a linha recém criada (é o próximo irmão da atual)
                const newRow = currentRow.nextElementSibling;
                
                // Foca no input dessa nova linha
                if (newRow) {
                    const newInput = newRow.querySelector('.tuning-input');
                    if(newInput) {
                        newInput.focus();
                        newInput.select();
                    }
                }

                // Atualiza tudo
                reindexBadges();
                saveData();
                calculate();
            }
        }
    });