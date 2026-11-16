<?php
/*
 * VIEW: ESQUECI MINHA SENHA
 * Página dedicada à recuperação de senha, com visual parecido ao login.
 */
$erros = $dados['erros'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha - Demandfy</title>
    <link rel="stylesheet" href="<?= URL ?>/css/variables.css">
    <link rel="stylesheet" href="<?= URL ?>/css/login.css">
    <link rel="stylesheet" href="<?= URL ?>/css/components/backend.css">
</head>
<body>
    <div class="login-container">
        <div class="login-panel">
            <h1 class="login-title">Recuperar senha</h1>

            <?php Sessao::mensagem('geral'); ?>
            <?php if (!empty($erros['geral'])): ?>
                <div class="alerta alerta-erro" role="alert"><?= Html::e($erros['geral']) ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?= URL ?>/esqueci-senha" novalidate>
                <?= Csrf::campo() ?>

                <div class="form-group">
                    <label for="login" class="form-label">E-mail ou celular</label>
                    <input type="text" id="login" name="login" class="form-input"
                           value="<?= Html::e($dados['valores']['login']) ?>"
                           placeholder="seu@gmail.com ou (69) 9 9999-9999"
                           autocomplete="username"
                           required>
                    <?php if (!empty($erros['login'])): ?><span class="erro-campo"><?= Html::e($erros['login']) ?></span><?php endif; ?>
                </div>

            </form>

            <div class="login-footer">
                <p class="footer-section"><a href="<?= URL ?>/login" class="link-blue">Voltar para o login</a></p>
            </div>
        </div>
    </div>

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
