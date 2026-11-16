-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/11/2026 às 03:28
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bd_demandfy`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `aviso`
--

CREATE TABLE `aviso` (
  `avis_id` int(11) NOT NULL,
  `avis_titulo` varchar(200) NOT NULL,
  `avis_descricao` varchar(500) NOT NULL,
  `avis_data` datetime NOT NULL DEFAULT current_timestamp(),
  `cate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `calendario`
--

CREATE TABLE `calendario` (
  `cale_id` int(11) NOT NULL,
  `cale_dia` date NOT NULL,
  `cale_semana` tinyint(4) NOT NULL,
  `cale_mes` tinyint(4) NOT NULL,
  `cale_ano` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

CREATE TABLE `categoria` (
  `cate_id` int(11) NOT NULL,
  `cate_nome` varchar(100) NOT NULL,
  `cate_tipo` enum('demanda','sugestao','evento','aviso') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categoria`
--

INSERT INTO `categoria` (`cate_id`, `cate_nome`, `cate_tipo`) VALUES
(26, 'Acadêmico', 'sugestao'),
(27, 'Bem-estar', 'sugestao'),
(5, 'Discursões', 'demanda'),
(10, 'Discursões', 'sugestao'),
(13, 'Evento da Escola', 'evento'),
(12, 'Evento do Grêmio', 'evento'),
(25, 'Eventos', 'sugestao'),
(14, 'Feriado', 'evento'),
(11, 'Importante', 'evento'),
(16, 'Importante', 'aviso'),
(17, 'Informativo', 'aviso'),
(19, 'Infraestrutura', 'demanda'),
(24, 'Infraestrutura', 'sugestao'),
(4, 'Internet com problemas', 'demanda'),
(9, 'Internet com problemas', 'sugestao'),
(18, 'Lembrete', 'aviso'),
(20, 'Limpeza', 'demanda'),
(23, 'Outros', 'demanda'),
(28, 'Outros', 'sugestao'),
(3, 'Problema nos laboratórios', 'demanda'),
(8, 'Problema nos laboratórios', 'sugestao'),
(1, 'Quebrado / desconectado', 'demanda'),
(6, 'Quebrado / desconectado', 'sugestao'),
(15, 'Reunião / Aula no AVA', 'evento'),
(22, 'Segurança', 'demanda'),
(2, 'Sumiço / desaparecimento', 'demanda'),
(7, 'Sumiço / desaparecimento', 'sugestao'),
(21, 'TI / Sistemas', 'demanda');

-- --------------------------------------------------------

--
-- Estrutura para tabela `demanda`
--

CREATE TABLE `demanda` (
  `dema_id` int(11) NOT NULL,
  `dema_titulo` varchar(200) NOT NULL,
  `dema_status` enum('aberta','em_analise','em_andamento','resolvida','cancelada','aguardando') NOT NULL DEFAULT 'aberta',
  `dema_descricao` varchar(600) NOT NULL,
  `dema_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dema_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `dema_ordem` int(11) DEFAULT NULL,
  `cate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deferido_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `evento`
--

CREATE TABLE `evento` (
  `even_id` int(11) NOT NULL,
  `even_titulo` varchar(200) NOT NULL,
  `even_descricao` varchar(600) NOT NULL,
  `cate_id` int(11) NOT NULL,
  `cale_id` int(11) NOT NULL,
  `even_hora_inicio` time DEFAULT NULL,
  `even_hora_fim` time DEFAULT NULL,
  `even_ordem` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_demanda`
--

CREATE TABLE `historico_demanda` (
  `hist_id` int(11) NOT NULL,
  `dema_id` int(11) NOT NULL,
  `hist_status` enum('aberta','em_analise','em_andamento','resolvida','cancelada','aguardando') NOT NULL,
  `hist_obs` varchar(300) DEFAULT NULL,
  `hist_data` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_sugestao`
--

CREATE TABLE `historico_sugestao` (
  `hist_id` int(11) NOT NULL,
  `suge_id` int(11) NOT NULL,
  `hist_status` enum('enviada','em_analise','aceita','recusada','em_andamento') NOT NULL,
  `hist_obs` varchar(300) DEFAULT NULL,
  `hist_data` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `instituto`
--

CREATE TABLE `instituto` (
  `inst_id` int(11) NOT NULL,
  `inst_nome` varchar(300) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao`
--

CREATE TABLE `notificacao` (
  `noti_id` int(11) NOT NULL,
  `noti_titulo` varchar(200) NOT NULL,
  `noti_mensagem` varchar(500) DEFAULT NULL,
  `noti_status` enum('nao_lida','lida') NOT NULL DEFAULT 'nao_lida',
  `noti_data` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `perfil`
--

CREATE TABLE `perfil` (
  `perf_id` int(11) NOT NULL,
  `perf_nome` varchar(50) NOT NULL,
  `perf_nivel` tinyint(4) NOT NULL,
  `perf_descricao` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `perfil`
--

INSERT INTO `perfil` (`perf_id`, `perf_nome`, `perf_nivel`, `perf_descricao`) VALUES
(1, 'Aluno', 0, 'Usuário padrão - pode cadastrar demandas e sugestões'),
(2, 'Membro do Grêmio', 1, 'Pode analisar e deferir demandas, gerenciar sugestões e avisos'),
(3, 'Administrador', 2, 'Possui permissões completas de gestão do sistema');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recuperacao_senha`
--

CREATE TABLE `recuperacao_senha` (
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expira_em` datetime NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `usado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sugestao`
--

CREATE TABLE `sugestao` (
  `suge_id` int(11) NOT NULL,
  `suge_titulo` varchar(200) NOT NULL,
  `suge_descricao` varchar(600) NOT NULL,
  `suge_categoria` enum('infraestrutura','eventos','academico','bem-estar','outros') NOT NULL DEFAULT 'outros',
  `suge_status` enum('enviada','em_analise','aceita','recusada','em_andamento') NOT NULL DEFAULT 'enviada',
  `suge_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `suge_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `suge_ordem` int(11) DEFAULT NULL,
  `cate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `user_id` int(11) NOT NULL,
  `user_nome` varchar(200) NOT NULL,
  `user_email` varchar(320) NOT NULL,
  `user_telefone` varchar(20) NOT NULL,
  `user_senha` varchar(255) NOT NULL,
  `user_matricula` varchar(15) DEFAULT NULL,
  `user_status` enum('ativo','inativo','suspenso') NOT NULL DEFAULT 'ativo',
  `user_curso` varchar(200) DEFAULT NULL,
  `user_ano_ingresso` year(4) DEFAULT NULL,
  `user_funcao_gremio` varchar(100) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `user_instituicao` varchar(300) DEFAULT NULL,
  `perf_id` int(11) NOT NULL,
  `user_notificacao_email` tinyint(1) NOT NULL DEFAULT 1,
  `user_notificacao_site` tinyint(1) NOT NULL DEFAULT 1,
  `user_tema` enum('claro','escuro') NOT NULL DEFAULT 'claro',
  `user_criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`user_id`, `user_nome`, `user_email`, `user_telefone`, `user_senha`, `user_matricula`, `user_status`, `user_curso`, `user_ano_ingresso`, `user_funcao_gremio`, `foto_perfil`, `user_instituicao`, `perf_id`, `user_notificacao_email`, `user_notificacao_site`, `user_tema`, `user_criado_em`) VALUES
(1, 'admin', 'admin@demandfy.local', '99999999999', '$2y$10$HAbHqIDYBEXezoYvGCwhVOEWfWZyLD7oSj6/VDaNK38R.FEuuwh7G', NULL, 'ativo', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 'claro', '2026-11-15 19:02:09');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_permissao`
--

CREATE TABLE `usuario_permissao` (
  `user_id` int(11) NOT NULL,
  `permissao` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `concedida_por` int(11) DEFAULT NULL,
  `concedida_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario_permissao`
--

INSERT INTO `usuario_permissao` (`user_id`, `permissao`, `concedida_por`, `concedida_em`) VALUES
(1, 'admin', NULL, '2026-11-15 19:03:17');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_avisos`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_avisos` (
`avis_id` int(11)
,`avis_titulo` varchar(200)
,`avis_descricao` varchar(500)
,`avis_data` datetime
,`categoria` varchar(100)
,`criado_por` varchar(200)
,`user_id` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_dashboard`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_dashboard` (
`total_usuarios` int(1)
,`total_admins` int(1)
,`total_alunos` int(1)
,`total_gremio` int(1)
,`total_demandas` int(1)
,`demandas_abertas` int(1)
,`demandas_em_analise` int(1)
,`demandas_em_andamento` int(1)
,`demandas_resolvidas` int(1)
,`total_sugestoes` int(1)
,`sugestoes_enviadas` int(1)
,`sugestoes_aceitas` int(1)
,`total_eventos` int(1)
,`total_avisos` int(1)
,`notificacoes_nao_lidas` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_demandas`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_demandas` (
`dema_id` int(1)
,`dema_titulo` int(1)
,`dema_descricao` int(1)
,`dema_status` int(1)
,`dema_atualizacao` int(1)
,`dema_criado_em` int(1)
,`categoria` int(1)
,`cadastrado_por` int(1)
,`email_cadastro` int(1)
,`deferido_por` int(1)
,`user_id` int(1)
,`deferido_por_id` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_demandas_abertas`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_demandas_abertas` (
`dema_id` int(1)
,`dema_titulo` int(1)
,`dema_descricao` int(1)
,`categoria` int(1)
,`cadastrado_por` int(1)
,`dema_atualizacao` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_eventos`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_eventos` (
`even_id` int(1)
,`even_titulo` int(1)
,`even_descricao` int(1)
,`categoria` int(1)
,`cale_dia` int(1)
,`cale_semana` int(1)
,`cale_mes` int(1)
,`cale_ano` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_historico_demandas`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_historico_demandas` (
`hist_id` int(1)
,`dema_id` int(1)
,`dema_titulo` int(1)
,`hist_status` int(1)
,`hist_obs` int(1)
,`hist_data` int(1)
,`alterado_por` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_historico_sugestoes`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_historico_sugestoes` (
`hist_id` int(1)
,`suge_id` int(1)
,`suge_titulo` int(1)
,`hist_status` int(1)
,`hist_obs` int(1)
,`hist_data` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_notificacoes`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_notificacoes` (
`noti_id` int(1)
,`noti_titulo` int(1)
,`noti_mensagem` int(1)
,`noti_status` int(1)
,`noti_data` int(1)
,`user_nome` int(1)
,`user_email` int(1)
,`user_id` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_sugestoes`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_sugestoes` (
`suge_id` int(1)
,`suge_titulo` int(1)
,`suge_descricao` int(1)
,`suge_status` int(1)
,`suge_criado_em` int(1)
,`categoria` int(1)
,`cadastrado_por` int(1)
,`email_cadastro` int(1)
,`user_id` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_usuarios`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_usuarios` (
`user_id` int(1)
,`user_nome` int(1)
,`user_email` int(1)
,`user_telefone` int(1)
,`user_matricula` int(1)
,`user_curso` int(1)
,`user_ano_ingresso` int(1)
,`user_instituicao` int(1)
,`user_status` int(1)
,`perf_nome` int(1)
,`perf_nivel` int(1)
,`user_tema` int(1)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_avisos`
--
DROP TABLE IF EXISTS `vw_avisos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_avisos`  AS SELECT `a`.`avis_id` AS `avis_id`, `a`.`avis_titulo` AS `avis_titulo`, `a`.`avis_descricao` AS `avis_descricao`, `a`.`avis_data` AS `avis_data`, `c`.`cate_nome` AS `categoria`, `u`.`user_nome` AS `criado_por`, `a`.`user_id` AS `user_id` FROM ((`aviso` `a` join `categoria` `c` on(`a`.`cate_id` = `c`.`cate_id`)) join `usuario` `u` on(`a`.`user_id` = `u`.`user_id`)) ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_dashboard`
--
DROP TABLE IF EXISTS `vw_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_dashboard`  AS SELECT 1 AS `total_usuarios`, 1 AS `total_admins`, 1 AS `total_alunos`, 1 AS `total_gremio`, 1 AS `total_demandas`, 1 AS `demandas_abertas`, 1 AS `demandas_em_analise`, 1 AS `demandas_em_andamento`, 1 AS `demandas_resolvidas`, 1 AS `total_sugestoes`, 1 AS `sugestoes_enviadas`, 1 AS `sugestoes_aceitas`, 1 AS `total_eventos`, 1 AS `total_avisos`, 1 AS `notificacoes_nao_lidas` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_demandas`
--
DROP TABLE IF EXISTS `vw_demandas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_demandas`  AS SELECT 1 AS `dema_id`, 1 AS `dema_titulo`, 1 AS `dema_descricao`, 1 AS `dema_status`, 1 AS `dema_atualizacao`, 1 AS `dema_criado_em`, 1 AS `categoria`, 1 AS `cadastrado_por`, 1 AS `email_cadastro`, 1 AS `deferido_por`, 1 AS `user_id`, 1 AS `deferido_por_id` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_demandas_abertas`
--
DROP TABLE IF EXISTS `vw_demandas_abertas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_demandas_abertas`  AS SELECT 1 AS `dema_id`, 1 AS `dema_titulo`, 1 AS `dema_descricao`, 1 AS `categoria`, 1 AS `cadastrado_por`, 1 AS `dema_atualizacao` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_eventos`
--
DROP TABLE IF EXISTS `vw_eventos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_eventos`  AS SELECT 1 AS `even_id`, 1 AS `even_titulo`, 1 AS `even_descricao`, 1 AS `categoria`, 1 AS `cale_dia`, 1 AS `cale_semana`, 1 AS `cale_mes`, 1 AS `cale_ano` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_historico_demandas`
--
DROP TABLE IF EXISTS `vw_historico_demandas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_historico_demandas`  AS SELECT 1 AS `hist_id`, 1 AS `dema_id`, 1 AS `dema_titulo`, 1 AS `hist_status`, 1 AS `hist_obs`, 1 AS `hist_data`, 1 AS `alterado_por` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_historico_sugestoes`
--
DROP TABLE IF EXISTS `vw_historico_sugestoes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_historico_sugestoes`  AS SELECT 1 AS `hist_id`, 1 AS `suge_id`, 1 AS `suge_titulo`, 1 AS `hist_status`, 1 AS `hist_obs`, 1 AS `hist_data` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_notificacoes`
--
DROP TABLE IF EXISTS `vw_notificacoes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_notificacoes`  AS SELECT 1 AS `noti_id`, 1 AS `noti_titulo`, 1 AS `noti_mensagem`, 1 AS `noti_status`, 1 AS `noti_data`, 1 AS `user_nome`, 1 AS `user_email`, 1 AS `user_id` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_sugestoes`
--
DROP TABLE IF EXISTS `vw_sugestoes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_sugestoes`  AS SELECT 1 AS `suge_id`, 1 AS `suge_titulo`, 1 AS `suge_descricao`, 1 AS `suge_status`, 1 AS `suge_criado_em`, 1 AS `categoria`, 1 AS `cadastrado_por`, 1 AS `email_cadastro`, 1 AS `user_id` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_usuarios`
--
DROP TABLE IF EXISTS `vw_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_usuarios`  AS SELECT 1 AS `user_id`, 1 AS `user_nome`, 1 AS `user_email`, 1 AS `user_telefone`, 1 AS `user_matricula`, 1 AS `user_curso`, 1 AS `user_ano_ingresso`, 1 AS `user_instituicao`, 1 AS `user_status`, 1 AS `perf_nome`, 1 AS `perf_nivel`, 1 AS `user_tema` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `aviso`
--
ALTER TABLE `aviso`
  ADD PRIMARY KEY (`avis_id`),
  ADD KEY `fk_aviso_categoria` (`cate_id`),
  ADD KEY `fk_aviso_usuario` (`user_id`);

--
-- Índices de tabela `calendario`
--
ALTER TABLE `calendario`
  ADD PRIMARY KEY (`cale_id`),
  ADD UNIQUE KEY `uk_calendario_dia` (`cale_dia`);

--
-- Índices de tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`cate_id`),
  ADD UNIQUE KEY `uk_categoria_nome_tipo` (`cate_nome`,`cate_tipo`);

--
-- Índices de tabela `demanda`
--
ALTER TABLE `demanda`
  ADD PRIMARY KEY (`dema_id`),
  ADD KEY `idx_demanda_status` (`dema_status`),
  ADD KEY `idx_demanda_categoria` (`cate_id`),
  ADD KEY `idx_demanda_user` (`user_id`),
  ADD KEY `fk_demanda_deferido_por` (`deferido_por`);

--
-- Índices de tabela `evento`
--
ALTER TABLE `evento`
  ADD PRIMARY KEY (`even_id`),
  ADD KEY `fk_evento_categoria` (`cate_id`),
  ADD KEY `fk_evento_calendario` (`cale_id`),
  ADD KEY `idx_evento_calendario_ordem` (`cale_id`,`even_ordem`,`even_id`);

--
-- Índices de tabela `historico_demanda`
--
ALTER TABLE `historico_demanda`
  ADD PRIMARY KEY (`hist_id`),
  ADD KEY `fk_historico_demanda` (`dema_id`);

--
-- Índices de tabela `historico_sugestao`
--
ALTER TABLE `historico_sugestao`
  ADD PRIMARY KEY (`hist_id`),
  ADD KEY `fk_historico_sugestao` (`suge_id`);

--
-- Índices de tabela `instituto`
--
ALTER TABLE `instituto`
  ADD PRIMARY KEY (`inst_id`),
  ADD UNIQUE KEY `uk_instituto_nome` (`inst_nome`),
  ADD KEY `fk_instituto_usuario` (`user_id`);

--
-- Índices de tabela `notificacao`
--
ALTER TABLE `notificacao`
  ADD PRIMARY KEY (`noti_id`),
  ADD KEY `fk_notificacao_usuario` (`user_id`);

--
-- Índices de tabela `perfil`
--
ALTER TABLE `perfil`
  ADD PRIMARY KEY (`perf_id`),
  ADD UNIQUE KEY `uk_perfil_nome` (`perf_nome`),
  ADD UNIQUE KEY `uk_perfil_nivel` (`perf_nivel`);

--
-- Índices de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_recuperacao_senha_token_hash` (`token_hash`),
  ADD KEY `idx_recuperacao_senha_expira_em` (`expira_em`);

--
-- Índices de tabela `sugestao`
--
ALTER TABLE `sugestao`
  ADD PRIMARY KEY (`suge_id`),
  ADD KEY `fk_sugestao_categoria` (`cate_id`),
  ADD KEY `fk_sugestao_usuario` (`user_id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uk_usuario_email` (`user_email`),
  ADD UNIQUE KEY `uk_usuario_telefone` (`user_telefone`),
  ADD UNIQUE KEY `uk_usuario_matricula` (`user_matricula`),
  ADD KEY `idx_usuario_perfil` (`perf_id`);

--
-- Índices de tabela `usuario_permissao`
--
ALTER TABLE `usuario_permissao`
  ADD PRIMARY KEY (`user_id`,`permissao`),
  ADD KEY `idx_usuario_permissao_permissao` (`permissao`),
  ADD KEY `idx_usuario_permissao_concedida_por` (`concedida_por`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aviso`
--
ALTER TABLE `aviso`
  MODIFY `avis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `calendario`
--
ALTER TABLE `calendario`
  MODIFY `cale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `cate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `demanda`
--
ALTER TABLE `demanda`
  MODIFY `dema_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `evento`
--
ALTER TABLE `evento`
  MODIFY `even_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `historico_demanda`
--
ALTER TABLE `historico_demanda`
  MODIFY `hist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_sugestao`
--
ALTER TABLE `historico_sugestao`
  MODIFY `hist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `instituto`
--
ALTER TABLE `instituto`
  MODIFY `inst_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacao`
--
ALTER TABLE `notificacao`
  MODIFY `noti_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `perfil`
--
ALTER TABLE `perfil`
  MODIFY `perf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `sugestao`
--
ALTER TABLE `sugestao`
  MODIFY `suge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aviso`
--
ALTER TABLE `aviso`
  ADD CONSTRAINT `fk_aviso_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aviso_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `demanda`
--
ALTER TABLE `demanda`
  ADD CONSTRAINT `fk_demanda_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_demanda_deferido_por` FOREIGN KEY (`deferido_por`) REFERENCES `usuario` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_demanda_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `evento`
--
ALTER TABLE `evento`
  ADD CONSTRAINT `fk_evento_calendario` FOREIGN KEY (`cale_id`) REFERENCES `calendario` (`cale_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evento_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `historico_demanda`
--
ALTER TABLE `historico_demanda`
  ADD CONSTRAINT `fk_historico_demanda` FOREIGN KEY (`dema_id`) REFERENCES `demanda` (`dema_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `historico_sugestao`
--
ALTER TABLE `historico_sugestao`
  ADD CONSTRAINT `fk_historico_sugestao` FOREIGN KEY (`suge_id`) REFERENCES `sugestao` (`suge_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `instituto`
--
ALTER TABLE `instituto`
  ADD CONSTRAINT `fk_instituto_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `notificacao`
--
ALTER TABLE `notificacao`
  ADD CONSTRAINT `fk_notificacao_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  ADD CONSTRAINT `fk_recuperacao_senha_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `sugestao`
--
ALTER TABLE `sugestao`
  ADD CONSTRAINT `fk_sugestao_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sugestao_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_perfil` FOREIGN KEY (`perf_id`) REFERENCES `perfil` (`perf_id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuario_permissao`
--
ALTER TABLE `usuario_permissao`
  ADD CONSTRAINT `fk_usuario_permissao_concedida_por` FOREIGN KEY (`concedida_por`) REFERENCES `usuario` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_permissao_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
