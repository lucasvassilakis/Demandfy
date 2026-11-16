<?php
/*
 * VIEW: CADASTRO (página completa, sem sidebar)
 * Mudanças: formulário envia de verdade para /cadastro (antes um JavaScript apenas
 * redirecionava para o login). O campo de confirmação agora se chama
 * "confirma_senha" (padrão do framework2026). Inclui CSRF e erros por campo.
 */
$erros = $dados['erros'];
$v = $dados['valores'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Demandfy</title>
    <link rel="stylesheet" href="<?= URL ?>/css/variables.css">
    <link rel="stylesheet" href="<?= URL ?>/css/login.css">
    <link rel="stylesheet" href="<?= URL ?>/css/components/backend.css">
</head>
<body>
    <div class="login-container">
        <div class="login-panel">
            <h1 class="login-title">Cadastro</h1>

            <?php if (!empty($erros['geral'])): ?>
                <div class="alerta alerta-erro" role="alert"><?= Html::e($erros['geral']) ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?= URL ?>/cadastro" novalidate>
                <?= Csrf::campo() ?>

                <div class="form-group">
                    <label for="nome" class="form-label">Nome completo</label>
                    <input type="text" id="nome" name="nome" class="form-input" value="<?= Html::e($v['nome']) ?>" maxlength="120" required>
                    <?php if (!empty($erros['nome'])): ?><span class="erro-campo"><?= Html::e($erros['nome']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= Html::e($v['email']) ?>" maxlength="150" placeholder="nome@gmail.com" inputmode="email" required>
                    <?php if (!empty($erros['email'])): ?><span class="erro-campo"><?= Html::e($erros['email']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-input telefone-input" placeholder="(69) 9 9999-9999" value="<?= Html::e($v['telefone']) ?>" maxlength="20" inputmode="numeric" required>
                    <?php if (!empty($erros['telefone'])): ?><span class="erro-campo"><?= Html::e($erros['telefone']) ?></span><?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="senha-wrapper">
                            <input type="password" id="senha" name="senha" class="form-input senha-input" autocomplete="new-password" required>
                            <button type="button" class="senha-toggle" aria-label="Mostrar senha">Mostrar</button>
                        </div>
                        <?php if (!empty($erros['senha'])): ?><span class="erro-campo"><?= Html::e($erros['senha']) ?></span><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirma_senha" class="form-label">Confirmar senha</label>
                        <div class="senha-wrapper">
                            <input type="password" id="confirma_senha" name="confirma_senha" class="form-input senha-input" autocomplete="new-password" required>
                            <button type="button" class="senha-toggle" aria-label="Mostrar senha">Mostrar</button>
                        </div>
                        <?php if (!empty($erros['confirma_senha'])): ?><span class="erro-campo"><?= Html::e($erros['confirma_senha']) ?></span><?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Enviar</button>
            </form>

            <div class="login-footer">
                <p class="footer-section">Já tem login? <a href="<?= URL ?>/login" class="link-blue">Log In</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formatarTelefone = function (valor) {
                const digits = valor.replace(/\D/g, '').slice(0, 11);
                if (!digits) return '';

                if (digits.length <= 2) {
                    return `(${digits}`;
                }

                if (digits.length <= 7) {
                    return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
                }

                if (digits.length <= 10) {
                    return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
                }

                const celular = digits.slice(0, 11);
                return `(${celular.slice(0, 2)}) ${celular[2]} ${celular.slice(3, 7)}-${celular.slice(7, 11)}`;
            };

            document.querySelectorAll('.telefone-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    const valor = input.value;
                    const formatado = formatarTelefone(valor);
                    if (formatado !== valor) {
                        input.value = formatado;
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var formatarTelefone = function (valor) {
                var digits = (valor || '').replace(/\D/g, '').slice(0, 11);
                if (!digits) { return ''; }
                if (digits.length <= 2) { return '(' + digits; }
                if (digits.length <= 7) { return '(' + digits.slice(0, 2) + ') ' + digits.slice(2); }
                if (digits.length <= 10) { return '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 6) + '-' + digits.slice(6); }
                return '(' + digits.slice(0, 2) + ') ' + digits[2] + ' ' + digits.slice(3, 7) + '-' + digits.slice(7, 11);
            };

            var telefone = document.getElementById('telefone');
            if (telefone) {
                telefone.addEventListener('input', function () {
                    telefone.value = formatarTelefone(telefone.value);
                });
            }

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
