<?php
/*
 * MODEL USUARIO
 * ------------------------------------------------------------------
 * Toda consulta ao banco relacionada a usuários fica aqui.
 * Baseado no Model Usuario do framework2026, com estas mudanças:
 *  - login por E-MAIL ou TELEFONE (a tela de login diz "E-mail/celular");
 *  - campo telefone e dados de perfil (instituição, curso, ano, situação, matrícula);
 *  - métodos de atualizar perfil e senha;
 *  - checagem de e-mail/telefone ignora o próprio usuário (para editar o perfil).
 */
class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Já existe outro usuário com este e-mail? ($ignorarId = o próprio usuário ao editar)
    public function emailExiste($email, $ignorarId = 0)
    {
        $this->db->query('SELECT user_id FROM usuario WHERE user_email = :email AND user_id <> :id');
        $this->db->bind(':email', $email);
        $this->db->bind(':id', (int) $ignorarId);
        return (bool) $this->db->resultado();
    }

    // Já existe outro usuário com este telefone? (só dígitos)
    public function telefoneExiste($telefone, $ignorarId = 0)
    {
        $this->db->query('SELECT user_id FROM usuario WHERE user_telefone = :telefone AND user_id <> :id');
        $this->db->bind(':telefone', $telefone);
        $this->db->bind(':id', (int) $ignorarId);
        return (bool) $this->db->resultado();
    }

    public function contarTodos()
    {
        $this->db->query('SELECT COUNT(*) AS total FROM usuario');
        return (int) $this->db->resultado()->total;
    }

    // Cadastra um usuário. $dados['senha'] já deve ser o HASH. Retorna o novo ID ou false.
    public function armazenar($dados)
    {
        $this->db->query('INSERT INTO usuario
                              (user_nome, user_email, user_telefone, user_senha,
                               user_matricula, user_status, user_curso, user_ano_ingresso, perf_id)
                          VALUES
                              (:nome, :email, :telefone, :senha,
                               NULL, \'ativo\', NULL, NULL,
                               (SELECT perf_id FROM perfil WHERE perf_nivel = 0 LIMIT 1))');
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':email', $dados['email']);
        $this->db->bind(':telefone', $dados['telefone']);
        $this->db->bind(':senha', $dados['senha']);

        if ($this->db->executa()) {
            return (int) $this->db->ultimoIdInserido();
        }
        return false;
    }

    // Confere login. $identificador pode ser e-mail ou telefone. Retorna o usuário ou false.
    public function checarLogin($identificador, $senha)
    {
        $identificador = trim((string) $identificador);

        if (strpos($identificador, '@') !== false) {
            $normalizado = Checa::emailPadrao($identificador);
            $this->db->query('SELECT user_id AS id, user_nome AS nome, user_email AS email, user_telefone AS telefone, user_senha AS senha, user_tema AS tema
                              FROM usuario WHERE user_email = :ident');
            $this->db->bind(':ident', $normalizado);
        } else {
            // Se não tem "@", trata como telefone (compara só os números).
            $this->db->query('SELECT user_id AS id, user_nome AS nome, user_email AS email, user_telefone AS telefone, user_senha AS senha, user_tema AS tema
                              FROM usuario WHERE user_telefone = :ident');
            $this->db->bind(':ident', Checa::soDigitos($identificador));
        }

        $usuario = $this->db->resultado();

        // password_verify compara a senha digitada com o hash guardado.
        if ($usuario && password_verify($senha, $usuario->senha)) {
            // Se o algoritmo de hash do PHP evoluiu, atualiza o hash guardado.
            if (password_needs_rehash($usuario->senha, PASSWORD_DEFAULT)) {
                $this->atualizarSenha($usuario->id, password_hash($senha, PASSWORD_DEFAULT));
            }
            return $usuario;
        }

        return false;
    }

    public function lerUsuarioPorId($id)
    {
        $this->db->query('SELECT u.user_id AS id, u.user_nome AS nome, u.user_email AS email,
                                 u.user_telefone AS telefone, u.user_senha AS senha,
                                 u.user_matricula AS matricula, u.user_status AS situacao,
                                 u.user_curso AS curso, u.user_ano_ingresso AS ano_ingresso,
                                 u.user_funcao_gremio AS funcao_gremio,
                                 u.foto_perfil AS foto_perfil,
                                 u.user_notificacao_email AS notificacao_email,
                                 u.user_notificacao_site AS notificacao_site,
                                 u.user_tema AS tema,
                                 (SELECT i.inst_nome FROM instituto i
                                  WHERE i.user_id = u.user_id ORDER BY i.inst_id DESC LIMIT 1) AS instituicao
                          FROM usuario u WHERE u.user_id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->resultado();
    }

    public function lerUsuarioPorEmail($email)
    {
        $this->db->query('SELECT user_id AS id, user_nome AS nome, user_email AS email, user_telefone AS telefone, user_senha AS senha
                          FROM usuario WHERE user_email = :email');
        $this->db->bind(':email', Checa::emailPadrao((string) $email));
        return $this->db->resultado();
    }

    public function buscarPorEmailOuTelefone($identificador)
    {
        $identificador = trim((string) $identificador);

        if ($identificador === '') {
            return false;
        }

        if (strpos($identificador, '@') !== false) {
            return $this->lerUsuarioPorEmail($identificador);
        }

        $this->db->query('SELECT user_id AS id, user_nome AS nome, user_email AS email, user_telefone AS telefone, user_senha AS senha
                          FROM usuario WHERE user_telefone = :telefone');
        $this->db->bind(':telefone', Checa::soDigitos($identificador));
        return $this->db->resultado();
    }

    public function armazenarTokenRedefinicao($usuarioId, $tokenHash, $expiraEm)
    {
        $this->db->query('INSERT INTO recuperacao_senha (user_id, token_hash, expira_em, criado_em, usado_em)
                          VALUES (:usuario_id, :token_hash, :expira_em, CURRENT_TIMESTAMP, NULL)
                          ON DUPLICATE KEY UPDATE token_hash = VALUES(token_hash),
                                                  expira_em = VALUES(expira_em),
                                                  criado_em = CURRENT_TIMESTAMP,
                                                  usado_em = NULL');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        $this->db->bind(':token_hash', $tokenHash);
        $this->db->bind(':expira_em', $expiraEm);
        return $this->db->executa();
    }

    public function tokenRedefinicaoValido($tokenHash)
    {
        $this->db->query('SELECT user_id FROM recuperacao_senha
                          WHERE token_hash = :token_hash
                            AND expira_em > CURRENT_TIMESTAMP
                            AND usado_em IS NULL');
        $this->db->bind(':token_hash', $tokenHash);
        return (bool) $this->db->resultado();
    }

    public function invalidarTokenRedefinicao($usuarioId)
    {
        $this->db->query('DELETE FROM recuperacao_senha WHERE user_id = :usuario_id');
        $this->db->bind(':usuario_id', (int) $usuarioId);
        return $this->db->executa();
    }

    public function redefinirSenhaComToken($tokenHash, $senhaHash)
    {
        $this->db->query('UPDATE usuario u
                          INNER JOIN recuperacao_senha r ON r.user_id = u.user_id
                          SET u.user_senha = :senha, r.usado_em = CURRENT_TIMESTAMP
                          WHERE r.token_hash = :token_hash
                            AND r.expira_em > CURRENT_TIMESTAMP
                            AND r.usado_em IS NULL');
        $this->db->bind(':senha', $senhaHash);
        $this->db->bind(':token_hash', $tokenHash);
        if (!$this->db->executa()) {
            return false;
        }
        return $this->db->totalResultados() > 0;
    }

    // Atualiza os dados da tela de Perfil.
    public function atualizarPerfil($id, $dados)
    {
        if ($dados['instituicao'] !== '') {
            $this->db->query('SELECT inst_id FROM instituto
                              WHERE inst_nome = :nome AND user_id <> :id');
            $this->db->bind(':nome', $dados['instituicao']);
            $this->db->bind(':id', (int) $id);
            if ($this->db->resultado()) {
                return false;
            }
        }

        $this->db->query('UPDATE usuario SET
                              user_nome = :nome, user_email = :email, user_telefone = :telefone,
                              user_curso = :curso, user_ano_ingresso = :ano,
                              user_matricula = :matricula, user_funcao_gremio = :funcao_gremio,
                              foto_perfil = :foto_perfil
                          WHERE user_id = :id');
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':email', $dados['email']);
        $this->db->bind(':telefone', $dados['telefone']);
        $this->db->bind(':curso', $dados['curso'] === '' ? null : $dados['curso']);
        $this->db->bind(':ano', $dados['ano_ingresso']);
        $this->db->bind(':matricula', $dados['matricula'] === '' ? null : $dados['matricula']);
        $this->db->bind(':funcao_gremio', $dados['funcao_gremio'] === '' ? null : $dados['funcao_gremio']);
        $this->db->bind(':foto_perfil', $dados['foto_perfil'] === '' ? null : $dados['foto_perfil']);
        $this->db->bind(':id', (int) $id);
        if (!$this->db->executa()) {
            return false;
        }

        $this->db->query('SELECT inst_id FROM instituto
                          WHERE user_id = :id ORDER BY inst_id DESC LIMIT 1');
        $this->db->bind(':id', (int) $id);
        $instituto = $this->db->resultado();

        if ($dados['instituicao'] === '') {
            $this->db->query('DELETE FROM instituto WHERE user_id = :id');
            $this->db->bind(':id', (int) $id);
            return $this->db->executa();
        }

        if ($instituto) {
            $this->db->query('UPDATE instituto SET inst_nome = :nome
                              WHERE inst_id = :inst_id AND user_id = :id');
            $this->db->bind(':inst_id', (int) $instituto->inst_id);
            $this->db->bind(':id', (int) $id);
        } else {
            $this->db->query('INSERT INTO instituto (inst_nome, user_id)
                              VALUES (:nome, :id)');
            $this->db->bind(':id', (int) $id);
        }
        $this->db->bind(':nome', $dados['instituicao']);
        return $this->db->executa();
    }

    // Troca a senha ($hash = resultado de password_hash).
    public function atualizarSenha($id, $hash)
    {
        $this->db->query('UPDATE usuario SET user_senha = :senha WHERE user_id = :id');
        $this->db->bind(':senha', $hash);
        $this->db->bind(':id', (int) $id);
        return $this->db->executa();
    }

    public function atualizarConfiguracoes($id, $preferencias, $senhaHash = null)
    {
        $sql = 'UPDATE usuario SET
                    user_notificacao_email = :notificacao_email,
                    user_notificacao_site = :notificacao_site,
                    user_tema = :tema';
        if ($senhaHash !== null) {
            $sql .= ', user_senha = :senha';
        }
        $sql .= ' WHERE user_id = :id';

        $this->db->query($sql);
        $this->db->bind(':notificacao_email', (int) $preferencias['notificacao_email']);
        $this->db->bind(':notificacao_site', (int) $preferencias['notificacao_site']);
        $this->db->bind(':tema', $preferencias['tema']);
        if ($senhaHash !== null) {
            $this->db->bind(':senha', $senhaHash);
        }
        $this->db->bind(':id', (int) $id);
        return $this->db->executa();
    }
}
