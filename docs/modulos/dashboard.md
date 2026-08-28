# Módulo de Dashboard & Painel Executivo

**Arquivos:** `dashboard.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Oferecer uma visão panorâmica 360º da saúde financeira, do fluxo de vendas e do nível de abastecimento de estoque da Papelaria Real em tempo real.

---

## 1. Cards de KPI Executivos (SalesOps Metrics)

No topo do painel, quatro cards informativos exibem os indicadores-chave de desempenho:

1. **Faturamento Hoje (R$):** Soma de todas as vendas com status `concluida` realizadas na data atual.
2. **Total de Vendas no Mês:** Quantidade acumulada de cupons emitidos no mês corrente.
3. **Alertas de Estoque Crítico:** Quantidade de produtos com estoque menor ou igual ao estoque mínimo.
4. **Catálogo Total Ativo:** Total de SKUs cadastrados e disponíveis para venda.

---

## 2. Fita de Alertas Inteligentes

O Dashboard verifica automaticamente o estado do inventário e exibe notificações proativas:
- ⚠️ **Estoque Mínimo Atingido:** Lista os produtos que exigem emissão urgente de pedido de compra ao fornecedor.
- ⏳ **Validade Próxima:** Alerta sobre produtos químicos (colas, tintas guache) com vencimento previsto para os próximos 30 dias.

---

## 3. Gráficos Interativos (Chart.js 4+)

1. **Evolução do Faturamento Diário:** Gráfico de linha/área demonstrando a curva de vendas dos últimos 7 dias.
2. **Top 5 Produtos Mais Vendidos:** Gráfico de barras com os itens de maior giro no balcão da Papelaria Real.

---

## 4. Atalho de Venda Rápida

O Dashboard conta com um widget de **Venda Rápida**:
- Permite selecionar um produto, a quantidade e a forma de pagamento diretamente na tela inicial.
- Executa a baixa com a mesma segurança transacional e bloqueio pessimista do PDV completo.