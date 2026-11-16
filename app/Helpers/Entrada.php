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
    public static function post($campo)
    {
        if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
            return '';
        }

        // Mantém a entrada consistente com a política do sistema: texto simples,
        // sem arrays e sem espaços em excesso ao redor do valor.
        return trim((string) $_POST[$campo]);
    }

    // Wrapper específico para senhas: preserva o valor original e não aplica trim,
    // porque espaços no início/fim podem ser parte da credencial.
    public static function postSenha($campo)
    {
        if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
            return '';
        }

        return $_POST[$campo];
    }

    // Leitura segura de inteiros vindos do formulário. Se o valor não for numérico,
    // devolve o padrão informado em vez de deixar o controller lidar com cast manual.
    public static function postInt($campo, $padrao = 0)
    {
        $valor = self::post($campo);
        return ctype_digit($valor) ? (int) $valor : $padrao;
    }
}
