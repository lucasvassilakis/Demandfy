<?php
/*
 * VIEW: MINHAS DEMANDAS (lista)
 * Equivale à view posts/index.php do framework2026. Cada demanda mostra título,
 * categoria, data, status e um botão para excluir (formulário POST com CSRF).
 */
?>
<main class="inicio">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <section class="conteudo-pagina">
        <div class="caixa-lista">
            <div class="caixa-lista-topo">
                <span><?= count($dados['demandas']) ?> demanda(s)</span>
                <a class="botao-verde" href="<?= URL ?>/sugestoes">+ Nova demanda</a>
            </div>

            <?php if (empty($dados['demandas'])): ?>
                <p class="vazio">Você ainda não enviou nenhuma demanda.</p>
            <?php endif; ?>

            <?php foreach ($dados['demandas'] as $demanda): ?>
                <div class="linha-demanda">
                    <a class="linha-demanda-info" href="<?= URL ?>/demandas/ver/<?= (int) $demanda->id ?>">
                        <strong>#<?= (int) $demanda->id ?> · <?= Html::e($demanda->titulo) ?></strong>
                        <small>
                            <?= Html::e(Demanda::CATEGORIAS[$demanda->categoria] ?? $demanda->categoria) ?>
                            · <?= Html::e(Checa::dataBr($demanda->criado_em, true)) ?>
                        </small>
                    </a>

                    <span class="status <?= Html::e(Demanda::STATUS_CLASSE[$demanda->status] ?? 'aguardando') ?>">
                        <?= Html::e(Demanda::STATUS[$demanda->status] ?? $demanda->status) ?>
                    </span>

                    <form method="POST" action="<?= URL ?>/demandas/excluir/<?= (int) $demanda->id ?>"
                          onsubmit="return confirm('Excluir esta demanda?');">
                        <?= Csrf::campo() ?>
                        <button type="submit" class="botao-excluir" title="Excluir">Excluir</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
