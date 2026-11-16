<?php
/*
 * VIEW: AVISOS
 * MUDANÇAS: lista vinda do banco; as abas (Todos/Importantes/Informativos) e as setas
 * de navegação agora são links que filtram e paginam de verdade.
 * Obs.: o tipo "lembrete" aparece em "Todos" (não havia aba própria no layout original).
 */
// [imagem, rótulo] de cada tipo; a classe CSS é o próprio tipo (importante/informativo/lembrete).
$icones = [
    'importante'  => 'Leave.png',
    'informativo' => 'Search%20Property.png',
    'lembrete'    => 'Timesheet.png',
];
$tipo = $dados['tipo'];
$pagina = $dados['pagina'];
$totalPaginas = $dados['totalPaginas'];
$podeGerenciar = !empty($dados['podeGerenciar']);
$categoriasAviso = isset($dados['categoriasAviso']) && is_array($dados['categoriasAviso'])
    ? $dados['categoriasAviso']
    : [];
$gerenciamentoDisponivel = $podeGerenciar && count($categoriasAviso) > 0;
$avisosJs = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'avisos.js';

// Monta o link preservando o filtro: /avisos?tipo=importante&pagina=2
if (!function_exists('linkAvisos')) {
    function linkAvisos($tipo, $pagina)
    {
        $params = [];
        if ($tipo !== '') {
            $params['tipo'] = $tipo;
        }
        if ($pagina > 1) {
            $params['pagina'] = $pagina;
        }
        return URL . '/avisos' . ($params ? '?' . http_build_query($params) : '');
    }
}
?>
<main class="inicio avisos-pagina">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <section class="avisos" data-url="<?= Html::e(URL) ?>">

        <?php if (!$dados['avisosDisponiveis']): ?>
            <p class="vazio">O banco atual não possui tabela de avisos. Esse recurso precisa ser configurado no banco para publicar comunicados.</p>
        <?php else: ?>
        <nav class="abas">
            <a class="aba<?= $tipo === '' ? ' ativa' : '' ?>" href="<?= linkAvisos('', 1) ?>">Todos</a>
            <a class="aba<?= $tipo === 'importante' ? ' ativa' : '' ?>" href="<?= linkAvisos('importante', 1) ?>">Importantes</a>
            <a class="aba<?= $tipo === 'informativo' ? ' ativa' : '' ?>" href="<?= linkAvisos('informativo', 1) ?>">Informativos</a>
            <a class="aba<?= $tipo === 'lembrete' ? ' ativa' : '' ?>" href="<?= linkAvisos('lembrete', 1) ?>">Lembretes</a>

            <?php if ($gerenciamentoDisponivel): ?>
                <button type="button" class="aviso-adicionar" id="aviso-adicionar">+ Adicionar aviso</button>
            <?php endif; ?>

            <div class="navegacao">
                <?php if ($pagina > 1): ?>
                    <a class="navegacao-link" href="<?= linkAvisos($tipo, $pagina - 1) ?>" aria-label="Voltar">
                        <img src="<?= URL ?>/img/Right.png" alt="Voltar">
                    </a>
                <?php else: ?>
                    <span class="navegacao-link desativado"><img src="<?= URL ?>/img/Right.png" alt="Voltar"></span>
                <?php endif; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <a class="navegacao-link" href="<?= linkAvisos($tipo, $pagina + 1) ?>" aria-label="Avançar">
                        <img src="<?= URL ?>/img/Right.png" alt="Avançar" style="transform: scaleX(-1);">
                    </a>
                <?php else: ?>
                    <span class="navegacao-link desativado"><img src="<?= URL ?>/img/Right.png" alt="Avançar" style="transform: scaleX(-1);"></span>
                <?php endif; ?>
            </div>
        </nav>

        <?php if ($podeGerenciar && !$categoriasAviso): ?>
            <p class="aviso-categorias-indisponiveis">Não há categorias de aviso cadastradas. O gerenciamento está indisponível.</p>
        <?php endif; ?>

        <div class="lista-avisos">
            <?php if (empty($dados['avisos'])): ?>
                <p class="vazio">Nenhum aviso encontrado.</p>
            <?php endif; ?>

            <?php foreach ($dados['avisos'] as $aviso): ?>
                <?php
                $t = isset($icones[$aviso->tipo]) ? $aviso->tipo : 'informativo';
                ?>
                <article class="aviso">
                    <div class="icone <?= Html::e($t) ?>">
                        <img src="<?= URL ?>/img/<?= $icones[$t] ?>" alt="<?= Html::e(Aviso::TIPOS[$t]) ?>">
                    </div>

                    <div class="conteudo">
                        <span class="tipo <?= Html::e($t) ?>-texto"><?= Html::e(Aviso::TIPOS[$t]) ?></span>
                        <h2><?= Html::e($aviso->titulo) ?></h2>
                        <p><?= nl2br(Html::e($aviso->texto)) ?></p>
                    </div>
                    <?php if ($gerenciamentoDisponivel): ?>
                        <div class="aviso-acoes">
                            <button type="button" class="aviso-editar"
                                    aria-label="Editar aviso: <?= Html::e($aviso->titulo) ?>"
                                    data-id="<?= (int) $aviso->id ?>"
                                    data-titulo="<?= Html::e($aviso->titulo) ?>"
                                    data-descricao="<?= Html::e($aviso->texto) ?>"
                                    data-categoria-id="<?= (int) $aviso->categoria_id ?>">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16.5V20h3.5L19 8.5 15.5 5 4 16.5Z"/><path d="m13.8 6.7 3.5 3.5"/></svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($gerenciamentoDisponivel): ?>
            <div class="aviso-modal-fundo" id="aviso-modal-fundo" hidden>
                <section class="aviso-modal" role="dialog" aria-modal="true" aria-labelledby="aviso-modal-titulo">
                    <header class="aviso-modal-cabecalho">
                        <span class="aviso-modal-icone" aria-hidden="true">
                            <svg viewBox="0 0 32 32"><path d="M5 6h22v20H5z"/><path d="M9 11h14M9 16h14M9 21h8"/></svg>
                        </span>
                        <div>
                            <h2 id="aviso-modal-titulo">Adicionar aviso</h2>
                            <p id="aviso-modal-subtitulo">Adicione um aviso ou lembrete para os usuários.</p>
                        </div>
                        <button type="button" class="aviso-modal-fechar" data-fechar-aviso-modal aria-label="Fechar">&times;</button>
                    </header>

                    <form id="aviso-form" method="post" action="<?= URL ?>/avisos/criarAviso">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id">

                        <label for="aviso-titulo">Título do aviso</label>
                        <input type="text" id="aviso-titulo" name="titulo" maxlength="200" placeholder="Ex: Reunião do Grêmio." required>

                        <label for="aviso-descricao">Explique resumidamente</label>
                        <textarea id="aviso-descricao" name="descricao" maxlength="200" placeholder="Explique o que é o aviso de forma resumida." required></textarea>
                        <span class="aviso-contador" id="aviso-contador">0/200</span>

                        <label for="aviso-categoria">Categoria</label>
                        <select id="aviso-categoria" name="categoria_id" required>
                            <option value="" disabled selected>Selecione uma categoria</option>
                            <?php foreach ($categoriasAviso as $categoria): ?>
                                <option value="<?= (int) $categoria->id ?>"><?= Html::e($categoria->nome) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <p class="aviso-modal-feedback" id="aviso-modal-feedback" role="status" aria-live="polite"></p>
                        <footer class="aviso-modal-rodape">
                            <button type="button" class="aviso-modal-excluir" id="aviso-form-excluir" hidden>Excluir aviso</button>
                            <button type="button" class="aviso-modal-cancelar" data-fechar-aviso-modal>Cancelar</button>
                            <button type="submit" class="aviso-modal-salvar" id="aviso-form-salvar">Enviar aviso</button>
                        </footer>
                    </form>
                </section>
            </div>
            <script src="<?= URL ?>/js/avisos.js?v=<?= (int) filemtime($avisosJs) ?>" defer></script>
        <?php endif; ?>
        <?php endif; ?>

    </section>
</main>
