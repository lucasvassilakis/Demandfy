<?php
class Aviso
{
    const TIPOS = [
        'importante'  => 'Importante',
        'informativo' => 'Informativo',
        'lembrete'    => 'Lembrete',
    ];

    private $db;
    private $disponivel;

    public function __construct()
    {
        $this->db = new Database();
        $this->disponivel = null;
    }

    public function disponivel()
    {
        if ($this->disponivel !== null) {
            return $this->disponivel;
        }

        $this->db->query('SELECT COUNT(*) AS total
                          FROM information_schema.tables
                          WHERE table_schema = DATABASE() AND table_name = :tabela');
        $this->db->bind(':tabela', 'aviso');
        $resultado = $this->db->resultado();
        $this->disponivel = $resultado && (int) $resultado->total > 0;

        return $this->disponivel;
    }

    public function listar($tipo = null, $limite = 10, $deslocamento = 0)
    {
        $categoria = $this->categoriaDoTipo($tipo);
        if (!$this->disponivel()) {
            return [];
        }

        $sql = '        SELECT a.avis_id AS id, a.avis_titulo AS titulo,
                       a.avis_descricao AS texto, a.avis_data AS data,
               LOWER(c.cate_nome) AS tipo, a.cate_id AS categoria_id
                FROM aviso a
                JOIN categoria c ON c.cate_id = a.cate_id
                WHERE c.cate_tipo = \'aviso\'';
        if ($categoria !== null) {
            $sql .= ' AND c.cate_nome = :categoria';
        }
        $sql .= ' ORDER BY a.avis_data DESC, a.avis_id DESC
                  LIMIT :limite OFFSET :deslocamento';

        $this->db->query($sql);
        if ($categoria !== null) {
            $this->db->bind(':categoria', $categoria);
        }
        $this->db->bind(':limite', max(1, (int) $limite));
        $this->db->bind(':deslocamento', max(0, (int) $deslocamento));
        return $this->db->resultados();
    }

    public function contar($tipo = null)
    {
        $categoria = $this->categoriaDoTipo($tipo);
        if (!$this->disponivel()) {
            return 0;
        }

        $sql = 'SELECT COUNT(*) AS total
                FROM aviso a
                JOIN categoria c ON c.cate_id = a.cate_id
                WHERE c.cate_tipo = \'aviso\'';
        if ($categoria !== null) {
            $sql .= ' AND c.cate_nome = :categoria';
        }

        $this->db->query($sql);
        if ($categoria !== null) {
            $this->db->bind(':categoria', $categoria);
        }
        $resultado = $this->db->resultado();
        return $resultado ? (int) $resultado->total : 0;
    }

    public function listarCategorias()
    {
        if (!$this->disponivel()) {
            return [];
        }

        $this->db->query("SELECT cate_id AS id, cate_nome AS nome
                          FROM categoria
                          WHERE cate_tipo = 'aviso'
                          ORDER BY cate_id");
        return $this->db->resultados();
    }

    public function categoriaValida($categoriaId)
    {
        if (!$this->disponivel() || (int) $categoriaId <= 0) {
            return false;
        }

        $this->db->query("SELECT cate_id FROM categoria
                          WHERE cate_id = :categoria_id AND cate_tipo = 'aviso'
                          LIMIT 1");
        $this->db->bind(':categoria_id', (int) $categoriaId);
        return (bool) $this->db->resultado();
    }

    public function lerPorId($id)
    {
        if (!$this->disponivel() || (int) $id <= 0) {
            return false;
        }

        $this->db->query('SELECT avis_id AS id, avis_titulo AS titulo,
                                 avis_descricao AS texto, cate_id AS categoria_id
                          FROM aviso
                          WHERE avis_id = :id
                          LIMIT 1');
        $this->db->bind(':id', (int) $id);
        return $this->db->resultado();
    }

    public function criar($usuarioId, $dados)
    {
        if (!$this->disponivel() || (int) $usuarioId <= 0) {
            return false;
        }

        $this->db->query('INSERT INTO aviso
                              (avis_titulo, avis_descricao, avis_data, cate_id, user_id)
                          VALUES (:titulo, :descricao, CURRENT_TIMESTAMP, :categoria_id, :usuario_id)');
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':categoria_id', (int) $dados['categoria_id']);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        if (!$this->db->executa()) {
            return false;
        }

        return (int) $this->db->ultimoIdInserido();
    }

    public function atualizar($id, $dados)
    {
        if (!$this->disponivel()) {
            return false;
        }

        $this->db->query('UPDATE aviso
                          SET avis_titulo = :titulo,
                              avis_descricao = :descricao,
                              cate_id = :categoria_id
                          WHERE avis_id = :id');
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':categoria_id', (int) $dados['categoria_id']);
        $this->db->bind(':id', (int) $id);
        return $this->db->executa();
    }

    public function excluir($id)
    {
        if (!$this->disponivel()) {
            return false;
        }

        $this->db->query('DELETE FROM aviso WHERE avis_id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->executa() && $this->db->totalResultados() > 0;
    }

    private function categoriaDoTipo($tipo)
    {
        if ($tipo === null) {
            return null;
        }
        if (!is_string($tipo) || !array_key_exists($tipo, self::TIPOS)) {
            throw new InvalidArgumentException('Tipo de aviso inválido.');
        }

        return self::TIPOS[$tipo];
    }
}
