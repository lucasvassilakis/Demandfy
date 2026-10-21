<?php
/*
 * AUTOLOAD DE CLASSES
 * ------------------------------------------------------------------
 * Quando o PHP encontra "new Database", "Sessao::..." etc. e a classe ainda não
 * foi carregada, esta função procura o arquivo <NomeDaClasse>.php nas pastas
 * abaixo e faz o require automaticamente.
 *
 * MUDANÇA: foi adicionada a pasta 'Models' (o framework2026 carregava os models
 * manualmente). Assim as views também podem usar constantes dos models,
 * ex.: Demanda::STATUS.
 */
spl_autoload_register(function ($classe) {
    $diretorios = ['Libraries', 'Helpers', 'Models'];

    foreach ($diretorios as $diretorio) {
        $arquivo = __DIR__ . DIRECTORY_SEPARATOR . $diretorio . DIRECTORY_SEPARATOR . $classe . '.php';

        if (file_exists($arquivo)) {
            require_once $arquivo;
            return; // achou, não precisa procurar nas outras pastas
        }
    }
});
