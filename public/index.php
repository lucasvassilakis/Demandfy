<?php
include '../app/configuracao.php';
include '../app/Autoload.php';
$db = new Database;

// Verificar se é uma página sem sidebar
$urlAtual = trim((string) filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL), '/');
$paginasSemSidebar = ['login', 'cadastro'];
$paginaSemSidebar = in_array($urlAtual, $paginasSemSidebar);

// Se for página sem sidebar, renderizar completo
if ($paginaSemSidebar) {
    $rotas = new Rota();
    return;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> </title>
    
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
    <?php
    include '../app/views/sidebar.php';
    $rotas = new Rota();
   // $rotas->url();
    ?>
</body>
</html>