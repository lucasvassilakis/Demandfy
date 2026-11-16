<?php
/*
 * VIEW: NOTIFICAÇÕES
 * MUDANÇAS: lista vinda do banco; abas "Todas"/"Não lidas (n)" viram links com filtro;
 * "Marcar todas como lidas" é um formulário POST (com CSRF).
 */
$somenteNaoLidas = $dados['somenteNaoLidas'];
?>
<main class="notificacoes">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <section class="notificacoes-panel" aria-label="Notificações">
        <div class="notificacoes-header">
            <div class="notificacoes-tabs" role="tablist" aria-label="Filtros de notificações">
                <a class="tab<?= $somenteNaoLidas ? '' : ' active' ?>" href="<?= URL ?>/notificacoes">Todas</a>
                <a class="tab<?= $somenteNaoLidas ? ' active' : '' ?>" href="<?= URL ?>/notificacoes?filtro=nao-lidas">Não lidas <span>(<?= (int) $dados['naoLidas'] ?>)</span></a>
            </div>

            <form method="POST" action="<?= URL ?>/notificacoes/marcarTodas">
                <?= Csrf::campo() ?>
                <button type="submit" class="marcar-todas">✓ Marcar todas como lidas</button>
            </form>
        </div>

        <ul class="notificacoes-lista">
            <?php if (empty($dados['notificacoes'])): ?>
                <li class="notificacao-item"><p class="vazio">Nenhuma notificação por aqui.</p></li>
            <?php endif; ?>

            <?php foreach ($dados['notificacoes'] as $n): ?>
                <li class="notificacao-item<?= $n->lida ? '' : ' nao-lida' ?>">
                    <div class="notificacao-icone verde">
                        <img src="<?= URL ?>/img/NotificacaoCommercial.png" alt="">
                    </div>

                    <div class="notificacao-conteudo">
                        <h2><?= Html::e($n->titulo) ?></h2>
                        <p><?= Html::e($n->mensagem) ?></p>
                    </div>

                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</main>
