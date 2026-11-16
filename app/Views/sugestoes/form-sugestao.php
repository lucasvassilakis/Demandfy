<?php $fs = $dados['formSugestao']; ?>
<form class="card suggestion" id="form-sugestao" method="POST" action="<?= URL ?>/sugestoes/criar">
    <?= Csrf::campo() ?>
    <div class="card-header">
        <img class="card-icon" src="<?= URL ?>/img/Light%20On.png" alt="">
        <div>
            <h2 id="titulo-form-sugestao">Nova Sugestão</h2>
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
