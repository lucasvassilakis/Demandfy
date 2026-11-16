<?php

class Evento
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function listarPorPeriodo($inicio, $fim)
    {
        $this->db->query('SELECT e.even_id AS id, e.even_titulo AS titulo,
                                 e.even_descricao AS descricao, c.cale_dia AS data_evento,
                                 e.even_hora_inicio AS hora_inicio, e.even_hora_fim AS hora_fim,
                                 e.even_ordem AS ordem, ca.cate_id AS categoria_id,
                                 ca.cate_nome AS categoria
                          FROM evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          JOIN categoria ca ON ca.cate_id = e.cate_id AND ca.cate_tipo = \'evento\'
                          WHERE c.cale_dia BETWEEN :inicio AND :fim
                          ORDER BY c.cale_dia, e.even_ordem, e.even_id');
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        return $this->db->resultados();
    }

    public function listarPorDia($data)
    {
        $this->db->query('SELECT e.even_id AS id, e.even_titulo AS titulo,
                                 e.even_descricao AS descricao, e.even_hora_inicio AS hora_inicio,
                                 e.even_hora_fim AS hora_fim, e.even_ordem AS ordem,
                                 e.cate_id AS categoria_id, ca.cate_nome AS categoria
                          FROM evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          JOIN categoria ca ON ca.cate_id = e.cate_id AND ca.cate_tipo = \'evento\'
                          WHERE c.cale_dia = :data
                          ORDER BY e.even_ordem, e.even_id');
        $this->db->bind(':data', $data);
        return $this->db->resultados();
    }

    public function listarCategorias()
    {
        $this->db->query('SELECT cate_id AS id, cate_nome AS nome
                          FROM categoria
                          WHERE cate_tipo = \'evento\'
                          ORDER BY cate_id');
        return $this->db->resultados();
    }

    public function categoriaValida($categoriaId)
    {
        $this->db->query('SELECT 1 FROM categoria
                          WHERE cate_id = :categoria_id AND cate_tipo = \'evento\'
                          LIMIT 1');
        $this->db->bind(':categoria_id', (int) $categoriaId);
        return (bool) $this->db->resultado();
    }

    public function criar($data, $dados)
    {
        $calendarioId = $this->obterCalendarioId($data);
        if ($calendarioId <= 0) {
            return false;
        }

        $this->db->query('SELECT COALESCE(MAX(even_ordem), 0) + 1 AS proxima_ordem
                          FROM evento WHERE cale_id = :calendario_id');
        $this->db->bind(':calendario_id', $calendarioId);
        $ordem = (int) $this->db->resultado()->proxima_ordem;

        $this->db->query('INSERT INTO evento
                              (even_titulo, even_descricao, cate_id, cale_id,
                               even_hora_inicio, even_hora_fim, even_ordem)
                          VALUES
                              (:titulo, :descricao, :categoria_id, :calendario_id,
                               :hora_inicio, :hora_fim, :ordem)');
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':categoria_id', (int) $dados['categoria_id']);
        $this->db->bind(':calendario_id', $calendarioId);
        $this->db->bind(':hora_inicio', $dados['hora_inicio']);
        $this->db->bind(':hora_fim', $dados['hora_fim']);
        $this->db->bind(':ordem', $ordem);
        return $this->db->executa() ? (int) $this->db->ultimoIdInserido() : false;
    }

    public function lerPorIdEDia($id, $data)
    {
        $this->db->query('SELECT e.even_id AS id, e.even_titulo AS titulo,
                                 e.even_descricao AS descricao, e.cate_id AS categoria_id,
                                 e.even_hora_inicio AS hora_inicio, e.even_hora_fim AS hora_fim
                          FROM evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          WHERE e.even_id = :id AND c.cale_dia = :data');
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':data', $data);
        return $this->db->resultado();
    }

    public function atualizar($id, $data, $dados)
    {
        $this->db->query('UPDATE evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          JOIN categoria ca
                            ON ca.cate_id = :categoria_id AND ca.cate_tipo = \'evento\'
                          SET e.even_titulo = :titulo,
                              e.even_descricao = :descricao,
                              e.cate_id = ca.cate_id,
                              e.even_hora_inicio = :hora_inicio,
                              e.even_hora_fim = :hora_fim
                          WHERE e.even_id = :id AND c.cale_dia = :data');
        $this->db->bind(':categoria_id', (int) $dados['categoria_id']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':hora_inicio', $dados['hora_inicio']);
        $this->db->bind(':hora_fim', $dados['hora_fim']);
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':data', $data);
        return $this->db->executa();
    }

    public function excluir($id, $data)
    {
        $this->db->query('DELETE e
                          FROM evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          WHERE e.even_id = :id AND c.cale_dia = :data');
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':data', $data);
        return $this->db->executa();
    }

    public function salvarOrdemDoDia($data, $ids)
    {
        $ids = $this->validarIds($ids);
        if (!$ids) {
            return false;
        }

        $parametrosIds = [];
        foreach ($ids as $indice => $id) {
            $parametrosIds[] = ':id_' . $indice;
        }

        $this->db->query('SELECT COUNT(*) AS total
                          FROM evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          WHERE c.cale_dia = :data');
        $this->db->bind(':data', $data);
        if ((int) $this->db->resultado()->total !== count($ids)) {
            return false;
        }

        $this->db->query('SELECT COUNT(*) AS total
                          FROM evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          WHERE c.cale_dia = :data AND e.even_id IN (' . implode(', ', $parametrosIds) . ')');
        $this->db->bind(':data', $data);
        foreach ($ids as $indice => $id) {
            $this->db->bind(':id_' . $indice, $id);
        }
        if ((int) $this->db->resultado()->total !== count($ids)) {
            return false;
        }

        $casos = [];
        $idsWhere = [];
        foreach ($ids as $indice => $id) {
            $casos[] = 'WHEN :case_id_' . $indice . ' THEN :ordem_' . $indice;
            $idsWhere[] = ':where_id_' . $indice;
        }

        $this->db->query('UPDATE evento e
                          JOIN calendario c ON c.cale_id = e.cale_id
                          SET e.even_ordem = CASE e.even_id ' . implode(' ', $casos) . ' END
                          WHERE c.cale_dia = :data AND e.even_id IN (' . implode(', ', $idsWhere) . ')');
        $this->db->bind(':data', $data);
        foreach ($ids as $indice => $id) {
            $this->db->bind(':case_id_' . $indice, $id);
            $this->db->bind(':ordem_' . $indice, $indice + 1);
            $this->db->bind(':where_id_' . $indice, $id);
        }
        return $this->db->executa();
    }

    private function obterCalendarioId($data)
    {
        $timestamp = strtotime($data);
        if ($timestamp === false) {
            return 0;
        }

        $this->db->query('INSERT INTO calendario (cale_dia, cale_semana, cale_mes, cale_ano)
                          VALUES (:data, :semana, :mes, :ano)
                          ON DUPLICATE KEY UPDATE cale_id = LAST_INSERT_ID(cale_id)');
        $this->db->bind(':data', $data);
        $this->db->bind(':semana', (int) date('W', $timestamp));
        $this->db->bind(':mes', (int) date('n', $timestamp));
        $this->db->bind(':ano', (int) date('Y', $timestamp));
        if (!$this->db->executa()) {
            return 0;
        }
        return (int) $this->db->ultimoIdInserido();
    }

    private function validarIds($ids)
    {
        if (!is_array($ids) || count($ids) === 0) {
            return false;
        }

        $validos = [];
        foreach ($ids as $id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0 || in_array($id, $validos, true)) {
                return false;
            }
            $validos[] = $id;
        }
        return $validos;
    }
}
