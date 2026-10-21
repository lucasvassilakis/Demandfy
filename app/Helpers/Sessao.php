<?php
/*
 * HELPER SESSAO
 * ------------------------------------------------------------------
 * Cuida de tudo que fica guardado na sessão do usuário:
 *   - mensagens "flash" (aparecem uma única vez, ex.: "Cadastro realizado!")
 *   - dados do usuário logado
 *   - dados de formulário com erro (para preencher de novo após redirecionar)
 *
 * MUDANÇAS:
 *  - mensagem() agora usa o tipo 'sucesso' ou 'erro' (classes CSS do Demandfy)
 *    em vez de classes do Bootstrap, escapa o texto (htmlspecialchars) e
 *    sobrescreve mensagens antigas.
 *  - Novos: logar(), sair(), usuarioId(), usuarioNome(), flashDados().
 */
class Sessao
{
    /*
     * Dois usos:
     *   Sessao::mensagem('geral', 'Texto', 'sucesso');  // GUARDA a mensagem
     *   Sessao::mensagem('geral');                      // EXIBE e apaga (aparece só uma vez)
     */
    public static function mensagem($nome, $texto = null, $tipo = 'sucesso')
    {
        if (empty($nome)) {
            return;
        }

        if (!empty($texto)) {
            // Guarda a mensagem.
            $_SESSION[$nome] = $texto;
            $_SESSION[$nome . '_tipo'] = ($tipo === 'erro') ? 'erro' : 'sucesso';
        } elseif (!empty($_SESSION[$nome])) {
            // Exibe e apaga.
            $classe = (isset($_SESSION[$nome . '_tipo']) && $_SESSION[$nome . '_tipo'] === 'erro')
                ? 'alerta alerta-erro'
                : 'alerta alerta-sucesso';
            echo '<div class="' . $classe . '" role="alert">' . htmlspecialchars($_SESSION[$nome]) . '</div>';
            unset($_SESSION[$nome], $_SESSION[$nome . '_tipo']);
        }
    }

    /*
     * Guarda/recupera dados de formulário (erros + valores digitados) entre dois pedidos.
     *   Sessao::flashDados('form_demanda', $dados);  // guarda
     *   $x = Sessao::flashDados('form_demanda');     // lê e apaga (ou null)
     */
    public static function flashDados($chave, $dados = null)
    {
        if ($dados !== null) {
            $_SESSION['flash_' . $chave] = $dados;
            return null;
        }

        $valor = isset($_SESSION['flash_' . $chave]) ? $_SESSION['flash_' . $chave] : null;
        unset($_SESSION['flash_' . $chave]);
        return $valor;
    }

    // O usuário está logado?
    public static function estaLogado()
    {
        return isset($_SESSION['usuario_id']);
    }

    // Registra o login na sessão.
    public static function logar($usuario)
    {
        // Gera um novo ID de sessão ao logar (evita "session fixation").
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario->id;
        $_SESSION['usuario_nome'] = $usuario->nome;
        $_SESSION['usuario_email'] = $usuario->email;
    }

    // Encerra o login. Limpa a sessão inteira e gera um ID novo.
    public static function sair()
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function usuarioId()
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : 0;
    }

    public static function usuarioNome()
    {
        return isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : '';
    }
}
