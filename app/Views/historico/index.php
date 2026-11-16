<?php
$tipoItem = $dados['tipo'] === 'demandas' ? 'demanda' : 'sugestao';
$nomeTipo = $dados['tipo'] === 'demandas' ? 'demanda' : 'sugestão';
$nomeTipoPlural = $dados['tipo'] === 'demandas' ? 'demandas' : 'sugestões';
$ordemJson = htmlspecialchars(json_encode($dados['ordemIds']), ENT_QUOTES, 'UTF-8');
$categoriasDemandaJson = htmlspecialchars(json_encode($dados['categoriasDemanda']), ENT_QUOTES, 'UTF-8');
$categoriasSugestaoJson = htmlspecialchars(json_encode($dados['categoriasSugestao']), ENT_QUOTES, 'UTF-8');
$historicoJs = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'history.js';
$verTodas = !empty($dados['verTodas']);
$filtrosComuns = [];
if ($verTodas && !empty($dados['filtros']['criado_por_mim'])) {
    $filtrosComuns['criado_por_mim'] = '1';
}
foreach (['data_inicio', 'data_fim'] as $campoData) {
    if ($verTodas && !empty($dados['filtros'][$campoData])) {
        $filtrosComuns[$campoData] = $dados['filtros'][$campoData];
    }
}
$queryDemandas = http_build_query(array_merge(['tipo' => 'demandas'], $filtrosComuns));
$querySugestoes = http_build_query(array_merge(['tipo' => 'sugestoes'], $filtrosComuns));
$totalColunas = 4 + ($verTodas ? 1 : 0) + (!empty($dados['temAcoes']) ? 1 : 0);
?>
<main class="inicio historico-pagina<?= $verTodas ? ' historico-todas' : '' ?><?= !empty($dados['temAcoes']) && $verTodas ? ' historico-com-acoes' : '' ?>">
    <h1 class="titulo historico-titulo"><?= Html::e($dados['titulo']) ?></h1>

    <nav class="historico-abas" aria-label="Tipo de histórico">
        <a class="historico-aba<?= $dados['tipo'] === 'demandas' ? ' ativa' : '' ?>"
           href="<?= URL ?>/historico?<?= Html::e($queryDemandas) ?>"<?= $dados['tipo'] === 'demandas' ? ' aria-current="page"' : '' ?>>
            Demandas (<?= (int) $dados['totalDemandas'] ?>)
        </a>
        <a class="historico-aba sugestao-aba<?= $dados['tipo'] === 'sugestoes' ? ' ativa' : '' ?>"
           href="<?= URL ?>/historico?<?= Html::e($querySugestoes) ?>"<?= $dados['tipo'] === 'sugestoes' ? ' aria-current="page"' : '' ?>>
            Sugestões (<?= (int) $dados['totalSugestoes'] ?>)
        </a>
    </nav>

    <?php if ($verTodas): ?>
        <form class="historico-filtros" method="get" action="<?= URL ?>/historico">
            <input type="hidden" name="tipo" value="<?= Html::e($dados['tipo']) ?>">
            <label>
                <span>Autoria</span>
                <select name="criado_por_mim">
                    <option value="">Qualquer pessoa</option>
                    <option value="1"<?= !empty($dados['filtros']['criado_por_mim']) ? ' selected' : '' ?>>Criadas por mim</option>
                </select>
            </label>
            <label>
                <span>Status</span>
                <select name="status">
                    <option value="">Todos os status</option>
                    <?php foreach ($dados['statusOptions'] as $valor => $rotulo): ?>
                        <option value="<?= Html::e($valor) ?>"<?= $dados['filtros']['status'] === $valor ? ' selected' : '' ?>><?= Html::e($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>De</span>
                <input type="date" name="data_inicio" value="<?= Html::e($dados['filtros']['data_inicio']) ?>">
            </label>
            <label>
                <span>Até</span>
                <input type="date" name="data_fim" value="<?= Html::e($dados['filtros']['data_fim']) ?>">
            </label>
            <button type="submit">Filtrar</button>
            <a href="<?= URL ?>/historico?tipo=<?= Html::e($dados['tipo']) ?>">Limpar</a>
        </form>
        <?php if ($dados['erroFiltro'] !== ''): ?>
            <p class="historico-filtro-erro" role="alert"><?= Html::e($dados['erroFiltro']) ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <section class="historico-painel" id="historico-painel"
             aria-labelledby="historico-painel-titulo"
             data-tipo="<?= Html::e($tipoItem) ?>"
             data-ordem-ids="<?= $ordemJson ?>"
             data-todas="<?= $verTodas ? '1' : '0' ?>"
             data-status-options="<?= Html::e($dados['statusOptionsJson']) ?>"
             data-categorias-demanda="<?= $categoriasDemandaJson ?>"
             data-categorias-sugestao="<?= $categoriasSugestaoJson ?>"
             data-url="<?= Html::e(URL) ?>">
        <header class="historico-painel-cabecalho">
            <img src="<?= URL ?>/img/Bookmark.png" alt="" class="historico-icone">
            <h2 id="historico-painel-titulo"><?= $dados['tipo'] === 'demandas' ? 'Demandas' : 'Sugestões' ?></h2>
        </header>

        <p class="historico-feedback" id="historico-feedback" role="status" aria-live="polite"></p>

        <div class="historico-tabela-wrap">
            <table class="historico-tabela">
                <thead>
                    <tr>
                        <th scope="col" class="col-numero">#</th>
                        <th scope="col" class="col-titulo">Título</th>
                        <?php if ($verTodas): ?><th scope="col" class="col-autor">Enviado por</th><?php endif; ?>
                        <th scope="col" class="col-status">Status</th>
                        <th scope="col" class="col-atualizacao">Última atualização</th>
                        <?php if (!empty($dados['temAcoes'])): ?><th scope="col" class="col-acoes">Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="historico-itens">
                    <?php if (!$dados['itens']): ?>
                        <tr>
                            <td colspan="<?= $totalColunas ?>" class="historico-vazio">
                                <?= $verTodas ? 'Nenhum registro encontrado.' : 'Você ainda não registrou ' . Html::e($nomeTipoPlural) . '.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dados['itens'] as $indice => $item): ?>
                            <tr id="historico-item-<?= Html::e($tipoItem) ?>-<?= (int) $item->id ?>"
                                class="<?= $tipoItem === 'demanda' && (int) $dados['destacarDemandaId'] === (int) $item->id ? 'demanda-destacada' : '' ?>"
                                data-id="<?= (int) $item->id ?>">
                                <td class="col-numero"><?= (int) ($dados['deslocamento'] + $indice + 1) ?></td>
                                <td class="col-titulo">
                                    <span class="historico-texto-cortado" title="<?= Html::e($item->titulo) ?>"><?= Html::e($item->titulo) ?></span>
                                </td>
                                <?php if ($verTodas): ?>
                                    <td class="col-autor"><span class="historico-texto-cortado" title="<?= Html::e($item->autor) ?>"><?= Html::e($item->autor) ?></span></td>
                                <?php endif; ?>
                                <td class="col-status">
                                    <span class="historico-status <?= Html::e($item->statusClasse) ?>"><?= Html::e($item->statusTexto) ?></span>
                                </td>
                                <td class="col-atualizacao"><?= Html::e($item->atualizacaoTexto) ?></td>
                                <?php if (!empty($dados['temAcoes'])): ?>
                                    <td class="col-acoes">
                                        <div class="historico-acoes">
                                            <?php if (!$verTodas): ?>
                                                <button type="button" class="historico-ordenar" draggable="true"
                                                        aria-label="Segure e arraste para mudar a ordem de <?= Html::e($item->titulo) ?>">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M3 6h18M3 12h18M3 18h18" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!empty($item->podeEditar)): ?>
                                                <button type="button" class="historico-editar"
                                                        aria-label="Editar <?= Html::e($nomeTipo) ?>: <?= Html::e($item->titulo) ?>"
                                                        data-id="<?= (int) $item->id ?>"
                                                        data-tipo="<?= Html::e($tipoItem) ?>"
                                                        data-titulo="<?= Html::e($item->titulo) ?>"
                                                        data-descricao="<?= Html::e($item->descricao) ?>"
                                                        data-categoria="<?= Html::e($item->categoriaChave) ?>"
                                                        data-status="<?= Html::e($item->statusTexto) ?>"
                                                        data-status-key="<?= Html::e($item->statusEdicao) ?>"
                                                        data-pode-alterar-status="<?= !empty($item->podeAlterarStatus) ? '1' : '0' ?>"
                                                        data-proprio="<?= !empty($item->proprio) ? '1' : '0' ?>">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M4 16.5V20h3.5L19 8.5 15.5 5 4 16.5Z" />
                                                        <path d="m13.8 6.7 3.5 3.5" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!empty($item->podeResponder) || $item->totalRespostas > 0): ?>
                                                <button type="button" class="historico-respostas-toggle"
                                                        aria-expanded="false"
                                                        aria-controls="historico-respostas-<?= Html::e($tipoItem) ?>-<?= (int) $item->id ?>">
                                                    <?= $item->totalRespostas > 0
                                                        ? 'Respostas (' . (int) $item->totalRespostas . ')'
                                                        : 'Responder' ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php if (!empty($item->podeResponder) || $item->totalRespostas > 0): ?>
                                <tr class="historico-respostas-linha" id="historico-respostas-<?= Html::e($tipoItem) ?>-<?= (int) $item->id ?>" hidden>
                                    <td colspan="<?= $totalColunas ?>">
                                        <section class="historico-respostas" aria-label="Respostas de <?= Html::e($item->titulo) ?>">
                                            <h3>Respostas</h3>
                                            <?php if (!$item->respostas): ?>
                                                <p class="historico-respostas-vazio">Ainda não há respostas.</p>
                                            <?php else: ?>
                                                <ol class="historico-respostas-lista">
                                                    <?php foreach ($item->respostas as $resposta): ?>
                                                        <?php $respostaTimestamp = strtotime($resposta->criado_em); ?>
                                                        <li>
                                                            <p><?= nl2br(Html::e($resposta->mensagem)) ?></p>
                                                            <span><?= Html::e($resposta->autor) ?> · <?= $respostaTimestamp ? date('d/m/Y H:i', $respostaTimestamp) : '—' ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            <?php endif; ?>
                                            <?php if (!empty($item->podeResponder)): ?>
                                                <form class="historico-responder-form" method="post" action="<?= URL ?>/historico/responder">
                                                    <?= Csrf::campo() ?>
                                                    <input type="hidden" name="tipo" value="<?= Html::e($tipoItem) ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                                    <label for="historico-resposta-<?= (int) $item->id ?>">Nova resposta</label>
                                                    <textarea id="historico-resposta-<?= (int) $item->id ?>" name="mensagem" maxlength="500" required></textarea>
                                                    <button type="submit">Enviar resposta</button>
                                                    <p class="historico-resposta-feedback" role="status" aria-live="polite"></p>
                                                </form>
                                            <?php endif; ?>
                                        </section>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $inicio = $dados['total'] > 0 ? $dados['deslocamento'] + 1 : 0;
        $fim = min($dados['deslocamento'] + count($dados['itens']), $dados['total']);
        $temMaisDeUmaPagina = $dados['totalPaginas'] > 1;
        ?>
        <footer class="historico-paginacao">
            <?php if ($temMaisDeUmaPagina && $dados['pagina'] > 1): ?>
                <a class="historico-pagina-anterior" aria-label="Página anterior"
                   href="<?= URL ?>/historico?<?= Html::e($dados['queryPaginacao']) ?>&amp;pagina=<?= (int) ($dados['pagina'] - 1) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7M8 12h12" /></svg>
                </a>
            <?php else: ?>
                <button type="button" aria-label="Página anterior" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7M8 12h12" /></svg>
                </button>
            <?php endif; ?>

            <?php if ($temMaisDeUmaPagina && $dados['pagina'] < $dados['totalPaginas']): ?>
                <a class="historico-proxima-pagina" aria-label="Próxima página"
                   href="<?= URL ?>/historico?<?= Html::e($dados['queryPaginacao']) ?>&amp;pagina=<?= (int) ($dados['pagina'] + 1) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 5 7 7-7 7M4 12h12" /></svg>
                </a>
            <?php else: ?>
                <button type="button" aria-label="Próxima página" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 5 7 7-7 7M4 12h12" /></svg>
                </button>
            <?php endif; ?>

            <span><?= (int) $inicio ?> a <?= (int) $fim ?> de <?= (int) $dados['total'] ?> <?= Html::e($nomeTipoPlural) ?></span>
        </footer>
    </section>
</main>

<?php if (!empty($dados['temAcoes'])): ?>
<div class="historico-modal-fundo" id="historico-modal-fundo" hidden>
    <section class="historico-modal" role="dialog" aria-modal="true" aria-labelledby="historico-modal-titulo">
        <form id="historico-formulario" method="post" action="<?= URL ?>/historico/atualizar">
            <?= Csrf::campo() ?>
            <input type="hidden" name="tipo">
            <input type="hidden" name="id">

            <header class="historico-modal-cabecalho">
                <img src="<?= URL ?>/img/Save%20as.png" alt="" class="historico-modal-icone">
                <div>
                    <h2 id="historico-modal-titulo">Editar registro</h2>
                    <p>Altere os dados e salve as alterações. Moderadores autorizados também podem alterar o status.</p>
                </div>
                <button type="button" class="historico-modal-fechar" aria-label="Fechar edição">&times;</button>
            </header>

            <div class="historico-modal-acoes">
                <button type="button" class="historico-modal-excluir" aria-label="Excluir registro">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 3v6m4-6v6" /></svg>
                </button>
                <button type="button" class="historico-modal-alternar" aria-label="Alternar edição" aria-pressed="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 16.5V20h3.5L19 8.5 15.5 5 4 16.5Z" />
                        <path d="m13.8 6.7 3.5 3.5" />
                    </svg>
                </button>
            </div>

            <label for="historico-titulo">Título da demanda</label>
            <input type="text" id="historico-titulo" name="titulo" maxlength="120" required>

            <label for="historico-descricao">Explique resumidamente</label>
            <div class="historico-textarea-wrap">
                <textarea id="historico-descricao" name="descricao" maxlength="600" required></textarea>
                <span class="historico-contador" aria-live="polite">0/600</span>
            </div>

            <label for="historico-categoria">Categoria</label>
            <select id="historico-categoria" name="categoria" required></select>

            <div class="historico-status-editor" hidden>
                <label for="historico-status">Alterar status</label>
                <select id="historico-status" name="status"></select>
            </div>
            <p class="historico-status-leitura">Status: <span></span></p>
            <p class="historico-modal-feedback" role="status" aria-live="polite"></p>
            <button type="submit" class="historico-salvar">Salvar alterações</button>
        </form>
    </section>
</div>
<script src="<?= URL ?>/js/history.js?v=<?= (int) filemtime($historicoJs) ?>" defer></script>
<?php endif; ?>
