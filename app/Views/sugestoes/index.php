<?php
$formDemanda = $dados['formDemanda'];
$formSugestao = $dados['formSugestao'];
$modalInicial = !empty($formDemanda['erros'])
    ? 'modal-nova-demanda'
    : (!empty($formSugestao['erros']) ? 'modal-nova-sugestao' : '');
$scriptPath = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'sugestoes.js';
?>
<?php if (!empty($dados['modoModerador'])): ?>
    <?php
    $indicadores = [
        ['chave' => 'resolvidas', 'rotulo' => 'Resolvidas', 'icone' => 'Timesheet.png', 'classe' => 'resolvidas'],
        ['chave' => 'em_andamento', 'rotulo' => 'Em andamento', 'icone' => 'Clock.png', 'classe' => 'andamento'],
        ['chave' => 'negadas', 'rotulo' => 'Negadas', 'icone' => 'Activity%20History.png', 'classe' => 'negadas'],
        ['chave' => 'em_analise', 'rotulo' => 'Em análise', 'icone' => 'NotificacaoCommercial.png', 'classe' => 'analise'],
    ];
    $paineis = [
        [
            'tipo' => 'demandas',
            'classe' => 'demanda',
            'titulo' => 'Demandas (Problemas)',
            'descricao' => 'Problemas ou situações que precisam de solução.',
            'icone' => 'Box%20Important.png',
            'resumo' => $dados['resumoDemandas'],
            'registros' => $dados['demandasRecentes'],
            'modal' => 'modal-nova-demanda',
            'acao' => 'Nova demanda',
            'tituloLista' => 'Demandas recentes',
        ],
        [
            'tipo' => 'sugestoes',
            'classe' => 'sugestao',
            'titulo' => 'Sugestões (Melhorias)',
            'descricao' => 'Ideias para melhorar o nosso campus.',
            'icone' => 'Light%20On.png',
            'resumo' => $dados['resumoSugestoes'],
            'registros' => $dados['sugestoesRecentes'],
            'modal' => 'modal-nova-sugestao',
            'acao' => 'Nova sugestão',
            'tituloLista' => 'Sugestões recentes',
        ],
    ];
    ?>
    <main class="inicio sugestoes-dashboard">
        <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>
        <div class="sugestoes-paineis">
            <?php foreach ($paineis as $painel): ?>
                <section class="sugestoes-painel sugestoes-painel-<?= Html::e($painel['classe']) ?>">
                    <header class="sugestoes-painel-cabecalho">
                        <img src="<?= URL ?>/img/<?= $painel['icone'] ?>" alt="">
                        <div>
                            <h2><?= Html::e($painel['titulo']) ?></h2>
                            <p><?= Html::e($painel['descricao']) ?></p>
                        </div>
                    </header>

                    <div class="sugestoes-painel-conteudo">
                        <div class="sugestoes-indicadores" aria-label="Resumo de status">
                            <?php foreach ($indicadores as $indicador): ?>
                                <article class="sugestoes-indicador sugestoes-indicador-<?= Html::e($indicador['classe']) ?>">
                                    <div class="sugestoes-indicador-rotulo">
                                        <img src="<?= URL ?>/img/<?= $indicador['icone'] ?>" alt="">
                                        <span><?= Html::e($indicador['rotulo']) ?></span>
                                    </div>
                                    <strong><?= (int) $painel['resumo'][$indicador['chave']] ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <section class="sugestoes-recentes" aria-labelledby="titulo-lista-<?= Html::e($painel['tipo']) ?>">
                            <header class="sugestoes-recentes-cabecalho">
                                <h3 id="titulo-lista-<?= Html::e($painel['tipo']) ?>"><?= Html::e($painel['tituloLista']) ?></h3>
                                <button type="button" class="sugestoes-nova sugestoes-nova-<?= Html::e($painel['classe']) ?>"
                                        data-modal="<?= Html::e($painel['modal']) ?>">
                                    + <?= Html::e($painel['acao']) ?>
                                </button>
                            </header>

                            <?php if ($painel['registros']): ?>
                                <ul class="sugestoes-lista">
                                    <?php foreach ($painel['registros'] as $registro): ?>
                                        <li class="sugestoes-registro">
                                            <div class="sugestoes-registro-dados">
                                                <span class="sugestoes-registro-titulo" title="<?= Html::e($registro->titulo) ?>">
                                                    <?= Html::e($registro->titulo) ?>
                                                </span>
                                                <small>
                                                    <?php if ($painel['tipo'] === 'sugestoes'): ?>
                                                        Enviado por: <?= Html::e($registro->autor) ?> ·
                                                    <?php endif; ?>
                                                    <?= Html::e($registro->criadoEmTexto) ?>
                                                </small>
                                            </div>
                                            <span class="sugestoes-status sugestoes-status-<?= Html::e($registro->statusClasse) ?>">
                                                <?= Html::e($registro->statusTexto) ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="sugestoes-lista-vazia">Nenhum registro por aqui ainda.</p>
                            <?php endif; ?>

                            <a class="sugestoes-ver-todas" href="<?= URL ?>/historico?tipo=<?= Html::e($painel['tipo']) ?>&amp;todas=1">
                                Ver todas
                            </a>
                        </section>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <div class="sugestoes-modal-fundo" id="modal-nova-demanda" data-modal-inicial="<?= $modalInicial === 'modal-nova-demanda' ? '1' : '0' ?>" hidden>
        <section class="sugestoes-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-form-demanda">
            <button type="button" class="sugestoes-modal-fechar" aria-label="Fechar formulário">&times;</button>
            <?php require APP . '/Views/sugestoes/form-demanda.php'; ?>
        </section>
    </div>
    <div class="sugestoes-modal-fundo" id="modal-nova-sugestao" data-modal-inicial="<?= $modalInicial === 'modal-nova-sugestao' ? '1' : '0' ?>" hidden>
        <section class="sugestoes-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-form-sugestao">
            <button type="button" class="sugestoes-modal-fechar" aria-label="Fechar formulário">&times;</button>
            <?php require APP . '/Views/sugestoes/form-sugestao.php'; ?>
        </section>
    </div>
    <script src="<?= URL ?>/js/sugestoes.js?v=<?= (int) filemtime($scriptPath) ?>" defer></script>
<?php else: ?>
    <main class="sugestoes">
        <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>
        <div class="cards">
            <?php require APP . '/Views/sugestoes/form-demanda.php'; ?>
            <?php require APP . '/Views/sugestoes/form-sugestao.php'; ?>
        </div>
    </main>
    <script src="<?= URL ?>/js/sugestoes.js?v=<?= (int) filemtime($scriptPath) ?>" defer></script>
<?php endif; ?>
