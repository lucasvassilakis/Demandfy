<?php
/*
 * VIEW: PERFIL
 * MUDANÇAS: os campos (antes só placeholders) agora mostram e SALVAM os dados do usuário.
 * Os campos abrem bloqueados; o botão ✎ libera a edição e mostra o botão "Salvar".
 * Se houver erro de validação, a página já abre liberada com as mensagens.
 */
$v = $dados['valores'];
$erros = $dados['erros'];
$editando = $dados['editando'];
// readonly só quando NÃO está editando
$ro = $editando ? '' : 'readonly';
?>
<style>
  .perfil-pagina .conteudo-pagina{
    box-sizing:border-box;
    width:min(980px,calc(100% - 48px));
    margin:145px auto 40px;
  }
  .perfil-formulario{padding:32px}
  .perfil-cabecalho{display:flex;align-items:center;gap:20px;padding-bottom:26px;border-bottom:1px solid var(--border-light)}
  .perfil-avatar{width:88px;height:88px;flex-shrink:0;border:4px solid var(--bg-green-light);border-radius:50%;object-fit:cover}
  .perfil-identificacao{min-width:0;flex:1}
  .perfil-identificacao h2{margin:0;color:var(--primary-green);font-size:24px}
  .perfil-identificacao p{margin-top:5px;color:var(--text-muted);font-family:var(--font-poppins);font-size:14px;overflow-wrap:anywhere}
  .perfil-editar{flex-shrink:0;padding:10px 18px;border:1px solid var(--border-green);border-radius:8px;background:var(--bg-green-light);color:var(--primary-green);cursor:pointer;font-family:var(--font-poppins);font-size:14px;font-weight:600}
  .perfil-editar:hover{background:var(--bg-green)}
  .perfil-foto-controles{display:flex;align-items:center;gap:12px;margin-top:12px}
  .perfil-foto-escolher{display:inline-flex;align-items:center;min-height:36px;padding:7px 12px;border:1px solid var(--border-green);border-radius:6px;background:#fff;color:var(--primary-green);cursor:pointer;font-family:var(--font-poppins);font-size:12px;font-weight:600}
  .perfil-foto-escolher:hover{background:var(--bg-green-light)}
  .perfil-foto-ajuda{color:var(--text-subtle);font-family:var(--font-poppins);font-size:12px}
  .perfil-foto-input{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;clip-path:inset(50%)}
  .perfil-foto-input:focus-visible+.perfil-foto-escolher{outline:2px solid var(--primary-green);outline-offset:2px}
  .perfil-secao{margin-top:25px}
  .perfil-secao h3{margin:0 0 16px;color:var(--primary-green);font-size:18px;font-weight:500}
  .perfil-campos{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 20px}
  .perfil-campo{min-width:0;margin-bottom:14px}
  .perfil-campo label{display:block;margin:0 0 7px;color:var(--text-medium);font-family:var(--font-poppins);font-size:13px;font-weight:600}
  .perfil-campo input{height:44px;padding:10px 12px;border:1px solid var(--border-green);border-radius:6px;background:#fff;color:var(--text-medium);font-family:var(--font-poppins);font-size:14px}
  .perfil-campo input[readonly]{background:#f5f8f5;color:var(--text-muted)}
  .perfil-campo input:not([readonly]):focus{border-color:var(--primary-green);box-shadow:0 0 0 2px #c0e8c1}
  .perfil-acoes{display:none;gap:10px;margin-top:12px;padding-top:20px;border-top:1px solid var(--border-light)}
  .perfil-acoes.visivel{display:flex}
  .perfil-acoes button,.perfil-acoes a{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:9px 20px;border:0;border-radius:8px;cursor:pointer;font-family:var(--font-poppins);font-size:14px;font-weight:600;text-decoration:none}
  .perfil .btn-salvar{background:#2f7d46;color:#fff}
  .perfil .btn-salvar:hover{background:#256638}
  .perfil .btn-cancelar{background:#edf1ed;color:var(--text-medium)}
  .perfil .btn-cancelar:hover{background:#dfe7df}

  @media(max-width:850px){
    .perfil-pagina .titulo{margin-bottom:24px}
    .perfil-pagina .conteudo-pagina{width:calc(100% - 32px);margin:24px auto 32px;padding:0}
    .perfil-formulario{padding:24px}
  }

  @media(max-width:520px){
    .perfil-formulario{padding:20px}
    .perfil-cabecalho{align-items:flex-start;gap:14px;flex-wrap:wrap}
    .perfil-avatar{width:68px;height:68px}
    .perfil-identificacao{padding-top:5px}
    .perfil-identificacao h2{font-size:20px}
    .perfil-editar{margin-left:82px}
    .perfil-foto-controles{align-items:flex-start;flex-direction:column;gap:6px}
    .perfil-campos{grid-template-columns:1fr;gap:0}
    .perfil-acoes{flex-direction:column}
    .perfil-acoes button,.perfil-acoes a{width:100%}
  }
</style>

<main class="inicio perfil-pagina perfil">
  <h1 class="titulo"><?= Html::e($dados['titulo']) ?></h1>

  <section class="conteudo-pagina">
    <form class="card perfil-formulario" method="POST" action="<?= URL ?>/perfil" id="form-perfil" enctype="multipart/form-data" novalidate>
      <?= Csrf::campo() ?>

      <?php if (!empty($erros['geral'])): ?>
        <div class="alerta alerta-erro" role="alert"><?= Html::e($erros['geral']) ?></div>
      <?php endif; ?>

      <header class="perfil-cabecalho">
        <img src="<?= URL ?>/<?= Html::e($v['foto_perfil'] !== '' ? $v['foto_perfil'] : 'img/perfil-de-usuario.png') ?>" alt="Foto do perfil" class="perfil-avatar" id="foto-perfil-preview">
        <div class="perfil-identificacao">
          <h2><?= Html::e($v['nome']) ?></h2>
          <p><?= Html::e($v['email']) ?></p>
          <div class="perfil-foto-controles">
            <input class="perfil-foto-input" type="file" name="foto_perfil" id="foto_perfil" accept="image/jpeg,image/png,image/webp">
            <label class="perfil-foto-escolher" for="foto_perfil">Alterar foto</label>
            <span class="perfil-foto-ajuda">JPG, PNG ou WebP · até 5 MB</span>
          </div>
          <?php if (!empty($erros['foto_perfil'])): ?><span class="erro-campo"><?= Html::e($erros['foto_perfil']) ?></span><?php endif; ?>
        </div>
        <button type="button" class="perfil-editar" id="btn-editar">Editar perfil</button>
      </header>

      <section class="perfil-secao">
        <h3>Dados pessoais</h3>
        <div class="perfil-campos">
          <div class="perfil-campo">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" <?= $ro ?> maxlength="120" value="<?= Html::e($v['nome']) ?>">
            <?php if (!empty($erros['nome'])): ?><span class="erro-campo"><?= Html::e($erros['nome']) ?></span><?php endif; ?>
          </div>
          <div class="perfil-campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" <?= $ro ?> maxlength="150" value="<?= Html::e($v['email']) ?>">
            <?php if (!empty($erros['email'])): ?><span class="erro-campo"><?= Html::e($erros['email']) ?></span><?php endif; ?>
          </div>
          <div class="perfil-campo">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" <?= $ro ?> maxlength="20" placeholder="ex: 69 9990-0000" value="<?= Html::e($v['telefone']) ?>">
            <?php if (!empty($erros['telefone'])): ?><span class="erro-campo"><?= Html::e($erros['telefone']) ?></span><?php endif; ?>
          </div>
          <div class="perfil-campo">
            <label for="matricula">Matrícula</label>
            <input type="text" id="matricula" name="matricula" <?= $ro ?> maxlength="30" placeholder="ex: 2020109100000" value="<?= Html::e($v['matricula']) ?>">
            <?php if (!empty($erros['matricula'])): ?><span class="erro-campo"><?= Html::e($erros['matricula']) ?></span><?php endif; ?>
          </div>
        </div>
      </section>

      <section class="perfil-secao">
        <h3>Dados acadêmicos</h3>
        <div class="perfil-campos">
          <div class="perfil-campo">
            <label for="instituicao">Instituição</label>
            <input type="text" id="instituicao" name="instituicao" <?= $ro ?> maxlength="120" placeholder="ex: IFRO - Campus Guajará-Mirim" value="<?= Html::e($v['instituicao']) ?>">
            <?php if (!empty($erros['instituicao'])): ?><span class="erro-campo"><?= Html::e($erros['instituicao']) ?></span><?php endif; ?>
          </div>
          <div class="perfil-campo">
            <label for="curso">Curso</label>
            <input type="text" id="curso" name="curso" <?= $ro ?> maxlength="120" placeholder="ex: Técnico em Informática" value="<?= Html::e($v['curso']) ?>">
            <?php if (!empty($erros['curso'])): ?><span class="erro-campo"><?= Html::e($erros['curso']) ?></span><?php endif; ?>
          </div>
          <div class="perfil-campo">
            <label for="ano_ingresso">Ano de ingresso</label>
            <input type="text" id="ano_ingresso" name="ano_ingresso" <?= $ro ?> maxlength="4" placeholder="ex: 2024" value="<?= Html::e($v['ano_ingresso']) ?>">
            <?php if (!empty($erros['ano_ingresso'])): ?><span class="erro-campo"><?= Html::e($erros['ano_ingresso']) ?></span><?php endif; ?>
          </div>
          <div class="perfil-campo">
            <label for="situacao">Situação</label>
            <input type="text" id="situacao" name="situacao" <?= $ro ?> maxlength="30" placeholder="ex: Ativo" value="<?= Html::e($v['situacao']) ?>">
            <?php if (!empty($erros['situacao'])): ?><span class="erro-campo"><?= Html::e($erros['situacao']) ?></span><?php endif; ?>
          </div>
        </div>
      </section>

      <div class="perfil-acoes<?= $editando ? ' visivel' : '' ?>" id="perfil-acoes">
        <button type="submit" class="btn-salvar">Salvar alterações</button>
        <a class="btn-cancelar" href="<?= URL ?>/perfil">Cancelar</a>
      </div>
    </form>
  </section>
</main>

<script>
  // Botão ✎: libera os campos para edição e mostra os botões Salvar/Cancelar.
  (function () {
    var botao = document.getElementById('btn-editar');
    var form = document.getElementById('form-perfil');
    var acoes = document.getElementById('perfil-acoes');
    var foto = document.getElementById('foto_perfil');
    var preview = document.getElementById('foto-perfil-preview');
    if (!botao || !form) { return; }
    botao.addEventListener('click', function () {
      var campos = form.querySelectorAll('input[type=text], input[type=email]');
      for (var i = 0; i < campos.length; i++) { campos[i].removeAttribute('readonly'); }
      acoes.classList.add('visivel');
      document.getElementById('nome').focus();
    });
    if (foto && preview) {
      foto.addEventListener('change', function () {
        if (!foto.files || !foto.files[0]) { return; }
        preview.src = URL.createObjectURL(foto.files[0]);
        acoes.classList.add('visivel');
      });
    }
  })();
</script>
