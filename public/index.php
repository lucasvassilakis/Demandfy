<?php
/*
 * FRONT CONTROLLER (ponto de entrada único do sistema)
 * ------------------------------------------------------------------
 * Toda requisição passa por aqui (o .htaccess redireciona tudo para este arquivo).
 *
 * MUDANÇAS em relação à versão anterior:
 *  - session_start(): necessário para login, mensagens e proteção CSRF.
 *  - Removida a conexão "new Database" que ficava aqui: cada Model abre a sua.
 *  - Removido o HTML daqui: agora o layout (<html>, <head>, sidebar) é montado
 *    pelo Controller (Views/layout/topo.php e rodape.php). Assim as páginas de
 *    login/cadastro (sem sidebar) e as demais (com sidebar) são decididas pelo
 *    controller, e redirecionamentos funcionam sem erro "headers already sent".
 */

// Cookie de sessão mais seguro: não acessível por JavaScript e protegido contra CSRF entre sites.
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once '../app/configuracao.php';
require_once '../app/Autoload.php';

// A classe Rota lê a URL, escolhe o controller/método e executa.
new Rota();
?>
<div vw class="enabled">
  <div vw-access-button class="active"></div>
  <div vw-plugin-wrapper>
    <div class="vw-plugin-top-wrapper"></div>
  </div>
</div>
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
  new window.VLibras.Widget('https://vlibras.gov.br/app');
