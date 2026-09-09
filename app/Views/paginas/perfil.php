<?php // View: Página de perfil - baseado na referência fornecida ?>
<style>
  .perfil{padding:30px;background:transparent;min-height:80vh;font-family:Arial,Helvetica,sans-serif}
  .perfil .titulo{font-weight:700}

  .perfil{width:calc(100% - 80px);max-width:1120px;margin:0 0 0 80px;padding-top:1px}

  /* cartão branco - posição e largura reduzida para combinar com outras páginas */
  .perfil .content-card{background:#f3f3f3;border-radius:12px;padding:22px;border:0;box-shadow:none;position:relative;margin:200px 30px 0 35px;max-width:980px}

  /* reduzir gap para aproximar colunas */
  .perfil .perfil-card{background:transparent;border-radius:6px;padding:18px;display:flex;gap:18px;align-items:flex-start}
  .perfil .perfil-left{width:60%;}
  .perfil .perfil-right{width:40%;}

  /* avatar area - diminuir tamanho do ícone e remover fundo */
  .perfil .avatar-wrap{position:relative;background:transparent;border-radius:8px;padding:8px;display:flex;flex-direction:column;align-items:center}
  .perfil .avatar{width:120px;height:120px;border-radius:50%;background:#cfcfcf;display:block;object-fit:cover}

  /* edit button fixed to top-left of the content card */
  .perfil .edit-btn{position:absolute;left:18px;top:18px;background:#f0f0f0;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);cursor:pointer}

  .perfil .mini-card{background:#ececec;border-radius:8px;padding:18px;margin-top:22px}
  .perfil .field{margin-bottom:14px}
  .perfil label{display:block;font-size:13px;color:#333;margin-bottom:6px;font-weight:600}
  .perfil input[type=text]{width:100%;padding:10px;border-radius:6px;border:1px solid #e0e0e0;background:#fff;color:#333}

  .perfil .two-cols{display:flex;gap:12px}
  .perfil .small-input{width:48%;}

  @media(max-width:900px){
    .perfil .perfil-card{flex-direction:column}
    .perfil .perfil-left,.perfil .perfil-right{width:100%}
    .perfil .content-card{padding:14px;margin:140px 14px 0 14px}
  }
</style>

<main class="perfil">
  <h1 class="titulo"><?= htmlspecialchars($dados['titulo'] ?? 'Perfil') ?></h1>

  <div class="content-card">
    <div class="edit-btn" title="Editar">✎</div>

    <div class="perfil-card">
      <div class="perfil-left">
        <div class="avatar-wrap">
          <img src="<?=URL?>/public/img/User.png" alt="User" class="avatar">
          <div class="mini-card">
            <div class="field">
              <label>Instituição</label>
              <input type="text" placeholder="ex: IFRO - Campus Guajará-Mirim">
            </div>
            <div class="field">
              <label>Curso</label>
              <input type="text" placeholder="ex: Técnico em Informática">
            </div>
            <div class="two-cols">
              <div class="field small-input">
                <label>Ano de ingresso</label>
                <input type="text" placeholder="ex: 2024">
              </div>
              <div class="field small-input">
                <label>Situação</label>
                <input type="text" placeholder="ex: Ativo">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="perfil-right">
        <div class="field">
          <label>Nome completo</label>
          <input type="text" placeholder="ex: XXXXXXXXXX XXXXXXXXX XXXX">
        </div>
        <div class="field">
          <label>E-mail</label>
          <input type="text" placeholder="ex: XXXXXXX.XXXXX@XXXX.com">
        </div>
        <div class="field">
          <label>Telefone</label>
          <input type="text" placeholder="ex: 69 9990-0000">
        </div>
        <div class="field">
          <label>Matrícula</label>
          <input type="text" placeholder="ex: 2020109100000">
        </div>
      </div>
    </div>
  </div>
</main>
