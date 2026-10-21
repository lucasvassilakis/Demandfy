<?php
/*
 * MODEL SUGESTAO (ideias para melhorar o campus). Mesma lógica do model Demanda.
 */
class Sugestao
{
    const CATEGORIAS = [
        'infraestrutura' => 'Infraestrutura',
        'eventos'        => 'Eventos',
        'academico'      => 'Acadêmico',
        'bem-estar'      => 'Bem-estar',
        'outros'         => 'Outros',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Retorna o ID criado ou false.
    public function armazenar($dados)
    {
        $this->db->query('INSERT INTO sugestoes (usuario_id, titulo, descricao, categoria)
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

    public function listarPorUsuario($usuarioId, $limite = 5)
    {
        $this->db->query('SELECT * FROM sugestoes WHERE usuario_id = :usuario_id
                          ORDER BY id DESC LIMIT :limite');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':limite', (int) $limite);
        return $this->db->resultados();
    }
}
