// Funcoes JS do pagina de montagem de escalas
function toggleCheckboxes(state) {
    document.querySelectorAll('.recipient-checkbox').forEach(el => el.checked = state);
}

function regenerateDay(date, mode) {
    if(confirm('Isso resetará os operadores deste dia. Confirmar?')) {
        document.getElementById('regDate').value = date;
        document.getElementById('regMode').value = mode;
        document.getElementById('regenerateForm').submit();
    }
}