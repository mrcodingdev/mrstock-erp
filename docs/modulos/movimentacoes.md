# 📋 Módulo: Movimentações de Estoque & Ajuste de Perdas
**Arquivo Principal:** `produtos/movimentacoes.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
O módulo atua como o **Livro-Razão Contábil do Estoque** da Papelaria Real. Ele audita com carimbo de data/hora todas as alterações físicas de saldo decorrentes de compras, vendas no PDV, devoluções de clientes, devoluções a fornecedores e perdas/avarias de mercadorias (cadernos molhados, canetas danificadas, etc.).

---

## 2. Interface & Componentes Visuais
- **Cards Resumo de Movimento:** Quantitativo de Entradas, Saídas, Devoluções e Perdas registradas no mês.
- **Barra de Filtros:** Filtro por Tipo de Movimentação (`entrada_compra`, `saida_venda`, `devolucao_cliente`, `devolucao_fornecedor`, `perda`), período e busca por produto.
- **Modal de Lançamento de Ajuste Manual / Perda:** Permite ao administrador lançar quebras ou avarias com justificativa obrigatória.
- **Tabela Cronológica:** Data/Hora, Produto, Tipo (com badges coloridos), Quantidade e Observação.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Registro de Ajuste Manual de Estoque (Transação ACID)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'lancar_ajuste') {
    csrf_verify();
    $produtoId  = (int)$_POST['produto_id'];
    $tipo       = clean_input($_POST['tipo']);
    $quantidade = (int)$_POST['quantidade'];
    $motivo     = clean_input($_POST['observacao']);
    
    $pdo->beginTransaction();
    try {
        // Bloqueio pessimista do produto
        $stmt = $pdo->prepare("SELECT quantidade, nome FROM produtos WHERE id = ? FOR UPDATE");
        $stmt->execute([$produtoId]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prod) {
            throw new Exception("Produto não localizado.");
        }
        
        // Ajusta o saldo físico
        if ($tipo === 'perda' || $tipo === 'saida_venda' || $tipo === 'devolucao_fornecedor') {
            if ($prod['quantidade'] < $quantidade) {
                throw new Exception("Saldo insuficiente para baixa de perda!");
            }
            $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
            $stmt->execute([$quantidade, $produtoId]);
        } else {
            $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
            $stmt->execute([$quantidade, $produtoId]);
        }
        
        // Registra a movimentação
        $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao, data_movimento) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$produtoId, $tipo, $quantidade, $motivo]);
        
        $pdo->commit();
        set_flash("Movimentação registrada com sucesso!", "success");
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash("Erro ao movimentar estoque: " . $e->getMessage(), "danger");
    }
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Perfil Restrito:** Apenas o Administrador pode consultar movimentações ou lançar perdas/avarias.
- **Imutabilidade de Histórico:** Movimentações registradas não podem ser editadas ou excluídas diretamente, garantindo auditoria forense.
