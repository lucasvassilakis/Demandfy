CREATE TABLE IF NOT EXISTS `recuperacao_senha` (
    `user_id` INT NOT NULL,
    `token_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `expira_em` DATETIME NOT NULL,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usado_em` DATETIME DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uq_recuperacao_senha_token_hash` (`token_hash`),
    KEY `idx_recuperacao_senha_expira_em` (`expira_em`),
    CONSTRAINT `fk_recuperacao_senha_usuario`
        FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
