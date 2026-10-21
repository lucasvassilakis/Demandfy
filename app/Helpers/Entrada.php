<?php
/*
 * HELPER ENTRADA (leitura dos formulários)
 * ------------------------------------------------------------------
 * NOVO. Substitui o filter_input_array(INPUT_POST, ...) do framework2026.
 */
class Entrada
{
    // A requisição atual é um envio de formulário (POST)?
    public static function ehPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // Lê um campo do POST já sem espaços nas pontas. Se não vier, devolve ''.
    // (Para senhas use $_POST direto, pois espaços podem fazer parte da senha.)
    public static function post($campo)
    {
        if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
            return '';
        }
        return trim($_POST[$campo]);
    }
}
