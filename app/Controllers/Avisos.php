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

    public function __construct()
    {
        $this->avisoModel = $this->model('Aviso');
    }

    public function index()
    {
        $this->exigirLogin();

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
            'tipo'         => $tipo,
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
        ];

        $this->view('avisos/index', $dados);
    }
}
