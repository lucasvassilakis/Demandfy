<main class="notificacoes">
    <h1 class="titulo"><?= htmlspecialchars($dados['titulo'] ?? 'Notificação') ?></h1>

    <section class="notificacoes-panel" aria-label="Notificações">
        <div class="notificacoes-header">
            <div class="notificacoes-tabs" role="tablist" aria-label="Filtros de notificações">
                <button type="button" class="tab active" role="tab" aria-selected="true">Todas</button>
                <button type="button" class="tab" role="tab" aria-selected="false">Não lidas <span>(4)</span></button>
            </div>

            <button type="button" class="marcar-todas">✓ Marcar todas como lidas</button>
        </div>

        <ul class="notificacoes-lista">
            <li class="notificacao-item">
                <div class="notificacao-icone verde">
                    <img src="img/NotificacaoCommercial.png" alt="Aviso">
                </div>

                <div class="notificacao-conteudo">
                    <h2>Novo aviso publicado</h2>
                    <p>Foi publicado um novo aviso: "Manutenção da internet".</p>
                </div>

                <time>Há 10 minutos</time>
            </li>

            <li class="notificacao-item">
                <div class="notificacao-icone verde">
                    <img src="img/NotificacaoShowProperty.png" alt="Atualização">
                </div>

                <div class="notificacao-conteudo">
                    <h2>Atualização na sua demanda</h2>
                    <p>Sua demanda #20 foi atualizada para o status "Em andamento".</p>
                </div>

                <time>Há 2 horas</time>
            </li>

            <li class="notificacao-item">
                <div class="notificacao-icone verde">
                    <img src="img/NotificacaoSchedule.png" alt="Lembrete">
                </div>

                <div class="notificacao-conteudo">
                    <h2>Lembrete de evento</h2>
                    <p>Reunião do Grêmio Estudantil hoje às 18h na sala 21.</p>
                </div>

                <time>Há 5 horas</time>
            </li>

            <li class="notificacao-item">
                <div class="notificacao-icone verde">
                    <img src="img/NotificacaoIdea.png" alt="Resposta">
                </div>

                <div class="notificacao-conteudo">
                    <h2>Resposta no RDD</h2>
                    <p>Sua sugestão no RDD recebeu uma resposta. Confira!</p>
                </div>

                <time>Há 4 dias</time>
            </li>
        </ul>
    </section>
</main>
