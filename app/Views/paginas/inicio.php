<main class="inicio">
    <h1 class="titulo"><?= htmlspecialchars($dados['titulo'] ?? 'Início') ?></h1>

    <section class="paineis">
        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="img/Plus.png" alt="Nova demanda">
                <h2>Nova Demanda</h2>
            </div>

            <p>Abra uma nova solicitação para<br>o Grêmio</p>
            <button>Criar demanda</button>
        </article>

        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="img/Activity%20History.png" alt="Minhas demandas">
                <h2>Minhas Demandas</h2>
            </div>

            <div class="resumos">
                <div><strong>12</strong><small>Enviadas</small></div>
                <div><strong>5</strong><small>Em Análise</small></div>
                <div><strong>7</strong><small>Resolvidas</small></div>
            </div>

            <a href="#" class="link-painel">Ver Todas</a>
        </article>

        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="img/Commercial.png" alt="Avisos do Grêmio">
                <h2>Avisos do Grêmio</h2>
            </div>

            <ul class="avisos">
                <li>Reunião do Grêmio - 01/07 às 18hrs na sala 21.</li>
                <li>Participe do evento consciência negra - 20/11 às 09hrs no auditório.</li>
                <li>Novos horários de atendimento.</li>
            </ul>
        </article>

        <article class="painel">
            <div class="painel-cabecalho">
                <img class="icone" src="img/Clock.png" alt="Demandas recentes">
                <h2>Demandas Recentes</h2>
            </div>

            <div class="demanda">
                <div>
                    <p>Ar-condicionado sala 24.</p>
                    <small>10/07/2026</small>
                </div>
                <span class="status resolvido">Resolvido</span>
            </div>

            <div class="demanda">
                <div>
                    <p>Internet do Bloco b</p>
                    <small>10/07/2026</small>
                </div>
                <span class="status aguardando">Aguardando</span>
            </div>
        </article>
    </section>
</main> 