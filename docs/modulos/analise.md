# Módulo de Inteligência Comercial (Centro de Análise & BI)

**Arquivos:** `relatorios/analise.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Apoiar a tomada de decisões gerenciais através de cálculos de lucratividade, margem comercial média e gráficos estatísticos interativos.

---

## 1. Seletor de Períodos & Métricas Financeiras

O centro de análise permite alternar entre 3 janelas temporais:
1. **Últimos 7 Dias:** Acompanhamento da semana em curso.
2. **Mês Atual:** Fechamento do período contábil vigente.
3. **Últimos 12 Meses:** Visão macro e sazonalidade (volta às aulas, períodos promocionais).

### 📈 Indicadores Calculados:
- **Receita Total Bruta (R$):** Valor total faturado no período.
- **Custo Total de Mercadorias Vendidas - CMV (R$):** Custo de compra dos produtos baixados.
- **Lucro Bruto (R$):** $\text{Receita} - \text{CMV}$.
- **Margem de Lucro Geral (%):** $(\text{Lucro Bruto} / \text{Receita}) \times 100$.

---

## 2. Gráficos com Chart.js 4+

1. **Faturamento vs Custo:** Gráfico comparativo de barras revelando a rentabilidade diária/mensal.
2. **Distribuição de Vendas por Categoria:** Gráfico em rosca (*Doughnut*) destacando a participação das categorias no faturamento.
3. **Curva ABC / Top Produtos:** Gráfico horizontal classificando os itens de maior impacto financeiro.