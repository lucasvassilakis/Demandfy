(function () {
    'use strict';

    const panel = document.querySelector('.avisos[data-url]');
    const modal = document.getElementById('aviso-modal-fundo');
    const form = document.getElementById('aviso-form');
    const addButton = document.getElementById('aviso-adicionar');
    if (!panel || !modal || !form || !addButton) {
        return;
    }

    const baseUrl = panel.dataset.url;
    const csrf = form.elements.namedItem('csrf').value;
    const title = document.getElementById('aviso-modal-titulo');
    const subtitle = document.getElementById('aviso-modal-subtitulo');
    const description = document.getElementById('aviso-descricao');
    const counter = document.getElementById('aviso-contador');
    const feedback = document.getElementById('aviso-modal-feedback');
    const saveButton = document.getElementById('aviso-form-salvar');
    const deleteButton = document.getElementById('aviso-form-excluir');
    let activeElement = null;

    function setFeedback(message, success) {
        feedback.textContent = message;
        feedback.classList.toggle('sucesso', Boolean(success));
    }

    function updateCounter() {
        counter.textContent = Array.from(description.value).length + '/200';
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

    function openModal(button) {
        activeElement = document.activeElement;
        form.reset();
        form.elements.namedItem('id').value = '';
        form.action = baseUrl + '/avisos/criarAviso';
        title.textContent = 'Adicionar aviso';
        subtitle.textContent = 'Adicione um aviso ou lembrete para os usuários.';
        saveButton.textContent = 'Enviar aviso';
        deleteButton.hidden = true;
        saveButton.disabled = false;
        deleteButton.disabled = false;

        if (button) {
            form.elements.namedItem('id').value = button.dataset.id;
            form.elements.namedItem('titulo').value = button.dataset.titulo;
            form.elements.namedItem('descricao').value = button.dataset.descricao;
            form.elements.namedItem('categoria_id').value = button.dataset.categoriaId;
            form.action = baseUrl + '/avisos/atualizarAviso';
            title.textContent = 'Editar aviso';
            subtitle.textContent = 'Altere as informações ou exclua este aviso.';
            saveButton.textContent = 'Salvar alterações';
            deleteButton.hidden = false;
        }

        setFeedback('', false);
        updateCounter();
        modal.hidden = false;
        document.body.classList.add('aviso-modal-aberto');
        document.getElementById('aviso-titulo').focus();
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('aviso-modal-aberto');
        if (activeElement && typeof activeElement.focus === 'function') {
            activeElement.focus();
        }
    }

    addButton.addEventListener('click', () => openModal(null));
    document.querySelectorAll('.aviso-editar').forEach((button) => {
        button.addEventListener('click', () => openModal(button));
    });

    document.querySelectorAll('[data-fechar-aviso-modal]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    description.addEventListener('input', updateCounter);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        saveButton.disabled = true;
        deleteButton.disabled = true;
        setFeedback('Salvando aviso...', true);
        try {
            const result = await post(form.action, new FormData(form));
            setFeedback(result.message, true);
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            setFeedback(error.message, false);
            saveButton.disabled = false;
            deleteButton.disabled = false;
        }
    });

    deleteButton.addEventListener('click', async () => {
        if (!window.confirm('Deseja excluir este aviso? Esta ação não pode ser desfeita.')) {
            return;
        }

        const data = new FormData();
        data.append('csrf', csrf);
        data.append('id', form.elements.namedItem('id').value);
        deleteButton.disabled = true;
        saveButton.disabled = true;
        setFeedback('Excluindo aviso...', true);
        try {
            const result = await post(baseUrl + '/avisos/excluirAviso', data);
            setFeedback(result.message, true);
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            setFeedback(error.message, false);
            deleteButton.disabled = false;
            saveButton.disabled = false;
        }
    });
})();
