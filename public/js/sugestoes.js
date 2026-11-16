(function () {
    'use strict';

    document.querySelectorAll('.textarea-wrap textarea').forEach(function (textarea) {
        var counter = textarea.parentElement.querySelector('.char-count');
        if (!counter) {
            return;
        }

        var updateCounter = function () {
            counter.textContent = textarea.value.length + '/' + textarea.maxLength;
        };
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });

    var modals = Array.from(document.querySelectorAll('.sugestoes-modal-fundo'));
    if (!modals.length) {
        return;
    }

    var triggerAtivo = null;
    var modalAtivo = null;

    function abrirModal(modal, trigger) {
        modalAtivo = modal;
        triggerAtivo = trigger || null;
        modal.hidden = false;
        document.body.classList.add('sugestoes-modal-aberto');
        var campo = modal.querySelector('input:not([type="hidden"]), textarea, select');
        if (campo) {
            campo.focus();
        }
    }

    function fecharModal() {
        if (!modalAtivo) {
            return;
        }

        modalAtivo.hidden = true;
        document.body.classList.remove('sugestoes-modal-aberto');
        if (triggerAtivo) {
            triggerAtivo.focus();
        }
        triggerAtivo = null;
        modalAtivo = null;
    }

    document.querySelectorAll('[data-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var modal = document.getElementById(trigger.dataset.modal);
            if (modal) {
                abrirModal(modal, trigger);
            }
        });
    });

    modals.forEach(function (modal) {
        var closeButton = modal.querySelector('.sugestoes-modal-fechar');
        closeButton.addEventListener('click', fecharModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                fecharModal();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            fecharModal();
        }
    });

    var modalInicial = modals.find(function (modal) {
        return modal.dataset.modalInicial === '1';
    });
    if (modalInicial) {
        abrirModal(modalInicial);
    }
})();
