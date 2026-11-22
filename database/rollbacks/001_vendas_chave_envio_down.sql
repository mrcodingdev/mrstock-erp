-- Reversão manual da migration 001. A retirada do índice e da coluna é permanente.
-- Guarde um backup verificado de vendas e interrompa os envios do PDV antes da execução.
-- Primeiro, ative e confira um checkout compatível com o schema sem chave_envio.
-- Só então execute este SQL, com o PDV ainda interrompido.
-- Para reverter apenas a aplicação, preserve esta coluna e este índice.
ALTER TABLE vendas
    DROP INDEX uq_vendas_chave_envio,
    DROP COLUMN chave_envio;
