<?php
/*
 * VIEW: REDEFINIR SENHA
 * Página para criar uma nova senha depois do link recebido por e-mail.
 */
$erros = $dados['erros'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova senha - Demandfy</title>
    <link rel="stylesheet" href="<?= URL ?>/css/variables.css">
    <link rel="stylesheet" href="<?= URL ?>/css/login.css">
    <link rel="stylesheet" href="<?= URL ?>/css/components/backend.css">
</head>
<body>
    <div class="login-container">
        <div class="login-panel">
            <h1 class="login-title">Nova senha</h1>

            <?php if (!empty($erros['geral'])): ?>
                <div class="alerta alerta-erro" role="alert"><?= Html::e($erros['geral']) ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?= URL ?>/redefinir-senha?token=<?= urlencode($dados['token']) ?>" novalidate>
                <?= Csrf::campo() ?>

                <div class="form-group">
                    <label for="nova_senha" class="form-label">Nova senha</label>
                    <div class="senha-wrapper">
                        <input type="password" id="nova_senha" name="nova_senha" class="form-input senha-input" autocomplete="new-password" required>
                        <button type="button" class="senha-toggle" aria-label="Mostrar senha">Mostrar</button>
                    </div>
                    <?php if (!empty($erros['nova_senha'])): ?><span class="erro-campo"><?= Html::e($erros['nova_senha']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirma_senha" class="form-label">Confirmar senha</label>
                    <div class="senha-wrapper">
                        <input type="password" id="confirma_senha" name="confirma_senha" class="form-input senha-input" autocomplete="new-password" required>
                        <button type="button" class="senha-toggle" aria-label="Mostrar senha">Mostrar</button>
                    </div>
                    <?php if (!empty($erros['confirma_senha'])): ?><span class="erro-campo"><?= Html::e($erros['confirma_senha']) ?></span><?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">Salvar nova senha</button>
            </form>

            <div class="login-footer">
                <p class="footer-section"><a href="<?= URL ?>/login" class="link-blue">Voltar para o login</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.senha-toggle').forEach(function (botao) {
                botao.addEventListener('click', function () {
                    var campo = botao.parentElement.querySelector('.senha-input');
                    if (!campo) { return; }
                    var mostrar = campo.type === 'password';
                    campo.type = mostrar ? 'text' : 'password';
                    botao.textContent = mostrar ? 'Ocultar' : 'Mostrar';
                });
            });
        });
    </script>

    <div class="login-decoration">
        <img src="<?= URL ?>/img/Rectangle 20.png" alt="Decoração" class="decoration-img">
    </div>

    <div class="login-logo">
        <img src="<?= URL ?>/img/DemandfyBig.png" alt="Demandfy Logo" class="logo-img">
        <div class="logo-texts">
            <p class="logo-line1">Sistema de Representação Estudantil</p>
            <p class="logo-line2">Plataforma digital para comunicação, análise e gerenciamento das demandas estudantis</p>
        </div>
    </div>
</body>
</html>
