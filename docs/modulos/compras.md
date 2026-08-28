# Módulo de Gestão de Compras & Abastecimento

**Arquivos:** `compras/index.php`, `compras/nova.php`, `compras/visualizar.php`, `compras/functions.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Gerenciar pedidos de compras junto a fornecedores e distribuidores (Tilibra, Bic, Acrilex), dar entrada automatizada de mercadorias no estoque e controlar contas a pagar.

---

## 1. Visão Geral do Fluxo de Compras

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Administrador
    participant View as compras/nova.php
    participant Controller as compras/functions.php
    participant DB as MySQL (compras, itens_compra, movimentacoes)

    Admin->>View: Seleciona Fornecedor, Nota Fiscal e Itens
    View->>Controller: Envia POST com dados da compra e CSRF
    Controller->>DB: beginTransaction()
    Controller->>DB: INSERT INTO compras (fornecedor_id, valor_total, status)
    Controller->>DB: INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario)
    Controller->>DB: UPDATE produtos SET quantidade = quantidade + ? (Incrementa Estoque)
    Controller->>DB: INSERT INTO movimentacoes (tipo = 'entrada_compra')
    Controller->>DB: commit()
    Controller->>View: Redireciona para compras/index.php com sucesso
```

---

## 2. Telas do Módulo

### 📋 2.1 Listagem de Compras (`compras/index.php`)
- Tabela com Live Search, data da compra, fornecedor, número de nota, valor total e status (`PENDENTE` / `PAGA`).
- Menu de ações com atalho para **Visualizar Pedido** e **Alternar Status de Pagamento**.

### ➕ 2.2 Nova Compra (`compras/nova.php`)
- Seleção dinâmica de fornecedor com preenchimento automático.
- Adição dinâmica de múltiplos produtos no carrinho de compras.
- Cálculo automático de subtotais e valor global da ordem de compra.

### 👁️ 2.3 Visualização de Pedido Master-Detail (`compras/visualizar.php`)
- Espelho formal da ordem de compra com dados completos do distribuidor.
- Listagem dos itens comprados, quantidades e custos unitários.
- Botão de impressão formatado para auditoria física no recebimento de mercadorias no depósito.