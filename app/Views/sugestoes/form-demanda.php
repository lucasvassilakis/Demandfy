<?php $fd = $dados['formDemanda']; ?>
<form class="card demand" id="form-demanda" method="POST" action="<?= URL ?>/demandas/criar">
    <?= Csrf::campo() ?>
    <div class="card-header">
        <img class="card-icon" src="<?= URL ?>/img/Box%20Important.png" alt="">
        <div>
            <h2 id="titulo-form-demanda">Nova Demanda</h2>
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
