<?php
/*
 * HELPER HTML
 * ------------------------------------------------------------------
 * NOVO. Html::e($texto) escapa caracteres especiais antes de imprimir na tela
 * (protege contra XSS). Regra do sistema: os dados são guardados no banco como o
 * usuário digitou e ESCAPADOS NA HORA DE EXIBIR, nunca antes de salvar.
 * (O framework2026 usava FILTER_SANITIZE_SPECIAL_CHARS ao receber, o que
 * deformava senhas e textos com & < > " ').
 */
class Html
{
    public static function e($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}
