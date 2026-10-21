# Demandfy
Demandfy é uma plataforma voltada aos representantes dos Grêmios estudantis de instituições e escolas. Fornece um meio de comunicação entre aluno e Grêmio mais acessível e direto.

## Como rodar (XAMPP)
1. Copie a pasta `Demandfy` para `htdocs` (o endereço fica `http://localhost/Demandfy`).
2. Ligue **Apache** e **MySQL** no XAMPP.
3. No phpMyAdmin, importe/execute `database/schema.sql` (cria o banco `demandfy`, as tabelas e dados de exemplo).
4. Confira `app/configuracao.php`: `URL`, usuário/senha e **porta** do MySQL (padrão 3307 neste XAMPP).
5. Se a pasta não se chamar `Demandfy`, ajuste também o `RewriteBase` em `public/.htaccess`.
6. Abra `http://localhost/Demandfy`, clique em **Cadastre-se** e crie sua conta.

Em bancos já existentes, execute uma vez `database/migrations/20261014_add_foto_perfil.sql` para habilitar o armazenamento das fotos de perfil.

## Estrutura (MVC)
- `app/Controllers` — Usuarios, Paginas, Demandas, Sugestoes, Avisos, Calendario, Notificacoes
- `app/Models` — Usuario, Demanda, Sugestao, Aviso, Evento, Notificacao
- `app/Views` — telas (`layout/` = topo e rodapé com a sidebar)
- `app/Libraries` — Rota, Controller, Database
- `app/Helpers` — Sessao, Url, Csrf, Html, Checa, Entrada
- `database/schema.sql` — criação do banco
