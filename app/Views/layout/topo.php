<?php
/*
 * LAYOUT - TOPO (usado por Controller::view)
 * Abre o HTML, carrega os CSS, inclui a sidebar e mostra a mensagem "flash" (sucesso/erro).
 * Antes isso ficava dentro de public/index.php.
 * Os caminhos de CSS e imagens agora usam <?= URL ?> (absolutos), senão quebram em
 * URLs com mais de um nível, como /demandas/ver/5.
 */
$tituloPagina = !empty($dados['titulo']) ? $dados['titulo'] . ' - ' . APP_NAME : APP_NAME;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::e($tituloPagina) ?></title>

    <link rel="stylesheet" href="<?= URL ?>/css/estilo.css">
    <link rel="stylesheet" href="<?= URL ?>/css/sidebar.css">
</head>
<body>
    <?php require APP . '/Views/sidebar.php'; ?>
    <?php Sessao::mensagem('geral'); // mensagem flutuante de sucesso/erro (aparece uma vez) ?>
