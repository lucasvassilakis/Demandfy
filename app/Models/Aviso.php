<?php
/*
 * MODEL AVISO (comunicados do Grêmio, somente leitura no sistema).
 * Para cadastrar novos avisos, por enquanto, use o phpMyAdmin (tabela "avisos").
 */
class Aviso
{
    const TIPOS = [
        'importante'  => 'Importante',
        'informativo' => 'Informativo',
        'lembrete'    => 'Lembrete',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Lista avisos. $tipo = null traz todos. $limite/$deslocamento servem para paginar.
    public function listar($tipo = null, $limite = 10, $deslocamento = 0)
    {
        $sql = 'SELECT * FROM avisos';
        if ($tipo !== null) {
            $sql .= ' WHERE tipo = :tipo';
        }
        $sql .= ' ORDER BY criado_em DESC, id DESC LIMIT :limite OFFSET :deslocamento';

        $this->db->query($sql);
        if ($tipo !== null) {
            $this->db->bind(':tipo', $tipo);
        }
        $this->db->bind(':limite', (int) $limite);
        $this->db->bind(':deslocamento', (int) $deslocamento);
        return $this->db->resultados();
    }

    // Quantos avisos existem (para calcular o número de páginas).
    public function contar($tipo = null)
    {
        $sql = 'SELECT COUNT(*) AS total FROM avisos';
        if ($tipo !== null) {
            $sql .= ' WHERE tipo = :tipo';
        }

        $this->db->query($sql);
        if ($tipo !== null) {
            $this->db->bind(':tipo', $tipo);
        }
        $linha = $this->db->resultado();
        return $linha ? (int) $linha->total : 0;
    }
}
