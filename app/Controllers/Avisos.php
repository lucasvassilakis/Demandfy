<?php
/*
 * CONTROLLER AVISOS
 * ------------------------------------------------------------------
 * /avisos                        -> todos os avisos (página 1)
 * /avisos?tipo=importante        -> filtra por tipo (importante | informativo | lembrete)
 * /avisos?tipo=lembrete&pagina=2 -> paginação (setas "Voltar" e "Avançar" da tela)
 */
class Avisos extends Controller
{

    const POR_PAGINA = 4;

    private $avisoModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->avisoModel = $this->model('Aviso');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    public function index()
    {
        $this->exigirLogin();
        $avisosDisponiveis = $this->avisoModel->disponivel();
        $podeGerenciar = $avisosDisponiveis && $this->podeGerenciarAvisos();

        // Filtro: só aceita valores conhecidos (qualquer outro vira "todos").
        $tipo = isset($_GET['tipo']) && is_string($_GET['tipo']) ? $_GET['tipo'] : '';
        if (!array_key_exists($tipo, Aviso::TIPOS)) {
            $tipo = '';
        }
        $filtro = $tipo === '' ? null : $tipo;

        // Paginação.
        $total = $this->avisoModel->contar($filtro);
        $totalPaginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina = isset($_GET['pagina']) && ctype_digit((string) $_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        $pagina = min(max($pagina, 1), $totalPaginas);

        $dados = [
            'titulo'       => 'Avisos',
            'avisos'       => $this->avisoModel->listar($filtro, self::POR_PAGINA, ($pagina - 1) * self::POR_PAGINA),
            'avisosDisponiveis' => $avisosDisponiveis,
            'podeGerenciar' => $podeGerenciar,
            'categoriasAviso' => $podeGerenciar ? $this->avisoModel->listarCategorias() : [],
            'tipo'         => $tipo,
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
        ];

        $this->view('avisos/index', $dados);
    }

    public function criarAviso()
    {
        if (!$this->validarApiAvisos()) {
            return;
        }

        $dadosAviso = $this->validarDadosAviso();
        if ($dadosAviso === false) {
            return;
        }

        $avisoId = $this->avisoModel->criar(Sessao::usuarioId(), $dadosAviso);
        if (!$avisoId) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível publicar o aviso.']);
            return;
        }

        $this->notificacaoModel->notificarUsuarios(
            'Novo aviso do Grêmio',
            'Foi publicado um novo aviso: "' . $dadosAviso['titulo'] . '".',
            Sessao::usuarioId()
        );
        $this->responderJson(200, ['success' => true, 'message' => 'Aviso publicado com sucesso.']);
    }

    public function atualizarAviso()
    {
        if (!$this->validarApiAvisos()) {
            return;
        }

        $id = $this->postId();
        if ($id === false || $id <= 0) {
            $this->responderJson(400, ['success' => false, 'message' => 'O aviso selecionado é inválido.']);
            return;
        }
        if (!$this->avisoModel->lerPorId($id)) {
            $this->responderJson(404, ['success' => false, 'message' => 'O aviso não foi encontrado.']);
            return;
        }

        $dadosAviso = $this->validarDadosAviso();
        if ($dadosAviso === false) {
            return;
        }
        if (!$this->avisoModel->atualizar($id, $dadosAviso)) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível salvar as alterações.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Aviso atualizado com sucesso.']);
    }

    public function excluirAviso()
    {
        if (!$this->validarApiAvisos()) {
            return;
        }

        $id = $this->postId();
        if ($id === false || $id <= 0) {
            $this->responderJson(400, ['success' => false, 'message' => 'O aviso selecionado é inválido.']);
            return;
        }
        if (!$this->avisoModel->lerPorId($id)) {
            $this->responderJson(404, ['success' => false, 'message' => 'O aviso não foi encontrado.']);
            return;
        }
        if (!$this->avisoModel->excluir($id)) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível excluir o aviso.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Aviso excluído com sucesso.']);
    }

    private function podeGerenciarAvisos()
    {
        return Sessao::ehAdministrador()
            || (Sessao::ehModerador() && Sessao::temPermissao(Permissao::GERENCIAR_AVISOS));
    }

    private function validarApiAvisos()
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
        if (!$this->podeGerenciarAvisos()) {
            $this->responderJson(403, ['success' => false, 'message' => 'Você não tem permissão para gerenciar avisos.']);
            return false;
        }
        if (!$this->avisoModel->disponivel()) {
            $this->responderJson(503, ['success' => false, 'message' => 'A tabela de avisos não está disponível neste banco.']);
            return false;
        }
        return true;
    }

    private function validarDadosAviso()
    {
        $titulo = $this->postString('titulo');
        $descricao = $this->postString('descricao');
        $categoriaId = isset($_POST['categoria_id']) && is_scalar($_POST['categoria_id'])
            ? filter_var($_POST['categoria_id'], FILTER_VALIDATE_INT)
            : false;

        if (mb_strlen($titulo) < 3 || mb_strlen($titulo) > 200) {
            $this->responderJson(422, ['success' => false, 'message' => 'O título deve ter entre 3 e 200 caracteres.']);
            return false;
        }
        if ($descricao === '' || mb_strlen($descricao) > 200) {
            $this->responderJson(422, ['success' => false, 'message' => 'A descrição é obrigatória e pode ter no máximo 200 caracteres.']);
            return false;
        }
        if ($categoriaId === false || $categoriaId <= 0
            || !$this->avisoModel->categoriaValida($categoriaId)) {
            $this->responderJson(422, ['success' => false, 'message' => 'Selecione uma categoria válida.']);
            return false;
        }

        return [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'categoria_id' => $categoriaId,
        ];
    }

    private function postString($nome)
    {
        return isset($_POST[$nome]) && is_string($_POST[$nome]) ? trim($_POST[$nome]) : '';
    }

    private function postId()
    {
        return isset($_POST['id']) && is_scalar($_POST['id'])
            ? filter_var($_POST['id'], FILTER_VALIDATE_INT)
            : false;
    }

    private function responderJson($status, $dados)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
