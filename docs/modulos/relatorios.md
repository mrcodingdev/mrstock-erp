# 📈 Módulo: Centro de Relatórios, Inventário Geral & DRE
**Arquivos Principais:** `relatorios/index.php`, `relatorios/pdf.php`, `relatorios/excel.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
Fornece inteligência contábil e fiscal para os proprietários da Papelaria Real (Sueli & Osnir). O módulo consolida o **DRE Gerencial de Varejo** (Demonstrativo de Resultados do Exercício), calcula o valor patrimonial imobilizado em estoque, detalha produtos vencendo na janela configurada (shelf-life) e exporta relatórios executivos formatados para **PDF A4 em 9 colunas** e planilhas Excel.

---

## 2. Interface & Componentes Visuais
- **Painel Superior de Métricas Consolidadas:**
  1. *Patrimônio em Estoque (Custo Total)*
  2. *Potencial de Venda (Preço de Venda Total)*
  3. *Margem Bruta Projetada (Lucro Potencial)*
  4. *SKUs Monitorados*
- **Tabela DRE Gerencial de Varejo:**
  - `(+) Receita Bruta de Vendas`
  - `(-) Descontos Concedidos no Caixa`
  - `(=) Receita Líquida`
  - `(-) Custo das Mercadorias Vendidas (CMV Real)`
  - `(=) Lucro Bruto Real (Margem %)`
- **Ações de Exportação em 1-Clique:** Botões para emissão de PDF Executivo (`/relatorios/pdf.php`) e Exportação CSV/Excel (`/relatorios/excel.php`).

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Consulta e Apuração do DRE Gerencial
```php
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim    = $_GET['data_fim'] ?? date('Y-m-d');

// 1. Receita e Descontos
$sqlVendas = "SELECT COALESCE(SUM(total), 0) as receita_liquida, COUNT(*) as total_cupons 
              FROM vendas 
              WHERE DATE(data_venda) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sqlVendas);
$stmt->execute([$dataInicio, $dataFim]);
$dadosVendas = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Custo das Mercadorias Vendidas (CMV) Real
$sqlCmv = "SELECT COALESCE(SUM(vi.quantidade * p.preco_compra), 0) as cmv_total 
           FROM vendas_itens vi 
           JOIN vendas v ON vi.venda_id = v.id 
           JOIN produtos p ON vi.produto_id = p.id 
           WHERE DATE(v.data_venda) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sqlCmv);
$stmt->execute([$dataInicio, $dataFim]);
$dadosCmv = $stmt->fetch(PDO::FETCH_ASSOC);

$receitaLiquida = (float)$dadosVendas['receita_liquida'];
$cmvTotal       = (float)$dadosCmv['cmv_total'];
$lucroBruto     = $receitaLiquida - $cmvTotal;
$margemPercent  = ($receitaLiquida > 0) ? ($lucroBruto / $receitaLiquida) * 100 : 0;
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Acesso Restrito:** Apenas o Administrador pode visualizar o DRE, custos e margens de lucro.
- **Parametrização Segura:** Filtros de data validados contra injeção SQL via PDO Prepared Statements.
