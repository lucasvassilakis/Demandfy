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
<main class="inicio">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

    <section class="avisos">

        <nav class="abas">
            <a class="aba<?= $tipo === '' ? ' ativa' : '' ?>" href="<?= linkAvisos('', 1) ?>">Todos</a>
            <a class="aba<?= $tipo === 'importante' ? ' ativa' : '' ?>" href="<?= linkAvisos('importante', 1) ?>">Importantes</a>
            <a class="aba<?= $tipo === 'informativo' ? ' ativa' : '' ?>" href="<?= linkAvisos('informativo', 1) ?>">Informativos</a>
            <a class="aba<?= $tipo === 'lembrete' ? ' ativa' : '' ?>" href="<?= linkAvisos('lembrete', 1) ?>">Lembretes</a>

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
                </article>
            <?php endforeach; ?>
        </div>

    </section>
</main>
