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
        'em_andamento' => 'Em andamento',
        'resolvida'  => 'Resolvida',
        'cancelada'  => 'Cancelada',
    ];

    // status no banco => classe CSS do selo colorido
    const STATUS_CLASSE = [
        'aguardando' => 'aguardando',
        'em_analise' => 'analise',
        'em_andamento' => 'andamento',
        'resolvida'  => 'resolvido',
        'cancelada'  => 'cancelada',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Normaliza os dados antes de inserir ou consultar. Essa regra centraliza a
    // limpeza de strings e deixa a model mais previsível para manutenção.
    public function normalizarDados($dados)
    {
        return [
            'usuario_id' => isset($dados['usuario_id']) ? (int) $dados['usuario_id'] : 0,
            'titulo' => trim((string) ($dados['titulo'] ?? '')),
            'descricao' => trim((string) ($dados['descricao'] ?? '')),
            'categoria' => trim((string) ($dados['categoria'] ?? '')),
        ];
    }

    public function categoriaValida($categoria)
    {
        return is_string($categoria) && array_key_exists($categoria, self::CATEGORIAS);
    }

    // Grava uma nova demanda. Retorna o ID criado ou false.
    public function armazenar($dados)
    {
        $dados = $this->normalizarDados($dados);

        if ($dados['usuario_id'] <= 0 || !$this->categoriaValida($dados['categoria'])) {
            return false;
        }

        if (mb_strlen($dados['titulo']) < 5 || mb_strlen($dados['titulo']) > 120) {
            return false;
        }

        if (mb_strlen($dados['descricao']) < 10 || mb_strlen($dados['descricao']) > 600) {
            return false;
        }

        $this->db->query('INSERT INTO demanda (dema_titulo, dema_descricao, cate_id, user_id)
                          SELECT :titulo, :descricao, cate_id, :usuario_id
                          FROM categoria
                          WHERE cate_nome = :categoria AND cate_tipo = \'demanda\'');
        $this->db->bind(':usuario_id', $dados['usuario_id']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':categoria', self::CATEGORIAS[$dados['categoria']]);

        if ($this->db->executa() && $this->db->totalResultados() > 0) {
            return (int) $this->db->ultimoIdInserido();
        }
        return false;
    }

    // Demandas de um usuário, da mais nova para a mais antiga. $limite = null traz todas.
    public function listarPorUsuario($usuarioId, $limite = null)
    {
        $sql = 'SELECT d.dema_id AS id, d.user_id AS usuario_id, d.dema_titulo AS titulo,
                       d.dema_descricao AS descricao, c.cate_nome AS categoria,
                       CASE d.dema_status
                           WHEN \'aberta\' THEN \'aguardando\'
                           WHEN \'em_andamento\' THEN \'em_analise\'
                           WHEN \'deferido\' THEN \'resolvida\'
                           ELSE d.dema_status
                       END AS status,
                       d.dema_status AS status_original,
                       d.dema_atualizacao AS criado_em
                FROM demanda d
                JOIN categoria c ON c.cate_id = d.cate_id
                WHERE d.user_id = :usuario_id ORDER BY d.dema_id DESC';
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
        $this->db->query('SELECT d.dema_id AS id, d.user_id AS usuario_id, d.dema_titulo AS titulo,
                                 d.dema_descricao AS descricao, c.cate_nome AS categoria,
                                 CASE d.dema_status
                                     WHEN \'aberta\' THEN \'aguardando\'
                                     WHEN \'em_andamento\' THEN \'em_analise\'
                                     WHEN \'deferido\' THEN \'resolvida\'
                                     ELSE d.dema_status
                                 END AS status,
                                 d.dema_status AS status_original,
                                 d.dema_atualizacao AS criado_em
                          FROM demanda d
                          JOIN categoria c ON c.cate_id = d.cate_id
                          WHERE d.dema_id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->resultado();
    }

    public function listarHistoricoPorUsuario($usuarioId, $limite, $deslocamento)
    {
        $this->db->query('SELECT d.dema_id AS id, d.user_id AS usuario_id, d.dema_titulo AS titulo,
                                 d.dema_descricao AS descricao, c.cate_nome AS categoria,
                                 d.dema_status AS status, d.dema_atualizacao AS atualizacao
                          FROM demanda d
                          JOIN categoria c ON c.cate_id = d.cate_id
                          WHERE d.user_id = :usuario_id
                          ORDER BY d.dema_ordem IS NULL, d.dema_ordem ASC, d.dema_atualizacao DESC, d.dema_id DESC
                          LIMIT :limite OFFSET :deslocamento');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':limite', (int) $limite);
        $this->db->bind(':deslocamento', (int) $deslocamento);
        return $this->db->resultados();
    }

    public function contarPorUsuario($usuarioId)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM demanda WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $resultado = $this->db->resultado();
        return (int) $resultado->total;
    }

    public function listarIdsHistoricoPorUsuario($usuarioId)
    {
        $this->db->query('SELECT dema_id AS id
                          FROM demanda
                          WHERE user_id = :usuario_id
                          ORDER BY dema_ordem IS NULL, dema_ordem ASC, dema_atualizacao DESC, dema_id DESC');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return array_map(static function ($linha) {
            return (int) $linha->id;
        }, $this->db->resultados());
    }

    public function lerHistoricoPorIdDoUsuario($id, $usuarioId)
    {
        $this->db->query('SELECT d.dema_id AS id, d.user_id AS usuario_id, d.dema_titulo AS titulo,
                                 d.dema_descricao AS descricao, c.cate_nome AS categoria,
                                 d.dema_status AS status, d.dema_atualizacao AS atualizacao
                          FROM demanda d
                          JOIN categoria c ON c.cate_id = d.cate_id
                          WHERE d.dema_id = :id AND d.user_id = :usuario_id');
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
        $dados = $this->normalizarDados($dados);
        if (!$this->categoriaValida($dados['categoria'])
            || mb_strlen($dados['titulo']) < 5
            || mb_strlen($dados['titulo']) > 120
            || mb_strlen($dados['descricao']) < 10
            || mb_strlen($dados['descricao']) > 600
            || ($status !== null && !array_key_exists($status, self::STATUS))) {
            return false;
        }

        $this->db->query('UPDATE demanda
                          SET dema_titulo = :titulo,
                              dema_descricao = :descricao,
                              cate_id = (
                                  SELECT cate_id FROM categoria
                                  WHERE cate_nome = :categoria AND cate_tipo = \'demanda\'
                              )' . ($status === null ? '' : ', dema_status = :status') . '
                          WHERE dema_id = :id'
                              . ($usuarioId === null ? '' : ' AND user_id = :usuario_id')
                              . ($status === null ? '' : ' AND dema_status = :status_atual'));
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':categoria', self::CATEGORIAS[$dados['categoria']]);
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

    public function tituloEmUso($titulo, $id)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM demanda WHERE dema_titulo = :titulo AND dema_id <> :id');
        $this->db->bind(':titulo', trim((string) $titulo));
        $this->db->bind(':id', (int) $id);
        return (int) $this->db->resultado()->total > 0;
    }

    public function salvarOrdemDoUsuario($usuarioId, $ids)
    {
        return $this->salvarOrdem('demanda', 'dema_id', 'dema_ordem', $usuarioId, $ids);
    }

    private function salvarOrdem($tabela, $colunaId, $colunaOrdem, $usuarioId, $ids)
    {
        $ids = $this->validarIdsOrdem($ids);
        if (!$ids) {
            return false;
        }

        $this->db->query('SELECT COUNT(*) AS total FROM ' . $tabela . ' WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        if ((int) $this->db->resultado()->total !== count($ids)) {
            return false;
        }

        $parametrosIds = [];
        foreach ($ids as $indice => $id) {
            $parametrosIds[] = ':id_' . $indice;
        }

        $this->db->query('SELECT COUNT(*) AS total FROM ' . $tabela . '
                          WHERE user_id = :usuario_id AND ' . $colunaId . ' IN (' . implode(', ', $parametrosIds) . ')');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        foreach ($ids as $indice => $id) {
            $this->db->bind(':id_' . $indice, $id);
        }
        if ((int) $this->db->resultado()->total !== count($ids)) {
            return false;
        }

        $casos = [];
        $parametros = [];
        foreach ($ids as $indice => $id) {
            $idParametro = ':case_id_' . $indice;
            $ordemParametro = ':ordem_' . $indice;
            $casos[] = 'WHEN ' . $idParametro . ' THEN ' . $ordemParametro;
            $parametros[$idParametro] = $id;
            $parametros[$ordemParametro] = $indice + 1;
        }
        $whereIds = [];
        foreach ($ids as $indice => $id) {
            $whereIds[] = ':where_id_' . $indice;
            $parametros[':where_id_' . $indice] = $id;
        }

        $this->db->query('UPDATE ' . $tabela . '
                          SET ' . $colunaOrdem . ' = CASE ' . $colunaId . ' ' . implode(' ', $casos) . ' END
                          WHERE user_id = :usuario_id AND ' . $colunaId . ' IN (' . implode(', ', $whereIds) . ')');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, (int) $valor);
        }
        return $this->db->executa();
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

    // Números do painel "Minhas Demandas": total, em análise e resolvidas.
    public function resumo($usuarioId)
    {
        $this->db->query('SELECT CASE dema_status
                                  WHEN \'aberta\' THEN \'aguardando\'
                                  WHEN \'em_andamento\' THEN \'em_analise\'
                                  WHEN \'deferido\' THEN \'resolvida\'
                                  ELSE dema_status
                              END AS status,
                              COUNT(*) AS total
                          FROM demanda WHERE user_id = :usuario_id GROUP BY status');
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

    public function resumoGlobal()
    {
        $this->db->query('SELECT
                              SUM(CASE WHEN dema_status IN (\'resolvida\', \'deferido\') THEN 1 ELSE 0 END) AS resolvidas,
                              SUM(CASE WHEN dema_status IN (\'em_andamento\', \'em_analise\') THEN 1 ELSE 0 END) AS em_andamento,
                              SUM(CASE WHEN dema_status IN (\'aberta\', \'aguardando\') THEN 1 ELSE 0 END) AS pendentes
                          FROM demanda');
        $resultado = $this->db->resultado();

        return [
            'resolvidas' => (int) $resultado->resolvidas,
            'em_andamento' => (int) $resultado->em_andamento,
            'pendentes' => (int) $resultado->pendentes,
        ];
    }

    public function resumoModeracaoGlobal()
    {
        $this->db->query('SELECT
                              SUM(CASE WHEN dema_status IN (\'resolvida\', \'deferido\') THEN 1 ELSE 0 END) AS resolvidas,
                              SUM(CASE WHEN dema_status = \'em_andamento\' THEN 1 ELSE 0 END) AS em_andamento,
                              SUM(CASE WHEN dema_status = \'cancelada\' THEN 1 ELSE 0 END) AS negadas,
                              SUM(CASE WHEN dema_status IN (\'aberta\', \'aguardando\', \'em_analise\') THEN 1 ELSE 0 END) AS em_analise
                          FROM demanda');
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
        $this->db->query('SELECT COUNT(*) AS total FROM demanda d' . $filtroSql['sql']);
        $this->vincularFiltrosGlobais($filtroSql['parametros']);
        return (int) $this->db->resultado()->total;
    }

    public function listarTodasHistorico($limite, $deslocamento, $filtros = [])
    {
        $filtroSql = $this->construirFiltrosGlobais($filtros);
        $this->db->query('SELECT d.dema_id AS id, d.user_id AS usuario_id, d.dema_titulo AS titulo,
                                 d.dema_descricao AS descricao, c.cate_nome AS categoria,
                                 d.dema_status AS status, d.dema_atualizacao AS atualizacao,
                                 d.dema_criado_em AS criado_em, u.user_nome AS autor
                          FROM demanda d
                          JOIN categoria c ON c.cate_id = d.cate_id
                          JOIN usuario u ON u.user_id = d.user_id
                          ' . $filtroSql['sql'] . '
                          ORDER BY d.dema_atualizacao DESC, d.dema_id DESC
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
            $condicoes[] = 'd.user_id = :filtro_usuario_id';
            $parametros[':filtro_usuario_id'] = (int) $filtros['usuario_id'];
        }

        $statusPorFiltro = [
            'aguardando' => ['aberta', 'aguardando'],
            'em_analise' => ['em_analise'],
            'em_andamento' => ['em_andamento'],
            'resolvida' => ['deferido', 'resolvida'],
            'cancelada' => ['cancelada'],
        ];
        $status = isset($filtros['status']) ? $filtros['status'] : '';
        if (isset($statusPorFiltro[$status])) {
            $placeholders = [];
            foreach ($statusPorFiltro[$status] as $indice => $valor) {
                $placeholder = ':filtro_status_' . $indice;
                $placeholders[] = $placeholder;
                $parametros[$placeholder] = $valor;
            }
            $condicoes[] = 'd.dema_status IN (' . implode(', ', $placeholders) . ')';
        }

        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = 'd.dema_criado_em >= :filtro_data_inicio';
            $parametros[':filtro_data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }
        if (!empty($filtros['data_fim'])) {
            $condicoes[] = 'd.dema_criado_em < DATE_ADD(:filtro_data_fim, INTERVAL 1 DAY)';
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

    public function excluir($id)
    {
        $this->db->query('DELETE FROM demanda WHERE dema_id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->executa();
    }

    public function excluirDoUsuario($id, $usuarioId)
    {
        $this->db->query('DELETE FROM demanda WHERE dema_id = :id AND user_id = :usuario_id');
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->executa() && $this->db->totalResultados() > 0;
    }
}
