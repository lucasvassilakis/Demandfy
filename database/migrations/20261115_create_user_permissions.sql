CREATE TABLE IF NOT EXISTS `usuario_permissao` (
    `user_id` INT(11) NOT NULL,
    `permissao` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `concedida_por` INT(11) DEFAULT NULL,
    `concedida_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `permissao`),
    KEY `idx_usuario_permissao_permissao` (`permissao`),
    KEY `idx_usuario_permissao_concedida_por` (`concedida_por`),
    CONSTRAINT `fk_usuario_permissao_usuario`
        FOREIGN KEY (`user_id`) REFERENCES `usuario` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_usuario_permissao_concedida_por`
        FOREIGN KEY (`concedida_por`) REFERENCES `usuario` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
