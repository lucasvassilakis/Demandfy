(function () {
    'use strict';

    const panel = document.querySelector('.eventos-painel');
    const listModal = document.getElementById('eventos-lista-fundo');
    const formModal = document.getElementById('evento-form-fundo');
    const form = document.getElementById('evento-form');
    const eventList = document.getElementById('eventos-editor-lista');
    if (!panel || !listModal || !formModal || !form || !eventList) {
        return;
    }

    const baseUrl = panel.dataset.url;
    const selectedDate = panel.dataset.data;
    const csrf = form.elements.csrf.value;
    const formFeedback = document.getElementById('evento-form-feedback');
    const listFeedback = document.getElementById('eventos-lista-feedback');
    const saveButton = document.getElementById('evento-form-salvar');
    const saveOrderButton = document.getElementById('eventos-salvar-ordem');
    let formOrigin = 'panel';
    let activeElement = null;
    let draggedRow = null;
    let orderChanged = false;

    function setFeedback(element, message, success) {
        element.textContent = message;
        element.classList.toggle('sucesso', Boolean(success));
    }

    function updateModalLock() {
        document.body.classList.toggle(
            'calendario-modal-aberto',
            !listModal.hidden || !formModal.hidden
        );
    }

    async function post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { Accept: 'application/json' }
        });
        let result;
        try {
            result = await response.json();
        } catch (error) {
            throw new Error('O servidor retornou uma resposta inválida. Atualize a página e tente novamente.');
        }
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Não foi possível concluir a ação.');
        }
        return result;
    }

    function openList() {
        activeElement = document.activeElement;
        listModal.hidden = false;
        updateModalLock();
        const closeButton = listModal.querySelector('[data-fechar-modal]');
        closeButton.focus();
    }

    function closeList() {
        listModal.hidden = true;
        updateModalLock();
        if (activeElement && typeof activeElement.focus === 'function') {
            activeElement.focus();
        }
    }

    function openForm(button, origin) {
        formOrigin = origin;
        if (origin === 'lista') {
            listModal.hidden = true;
        } else {
            activeElement = document.activeElement;
        }

        form.reset();
        form.elements.id.value = '';
        form.elements.data.value = selectedDate;
        form.action = baseUrl + '/calendario/criarEvento';
        document.getElementById('evento-form-titulo').textContent = 'Adicionar evento';
        saveButton.textContent = 'Salvar evento';

        if (button) {
            const eventType = button.dataset;
            form.elements.id.value = eventType.id;
            form.action = baseUrl + '/calendario/atualizarEvento';
            form.elements.titulo.value = eventType.titulo;
            form.elements.descricao.value = eventType.descricao;
            form.elements.categoria_id.value = eventType.categoriaId;
            form.elements.hora_inicio.value = eventType.horaInicio.slice(0, 5);
            form.elements.hora_fim.value = eventType.horaFim.slice(0, 5);
            document.getElementById('evento-form-titulo').textContent = 'Editar evento';
            saveButton.textContent = 'Salvar alterações';
        }

        setFeedback(formFeedback, '', false);
        saveButton.disabled = false;
        formModal.hidden = false;
        updateModalLock();
        document.getElementById('evento-titulo').focus();
    }

    function closeForm() {
        formModal.hidden = true;
        if (formOrigin === 'lista') {
            listModal.hidden = false;
            listModal.querySelector('[data-fechar-modal]').focus();
        } else if (activeElement && typeof activeElement.focus === 'function') {
            activeElement.focus();
        }
        updateModalLock();
    }

    document.getElementById('evento-adicionar').addEventListener('click', () => openForm(null, 'painel'));
    document.getElementById('evento-gerenciar').addEventListener('click', openList);
    document.getElementById('eventos-lista-adicionar').addEventListener('click', () => openForm(null, 'lista'));

    document.querySelectorAll('[data-fechar-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.closest('#evento-form-fundo')) {
                closeForm();
            } else {
                closeList();
            }
        });
    });

    [listModal, formModal].forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target !== modal) {
                return;
            }
            if (modal === formModal) {
                closeForm();
            } else {
                closeList();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        if (!formModal.hidden) {
            closeForm();
        } else if (!listModal.hidden) {
            closeList();
        }
    });

    eventList.addEventListener('click', async (event) => {
        const editButton = event.target.closest('.evento-item-editar');
        if (editButton) {
            openForm(editButton, 'lista');
            return;
        }

        const removeButton = event.target.closest('.evento-item-remover');
        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('.evento-gerenciavel');
        if (!window.confirm('Deseja remover este evento? Esta ação não pode ser desfeita.')) {
            return;
        }
        const data = new FormData();
        data.append('csrf', csrf);
        data.append('id', row.dataset.id);
        data.append('data', selectedDate);
        removeButton.disabled = true;
        setFeedback(listFeedback, 'Removendo evento...', true);
        try {
            const result = await post(baseUrl + '/calendario/excluirEvento', data);
            setFeedback(listFeedback, result.message, true);
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            setFeedback(listFeedback, error.message, false);
            removeButton.disabled = false;
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        saveButton.disabled = true;
        setFeedback(formFeedback, 'Salvando evento...', true);
        try {
            const result = await post(form.action, new FormData(form));
            setFeedback(formFeedback, result.message, true);
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            setFeedback(formFeedback, error.message, false);
            saveButton.disabled = false;
        }
    });

    eventList.addEventListener('dragstart', (event) => {
        const handle = event.target.closest('.evento-item-ordenar');
        if (!handle) {
            event.preventDefault();
            return;
        }
        draggedRow = handle.closest('.evento-gerenciavel');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', draggedRow.dataset.id);
        window.setTimeout(() => draggedRow && draggedRow.classList.add('arrastando'), 0);
    });

    eventList.addEventListener('dragover', (event) => {
        if (!draggedRow) {
            return;
        }
        event.preventDefault();
        const targetRow = event.target.closest('.evento-gerenciavel');
        if (!targetRow || targetRow === draggedRow) {
            return;
        }
        const bounds = targetRow.getBoundingClientRect();
        const insertAfter = event.clientY > bounds.top + bounds.height / 2;
        eventList.insertBefore(draggedRow, insertAfter ? targetRow.nextSibling : targetRow);
        eventList.querySelectorAll('.alvo-arraste').forEach((row) => row.classList.remove('alvo-arraste'));
        targetRow.classList.add('alvo-arraste');
    });

    eventList.addEventListener('drop', (event) => {
        if (!draggedRow) {
            return;
        }
        event.preventDefault();
        draggedRow.classList.remove('arrastando');
        draggedRow = null;
        eventList.querySelectorAll('.alvo-arraste').forEach((row) => row.classList.remove('alvo-arraste'));
        orderChanged = true;
        saveOrderButton.disabled = false;
        setFeedback(listFeedback, 'A ordem foi alterada. Salve para aplicar.', true);
    });

    eventList.addEventListener('dragend', () => {
        if (draggedRow) {
            draggedRow.classList.remove('arrastando');
            draggedRow = null;
        }
        eventList.querySelectorAll('.alvo-arraste').forEach((row) => row.classList.remove('alvo-arraste'));
    });

    saveOrderButton.addEventListener('click', async () => {
        if (!orderChanged) {
            return;
        }
        const ids = Array.from(eventList.querySelectorAll('.evento-gerenciavel'), (row) => Number(row.dataset.id));
        const data = new FormData();
        data.append('csrf', csrf);
        data.append('data', selectedDate);
        data.append('ids', JSON.stringify(ids));
        saveOrderButton.disabled = true;
        setFeedback(listFeedback, 'Salvando a ordem...', true);
        try {
            const result = await post(baseUrl + '/calendario/reordenarEventos', data);
            setFeedback(listFeedback, result.message, true);
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            setFeedback(listFeedback, error.message, false);
            saveOrderButton.disabled = false;
        }
    });
}());
