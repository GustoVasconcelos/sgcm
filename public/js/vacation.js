function updateForm() {
    const mode = document.getElementById('modeSelector').value;
    const box2 = document.getElementById('period2_box');
        
    // Pega inputs do período 2 para controlar o 'required'
    const p2Inputs = box2.querySelectorAll('input');

    // Modos que usam APENAS 1 período
    if (mode === '30_dias' || mode === '20_venda') {
        box2.classList.add('d-none'); // Esconde o 2º
        removeRequired(p2Inputs);
    }
    // Modos que usam 2 períodos (15/15 OU 20/10)
    else if (mode === '15_15' || mode === '20_10') {
        box2.classList.remove('d-none'); // Mostra o 2º
        setRequired(p2Inputs);
    }
}

function setRequired(inputs) {
    inputs.forEach(input => input.setAttribute('required', 'required'));
}

function removeRequired(inputs) {
    inputs.forEach(input => {
        input.removeAttribute('required');
        input.value = ''; // Limpa o valor se esconder
    });
}

// usado na pagina de edit
document.addEventListener('DOMContentLoaded', function() {
    updateForm();
});