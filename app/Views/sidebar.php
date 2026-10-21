<?php
/*
 * SIDEBAR (menu lateral)
 * ------------------------------------------------------------------
 * MUDANÇAS:
 *  - Os itens do menu agora são montados por um array + foreach (antes eram 7 blocos repetidos).
 *  - O item "ativo" é descoberto pelo 1º pedaço da URL (ex.: /demandas/ver/3 -> "demandas").
 *  - Imagens com caminho absoluto (<?= URL ?>/img/...), para funcionar em qualquer URL.
 *  - O botão "Sair" agora chama /sair (antes apontava para /cadastro).
 *  - Mostra o nome do usuário logado no tooltip do item "Perfil".
 */
$urlAtual = trim((string) filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL), '/');
$secaoAtual = $urlAtual === '' ? '' : explode('/', $urlAtual)[0];

// "demandas" faz parte da área RDD, então destaca o item RDD - Sugestões.
if ($secaoAtual === 'demandas') {
    $secaoAtual = 'sugestoes';
}

// [caminho na URL, texto do menu, título (tooltip), imagem]
$itensMenu = [
    ['',              'Início',          'Página Inicial', 'home.png'],
    ['sugestoes',     'RDD - Sugestões', 'Sugestões',      'idea.png'],
    ['calendario',    'Calendário',      'Calendário',     'calendar.png'],
    ['avisos',        'Avisos',          'Avisos',         'megaphone.png'],
    ['notificacoes',  'Notificações',    'Notificações',   'notification.png'],
    ['perfil',        'Perfil',          'Perfil de ' . Sessao::usuarioNome(), 'perfil-de-usuario.png'],
    ['configuracoes', 'Configurações',   'Configurações',  'setting.png'],
];
?>
<header>
    <div class="container">
        <nav class="sidebar">
            <a class="navbar-brand" href="<?= URL ?>"><img src="<?= URL ?>/img/Demandfy.png" alt="Demandfy"></a>
            <div class="navbar">
                <div class="search-container">
                    <input type="text" placeholder="Pesquisa" class="search-input">
                    <img class="search-icon" src="<?= URL ?>/img/search.png" alt="Buscar">
                </div>
                <ul class="nav-list">
                    <?php foreach ($itensMenu as $item): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $secaoAtual === $item[0] ? ' active' : '' ?>"
                               href="<?= URL . ($item[0] === '' ? '' : '/' . $item[0]) ?>"
                               data-tooltip="tooltip" title="<?= Html::e($item[2]) ?>">
                                <img class="nav-img" src="<?= URL ?>/img/<?= Html::e($item[3]) ?>" alt="">
                                <span class="nav-text"><?= Html::e($item[1]) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="logout-container">
                <a class="nav-link logout-link" href="<?= URL ?>/sair" data-tooltip="tooltip" title="Sair"><img class="nav-img" src="<?= URL ?>/img/logout.png" alt=""><span class="nav-text logout-text">Sair</span></a>
            </div>
        </nav>
    </div>
</header>
