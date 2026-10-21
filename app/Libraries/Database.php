<?php
/*
 * CLASSE DATABASE (acesso ao MySQL via PDO)
 * ------------------------------------------------------------------
 * Fluxo de uso nos Models:
 *   $this->db->query("SELECT ... WHERE id = :id");   // prepara a SQL
 *   $this->db->bind(':id', $id);                      // vincula valores (evita SQL Injection)
 *   $this->db->resultado();                           // 1 linha  (objeto)  | resultados() = várias linhas
 *
 * MUDANÇAS:
 *  - Usa as constantes DB_* de configuracao.php (antes os dados estavam fixos aqui).
 *  - resultados() voltou a executar a consulta (a versão do Demandfy tinha perdido o
 *    $this->executa(), então listagens não funcionavam).
 *  - Charset utf8mb4 (acentos e emojis) e fuso horário do MySQL igual ao do PHP.
 *  - Mensagem de erro de conexão útil, sem expor a senha.
 */
class Database
{
    private $dbh;   // conexão PDO
    private $stmt;  // consulta preparada atual

    public function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $opcoes = [
            // Reaproveita a conexão entre requisições (mais rápido).
            PDO::ATTR_PERSISTENT => true,
            // Erros do banco viram exceções (PDOException) em vez de falhas silenciosas.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Define o fuso horário do MySQL igual ao do PHP (ex.: -04:00), assim NOW() e
            // as datas exibidas ("há 5 minutos") ficam coerentes.
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . date('P') . "'",
        ];

        try {
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        } catch (PDOException $erro) {
            // O detalhe técnico vai para o log do PHP; o usuário vê só uma mensagem genérica.
            error_log('Demandfy - erro de conexão: ' . $erro->getMessage());
            http_response_code(500);
            die('Não foi possível conectar ao banco de dados. Verifique as configurações em app/configuracao.php e se o MySQL está ligado.');
        }
    }

    // Prepara a consulta SQL (ainda não executa).
    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Vincula um valor a um parâmetro (:nome) detectando o tipo automaticamente.
    public function bind($parametro, $valor, $tipo = null)
    {
        if (is_null($tipo)) {
            switch (true) {
                case is_int($valor):
                    $tipo = PDO::PARAM_INT;
                    break;
                case is_bool($valor):
                    $tipo = PDO::PARAM_BOOL;
                    break;
                case is_null($valor):
                    $tipo = PDO::PARAM_NULL;
                    break;
                default:
                    $tipo = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($parametro, $valor, $tipo);
    }

    // Executa a consulta preparada (INSERT, UPDATE, DELETE...). Retorna true/false.
    public function executa()
    {
        return $this->stmt->execute();
    }

    // Executa e devolve UMA linha como objeto (ou false se não existir).
    public function resultado()
    {
        $this->executa();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Executa e devolve VÁRIAS linhas (array de objetos).
    public function resultados()
    {
        $this->executa();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Quantidade de linhas afetadas pela última instrução.
    public function totalResultados()
    {
        return $this->stmt->rowCount();
    }

    // ID gerado pelo último INSERT.
    public function ultimoIdInserido()
    {
        return $this->dbh->lastInsertId();
    }
}
