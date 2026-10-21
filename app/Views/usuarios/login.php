<?php
/*
 * VIEW: LOGIN (página completa, sem sidebar)
 * Mudanças: o formulário agora ENVIA de verdade para /login (antes um JavaScript
 * redirecionava para o início sem checar nada); inclui o token CSRF, mostra
 * erros por campo e preenche o campo de login de novo quando há erro.
 */
$erros = $dados['erros'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Demandfy</title>
    <link rel="stylesheet" href="<?= URL ?>/css/variables.css">
    <link rel="stylesheet" href="<?= URL ?>/css/login.css">
    <link rel="stylesheet" href="<?= URL ?>/css/components/backend.css">
</head>
<body>
    <div class="login-container">
        <div class="login-panel">
            <h1 class="login-title">Log In</h1>

            <?php Sessao::mensagem('geral'); // mensagem de sucesso/erro vinda de outra página ?>
            <?php if (!empty($erros['geral'])): ?>
                <div class="alerta alerta-erro" role="alert"><?= Html::e($erros['geral']) ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?= URL ?>/login" novalidate>
                <?= Csrf::campo() ?>

                <div class="form-group">
                    <label for="login" class="form-label">E-mail/celular</label>
                    <input type="text" id="login" name="login" class="form-input"
                           value="<?= Html::e($dados['valores']['login']) ?>" autocomplete="username" required>
                    <?php if (!empty($erros['login'])): ?><span class="erro-campo"><?= Html::e($erros['login']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-input" autocomplete="current-password" required>
                    <?php if (!empty($erros['senha'])): ?><span class="erro-campo"><?= Html::e($erros['senha']) ?></span><?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">Entrar</button>
            </form>

            <div class="login-footer">
                <p class="footer-section">Esqueceu a senha? <a href="#" class="link-blue">Esqueci minha senha</a></p>
                <p class="footer-section">Ainda não é cadastrado? <a href="<?= URL ?>/cadastro" class="link-blue">Cadastre-se</a></p>
            </div>
        </div>
    </div>

    <!-- Imagem decorativa na direita -->
    <div class="login-decoration">
        <img src="<?= URL ?>/img/Rectangle 20.png" alt="Decoração" class="decoration-img">
    </div>

    <!-- Logo do Demandfy -->
    <div class="login-logo">
        <img src="<?= URL ?>/img/DemandfyBig.png" alt="Demandfy Logo" class="logo-img">
        <div class="logo-texts">
            <p class="logo-line1">Sistema de Representação Estudantil</p>
            <p class="logo-line2">Plataforma digital para comunicação, análise e gerenciamento das demandas estudantis</p>
        </div>
    </div>
</body>
</html>
