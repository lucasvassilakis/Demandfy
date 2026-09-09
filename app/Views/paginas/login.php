<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Demandfy</title>
    <link rel="stylesheet" href="<?php echo URL; ?>/css/variables.css">
    <link rel="stylesheet" href="<?php echo URL; ?>/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-panel">
            <h1 class="login-title">Log In</h1>
            
            <form class="login-form" method="POST" action="#">
                <div class="form-group">
                    <label for="email" class="form-label">E-mail/celular</label>
                    <input type="text" id="email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-input" required>
                </div>

                <button type="submit" class="btn-submit">Entrar</button>
            </form>

            <div class="login-footer">
                <p class="footer-section">Esqueceu a senha? <a href="#" class="link-blue">Esqueci minha senha</a></p>
                <p class="footer-section">Ainda não é cadastrado? <a href="<?php echo URL; ?>/cadastro" class="link-blue">Cadastre-se</a></p>
            </div>
        </div>
    </div>

    <!-- Imagem decorativa na direita -->
    <div class="login-decoration">
        <img src="<?php echo URL; ?>/img/Rectangle 20.png" alt="Decoração" class="decoration-img">
    </div>

    <!-- Logo do Demandfy -->
    <div class="login-logo">
        <img src="<?php echo URL; ?>/img/DemandfyBig.png" alt="Demandfy Logo" class="logo-img">
        <div class="logo-texts">
            <p class="logo-line1">Sistema de Representação Estudantil</p>
            <p class="logo-line2">Plataforma digital para comunicação, análise e gerenciamento das demandas estudantis</p>
        </div>
    </div>
    
    <script>
        // Redireciona para a página inicial ao submeter o formulário, independente dos dados
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('.login-form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    window.location.href = '<?php echo URL; ?>';
                });
            }
        });
    </script>

</body>
</html>
