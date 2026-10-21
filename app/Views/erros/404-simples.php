<?php /* VIEW: erro 404 para visitantes (sem layout). */ ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - <?= Html::e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= URL ?>/css/variables.css">
    <link rel="stylesheet" href="<?= URL ?>/css/components/backend.css">
</head>
<body class="pagina-erro">
    <div class="caixa-simples">
        <h1>404</h1>
        <p>Página não encontrada.</p>
        <a class="botao-verde" href="<?= URL ?>/login">Ir para o login</a>
    </div>
</body>
</html>
