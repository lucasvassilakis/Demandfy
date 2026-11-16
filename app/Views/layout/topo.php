<?php
/*
 * LAYOUT - TOPO (usado por Controller::view)
 * Abre o HTML, carrega os CSS, inclui a sidebar e mostra a mensagem "flash" (sucesso/erro).
 * Antes isso ficava dentro de public/index.php.
 * Os caminhos de CSS e imagens agora usam <?= URL ?> (absolutos), senão quebram em
 * URLs com mais de um nível, como /demandas/ver/5.
 */
$tituloPagina = !empty($dados['titulo']) ? $dados['titulo'] . ' - ' . APP_NAME : APP_NAME;
$estiloCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'estilo.css';
$responsiveCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'responsive.css';
$sidebarCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'sidebar.css';
$paginaAtual = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
$paginaAtual = $paginaAtual ? explode('/', trim($paginaAtual, '/'))[0] : '';
$classesBody = [];
if ($paginaAtual === 'historico') {
    $classesBody[] = 'pagina-historico';
}
if (Sessao::tema() === 'escuro') {
    $classesBody[] = 'tema-escuro';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::e($tituloPagina) ?></title>

    <link rel="stylesheet" href="<?= URL ?>/css/estilo.css?v=<?= (int) filemtime($estiloCss) ?>">
    <link rel="stylesheet" href="<?= URL ?>/css/responsive.css?v=<?= (int) filemtime($responsiveCss) ?>">
    <link rel="stylesheet" href="<?= URL ?>/css/sidebar.css?v=<?= (int) filemtime($sidebarCss) ?>">
    <?php if ($paginaAtual === 'historico'): ?>
        <?php $historicoCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'history.css'; ?>
        <link rel="stylesheet" href="<?= URL ?>/css/components/history.css?v=<?= (int) filemtime($historicoCss) ?>">
    <?php endif; ?>
    <?php if ($paginaAtual === 'calendario'): ?>
        <?php $calendarioCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'calendar.css'; ?>
        <link rel="stylesheet" href="<?= URL ?>/css/components/calendar.css?v=<?= (int) filemtime($calendarioCss) ?>">
    <?php endif; ?>
    <?php if ($paginaAtual === 'avisos'): ?>
        <?php $avisosCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'notifications.css'; ?>
        <link rel="stylesheet" href="<?= URL ?>/css/components/notifications.css?v=<?= (int) filemtime($avisosCss) ?>">
    <?php endif; ?>
    <?php if ($paginaAtual === 'notificacoes'): ?>
        <?php $notificacoesCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'notifications.css'; ?>
        <link rel="stylesheet" href="<?= URL ?>/css/components/notifications.css?v=<?= (int) filemtime($notificacoesCss) ?>">
    <?php endif; ?>
    <?php if ($paginaAtual === 'sugestoes'): ?>
        <?php $sugestoesCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'sugestoes.css'; ?>
        <link rel="stylesheet" href="<?= URL ?>/css/sugestoes.css?v=<?= (int) filemtime($sugestoesCss) ?>">
    <?php endif; ?>
    <?php if ($paginaAtual === 'configuracoes'): ?>
        <?php $configuracoesCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'settings.css'; ?>
        <link rel="stylesheet" href="<?= URL ?>/css/components/settings.css?v=<?= (int) filemtime($configuracoesCss) ?>">
    <?php endif; ?>
    <?php if ($paginaAtual === 'usuarios'): ?>
        <?php $usuariosCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'users.css'; ?>
        <link rel="stylesheet" href="<?= URL ?>/css/components/users.css?v=<?= (int) filemtime($usuariosCss) ?>">
    <?php endif; ?>
    <?php $temaCss = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'theme.css'; ?>
    <link rel="stylesheet" href="<?= URL ?>/css/components/theme.css?v=<?= (int) filemtime($temaCss) ?>">
</head>
<body<?= $classesBody ? ' class="' . implode(' ', $classesBody) . '"' : '' ?>>
    <?php require APP . '/Views/sidebar.php'; ?>
    <?php Sessao::mensagem('geral'); // mensagem flutuante de sucesso/erro (aparece uma vez) ?>
