<?php
/*
 * HELPER URL
 * ------------------------------------------------------------------
 * MUDANÇAS: usa "/" (antes DIRECTORY_SEPARATOR, que no Windows é "\" e gera
 * URL errada) e dá exit depois do redirecionamento. Sem o exit o PHP continuava
 * executando o resto do controller mesmo depois de redirecionar.
 */
class Url
{
    // Redireciona para uma rota do sistema. Ex.: Url::redirecionar('demandas');
    public static function redirecionar($url)
    {
        header('Location: ' . URL . '/' . ltrim($url, '/'));
        exit;
    }
}
