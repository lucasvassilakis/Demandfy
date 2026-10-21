<?php
/*
 * VIEW: INÍCIO (painel)
 * MUDANÇAS: os números, avisos e demandas recentes deixaram de ser textos fixos e
 * agora vêm do banco ($dados['resumo'], $dados['avisos'], $dados['recentes']).
 * "Criar demanda" e "Ver Todas" agora levam às páginas reais.
 */
$resumo = $dados['resumo'];
?>
<main class="inicio">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <section class="paineis">
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

            <a href="<?= URL ?>/demandas" class="link-painel">Ver Todas</a>
        </article>

        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="<?= URL ?>/img/Commercial.png" alt="Avisos do Grêmio">
                <h2>Avisos do Grêmio</h2>
            </div>

            <ul class="avisos-lista">
                <?php if (empty($dados['avisos'])): ?>
                    <li>Nenhum aviso publicado.</li>
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
                <a class="demanda demanda-link" href="<?= URL ?>/demandas/ver/<?= (int) $demanda->id ?>">
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
</main>
