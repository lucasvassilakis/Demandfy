ALTER TABLE evento
  ADD COLUMN even_hora_inicio TIME NULL AFTER cale_id,
  ADD COLUMN even_hora_fim TIME NULL AFTER even_hora_inicio,
  ADD COLUMN even_ordem INT NOT NULL DEFAULT 0 AFTER even_hora_fim,
  ADD INDEX idx_evento_calendario_ordem (cale_id, even_ordem, even_id);
