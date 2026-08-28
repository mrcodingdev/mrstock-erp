# Módulo de Produtos & Controle de Estoque

**Arquivos:** `produtos/index.php`, `produtos/functions.php`, `produtos/etiquetas.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Centralizar o cadastro, categorização, precificação (custo, markup e venda), controle de estoque mínimo e emissão de etiquetas de código de barras da Papelaria Real.

---

## 1. Visão Geral da Interface SalesOps

A listagem de produtos (`produtos/index.php`) conta com as inovações da **Versão 2.0**:
- **Live Search Instantâneo:** Filtragem reativa por nome, código EAN-13 ou categoria em tempo real via JavaScript.
- **Menu de Ações Flutuante (3 Pontos):** Ações agrupadas em `.so-actions-btn` (*Editar*, *Visualizar*, *Imprimir Etiqueta*, *Excluir*).
- **Badges Semânticos de Estoque:**
  - 🟢 **Normal:** Saldo superior ao estoque mínimo.
  - 🟡 **Estoque Baixo:** Saldo igual ou inferior ao estoque mínimo.
  - 🔴 **Sem Estoque:** Saldo zerado (desabilitado para venda no PDV).
- **Paginação Institucional Verde:** Navegação ágil com 10 ou 20 itens por página.

---

## 2. Estrutura de Precificação e Markup

O cadastro de produtos realiza o cálculo e exibição de margem comercial:

$$\text{Margem Bruta (\%)} = \left( \frac{\text{Preço de Venda} - \text{Preço de Custo}}{\text{Preço de Custo}} \right) \times 100$$

### 📦 Campos do Cadastro de Produto:
1. **Nome do Produto:** Descrição comercial completa (ex: *Caderno Universitário 10 Matérias Spiral*).
2. **Código de Barras (EAN-13 / Code-128):** Código numérico de 13 dígitos para bipe no leitor ótico.
3. **Categoria:** Vínculo com a tabela `categorias` (`ON DELETE SET NULL`).
4. **Fornecedor Principal:** Vínculo com a tabela `fornecedores` (`ON DELETE SET NULL`).
5. **Preço de Custo (Compra):** Valor pago ao distribuidor em R$.
6. **Preço de Venda:** Valor final cobrado do consumidor no balcão.
7. **Estoque Atual:** Saldo físico disponível na loja.
8. **Estoque Mínimo:** Ponto de pedido para alertas automáticos no Dashboard.
9. **Data de Validade:** Opcional (utilizado para tintas, colas e itens químicos).

---

## 3. Ações Disponíveis

- **Novo Produto:** Modal com validação de campos obrigatórios e cálculo de margem em tempo real.
- **Edição Rápida:** Ajuste de preços, saldos e códigos com validação CSRF.
- **Impressão de Etiquetas:** Atalho direto para gerar o código de barras SVG no formato térmico ou folha A4.
- **Exclusão Segura:** Verificação de integridade referencial antes da remoção.