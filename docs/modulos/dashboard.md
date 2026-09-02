# 📊 Módulo: Dashboard & Painel Operacional
**Arquivo Principal:** `dashboard.php`  
**Escopo de Acesso:** Administrador e Operador de Caixa

---

## 1. Objetivo & Contexto de Negócio
O Dashboard é o centro nevrálgico do MrStock ERP. Ele fornece ao lojista uma visão executiva instantânea da saúde da Papelaria Real, consolidando faturamento diário, volume de vendas, estoque baixo e produtos próximos do vencimento (shelf-life), além de oferecer um terminal de **Venda Rápida Externa** para saídas ágeis no balcão sem abrir o PDV completo.

---

## 2. Interface & Componentes Visuais
- **Bento Grid de Estatísticas (4 KPIs):**
  1. *Faturamento Hoje (R$)*: Total faturado no dia com badge percentual e formatação tabular.
  2. *Vendas Realizadas*: Contagem total de transações finalizadas hoje.
  3. *Estoque Baixo*: Quantidade de SKUs com saldo abaixo do estoque mínimo (`quantidade <= estoque_minimo`).
  4. *Alerta de Vencimento (Shelf-Life)*: Produtos vencendo na janela configurada (padrão de 30 dias).
- **Terminal de Venda Rápida Externa:** Formulário ágil no topo para seleção de produto, quantidade, forma de pagamento e desconto.
- **Tabela de Vendas Recentes:** Listagem com número da venda, cliente, horário, valor total em negrito e forma de pagamento com ícones temáticos (`render_forma_pagamento()`).

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Consultas de Estatísticas Principais (KPIs)
```php
// Faturamento e Contagem de Vendas do Dia Atual
$stmt = $pdo->query("SELECT COUNT(*) as total_vendas, COALESCE(SUM(total), 0) as faturamento_hoje 
                     FROM vendas 
                     WHERE DATE(data_venda) = CURDATE()");
$kpiVendas = $stmt->fetch(PDO::FETCH_ASSOC);

// Produtos em Estoque Crítico
$stmt = $pdo->query("SELECT COUNT(*) as total_baixo 
                     FROM produtos 
                     WHERE status = 'ativo' AND quantidade <= estoque_minimo");
$kpiEstoque = $stmt->fetch(PDO::FETCH_ASSOC);

// Produtos com Validade Próxima (Shelf-Life Dinâmico)
$diasAlertaVenc = (int)get_app_config($pdo, 'alerta_vencimento_dias', '30');
$stmt = $pdo->prepare("SELECT COUNT(*) as total_vencendo 
                       FROM produtos 
                       WHERE status = 'ativo' 
                         AND validade IS NOT NULL 
                         AND validade <= DATE_ADD(CURDATE(), INTERVAL ? DAY)");
$stmt->execute([$diasAlertaVenc]);
$kpiValidade = $stmt->fetch(PDO::FETCH_ASSOC);
```

### 3.2 Processamento da Venda Rápida (Transação ACID & Lock Pessimista)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'venda_rapida') {
    csrf_verify();
    $produtoId  = (int)$_POST['produto_id'];
    $quantidade = (int)$_POST['quantidade'];
    $formaPagto = clean_input($_POST['forma_pagamento']);
    
    $pdo->beginTransaction();
    try {
        // Bloqueio de linha pessimista
        $stmt = $pdo->prepare("SELECT nome, quantidade, preco_venda, preco_compra FROM produtos WHERE id = ? FOR UPDATE");
        $stmt->execute([$produtoId]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prod || $prod['quantidade'] < $quantidade) {
            throw new Exception("Saldo insuficiente em estoque!");
        }
        
        $totalVenda = $prod['preco_venda'] * $quantidade;
        
        // Insere Venda
        $stmt = $pdo->prepare("INSERT INTO vendas (cliente_id, total, forma_pagamento) VALUES (NULL, ?, ?)");
        $stmt->execute([$totalVenda, $formaPagto]);
        $vendaId = $pdo->lastInsertId();
        
        // Insere Item da Venda
        $stmt = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$vendaId, $produtoId, $quantidade, $prod['preco_venda']]);
        
        // Decrementa Estoque
        $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
        $stmt->execute([$quantidade, $produtoId]);
        
        // Registra Movimentação
        $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'saida_venda', ?, ?)");
        $stmt->execute([$produtoId, $quantidade, "Venda Rápida #$vendaId"]);
        
        $pdo->commit();
        set_flash("Venda Rápida #$vendaId registrada com sucesso!", "success");
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash("Erro na venda: " . $e->getMessage(), "danger");
    }
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Autenticação Obrigatória:** Invocação de `require_auth()` no topo.
- **Isolamento de Funções:** Operadores Caixa visualizam apenas o faturamento do dia e o formulário de venda rápida, sem acesso a menus restritos.
- **Proteção CSRF:** Validação atômica de token em todas as submissões POST da Venda Rápida.
- **Sanitização XSS:** Todos os nomes de produtos e formas de pagamento escapados via `htmlspecialchars()`.
