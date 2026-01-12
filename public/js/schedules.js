
// Preenche duração automática ao escolher programa
document.getElementById('programSelect').addEventListener('change', function() {
    var duration = this.options[this.selectedIndex].getAttribute('data-duration');
    if(duration) document.getElementById('durationInput').value = duration;
});

// Função AJAX para toggle sem recarregar
function toggleStatus(btn, id, type) {
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
                
            // Pinta a linha se ambos estiverem OK (lógica simples visual)
            const row = btn.closest('tr');
            const magoBtn = row.querySelector('.btn-mago');
            const verifBtn = row.querySelector('.btn-verif');
                
            // Verifica classes para pintar a linha
            if (magoBtn.classList.contains('btn-toggle-on') && verifBtn.classList.contains('btn-toggle-on')) {
                row.classList.add('row-ok');
            } else {
                row.classList.remove('row-ok');
            }
        }
    });
}