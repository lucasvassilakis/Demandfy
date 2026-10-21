<?php
/*
 * VIEW: DETALHE DE UMA DEMANDA (equivale a posts/ver.php do framework2026).
 */
$d = $dados['demanda'];
?>
<main class="inicio">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <section class="conteudo-pagina">
        <div class="caixa-lista">
            <div class="caixa-lista-topo">
                <a class="link-voltar" href="<?= URL ?>/demandas">&larr; Voltar</a>
                <span class="status <?= Html::e(Demanda::STATUS_CLASSE[$d->status] ?? 'aguardando') ?>">
                    <?= Html::e(Demanda::STATUS[$d->status] ?? $d->status) ?>
                </span>
            </div>

            <h2 class="detalhe-titulo"><?= Html::e($d->titulo) ?></h2>
            <p class="detalhe-meta">
                <?= Html::e(Demanda::CATEGORIAS[$d->categoria] ?? $d->categoria) ?>
                · Enviada em <?= Html::e(Checa::dataBr($d->criado_em, true)) ?>
            </p>
            <!-- nl2br mantém as quebras de linha digitadas; o texto é escapado antes -->
            <p class="detalhe-texto"><?= nl2br(Html::e($d->descricao)) ?></p>

            <form method="POST" action="<?= URL ?>/demandas/excluir/<?= (int) $d->id ?>"
                  onsubmit="return confirm('Excluir esta demanda?');">
                <?= Csrf::campo() ?>
                <button type="submit" class="botao-excluir">Excluir demanda</button>
            </form>
        </div>
    </section>
</main>
