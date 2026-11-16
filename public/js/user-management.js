(function () {
    'use strict';

    document.querySelectorAll('.usuarios-nivel-form').forEach((form) => {
        const select = form.querySelector('select[name="nivel"]');
        const button = form.querySelector('button[type="submit"]');
        const nivelOriginal = select.dataset.perfilOriginal;

        select.addEventListener('change', () => {
            button.disabled = select.value === nivelOriginal;
        });

        form.addEventListener('submit', (event) => {
            const estaRebaixandoPropriaConta = button.dataset.proprio === '1'
                && nivelOriginal === 'admin'
                && select.value === 'padrao';
            if (estaRebaixandoPropriaConta
                && !window.confirm('Ao continuar, sua conta perderá o acesso de administrador. Deseja rebaixar seu perfil?')) {
                event.preventDefault();
                return;
            }
            button.disabled = true;
        });
    });

    document.querySelectorAll('.usuarios-excluir-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const nome = form.dataset.usuarioNome || 'este usuário';
            const confirmar = window.confirm(
                'Deseja excluir a conta de "' + nome + '"? Esta ação não pode ser desfeita. ' +
                'Contas com demandas, sugestões, avisos ou outros registros vinculados não podem ser excluídas.'
            );
            if (!confirmar) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.usuarios-filtro-perfil select').forEach((select) => {
        select.addEventListener('change', () => select.form.submit());
    });
}());
