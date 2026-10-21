<?php
/*
 * MODEL DEMANDA
 * ------------------------------------------------------------------
 * Equivale ao Model "Post" do framework2026 (armazenar / ler / excluir),
 * adaptado para as demandas do Demandfy.
 */
class Demanda
{
    // Valores aceitos => texto exibido. Usado no <select> e na validação.
    const CATEGORIAS = [
        'infraestrutura' => 'Infraestrutura',
        'limpeza'        => 'Limpeza',
        'ti'             => 'TI / Sistemas',
        'seguranca'      => 'Segurança',
        'outros'         => 'Outros',
    ];

    // status no banco => texto exibido
    const STATUS = [
        'aguardando' => 'Aguardando',
        'em_analise' => 'Em análise',
        'resolvida'  => 'Resolvida',
    ];

    // status no banco => classe CSS do selo colorido
    const STATUS_CLASSE = [
        'aguardando' => 'aguardando',
        'em_analise' => 'analise',
        'resolvida'  => 'resolvido',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Grava uma nova demanda. Retorna o ID criado ou false.
    public function armazenar($dados)
    {
        $this->db->query('INSERT INTO demandas (usuario_id, titulo, descricao, categoria)
                          VALUES (:usuario_id, :titulo, :descricao, :categoria)');
        $this->db->bind(':usuario_id', (int) $dados['usuario_id']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':categoria', $dados['categoria']);

        if ($this->db->executa()) {
            return (int) $this->db->ultimoIdInserido();
        }
        return false;
    }

    // Demandas de um usuário, da mais nova para a mais antiga. $limite = null traz todas.
    public function listarPorUsuario($usuarioId, $limite = null)
    {
        $sql = 'SELECT * FROM demandas WHERE usuario_id = :usuario_id ORDER BY id DESC';
        if ($limite !== null) {
            $sql .= ' LIMIT :limite';
        }

        $this->db->query($sql);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        if ($limite !== null) {
            $this->db->bind(':limite', (int) $limite);
        }
        return $this->db->resultados();
    }

    public function lerDemandaPorId($id)
    {
        $this->db->query('SELECT * FROM demandas WHERE id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->resultado();
    }

    // Números do painel "Minhas Demandas": total, em análise e resolvidas.
    public function resumo($usuarioId)
    {
        $this->db->query('SELECT status, COUNT(*) AS total FROM demandas
                          WHERE usuario_id = :usuario_id GROUP BY status');
        $this->db->bind(':usuario_id', (int) $usuarioId);

        $resumo = ['total' => 0, 'aguardando' => 0, 'em_analise' => 0, 'resolvida' => 0];
        foreach ($this->db->resultados() as $linha) {
            $resumo['total'] += (int) $linha->total;
            if (isset($resumo[$linha->status])) {
                $resumo[$linha->status] = (int) $linha->total;
            }
        }
        return $resumo;
    }

    public function excluir($id)
    {
        $this->db->query('DELETE FROM demandas WHERE id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->executa();
    }
}
