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
        unset($_SESSION['usuario_administrador']);
        $_SESSION['usuario_id'] = (int) $usuario->id;
        $_SESSION['usuario_nome'] = $usuario->nome;
        $_SESSION['usuario_email'] = $usuario->email;
        $permissoes = new Permissao();
        if ($permissoes->usuarioTem((int) $usuario->id, 'admin')) {
            $_SESSION['usuario_administrador'] = true;
        } else {
            unset($_SESSION['usuario_administrador']);
        }
        self::definirTema(isset($usuario->tema) ? $usuario->tema : 'claro');
    }

    public static function ehAdministrador()
    {
        $usuarioId = self::usuarioId();
        if ($usuarioId <= 0) {
            return false;
        }

        static $usuarioVerificado = 0;
        static $administradorVerificado = false;
        if ($usuarioVerificado !== $usuarioId) {
            $permissoes = new Permissao();
            $administradorVerificado = $permissoes->usuarioTem($usuarioId, 'admin');
            $usuarioVerificado = $usuarioId;
            if ($administradorVerificado) {
                $_SESSION['usuario_administrador'] = true;
            } else {
                unset($_SESSION['usuario_administrador']);
            }
        }
        return $administradorVerificado;
    }

    public static function definirAdministrador($ehAdministrador)
    {
        if ($ehAdministrador) {
            $_SESSION['usuario_administrador'] = true;
        } else {
            unset($_SESSION['usuario_administrador']);
        }
    }

    public static function ehModerador()
    {
        $usuarioId = self::usuarioId();
        if ($usuarioId <= 0) {
            return false;
        }

        $permissoes = new Permissao();
        return $permissoes->usuarioTem($usuarioId, Permissao::GERENCIAR_PERMISSOES);
    }

    public static function temPermissao($permissao)
    {
        if (self::ehAdministrador()) {
            return true;
        }

        $usuarioId = self::usuarioId();
        if ($usuarioId <= 0 || !is_string($permissao)) {
            return false;
        }

        $modelo = new Permissao();
        return $modelo->usuarioTem($usuarioId, $permissao);
    }

    // Encerra o login. Limpa a sessão inteira, remove o cookie e cria uma sessão
    // nova para evitar reuso indevido de dados do usuário anterior.
    public static function sair()
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_unset();
            session_destroy();
        }

        session_start();
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

    public static function definirTema($tema)
    {
        if (in_array($tema, ['claro', 'escuro'], true)) {
            $_SESSION['usuario_tema'] = $tema;
        }
    }

    public static function tema()
    {
        return isset($_SESSION['usuario_tema']) && $_SESSION['usuario_tema'] === 'escuro'
            ? 'escuro'
            : 'claro';
    }
}
