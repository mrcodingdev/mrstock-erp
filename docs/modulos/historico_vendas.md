# Módulo de Histórico de Vendas

**Arquivos:** `vendas/historico.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Permitir a consulta retroativa de todas as transações de venda realizadas na Papelaria Real, filtros avançados e reimpressão de cupons.

---

## 1. Cards de Resumo Executivo (KPIs do Filtro)

Ao aplicar filtros de data ou forma de pagamento, o topo da tela atualiza dinamicamente:
- **Faturamento do Período (R$):** Valor bruto faturado nas vendas selecionadas.
- **Quantidade de Vendas:** Volume de atendimentos finalizados.
- **Ticket Médio (R$):** Faturamento dividido pelo número de vendas.

---

## 2. Filtros e Ações

- **Filtro por Período:** Data Inicial e Data Final.
- **Filtro por Forma de Pagamento:** Dinheiro, PIX, Cartão de Crédito, Cartão de Débito.
- **Menu de Ações (3 Pontos):**
  - **Reimprimir Cupom:** Abre a tela do cupom térmico para reemissão.
  - **Visualizar Detalhes:** Exibe os itens vendidos e operador do caixa.
  - **Cancelar Venda:** Estorna a venda e devolve as quantidades automaticamente ao estoque físico.