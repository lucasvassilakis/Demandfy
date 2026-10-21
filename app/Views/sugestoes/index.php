<?php
/*
 * VIEW: RDD - SUGESTÕES (dois formulários)
 * MUDANÇAS: os campos agora têm atributo name e os formulários são enviados de verdade
 * (POST) para /demandas/criar e /sugestoes/criar, com token CSRF. Erros aparecem abaixo
 * dos campos e o que foi digitado é mantido. As categorias vêm dos Models (Demanda e
 * Sugestao), os mesmos usados na validação. Foi incluído o contador de caracteres
 * (0/600), que existia só no HTML, e a lista das últimas sugestões enviadas.
 */
$fd = $dados['formDemanda'];
$fs = $dados['formSugestao'];
?>
<main class="sugestoes">
    <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>
    <div class="cards">

        <!-- ===================== NOVA DEMANDA ===================== -->
        <form class="card demand" id="form-demanda" method="POST" action="<?= URL ?>/demandas/criar">
            <?= Csrf::campo() ?>
            <div class="card-header">
                <img class="card-icon" src="<?= URL ?>/img/Box%20Important.png" alt="">
                <div>
                    <h2>Nova Demanda</h2>
                    <p>Informe um problema ou situação que precise de atenção.</p>
                </div>
            </div>

            <label for="titulo-demanda">Título da demanda</label>
            <input type="text" id="titulo-demanda" name="titulo" maxlength="120" required
                   placeholder="Ex: Ar-condicionado da sala 24 quebrado"
                   value="<?= Html::e($fd['valores']['titulo']) ?>">
            <?php if (!empty($fd['erros']['titulo'])): ?><span class="erro-campo"><?= Html::e($fd['erros']['titulo']) ?></span><?php endif; ?>

            <label for="desc-demanda">Descreva o problema</label>
            <div class="textarea-wrap">
                <textarea id="desc-demanda" name="descricao" maxlength="600" required
                          placeholder="Explique o que está acontecendo, onde e como podemos identificar..."><?= Html::e($fd['valores']['descricao']) ?></textarea>
                <span class="char-count" id="count-demanda">0/600</span>
            </div>
            <?php if (!empty($fd['erros']['descricao'])): ?><span class="erro-campo"><?= Html::e($fd['erros']['descricao']) ?></span><?php endif; ?>

            <label for="cat-demanda">Categoria</label>
            <select id="cat-demanda" name="categoria" required>
                <option value="" disabled <?= $fd['valores']['categoria'] === '' ? 'selected' : '' ?>>Selecione uma categoria</option>
                <?php foreach (Demanda::CATEGORIAS as $valor => $rotulo): ?>
                    <option value="<?= Html::e($valor) ?>" <?= $fd['valores']['categoria'] === $valor ? 'selected' : '' ?>><?= Html::e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($fd['erros']['categoria'])): ?><span class="erro-campo"><?= Html::e($fd['erros']['categoria']) ?></span><?php endif; ?>

            <button type="submit" class="submit-btn btn-demand">Enviar demanda</button>
        </form>

        <!-- ===================== NOVA SUGESTÃO ===================== -->
        <form class="card suggestion" id="form-sugestao" method="POST" action="<?= URL ?>/sugestoes/criar">
            <?= Csrf::campo() ?>
            <div class="card-header">
                <img class="card-icon" src="<?= URL ?>/img/Light%20On.png" alt="">
                <div>
                    <h2>Nova Sugestão</h2>
                    <p>Compartilhe suas ideias para melhorar o nosso campus.</p>
                </div>
            </div>

            <label for="titulo-sugestao">Título da sugestão</label>
            <input type="text" id="titulo-sugestao" name="titulo" maxlength="120" required
                   placeholder="Ex: Mais cadeiras em dia de contraturno"
                   value="<?= Html::e($fs['valores']['titulo']) ?>">
            <?php if (!empty($fs['erros']['titulo'])): ?><span class="erro-campo"><?= Html::e($fs['erros']['titulo']) ?></span><?php endif; ?>

            <label for="desc-sugestao">Descreva sua sugestão</label>
            <div class="textarea-wrap">
                <textarea id="desc-sugestao" name="descricao" maxlength="600" required
                          placeholder="Conte sua ideia e como ela pode melhorar a experiência de todos..."><?= Html::e($fs['valores']['descricao']) ?></textarea>
                <span class="char-count" id="count-sugestao">0/600</span>
            </div>
            <?php if (!empty($fs['erros']['descricao'])): ?><span class="erro-campo"><?= Html::e($fs['erros']['descricao']) ?></span><?php endif; ?>

            <label for="cat-sugestao">Categoria</label>
            <select id="cat-sugestao" name="categoria" required>
                <option value="" disabled <?= $fs['valores']['categoria'] === '' ? 'selected' : '' ?>>Selecione uma categoria</option>
                <?php foreach (Sugestao::CATEGORIAS as $valor => $rotulo): ?>
                    <option value="<?= Html::e($valor) ?>" <?= $fs['valores']['categoria'] === $valor ? 'selected' : '' ?>><?= Html::e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($fs['erros']['categoria'])): ?><span class="erro-campo"><?= Html::e($fs['erros']['categoria']) ?></span><?php endif; ?>

            <button type="submit" class="submit-btn btn-suggestion">Enviar sugestão</button>
        </form>
    </div>

    <!-- ===================== ÚLTIMAS SUGESTÕES ===================== -->
    <section class="lista-simples">
        <h2>Minhas últimas sugestões</h2>
        <?php if (empty($dados['minhasSugestoes'])): ?>
            <p class="vazio">Você ainda não enviou nenhuma sugestão.</p>
        <?php endif; ?>
        <?php foreach ($dados['minhasSugestoes'] as $sugestao): ?>
            <div class="item-simples">
                <div>
                    <strong><?= Html::e($sugestao->titulo) ?></strong>
                    <small><?= Html::e(Sugestao::CATEGORIAS[$sugestao->categoria] ?? $sugestao->categoria) ?> · <?= Html::e(Checa::dataBr($sugestao->criado_em)) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<script>
    // Contador de caracteres dos dois textareas (ex.: "35/600").
    (function () {
        function ligarContador(idTexto, idContador) {
            var texto = document.getElementById(idTexto);
            var contador = document.getElementById(idContador);
            if (!texto || !contador) { return; }
            function atualizar() { contador.textContent = texto.value.length + '/' + texto.maxLength; }
            texto.addEventListener('input', atualizar);
            atualizar();
        }
        ligarContador('desc-demanda', 'count-demanda');
        ligarContador('desc-sugestao', 'count-sugestao');
    })();
</script>
