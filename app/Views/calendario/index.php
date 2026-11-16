<?php
/*
 * VIEW: CALENDÁRIO
 * MUDANÇAS: a grade do mês e os eventos vêm do controller/banco (antes era só o mês
 * de Agosto de 2026 escrito à mão). Os botões ‹ › e "Hoje" são links (?mes=AAAA-MM);
 * clicar em um dia mostra os eventos dele em "Eventos do dia".
 */
$corCard = ['verde' => 'verde', 'azul' => 'azul', 'amarelo' => 'amarelo'];
$corPorCategoria = [
    'Importante' => 'amarelo',
    'Evento do Grêmio' => 'verde',
    'Evento da Escola' => 'azul',
    'Feriado' => 'amarelo',
    'Reunião / Aula no AVA' => 'azul',
];
$nomesDias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
$calendarJs = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'calendar.js';
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
                                        <span class="evento evento-<?= Html::e($corCard[$corPorCategoria[$ev->categoria] ?? 'verde']) ?>"></span>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="eventos-painel" aria-label="Eventos do dia"
               data-url="<?= Html::e(URL) ?>"
               data-data="<?= Html::e($dados['dataIso']) ?>"
               data-data-texto="<?= Html::e($dados['dataSelecionada']) ?>">
            <h2>Eventos do dia</h2>
            <p class="eventos-data"><?= Html::e($dados['dataSelecionada']) ?></p>

            <?php if (!empty($dados['podeGerenciar'])): ?>
                <div class="eventos-acoes">
                    <button type="button" class="evento-adicionar" id="evento-adicionar">+ Adicionar item</button>
                    <button type="button" class="evento-gerenciar" id="evento-gerenciar">✎ Editar item</button>
                </div>
            <?php endif; ?>

            <?php if (empty($dados['eventosDoDia'])): ?>
                <p class="vazio">Nenhum evento neste dia.</p>
            <?php endif; ?>

            <?php foreach ($dados['eventosDoDia'] as $ev): ?>
                <?php
                $horario = '';
                if (!empty($ev->hora_inicio)) {
                    $horario = substr($ev->hora_inicio, 0, 5);
                    if (!empty($ev->hora_fim)) { $horario .= ' - ' . substr($ev->hora_fim, 0, 5); }
                } elseif (!empty($ev->hora_fim)) {
                    $horario = 'Até ' . substr($ev->hora_fim, 0, 5);
                }
                $cor = $corPorCategoria[$ev->categoria] ?? 'verde';
                ?>
                <div class="evento-card evento-card-<?= Html::e($corCard[$cor]) ?>">
                    <div class="evento-card-topo">
                        <strong><?= Html::e($ev->titulo) ?></strong>
                        <span><?= Html::e($horario) ?></span>
                    </div>
                    <p><?= Html::e($ev->descricao) ?></p>
                </div>
            <?php endforeach; ?>
        </aside>
    </div>

    <?php if (!empty($dados['podeGerenciar'])): ?>
        <div class="calendario-modal-fundo" id="eventos-lista-fundo" hidden>
            <section class="eventos-modal eventos-modal-lista" role="dialog" aria-modal="true" aria-labelledby="eventos-lista-titulo">
                <header class="eventos-modal-cabecalho">
                    <span class="eventos-modal-icone" aria-hidden="true">
                        <svg viewBox="0 0 32 32"><path d="M5 22.5V27h4.5L26 10.5 21.5 6 5 22.5Z"/><path d="m19.8 7.7 4.5 4.5"/></svg>
                    </span>
                    <div>
                        <h2 id="eventos-lista-titulo">Editar eventos</h2>
                        <p>Remova, altere ou adicione eventos.</p>
                    </div>
                    <button type="button" class="eventos-modal-fechar" data-fechar-modal aria-label="Fechar">&times;</button>
                </header>
                <p class="eventos-modal-data"><?= Html::e($dados['dataSelecionada']) ?></p>
                <p class="eventos-arraste-ajuda">Arraste pelo ícone de linhas para mudar a ordem.</p>

                <div class="eventos-editor-lista" id="eventos-editor-lista">
                    <?php if (empty($dados['eventosDoDia'])): ?>
                        <p class="eventos-editor-vazio">Nenhum evento neste dia.</p>
                    <?php endif; ?>
                    <?php foreach ($dados['eventosDoDia'] as $ev): ?>
                        <?php
                        $horario = '';
                        if (!empty($ev->hora_inicio)) {
                            $horario = substr($ev->hora_inicio, 0, 5);
                            if (!empty($ev->hora_fim)) { $horario .= ' - ' . substr($ev->hora_fim, 0, 5); }
                        } elseif (!empty($ev->hora_fim)) {
                            $horario = 'Até ' . substr($ev->hora_fim, 0, 5);
                        }
                        $cor = $corPorCategoria[$ev->categoria] ?? 'verde';
                        ?>
                        <div class="evento-gerenciavel" data-id="<?= (int) $ev->id ?>">
                            <div class="evento-card evento-card-<?= Html::e($corCard[$cor]) ?>">
                                <div class="evento-card-topo">
                                    <strong><?= Html::e($ev->titulo) ?></strong>
                                    <span><?= Html::e($horario) ?></span>
                                </div>
                                <p><?= Html::e($ev->descricao) ?></p>
                            </div>
                            <div class="evento-gerenciavel-acoes">
                                <button type="button" class="evento-item-editar"
                                        aria-label="Editar evento: <?= Html::e($ev->titulo) ?>"
                                        data-id="<?= (int) $ev->id ?>"
                                        data-titulo="<?= Html::e($ev->titulo) ?>"
                                        data-descricao="<?= Html::e($ev->descricao) ?>"
                                        data-categoria-id="<?= (int) $ev->categoria_id ?>"
                                        data-hora-inicio="<?= Html::e($ev->hora_inicio ?? '') ?>"
                                        data-hora-fim="<?= Html::e($ev->hora_fim ?? '') ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16.5V20h3.5L19 8.5 15.5 5 4 16.5Z"/><path d="m13.8 6.7 3.5 3.5"/></svg>
                                </button>
                                <button type="button" class="evento-item-remover" aria-label="Remover evento: <?= Html::e($ev->titulo) ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 3v6m4-6v6"/></svg>
                                </button>
                                <button type="button" class="evento-item-ordenar" draggable="true"
                                        aria-label="Arrastar para mudar a ordem de <?= Html::e($ev->titulo) ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p class="eventos-modal-feedback" id="eventos-lista-feedback" role="status" aria-live="polite"></p>
                <footer class="eventos-modal-rodape">
                    <button type="button" class="eventos-modal-adicionar" id="eventos-lista-adicionar">+ Adicionar evento</button>
                    <button type="button" class="eventos-modal-salvar-ordem" id="eventos-salvar-ordem" disabled>Salvar ordem</button>
                </footer>
            </section>
        </div>

        <div class="calendario-modal-fundo calendario-modal-form-fundo" id="evento-form-fundo" hidden>
            <section class="eventos-modal eventos-modal-form" role="dialog" aria-modal="true" aria-labelledby="evento-form-titulo">
                <form id="evento-form" method="post" action="<?= URL ?>/calendario/criarEvento">
                    <?= Csrf::campo() ?>
                    <input type="hidden" name="id">
                    <input type="hidden" name="data" value="<?= Html::e($dados['dataIso']) ?>">
                    <header class="eventos-modal-cabecalho">
                        <span class="eventos-modal-icone" aria-hidden="true">
                            <svg viewBox="0 0 32 32"><path d="M5 22.5V27h4.5L26 10.5 21.5 6 5 22.5Z"/><path d="m19.8 7.7 4.5 4.5"/></svg>
                        </span>
                        <div>
                            <h2 id="evento-form-titulo">Adicionar evento</h2>
                            <p id="evento-form-subtitulo"><?= Html::e($dados['dataSelecionada']) ?></p>
                        </div>
                        <button type="button" class="eventos-modal-fechar" data-fechar-modal aria-label="Fechar">&times;</button>
                    </header>

                    <label for="evento-titulo">Título</label>
                    <input type="text" id="evento-titulo" name="titulo" maxlength="200" required>

                    <label for="evento-descricao">Descrição</label>
                    <textarea id="evento-descricao" name="descricao" maxlength="600" rows="4"></textarea>

                    <label for="evento-categoria">Categoria</label>
                    <select id="evento-categoria" name="categoria_id" required>
                        <?php foreach ($dados['categoriasEvento'] as $categoria): ?>
                            <option value="<?= (int) $categoria->id ?>"><?= Html::e($categoria->nome) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="evento-horarios">
                        <label for="evento-hora-inicio">Horário de início
                            <input type="time" id="evento-hora-inicio" name="hora_inicio">
                        </label>
                        <label for="evento-hora-fim">Horário de término
                            <input type="time" id="evento-hora-fim" name="hora_fim">
                        </label>
                    </div>
                    <p class="eventos-modal-feedback" id="evento-form-feedback" role="status" aria-live="polite"></p>
                    <footer class="eventos-modal-rodape">
                        <button type="button" class="eventos-modal-cancelar" data-fechar-modal>Cancelar</button>
                        <button type="submit" class="eventos-modal-salvar" id="evento-form-salvar">Salvar evento</button>
                    </footer>
                </form>
            </section>
        </div>
        <script src="<?= URL ?>/js/calendar.js?v=<?= (int) filemtime($calendarJs) ?>" defer></script>
    <?php endif; ?>
</main>
