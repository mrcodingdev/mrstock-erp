# 📦 Módulo: Catálogo de Produtos & Controle de Estoque
**Arquivos Principais:** `produtos/index.php`, `produtos/form.php`, `produtos/functions.php`  
**Escopo de Acesso:** Administrador (Edição/Custo) e Caixa (Consulta Rápida)

---

## 1. Objetivo & Contexto de Negócio
Gerencia todo o acervo de mercadorias da Papelaria Real. Organizado estritamente pelas **10 Famílias Funcionais de Produtos**, o módulo monitora o estoque mínimo de segurança, calcula o markup de venda sobre o Custo Médio Ponderado, rastreia validades de perecíveis (shelf-life) e fornece busca instantânea por código de barras ou nome.

---

## 2. Interface & Componentes Visuais
- **Painel de Chips de Filtro por Família:** Botões segmentados para filtragem rápida (`Cadernos & Blocos`, `Canetas & Marcadores`, `Lápis & Apontadores`, etc.).
- **Tabela com Badges Semânticos de Estoque:**
  - *Verde*: Saldo normal (`> estoque_minimo`).
  - *Amarelo*: Estoque de atenção (`<= estoque_minimo`).
  - *Vermelho*: Estoque zerado / ruptura.
  - *Alerta Laranja*: Vencimento em menos de 30 dias.
- **Modal de Cadastro e Edição:** Campos para Nome, Família, Código de Barras (com gerador automático), Fornecedor, Preço de Compra, Margem de Lucro (% Markup), Preço de Venda e Validade.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Cadastro com Cálculo Automático de Markup e Código de Barras
```php
function cadastrar_produto(PDO $pdo, array $dados): int {
    $nome           = clean_input($dados['nome']);
    $categoriaId    = (int)$dados['categoria_id'];
    $fornecedorId   = !empty($dados['fornecedor_id']) ? (int)$dados['fornecedor_id'] : null;
    $quantidade     = (int)$dados['quantidade'];
    $estoqueMinimo  = (int)($dados['estoque_minimo'] ?? 5);
    $precoCompra    = (float)$dados['preco_compra'];
    $precoVenda     = (float)$dados['preco_venda'];
    $validade       = !empty($dados['validade']) ? $dados['validade'] : null;
    $codigoBarra    = !empty($dados['codigo_de_barra']) ? clean_input($dados['codigo_de_barra']) : gerar_codigo_ean13_unico($pdo);
    
    $stmt = $pdo->prepare("INSERT INTO produtos 
        (nome, categoria_id, fornecedor_id, quantidade, estoque_minimo, preco_compra, preco_venda, validade, codigo_de_barra, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo')");
    $stmt->execute([$nome, $categoriaId, $fornecedorId, $quantidade, $estoqueMinimo, $precoCompra, $precoVenda, $validade, $codigoBarra]);
    
    $produtoId = (int)$pdo->lastInsertId();
    
    if ($quantidade > 0) {
        $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'entrada_compra', ?, 'Saldo Inicial de Cadastro')");
        $stmt->execute([$produtoId, $quantidade]);
    }
    
    return $produtoId;
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Proteção de Margens:** O perfil Caixa **NÃO visualiza as colunas de Preço de Compra, Fornecedor ou Markup** na tabela de produtos.
- **Ações Administrativas:** Modificar preço, cadastrar ou excluir produtos exige perfil `admin`.
- **Integridade Referencial:** A exclusão é impedida caso o produto possua registros vinculados em compras ou vendas.
