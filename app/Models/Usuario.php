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
        $this->db->query('SELECT id FROM usuarios WHERE email = :email AND id <> :id');
        $this->db->bind(':email', $email);
        $this->db->bind(':id', (int) $ignorarId);
        return (bool) $this->db->resultado();
    }

    // Já existe outro usuário com este telefone? (só dígitos)
    public function telefoneExiste($telefone, $ignorarId = 0)
    {
        $this->db->query('SELECT id FROM usuarios WHERE telefone = :telefone AND id <> :id');
        $this->db->bind(':telefone', $telefone);
        $this->db->bind(':id', (int) $ignorarId);
        return (bool) $this->db->resultado();
    }

    // Cadastra um usuário. $dados['senha'] já deve ser o HASH. Retorna o novo ID ou false.
    public function armazenar($dados)
    {
        $this->db->query('INSERT INTO usuarios (nome, email, telefone, senha)
                          VALUES (:nome, :email, :telefone, :senha)');
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
        if (strpos($identificador, '@') !== false) {
            $this->db->query('SELECT * FROM usuarios WHERE email = :ident');
            $this->db->bind(':ident', $identificador);
        } else {
            // Se não tem "@", trata como telefone (compara só os números).
            $this->db->query('SELECT * FROM usuarios WHERE telefone = :ident');
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
        $this->db->query('SELECT * FROM usuarios WHERE id = :id');
        $this->db->bind(':id', (int) $id);
        return $this->db->resultado();
    }

    // Atualiza os dados da tela de Perfil.
    public function atualizarPerfil($id, $dados)
    {
        $this->db->query('UPDATE usuarios SET
                            nome = :nome, email = :email, telefone = :telefone,
                            instituicao = :instituicao, curso = :curso,
                            ano_ingresso = :ano, situacao = :situacao, matricula = :matricula,
                            foto_perfil = :foto_perfil
                          WHERE id = :id');
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':email', $dados['email']);
        $this->db->bind(':telefone', $dados['telefone']);
        $this->db->bind(':instituicao', $dados['instituicao'] === '' ? null : $dados['instituicao']);
        $this->db->bind(':curso', $dados['curso'] === '' ? null : $dados['curso']);
        $this->db->bind(':ano', $dados['ano_ingresso']); // int ou null
        $this->db->bind(':situacao', $dados['situacao'] === '' ? null : $dados['situacao']);
        $this->db->bind(':matricula', $dados['matricula'] === '' ? null : $dados['matricula']);
        $this->db->bind(':foto_perfil', $dados['foto_perfil'] === '' ? null : $dados['foto_perfil']);
        $this->db->bind(':id', (int) $id);
        return $this->db->executa();
    }

    // Troca a senha ($hash = resultado de password_hash).
    public function atualizarSenha($id, $hash)
    {
        $this->db->query('UPDATE usuarios SET senha = :senha WHERE id = :id');
        $this->db->bind(':senha', $hash);
        $this->db->bind(':id', (int) $id);
        return $this->db->executa();
    }
}
