# 🗂️ Módulo: Categorias & Famílias Funcionais de Produtos
**Arquivos Principais:** `categorias/index.php`, `categorias/functions.php`, `categorias/form.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
Estrutura o acervo da Papelaria Real nas **10 Famílias Funcionais de Produtos** padronizadas no banco de dados `mrstock_db`. Esse agrupamento setorial elimina categorizações genéricas ("Escolar/Escritório") e viabiliza a organização física de prateleiras e gôndolas:
1. `Cadernos & Blocos`
2. `Canetas & Marcadores`
3. `Lápis & Apontadores`
4. `Borrachas & Correção`
5. `Colas & Fitas Adesivas`
6. `Papéis & Folhas`
7. `Pastas & Organização`
8. `Corte & Medição`
9. `Tintas & Pintura`
10. `Grampeadores & Fixação`

---

## 2. Interface & Componentes Visuais
- **Tabela de Categorias com Contagem Dinâmica:** Exibe o Nome da Família, Descrição Funcional, Total de SKUs Vinculados (em badge tabular) e Ações (Editar e Excluir).
- **Modal de Cadastro / Edição:** Campos para Nome da Família e Descrição de Produtos Abrangidos.
- **Validação de Exclusão:** Alerta impeditivo caso existam produtos vinculados à categoria.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Consulta de Categorias com Contagem Relacional
```php
function get_todas_categorias(PDO $pdo): array {
    $sql = "SELECT c.*, COUNT(p.id) as total_produtos 
            FROM categorias c 
            LEFT JOIN produtos p ON c.id = p.categoria_id AND p.status = 'ativo' 
            GROUP BY c.id 
            ORDER BY c.nome ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
```

### 3.2 Exclusão Segura com Verificação de Vínculos
```php
function excluir_categoria(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Não é possível excluir: existem produtos vinculados a esta família!");
    }
    
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    return $stmt->execute([$id]);
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Acesso Restrito:** Apenas administradores podem criar, editar ou excluir categorias.
- **Integridade Referencial:** Chave estrangeira `fk_produtos_categoria` protege os produtos contra desvinculação acidental (`ON DELETE SET NULL` ou bloqueio).
