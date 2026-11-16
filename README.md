# Demandfy
Demandfy é uma plataforma voltada aos representantes dos Grêmios estudantis de instituições e escolas. Fornece um meio de comunicação entre aluno e Grêmio mais acessível e direto.

## Como rodar (XAMPP)
1. Copie a pasta `Demandfy` para `htdocs` (o endereço fica `http://localhost/Demandfy`).
2. Ligue **Apache** e **MySQL** no XAMPP.
3. Crie/selecione o banco `bd_demandfy` no phpMyAdmin e importe `database/bd_demandfy.sql`. O dump contém o esquema atual, as views e categorias padrão, mas não contém contas nem dados pessoais.
4. Em uma instalação nova feita com esse dump, **não execute as migrações novamente**: elas já estão incorporadas. Para atualizar um banco antigo em vez de importar o dump, faça backup e aplique apenas as migrações necessárias para as tabelas/colunas que ainda faltam, sem repetir migrações já aplicadas.
5. Confira `app/configuracao.php`: o padrão do XAMPP é `http://localhost/Demandfy`, MySQL em `127.0.0.1:3306`, usuário `root` e senha vazia. Se o computador da escola usar outros dados, ajuste esses valores ou defina as variáveis `DEMANDFY_URL` e `DEMANDFY_DB_*`.
6. Se a pasta não se chamar `Demandfy`, ajuste também o `RewriteBase` em `public/.htaccess`.
7. Abra `http://localhost/Demandfy`, clique em **Cadastre-se** e crie sua conta.

## E-mail e recuperação de senha

O envio usa PHPMailer por SMTP. Configure estas variáveis de ambiente no Windows e feche/reabra o Painel de Controle do XAMPP antes de iniciar o Apache, para que o processo receba os novos valores:

- `DEMANDFY_SMTP_HOST` — servidor SMTP (Gmail: `smtp.gmail.com`)
- `DEMANDFY_SMTP_PORT` — porta (Gmail: `587` para TLS ou `465` para SSL)
- `DEMANDFY_SMTP_SECURE` — `tls` ou `ssl`
- `DEMANDFY_SMTP_USER` — conta usada para autenticação
- `DEMANDFY_SMTP_PASSWORD` — senha SMTP ou senha de app
- `DEMANDFY_SMTP_FROM` — endereço remetente (opcional; por padrão usa o usuário SMTP)
- `DEMANDFY_SMTP_FROM_NAME` — nome exibido (opcional; padrão `Demandfy`)

No Gmail, use uma senha de app (exige verificação em duas etapas), não a senha normal da conta. Nunca coloque credenciais no código nem as envie pelo chat. O código de envio por e-mail permanece, mas a interface não oferece mais ações para solicitar recuperação de senha por e-mail nem a opção de alterar a preferência de notificações por e-mail. Preferências já salvas são preservadas ao atualizar outras configurações.

## Administrador e permissões

Crie a conta administrativa pela tela normal de cadastro; assim, a senha já é armazenada com hash seguro e não fica no código. O dump atual já inclui `usuario_permissao`; em um banco antigo, aplique `database/migrations/20261115_create_user_permissions.sql` somente se essa tabela ainda não existir. Depois, promova a conta pelo phpMyAdmin, na aba SQL, substituindo o e-mail pelo endereço cadastrado:

```sql
INSERT INTO usuario_permissao (user_id, permissao, concedida_por)
SELECT user_id, 'admin', NULL
FROM usuario
WHERE user_email = 'admin@demandfy.local'
ON DUPLICATE KEY UPDATE permissao = VALUES(permissao);
```

Faça logout e entre novamente para carregar a permissão administrativa. Para remover o acesso, execute:

```sql
DELETE p
FROM usuario_permissao p
INNER JOIN usuario u ON u.user_id = p.user_id
WHERE u.user_email = 'admin@demandfy.local' AND p.permissao = 'admin';
```

Ao levar o projeto para outro computador, exporte e importe o banco `bd_demandfy`; a conta e a permissão acompanham os dados, sem precisar configurar variáveis de ambiente nem inserir senhas no código. Mantenha uma senha forte e não compartilhe o dump do banco publicamente.

Administradores podem conceder ou revogar permissões identificadas por chaves, e usuários com `usuarios.permissoes.gerenciar` podem delegá-las a outros usuários, mas não podem alterar as próprias permissões. `demandas.editar` e `sugestoes.editar` permitem editar e atualizar o status dos respectivos registros, além de responder a eles no Histórico; `eventos.gerenciar` permite gerenciar eventos e `avisos.gerenciar` permite publicar, editar e excluir avisos. A permissão `admin` é reservada à promoção explícita no banco. A rota POST `/permissoes/atualizar` recebe `csrf`, `usuario_id`, `permissao` e `acao` (`conceder` ou `revogar`); ela é a interface de backend para a futura tela de gestão. `Sessao::temPermissao($chave)` e `Controller::exigirPermissao($chave)` permitem que as telas consultem e imponham essas permissões.

Administradores e moderadores com `usuarios.permissoes.gerenciar` podem abrir a aba **Usuários** para pesquisar e filtrar a lista paginada. Somente administradores podem alterar perfis: podem promover outros usuários, mas só podem rebaixar uma conta administradora se for a própria. Rebaixar a própria conta remove suas permissões elevadas e encerra imediatamente o acesso administrativo. Administradores também podem excluir outros usuários, mas nunca a própria conta; a exclusão é bloqueada quando há demandas, sugestões, avisos, instituições, respostas ou histórico vinculados.

Todas as contas também podem solicitar a própria exclusão em **Configurações**. A ação exige confirmação e encerra a sessão; contas com demandas, sugestões, avisos, instituições, respostas ou histórico vinculados não podem ser excluídas.

O dump inclui a tabela `aviso`, mas ela começa sem comunicados; a página mostra “Nenhum aviso encontrado” até que registros sejam publicados.

## Estrutura (MVC)
- `app/Controllers` — Usuarios, Paginas, Demandas, Sugestoes, Avisos, Calendario, Notificacoes
- `app/Models` — Usuario, Demanda, Sugestao, RespostaHistorico, Aviso, Evento, Notificacao
- `app/Views` — telas (`layout/` = topo e rodapé com a sidebar)
- `app/Libraries` — Rota, Controller, Database
- `app/Helpers` — Sessao, Url, Csrf, Html, Checa, Entrada
- `database/bd_demandfy.sql` — estrutura do banco atual
