<?php
/*
 * CONTROLLER CALENDARIO
 * ------------------------------------------------------------------
 * /calendario                       -> mês atual
 * /calendario?mes=2026-11           -> outro mês (botões ‹ › da tela)
 * /calendario?mes=2026-11&dia=20    -> escolhe o dia mostrado em "Eventos do dia"
 *
 * A grade do mês (com os dias do mês anterior/seguinte que completam as semanas)
 * é montada AQUI no PHP; a view só desenha.
 */
class Calendario extends Controller
{
    private $eventoModel;

    public function __construct()
    {
        $this->eventoModel = $this->model('Evento');
    }

    public function index()
    {
        $this->exigirLogin();
        $podeGerenciar = $this->podeGerenciarEventos();

        $nomesMeses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio',
                       6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro',
                       11 => 'Novembro', 12 => 'Dezembro'];

        // 1) Qual mês mostrar? (?mes=AAAA-MM; se vier errado usa o mês atual)
        $ano = (int) date('Y');
        $mes = (int) date('n');
        $param = isset($_GET['mes']) && is_string($_GET['mes']) ? $_GET['mes'] : '';
        if (preg_match('/^(\d{4})-(\d{2})$/', $param, $m)
            && (int) $m[1] >= 2000 && (int) $m[1] <= 2100
            && (int) $m[2] >= 1 && (int) $m[2] <= 12) {
            $ano = (int) $m[1];
            $mes = (int) $m[2];
        }

        $primeiroDia = sprintf('%04d-%02d-01', $ano, $mes);
        $diasNoMes = (int) date('t', strtotime($primeiroDia));
        $ultimoDia = sprintf('%04d-%02d-%02d', $ano, $mes, $diasNoMes);

        // 2) Eventos do mês, agrupados pelo número do dia.
        $porDia = [];
        foreach ($this->eventoModel->listarPorPeriodo($primeiroDia, $ultimoDia) as $evento) {
            $porDia[(int) substr($evento->data_evento, 8, 2)][] = $evento;
        }

        // 3) Qual dia mostrar no painel "Eventos do dia"?
        $ehMesAtual = ($ano === (int) date('Y') && $mes === (int) date('n'));
        $diaSelecionado = $ehMesAtual ? (int) date('j') : 1;
        if (isset($_GET['dia']) && ctype_digit((string) $_GET['dia'])
            && (int) $_GET['dia'] >= 1 && (int) $_GET['dia'] <= $diasNoMes) {
            $diaSelecionado = (int) $_GET['dia'];
        }
        $dataIso = sprintf('%04d-%02d-%02d', $ano, $mes, $diaSelecionado);

        // 4) Monta as células da grade (semana começa no domingo).
        $celulas = [];
        $posicaoDia1 = (int) date('w', strtotime($primeiroDia)); // 0 = domingo
        $diasMesAnterior = (int) date('t', strtotime($primeiroDia . ' -1 month'));

        for ($i = 0; $i < $posicaoDia1; $i++) {
            $celulas[] = ['dia' => $diasMesAnterior - $posicaoDia1 + 1 + $i, 'outroMes' => true, 'eventos' => []];
        }
        for ($d = 1; $d <= $diasNoMes; $d++) {
            $celulas[] = [
                'dia'      => $d,
                'outroMes' => false,
                'eventos'  => isset($porDia[$d]) ? $porDia[$d] : [],
                'hoje'     => ($ehMesAtual && $d === (int) date('j')),
                'selecionado' => ($d === $diaSelecionado),
            ];
        }
        $proximoMes = 1;
        while (count($celulas) % 7 !== 0) {
            $celulas[] = ['dia' => $proximoMes++, 'outroMes' => true, 'eventos' => []];
        }

        $dados = [
            'titulo'          => 'Calendário',
            'nomeMes'         => $nomesMeses[$mes] . ' ' . $ano,
            'ano'             => $ano,
            'mes'             => $mes,
            'celulas'         => $celulas,
            'mesAnterior'     => date('Y-m', strtotime($primeiroDia . ' -1 month')),
            'mesSeguinte'     => date('Y-m', strtotime($primeiroDia . ' +1 month')),
            'mesAtual'        => sprintf('%04d-%02d', $ano, $mes),
            'diaSelecionado'  => $diaSelecionado,
            'dataSelecionada' => $diaSelecionado . ' de ' . $nomesMeses[$mes] . ' de ' . $ano,
            'dataIso'         => $dataIso,
            'eventosDoDia'    => $this->eventoModel->listarPorDia($dataIso),
            'categoriasEvento' => $podeGerenciar ? $this->eventoModel->listarCategorias() : [],
            'podeGerenciar'   => $podeGerenciar,
        ];

        $this->view('calendario/index', $dados);
    }

    public function criarEvento()
    {
        if (!$this->validarApiEventos()) {
            return;
        }

        $data = $this->validarData($this->postString('data'));
        if ($data === false) {
            $this->responderJson(422, ['success' => false, 'message' => 'Selecione uma data válida.']);
            return;
        }
        $dadosEvento = $this->validarDadosEvento();
        if ($dadosEvento === false) {
            return;
        }

        $id = $this->eventoModel->criar($data, $dadosEvento);
        if (!$id) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível criar o evento.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Evento criado com sucesso.']);
    }

    public function atualizarEvento()
    {
        if (!$this->validarApiEventos()) {
            return;
        }

        $id = $this->postId();
        $data = $this->validarData($this->postString('data'));
        if ($id <= 0 || $data === false) {
            $this->responderJson(400, ['success' => false, 'message' => 'O evento selecionado é inválido.']);
            return;
        }
        if (!$this->eventoModel->lerPorIdEDia($id, $data)) {
            $this->responderJson(404, ['success' => false, 'message' => 'O evento não foi encontrado neste dia.']);
            return;
        }

        $dadosEvento = $this->validarDadosEvento();
        if ($dadosEvento === false) {
            return;
        }
        if (!$this->eventoModel->atualizar($id, $data, $dadosEvento)) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível salvar as alterações.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Evento atualizado com sucesso.']);
    }

    public function excluirEvento()
    {
        if (!$this->validarApiEventos()) {
            return;
        }

        $id = $this->postId();
        $data = $this->validarData($this->postString('data'));
        if ($id <= 0 || $data === false) {
            $this->responderJson(400, ['success' => false, 'message' => 'O evento selecionado é inválido.']);
            return;
        }
        if (!$this->eventoModel->lerPorIdEDia($id, $data)) {
            $this->responderJson(404, ['success' => false, 'message' => 'O evento não foi encontrado neste dia.']);
            return;
        }
        if (!$this->eventoModel->excluir($id, $data)) {
            $this->responderJson(500, ['success' => false, 'message' => 'Não foi possível remover o evento.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Evento removido com sucesso.']);
    }

    public function reordenarEventos()
    {
        if (!$this->validarApiEventos()) {
            return;
        }

        $data = $this->validarData($this->postString('data'));
        $ids = isset($_POST['ids']) && is_string($_POST['ids'])
            ? json_decode($_POST['ids'], true)
            : false;
        if ($data === false || !is_array($ids)) {
            $this->responderJson(422, ['success' => false, 'message' => 'A ordem enviada é inválida.']);
            return;
        }
        if (!$this->eventoModel->salvarOrdemDoDia($data, $ids)) {
            $this->responderJson(422, ['success' => false, 'message' => 'A ordem não pôde ser salva. Atualize a página e tente novamente.']);
            return;
        }

        $this->responderJson(200, ['success' => true, 'message' => 'Ordem dos eventos atualizada.']);
    }

    private function podeGerenciarEventos()
    {
        if (Sessao::ehAdministrador()) {
            return true;
        }
        return Sessao::ehModerador() && Sessao::temPermissao(Permissao::GERENCIAR_EVENTOS);
    }

    private function validarApiEventos()
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
        if (!$this->podeGerenciarEventos()) {
            $this->responderJson(403, ['success' => false, 'message' => 'Você não tem permissão para gerenciar eventos.']);
            return false;
        }
        return true;
    }

    private function validarDadosEvento()
    {
        $titulo = $this->postString('titulo');
        $descricao = $this->postString('descricao');
        $categoriaId = isset($_POST['categoria_id']) && is_scalar($_POST['categoria_id'])
            ? filter_var($_POST['categoria_id'], FILTER_VALIDATE_INT)
            : false;
        $horaInicio = $this->validarHora($this->postString('hora_inicio'));
        $horaFim = $this->validarHora($this->postString('hora_fim'));

        if (mb_strlen($titulo) < 3 || mb_strlen($titulo) > 200) {
            $this->responderJson(422, ['success' => false, 'message' => 'O título deve ter entre 3 e 200 caracteres.']);
            return false;
        }
        if (mb_strlen($descricao) > 600) {
            $this->responderJson(422, ['success' => false, 'message' => 'A descrição pode ter no máximo 600 caracteres.']);
            return false;
        }
        if ($categoriaId === false || $categoriaId <= 0 || !$this->eventoModel->categoriaValida($categoriaId)) {
            $this->responderJson(422, ['success' => false, 'message' => 'Selecione uma categoria válida.']);
            return false;
        }
        if ($horaInicio === false || $horaFim === false) {
            $this->responderJson(422, ['success' => false, 'message' => 'Informe horários válidos.']);
            return false;
        }
        if ($horaInicio !== null && $horaFim !== null && $horaFim < $horaInicio) {
            $this->responderJson(422, ['success' => false, 'message' => 'O horário final não pode ser anterior ao inicial.']);
            return false;
        }

        return [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'categoria_id' => $categoriaId,
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
        ];
    }

    private function validarData($data)
    {
        if (!is_string($data) || preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $data) !== 1) {
            return false;
        }
        $dataObj = DateTime::createFromFormat('!Y-m-d', $data);
        return $dataObj && $dataObj->format('Y-m-d') === $data ? $data : false;
    }

    private function validarHora($hora)
    {
        if ($hora === '') {
            return null;
        }
        return preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d\z/', $hora) === 1 ? $hora : false;
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
