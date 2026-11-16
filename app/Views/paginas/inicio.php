<?php
/*
 * VIEW: INÍCIO (painel)
 * MUDANÇAS: os números, avisos e demandas recentes deixaram de ser textos fixos e
 * agora vêm do banco ($dados['resumo'], $dados['avisos'], $dados['recentes']).
 * Os atalhos de demandas levam ao formulário e ao histórico.
 */
$resumo = $dados['resumo'];
$ehModerador = !empty($dados['ehModerador']);
?>
<main class="inicio<?= $ehModerador ? ' inicio-com-estatisticas' : '' ?>">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <?php if ($ehModerador): ?>
        <div class="inicio-dashboard-moderador">
            <aside class="estatisticas-moderador" aria-label="Indicadores do Grêmio">
                <article class="estatistica-moderador estatistica-resolvidas">
                    <div class="estatistica-cabecalho">
                        <img src="<?= URL ?>/img/Timesheet.png" alt="">
                        <span>Demandas resolvidas</span>
                    </div>
                    <strong><?= (int) $dados['estatisticasModerador']['resolvidas'] ?></strong>
                </article>
                <article class="estatistica-moderador estatistica-andamento">
                    <div class="estatistica-cabecalho">
                        <img src="<?= URL ?>/img/Clock.png" alt="">
                        <span>Em andamento</span>
                    </div>
                    <strong><?= (int) $dados['estatisticasModerador']['em_andamento'] ?></strong>
                </article>
                <article class="estatistica-moderador estatistica-pendentes">
                    <div class="estatistica-cabecalho">
                        <img src="<?= URL ?>/img/Activity%20History.png" alt="">
                        <span>Pendentes</span>
                    </div>
                    <strong><?= (int) $dados['estatisticasModerador']['pendentes'] ?></strong>
                </article>
                <article class="estatistica-moderador estatistica-usuarios">
                    <div class="estatistica-cabecalho">
                        <img src="<?= URL ?>/img/User.png" alt="">
                        <span>Total de usuários</span>
                    </div>
                    <strong><?= (int) $dados['totalUsuarios'] ?></strong>
                </article>
            </aside>
    <?php endif; ?>

    <section class="paineis<?= $ehModerador ? ' paineis-moderador' : '' ?>">
        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="<?= URL ?>/img/Plus.png" alt="Nova demanda">
                <h2>Nova Demanda</h2>
            </div>

            <p>Abra uma nova solicitação para<br>o Grêmio</p>
            <!-- era <button> sem ação; agora é um link com o mesmo visual -->
            <a class="painel-botao" href="<?= URL ?>/sugestoes">Criar demanda</a>
        </article>

        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="<?= URL ?>/img/Activity%20History.png" alt="Minhas demandas">
                <h2>Minhas Demandas</h2>
            </div>

            <div class="resumos">
                <div><strong><?= (int) $resumo['total'] ?></strong><small>Enviadas</small></div>
                <div><strong><?= (int) $resumo['em_analise'] ?></strong><small>Em Análise</small></div>
                <div><strong><?= (int) $resumo['resolvida'] ?></strong><small>Resolvidas</small></div>
            </div>

            <a href="<?= URL ?>/historico?tipo=demandas" class="link-painel">Ver Histórico</a>
        </article>

        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="<?= URL ?>/img/Commercial.png" alt="Avisos do Grêmio">
                <h2>Avisos do Grêmio</h2>
            </div>

            <ul class="avisos-lista">
                <?php if (empty($dados['avisos'])): ?>
                    <li><?= $dados['avisosDisponiveis'] ? 'Nenhum aviso publicado.' : 'Avisos indisponíveis: a tabela não existe neste banco.' ?></li>
                <?php endif; ?>
                <?php foreach ($dados['avisos'] as $aviso): ?>
                    <li><?= Html::e($aviso->titulo) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="<?= URL ?>/img/Clock.png" alt="Demandas recentes">
                <h2>Demandas Recentes</h2>
            </div>

            <?php if (empty($dados['recentes'])): ?>
                <p>Você ainda não enviou nenhuma demanda.</p>
            <?php endif; ?>

            <?php foreach ($dados['recentes'] as $demanda): ?>
                <a class="demanda demanda-link"
                   href="<?= URL ?>/historico?tipo=demandas&amp;demanda=<?= (int) $demanda->id ?>#historico-item-demanda-<?= (int) $demanda->id ?>">
                    <div>
                        <p><?= Html::e($demanda->titulo) ?></p>
                        <small><?= Html::e(Checa::dataBr($demanda->criado_em)) ?></small>
                    </div>
                    <span class="status <?= Html::e(Demanda::STATUS_CLASSE[$demanda->status] ?? 'aguardando') ?>">
                        <?= Html::e(Demanda::STATUS[$demanda->status] ?? $demanda->status) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </article>
    </section>
    <?php if ($ehModerador): ?>
        </div>
    <?php endif; ?>
</main>
