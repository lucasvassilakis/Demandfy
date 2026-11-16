
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `bd_demandfy` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `bd_demandfy`;
DROP TABLE IF EXISTS `aviso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aviso` (
  `avis_id` int(11) NOT NULL AUTO_INCREMENT,
  `avis_titulo` varchar(200) NOT NULL,
  `avis_descricao` varchar(500) NOT NULL,
  `avis_data` datetime NOT NULL DEFAULT current_timestamp(),
  `cate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`avis_id`),
  KEY `fk_aviso_categoria` (`cate_id`),
  KEY `fk_aviso_usuario` (`user_id`),
  CONSTRAINT `fk_aviso_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_aviso_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calendario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendario` (
  `cale_id` int(11) NOT NULL AUTO_INCREMENT,
  `cale_dia` date NOT NULL,
  `cale_semana` tinyint(4) NOT NULL,
  `cale_mes` tinyint(4) NOT NULL,
  `cale_ano` year(4) NOT NULL,
  PRIMARY KEY (`cale_id`),
  UNIQUE KEY `uk_calendario_dia` (`cale_dia`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categoria` (
  `cate_id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_nome` varchar(100) NOT NULL,
  `cate_tipo` enum('demanda','sugestao','evento','aviso') NOT NULL,
  PRIMARY KEY (`cate_id`),
  UNIQUE KEY `uk_categoria_nome_tipo` (`cate_nome`,`cate_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `demanda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demanda` (
  `dema_id` int(11) NOT NULL AUTO_INCREMENT,
  `dema_titulo` varchar(200) NOT NULL,
  `dema_status` enum('aberta','em_analise','em_andamento','resolvida','cancelada','aguardando') NOT NULL DEFAULT 'aberta',
  `dema_descricao` varchar(600) NOT NULL,
  `dema_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dema_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `dema_ordem` int(11) DEFAULT NULL,
  `cate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deferido_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`dema_id`),
  KEY `idx_demanda_status` (`dema_status`),
  KEY `idx_demanda_categoria` (`cate_id`),
  KEY `idx_demanda_user` (`user_id`),
  KEY `fk_demanda_deferido_por` (`deferido_por`),
  CONSTRAINT `fk_demanda_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_demanda_deferido_por` FOREIGN KEY (`deferido_por`) REFERENCES `usuario` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_demanda_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `demanda_resposta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demanda_resposta` (
  `dres_id` int(11) NOT NULL AUTO_INCREMENT,
  `dema_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dres_mensagem` varchar(500) NOT NULL,
  `dres_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`dres_id`),
  KEY `idx_demanda_resposta_demanda` (`dema_id`),
  KEY `idx_demanda_resposta_usuario` (`user_id`),
  CONSTRAINT `fk_demanda_resposta_demanda` FOREIGN KEY (`dema_id`) REFERENCES `demanda` (`dema_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_demanda_resposta_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evento` (
  `even_id` int(11) NOT NULL AUTO_INCREMENT,
  `even_titulo` varchar(200) NOT NULL,
  `even_descricao` varchar(600) NOT NULL,
  `cate_id` int(11) NOT NULL,
  `cale_id` int(11) NOT NULL,
  `even_hora_inicio` time DEFAULT NULL,
  `even_hora_fim` time DEFAULT NULL,
  `even_ordem` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`even_id`),
  KEY `fk_evento_categoria` (`cate_id`),
  KEY `fk_evento_calendario` (`cale_id`),
  KEY `idx_evento_calendario_ordem` (`cale_id`,`even_ordem`,`even_id`),
  CONSTRAINT `fk_evento_calendario` FOREIGN KEY (`cale_id`) REFERENCES `calendario` (`cale_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_evento_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `historico_demanda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_demanda` (
  `hist_id` int(11) NOT NULL AUTO_INCREMENT,
  `dema_id` int(11) NOT NULL,
  `hist_status` enum('aberta','em_analise','em_andamento','resolvida','cancelada','aguardando') NOT NULL,
  `hist_obs` varchar(300) DEFAULT NULL,
  `hist_data` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `fk_historico_demanda` (`dema_id`),
  CONSTRAINT `fk_historico_demanda` FOREIGN KEY (`dema_id`) REFERENCES `demanda` (`dema_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `historico_sugestao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_sugestao` (
  `hist_id` int(11) NOT NULL AUTO_INCREMENT,
  `suge_id` int(11) NOT NULL,
  `hist_status` enum('enviada','em_analise','aceita','recusada','em_andamento') NOT NULL,
  `hist_obs` varchar(300) DEFAULT NULL,
  `hist_data` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`hist_id`),
  KEY `fk_historico_sugestao` (`suge_id`),
  CONSTRAINT `fk_historico_sugestao` FOREIGN KEY (`suge_id`) REFERENCES `sugestao` (`suge_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `instituto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instituto` (
  `inst_id` int(11) NOT NULL AUTO_INCREMENT,
  `inst_nome` varchar(300) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`inst_id`),
  UNIQUE KEY `uk_instituto_nome` (`inst_nome`),
  KEY `fk_instituto_usuario` (`user_id`),
  CONSTRAINT `fk_instituto_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notificacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificacao` (
  `noti_id` int(11) NOT NULL AUTO_INCREMENT,
  `noti_titulo` varchar(200) NOT NULL,
  `noti_mensagem` varchar(500) DEFAULT NULL,
  `noti_status` enum('nao_lida','lida') NOT NULL DEFAULT 'nao_lida',
  `noti_data` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`noti_id`),
  KEY `fk_notificacao_usuario` (`user_id`),
  CONSTRAINT `fk_notificacao_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `perfil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perfil` (
  `perf_id` int(11) NOT NULL AUTO_INCREMENT,
  `perf_nome` varchar(50) NOT NULL,
  `perf_nivel` tinyint(4) NOT NULL,
  `perf_descricao` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`perf_id`),
  UNIQUE KEY `uk_perfil_nome` (`perf_nome`),
  UNIQUE KEY `uk_perfil_nivel` (`perf_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recuperacao_senha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recuperacao_senha` (
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expira_em` datetime NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `usado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_recuperacao_senha_token_hash` (`token_hash`),
  KEY `idx_recuperacao_senha_expira_em` (`expira_em`),
  CONSTRAINT `fk_recuperacao_senha_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sugestao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sugestao` (
  `suge_id` int(11) NOT NULL AUTO_INCREMENT,
  `suge_titulo` varchar(200) NOT NULL,
  `suge_descricao` varchar(600) NOT NULL,
  `suge_categoria` enum('infraestrutura','eventos','academico','bem-estar','outros') NOT NULL DEFAULT 'outros',
  `suge_status` enum('enviada','em_analise','aceita','recusada','em_andamento') NOT NULL DEFAULT 'enviada',
  `suge_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `suge_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `suge_ordem` int(11) DEFAULT NULL,
  `cate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`suge_id`),
  KEY `fk_sugestao_categoria` (`cate_id`),
  KEY `fk_sugestao_usuario` (`user_id`),
  CONSTRAINT `fk_sugestao_categoria` FOREIGN KEY (`cate_id`) REFERENCES `categoria` (`cate_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sugestao_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sugestao_resposta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sugestao_resposta` (
  `sres_id` int(11) NOT NULL AUTO_INCREMENT,
  `suge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sres_mensagem` varchar(500) NOT NULL,
  `sres_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sres_id`),
  KEY `idx_sugestao_resposta_sugestao` (`suge_id`),
  KEY `idx_sugestao_resposta_usuario` (`user_id`),
  CONSTRAINT `fk_sugestao_resposta_sugestao` FOREIGN KEY (`suge_id`) REFERENCES `sugestao` (`suge_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sugestao_resposta_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `user_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uk_usuario_email` (`user_email`),
  UNIQUE KEY `uk_usuario_telefone` (`user_telefone`),
  UNIQUE KEY `uk_usuario_matricula` (`user_matricula`),
  KEY `idx_usuario_perfil` (`perf_id`),
  CONSTRAINT `fk_usuario_perfil` FOREIGN KEY (`perf_id`) REFERENCES `perfil` (`perf_id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_email` CHECK (`user_email` like _utf8mb4'%@%.%'),
  CONSTRAINT `chk_telefone` CHECK (char_length(`user_telefone`) >= 10)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuario_permissao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_permissao` (
  `user_id` int(11) NOT NULL,
  `permissao` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `concedida_por` int(11) DEFAULT NULL,
  `concedida_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`permissao`),
  KEY `idx_usuario_permissao_permissao` (`permissao`),
  KEY `idx_usuario_permissao_concedida_por` (`concedida_por`),
  CONSTRAINT `fk_usuario_permissao_concedida_por` FOREIGN KEY (`concedida_por`) REFERENCES `usuario` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_usuario_permissao_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vw_avisos`;
/*!50001 DROP VIEW IF EXISTS `vw_avisos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_avisos` AS SELECT
 1 AS `avis_id`,
  1 AS `avis_titulo`,
  1 AS `avis_descricao`,
  1 AS `avis_data`,
  1 AS `categoria`,
  1 AS `criado_por`,
  1 AS `user_id` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_dashboard`;
/*!50001 DROP VIEW IF EXISTS `vw_dashboard`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_dashboard` AS SELECT
 1 AS `total_usuarios`,
  1 AS `total_admins`,
  1 AS `total_alunos`,
  1 AS `total_gremio`,
  1 AS `total_demandas`,
  1 AS `demandas_abertas`,
  1 AS `demandas_em_analise`,
  1 AS `demandas_em_andamento`,
  1 AS `demandas_resolvidas`,
  1 AS `total_sugestoes`,
  1 AS `sugestoes_enviadas`,
  1 AS `sugestoes_aceitas`,
  1 AS `total_eventos`,
  1 AS `total_avisos`,
  1 AS `notificacoes_nao_lidas` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_demandas`;
/*!50001 DROP VIEW IF EXISTS `vw_demandas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_demandas` AS SELECT
 1 AS `dema_id`,
  1 AS `dema_titulo`,
  1 AS `dema_descricao`,
  1 AS `dema_status`,
  1 AS `dema_atualizacao`,
  1 AS `dema_criado_em`,
  1 AS `categoria`,
  1 AS `cadastrado_por`,
  1 AS `email_cadastro`,
  1 AS `deferido_por`,
  1 AS `user_id`,
  1 AS `deferido_por_id` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_demandas_abertas`;
/*!50001 DROP VIEW IF EXISTS `vw_demandas_abertas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_demandas_abertas` AS SELECT
 1 AS `dema_id`,
  1 AS `dema_titulo`,
  1 AS `dema_descricao`,
  1 AS `categoria`,
  1 AS `cadastrado_por`,
  1 AS `dema_atualizacao` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_eventos`;
/*!50001 DROP VIEW IF EXISTS `vw_eventos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_eventos` AS SELECT
 1 AS `even_id`,
  1 AS `even_titulo`,
  1 AS `even_descricao`,
  1 AS `categoria`,
  1 AS `cale_dia`,
  1 AS `cale_semana`,
  1 AS `cale_mes`,
  1 AS `cale_ano` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_historico_demandas`;
/*!50001 DROP VIEW IF EXISTS `vw_historico_demandas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_historico_demandas` AS SELECT
 1 AS `hist_id`,
  1 AS `dema_id`,
  1 AS `dema_titulo`,
  1 AS `hist_status`,
  1 AS `hist_obs`,
  1 AS `hist_data`,
  1 AS `alterado_por` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_historico_sugestoes`;
/*!50001 DROP VIEW IF EXISTS `vw_historico_sugestoes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_historico_sugestoes` AS SELECT
 1 AS `hist_id`,
  1 AS `suge_id`,
  1 AS `suge_titulo`,
  1 AS `hist_status`,
  1 AS `hist_obs`,
  1 AS `hist_data` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_notificacoes`;
/*!50001 DROP VIEW IF EXISTS `vw_notificacoes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_notificacoes` AS SELECT
 1 AS `noti_id`,
  1 AS `noti_titulo`,
  1 AS `noti_mensagem`,
  1 AS `noti_status`,
  1 AS `noti_data`,
  1 AS `user_nome`,
  1 AS `user_email`,
  1 AS `user_id` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_sugestoes`;
/*!50001 DROP VIEW IF EXISTS `vw_sugestoes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_sugestoes` AS SELECT
 1 AS `suge_id`,
  1 AS `suge_titulo`,
  1 AS `suge_descricao`,
  1 AS `suge_status`,
  1 AS `suge_criado_em`,
  1 AS `categoria`,
  1 AS `cadastrado_por`,
  1 AS `email_cadastro`,
  1 AS `user_id` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_usuarios`;
/*!50001 DROP VIEW IF EXISTS `vw_usuarios`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_usuarios` AS SELECT
 1 AS `user_id`,
  1 AS `user_nome`,
  1 AS `user_email`,
  1 AS `user_telefone`,
  1 AS `user_matricula`,
  1 AS `user_curso`,
  1 AS `user_ano_ingresso`,
  1 AS `user_instituicao`,
  1 AS `user_status`,
  1 AS `perf_nome`,
  1 AS `perf_nivel`,
  1 AS `user_tema` */;
SET character_set_client = @saved_cs_client;

USE `bd_demandfy`;
/*!50001 DROP VIEW IF EXISTS `vw_avisos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_avisos` AS select `a`.`avis_id` AS `avis_id`,`a`.`avis_titulo` AS `avis_titulo`,`a`.`avis_descricao` AS `avis_descricao`,`a`.`avis_data` AS `avis_data`,`c`.`cate_nome` AS `categoria`,`u`.`user_nome` AS `criado_por`,`a`.`user_id` AS `user_id` from ((`aviso` `a` join `categoria` `c` on(`a`.`cate_id` = `c`.`cate_id`)) join `usuario` `u` on(`a`.`user_id` = `u`.`user_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_dashboard`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_dashboard` AS select 1 AS `total_usuarios`,1 AS `total_admins`,1 AS `total_alunos`,1 AS `total_gremio`,1 AS `total_demandas`,1 AS `demandas_abertas`,1 AS `demandas_em_analise`,1 AS `demandas_em_andamento`,1 AS `demandas_resolvidas`,1 AS `total_sugestoes`,1 AS `sugestoes_enviadas`,1 AS `sugestoes_aceitas`,1 AS `total_eventos`,1 AS `total_avisos`,1 AS `notificacoes_nao_lidas` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_demandas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_demandas` AS select 1 AS `dema_id`,1 AS `dema_titulo`,1 AS `dema_descricao`,1 AS `dema_status`,1 AS `dema_atualizacao`,1 AS `dema_criado_em`,1 AS `categoria`,1 AS `cadastrado_por`,1 AS `email_cadastro`,1 AS `deferido_por`,1 AS `user_id`,1 AS `deferido_por_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_demandas_abertas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_demandas_abertas` AS select 1 AS `dema_id`,1 AS `dema_titulo`,1 AS `dema_descricao`,1 AS `categoria`,1 AS `cadastrado_por`,1 AS `dema_atualizacao` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_eventos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_eventos` AS select 1 AS `even_id`,1 AS `even_titulo`,1 AS `even_descricao`,1 AS `categoria`,1 AS `cale_dia`,1 AS `cale_semana`,1 AS `cale_mes`,1 AS `cale_ano` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_historico_demandas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_historico_demandas` AS select 1 AS `hist_id`,1 AS `dema_id`,1 AS `dema_titulo`,1 AS `hist_status`,1 AS `hist_obs`,1 AS `hist_data`,1 AS `alterado_por` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_historico_sugestoes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_historico_sugestoes` AS select 1 AS `hist_id`,1 AS `suge_id`,1 AS `suge_titulo`,1 AS `hist_status`,1 AS `hist_obs`,1 AS `hist_data` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_notificacoes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_notificacoes` AS select 1 AS `noti_id`,1 AS `noti_titulo`,1 AS `noti_mensagem`,1 AS `noti_status`,1 AS `noti_data`,1 AS `user_nome`,1 AS `user_email`,1 AS `user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_sugestoes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_sugestoes` AS select 1 AS `suge_id`,1 AS `suge_titulo`,1 AS `suge_descricao`,1 AS `suge_status`,1 AS `suge_criado_em`,1 AS `categoria`,1 AS `cadastrado_por`,1 AS `email_cadastro`,1 AS `user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_usuarios`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_usuarios` AS select 1 AS `user_id`,1 AS `user_nome`,1 AS `user_email`,1 AS `user_telefone`,1 AS `user_matricula`,1 AS `user_curso`,1 AS `user_ano_ingresso`,1 AS `user_instituicao`,1 AS `user_status`,1 AS `perf_nome`,1 AS `perf_nivel`,1 AS `user_tema` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `categoria` (`cate_nome`, `cate_tipo`) VALUES
  ('Infraestrutura', 'demanda'),
  ('Limpeza', 'demanda'),
  ('TI / Sistemas', 'demanda'),
  ('Segurança', 'demanda'),
  ('Outros', 'demanda'),
  ('Infraestrutura', 'sugestao'),
  ('Eventos', 'sugestao'),
  ('Acadêmico', 'sugestao'),
  ('Bem-estar', 'sugestao'),
  ('Outros', 'sugestao')
ON DUPLICATE KEY UPDATE `cate_nome` = VALUES(`cate_nome`);

