// --- MODAL: ZERAR BANCO DE DADOS ---
(function () {
    const passwordInput = document.getElementById('modalPasswordInput');
    const confirmBtn = document.getElementById('btnConfirmClearAll');
    const hiddenPassword = document.getElementById('clearAllPassword');
    const form = document.getElementById('formClearAll');
    const modal = document.getElementById('modalClearAll');

    // Habilita o botão de confirmar apenas quando o campo não está vazio
    passwordInput.addEventListener('input', function () {
        confirmBtn.disabled = this.value.trim() === '';
    });

    // Ao confirmar: copia a senha para o hidden input e submete o formulário
    confirmBtn.addEventListener('click', function () {
        hiddenPassword.value = passwordInput.value;
        form.submit();
    });

    // Limpa o campo e redefine o botão ao fechar o modal
    modal.addEventListener('hidden.bs.modal', function () {
        passwordInput.value = '';
        hiddenPassword.value = '';
        confirmBtn.disabled = true;
    });
})();
