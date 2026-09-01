<div align="center">
  <img src="assets/img/logo.png" alt="MrStock ERP Logo" width="220"/>
  
  # MrStock ERP v2.0
  
  <p align="center">
    <strong>Sistema Integrado de Gestão Empresarial, Controle de Estoque e Frente de Caixa (PDV)</strong>
    <br />
    <em>Desenvolvido com foco em alta performance, robustez transacional e aderência ao comércio varejista.</em>
  </p>

  <p align="center">
    <a href="#-visão-geral--estudo-de-caso"><img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+"/></a>
    <a href="#-visão-geral--estudo-de-caso"><img src="https://img.shields.io/badge/MySQL-8.0%20%2F%20MariaDB-005C84?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8.0"/></a>
    <a href="#-visão-geral--estudo-de-caso"><img src="https://img.shields.io/badge/Bootstrap-5.3-563D7C?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap 5.3"/></a>
    <a href="#-segurança--integridade"><img src="https://img.shields.io/badge/Transações-ACID%20(PDO)-10b981?style=flat-square&logo=speedtest&logoColor=white" alt="ACID"/></a>
    <a href="#-controle-de-acesso-rbac"><img src="https://img.shields.io/badge/Segurança-RBAC%20%26%20Anti--CSRF-284936?style=flat-square&logo=auth0&logoColor=white" alt="RBAC & CSRF"/></a>
    <a href="LICENSE"><img src="https://img.shields.io/badge/Licença-MIT-blue?style=flat-square" alt="Licença MIT"/></a>
    <a href="#-status-do-projeto"><img src="https://img.shields.io/badge/Status-Produção%20%2F%20Homologado-success?style=flat-square" alt="Status"/></a>
  </p>

  <p align="center">
    <a href="#-funcionalidades-principais">Funcionalidades</a> •
    <a href="#-arquitetura--engenharia">Arquitetura</a> •
    <a href="#-módulos-do-sistema">Módulos</a> •
    <a href="#-controle-de-acesso-rbac">Perfis (RBAC)</a> •
    <a href="#-instalação-e-execução">Instalação</a> •
    <a href="#-equipe-e-créditos">Equipe</a>
  </p>
</div>

---

## 📌 Visão Geral & Estudo de Caso

O **MrStock ERP** é uma plataforma de gestão empresarial desenvolvida especificamente para resolver os desafios operacionais do varejo físico de pequeno e médio porte, tomando como caso de estudo a operação real da **Papelaria Real**.

O sistema unifica a **Frente de Caixa (PDV)** de alta velocidade com o **Controle de Estoque Multinível**, **Gestão de Compras (Master-Detail)**, **Simulação Fiscal de NFC-e com QR Code** e **Relatórios Financeiros Estratégicos (Curva ABC e DRE Gerencial)**.

### 🎯 Principais Dores Solucionadas:
* **Fila no Caixa:** Checkout otimizado com busca instantânea por código de barras, atalhos de teclado (`F2`, `F4`, `F8`, `ESC`) e cálculo dinâmico de troco.
* **Ruptura de Estoque:** Monitoramento de estoque mínimo e classificação por **Famílias Funcionais de Produtos** da papelaria.
* **Perdas por Validade:** Alertas automáticos no dashboard para itens com vencimento em até 30 dias.
* **Segurança Operacional:** Blindagem financeira que impede que operadores de caixa visualizem custos de compra ou margens de lucro.

---

## 🏗️ Arquitetura & Engenharia

O sistema foi arquitetado em **PHP 8.2 Nativo** utilizando o padrão **Data Mapper / Active Gateway com PDO**, garantindo tempo de resposta sub-milissegundo sem sobrecarga de frameworks pesados no ambiente local de contingência.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              MRSTOCK ERP v2.0                               │
├───────────────────────────────┬─────────────────────────────────────────────┤
│      Camada de Apresentação   │ HTML5 Semântico • Bootstrap 5 • Custom CSS  │
│      (UI / UX Design System)  │ Tipografia Tabular (tnum) • Ícones FA6      │
├───────────────────────────────┼─────────────────────────────────────────────┤
│      Camada de Controle       │ Sessões Seguras • RBAC (Admin / Caixa)      │
│      & Segurança              │ Proteção Anti-CSRF • Sanitização XSS        │
├───────────────────────────────┼─────────────────────────────────────────────┤
│      Camada de Negócio        │ Transações ACID (PDO::beginTransaction)     │
│      (Core Engine)            │ Regras de Desconto • Baixa de Estoque       │
├───────────────────────────────┼─────────────────────────────────────────────┤
│      Persistência & Dados     │ MySQL 8.0 / MariaDB (InnoDB Engine)         │
│      (mrstock_db)             │ Chaves Estrangeiras • Índices Otimizados    │
└───────────────────────────────┴─────────────────────────────────────────────┘
```

### 🛠️ Stack Tecnológica:
* **Backend:** PHP 8.2+ (Tipagem estrita, Coalescência nula, PDO Prepared Statements).
* **Banco de Dados:** MySQL 8.0 / MariaDB (Engine InnoDB, Integridade Referencial `ON DELETE RESTRICT/CASCADE`).
* **Frontend:** Bootstrap 5.3 (Cores sólidas, sem dependência de temas externos), CSS3 Moderno, JavaScript Vanilla.
* **Visualização de Dados:** Chart.js 4 (Gráficos de Vendas, Receita e Curva ABC).
* **Tipografia:** Inter & Roboto Mono com suporte a `font-variant-numeric: tabular-nums`.

---

## 🚀 Módulos do Sistema

### 🛒 1. Frente de Caixa & Ponto de Venda (PDV)
* **Bipagem Contínua:** Leitura ágil de código de barras ou SKU com feedback sonoro via Web Audio API.
* **Atalhos Operacionais:** Suporte total a navegação por teclado (`F2` Buscar Produto, `F4` Aplicar Desconto, `F8` Finalizar Venda, `ESC` Cancelar).
* **Simulação de NFC-e:** Geração de Chave de Acesso de 44 dígitos, Protocolo de Autorização SEFAZ e **QR Code de Consulta Acadêmica**.
* **Impressão Térmica:** Formatação de cupom fiscal para impressoras térmicas padrão 80mm e 58mm.
* **Múltiplas Formas de Pagamento:** Dinheiro (com cálculo dinâmico de cédulas e troco), PIX, Cartão de Débito e Cartão de Crédito.

### 📦 2. Gestão de Estoque & Catálogo
* **Classificação por Famílias:** Micro-categorias funcionais (*Cadernos & Blocos, Canetas & Marcadores, Lápis & Apontadores, Borrachas & Correção, Colas & Fitas, Papéis, Pastas, Corte & Medição, Tintas, Grampeadores*).
* **Barra de Filtros Unificada:** Pesquisa dinâmica por texto, categoria, fornecedor e status de disponibilidade em 1 linha limpa.
* **Gerador de Etiquetas:** Geração de etiquetas de código de barras prontas para impressão e colagem nos produtos.
* **Auditoria de Validade:** Indicadores visuais de produtos vencidos ou próximos do vencimento (< 30 dias).

### 📑 3. Gestão de Compras & Abastecimento (Master-Detail)
* **Lançamento de Entradas:** Formulário mestre-detalhe para registro de notas e abastecimento dinâmico de múltiplos itens.
* **Atualização Automática:** Recalculo automático do preço de custo, preço de venda e incremento de saldo no estoque em transação atômica.

### 📊 4. Inteligência Financeira & Relatórios
* **Curva ABC de Produtos:** Classificação dos produtos em Classes A (80% do faturamento), B (15%) e C (5%) para otimização de compras.
* **DRE Gerencial Simplificado:** Demonstração do Resultado do Exercício com Receita Bruta, Custos das Mercadorias Vendidas (CMV) e Lucro Operacional Real.
* **Histórico de Vendas:** Rastreamento completo de pedidos, emissão de segunda via de cupom e cancelamento auditado com estorno automático de estoque.

### 👥 5. Cadastros Comerciais & CRM
* **Clientes & Fornecedores:** Formulários com validação e máscaras automáticas de CPF, CNPJ, CEP e Telefone.
* **Contato Rápido WhatsApp:** Integração direta com WhatsApp Web via botão circular padronizado.

---

## 🔐 Controle de Acesso (RBAC)

O MrStock ERP implementa uma política rigorosa de **Role-Based Access Control** para segregação de funções no ambiente comercial:

| Módulo / Funcionalidade | Administrador | Operador de Caixa |
| :--- | :---: | :---: |
| **Frente de Caixa (PDV)** | ✅ Acesso Total | ✅ Acesso Total |
| **Histórico de Vendas (Consulta)** | ✅ Visualiza Tudo | ✅ Apenas Vendas Próprias |
| **Preço de Custo / Compra** | ✅ Visível | ❌ Oculto (Sigilo Comercial) |
| **Margem de Lucro / DRE** | ✅ Visível | ❌ Oculto |
| **Entrada de Compras / Fornecedores**| ✅ Acesso Total | ❌ Acesso Bloqueado |
| **Exclusão de Registros / Cancelamentos**| ✅ Permitido | ❌ Exige Admin |
| **Configurações & Backup do Banco** | ✅ Acesso Total | ❌ Acesso Bloqueado |

---

## 🛡️ Segurança & Integridade de Dados

* **Transações ACID:** Todas as baixas e estornos de estoque em vendas e compras utilizam `PDO::beginTransaction()`, `PDO::commit()` e `PDO::rollBack()`, prevenindo inconsistências em caso de falha de conexão.
* **Proteção contra SQL Injection:** 100% das consultas utilizam Prepared Statements com bind explícito de tipos de dados.
* **Proteção Anti-CSRF:** Tokens criptográficos exclusivos por sessão injetados em todos os formulários POST via `csrf_input()` e validados via `csrf_verify()`.
* **Sanitização contra XSS:** Todas as saídas de dados no HTML passam por `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
* **Criptografia de Senhas:** Armazenamento seguro de senhas com algoritmo nativo `password_hash()` (Bcrypt).

---

## ⚙️ Instalação e Execução

### Pré-requisitos
* Servidor Web (Apache / Nginx) — Recomendado: **XAMPP** no Windows ou LAMP no Linux.
* **PHP >= 8.2** com as extensões ativas: `pdo_mysql`, `mbstring`, `openssl`, `gd`.
* **MySQL >= 8.0** ou **MariaDB >= 10.4**.

### Passo a Passo

1. **Clonar o Repositório:**
   ```bash
   cd C:/xampp/htdocs/
   git clone https://github.com/mrcodingdev/mrstock-erp.git MrStock
   ```

2. **Importar o Banco de Dados:**
   * Abra o gerenciador de banco (ex: `phpMyAdmin` em `http://localhost/phpmyadmin/`).
   * Crie uma base de dados chamada `mrstock_db`.
   * Importe o arquivo: [`database/mrstock_db.sql`](database/mrstock_db.sql).

3. **Verificar Conexão:**
   * O arquivo de conexão [`inc/database.php`](inc/database.php) já vem configurado para o padrão local (`root` sem senha na porta `3306`).
   * Caso necessário, ajuste os parâmetros no arquivo [`config.php`](config.php).

4. **Acessar o Sistema:**
   * Abra o navegador e acesse: `http://localhost/MrStock/`

### 🔑 Credenciais de Demonstração

| Perfil | Usuário | Senha | Nível de Permissão |
| :--- | :---: | :---: | :--- |
| **Administrador** | `admin` | `admin` | Acesso completo a todos os módulos e relatórios |
| **Operador de Caixa** | `caixa` | `caixa` | Acesso restrito ao PDV e consultas operacionais |

---

## 📂 Estrutura de Diretórios

```
MrStock/
├── assets/          # Logotipos, ícones e fontes locais
├── categorias/      # Gestão de famílias e categorias de produtos
├── clientes/        # Cadastro e histórico de clientes
├── compras/         # Módulo de entrada de notas e abastecimento
├── css/             # Folhas de estilo (style.css com Design System oficial)
├── database/        # Scripts SQL de schema e dados iniciais (mrstock_db.sql)
├── docs/            # Documentação técnica, DER, casos de uso e guias
├── fornecedores/    # Gestão de fornecedores e marcas parceiras
├── inc/             # Núcleo do sistema (database.php, auth.php, header.php, functions.php)
├── js/              # Scripts de máscaras, atalhos de PDV e validações
├── produtos/        # Catálogo, controle de estoque e etiquetas
├── relatorios/      # Curva ABC, DRE, Vendas por período e métricas
├── vendas/          # Frente de Caixa (pdv.php), NFC-e e Histórico de Vendas
├── config.php       # Constantes globais e URLs base
├── dashboard.php    # Painel principal com KPIs executivos
├── index.php        # Ponto de entrada e redirecionamento de rotas
└── README.md        # Apresentação executiva do repositório
```

---

## 👥 Equipe do Projeto & Créditos

Projeto desenvolvido como **Trabalho de Conclusão de Curso (TCC)** no curso Técnico em Desenvolvimento de Sistemas na **ETEC**:

* **Douglas** — *Direção Técnica, Arquitetura de Software e Desenvolvimento Full-Stack*
* **Nikolas** — *Modelagem de Dados, Engenharia de Banco de Dados (DER) e Otimização SQL*
* **Cesar** — *Levantamento de Requisitos, Regras de Negócio e Relações Comerciais*
* **Enzo** — *Engenharia de Documentação, Diagramas Técnicos e Roteiros de QA*
* **Sugahara** — *Navegação de Sistema, Validação Funcional e Apresentação Executiva*
* **Prof. Vinicius** — *Orientação Técnica e Acadêmica*

---

## 📄 Licença

Este projeto é distribuído sob a licença **MIT**. Consulte o arquivo [`LICENSE`](LICENSE) para mais detalhes.

<div align="center">
  <sub>MrStock ERP v2.0 • Papelaria Real • 2026</sub>
</div>

