<?php
/*
 * ROTEADOR
 * ------------------------------------------------------------------
 * Converte a URL em  /controller/metodo/parametro1/parametro2...
 *   /demandas/ver/5      -> Demandas::ver(5)
 *   /avisos              -> Avisos::index()
 *   /                    -> Paginas::index()
 *
 * MUDANÇAS em relação à versão anterior:
 *  - ATALHOS: URLs curtas que já existiam (/login, /cadastro, /perfil...) apontam
 *    para os métodos do controller Usuarios.
 *  - Só executa métodos PÚBLICOS do controller e que não sejam da classe base
 *    (antes era possível chamar /demandas/model/... pela URL).
 *  - Se faltar/sobrar parâmetro na URL, mostra a página 404 (antes dava erro fatal).
 */
class Rota
{
    private $controlador = 'Paginas';
    private $metodo = 'index';
    private $parametros = [];

    // URL curta => [Controller, método]
    private $atalhos = [
        'login'         => ['Usuarios', 'login'],
        'cadastro'      => ['Usuarios', 'cadastrar'],
        'sair'          => ['Usuarios', 'sair'],
        'perfil'        => ['Usuarios', 'perfil'],
        'configuracoes' => ['Usuarios', 'configuracoes'],
    ];

    public function __construct()
    {
        $url = $this->url();
        $primeiro = isset($url[0]) ? strtolower($url[0]) : '';

        if (count($url) === 1 && isset($this->atalhos[$primeiro])) {
            // Caso 1: a URL é um atalho (ex.: /login).
            list($this->controlador, $this->metodo) = $this->atalhos[$primeiro];
            $url = [];
        } elseif ($primeiro !== '' && preg_match('/^[a-z]+$/', $primeiro)
            && file_exists(APP . '/Controllers/' . ucfirst($primeiro) . '.php')) {
            // Caso 2: o primeiro pedaço da URL é o nome de um controller que existe.
            $this->controlador = ucfirst($primeiro);
            array_shift($url);
        }
        // Caso 3: nenhum dos anteriores -> fica o controller padrão (Paginas).

        require_once APP . '/Controllers/' . $this->controlador . '.php';
        $this->controlador = new $this->controlador;

        // O próximo pedaço da URL é o método (se existir e for permitido).
        if (isset($url[0]) && $this->metodoPermitido($this->controlador, $url[0])) {
            $this->metodo = $url[0];
            array_shift($url);
        }

        // O que sobrou na URL são os parâmetros do método.
        $this->parametros = array_values($url);

        // Se o controller não tem o método (ex.: /usuarios sem "index"), mostra 404.
        if (!method_exists($this->controlador, $this->metodo)) {
            $this->naoEncontrado();
            return;
        }

        // Confere se a quantidade de parâmetros bate com o método; senão, 404.
        $reflexao = new ReflectionMethod($this->controlador, $this->metodo);
        $total = count($this->parametros);
        if ($total > $reflexao->getNumberOfParameters() || $total < $reflexao->getNumberOfRequiredParameters()) {
            $this->naoEncontrado();
            return;
        }

        call_user_func_array([$this->controlador, $this->metodo], $this->parametros);
    }

    // Verifica se o método pode ser chamado pela URL.
    private function metodoPermitido($controlador, $nome)
    {
        if (strpos($nome, '__') === 0) {
            return false; // __construct etc.
        }
        if (!method_exists($controlador, $nome)) {
            return false;
        }
        if (method_exists('Controller', $nome)) {
            return false; // métodos da classe base (model, view, exigirLogin...)
        }
        $reflexao = new ReflectionMethod($controlador, $nome);
        return $reflexao->isPublic();
    }

    private function naoEncontrado()
    {
        require_once APP . '/Controllers/Paginas.php';
        $paginas = new Paginas();
        $paginas->erro404();
    }

    // Lê ?url=... (enviado pelo .htaccess) e devolve array: ['demandas','ver','5']
    private function url()
    {
        $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);

        if (empty($url)) {
            return [];
        }

        $url = trim($url, '/');

        return $url === '' ? [] : explode('/', $url);
    }
}
