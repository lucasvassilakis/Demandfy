<?php
/*
 * CONTROLLER PAGINAS
 * ------------------------------------------------------------------
 * Páginas gerais: início (painel do usuário), contato e erro 404.
 * As demais páginas agora têm controller próprio (Demandas, Sugestoes, Avisos,
 * Calendario, Notificacoes, Usuarios).
 */
class Paginas extends Controller
{
    private $demandaModel;
    private $avisoModel;

    public function __construct()
    {
        $this->demandaModel = $this->model('Demanda');
        $this->avisoModel = $this->model('Aviso');
    }

    // Página inicial (painel). Agora mostra dados REAIS do usuário logado.
    public function index()
    {
        $this->exigirLogin();

        $usuarioId = Sessao::usuarioId();

        $dados = [
            'titulo'    => 'Início',
            // contadores do painel "Minhas Demandas"
            'resumo'    => $this->demandaModel->resumo($usuarioId),
            // as 2 demandas mais recentes
            'recentes'  => $this->demandaModel->listarPorUsuario($usuarioId, 2),
            // os 3 avisos mais recentes
            'avisos'    => $this->avisoModel->listar(null, 3),
        ];

        $this->view('paginas/inicio', $dados);
    }

    public function contato()
    {
        $dados = [
            'titulo'    => 'Contato',
            'descricao' => 'Página de Contatos',
        ];

        $this->view('paginas/contato', $dados);
    }

    // Página não encontrada (também chamada pela Rota).
    public function erro404()
    {
        http_response_code(404);

        $dados = ['titulo' => 'Página não encontrada'];

        // Quem está logado vê o erro dentro do layout; visitante vê página simples.
        if (Sessao::estaLogado()) {
            $this->view('erros/404', $dados);
        } else {
            $this->viewSolo('erros/404-simples', $dados);
        }
    }
}
