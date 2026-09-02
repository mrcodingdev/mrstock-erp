# 🛒 Módulo: Gestão de Compras & Entrada de Mercadorias (CMP)
**Arquivos Principais:** `compras/index.php`, `compras/nova.php`, `compras/visualizar.php`, `compras/functions.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
Gerencia a cadeia de suprimentos da Papelaria Real, registrando notas fiscais de entrada de fornecedores (Tilibra, Faber-Castell, Bic, Chamex, etc.). O módulo atualiza automaticamente os saldos de estoque e **recalcula o Custo Médio Ponderado (CMP)** de cada item, garantindo que o custo real do estoque reflita a média ponderada de todas as aquisições.

---

## 2. Interface & Componentes Visuais
- **Tela de Nova Compra com Lançamento Dinâmico:** Seletor de Fornecedor, Número da NF, Forma de Pagamento e Grade Dinâmica de Itens (seleção de produto, quantidade com casas decimais, custo unitário e subtotal).
- **Tabela Histórica de Compras:** Número da Compra, Fornecedor, Data, Número da NF, Valor Total e Status (`PAGA`, `PENDENTE`, `CANCELADA`).
- **Visualizador de Nota:** Espelho detalhado da compra com lista de itens, subtotais e dados do fornecedor.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Transação ACID de Entrada e Recálculo de Custo Médio Ponderado (CMP)
```php
function registrar_compra(PDO $pdo, array $dadosCompra, array $itens): int {
    $pdo->beginTransaction();
    try {
        // 1. Grava cabeçalho da compra
        $stmt = $pdo->prepare("INSERT INTO compras (fornecedor_id, total, status, numero_nf, forma_pagamento, observacoes, data_compra) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $dadosCompra['fornecedor_id'],
            $dadosCompra['total'],
            $dadosCompra['status'] ?? 'PAGA',
            $dadosCompra['numero_nf'],
            $dadosCompra['forma_pagamento'],
            $dadosCompra['observacoes'] ?? ''
        ]);
        $compraId = (int)$pdo->lastInsertId();
        
        // 2. Itera itens para gravar na nota e recalcular CMP
        foreach ($itens as $item) {
            $prodId      = (int)$item['produto_id'];
            $qtdComprada = (float)$item['quantidade'];
            $custoNovo   = (float)$item['preco_unitario'];
            $subtotal    = $qtdComprada * $custoNovo;
            
            // Grava item da compra
            $stmt = $pdo->prepare("INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$compraId, $prodId, $qtdComprada, $custoNovo, $subtotal]);
            
            // Bloqueio pessimista para recálculo de CMP
            $stmt = $pdo->prepare("SELECT quantidade, preco_compra FROM produtos WHERE id = ? FOR UPDATE");
            $stmt->execute([$prodId]);
            $prodAtual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $qtdAtual   = (float)$prodAtual['quantidade'];
            $custoAtual = (float)$prodAtual['preco_compra'];
            
            // Fórmula do Custo Médio Ponderado (CMP)
            $qtdTotalNova = $qtdAtual + $qtdComprada;
            if ($qtdTotalNova > 0) {
                $novoCmp = (($qtdAtual * $custoAtual) + ($qtdComprada * $custoNovo)) / $qtdTotalNova;
            } else {
                $novoCmp = $custoNovo;
            }
            $novoCmp = round($novoCmp, 2);
            
            // Atualiza saldo e novo CMP do produto
            $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ?, preco_compra = ? WHERE id = ?");
            $stmt->execute([$qtdComprada, $novoCmp, $prodId]);
            
            // Registra movimentação no livro-razão
            $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'entrada_compra', ?, ?)");
            $stmt->execute([$prodId, $qtdComprada, "Entrada NF {$dadosCompra['numero_nf']} (Compra #$compraId)"]);
        }
        
        $pdo->commit();
        return $compraId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Acesso Restrito:** Apenas o Administrador pode visualizar valores de compra e lançar notas fiscais.
- **Transações ACID:** Garante que ou toda a nota de compra entra e recalcula os custos com sucesso, ou nenhum dado é gravado.
