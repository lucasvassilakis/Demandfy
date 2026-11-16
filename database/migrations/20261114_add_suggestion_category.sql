ALTER TABLE sugestao
  ADD COLUMN suge_categoria ENUM('infraestrutura', 'eventos', 'academico', 'bem-estar', 'outros')
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outros'
    AFTER suge_descricao;
