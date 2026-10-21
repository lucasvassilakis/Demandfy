<?php
/*
 * CONTROLLER SUGESTOES (página "RDD - Sugestões")
 * ------------------------------------------------------------------
 * A página /sugestoes tem DOIS formulários:
 *   - Nova Demanda   -> envia para /demandas/criar    (Controller Demandas)
 *   - Nova Sugestão  -> envia para /sugestoes/criar   (este controller)
 *
 * Se algum formulário tiver erro, o controller que recebeu o POST guarda os erros
 * na sessão (Sessao::flashDados) e redireciona de volta para /sugestoes, onde o
 * método index() recupera esses dados e mostra os erros ao lado dos campos.
 */
class Sugestoes extends Controller
{
    private $sugestaoModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->sugestaoModel = $this->model('Sugestao');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    // Exibe a página com os dois formulários e as últimas sugestões enviadas.
    public function index()
    {
        $this->exigirLogin();

        $vazio = ['erros' => [], 'valores' => ['titulo' => '', 'descricao' => '', 'categoria' => '']];

        // flashDados lê e apaga: o erro aparece uma vez só.
        $formDemanda = Sessao::flashDados('form_demanda');
        $formSugestao = Sessao::flashDados('form_sugestao');

        $dados = [
            'titulo'        => 'RDD - Sugestões',
            'formDemanda'   => $formDemanda ? $formDemanda : $vazio,
            'formSugestao'  => $formSugestao ? $formSugestao : $vazio,
            'minhasSugestoes' => $this->sugestaoModel->listarPorUsuario(Sessao::usuarioId(), 5),
        ];

        $this->view('sugestoes/index', $dados);
    }

    // Recebe o formulário "Nova Sugestão".
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
            $erros['descricao'] = 'Descreva a sua sugestão.';
        } elseif (mb_strlen($v['descricao']) < 10) {
            $erros['descricao'] = 'A descrição deve ter no mínimo 10 caracteres.';
        } elseif (mb_strlen($v['descricao']) > 600) {
            $erros['descricao'] = 'A descrição deve ter no máximo 600 caracteres.';
        }

        if (!array_key_exists($v['categoria'], Sugestao::CATEGORIAS)) {
            $erros['categoria'] = 'Selecione uma categoria.';
        }

        if (!empty($erros)) {
            Sessao::flashDados('form_sugestao', ['erros' => $erros, 'valores' => $v]);
            Url::redirecionar('sugestoes');
        }

        $id = $this->sugestaoModel->armazenar([
            'usuario_id' => Sessao::usuarioId(),
            'titulo'     => $v['titulo'],
            'descricao'  => $v['descricao'],
            'categoria'  => $v['categoria'],
        ]);

        if (!$id) {
            Sessao::mensagem('geral', 'Não foi possível enviar a sugestão. Tente novamente.', 'erro');
            Url::redirecionar('sugestoes');
        }

        $this->notificacaoModel->criar(
            Sessao::usuarioId(),
            'sugestao',
            'Sugestão enviada',
            'Sua sugestão "' . $v['titulo'] . '" foi registrada. Obrigado por participar!'
        );

        Sessao::mensagem('geral', 'Sugestão enviada com sucesso!');
        Url::redirecionar('sugestoes');
    }
}
