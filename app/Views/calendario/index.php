<?php
/*
 * VIEW: CALENDÁRIO
 * MUDANÇAS: a grade do mês e os eventos vêm do controller/banco (antes era só o mês
 * de Agosto de 2026 escrito à mão). Os botões ‹ › e "Hoje" são links (?mes=AAAA-MM);
 * clicar em um dia mostra os eventos dele em "Eventos do dia".
 */
$corCard = ['verde' => 'verde', 'azul' => 'azul', 'amarelo' => 'amarelo'];
$nomesDias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
?>
<main class="calendario-pagina">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>
    <div class="calendario-layout">
        <div class="calendario">
            <div class="calendario-cabecalho">
                <div class="calendario-controles" aria-label="Navegação entre meses">
                    <a class="calendario-navegacao calendario-anterior" href="<?= URL ?>/calendario?mes=<?= Html::e($dados['mesAnterior']) ?>" aria-label="Mês anterior">
                        <img src="<?= URL ?>/img/Right.png" alt="">
                    </a>
                    <a class="calendario-navegacao calendario-proximo" href="<?= URL ?>/calendario?mes=<?= Html::e($dados['mesSeguinte']) ?>" aria-label="Próximo mês">
                        <img src="<?= URL ?>/img/Right.png" alt="">
                    </a>
                </div>
                <h2 class="calendario-mes"><?= Html::e($dados['nomeMes']) ?></h2>
                <a class="calendario-hoje" href="<?= URL ?>/calendario">Hoje</a>
            </div>

            <div class="calendario-grade" role="grid" aria-label="Calendário de <?= Html::e($dados['nomeMes']) ?>">
                <?php foreach ($nomesDias as $nome): ?>
                    <div class="calendario-dia-semana" role="columnheader"><?= $nome ?></div>
                <?php endforeach; ?>

                <?php foreach ($dados['celulas'] as $celula): ?>
                    <?php if ($celula['outroMes']): ?>
                        <!-- dias do mês anterior/seguinte (só preenchem a semana) -->
                        <div class="calendario-data outro-mes" role="gridcell"><?= (int) $celula['dia'] ?></div>
                    <?php else: ?>
                        <?php
                        $classes = 'calendario-data celula-link';
                        if (!empty($celula['eventos'])) { $classes .= ' com-eventos'; }
                        if (!empty($celula['hoje'])) { $classes .= ' hoje'; }
                        if (!empty($celula['selecionado'])) { $classes .= ' selecionado'; }
                        ?>
                        <a class="<?= $classes ?>" role="gridcell"
                           href="<?= URL ?>/calendario?mes=<?= Html::e($dados['mesAtual']) ?>&amp;dia=<?= (int) $celula['dia'] ?>">
                            <span><?= (int) $celula['dia'] ?></span>
                            <?php if (!empty($celula['eventos'])): ?>
                                <span class="calendario-eventos" aria-label="<?= count($celula['eventos']) ?> evento(s)">
                                    <?php foreach (array_slice($celula['eventos'], 0, 3) as $ev): ?>
                                        <span class="evento evento-<?= Html::e($corCard[$ev->cor] ?? 'verde') ?>"></span>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="eventos-painel" aria-label="Eventos do dia">
            <h2>Eventos do dia</h2>
            <p class="eventos-data"><?= Html::e($dados['dataSelecionada']) ?></p>

            <?php if (empty($dados['eventosDoDia'])): ?>
                <p class="vazio">Nenhum evento neste dia.</p>
            <?php endif; ?>

            <?php foreach ($dados['eventosDoDia'] as $ev): ?>
                <?php
                $horario = '';
                if (!empty($ev->hora_inicio)) {
                    $horario = substr($ev->hora_inicio, 0, 5);
                    if (!empty($ev->hora_fim)) { $horario .= ' - ' . substr($ev->hora_fim, 0, 5); }
                }
                ?>
                <div class="evento-card evento-card-<?= Html::e($corCard[$ev->cor] ?? 'verde') ?>">
                    <div class="evento-card-topo">
                        <strong><?= Html::e($ev->titulo) ?></strong>
                        <span><?= Html::e($horario) ?></span>
                    </div>
                    <p><?= Html::e($ev->descricao) ?></p>
                </div>
            <?php endforeach; ?>
        </aside>
    </div>
</main>
