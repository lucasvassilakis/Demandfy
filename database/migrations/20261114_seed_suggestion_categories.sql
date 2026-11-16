INSERT INTO categoria (cate_nome, cate_tipo) VALUES
  ('Infraestrutura', 'sugestao'),
  ('Eventos', 'sugestao'),
  ('Acadêmico', 'sugestao'),
  ('Bem-estar', 'sugestao'),
  ('Outros', 'sugestao')
ON DUPLICATE KEY UPDATE cate_nome = VALUES(cate_nome);
