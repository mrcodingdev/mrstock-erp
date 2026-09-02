# 🔄 Fluxos Operacionais e Ciclo de Vida do Sistema

Os diagramas abaixo ilustram a esteira completa de processamento de dados no MrStock ERP v2.1.0:

---

## 1. Ciclo de Vida Comercial Integrado (Entrada ➔ PDV ➔ BI)

```mermaid
sequenceDiagram
    autonumber
    actor Fornecedor
    actor Admin as Administrador
    actor Caixa as Operador de Caixa
    actor Cliente
    participant Compras as Módulo Compras
    participant DB as MariaDB (mrstock_db)
    participant PDV as Frente de Caixa
    participant Fiscal as Módulo Fiscal (NFC-e)
    participant BI as Analytics & DRE

    Fornecedor->>Admin: Entrega Mercadorias com NF
    Admin->>Compras: Registra Compra (Itens, Quantidades e Preço de Custo)
    Compras->>DB: Transação ACID: Atualiza Estoque + Recalcula CMP (Custo Médio)
    
    Cliente->>Caixa: Apresenta Produtos no Balcão
    Caixa->>PDV: Bipa Códigos de Barras (Leitor ou F2)
    PDV->>PDV: Valida Margem Negativa e Limite de Desconto
    Caixa->>PDV: Pressiona F4 (Pagamento em Dinheiro/Cartão/Pix)
    PDV->>DB: Lock Pessimista (SELECT ... FOR UPDATE) + Decrementa Estoque
    DB-->>PDV: Transação Commitada
    PDV->>Fiscal: Gera Cupom Térmico com QR Code SEFAZ e Tributos IBPT
    Fiscal-->>Cliente: Emissão de Cupom Fiscal
    
    Admin->>BI: Acessa Relatórios Gerenciais
    BI->>DB: Consolida Faturamento, CMV, Lucro Bruto e Curva ABC
```

---

## 2. Fluxo Transacional do Checkout no PDV (Concorrência & ACID)

```mermaid
flowchart TD
    Start(["Início da Venda"]) --> ReadItem["Operador Bipa / Seleciona Produto"]
    ReadItem --> CheckMem["Item inserido na grade do PDV (Memória JS)"]
    CheckMem --> ApplyDesc{"Aplicar Desconto?"}
    ApplyDesc -- Sim --> ValidaDesc["Valida Desconto <= Teto & Preço >= Custo (Trava)"]
    ApplyDesc -- Não --> FinishModal["Pressiona F4 (Modal de Finalização)"]
    ValidaDesc --> FinishModal
    FinishModal --> CalcTroco["Calcula Troco com Math.round(centesimal)"]
    CalcTroco --> SubmitPost["Submete POST para /vendas/pdv.php (com Token CSRF)"]
    
    SubmitPost --> BeginTx["Backend: $pdo->beginTransaction()"]
    BeginTx --> LockRow["SELECT quantidade, preco_compra FROM produtos WHERE id = ? FOR UPDATE"]
    LockRow --> CheckStock{"Saldo em Estoque Suficiente?"}
    
    CheckStock -- Não --> RollbackTx["$pdo->rollBack() & Retorna Erro de Ruptura"]
    CheckStock -- Sim --> InsertVenda["INSERT INTO vendas & vendas_itens"]
    InsertVenda --> DecStock["UPDATE produtos SET quantidade = quantidade - ?"]
    DecStock --> InsertLog["INSERT INTO logs & movimentacoes (saida_venda)"]
    InsertLog --> CommitTx["$pdo->commit()"]
    CommitTx --> RedirectCupom["Redireciona para /vendas/cupom.php?id=X"]
    RedirectCupom --> PlaySuccess["Sintetizador Web Audio API emite Beep de Sucesso"]
```
