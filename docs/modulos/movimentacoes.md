# Módulo de Movimentações & Livro-Razão de Estoque

**Arquivos:** `produtos/movimentacoes.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Rastrear com rigor de auditoria todas as entradas, saídas, perdas e ajustes manuais de estoque ocorridos no sistema.

---

## 1. Tipos de Movimentação Homologados

1. `entrada_compra`: Gerado automaticamente no recebimento de pedidos do módulo de Compras.
2. `saida_venda`: Gerado automaticamente na baixa de vendas do PDV e Venda Rápida.
3. `devolucao_cliente`: Estorno de itens vendidos retornando ao saldo da loja.
4. `devolucao_fornecedor`: Envio de mercadorias com defeito de volta ao distribuidor.
5. `perda`: Registro de itens danificados, vencidos ou furtados no salão da loja.

---

## 2. Recursos da Interface SalesOps

- **Live Search:** Filtragem em tempo real por nome do produto ou motivo/observação.
- **Badges Semânticos:** Entradas destacadas em verde esmeralda (`+`) e saídas/perdas em vermelho (`-`).
- **Histórico Cronológico:** Ordenação decrescente por data/hora com indicação do usuário que executou a operação.
- **Paginação Verde:** Navegação otimizada para tabelas de grande volume.