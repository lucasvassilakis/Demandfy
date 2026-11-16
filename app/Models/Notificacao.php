<?php
/*
 * MODEL NOTIFICACAO
 * ------------------------------------------------------------------
 * Notificações pessoais de cada usuário. Os controllers chamam criar()
 * quando algo acontece (cadastro, demanda enviada, sugestão enviada).
 */
class Notificacao
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function criar($usuarioId, $titulo, $mensagem)
    {
        $this->db->query('SELECT user_email, user_notificacao_site, user_notificacao_email
                          FROM usuario WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $usuario = $this->db->resultado();
        if (!$usuario) {
            return false;
        }

        $sucesso = true;
        if ((int) $usuario->user_notificacao_site) {
            $this->db->query('INSERT INTO notificacao (user_id, noti_titulo, noti_mensagem)
                              VALUES (:usuario_id, :titulo, :mensagem)');
            $this->db->bind(':usuario_id', (int) $usuarioId);
            $this->db->bind(':titulo', $titulo);
            $this->db->bind(':mensagem', $mensagem);
            $sucesso = $this->db->executa();
        }

        if ((int) $usuario->user_notificacao_email) {
            require_once APP . '/Libraries/EmailService.php';
            $emailService = new \App\Libraries\EmailService();
            if (!$emailService->enviarNotificacao($usuario->user_email, $titulo, $mensagem)) {
                $sucesso = false;
            }
        }

        return $sucesso;
    }

    public function notificarModeradores($titulo, $mensagem, $excluirUsuarioIds = [])
    {
        $excluirUsuarioIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $excluirUsuarioIds),
            static function ($usuarioId) {
                return $usuarioId > 0;
            }
        )));
        $sql = 'SELECT DISTINCT u.user_id
                FROM usuario u
                JOIN usuario_permissao p ON p.user_id = u.user_id
                WHERE p.permissao IN (\'admin\', :permissao_moderador)';
        if ($excluirUsuarioIds) {
            $placeholders = [];
            foreach ($excluirUsuarioIds as $indice => $usuarioId) {
                $placeholders[] = ':excluir_usuario_' . $indice;
            }
            $sql .= ' AND u.user_id NOT IN (' . implode(', ', $placeholders) . ')';
        }

        $this->db->query($sql);
        $this->db->bind(':permissao_moderador', Permissao::GERENCIAR_PERMISSOES);
        foreach ($excluirUsuarioIds as $indice => $usuarioId) {
            $this->db->bind(':excluir_usuario_' . $indice, $usuarioId);
        }

        return $this->notificarDestinatarios($this->db->resultados(), $titulo, $mensagem);
    }

    public function notificarUsuarios($titulo, $mensagem, $excluirUsuarioId = 0)
    {
        $this->db->query('SELECT user_id FROM usuario WHERE user_id <> :excluir_usuario_id');
        $this->db->bind(':excluir_usuario_id', (int) $excluirUsuarioId);

        return $this->notificarDestinatarios($this->db->resultados(), $titulo, $mensagem);
    }

    private function notificarDestinatarios($destinatarios, $titulo, $mensagem)
    {
        $sucesso = true;
        foreach ($destinatarios as $destinatario) {
            if (!$this->criar((int) $destinatario->user_id, $titulo, $mensagem)) {
                error_log('Demandfy: falha ao enviar notificação para o usuário ' . (int) $destinatario->user_id . '.');
                $sucesso = false;
            }
        }

        return $sucesso;
    }

    // Lista as notificações do usuário. $somenteNaoLidas = true filtra as não lidas.
    public function listar($usuarioId, $somenteNaoLidas = false)
    {
        $sql = 'SELECT noti_id AS id, user_id AS usuario_id, noti_titulo AS titulo,
                       noti_mensagem AS mensagem, (noti_status = \'lida\') AS lida
                FROM notificacao WHERE user_id = :usuario_id';
        if ($somenteNaoLidas) {
            $sql .= ' AND noti_status = \'nao_lida\'';
        }
        $sql .= ' ORDER BY noti_id DESC LIMIT 50';

        $this->db->query($sql);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->resultados();
    }

    public function contarNaoLidas($usuarioId)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM notificacao
                          WHERE user_id = :usuario_id AND noti_status = \'nao_lida\'');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $linha = $this->db->resultado();
        return $linha ? (int) $linha->total : 0;
    }

    public function marcarTodasComoLidas($usuarioId)
    {
        $this->db->query('UPDATE notificacao SET noti_status = \'lida\'
                          WHERE user_id = :usuario_id AND noti_status = \'nao_lida\'');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->executa();
    }
}
