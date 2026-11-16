<?php
class RespostaHistorico
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function listar($tipo, $registroId)
    {
        if ($tipo === 'demanda') {
            $sql = 'SELECT r.dres_mensagem AS mensagem, r.dres_criado_em AS criado_em,
                           u.user_nome AS autor
                    FROM demanda_resposta r
                    JOIN usuario u ON u.user_id = r.user_id
                    WHERE r.dema_id = :registro_id
                    ORDER BY r.dres_criado_em ASC, r.dres_id ASC';
        } elseif ($tipo === 'sugestao') {
            $sql = 'SELECT r.sres_mensagem AS mensagem, r.sres_criado_em AS criado_em,
                           u.user_nome AS autor
                    FROM sugestao_resposta r
                    JOIN usuario u ON u.user_id = r.user_id
                    WHERE r.suge_id = :registro_id
                    ORDER BY r.sres_criado_em ASC, r.sres_id ASC';
        } else {
            return [];
        }

        $this->db->query($sql);
        $this->db->bind(':registro_id', (int) $registroId);
        return $this->db->resultados();
    }

    public function criar($tipo, $registroId, $usuarioId, $mensagem)
    {
        if ($tipo === 'demanda') {
            $sql = 'INSERT INTO demanda_resposta (dema_id, user_id, dres_mensagem)
                    VALUES (:registro_id, :usuario_id, :mensagem)';
        } elseif ($tipo === 'sugestao') {
            $sql = 'INSERT INTO sugestao_resposta (suge_id, user_id, sres_mensagem)
                    VALUES (:registro_id, :usuario_id, :mensagem)';
        } else {
            return false;
        }

        $this->db->query($sql);
        $this->db->bind(':registro_id', (int) $registroId);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':mensagem', $mensagem);
        return $this->db->executa();
    }
}
