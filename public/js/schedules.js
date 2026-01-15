document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Lógica do Modal de CADASTRO (Já existia) ---
    // Atualiza a duração automaticamente ao escolher o programa
    const programSelect = document.getElementById('programSelect');
    const durationInput = document.getElementById('durationInput');

    if(programSelect) {
        programSelect.addEventListener('change', function() {
            var duration = this.options[this.selectedIndex].getAttribute('data-duration');
            if(duration && durationInput) durationInput.value = duration;
        });
    }

    // --- 2. Lógica do Modal de EDIÇÃO (NOVO) ---
    // Faz a mesma coisa (atualiza duração), mas no modal de editar
    const editProgramSelect = document.getElementById('edit_program_id');
    const editDurationInput = document.getElementById('edit_duration');

    if(editProgramSelect) {
        editProgramSelect.addEventListener('change', function() {
            var duration = this.options[this.selectedIndex].getAttribute('data-duration');
            if(duration && editDurationInput) editDurationInput.value = duration;
        });
    }
});

// --- 3. Função para abrir o Modal de Edição (NOVO) ---
function openEditModal(button) {
    // 1. Recupera os dados escondidos no botão
    const id = button.getAttribute('data-id');
    const date = button.getAttribute('data-date');
    const programId = button.getAttribute('data-program-id');
    const startTime = button.getAttribute('data-start-time');
    const duration = button.getAttribute('data-duration');
    const customInfo = button.getAttribute('data-custom-info');
    const notes = button.getAttribute('data-notes');

    // 2. Preenche os campos do Modal de Edição
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_program_id').value = programId;
    document.getElementById('edit_start_time').value = startTime;
    document.getElementById('edit_duration').value = duration;
    document.getElementById('edit_custom_info').value = customInfo || '';
    document.getElementById('edit_notes').value = notes || '';

    // 3. Atualiza a rota do formulário para editar o ID correto
    const form = document.getElementById('editScheduleForm');
    form.action = '/schedules/' + id;

    // 4. Abre o modal usando o Bootstrap
    const modalEl = document.getElementById('editScheduleModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// --- 4. Função AJAX (Mantida a sua versão original) ---
function toggleStatus(btn, id, type) {
    // Trava o botão para não clicar 2x
    btn.disabled = true;

    fetch(`/schedules/${id}/toggle/${type}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Atualiza visual do botão
            if (data.new_status) {
                btn.classList.remove('btn-toggle-off');
                btn.classList.add('btn-toggle-on');
                btn.innerHTML = '<i class="bi bi-check-lg"></i> OK';
            } else {
                btn.classList.remove('btn-toggle-on');
                btn.classList.add('btn-toggle-off');
                btn.innerHTML = 'Pendente';
            }
                
            // Pinta a linha se ambos estiverem OK
            const row = btn.closest('tr');
            const magoBtn = row.querySelector('.btn-mago');
            const verifBtn = row.querySelector('.btn-verif');
                
            if (magoBtn.classList.contains('btn-toggle-on') && verifBtn.classList.contains('btn-toggle-on')) {
                row.classList.add('row-ok');
            } else {
                row.classList.remove('row-ok');
            }
        }
    })
    .catch(error => console.error('Erro:', error))
    .finally(() => {
        btn.disabled = false; // Destrava o botão
    });
}