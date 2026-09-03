<main class="sugestoes">
    <h1 class="titulo"><?= htmlspecialchars($dados['titulo'] ?? 'Sugestões') ?></h1>
    <div class="cards">
    <form class="card demand" id="form-demanda">
        <div class="card-header">   
        <img class="card-icon" src="img/Box%20Important.png" alt="">
        <div>
            <h2>Nova Demanda</h2>
            <p>Informe um problema ou situação que precise de atenção.</p>
        </div>
        </div>

        <label for="titulo-demanda">Título da demanda</label>
        <input type="text" id="titulo-demanda" placeholder="Ex: Ar-condicionado da sala 24 quebrado" maxlength="120" required>

        <label for="desc-demanda">Descreva o problema</label>
        <div class="textarea-wrap">
        <textarea id="desc-demanda" maxlength="600" placeholder="Explique o que está acontecendo, onde e como podemos identificar..." required></textarea>
        <span class="char-count" id="count-demanda">0/600</span>
        </div>

        <label for="cat-demanda">Categoria</label>
        <select id="cat-demanda" required>
        <option value="" disabled selected>Selecione uma categoria</option>
        <option value="infraestrutura">Infraestrutura</option>
        <option value="limpeza">Limpeza</option>
        <option value="ti">TI / Sistemas</option>
        <option value="seguranca">Segurança</option>
        <option value="outros">Outros</option>
        </select>

        <button type="submit" class="submit-btn btn-demand">Enviar demanda</button>
    </form>

    <form class="card suggestion" id="form-sugestao">
        <div class="card-header">
        <img class="card-icon" src="img/Light%20On.png" alt="">
        <div>
            <h2>Nova Sugestão</h2>
            <p>Compartilhe suas ideias para melhorar o nosso campus.</p>
        </div>
        </div>

        <label for="titulo-sugestao">Título da sugestão</label>
        <input type="text" id="titulo-sugestao" placeholder="Ex: Mais cadeiras em dia de contraturno" maxlength="120" required>

        <label for="desc-sugestao">Descreva sua demanda</label>
        <div class="textarea-wrap">
        <textarea id="desc-sugestao" maxlength="600" placeholder="Conte sua ideia e como ela pode melhorar a experiência de todos..." required></textarea>
        <span class="char-count" id="count-sugestao">0/600</span>
        </div>

        <label for="cat-sugestao">Categoria</label>
        <select id="cat-sugestao" required>
        <option value="" disabled selected>Selecione uma categoria</option>
        <option value="infraestrutura">Infraestrutura</option>
        <option value="eventos">Eventos</option>
        <option value="academico">Acadêmico</option>
        <option value="bem-estar">Bem-estar</option>
        <option value="outros">Outros</option>
        </select>

        <button type="submit" class="submit-btn btn-suggestion">Enviar sugestão</button>
    </form>
    </div>
</main>