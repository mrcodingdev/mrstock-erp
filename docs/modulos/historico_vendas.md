# 📜 Módulo: Histórico de Vendas & Cancelamentos
**Arquivo Principal:** `vendas/historico.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
Centraliza o controle e a auditoria de todas as vendas realizadas na Papelaria Real. Permite filtrar vendas por período, cliente e forma de pagamento, emitir segunda via de cupons fiscais e realizar **estornos / cancelamentos de vendas**, com devolução automática de mercadorias ao estoque em transação ACID.

---

## 2. Interface & Componentes Visuais
- **Barra de Filtros Unificada:** Campos para Data Inicial, Data Final, Seleção de Cliente e Forma de Pagamento com botão "Filtrar" e "Limpar".
- **Tabela de Vendas com Paginação:** Colunas com ID da Venda, Data/Hora, Cliente, Quantidade de Itens, Valor Total em negrito (`.tabular-nums`), Forma de Pagamento e Ações.
- **Modais de Detalhes e Confirmação de Estorno:** Modal interativo para visualização de itens vendidos e modal de confirmação de cancelamento com aviso em vermelho.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Consulta Paginada e Filtrada
```php
$where = ["1=1"];
$params = [];

if (!empty($_GET['data_inicio'])) {
    $where[] = "DATE(v.data_venda) >= ?";
    $params[] = $_GET['data_inicio'];
}
if (!empty($_GET['data_fim'])) {
    $where[] = "DATE(v.data_venda) <= ?";
    $params[] = $_GET['data_fim'];
}
if (!empty($_GET['cliente_id'])) {
    $where[] = "v.cliente_id = ?";
    $params[] = (int)$_GET['cliente_id'];
}

$sql = "SELECT v.*, c.nome as cliente_nome, COUNT(vi.id) as total_itens 
        FROM vendas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        LEFT JOIN vendas_itens vi ON v.id = vi.venda_id 
        WHERE " . implode(" AND ", $where) . " 
        GROUP BY v.id 
        ORDER BY v.data_venda DESC 
        LIMIT ? OFFSET ?";
```

### 3.2 Rotina de Cancelamento / Estorno com Devolução de Estoque
```php
function estornar_venda(PDO $pdo, int $vendaId): void {
    $pdo->beginTransaction();
    try {
        // 1. Busca itens da venda para estorno
        $stmt = $pdo->prepare("SELECT produto_id, quantidade FROM vendas_itens WHERE venda_id = ?");
        $stmt->execute([$vendaId]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Devolve produtos ao estoque e registra movimentação
        foreach ($itens as $item) {
            $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
            $stmt->execute([$item['quantidade'], $item['produto_id']]);
            
            $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'devolucao_cliente', ?, ?)");
            $stmt->execute([$item['produto_id'], $item['quantidade'], "Estorno da Venda #$vendaId"]);
        }
        
        // 3. Atualiza cupom fiscal para CANCELADA
        $stmt = $pdo->prepare("UPDATE cupons_fiscais SET status = 'CANCELADA' WHERE venda_id = ?");
        $stmt->execute([$vendaId]);
        
        // 4. Grava Log de Auditoria
        log_sistema($pdo, $_SESSION['usuario_id'], "Estorno de Venda", "Venda #$vendaId cancelada com estorno de estoque.", "vendas");
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Restrição Estrita:** Acesso protegido por `require_admin()`; operadores de caixa são interceptados e redirecionados.
- **Proteção Transacional:** O estorno é 100% atômico; falhas em qualquer item revertem todo o estorno.
