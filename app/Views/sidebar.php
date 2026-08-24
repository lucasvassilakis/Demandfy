<?php
$urlAtual = trim((string) filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL), '/');
$inicioAtivo = $urlAtual === '';
$sugestoesAtiva = $urlAtual === 'sugestoes' || $urlAtual === 'paginas/sugestoes';
?>
<header>
    <div class="container">
        <nav class="sidebar">
            <a class="navbar-brand" href="<?=URL?>"><img src="public\img\Demandfy.png" alt=""></a>
            <div class="navbar">
                <div class="search-container">
                    <input type="text" placeholder="Pesquisa" class="search-input">
                    <img class="search-icon" src="public\img\search.png" alt="Buscar">
                </div>
                <ul class="nav-list">   
                    <li class="nav-item">
                        <a class="nav-link<?= $inicioAtivo ? ' active' : '' ?>" href="<?=URL?>" data-tooltip="tooltip" title="Página Inicial"><img class="nav-img" src="public\img\home.png" alt=""><span class="nav-text">Início</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= $sugestoesAtiva ? ' active' : '' ?>" href="<?=URL?>/sugestoes" data-tooltip="tooltip" title="Sugestões"><img class="nav-img" src="public\img\idea.png" alt=""><span class="nav-text">RDD - Sugestões</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>" data-tooltip="tooltip" title="Calendário"><img class="nav-img" src="public\img\calendar.png" alt=""><span class="nav-text">Calendário</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>" data-tooltip="tooltip" title="Avisos"><img class="nav-img" src="public\img\megaphone.png" alt=""><span class="nav-text">Avisos</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>" data-tooltip="tooltip" title="Notificações"><img class="nav-img" src="public\img\notification.png" alt=""><span class="nav-text">Notificações</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>" data-tooltip="tooltip" title="Perfil"><img class="nav-img" src="public\img\perfil-de-usuario.png" alt=""><span class="nav-text">Perfil</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>" data-tooltip="tooltip" title="Configurações"><img class="nav-img" src="public\img\setting.png" alt=""><span class="nav-text">Configurações</span></a>
                    </li>
                </ul>
            </div>
            <div class="logout-container">
                <a class="nav-link logout-link" href="<?=URL?>logout" data-tooltip="tooltip" title="Sair"><img class="nav-img" src="public\img\logout.png" alt=""><span class="nav-text logout-text">Sair</span></a>
            </div>
        </nav>
    </div>
</header>