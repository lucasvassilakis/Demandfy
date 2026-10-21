<?php
/*
 * CONTROLLER DEMANDAS
 * ------------------------------------------------------------------
 * Equivale ao controller Posts do framework2026 (listar, ver, cadastrar, deletar).
 *
 * URLs:
 *   /demandas              -> index()    lista as minhas demandas
 *   /demandas/ver/5        -> ver(5)     detalhe de uma demanda
 *   /demandas/criar        -> criar()    recebe o formulário de /sugestoes (POST)
 *   /demandas/excluir/5    -> excluir(5) apaga uma demanda (POST)
 *
 * Regra de segurança (igual à do Posts::deletar do framework): cada usuário só
 * enxerga e apaga as PRÓPRIAS demandas.
 */
class Demandas extends Controller
{
    private $demandaModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->demandaModel = $this->model('Demanda');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    // Lista todas as demandas do usuário logado.
    public function index()
    {
        $this->exigirLogin();

        $dados = [
            'titulo'   => 'Minhas Demandas',
            'demandas' => $this->demandaModel->listarPorUsuario(Sessao::usuarioId()),
        ];

        $this->view('demandas/index', $dados);
    }

    // Detalhe de uma demanda.
    public function ver($id)
    {
        $this->exigirLogin();

        $demanda = $this->demandaModel->lerDemandaPorId((int) $id);

        // Não existe ou é de outra pessoa: mesma resposta (404) para não revelar nada.
        if (!$demanda || (int) $demanda->usuario_id !== Sessao::usuarioId()) {
            $this->naoEncontrado();
            return;
        }

        $dados = [
            'titulo'  => 'Demanda #' . $demanda->id,
            'demanda' => $demanda,
        ];

        $this->view('demandas/ver', $dados);
    }

    // Recebe o formulário "Nova Demanda" da página /sugestoes.
    public function criar()
    {
        $this->exigirLogin();
        $this->exigirPost('sugestoes');
        $this->validarCsrf('sugestoes');

        $v = [
            'titulo'    => Entrada::post('titulo'),
            'descricao' => Entrada::post('descricao'),
            'categoria' => Entrada::post('categoria'),
        ];
        $erros = [];

        if ($v['titulo'] === '') {
            $erros['titulo'] = 'Preencha o título.';
        } elseif (mb_strlen($v['titulo']) < 5) {
            $erros['titulo'] = 'O título deve ter no mínimo 5 caracteres.';
        } elseif (mb_strlen($v['titulo']) > 120) {
            $erros['titulo'] = 'O título deve ter no máximo 120 caracteres.';
        }

        if ($v['descricao'] === '') {
            $erros['descricao'] = 'Descreva o problema.';
        } elseif (mb_strlen($v['descricao']) < 10) {
            $erros['descricao'] = 'A descrição deve ter no mínimo 10 caracteres.';
        } elseif (mb_strlen($v['descricao']) > 600) {
            $erros['descricao'] = 'A descrição deve ter no máximo 600 caracteres.';
        }

        // A categoria precisa ser uma das opções permitidas.
        if (!array_key_exists($v['categoria'], Demanda::CATEGORIAS)) {
            $erros['categoria'] = 'Selecione uma categoria.';
        }

        if (!empty($erros)) {
            // Guarda erros + o que foi digitado e volta para a página do formulário.
            Sessao::flashDados('form_demanda', ['erros' => $erros, 'valores' => $v]);
            Url::redirecionar('sugestoes');
        }

        $id = $this->demandaModel->armazenar([
            'usuario_id' => Sessao::usuarioId(),
            'titulo'     => $v['titulo'],
            'descricao'  => $v['descricao'],
            'categoria'  => $v['categoria'],
        ]);

        if (!$id) {
            Sessao::mensagem('geral', 'Não foi possível enviar a demanda. Tente novamente.', 'erro');
            Url::redirecionar('sugestoes');
        }

        $this->notificacaoModel->criar(
            Sessao::usuarioId(),
            'demanda',
            'Demanda enviada',
            'Sua demanda #' . $id . ' "' . $v['titulo'] . '" foi enviada ao Grêmio.'
        );

        Sessao::mensagem('geral', 'Demanda enviada com sucesso!');
        Url::redirecionar('demandas');
    }

    // Apaga uma demanda (somente do próprio usuário e somente por POST).
    public function excluir($id)
    {
        $this->exigirLogin();
        $this->exigirPost('demandas');
        $this->validarCsrf('demandas');

        $demanda = $this->demandaModel->lerDemandaPorId((int) $id);

        if (!$demanda || (int) $demanda->usuario_id !== Sessao::usuarioId()) {
            Sessao::mensagem('geral', 'Você não tem autorização para excluir esta demanda.', 'erro');
            Url::redirecionar('demandas');
        }

        if ($this->demandaModel->excluir((int) $id)) {
            Sessao::mensagem('geral', 'Demanda excluída com sucesso!');
        } else {
            Sessao::mensagem('geral', 'Não foi possível excluir a demanda.', 'erro');
        }
        Url::redirecionar('demandas');
    }
}
