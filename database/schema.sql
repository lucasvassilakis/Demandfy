-- =====================================================================
-- DEMANDFY - Script de criação do banco de dados
-- Como usar: abra o phpMyAdmin (ou o cliente MySQL) e execute este arquivo
-- inteiro. Ele cria o banco "demandfy", as tabelas e alguns dados de exemplo
-- (avisos e eventos) para as páginas não ficarem vazias.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS demandfy
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE demandfy;

-- ---------------------------------------------------------------------
-- USUÁRIOS (alunos/representantes). Usado em: cadastro, login, perfil.
-- A senha NUNCA é guardada em texto: aqui fica o hash gerado por password_hash().
-- O telefone é guardado só com números (ex.: 69999000000).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(120) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  telefone      VARCHAR(15)  NOT NULL,
  senha         VARCHAR(255) NOT NULL,
  instituicao   VARCHAR(120) NULL,
  curso         VARCHAR(120) NULL,
  ano_ingresso  SMALLINT UNSIGNED NULL,
  situacao      VARCHAR(30)  NULL DEFAULT 'Ativo',
  matricula     VARCHAR(30)  NULL,
  foto_perfil   VARCHAR(255) NULL,
  criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),
  UNIQUE KEY uq_usuarios_telefone (telefone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- DEMANDAS (problemas enviados ao Grêmio).
-- status: aguardando | em_analise | resolvida
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS demandas (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  titulo     VARCHAR(120) NOT NULL,
  descricao  VARCHAR(600) NOT NULL,
  categoria  VARCHAR(30)  NOT NULL,
  status     VARCHAR(20)  NOT NULL DEFAULT 'aguardando',
  criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_demandas_usuario (usuario_id),
  CONSTRAINT fk_demandas_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SUGESTÕES (ideias para melhorar o campus).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sugestoes (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  titulo     VARCHAR(120) NOT NULL,
  descricao  VARCHAR(600) NOT NULL,
  categoria  VARCHAR(30)  NOT NULL,
  criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sugestoes_usuario (usuario_id),
  CONSTRAINT fk_sugestoes_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- AVISOS do Grêmio (iguais para todos os usuários).
-- tipo: importante | informativo | lembrete
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS avisos (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo      VARCHAR(20)  NOT NULL,
  titulo    VARCHAR(150) NOT NULL,
  texto     TEXT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_avisos_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- EVENTOS do calendário (iguais para todos).
-- cor: verde | azul | amarelo (cores já existentes no CSS)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS eventos (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  titulo       VARCHAR(150) NOT NULL,
  descricao    VARCHAR(300) NULL,
  data_evento  DATE NOT NULL,
  hora_inicio  TIME NULL,
  hora_fim     TIME NULL,
  cor          VARCHAR(10) NOT NULL DEFAULT 'verde',
  PRIMARY KEY (id),
  KEY idx_eventos_data (data_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- NOTIFICAÇÕES de cada usuário (geradas pelo sistema quando algo acontece).
-- tipo: aviso | demanda | evento | sugestao
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notificacoes (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  tipo       VARCHAR(20)  NOT NULL,
  titulo     VARCHAR(150) NOT NULL,
  mensagem   VARCHAR(300) NOT NULL,
  lida       TINYINT(1) NOT NULL DEFAULT 0,
  criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notificacoes_usuario (usuario_id, lida),
  CONSTRAINT fk_notificacoes_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DADOS DE EXEMPLO (avisos e eventos). Pode apagar ou editar à vontade.
-- =====================================================================
INSERT INTO avisos (tipo, titulo, texto, criado_em) VALUES
('importante',  'Manutenção da Internet',
 'Haverá manutenção na rede do campus no dia 05/09 (sábado) das 8h às 11h. Durante esse período, a internet poderá apresentar instabilidades.',
 NOW() - INTERVAL 1 DAY),
('informativo', 'Reunião aberta do Grêmio',
 'Participe da reunião aberta do Grêmio Estudantil que acontecerá no dia 14/09 (segunda) às 18h na sala 13.',
 NOW() - INTERVAL 2 DAY),
('lembrete',    'Prazo para sugestões no RDD',
 'Não se esqueça! O prazo para enviar sugestões no RDD vai até 18/09. Sua participação é muito importante!',
 NOW() - INTERVAL 3 DAY),
('informativo', 'Campanha de doação de livros',
 'O Grêmio está arrecadando livros para a biblioteca. Doe e ajude a incentivar a leitura!',
 NOW() - INTERVAL 4 DAY);

-- Eventos de exemplo no mês atual (as datas acompanham o dia em que o script é executado).
INSERT INTO eventos (titulo, descricao, data_evento, hora_inicio, hora_fim, cor) VALUES
('Descomplica IF',          'Comemoração ao dia do estudante.',              CURDATE() + INTERVAL 3 DAY,  '08:00', '17:40', 'verde'),
('Apresentação dos grupos', 'Apresentação dos grupos do Descomplica IF.',    CURDATE() + INTERVAL 3 DAY,  '10:00', NULL,    'azul'),
('Contraturno',             'Contraturno para o Descomplica IF.',            CURDATE() + INTERVAL 3 DAY,  '08:00', '16:20', 'amarelo'),
('Reunião do Grêmio',       'Reunião aberta do Grêmio Estudantil, sala 21.', CURDATE() + INTERVAL 7 DAY,  '18:00', NULL,    'verde'),
('Consciência Negra',       'Evento no auditório.',                          CURDATE() + INTERVAL 14 DAY, '09:00', NULL,    'azul');
