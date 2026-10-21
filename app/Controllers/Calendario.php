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
            'eventosDoDia'    => isset($porDia[$diaSelecionado]) ? $porDia[$diaSelecionado] : [],
        ];

        $this->view('calendario/index', $dados);
    }
}
