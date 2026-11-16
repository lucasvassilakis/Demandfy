<?php
$erros = $dados['erros'];
$preferencias = $dados['preferencias'];
?>
<main class="configuracoes-pagina">
    <h1 class="configuracoes-titulo">Configuração</h1>

    <form class="configuracoes-painel" method="POST" action="<?= URL ?>/configuracoes" novalidate>
        <?= Csrf::campo() ?>

        <div class="configuracoes-coluna">
            <section class="configuracoes-cartao preferencias-cartao">
                <header class="configuracoes-cartao-titulo">
                    <img src="<?= URL ?>/img/Doorbell.png" alt="">
                    <h2>Preferências</h2>
                </header>

                <div class="preferencias-lista">
                    <label class="preferencia-alternancia">
                        <span>Receber notificações no site</span>
                        <input type="checkbox" name="notificacao_site" value="1" <?= $preferencias['notificacao_site'] ? 'checked' : '' ?>>
                        <span class="alternancia-visual" aria-hidden="true"></span>
                    </label>
                </div>
            </section>

            <section class="configuracoes-cartao conta-excluir-cartao" aria-labelledby="conta-excluir-titulo">
                <header class="configuracoes-cartao-titulo conta-excluir-titulo">
                    <h2 id="conta-excluir-titulo">Excluir conta</h2>
                </header>
                <p>Esta ação é permanente e encerra sua sessão. A conta não pode ser excluída enquanto houver demandas, sugestões, avisos ou outros registros vinculados.</p>
                <button class="conta-excluir-botao"
                        type="submit"
                        form="excluir-conta-form">
                    Excluir minha conta
                </button>
            </section>

        </div>

        <section class="configuracoes-cartao senha-cartao">
            <?php if (!empty($erros['geral'])): ?>
                <p class="configuracoes-erro-geral" role="alert"><?= Html::e($erros['geral']) ?></p>
            <?php endif; ?>

            <header class="configuracoes-cartao-titulo senha-titulo">
                <img src="<?= URL ?>/img/Padlock.png" alt="">
                <h2>Alterar senha</h2>
            </header>

            <div class="senha-campo">
                <label for="senha_atual">Senha atual</label>
                <div class="senha-controle">
                    <input type="password" id="senha_atual" name="senha_atual" autocomplete="current-password" placeholder="Digite sua senha atual">
                    <button class="senha-visibilidade" type="button" data-alvo="senha_atual" aria-label="Mostrar senha atual" aria-pressed="false">
                        <img src="<?= URL ?>/img/Hide.png" alt="">
                    </button>
                </div>
                <?php if (!empty($erros['senha_atual'])): ?><span class="configuracoes-erro"><?= Html::e($erros['senha_atual']) ?></span><?php endif; ?>
            </div>

            <div class="senha-campo">
                <label for="nova_senha">Nova senha</label>
                <div class="senha-controle">
                    <input type="password" id="nova_senha" name="nova_senha" autocomplete="new-password" placeholder="Digite a nova senha">
                    <button class="senha-visibilidade" type="button" data-alvo="nova_senha" aria-label="Mostrar nova senha" aria-pressed="false">
                        <img src="<?= URL ?>/img/Hide.png" alt="">
                    </button>
                </div>
                <?php if (!empty($erros['nova_senha'])): ?><span class="configuracoes-erro"><?= Html::e($erros['nova_senha']) ?></span><?php endif; ?>
            </div>

            <div class="senha-campo">
                <label for="confirma_senha">Confirmar nova senha</label>
                <div class="senha-controle">
                    <input type="password" id="confirma_senha" name="confirma_senha" autocomplete="new-password" placeholder="Confirme a nova senha">
                    <button class="senha-visibilidade" type="button" data-alvo="confirma_senha" aria-label="Mostrar confirmação da senha" aria-pressed="false">
                        <img src="<?= URL ?>/img/Hide.png" alt="">
                    </button>
                </div>
                <?php if (!empty($erros['confirma_senha'])): ?><span class="configuracoes-erro"><?= Html::e($erros['confirma_senha']) ?></span><?php endif; ?>
            </div>

            <button type="submit" class="configuracoes-salvar">Salvar alterações</button>
        </section>
    </form>

    <form id="excluir-conta-form"
          hidden
          method="POST"
          action="<?= URL ?>/usuarios/excluirConta"
          data-confirmar-exclusao>
        <?= Csrf::campo() ?>
    </form>
</main>

<script>
document.querySelectorAll('.senha-visibilidade').forEach(function (botao) {
    botao.addEventListener('click', function () {
        var campo = document.getElementById(botao.dataset.alvo);
        var mostrar = campo.type === 'password';
        campo.type = mostrar ? 'text' : 'password';
        botao.setAttribute('aria-label', (mostrar ? 'Ocultar ' : 'Mostrar ') + campo.labels[0].textContent.toLowerCase());
        botao.setAttribute('aria-pressed', String(mostrar));
    });
});

document.querySelectorAll('[data-confirmar-exclusao]').forEach(function (formulario) {
    formulario.addEventListener('submit', function (evento) {
        if (!window.confirm('Tem certeza de que deseja excluir sua conta? Esta ação não pode ser desfeita.')) {
            evento.preventDefault();
        }
    });
});
</script>
