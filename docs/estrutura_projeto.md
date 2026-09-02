# 📂 Estrutura de Diretórios e Arquivos — MrStock ERP v2.1.0

A raiz do projeto em `C:\xampp\htdocs\MrStock\` adota uma arquitetura modular coesa em PHP 8.2 nativo:

```
C:\xampp\htdocs\MrStock\
├── .htaccess                       # Hardening de produção Apache (bloqueio de .env, .sql, etc.)
├── .env.example                    # Modelo de variáveis de ambiente
├── config.php                      # Configurações globais, detecção de ambiente e headers HTTP
├── configuracoes.php               # Painel administrativo de parâmetros (7 abas) e Backup SQL
├── dashboard.php                   # Painel principal Bento Grid e Venda Rápida
├── ajuda.php                       # Central de Ajuda, Base de Conhecimento e Mesa de Atalhos
├── login.php                       # Tela de autenticação com segurança defensiva e CSRF
├── logout.php                      # Encerramento seguro de sessão e destruição de cookies
├── index.php                       # Roteador inicial (redireciona para login ou dashboard)
│
├── assets/                         # Identidade visual e mídias
│   └── img/                        # Logotipo oficial Papelaria Real, favicon e ícones SVG
│
├── categorias/                     # Módulo de Categorias / Famílias de Produtos
│   ├── index.php                   # Listagem com contagem de vínculos e busca
│   ├── form.php                    # Modal/Formulário de cadastro e edição
│   └── functions.php               # Operações CRUD com PDO e validações relacionais
│
├── clientes/                       # Módulo de Gestão de Clientes
│   ├── index.php                   # Tabela com busca, status e WhatsApp circular
│   ├── form.php                    # Formulário com CPF/CNPJ e busca CEP
│   └── functions.php               # CRUD de clientes e validação de documentos
│
├── compras/                        # Módulo de Compras & Entrada de Mercadorias
│   ├── index.php                   # Histórico de compras de fornecedores
│   ├── nova.php                    # Lançamento de NF com recálculo de CMP
│   ├── visualizar.php              # Detalhamento de itens da nota de compra
│   └── functions.php               # Regras transacionais ACID de entrada
│
├── fornecedores/                   # Módulo de Gestão de Fornecedores
│   ├── index.php                   # Catálogo de parceiros comerciais
│   ├── form.php                    # Formulário com CNPJ e contato ágil
│   └── functions.php               # CRUD de fornecedores
│
├── produtos/                       # Módulo de Estoque e Catálogo
│   ├── index.php                   # Catálogo com filtros por família e alertas
│   ├── form.php                    # Cadastro com markup e validade
│   ├── movimentacoes.php           # Livro-razão de entradas, saídas e perdas
│   ├── etiquetas.php               # Emissor de etiquetas de código de barras
│   └── functions.php               # Funções de saldo, estoque mínimo e preço
│
├── vendas/                         # Módulo de Frente de Caixa e Vendas
│   ├── pdv.php                     # Terminal de PDV com catálogo em memória JS
│   ├── cupom.php                   # Emissor térmico (80mm/58mm/A4) com QR Code
│   ├── nfce.php                    # Consulta de Danfe NFC-e Didática
│   ├── historico.php               # Histórico de vendas e cancelamento/estorno
│   └── functions.php               # Motor de checkout transacional e concorrência
│
├── relatorios/                     # Centro de Inteligência e Relatórios
│   ├── index.php                   # Painel DRE Gerencial e Inventário Geral
│   ├── analise.php                 # Curva ABC (80-15-5) e gráficos Chart.js
│   ├── pdf.php                     # Gerador de relatório executivo A4 em 9 colunas
│   └── excel.php                   # Exportador CSV/Excel formatado
│
├── inc/                            # Núcleo de Bibliotecas e Includes Globais
│   ├── auth.php                    # Controle de sessão, RBAC e CSRF
│   ├── database.php                # Conexão singleton PDO com tratamento de erros
│   ├── functions.php               # Formatadores de moeda, data, flash messages e configs
│   ├── header.php                  # Topbar institucional e Sidebar colapsável
│   ├── footer.php                  # Rodapé corporativo e scripts globais
│   ├── barcode_helper.php          # Algoritmos vetoriais SVG (Code 128B e QR Code)
│   └── viacep.php                  # Proxy seguro para consulta de CEP
│
├── css/                            # Folhas de Estilo Locais
│   ├── style.css                   # Design System MrStock (botões sólidos, animações)
│   ├── bootstrap.min.css           # Framework Bootstrap 5.3 local
│   ├── all.min.css                 # FontAwesome 6.x local
│   └── inter.css                   # Tipografia Inter auto-hospedada
│
├── js/                             # Scripts e Bibliotecas Client-side
│   ├── bootstrap.bundle.min.js     # Popper e componentes interativos
│   └── chart.min.js                # Biblioteca Chart.js para dashboards
│
├── webfonts/                       # Fontes WOFF2 / TTF locais (FontAwesome e Inter)
├── database/                       # Scripts de Banco de Dados
│   ├── mrstock_db.sql              # Dump completo local
│   └── mrstock_db_unaux_production.sql # Dump de produção sem CREATE DATABASE
└── docs/                           # Documentação Técnica e Acadêmica Oficial
```
