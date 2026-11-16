CREATE TABLE IF NOT EXISTS `demanda_resposta` (
    `dres_id` INT(11) NOT NULL AUTO_INCREMENT,
    `dema_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `dres_mensagem` VARCHAR(500) NOT NULL,
    `dres_criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`dres_id`),
    KEY `idx_demanda_resposta_demanda` (`dema_id`),
    KEY `idx_demanda_resposta_usuario` (`user_id`),
    CONSTRAINT `fk_demanda_resposta_demanda`
        FOREIGN KEY (`dema_id`) REFERENCES `demanda` (`dema_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_demanda_resposta_usuario`
        FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sugestao_resposta` (
    `sres_id` INT(11) NOT NULL AUTO_INCREMENT,
    `suge_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `sres_mensagem` VARCHAR(500) NOT NULL,
    `sres_criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`sres_id`),
    KEY `idx_sugestao_resposta_sugestao` (`suge_id`),
    KEY `idx_sugestao_resposta_usuario` (`user_id`),
    CONSTRAINT `fk_sugestao_resposta_sugestao`
        FOREIGN KEY (`suge_id`) REFERENCES `sugestao` (`suge_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sugestao_resposta_usuario`
        FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
