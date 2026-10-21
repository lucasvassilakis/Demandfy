<?php
/*
 * CONTROLLER BASE
 * ------------------------------------------------------------------
 * Todos os controllers (Usuarios, Demandas...) herdam desta classe.
 *
 * MUDANÇAS:
 *  - Caminhos usam a constante APP (antes "../app/..." dependia da pasta atual).
 *  - view() agora monta o LAYOUT completo (topo + sidebar + página + rodapé).
 *  - viewSolo() para páginas sem sidebar (login e cadastro).
 *  - exigirLogin(), exigirPost() e validarCsrf(): proteções reaproveitadas pelos controllers.
 */
class Controller
{
    // Carrega e devolve um Model. Ex.: $this->model('Usuario')
    public function model($model)
    {
        require_once APP . '/Models/' . $model . '.php';
        return new $model;
    }

    // Exibe uma página COM layout (sidebar). $dados fica disponível dentro da view.
    public function view($view, $dados = [])
    {
        $arquivo = APP . '/Views/' . $view . '.php';

        if (!file_exists($arquivo)) {
            die('O arquivo de view não existe: ' . htmlspecialchars($view));
        }

        require APP . '/Views/layout/topo.php';
        require $arquivo;
        require APP . '/Views/layout/rodape.php';
    }

    // Exibe uma página SEM layout (a própria view já é um HTML completo: login e cadastro).
    public function viewSolo($view, $dados = [])
    {
        $arquivo = APP . '/Views/' . $view . '.php';

        if (!file_exists($arquivo)) {
            die('O arquivo de view não existe: ' . htmlspecialchars($view));
        }

        require $arquivo;
    }

    // Se o usuário NÃO estiver logado, manda para o login e interrompe a execução.
    protected function exigirLogin()
    {
        if (!Sessao::estaLogado()) {
            Sessao::mensagem('geral', 'Faça login para continuar.', 'erro');
            Url::redirecionar('login');
        }
    }

    // Mostra a página de erro 404 (usada quando o registro não existe ou não é do usuário).
    protected function naoEncontrado()
    {
        require_once APP . '/Controllers/Paginas.php';
        $paginas = new Paginas();
        $paginas->erro404();
    }

    // Ações que alteram dados só podem ser feitas por POST (formulário), nunca por link.
    protected function exigirPost($destino)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Url::redirecionar($destino);
        }
    }

    // Confere o token CSRF enviado no formulário. Se for inválido, volta com erro.
    protected function validarCsrf($destino)
    {
        if (!Csrf::validar()) {
            Sessao::mensagem('geral', 'Sessão expirada ou formulário inválido. Tente novamente.', 'erro');
            Url::redirecionar($destino);
        }
    }
}
