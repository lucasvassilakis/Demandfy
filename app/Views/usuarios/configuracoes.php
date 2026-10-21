<?php
/*
 * VIEW: CONFIGURAÇÕES (antes era um arquivo vazio)
 * Contém o formulário de troca de senha. Usa a classe .card (já estilizada em forms.css).
 */
$erros = $dados['erros'];
?>
<main class="inicio">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <section class="conteudo-pagina">
        <form class="card suggestion form-estreito" method="POST" action="<?= URL ?>/configuracoes" novalidate>
            <?= Csrf::campo() ?>

            <div class="card-header">
                <img class="card-icon" src="<?= URL ?>/img/setting.png" alt="">
                <div>
                    <h2>Alterar senha</h2>
                    <p>Use no mínimo 6 caracteres.</p>
                </div>
            </div>

            <label for="senha_atual">Senha atual</label>
            <input type="password" id="senha_atual" name="senha_atual" autocomplete="current-password" required>
            <?php if (!empty($erros['senha_atual'])): ?><span class="erro-campo"><?= Html::e($erros['senha_atual']) ?></span><?php endif; ?>

            <label for="nova_senha">Nova senha</label>
            <input type="password" id="nova_senha" name="nova_senha" autocomplete="new-password" required>
            <?php if (!empty($erros['nova_senha'])): ?><span class="erro-campo"><?= Html::e($erros['nova_senha']) ?></span><?php endif; ?>

            <label for="confirma_senha">Confirmar nova senha</label>
            <input type="password" id="confirma_senha" name="confirma_senha" autocomplete="new-password" required>
            <?php if (!empty($erros['confirma_senha'])): ?><span class="erro-campo"><?= Html::e($erros['confirma_senha']) ?></span><?php endif; ?>

            <button type="submit" class="submit-btn btn-suggestion">Salvar nova senha</button>
        </form>
    </section>
</main>
