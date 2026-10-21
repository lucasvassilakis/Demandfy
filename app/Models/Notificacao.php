<?php
/*
 * MODEL NOTIFICACAO
 * ------------------------------------------------------------------
 * Notificações pessoais de cada usuário. Os controllers chamam criar()
 * quando algo acontece (cadastro, demanda enviada, sugestão enviada).
 */
class Notificacao
{
    // Imagem de cada tipo de notificação (arquivos já existentes em public/img).
    const ICONES = [
        'aviso'    => 'NotificacaoCommercial.png',
        'demanda'  => 'NotificacaoShowProperty.png',
        'evento'   => 'NotificacaoSchedule.png',
        'sugestao' => 'NotificacaoIdea.png',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function criar($usuarioId, $tipo, $titulo, $mensagem)
    {
        $this->db->query('INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem)
                          VALUES (:usuario_id, :tipo, :titulo, :mensagem)');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':tipo', $tipo);
        $this->db->bind(':titulo', $titulo);
        $this->db->bind(':mensagem', $mensagem);
        return $this->db->executa();
    }

    // Lista as notificações do usuário. $somenteNaoLidas = true filtra as não lidas.
    public function listar($usuarioId, $somenteNaoLidas = false)
    {
        $sql = 'SELECT * FROM notificacoes WHERE usuario_id = :usuario_id';
        if ($somenteNaoLidas) {
            $sql .= ' AND lida = 0';
        }
        $sql .= ' ORDER BY id DESC LIMIT 50';

        $this->db->query($sql);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->resultados();
    }

    public function contarNaoLidas($usuarioId)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM notificacoes
                          WHERE usuario_id = :usuario_id AND lida = 0');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $linha = $this->db->resultado();
        return $linha ? (int) $linha->total : 0;
    }

    public function marcarTodasComoLidas($usuarioId)
    {
        $this->db->query('UPDATE notificacoes SET lida = 1
                          WHERE usuario_id = :usuario_id AND lida = 0');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->executa();
    }
}
