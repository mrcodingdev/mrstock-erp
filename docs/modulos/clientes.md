# 👥 Módulo: Gestão de Clientes & Contatos
**Arquivos Principais:** `clientes/index.php`, `clientes/functions.php`, `clientes/form.php`  
**Escopo de Acesso:** Administrador e Operador de Caixa

---

## 1. Objetivo & Contexto de Negócio
Centraliza o cadastro de clientes da Papelaria Real, atendendo tanto consumidores finais (CPF) quanto clientes corporativos/escolas (CNPJ). Permite cadastro rápido no balcão do PDV, consulta automática de endereço via CEP (ViaCEP proxy local), histórico de compras realizadas e contato direto via WhatsApp.

---

## 2. Interface & Componentes Visuais
- **Tabela de Clientes:** Nome, CPF/CNPJ formatado, E-mail, Telefone com **Botão Circular Verde do WhatsApp** (`.btn-whatsapp`), Data de Cadastro e Ações.
- **Busca Rápida de CEP Integrada:** Campo de CEP com preenchimento automático de Logradouro, Bairro, Cidade e UF em tempo real via JavaScript.
- **Histórico de Compras do Cliente:** Modal que exibe todas as vendas associadas àquele cliente com valores e datas.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Cadastro de Cliente com Validação
```php
function cadastrar_cliente(PDO $pdo, array $dados): int {
    $nome     = clean_input($dados['nome']);
    $cpfCnpj  = clean_input($dados['cpf_cnpj'] ?? '');
    $email    = clean_input($dados['email'] ?? '');
    $telefone = clean_input($dados['telefone'] ?? '');
    $endereco = clean_input($dados['endereco'] ?? '');
    $numero   = clean_input($dados['numero'] ?? '');
    $bairro   = clean_input($dados['bairro'] ?? '');
    $cidade   = clean_input($dados['cidade'] ?? 'Sorocaba');
    $estado   = clean_input($dados['estado'] ?? 'SP');
    $cep      = clean_input($dados['cep'] ?? '');
    
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, cpf_cnpj, email, telefone, endereco, numero, bairro, cidade, estado, cep, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo')");
    $stmt->execute([$nome, $cpfCnpj, $email, $telefone, $endereco, $numero, $bairro, $cidade, $estado, $cep]);
    return (int)$pdo->lastInsertId();
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Operação de Balcão:** O Operador de Caixa possui permissão para cadastrar e consultar clientes para emissão de comprovantes no PDV.
- **Proxy ViaCEP Seguro:** As consultas de CEP são intermediadas por `inc/viacep.php` com validação de formato numérico de 8 dígitos, impedindo SSRF.
