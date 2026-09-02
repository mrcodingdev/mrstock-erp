# 🗄️ Dicionário de Dados do Banco de Dados (`mrstock_db`)
**Engine:** MySQL / MariaDB (InnoDB)  
**Charset / Collation:** `utf8mb4` / `utf8mb4_general_ci`  
**Total de Tabelas:** 14  
**Integridade Referencial:** Chaves Estrangeiras com `ON DELETE CASCADE` / `RESTRICT` e Lock Pessimista (`FOR UPDATE`).

---

## 📋 Lista das 14 Tabelas Oficiais

| # | Tabela | Descrição Funcional | Chave Primária | Quantidade de Colunas |
| :---: | :--- | :--- | :---: | :---: |
| **01** | `categorias` | 10 Famílias Funcionais de Produtos da Papelaria Real | `id` | 3 |
| **02** | `clientes` | Cadastro de clientes, endereços e contato WhatsApp | `id` | 14 |
| **03** | `fornecedores` | Homologação de parceiros comerciais e dados de contato | `id` | 13 |
| **04** | `produtos` | Catálogo de produtos, saldo, preços, markup e shelf-life | `id` | 12 |
| **05** | `lotes` | Rastreabilidade de lotes, validades e entradas | `id` | 9 |
| **06** | `movimentacoes` | Livro-razão de movimentações de estoque e perdas | `id` | 6 |
| **07** | `compras` | Registro de notas de compra e faturamento | `id` | 8 |
| **08** | `itens_compra` | Itens vinculados a cada ordem de compra de fornecedor | `id` | 6 |
| **09** | `vendas` | Cabeçalho das vendas finalizadas no PDV | `id` | 5 |
| **10** | `vendas_itens` | Detalhamento dos itens comercializados por venda | `id` | 5 |
| **11** | `cupons_fiscais` | Registro fiscal com chave de 44 dígitos e protocolo | `id` | 8 |
| **12** | `usuarios` | Contas de acesso e níveis de privilégio (Admin/Caixa) | `id` | 4 |
| **13** | `configuracoes` | Repositório dinâmico de parâmetros operacionais da loja | `chave` | 3 |
| **14** | `logs` | Trilha de auditoria forense de ações operacionais | `id` | 7 |

---

## 🔍 Detalhamento dos Esquemas de Tabelas

### 1. Tabela `produtos`
Armazena os itens comercializados, controlando preços de custo, markup, estoque mínimo e validade.
```sql
CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 0,
  `estoque_minimo` int(11) DEFAULT 5,
  `validade` date DEFAULT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `preco_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fornecedor_id` int(11) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `codigo_de_barra` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_produtos_categoria` (`categoria_id`),
  KEY `fk_produtos_fornecedor` (`fornecedor_id`),
  CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_produtos_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Tabela `configuracoes`
Tabela chave-valor para parâmetros do sistema gerenciados no painel de configurações.
```sql
CREATE TABLE `configuracoes` (
  `chave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Tabela `compras` e `itens_compra`
```sql
CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fornecedor_id` int(11) NOT NULL,
  `data_compra` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `status` enum('PAGA','PENDENTE','CANCELADA') DEFAULT 'PAGA',
  `numero_nf` varchar(50) DEFAULT NULL,
  `forma_pagamento` varchar(50) DEFAULT 'Boleto',
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_compras_fornecedor` (`fornecedor_id`),
  CONSTRAINT `fk_compras_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `itens_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compra_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_itens_compra_compra` (`compra_id`),
  KEY `fk_itens_compra_produto` (`produto_id`),
  CONSTRAINT `fk_itens_compra_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_compra_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. Tabela `vendas` e `vendas_itens`
```sql
CREATE TABLE `vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) DEFAULT NULL,
  `data_venda` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `forma_pagamento` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vendas_data` (`data_venda`),
  KEY `idx_vendas_cliente` (`cliente_id`),
  CONSTRAINT `fk_vendas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `vendas_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vendas_itens_venda` (`venda_id`),
  KEY `idx_vendas_itens_produto` (`produto_id`),
  CONSTRAINT `fk_vendas_itens_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vendas_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5. Tabela `movimentacoes`
Rastreabilidade forense de todas as alterações de saldo.
```sql
CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada_compra','saida_venda','devolucao_cliente','devolucao_fornecedor','perda') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_movimento` datetime DEFAULT current_timestamp(),
  `observacao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_movimentacoes_produto` (`produto_id`),
  KEY `idx_movimentacoes_data` (`data_movimento`),
  CONSTRAINT `fk_movimentacoes_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 6. Tabela `cupons_fiscais`
```sql
CREATE TABLE `cupons_fiscais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `chave_acesso` varchar(44) NOT NULL,
  `protocolo_autorizacao` varchar(20) DEFAULT NULL,
  `data_emissao` datetime DEFAULT current_timestamp(),
  `xml_simulado` longtext DEFAULT NULL,
  `qr_code_payload` text DEFAULT NULL,
  `status` enum('AUTORIZADA','CANCELADA','REJEITADA') DEFAULT 'AUTORIZADA',
  PRIMARY KEY (`id`),
  KEY `idx_cupons_venda` (`venda_id`),
  CONSTRAINT `fk_cupons_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
