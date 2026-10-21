<?php
/*
 * HELPER CSRF (proteção contra formulários forjados)
 * ------------------------------------------------------------------
 * NOVO. Cada sessão tem um token secreto. Todo <form method="POST"> inclui esse
 * token em um campo escondido (<?= Csrf::campo() ?>) e o controller confere
 * com Csrf::validar(). Um site malicioso não conhece o token, então não
 * consegue enviar formulários em nome do usuário.
 */
class Csrf
{
    public static function token()
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    // HTML do campo escondido, para colocar dentro dos formulários.
    public static function campo()
    {
        return '<input type="hidden" name="csrf" value="' . self::token() . '">';
    }

    // Confere o token recebido por POST.
    public static function validar()
    {
        $enviado = isset($_POST['csrf']) ? $_POST['csrf'] : '';

        return is_string($enviado)
            && !empty($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $enviado);
    }
}
