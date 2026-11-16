ALTER TABLE demanda
  ADD COLUMN dema_ordem INT NULL AFTER dema_atualizacao;

ALTER TABLE sugestao
  ADD COLUMN suge_atualizacao DATETIME NULL AFTER suge_categoria,
  ADD COLUMN suge_ordem INT NULL AFTER suge_atualizacao;

UPDATE sugestao s
SET suge_atualizacao = COALESCE(
  (SELECT MAX(h.hist_data) FROM historico_sugestao h WHERE h.suge_id = s.suge_id),
  CURRENT_TIMESTAMP
);

ALTER TABLE sugestao
  MODIFY COLUMN suge_atualizacao DATETIME NOT NULL
  DEFAULT CURRENT_TIMESTAMP
  ON UPDATE CURRENT_TIMESTAMP;
