<main class="calendario-pagina">
    <h1 class="titulo"><?= htmlspecialchars($dados['titulo'] ?? 'Calendário') ?></h1>
	<div class="calendario-layout">
		<div class="calendario">
        <div class="calendario-cabecalho">
            <div class="calendario-controles" aria-label="Navegação entre meses">
                <button type="button" class="calendario-navegacao calendario-anterior" aria-label="Mês anterior">
                    <img src="img/Right.png" alt="">
                </button>
                <button type="button" class="calendario-navegacao calendario-proximo" aria-label="Próximo mês">
                    <img src="img/Right.png" alt="">
                </button>
            </div>
            <h2 class="calendario-mes">Agosto 2026</h2>
            <button type="button" class="calendario-hoje">Hoje</button>
        </div>
		<div class="calendario-grade" role="grid" aria-label="Calendário de Agosto de 2026">
			<div class="calendario-dia-semana" role="columnheader">Dom</div>
			<div class="calendario-dia-semana" role="columnheader">Seg</div>
			<div class="calendario-dia-semana" role="columnheader">Ter</div>
			<div class="calendario-dia-semana" role="columnheader">Qua</div>
			<div class="calendario-dia-semana" role="columnheader">Qui</div>
			<div class="calendario-dia-semana" role="columnheader">Sex</div>
			<div class="calendario-dia-semana" role="columnheader">Sáb</div>

			<div class="calendario-data outro-mes" role="gridcell">26</div>
			<div class="calendario-data outro-mes" role="gridcell">27</div>
			<div class="calendario-data outro-mes" role="gridcell">28</div>
			<div class="calendario-data outro-mes" role="gridcell">29</div>
			<div class="calendario-data outro-mes" role="gridcell">30</div>
			<div class="calendario-data outro-mes" role="gridcell">31</div>
			<div class="calendario-data" role="gridcell">1</div>

			<div class="calendario-data" role="gridcell">2</div>
			<div class="calendario-data" role="gridcell">3</div>
			<div class="calendario-data" role="gridcell">4</div>
			<div class="calendario-data" role="gridcell">5</div>
			<div class="calendario-data" role="gridcell">6</div>
			<div class="calendario-data" role="gridcell">7</div>
			<div class="calendario-data" role="gridcell">8</div>

			<div class="calendario-data" role="gridcell">9</div>
			<div class="calendario-data" role="gridcell">10</div>
			<div class="calendario-data" role="gridcell">11</div>
			<div class="calendario-data" role="gridcell">12</div>
			<div class="calendario-data" role="gridcell">13</div>
			<div class="calendario-data" role="gridcell">14</div>
			<div class="calendario-data" role="gridcell">15</div>

			<div class="calendario-data" role="gridcell">16</div>
			<div class="calendario-data" role="gridcell">17</div>
			<div class="calendario-data" role="gridcell">18</div>
			<div class="calendario-data" role="gridcell">19</div>
			<div class="calendario-data" role="gridcell">20</div>
			<div class="calendario-data" role="gridcell">21</div>
			<div class="calendario-data com-eventos" role="gridcell">
				<span>22</span>
				<span class="calendario-eventos" aria-label="Três eventos">
					<span class="evento evento-verde"></span>
					<span class="evento evento-azul"></span>
					<span class="evento evento-amarelo"></span>
				</span>
			</div>

			<div class="calendario-data" role="gridcell">23</div>
			<div class="calendario-data" role="gridcell">24</div>
			<div class="calendario-data" role="gridcell">25</div>
			<div class="calendario-data" role="gridcell">26</div>
			<div class="calendario-data" role="gridcell">27</div>
			<div class="calendario-data" role="gridcell">28</div>
			<div class="calendario-data com-eventos" role="gridcell">
				<span>29</span>
				<span class="calendario-eventos" aria-label="Três eventos">
					<span class="evento evento-verde"></span>
					<span class="evento evento-azul"></span>
					<span class="evento evento-amarelo"></span>
				</span>
			</div>

			<div class="calendario-data" role="gridcell">30</div>
			<div class="calendario-data" role="gridcell">31</div>
			<div class="calendario-data outro-mes" role="gridcell">1</div>
			<div class="calendario-data outro-mes" role="gridcell">2</div>
			<div class="calendario-data outro-mes" role="gridcell">3</div>
			<div class="calendario-data outro-mes" role="gridcell">4</div>
			<div class="calendario-data outro-mes" role="gridcell">5</div>
		</div>
	    </div>

		<aside class="eventos-painel" aria-label="Eventos do dia">
			<h2>Eventos do dia</h2>
			<p class="eventos-data">22 de Agosto de 2026</p>

			<div class="evento-card evento-card-verde">
				<div class="evento-card-topo">
					<strong>Descomplica IF</strong>
					<span>08:00 - 17:40</span>
				</div>
				<p>Comemoração ao dia do estudante.</p>
			</div>

			<div class="evento-card evento-card-azul">
				<div class="evento-card-topo">
					<strong>Apresentação dos grupos</strong>
					<span>10:00</span>
				</div>
				<p>Apresentação dos grupos do descomplica IF.</p>
			</div>

			<div class="evento-card evento-card-amarelo">
				<div class="evento-card-topo">
					<strong>Contraturno</strong>
					<span>08:00 - 16:20</span>
				</div>
				<p>Contraturno para o descomplica IF.</p>
			</div>

			<a class="eventos-link" href="#">Ver todos os eventos</a>
		</aside>
	</div>
</main>
