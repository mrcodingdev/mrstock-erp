-- Aplicar antes de ativar o código que grava chave_envio.
ALTER TABLE vendas
    ADD COLUMN chave_envio CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NULL,
    ADD UNIQUE INDEX uq_vendas_chave_envio (chave_envio);
