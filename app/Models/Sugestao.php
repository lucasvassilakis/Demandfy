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

    const STATUS = [
        'enviada'    => 'Enviada',
        'em_analise' => 'Em análise',
        'aceita'     => 'Aceita',
        'recusada'   => 'Recusada',
        'em_andamento' => 'Em andamento',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Retorna o ID criado ou false.
    public function armazenar($dados)
    {
        if (!isset($dados['categoria']) || !array_key_exists($dados['categoria'], self::CATEGORIAS)) {
            return false;
        }

        $this->db->query('INSERT INTO sugestao (user_id, suge_titulo, suge_descricao, suge_categoria, cate_id)
                          SELECT :usuario_id, :titulo, :descricao, :categoria, c.cate_id
                          FROM categoria c
                          WHERE c.cate_nome = :categoria_nome AND c.cate_tipo = \'sugestao\'');
        $this->db->bind(':usuario_id', (int) $dados['usuario_id']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':categoria', $dados['categoria']);
        $this->db->bind(':categoria_nome', self::CATEGORIAS[$dados['categoria']]);

        if ($this->db->executa() && $this->db->totalResultados() > 0) {
            return (int) $this->db->ultimoIdInserido();
        }
        return false;
    }

    public function listarPorUsuario($usuarioId, $limite = 5)
    {
        $this->db->query('SELECT suge_id AS id, user_id AS usuario_id, suge_titulo AS titulo,
                                 suge_descricao AS descricao, suge_categoria AS categoria,
                                 suge_status AS status
                          FROM sugestao WHERE user_id = :usuario_id
                          ORDER BY suge_id DESC LIMIT :limite');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':limite', (int) $limite);
        return $this->db->resultados();
    }

    public function listarHistoricoPorUsuario($usuarioId, $limite, $deslocamento)
    {
        $this->db->query('SELECT s.suge_id AS id, s.user_id AS usuario_id, s.suge_titulo AS titulo,
                                 s.suge_descricao AS descricao, s.suge_categoria AS categoria,
                                 s.suge_status AS status,
                                 COALESCE(
                                     (SELECT MAX(h.hist_data) FROM historico_sugestao h WHERE h.suge_id = s.suge_id),
                                     s.suge_atualizacao
                                 ) AS atualizacao
                          FROM sugestao s
                          WHERE s.user_id = :usuario_id
                          ORDER BY s.suge_ordem IS NULL, s.suge_ordem ASC, atualizacao DESC, s.suge_id DESC
                          LIMIT :limite OFFSET :deslocamento');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':limite', (int) $limite);
        $this->db->bind(':deslocamento', (int) $deslocamento);
        return $this->db->resultados();
    }

    public function contarPorUsuario($usuarioId)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM sugestao WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $resultado = $this->db->resultado();
        return (int) $resultado->total;
    }

    public function resumoModeracaoGlobal()
    {
        $this->db->query('SELECT
                              SUM(CASE WHEN suge_status = \'aceita\' THEN 1 ELSE 0 END) AS resolvidas,
                              SUM(CASE WHEN suge_status = \'em_andamento\' THEN 1 ELSE 0 END) AS em_andamento,
                              SUM(CASE WHEN suge_status = \'recusada\' THEN 1 ELSE 0 END) AS negadas,
                              SUM(CASE WHEN suge_status IN (\'enviada\', \'em_analise\') THEN 1 ELSE 0 END) AS em_analise
                          FROM sugestao');
        $resultado = $this->db->resultado();

        return [
            'resolvidas' => (int) $resultado->resolvidas,
            'em_andamento' => (int) $resultado->em_andamento,
            'negadas' => (int) $resultado->negadas,
            'em_analise' => (int) $resultado->em_analise,
        ];
    }

    public function contarTodas($filtros = [])
    {
        $filtroSql = $this->construirFiltrosGlobais($filtros);
        $this->db->query('SELECT COUNT(*) AS total FROM sugestao s' . $filtroSql['sql']);
        $this->vincularFiltrosGlobais($filtroSql['parametros']);
        return (int) $this->db->resultado()->total;
    }

    public function listarTodasHistorico($limite, $deslocamento, $filtros = [])
    {
        $filtroSql = $this->construirFiltrosGlobais($filtros);
        $this->db->query('SELECT s.suge_id AS id, s.user_id AS usuario_id, s.suge_titulo AS titulo,
                                 s.suge_descricao AS descricao, s.suge_categoria AS categoria,
                                 s.suge_status AS status,
                                 COALESCE(
                                     (SELECT MAX(h.hist_data) FROM historico_sugestao h WHERE h.suge_id = s.suge_id),
                                     s.suge_atualizacao
                                 ) AS atualizacao,
                                 s.suge_criado_em AS criado_em, u.user_nome AS autor
                          FROM sugestao s
                          JOIN usuario u ON u.user_id = s.user_id
                          ' . $filtroSql['sql'] . '
                          ORDER BY atualizacao DESC, s.suge_id DESC
                          LIMIT :limite OFFSET :deslocamento');
        $this->db->bind(':limite', (int) $limite);
        $this->db->bind(':deslocamento', (int) $deslocamento);
        $this->vincularFiltrosGlobais($filtroSql['parametros']);
        return $this->db->resultados();
    }

    private function construirFiltrosGlobais($filtros)
    {
        $condicoes = [];
        $parametros = [];
        if (!empty($filtros['usuario_id'])) {
            $condicoes[] = 's.user_id = :filtro_usuario_id';
            $parametros[':filtro_usuario_id'] = (int) $filtros['usuario_id'];
        }

        $statusPorFiltro = [
            'enviada' => ['enviada'],
            'em_analise' => ['em_analise'],
            'em_andamento' => ['em_andamento'],
            'aceita' => ['aceita'],
            'recusada' => ['recusada'],
        ];
        $status = isset($filtros['status']) ? $filtros['status'] : '';
        if (isset($statusPorFiltro[$status])) {
            $placeholders = [];
            foreach ($statusPorFiltro[$status] as $indice => $valor) {
                $placeholder = ':filtro_status_' . $indice;
                $placeholders[] = $placeholder;
                $parametros[$placeholder] = $valor;
            }
            $condicoes[] = 's.suge_status IN (' . implode(', ', $placeholders) . ')';
        }

        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = 's.suge_criado_em >= :filtro_data_inicio';
            $parametros[':filtro_data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }
        if (!empty($filtros['data_fim'])) {
            $condicoes[] = 's.suge_criado_em < DATE_ADD(:filtro_data_fim, INTERVAL 1 DAY)';
            $parametros[':filtro_data_fim'] = $filtros['data_fim'];
        }

        return [
            'sql' => $condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '',
            'parametros' => $parametros,
        ];
    }

    private function vincularFiltrosGlobais($parametros)
    {
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }
    }

    public function lerSugestaoPorId($id)
    {
        $this->db->query('SELECT suge_id AS id, user_id AS usuario_id, suge_titulo AS titulo,
                                 suge_descricao AS descricao, suge_categoria AS categoria,
                                 suge_status AS status, suge_status AS status_original
                          FROM sugestao WHERE suge_id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->resultado();
    }

    public function listarIdsHistoricoPorUsuario($usuarioId)
    {
        $this->db->query('SELECT s.suge_id AS id,
                                 COALESCE(
                                     (SELECT MAX(h.hist_data) FROM historico_sugestao h WHERE h.suge_id = s.suge_id),
                                     s.suge_atualizacao
                                 ) AS atualizacao
                          FROM sugestao s
                          WHERE s.user_id = :usuario_id
                          ORDER BY s.suge_ordem IS NULL, s.suge_ordem ASC, atualizacao DESC, s.suge_id DESC');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return array_map(static function ($linha) {
            return (int) $linha->id;
        }, $this->db->resultados());
    }

    public function lerHistoricoPorIdDoUsuario($id, $usuarioId)
    {
        $this->db->query('SELECT s.suge_id AS id, s.user_id AS usuario_id, s.suge_titulo AS titulo,
                                 s.suge_descricao AS descricao, s.suge_categoria AS categoria,
                                 s.suge_status AS status,
                                 COALESCE(
                                     (SELECT MAX(h.hist_data) FROM historico_sugestao h WHERE h.suge_id = s.suge_id),
                                     s.suge_atualizacao
                                 ) AS atualizacao
                          FROM sugestao s
                          WHERE s.suge_id = :id AND s.user_id = :usuario_id');
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->resultado();
    }

    public function atualizarDoUsuario($id, $usuarioId, $dados)
    {
        return $this->atualizar($id, $dados, (int) $usuarioId);
    }

    public function atualizarComoModerador($id, $dados, $status = null, $statusAtual = null)
    {
        return $this->atualizar($id, $dados, null, $status, $statusAtual);
    }

    private function atualizar($id, $dados, $usuarioId, $status = null, $statusAtual = null)
    {
        $titulo = trim((string) ($dados['titulo'] ?? ''));
        $descricao = trim((string) ($dados['descricao'] ?? ''));
        $categoria = trim((string) ($dados['categoria'] ?? ''));
        if (!array_key_exists($categoria, self::CATEGORIAS)
            || mb_strlen($titulo) < 5
            || mb_strlen($titulo) > 120
            || mb_strlen($descricao) < 10
            || mb_strlen($descricao) > 600
            || ($status !== null && !array_key_exists($status, self::STATUS))) {
            return false;
        }

        $this->db->query('UPDATE sugestao s
                          JOIN categoria c
                            ON c.cate_nome = :categoria_nome AND c.cate_tipo = \'sugestao\'
                          SET s.suge_titulo = :titulo,
                              s.suge_descricao = :descricao,
                              s.suge_categoria = :categoria,
                              s.cate_id = c.cate_id,
                              s.suge_atualizacao = CURRENT_TIMESTAMP'
                              . ($status === null ? '' : ', s.suge_status = :status') . '
                          WHERE s.suge_id = :id'
                              . ($usuarioId === null ? '' : ' AND s.user_id = :usuario_id')
                              . ($status === null ? '' : ' AND s.suge_status = :status_atual'));
        $this->db->bind(':categoria_nome', self::CATEGORIAS[$categoria]);
        $this->db->bind(':titulo', $titulo);
        $this->db->bind(':descricao', $descricao);
        $this->db->bind(':categoria', $categoria);
        $this->db->bind(':id', (int) $id);
        if ($status !== null) {
            $this->db->bind(':status', $status);
            $this->db->bind(':status_atual', $statusAtual);
        }
        if ($usuarioId !== null) {
            $this->db->bind(':usuario_id', $usuarioId);
        }
        return $this->db->executa() && ($status === null || $this->db->totalResultados() > 0);
    }

    public function salvarOrdemDoUsuario($usuarioId, $ids)
    {
        $ids = $this->validarIdsOrdem($ids);
        if (!$ids) {
            return false;
        }

        $this->db->query('SELECT COUNT(*) AS total FROM sugestao WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        if ((int) $this->db->resultado()->total !== count($ids)) {
            return false;
        }

        $parametrosIds = [];
        foreach ($ids as $indice => $id) {
            $parametrosIds[] = ':id_' . $indice;
        }

        $this->db->query('SELECT COUNT(*) AS total FROM sugestao
                          WHERE user_id = :usuario_id AND suge_id IN (' . implode(', ', $parametrosIds) . ')');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        foreach ($ids as $indice => $id) {
            $this->db->bind(':id_' . $indice, $id);
        }
        if ((int) $this->db->resultado()->total !== count($ids)) {
            return false;
        }

        $casos = [];
        $parametros = [];
        $whereIds = [];
        foreach ($ids as $indice => $id) {
            $casos[] = 'WHEN :case_id_' . $indice . ' THEN :ordem_' . $indice;
            $parametros[':case_id_' . $indice] = $id;
            $parametros[':ordem_' . $indice] = $indice + 1;
            $whereIds[] = ':where_id_' . $indice;
            $parametros[':where_id_' . $indice] = $id;
        }

        $this->db->query('UPDATE sugestao
                          SET suge_ordem = CASE suge_id ' . implode(' ', $casos) . ' END
                          WHERE user_id = :usuario_id AND suge_id IN (' . implode(', ', $whereIds) . ')');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, (int) $valor);
        }
        return $this->db->executa();
    }

    public function tituloEmUso($titulo, $id)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM sugestao WHERE suge_titulo = :titulo AND suge_id <> :id');
        $this->db->bind(':titulo', trim((string) $titulo));
        $this->db->bind(':id', (int) $id);
        return (int) $this->db->resultado()->total > 0;
    }

    private function validarIdsOrdem($ids)
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

    public function excluirDoUsuario($id, $usuarioId)
    {
        $this->db->query('DELETE FROM sugestao WHERE suge_id = :id AND user_id = :usuario_id');
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->executa() && $this->db->totalResultados() > 0;
    }
}
