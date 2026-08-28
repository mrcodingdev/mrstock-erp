# Módulo de Emissão de Relatórios & Exportação

**Arquivos:** `relatorios/index.php`, `relatorios/pdf.php`, `relatorios/excel.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Gerar relatórios executivos para impressão em PDF e exportar planilhas Excel formatadas.

---

## 1. Tipos de Relatórios Disponíveis

1. **Relatório de Vendas por Período:** Extrato detalhado com totalizadores por forma de pagamento.
2. **Relatório de Posição de Estoque:** Inventário físico com custo, preço de venda e margens.
3. **Relatório de Compras e Fornecedores:** Histórico de aquisições e contas pagas/pendentes.
4. **Relatório de Produtos Críticos:** Itens abaixo do estoque mínimo.

---

## 2. Exportação para Excel (`relatorios/excel.php`)

A exportação de planilha foi calibrada para o padrão corporativo:
- **Formatação de 9 Colunas Estritas (A até I):** Elimina colunas fantasmas ou células desconfiguradas.
- **Cabeçalhos:** Estilizados com fundo verde escuro e texto em negrito.
- **Tipos de Dados:** Numéricos e monetários formatados no padrão brasileiro (`R$ #.##0,00`).