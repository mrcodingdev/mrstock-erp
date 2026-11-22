-- Reversão manual da migration 001. A retirada do índice e da coluna é permanente.
-- Guarde um backup verificado de vendas e interrompa os envios do PDV antes da execução.
-- O código em execução precisa ser compatível com o schema sem chave_envio.
-- Para reverter apenas a aplicação, preserve esta coluna e este índice.
ALTER TABLE vendas
    DROP INDEX uq_vendas_chave_envio,
    DROP COLUMN chave_envio;
