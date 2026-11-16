(function () {
    'use strict';

    const panel = document.getElementById('historico-painel');
    const modal = document.getElementById('historico-modal-fundo');
    const form = document.getElementById('historico-formulario');
    const tableBody = document.getElementById('historico-itens');
    if (!panel || !modal || !form || !tableBody) {
        return;
    }

    const baseUrl = panel.dataset.url;
    const type = panel.dataset.tipo;
    const orderIds = JSON.parse(panel.dataset.ordemIds || '[]');
    const categorySets = {
        demanda: JSON.parse(panel.dataset.categoriasDemanda || '{}'),
        sugestao: JSON.parse(panel.dataset.categoriasSugestao || '{}')
    };
    const statusOptions = JSON.parse(panel.dataset.statusOptions || '{}');
    const titleInput = document.getElementById('historico-titulo');
    const descriptionInput = document.getElementById('historico-descricao');
    const categorySelect = document.getElementById('historico-categoria');
    const statusSelect = document.getElementById('historico-status');
    const statusEditor = document.querySelector('.historico-status-editor');
    const statusReadOnly = document.querySelector('.historico-status-leitura');
    const countLabel = document.querySelector('.historico-contador');
    const modalFeedback = document.querySelector('.historico-modal-feedback');
    const pageFeedback = document.getElementById('historico-feedback');
    const saveButton = document.querySelector('.historico-salvar');
    const deleteButton = document.querySelector('.historico-modal-excluir');
    const toggleButton = document.querySelector('.historico-modal-alternar');
    const closeButton = document.querySelector('.historico-modal-fechar');
    let activeButton = null;
    let activeCanChangeStatus = false;
    let draggedRow = null;
    let dropHandled = false;

    function setFeedback(element, message, success) {
        element.textContent = message;
        element.classList.toggle('sucesso', Boolean(success));
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

    function setEditing(enabled) {
        titleInput.disabled = !enabled;
        descriptionInput.disabled = !enabled;
        categorySelect.disabled = !enabled;
        statusSelect.disabled = !enabled || !activeCanChangeStatus;
        saveButton.disabled = !enabled;
        toggleButton.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        toggleButton.setAttribute('aria-label', enabled ? 'Bloquear edição' : 'Habilitar edição');
    }

    function openEditor(button) {
        activeButton = button;
        const itemType = button.dataset.tipo;
        deleteButton.hidden = button.dataset.proprio !== '1';
        deleteButton.disabled = false;
        form.elements.tipo.value = itemType;
        form.elements.id.value = button.dataset.id;
        document.getElementById('historico-modal-titulo').textContent =
            itemType === 'demanda' ? 'Editar demanda' : 'Editar sugestão';
        document.querySelector('label[for="historico-titulo"]').textContent =
            itemType === 'demanda' ? 'Título da demanda' : 'Título da sugestão';
        document.querySelector('.historico-status-leitura span').textContent = button.dataset.status;
        activeCanChangeStatus = button.dataset.podeAlterarStatus === '1';
        statusEditor.hidden = !activeCanChangeStatus;
        statusReadOnly.hidden = activeCanChangeStatus;
        statusSelect.replaceChildren();
        Object.entries(statusOptions).forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            statusSelect.appendChild(option);
        });
        statusSelect.value = button.dataset.statusKey;
        titleInput.value = button.dataset.titulo;
        descriptionInput.value = button.dataset.descricao;
        categorySelect.replaceChildren();
        Object.entries(categorySets[itemType]).forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            categorySelect.appendChild(option);
        });
        categorySelect.value = button.dataset.categoria;
        countLabel.textContent = descriptionInput.value.length + '/600';
        setFeedback(modalFeedback, '', false);
        setEditing(true);
        modal.hidden = false;
        document.body.classList.add('historico-modal-aberto');
        titleInput.focus();
    }

    function closeEditor() {
        modal.hidden = true;
        document.body.classList.remove('historico-modal-aberto');
        if (activeButton) {
            activeButton.focus();
        }
        activeButton = null;
        activeCanChangeStatus = false;
    }

    document.querySelectorAll('.historico-editar').forEach((button) => {
        button.addEventListener('click', () => openEditor(button));
    });

    closeButton.addEventListener('click', closeEditor);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeEditor();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeEditor();
        }
    });

    descriptionInput.addEventListener('input', () => {
        countLabel.textContent = descriptionInput.value.length + '/600';
    });

    toggleButton.addEventListener('click', () => {
        setEditing(toggleButton.getAttribute('aria-pressed') !== 'true');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        saveButton.disabled = true;
        setFeedback(modalFeedback, 'Salvando alterações...', true);
        try {
            const result = await post(form.action, new FormData(form));
            setFeedback(modalFeedback, result.message, true);
            window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            setFeedback(modalFeedback, error.message, false);
            saveButton.disabled = false;
        }
    });

    deleteButton.addEventListener('click', async () => {
        const itemName = form.elements.tipo.value === 'demanda' ? 'demanda' : 'sugestão';
        if (!window.confirm('Deseja excluir definitivamente esta ' + itemName + '? Esta ação não pode ser desfeita.')) {
            return;
        }

        deleteButton.disabled = true;
        const data = new FormData();
        data.append('csrf', form.elements.csrf.value);
        data.append('tipo', form.elements.tipo.value);
        data.append('id', form.elements.id.value);
        setFeedback(modalFeedback, 'Excluindo registro...', true);
        try {
            const result = await post(baseUrl + '/historico/excluir', data);
            setFeedback(modalFeedback, result.message, true);
            window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            setFeedback(modalFeedback, error.message, false);
            deleteButton.disabled = false;
        }
    });

    document.querySelectorAll('.historico-respostas-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const details = document.getElementById(button.getAttribute('aria-controls'));
            const expanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            details.hidden = expanded;
        });
    });

    tableBody.addEventListener('submit', async (event) => {
        const responseForm = event.target.closest('.historico-responder-form');
        if (!responseForm) {
            return;
        }
        event.preventDefault();
        const button = responseForm.querySelector('button[type="submit"]');
        const feedback = responseForm.querySelector('.historico-resposta-feedback');
        button.disabled = true;
        feedback.textContent = 'Enviando resposta...';
        try {
            const result = await post(responseForm.action, new FormData(responseForm));
            feedback.textContent = result.message;
            window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            feedback.textContent = error.message;
            button.disabled = false;
        }
    });

    tableBody.addEventListener('dragstart', (event) => {
        const handle = event.target.closest('.historico-ordenar');
        if (!handle) {
            event.preventDefault();
            return;
        }
        draggedRow = handle.closest('tr');
        dropHandled = false;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', draggedRow.dataset.id);
        window.setTimeout(() => draggedRow && draggedRow.classList.add('arrastando'), 0);
    });

    tableBody.addEventListener('dragover', (event) => {
        if (!draggedRow) {
            return;
        }
        event.preventDefault();
        const targetRow = event.target.closest('tr[data-id]');
        if (!targetRow || targetRow === draggedRow) {
            return;
        }
        targetRow.classList.add('alvo-arraste');
        const bounds = targetRow.getBoundingClientRect();
        const insertAfter = event.clientY > bounds.top + bounds.height / 2;
        tableBody.insertBefore(draggedRow, insertAfter ? targetRow.nextSibling : targetRow);
        tableBody.querySelectorAll('.alvo-arraste').forEach((row) => row.classList.remove('alvo-arraste'));
    });

    tableBody.addEventListener('drop', async (event) => {
        if (!draggedRow) {
            return;
        }
        event.preventDefault();
        dropHandled = true;
        draggedRow.classList.remove('arrastando');
        draggedRow = null;

        const visibleIds = Array.from(tableBody.querySelectorAll('tr[data-id]'), (row) => Number(row.dataset.id));
        const rowNumbers = Array.from(tableBody.querySelectorAll('tr[data-id] .col-numero'), (cell) => Number(cell.textContent));
        const start = rowNumbers.length ? Math.max(0, Math.min(...rowNumbers) - 1) : 0;
        orderIds.splice(start, visibleIds.length, ...visibleIds);
        const data = new FormData();
        data.append('csrf', form.elements.csrf.value);
        data.append('tipo', type);
        data.append('ids', JSON.stringify(orderIds));
        setFeedback(pageFeedback, 'Salvando a nova ordem...', true);
        try {
            const result = await post(baseUrl + '/historico/reordenar', data);
            setFeedback(pageFeedback, result.message, true);
            window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            setFeedback(pageFeedback, error.message, false);
            window.setTimeout(() => window.location.reload(), 1800);
        }
    });

    tableBody.addEventListener('dragend', () => {
        if (draggedRow) {
            draggedRow.classList.remove('arrastando');
            draggedRow = null;
        }
        tableBody.querySelectorAll('.alvo-arraste').forEach((row) => row.classList.remove('alvo-arraste'));
        if (!dropHandled && tableBody.querySelector('.arrastando')) {
            window.location.reload();
        }
    });
}());
