<?php

class Permissao
{
    const GERENCIAR_PERMISSOES = 'usuarios.permissoes.gerenciar';
    const EDITAR_DEMANDAS = 'demandas.editar';
    const EDITAR_SUGESTOES = 'sugestoes.editar';
    const GERENCIAR_EVENTOS = 'eventos.gerenciar';
    const GERENCIAR_AVISOS = 'avisos.gerenciar';

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function usuarioTem($usuarioId, $permissao)
    {
        if ((int) $usuarioId <= 0 || !$this->permissaoValida($permissao)) {
            return false;
        }

        $this->db->query('SELECT 1 FROM usuario_permissao
                          WHERE user_id = :usuario_id AND permissao = :permissao
                          LIMIT 1');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':permissao', $permissao);
        return (bool) $this->db->resultado();
    }

    public function listarDoUsuario($usuarioId)
    {
        $this->db->query('SELECT permissao, concedida_em, concedida_por
                          FROM usuario_permissao
                          WHERE user_id = :usuario_id
                          ORDER BY permissao');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->resultados();
    }

    public function contarUsuariosGerenciaveis($busca = '', $perfil = '')
    {
        $filtro = $this->filtroUsuariosGerenciaveis($busca, $perfil);
        $this->db->query('SELECT COUNT(*) AS total FROM usuario u' . $filtro['sql']);
        $this->vincularFiltroUsuariosGerenciaveis($filtro['parametros']);
        return (int) $this->db->resultado()->total;
    }

    public function listarUsuariosGerenciaveis($limite, $deslocamento, $busca = '', $perfil = '', $priorizarUsuarioId = 0)
    {
        $filtro = $this->filtroUsuariosGerenciaveis($busca, $perfil);
        $this->db->query('SELECT u.user_id AS id, u.user_nome AS nome, u.user_email AS email,
                                 u.foto_perfil AS foto_perfil,
                                 EXISTS (
                                     SELECT 1 FROM usuario_permissao p
                                     WHERE p.user_id = u.user_id AND p.permissao = \'admin\'
                                 ) AS administrador
                          FROM usuario u' . $filtro['sql'] . '
                          ORDER BY CASE WHEN u.user_id = :priorizar_usuario_id THEN 0 ELSE 1 END,
                                   u.user_nome ASC, u.user_id ASC
                          LIMIT :limite OFFSET :deslocamento');
        $this->vincularFiltroUsuariosGerenciaveis($filtro['parametros']);
        $this->db->bind(':priorizar_usuario_id', (int) $priorizarUsuarioId);
        $this->db->bind(':limite', max(1, (int) $limite));
        $this->db->bind(':deslocamento', max(0, (int) $deslocamento));
        return $this->db->resultados();
    }

    public function alterarNivelAdministrador($atorId, $usuarioId, $nivel)
    {
        $atorId = (int) $atorId;
        $usuarioId = (int) $usuarioId;
        if ($atorId <= 0 || $usuarioId <= 0 || !in_array($nivel, ['admin', 'padrao'], true)
            || !$this->usuarioTem($atorId, 'admin')) {
            return false;
        }

        $this->db->query('SELECT u.user_id,
                                 EXISTS (
                                     SELECT 1 FROM usuario_permissao p
                                     WHERE p.user_id = u.user_id AND p.permissao = \'admin\'
                                 ) AS administrador
                          FROM usuario u
                          WHERE u.user_id = :usuario_id');
        $this->db->bind(':usuario_id', $usuarioId);
        $usuario = $this->db->resultado();
        if (!$usuario) {
            return false;
        }

        if ($nivel === 'padrao' && !empty($usuario->administrador) && $usuarioId !== $atorId) {
            return false;
        }

        if ($nivel === 'admin') {
            $this->db->query('INSERT INTO usuario_permissao (user_id, permissao, concedida_por)
                              VALUES (:usuario_id, \'admin\', :concedida_por)
                              ON DUPLICATE KEY UPDATE concedida_por = VALUES(concedida_por),
                                                      concedida_em = CURRENT_TIMESTAMP');
            $this->db->bind(':usuario_id', $usuarioId);
            $this->db->bind(':concedida_por', $atorId);
            return $this->db->executa();
        }

        $this->db->query('DELETE permissoes
                          FROM usuario_permissao permissoes
                          LEFT JOIN usuario_permissao administrador
                            ON administrador.user_id = permissoes.user_id
                           AND administrador.permissao = \'admin\'
                          WHERE permissoes.user_id = :usuario_id
                            AND (permissoes.user_id = :ator_id OR administrador.user_id IS NULL)');
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':ator_id', $atorId);
        return $this->db->executa();
    }

    public function excluirUsuarioAdministrador($atorId, $usuarioId)
    {
        $atorId = (int) $atorId;
        $usuarioId = (int) $usuarioId;
        if ($atorId <= 0 || $usuarioId <= 0) {
            return ['status' => 'invalido'];
        }
        if ($atorId === $usuarioId) {
            return ['status' => 'proprio'];
        }
        if (!$this->usuarioTem($atorId, 'admin')) {
            return ['status' => 'sem_permissao'];
        }

        $this->db->query('SELECT foto_perfil FROM usuario WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', $usuarioId);
        $usuario = $this->db->resultado();
        if (!$usuario) {
            return ['status' => 'inexistente'];
        }
        if ($this->usuarioPossuiRegistrosVinculados($usuarioId)) {
            return ['status' => 'com_registros'];
        }

        $this->db->query('DELETE FROM usuario
                          WHERE user_id = :usuario_id
                            AND user_id <> :ator_id
                            AND EXISTS (
                                SELECT 1 FROM usuario_permissao p
                                WHERE p.user_id = :ator_admin_id AND p.permissao = \'admin\'
                            )
                            AND ' . $this->condicaoSemRegistrosVinculados('usuario.user_id'));
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':ator_id', $atorId);
        $this->db->bind(':ator_admin_id', $atorId);
        $this->db->executa();

        if ($this->db->totalResultados() !== 1) {
            if (!$this->usuarioTem($atorId, 'admin')) {
                return ['status' => 'sem_permissao'];
            }
            $this->db->query('SELECT user_id FROM usuario WHERE user_id = :usuario_id');
            $this->db->bind(':usuario_id', $usuarioId);
            if (!$this->db->resultado()) {
                return ['status' => 'inexistente'];
            }
            return $this->usuarioPossuiRegistrosVinculados($usuarioId)
                ? ['status' => 'com_registros']
                : ['status' => 'falha'];
        }

        return [
            'status' => 'excluido',
            'foto_perfil' => $usuario->foto_perfil,
        ];
    }

    public function excluirContaPropria($usuarioId)
    {
        $usuarioId = (int) $usuarioId;
        if ($usuarioId <= 0) {
            return ['status' => 'invalido'];
        }

        $this->db->query('SELECT foto_perfil FROM usuario WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', $usuarioId);
        $usuario = $this->db->resultado();
        if (!$usuario) {
            return ['status' => 'inexistente'];
        }
        if ($this->usuarioPossuiRegistrosVinculados($usuarioId)) {
            return ['status' => 'com_registros'];
        }

        $this->db->query('DELETE FROM usuario
                          WHERE user_id = :usuario_id
                            AND ' . $this->condicaoSemRegistrosVinculados('usuario.user_id'));
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->executa();

        if ($this->db->totalResultados() !== 1) {
            $this->db->query('SELECT user_id FROM usuario WHERE user_id = :usuario_id');
            $this->db->bind(':usuario_id', $usuarioId);
            if (!$this->db->resultado()) {
                return ['status' => 'inexistente'];
            }
            return $this->usuarioPossuiRegistrosVinculados($usuarioId)
                ? ['status' => 'com_registros']
                : ['status' => 'falha'];
        }

        return [
            'status' => 'excluido',
            'foto_perfil' => $usuario->foto_perfil,
        ];
    }

    private function usuarioPossuiRegistrosVinculados($usuarioId)
    {
        $this->db->query('SELECT NOT (' . $this->condicaoSemRegistrosVinculados('u.user_id') . ') AS possui
                          FROM usuario u
                          WHERE u.user_id = :usuario_id');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $resultado = $this->db->resultado();
        return $resultado && (bool) $resultado->possui;
    }

    private function condicaoSemRegistrosVinculados($usuarioIdSql)
    {
        return 'NOT EXISTS (SELECT 1 FROM demanda d WHERE d.user_id = ' . $usuarioIdSql . ')
                AND NOT EXISTS (SELECT 1 FROM sugestao s WHERE s.user_id = ' . $usuarioIdSql . ')
                AND NOT EXISTS (SELECT 1 FROM aviso a WHERE a.user_id = ' . $usuarioIdSql . ')
                AND NOT EXISTS (SELECT 1 FROM instituto i WHERE i.user_id = ' . $usuarioIdSql . ')
                AND NOT EXISTS (SELECT 1 FROM demanda_resposta r WHERE r.user_id = ' . $usuarioIdSql . ')
                AND NOT EXISTS (SELECT 1 FROM sugestao_resposta r WHERE r.user_id = ' . $usuarioIdSql . ')
                AND NOT EXISTS (SELECT 1 FROM historico_demanda h WHERE h.user_id = ' . $usuarioIdSql . ')';
    }

    private function filtroUsuariosGerenciaveis($busca, $perfil)
    {
        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $condicoes[] = '(u.user_nome LIKE :busca_nome OR u.user_email LIKE :busca_email)';
            $parametros[':busca_nome'] = '%' . $busca . '%';
            $parametros[':busca_email'] = '%' . $busca . '%';
        }

        if ($perfil === 'admin') {
            $condicoes[] = 'EXISTS (
                                SELECT 1 FROM usuario_permissao p
                                WHERE p.user_id = u.user_id AND p.permissao = \'admin\'
                            )';
        } elseif ($perfil === 'padrao') {
            $condicoes[] = 'NOT EXISTS (
                                SELECT 1 FROM usuario_permissao p
                                WHERE p.user_id = u.user_id AND p.permissao = \'admin\'
                            )';
        }

        return [
            'sql' => $condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '',
            'parametros' => $parametros,
        ];
    }

    private function vincularFiltroUsuariosGerenciaveis($parametros)
    {
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }
    }

    public function atualizar($atorId, $atorAdministrador, $usuarioId, $permissao, $conceder)
    {
        $atorId = (int) $atorId;
        $usuarioId = (int) $usuarioId;

        if ($usuarioId <= 0 || !$this->permissaoValida($permissao)
            || in_array(strtolower($permissao), ['admin', 'administrador'], true)) {
            return false;
        }

        if (!$atorAdministrador
            && (!$this->usuarioTem($atorId, self::GERENCIAR_PERMISSOES) || $atorId === $usuarioId)) {
            return false;
        }

        $this->db->query('SELECT user_id FROM usuario WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', $usuarioId);
        if (!$this->db->resultado()) {
            return false;
        }

        if ($conceder) {
            $this->db->query('INSERT INTO usuario_permissao
                                  (user_id, permissao, concedida_por)
                              VALUES (:usuario_id, :permissao, :concedida_por)
                              ON DUPLICATE KEY UPDATE
                                  concedida_por = VALUES(concedida_por),
                                  concedida_em = CURRENT_TIMESTAMP');
            $this->db->bind(':usuario_id', $usuarioId);
            $this->db->bind(':permissao', $permissao);
            $this->db->bind(':concedida_por', $atorAdministrador ? null : $atorId);
            return $this->db->executa();
        }

        $this->db->query('DELETE FROM usuario_permissao
                          WHERE user_id = :usuario_id AND permissao = :permissao');
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':permissao', $permissao);
        return $this->db->executa();
    }

    private function permissaoValida($permissao)
    {
        return is_string($permissao)
            && strlen($permissao) <= 64
            && preg_match('/\A[a-z][a-z0-9._-]*\z/', $permissao) === 1;
    }
}
