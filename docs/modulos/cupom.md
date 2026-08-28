# Módulo de Emissão de Cupom Não-Fiscal Térmico

**Arquivos:** `vendas/cupom.php`  
**Acesso:** Operadores de Caixa (`caixa`) e Administradores (`admin`)  
**Objetivo:** Renderizar e imprimir o comprovante de venda no padrão térmico comercial (80mm e 58mm).

---

## 1. Características do Cupom Térmico

- **Cabeçalho:** Nome Fantasia (Papelaria Real), Razão Social, CNPJ, Endereço e Telefone.
- **Corpo:** Lista de produtos com quantidade, valor unitário e subtotal formatado.
- **Rodapé:** Total da venda, forma de pagamento, valor recebido, troco e Hash SHA-256 de autenticidade.
- **Avisos Legais:** Disclaimer explícito *"DOCUMENTO NÃO FISCAL - CONTROLE INTERNO"*.
- **Atalhos na Tela:** Botão **Imprimir** (dispara janela de impressão do SO) e Botão **Nova Venda** (retorna imediatamente ao PDV com foco no leitor).