# 📊 Módulo: Centro de Análise Avançada & Curva ABC (80-15-5)
**Arquivo Principal:** `relatorios/analise.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
Aplica o princípio de Pareto ao acervo da Papelaria Real, classificando os produtos em **Curva ABC (80-15-5)** com base na representatividade de faturamento acumulado:
- **Classe A (80% da Receita):** Produtos de altíssimo giro (cadernos universitários, canetas azuis, resmas de sulfite). Nunca podem faltar em estoque.
- **Classe B (15% da Receita):** Produtos de giro moderado (estojos, calculadoras, tintas específicas).
- **Classe C (5% da Receita):** Produtos de cauda longa (compassos profissionais, réguas técnicas). Exigem compras fracionadas.

---

## 2. Interface & Componentes Visuais
- **Gráficos Interativos Chart.js:**
  1. *Faturamento vs Custo Histórico (Gráfico de Linhas / Área)*
  2. *Distribuição de Vendas por Categoria (Gráfico Doughnut)*
  3. *Top 10 Produtos Mais Vendidos (Gráfico de Barras Horizontais)*
- **Tabela da Curva ABC:** Produto, Quantidade Vendida, Faturamento Total, % de Representatividade Individual, % Acumulado e **Badge de Classificação (A, B, C)**.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Algoritmo de Classificação da Curva ABC (80-15-5)
```php
function calcular_curva_abc(PDO $pdo, string $dataInicio, string $dataFim): array {
    // 1. Total faturado por produto
    $sql = "SELECT p.id, p.nome, c.nome as categoria_nome, 
                   SUM(vi.quantidade) as qtd_vendida, 
                   SUM(vi.quantidade * vi.preco_unitario) as faturamento_total 
            FROM vendas_itens vi 
            JOIN vendas v ON vi.venda_id = v.id 
            JOIN produtos p ON vi.produto_id = p.id 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE DATE(v.data_venda) BETWEEN ? AND ? 
            GROUP BY p.id 
            ORDER BY faturamento_total DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataInicio, $dataFim]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $faturamentoGeral = array_sum(array_column($produtos, 'faturamento_total'));
    $acumulado = 0;
    
    foreach ($produtos as &$prod) {
        $faturamentoProd = (float)$prod['faturamento_total'];
        $percIndividual = ($faturamentoGeral > 0) ? ($faturamentoProd / $faturamentoGeral) * 100 : 0;
        $acumulado += $percIndividual;
        
        $prod['perc_individual'] = round($percIndividual, 2);
        $prod['perc_acumulado']  = round($acumulado, 2);
        
        // Classificação Pareto
        if ($acumulado <= 80.0) {
            $prod['classe_abc'] = 'A';
        } elseif ($acumulado <= 95.0) {
            $prod['classe_abc'] = 'B';
        } else {
            $prod['classe_abc'] = 'C';
        }
    }
    return $produtos;
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Acesso Restrito:** Acesso exclusivo ao perfil `admin`.
- **Renderização Client-Side Segura:** Os dados para o Chart.js são serializados via `json_encode()` com sanitização UTF-8.
