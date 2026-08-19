<?php
include '../app/configuracao.php';
include '../app/Libraries/Rota.php';
include '../app/Libraries/Controller.php';
include '../app/Libraries/Database.php';
$db = new Database;/*
$db->query("SELECT * FROM posts ORDER BY id DESC");
$db->resultado();
echo $db->resultado()->titulo;
echo "<br>";
echo $db->resultado()->texto;
echo "<br>";
echo $db->resultado()->usuario_id;

/*
$id = 11;
$db->query("DELETE FROM posts WHERE id = :id");
$db->bind(":id", $id);
$db->executa();
echo '<hr>Total Resultados: '.$db->totalResultados();
/*
date_default_timezone_set('America/Cuiaba');
$id = 10;
$usuario_id = 100;
$titulo  = 'Titulo Editado';
$texto = 'Texto editado';
$criadoEm = date('Y-m-d H:i:s');

$db->query("UPDATE posts SET usuario_id = :usuario_id, titulo = :titulo, texto=:texto, criado_em=:criadoEm WHERE id = :id");
$db->bind(':id', $id);
$db->bind(':usuario_id', $usuario_id);
$db->bind(':titulo',$titulo);
$db->bind(':texto',$texto);
$db->bind(':criadoEm',$criadoEm);
$db->executa();
echo '<hr>Total Resultados'.$db->totalResultados();

/*
//inserindo dados no banco
$usuario_id = 10;
$titulo = 'Matemática';
$texto = '1+1 = 10';

$db->query("INSERT INTO posts (usuario_id, titulo, texto) VALUES (:usuario_id, :titulo, :texto)");
$db->bind(":usuario_id",$usuario_id);
$db->bind(":titulo",$titulo);
$db->bind(":texto",$texto);

$db->executa();

echo '<hr>Total Resultados: '.$db->totalResultados();
echo '<hr>Ultimo ID inserido'.$db->ultimoIdInserido();
*/
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> </title>
    
    <link rel="stylesheet" href="public\css\estilo.css">
    <link rel="stylesheet" href="public\css\sidebar.css">
</head>
<body>
    <?php
    include '../app/views/sidebar.php';
    $rotas = new Rota();
   // $rotas->url();
    ?>
</body>
</html>