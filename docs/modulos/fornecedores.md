# 🏭 Módulo: Gestão de Fornecedores & Contatos
**Arquivos Principais:** `fornecedores/index.php`, `fornecedores/functions.php`, `fornecedores/form.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
Gerencia a base de parceiros comerciais e fabricantes da Papelaria Real. Centraliza dados cadastrais, CNPJ, catálogo de produtos fornecidos, histórico de compras e canais diretos de comunicação, incorporando o botão circular verde oficial do WhatsApp para cotação ágil de mercadorias.

---

## 2. Interface & Componentes Visuais
- **Tabela de Fornecedores:** Razão Social / Nome Fantasia, CNPJ, Contato Responsável, Telefone formatado com **Botão de WhatsApp Circular** (`.btn-whatsapp` verde oficial de 22x22px), Cidade/UF e Ações.
- **Modal de Cadastro / Edição:** Formulário completo com CNPJ, Inscrição Estadual, E-mail, Telefone, WhatsApp, CEP e Endereço.
- **Histórico de Compras Vinculadas:** Aba rápida para consultar todas as ordens de compra efetuadas com aquele fornecedor.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 CRUD de Fornecedores com Sanitização
```php
function cadastrar_fornecedor(PDO $pdo, array $dados): int {
    $nome     = clean_input($dados['nome']);
    $cnpj     = clean_input($dados['cnpj']);
    $telefone = clean_input($dados['telefone']);
    $email    = clean_input($dados['email']);
    $contato  = clean_input($dados['contato'] ?? '');
    $endereco = clean_input($dados['endereco'] ?? '');
    $cidade   = clean_input($dados['cidade'] ?? 'Sorocaba');
    $estado   = clean_input($dados['estado'] ?? 'SP');
    $cep      = clean_input($dados['cep'] ?? '');
    
    $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj, telefone, email, contato, endereco, cidade, estado, cep, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo')");
    $stmt->execute([$nome, $cnpj, $telefone, $email, $contato, $endereco, $cidade, $estado, $cep]);
    return (int)$pdo->lastInsertId();
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Isolamento de Dados Estratégicos:** O Operador de Caixa não possui acesso aos dados de fornecedores ou valores negociados.
- **Proteção contra CSRF:** Formulários validados com token criptográfico em todas as ações.
