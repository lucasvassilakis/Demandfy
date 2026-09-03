# Demandfy
Demandfy é uma plataforma voltada aos representantes dos Grêmios estudantis de instituições e escolas. Fornece um meio de comunicação entre aluno e Grêmio mais acessível e direto.

## Configuração local

1. Inicie Apache e MySQL no XAMPP.
2. Crie no MySQL um banco chamado `framework` e importe o dump do banco usado pelo projeto.
3. Abra `http://localhost/Demandfy/public/`.

Por padrão, a aplicação usa `127.0.0.1:3306`, usuário `root` e senha vazia. Se a instalação usar outros valores, defina antes de iniciar o Apache as variáveis `DEMANDFY_DB_HOST`, `DEMANDFY_DB_PORT`, `DEMANDFY_DB_NAME`, `DEMANDFY_DB_USER` e `DEMANDFY_DB_PASS`.
