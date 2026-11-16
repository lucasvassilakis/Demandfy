INSERT INTO categoria (cate_nome, cate_tipo) VALUES
  ('Infraestrutura', 'demanda'),
  ('Limpeza', 'demanda'),
  ('TI / Sistemas', 'demanda'),
  ('Segurança', 'demanda'),
  ('Outros', 'demanda')
ON DUPLICATE KEY UPDATE cate_nome = VALUES(cate_nome);
