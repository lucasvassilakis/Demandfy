<?php
/*
 * CONTROLLER NOTIFICACOES
 * ------------------------------------------------------------------
 * /notificacoes                    -> todas
 * /notificacoes?filtro=nao-lidas   -> só as não lidas (aba "Não lidas")
 * /notificacoes/marcarTodas (POST) -> botão "Marcar todas como lidas"
 */
class Notificacoes extends Controller
{
    private $notificacaoModel;

    public function __construct()
    {
        $this->notificacaoModel = $this->model('Notificacao');
    }

    public function index()
    {
        $this->exigirLogin();

        $usuarioId = Sessao::usuarioId();
        $somenteNaoLidas = isset($_GET['filtro']) && $_GET['filtro'] === 'nao-lidas';

        $dados = [
            'titulo'        => 'Notificações',
            'notificacoes'  => $this->notificacaoModel->listar($usuarioId, $somenteNaoLidas),
            'naoLidas'      => $this->notificacaoModel->contarNaoLidas($usuarioId),
            'somenteNaoLidas' => $somenteNaoLidas,
        ];

        $this->view('notificacoes/index', $dados);
    }

    public function marcarTodas()
    {
        $this->exigirLogin();
        $this->exigirPost('notificacoes');
        $this->validarCsrf('notificacoes');

        $this->notificacaoModel->marcarTodasComoLidas(Sessao::usuarioId());
        Sessao::mensagem('geral', 'Todas as notificações foram marcadas como lidas.');
        Url::redirecionar('notificacoes');
    }
}
