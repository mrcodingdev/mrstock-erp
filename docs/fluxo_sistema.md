# Fluxos de Processo & Arquitetura do Sistema — MrStock ERP v2.0

Este documento apresenta os fluxogramas e diagramas de sequência dos processos de negócio fundamentais da Papelaria Real no **MrStock ERP**.

---

## 1. Macrofluxo Integrado do ERP

```mermaid
flowchart TD
    subgraph Suprimentos [1. Módulo de Compras]
        A1[Identificação de Estoque Crítico no Dashboard] --> A2[Emissão de Pedido de Compra]
        A2 --> A3[Recebimento Físico e Conferência de Nota]
        A3 --> A4[Lançamento da Compra no Sistema]
        A4 --> A5[Incremento Automático de Estoque e Registro no Razão]
    end

    subgraph Operacao [2. Módulo de Produtos & Etiquetas]
        A5 --> B1[Geração de Etiquetas Térmicas SVG]
        B1 --> B2[Fixação de Códigos de Barras nas Gôndolas e Itens]
    end

    subgraph Vendas [3. Módulo PDV & Frente de Caixa]
        B2 --> C1[Leitura Ótica no PDV com Bipe 880Hz]
        C1 --> C2[Aplicação de Desconto F9 e Fechamento F8]
        C2 --> C3[Cálculo de Troco Dinâmico e Seleção de Cédula]
        C3 --> C4[Commit Transacional com Lock Pessimista]
        C4 --> C5[Baixa Automática de Estoque e Emissão de Cupom]
    end

    subgraph Inteligencia [4. Módulo de Inteligência & Relatórios]
        C4 --> D1[Atualização em Tempo Real do Dashboard]
        D1 --> D2[Análise de Curva ABC e Margem Bruta]
        D2 --> D3[Exportação de Inventário em Excel e PDF]
        D3 --> A1
    end
```

---

## 2. Fluxo Detalhado do PDV com Bloqueio Pessimista

```mermaid
flowchart TD
    Start([Início da Venda]) --> Input[Operador Bipe Código de Barras F2]
    Input --> Beep[Web Audio API sintetiza Bip 880Hz]
    Beep --> Cart[Item adicionado ao carrinho local]
    Cart --> Checkout{Pressionou F8?}
    Checkout -- Não --> Input
    Checkout -- Sim --> Modal[Abre Modal de Troco Dinâmico]
    Modal --> Cash[Operador clica na Cédula ou digita Valor Pago]
    Cash --> Calc[Calcula Troco em Tempo Real]
    Calc --> Submit[Dispara POST para vendas/functions.php]
    Submit --> TransBegin[PDO: beginTransaction]
    TransBegin --> Lock[SELECT ... FOR UPDATE no Produto]
    Lock --> CheckStock{Saldo >= Solicitado?}
    CheckStock -- Não --> Rollback[PDO: rollBack]
    Rollback --> ErrorPage[Retorna erro de estoque insuficiente ao PDV]
    CheckStock -- Sim --> InsertSale[INSERT INTO vendas e vendas_itens]
    InsertSale --> UpdateStock[UPDATE produtos SET quantidade = quantidade - ?]
    UpdateStock --> InsertMov[INSERT INTO movimentacoes tipo saida_venda]
    InsertMov --> Commit[PDO: commit]
    Commit --> PrintCupom[Redireciona para emissão de Cupom Térmico]
    PrintCupom --> End([Fim da Venda])
```