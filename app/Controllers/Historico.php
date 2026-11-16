<?php
class Historico extends Controller
{
    const ITENS_POR_PAGINA = 4;

    private $demandaModel;
    private $sugestaoModel;
    private $respostaModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->demandaModel = $this->model('Demanda');
        $this->sugestaoModel = $this->model('Sugestao');
        $this->respostaModel = $this->model('RespostaHistorico');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    public function index()
    {
        $this->exigirLogin();

        $tipo = isset($_GET['tipo']) && $_GET['tipo'] === 'sugestoes' ? 'sugestoes' : 'demandas';
        $ehAdministrador = Sessao::ehAdministrador();
        $ehModerador = !$ehAdministrador && Sessao::ehModerador();
        $verTodas = $ehAdministrador || $ehModerador;
        $usuarioId = Sessao::usuarioId();
        $model = $tipo === 'demandas' ? $this->demandaModel : $this->sugestaoModel;
        $statusOptions = $this->statusOptions($tipo);
        $filtros = [
            'criado_por_mim' => false,
            'status' => '',
            'data_inicio' => '',
            'data_fim' => '',
        ];
        $erroFiltro = '';
        if ($verTodas) {
            if (isset($_GET['criado_por_mim']) && is_scalar($_GET['criado_por_mim'])) {
                if ((string) $_GET['criado_por_mim'] === '1') {
                    $filtros['criado_por_mim'] = true;
                } elseif ((string) $_GET['criado_por_mim'] !== '') {
                    $erroFiltro = 'O filtro de autoria é inválido.';
                }
            }

            if (isset($_GET['status']) && is_scalar($_GET['status']) && (string) $_GET['status'] !== '') {
                $status = (string) $_GET['status'];
                if (array_key_exists($status, $statusOptions)) {
                    $filtros['status'] = $status;
                } else {
                    $erroFiltro = 'O status selecionado não é válido para este tipo de registro.';
                }
            }

            foreach (['data_inicio', 'data_fim'] as $campoData) {
                if (!isset($_GET[$campoData]) || $_GET[$campoData] === '') {
                    continue;
                }
                $data = $this->normalizarDataFiltro($_GET[$campoData]);
                if ($data === false) {
                    $erroFiltro = 'Informe datas válidas para aplicar o filtro.';
                    continue;
                }
                $filtros[$campoData] = $data;
            }

            if ($filtros['data_inicio'] !== '' && $filtros['data_fim'] !== ''
                && $filtros['data_inicio'] > $filtros['data_fim']) {
                $erroFiltro = 'A data inicial não pode ser posterior à data final.';
                $filtros['data_inicio'] = '';
                $filtros['data_fim'] = '';
            }
        }

        $filtrosModelo = [
            'usuario_id' => $filtros['criado_por_mim'] ? $usuarioId : 0,
            'status' => $filtros['status'],
            'data_inicio' => $filtros['data_inicio'],
            'data_fim' => $filtros['data_fim'],
        ];
        $totalDemandas = $verTodas
            ? $this->demandaModel->contarTodas()
            : $this->demandaModel->contarPorUsuario($usuarioId);
        $totalSugestoes = $verTodas
            ? $this->sugestaoModel->contarTodas()
            : $this->sugestaoModel->contarPorUsuario($usuarioId);
        $total = $verTodas
            ? $model->contarTodas($filtrosModelo)
            : ($tipo === 'demandas' ? $totalDemandas : $totalSugestoes);
        $ordemIds = $verTodas
            ? []
            : ($tipo === 'demandas'
                ? $this->demandaModel->listarIdsHistoricoPorUsuario($usuarioId)
                : $this->sugestaoModel->listarIdsHistoricoPorUsuario($usuarioId));
        $totalPaginas = max(1, (int) ceil($total / self::ITENS_POR_PAGINA));
        $paginaValida = isset($_GET['pagina']) && is_scalar($_GET['pagina'])
            ? filter_var($_GET['pagina'], FILTER_VALIDATE_INT)
            : false;
        $pagina = $paginaValida !== false
            ? (int) $_GET['pagina']
            : 1;
        $pagina = min(max($pagina, 1), $totalPaginas);
        $destacarDemandaId = isset($_GET['demanda']) && is_scalar($_GET['demanda'])
            ? filter_var($_GET['demanda'], FILTER_VALIDATE_INT)
            : false;
        if (!$verTodas && $tipo === 'demandas' && $destacarDemandaId !== false && $destacarDemandaId > 0) {
            $indiceDemanda = array_search($destacarDemandaId, $ordemIds, true);
            if ($indiceDemanda === false) {
                $destacarDemandaId = 0;
            } else {
                $pagina = intdiv($indiceDemanda, self::ITENS_POR_PAGINA) + 1;
            }
        } else {
            $destacarDemandaId = 0;
        }
        if ($paginaValida !== false && (int) $_GET['pagina'] !== $pagina && $destacarDemandaId === 0) {
            $parametrosUrl = $verTodas ? $this->parametrosConsulta($tipo, $filtros) : ['tipo' => $tipo];
            if ($pagina > 1) {
                $parametrosUrl['pagina'] = $pagina;
            }
            Url::redirecionar('historico?' . http_build_query($parametrosUrl));
        }
        $deslocamento = ($pagina - 1) * self::ITENS_POR_PAGINA;

        $itens = $verTodas
            ? $model->listarTodasHistorico(self::ITENS_POR_PAGINA, $deslocamento, $filtrosModelo)
            : ($tipo === 'demandas'
                ? $this->demandaModel->listarHistoricoPorUsuario($usuarioId, self::ITENS_POR_PAGINA, $deslocamento)
                : $this->sugestaoModel->listarHistoricoPorUsuario($usuarioId, self::ITENS_POR_PAGINA, $deslocamento));

        $podeEditarOutros = $ehAdministrador;
        if ($ehModerador) {
            $permissaoEdicao = $tipo === 'demandas'
                ? Permissao::EDITAR_DEMANDAS
                : Permissao::EDITAR_SUGESTOES;
            $podeEditarOutros = Sessao::temPermissao($permissaoEdicao);
        }
        $temAcoes = !$verTodas;
        $tipoItem = $tipo === 'demandas' ? 'demanda' : 'sugestao';
        foreach ($itens as $item) {
            $this->formatarItem($item, $tipo);
            $item->proprio = (int) $item->usuario_id === $usuarioId;
            $item->podeEditar = !$verTodas || $item->proprio || $podeEditarOutros;
            $item->podeAlterarStatus = $podeEditarOutros;
            $item->podeResponder = $podeEditarOutros;
            $item->statusEdicao = $this->statusParaEdicao($tipoItem, $item->status);
            $item->respostas = $this->respostaModel->listar($tipoItem, $item->id);
            $item->totalRespostas = count($item->respostas);
            if ($item->podeEditar) {
                $temAcoes = true;
            }
            if ($item->podeResponder || $item->totalRespostas > 0) {
                $temAcoes = true;
            }
            if ($tipo === 'demandas') {
                $categoria = array_search($item->categoria, Demanda::CATEGORIAS, true);
                $item->categoriaChave = $categoria === false ? '' : $categoria;
            } else {
                $item->categoriaChave = $item->categoria;
            }
        }

        $this->view('historico/index', [
            'titulo' => 'Histórico',
            'tipo' => $tipo,
            'verTodas' => $verTodas,
            'temAcoes' => $temAcoes,
            'itens' => $itens,
            'total' => $total,
            'totalDemandas' => $totalDemandas,
            'totalSugestoes' => $totalSugestoes,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'deslocamento' => $deslocamento,
            'ordemIds' => $ordemIds,
            'destacarDemandaId' => $destacarDemandaId,
            'categoriasDemanda' => Demanda::CATEGORIAS,
            'categoriasSugestao' => Sugestao::CATEGORIAS,
            'statusOptions' => $statusOptions,
            'statusOptionsJson' => json_encode($statusOptions, JSON_UNESCAPED_UNICODE),
            'filtros' => $filtros,
            'erroFiltro' => $erroFiltro,
            'queryPaginacao' => http_build_query($verTodas
                ? $this->parametrosConsulta($tipo, $filtros)
                : ['tipo' => $tipo]),
        ]);
    }

    public function atualizar()
    {
        if (!$this->validarRequisicaoApi()) {
            return;
        }

        $tipo = $this->postString('tipo');
        $id = $this->postId();
        $titulo = $this->postString('titulo');
        $descricao = $this->postString('descricao');
        $categoria = $this->postString('categoria');
        $model = $this->modelPorTipo($tipo);

        if (!$model || $id <= 0) {
            $this->responderJson(400, ['success' => false, 'message' => 'O item selecionado é inválido.']);
            return;
        }

        $ehAdministrador = Sessao::ehAdministrador();
        $ehModerador = !$ehAdministrador && Sessao::ehModerador();
        $permissaoEdicao = $tipo === 'demanda'
            ? Permissao::EDITAR_DEMANDAS
            : Permissao::EDITAR_SUGESTOES;
        $podeEditarOutros = $ehAdministrador
            || ($ehModerador && Sessao::temPermissao($permissaoEdicao));
        $item = $tipo === 'demanda'
            ? $model->lerDemandaPorId($id)
            : $model->lerSugestaoPorId($id);
        if (!$item) {
            $this->responderJson(404, ['success' => false, 'message' => 'O registro não foi encontrado.']);
            return;
        }
        if ((int) $item->usuario_id !== Sessao::usuarioId() && !$podeEditarOutros) {
            $this->responderJson(403, ['success' => false, 'message' => 'Você não tem permissão para alterar este registro.']);
            return;
        }

        $statusFoiEnviado = array_key_exists('status', $_POST);
        $statusMudou = false;
        $status = '';
        if ($statusFoiEnviado) {
            if (!$podeEditarOutros) {
                $this->responderJson(403, ['success' => false, 'message' => 'Você não tem permissão para alterar o status.']);
                return;
            }
            if (!isset($_POST['status']) || !is_string($_POST['status'])) {
                $this->responderJson(422, ['success' => false, 'message' => 'Selecione um status válido.']);
                return;
            }

            $status = $this->postString('status');
            $opcoesStatus = $this->statusOptions($tipo === 'demanda' ? 'demandas' : 'sugestoes');
            if (!array_key_exists($status, $opcoesStatus)) {
                $this->responderJson(422, ['success' => false, 'message' => 'Selecione um status válido.']);
                return;
            }
            $statusMudou = $status !== $this->statusParaEdicao($tipo, $item->status_original);
        }

        $categorias = $tipo === 'demanda' ? Demanda::CATEGORIAS : Sugestao::CATEGORIAS;
        if (mb_strlen($titulo) < 5 || mb_strlen($titulo) > 120) {
            $this->responderJson(422, ['success' => false, 'message' => 'O título deve ter entre 5 e 120 caracteres.']);
            return;
        }
        if (mb_strlen($descricao) < 10 || mb_strlen($descricao) > 600) {
            $this->responderJson(422, ['success' => false, 'message' => 'A descrição deve ter entre 10 e 600 caracteres.']);
            return;
        }
        if (!array_key_exists($categoria, $categorias)) {
            $this->responderJson(422, ['success' => false, 'message' => 'Selecione uma categoria válida.']);
            return;
        }
        if ($model->tituloEmUso($titulo, $id)) {
            $this->responderJson(409, ['success' => false, 'message' => 'Outro registro já usa esse título.']);
            return;
        }

        $dados = [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'categoria' => $categoria,
        ];
        if ($statusMudou) {
            $salvo = $model->atualizarComoModerador($id, $dados, $status, $item->status_original);
        } else {
            $salvo = $podeEditarOutros
                ? $model->atualizarComoModerador($id, $dados)
                : $model->atualizarDoUsuario($id, Sessao::usuarioId(), $dados);
        }
        if (!$salvo) {
            $statusHttp = $statusMudou ? 409 : 500;
            $mensagem = $statusMudou
                ? 'O status mudou em outra sessão. Atualize a página e tente novamente.'
                : 'Não foi possível salvar as alterações.';
            $this->responderJson($statusHttp, ['success' => false, 'message' => $mensagem]);
            return;
        }

        if ($statusMudou) {
            $nomeTipo = $tipo === 'demanda' ? 'demanda' : 'sugestão';
            $statusTexto = $opcoesStatus[$status];
            $mensagemUsuario = 'O status da sua ' . $nomeTipo . ' #' . $id . ' foi alterado para "' . $statusTexto . '".';
            if (!$this->notificacaoModel->criar((int) $item->usuario_id, 'Status atualizado', $mensagemUsuario)) {
                error_log('Demandfy: falha ao notificar o autor após alteração de status.');
            }
            $this->notificacaoModel->notificarModeradores(
                'Status alterado',
                'A ' . $nomeTipo . ' #' . $id . ' mudou para "' . $statusTexto . '".',
                [Sessao::usuarioId(), (int) $item->usuario_id]
            );
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Alterações salvas com sucesso.']);
    }

    public function responder()
    {
        if (!$this->validarRequisicaoApi()) {
            return;
        }

        $tipo = $this->postString('tipo');
        $id = $this->postId();
        $mensagem = $this->postString('mensagem');
        $model = $this->modelPorTipo($tipo);
        if (!$model || $id <= 0) {
            $this->responderJson(400, ['success' => false, 'message' => 'O item selecionado é inválido.']);
            return;
        }

        $ehAdministrador = Sessao::ehAdministrador();
        $ehModerador = !$ehAdministrador && Sessao::ehModerador();
        $permissaoEdicao = $tipo === 'demanda'
            ? Permissao::EDITAR_DEMANDAS
            : Permissao::EDITAR_SUGESTOES;
        if (!$ehAdministrador && (!$ehModerador || !Sessao::temPermissao($permissaoEdicao))) {
            $this->responderJson(403, ['success' => false, 'message' => 'Você não tem permissão para responder a este registro.']);
            return;
        }

        $item = $tipo === 'demanda'
            ? $model->lerDemandaPorId($id)
            : $model->lerSugestaoPorId($id);
        if (!$item) {
            $this->responderJson(404, ['success' => false, 'message' => 'O registro não foi encontrado.']);
            return;
        }
        if ($mensagem === '' || mb_strlen($mensagem) > 500) {
            $this->responderJson(422, ['success' => false, 'message' => 'A resposta deve ter entre 1 e 500 caracteres.']);
            return;
        }
        if (!$this->respostaModel->criar($tipo, $id, Sessao::usuarioId(), $mensagem)) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível salvar a resposta.']);
            return;
        }

        $nomeTipo = $tipo === 'demanda' ? 'demanda' : 'sugestão';
        $trecho = mb_substr($mensagem, 0, 350);
        if (mb_strlen($mensagem) > 350) {
            $trecho .= '...';
        }
        $mensagemNotificacao = 'Sua ' . $nomeTipo . ' #' . $id . ' recebeu uma resposta: "' . $trecho . '". Acesse o Histórico para ver a conversa.';
        if (!$this->notificacaoModel->criar((int) $item->usuario_id, 'Resposta do Grêmio', $mensagemNotificacao)) {
            error_log('Demandfy: falha ao notificar o autor após nova resposta.');
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Resposta enviada com sucesso.']);
    }

    public function excluir()
    {
        if (!$this->validarRequisicaoApi()) {
            return;
        }

        $tipo = $this->postString('tipo');
        $id = $this->postId();
        $model = $this->modelPorTipo($tipo);
        if (!$model || $id <= 0) {
            $this->responderJson(400, ['success' => false, 'message' => 'O item selecionado é inválido.']);
            return;
        }

        if (!$model->lerHistoricoPorIdDoUsuario($id, Sessao::usuarioId())) {
            $this->responderJson(404, ['success' => false, 'message' => 'O item não foi encontrado no seu histórico.']);
            return;
        }

        if (!$model->excluirDoUsuario($id, Sessao::usuarioId())) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível excluir o item.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Item excluído com sucesso.']);
    }

    public function reordenar()
    {
        if (!$this->validarRequisicaoApi()) {
            return;
        }

        $tipo = $this->postString('tipo');
        $model = $this->modelPorTipo($tipo);
        $idsJson = isset($_POST['ids']) && is_string($_POST['ids']) ? $_POST['ids'] : '';
        $ids = json_decode($idsJson, true);
        if (!$model || !is_array($ids)) {
            $this->responderJson(400, ['success' => false, 'message' => 'A ordem enviada é inválida.']);
            return;
        }

        if (!$model->salvarOrdemDoUsuario(Sessao::usuarioId(), $ids)) {
            $this->responderJson(422, ['success' => false, 'message' => 'A ordem não pôde ser salva. Atualize a página e tente novamente.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Ordem atualizada.']);
    }

    private function formatarItem($item, $tipo)
    {
        $status = (string) $item->status;
        if ($tipo === 'demandas') {
            $mapa = [
                'aberta' => ['Aguardando', 'pendente'],
                'aguardando' => ['Aguardando', 'pendente'],
                'em_analise' => ['Em análise', 'andamento'],
                'em_andamento' => ['Em andamento', 'andamento'],
                'deferido' => ['Resolvida', 'resolvida'],
                'resolvida' => ['Resolvida', 'resolvida'],
                'cancelada' => ['Cancelada', 'negada'],
            ];
        } else {
            $mapa = [
                'enviada' => ['Enviada', 'pendente'],
                'em_analise' => ['Em análise', 'andamento'],
                'em_andamento' => ['Em andamento', 'andamento'],
                'aceita' => ['Aceita', 'resolvida'],
                'recusada' => ['Recusada', 'negada'],
            ];
        }

        $estado = isset($mapa[$status]) ? $mapa[$status] : [$status, 'pendente'];
        $item->statusTexto = $estado[0];
        $item->statusClasse = $estado[1];
        $data = isset($item->atualizacao) ? strtotime($item->atualizacao) : false;
        $item->atualizacaoTexto = $data ? date('d/m/Y H:i', $data) : '—';
    }

    private function validarRequisicaoApi()
    {
        if (!Sessao::estaLogado()) {
            $this->responderJson(401, ['success' => false, 'message' => 'Sua sessão expirou. Entre novamente.']);
            return false;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(405, ['success' => false, 'message' => 'Método não permitido.']);
            return false;
        }
        if (!Csrf::validar()) {
            $this->responderJson(403, ['success' => false, 'message' => 'Sessão expirada ou token inválido. Atualize a página.']);
            return false;
        }
        return true;
    }

    private function responderJson($status, $dados)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function modelPorTipo($tipo)
    {
        if ($tipo === 'demanda') {
            return $this->demandaModel;
        }
        if ($tipo === 'sugestao') {
            return $this->sugestaoModel;
        }
        return false;
    }

    private function postString($nome)
    {
        return isset($_POST[$nome]) && is_string($_POST[$nome]) ? trim($_POST[$nome]) : '';
    }

    private function postId()
    {
        return isset($_POST['id']) ? (int) $_POST['id'] : 0;
    }

    private function statusOptions($tipo)
    {
        return $tipo === 'demandas'
            ? [
                'aguardando' => 'Aguardando',
                'em_analise' => 'Em análise',
                'em_andamento' => 'Em andamento',
                'resolvida' => 'Resolvida',
                'cancelada' => 'Cancelada',
            ]
            : [
                'enviada' => 'Enviada',
                'em_analise' => 'Em análise',
                'em_andamento' => 'Em andamento',
                'aceita' => 'Aceita',
                'recusada' => 'Recusada',
            ];
    }

    private function statusParaEdicao($tipo, $status)
    {
        if ($tipo === 'demanda') {
            if ($status === 'aberta') {
                return 'aguardando';
            }
            if ($status === 'deferido') {
                return 'resolvida';
            }
        }
        return $status;
    }

    private function normalizarDataFiltro($valor)
    {
        if (!is_string($valor) || preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $valor) !== 1) {
            return false;
        }
        $data = DateTime::createFromFormat('!Y-m-d', $valor);
        return $data && $data->format('Y-m-d') === $valor ? $valor : false;
    }

    private function parametrosConsulta($tipo, $filtros)
    {
        $parametros = ['tipo' => $tipo];
        if (!empty($filtros['criado_por_mim'])) {
            $parametros['criado_por_mim'] = '1';
        }
        foreach (['status', 'data_inicio', 'data_fim'] as $campo) {
            if (!empty($filtros[$campo])) {
                $parametros[$campo] = $filtros[$campo];
            }
        }
        return $parametros;
    }
}
