# Estrutura Física do Projeto — MrStock ERP v2.0

Este documento apresenta a árvore de diretórios, a finalidade de cada arquivo e o mapa modular da arquitetura do **MrStock ERP**.

---

## 1. Árvore de Diretórios Completa

```
MrStock/
├── .htaccess                       # Regras de segurança, bloqueio de diretórios e cabeçalhos HTTP
├── .gitignore                      # Exclusões de arquivos temporários e logs do Git
├── config.php                      # Configurações globais, detecção híbrida de URL e session hardening
├── index.php                       # Roteador principal (redireciona para login ou dashboard)
├── login.php                       # Tela de autenticação com Bcrypt e proteção contra brute force
├── logout.php                      # Encerramento seguro de sessão e destruição de cookies
├── dashboard.php                   # Painel principal com KPIs, alertas e venda rápida
│
├── inc/                            # Middlewares, helpers e componentes compartilhados
│   ├── auth.php                    # Middleware de autenticação e proteção RBAC de rotas
│   ├── barcode_helper.php          # [NOVO v2.0] Gerador vetorial autônomo de código de barras SVG (Code-128B/EAN-13)
│   ├── database.php                # Singleton de conexão PDO com MySQL (utf8mb4 e ATTR_EMULATE_PREPARES => false)
│   ├── functions.php               # Utilitários de segurança (sanitize, csrf_token, csrf_verify, redirect_to)
│   ├── header.php                  # Cabeçalho HTML5, CSS SalesOps, script síncrono Anti-FOUC e Topbar
│   └── footer.php                  # Fechamento de layout, modais globais e scripts JavaScript
│
├── produtos/                       # Módulo de Gestão de Produtos e Estoque
│   ├── index.php                   # Listagem com Live Search, filtros, paginação verde e menu 3 pontos (.so-actions-btn)
│   ├── functions.php               # CRUD de produtos, upload de imagem, cálculo de margem e validações
│   ├── etiquetas.php               # [NOVO v2.0] Gerador e impressor de folhas de etiquetas térmicas e A4 em SVG
│   └── movimentacoes.php           # Livro-razão e auditoria de movimentações de estoque (entradas/saídas/perdas)
│
├── categorias/                     # Módulo de Classificação Mercadológica
│   ├── index.php                   # Listagem com Live Search e menu de ações
│   └── functions.php               # CRUD de categorias de produtos
│
├── clientes/                       # Módulo de Cadastro de Clientes
│   ├── index.php                   # Listagem com CPF/CNPJ, link para WhatsApp API e menu de ações
│   └── functions.php               # CRUD de clientes e validação de documentos
│
├── fornecedores/                   # Módulo de Gestão de Fornecedores
│   ├── index.php                   # Listagem com CNPJ, contatos e link direto para WhatsApp
│   └── functions.php               # CRUD de fornecedores
│
├── compras/                        # Módulo de Gestão de Compras e Abastecimento
│   ├── index.php                   # Listagem de pedidos de compra com status de pagamento
│   ├── nova.php                    # Formulário de entrada de mercadorias com busca dinâmica de itens
│   ├── visualizar.php              # Visualização detalhada (Master-Detail) com suporte a impressão
│   └── functions.php               # Controlador transacional de compras com atualização de estoque
│
├── vendas/                         # Módulo de Frente de Caixa (PDV) e Histórico Comercial
│   ├── pdv.php                     # Frente de caixa com atalhos F2-F9, Web Audio API 880Hz e troco dinâmico
│   ├── functions.php               # Controlador do PDV com bloqueio pessimista (SELECT ... FOR UPDATE) e transação ACID
│   ├── historico.php               # Histórico de vendas com filtros avançados, KPIs e ações de cancelamento
│   ├── cupom.php                   # Visualizador de cupom não-fiscal formatado para impressora térmica (80mm/58mm)
│   └── nfce.php                    # Layout demonstrativo de emissão de NFC-e com QR Code mock
│
├── relatorios/                     # Módulo de Inteligência Comercial e Exportação de Dados
│   ├── index.php                   # Central de emissão de relatórios
│   ├── analise.php                 # Centro de inteligência com gráficos Chart.js e seletor de períodos
│   ├── pdf.php                     # Renderização de relatórios em formato para impressão
│   └── excel.php                   # Exportação de inventário em planilha formatada (9 colunas estritas)
│
├── css/                            # Folhas de Estilo e Assets de Design System
│   ├── style.css                   # Folha de estilo mestra do Design System SalesOps (Sidebar, tabelas, modais, tema verde)
│   ├── bootstrap.min.css           # Framework CSS Bootstrap 5.3 (cópia local offline)
│   ├── all.min.css                 # Ícones FontAwesome 6 (cópia local offline)
│   └── inter.css                   # Tipografia Inter otimizada para telas corporativas
│
├── js/                             # Scripts e Bibliotecas Frontend
│   ├── bootstrap.bundle.min.js     # Componentes interativos do Bootstrap (Modais, tooltips, dropdowns)
│   └── chart.min.js                # Biblioteca de gráficos estatísticos Chart.js (cópia local offline)
│
├── webfonts/                       # Fontes locais para funcionamento 100% offline
│   ├── fa-solid-900.*              # FontAwesome Solid
│   ├── fa-regular-400.*            # FontAwesome Regular
│   ├── fa-brands-400.*             # FontAwesome Brands
│   └── inter_font_*.*              # Arquivos de fonte WOFF2 da família Inter
│
├── database/                       # Scripts e Dumps do Banco de Dados
│   └── mrstock_db.sql              # Dump DDL e DML oficial do banco de dados (12 tabelas InnoDB + seed Papelaria Real)
│
└── docs/                           # Documentação Técnica Oficial do TCC
    ├── visao_geral.md              # Visão geral, objetivos e ficha técnica
    ├── estrutura_projeto.md        # Este documento (árvore física e descrição dos arquivos)
    ├── tecnologias_utilizadas.md   # Stack tecnológica detalhada e justificativas
    ├── banco_de_dados.md           # Modelo conceitual, lógico e dicionário de dados
    ├── banco_de_dados_atualizado.md# Especificação detalhada do schema DDL, constraints e índices
    ├── fluxo_sistema.md            # Diagramas de fluxo e processos de negócio
    ├── diario_de_desenvolvimento.md# Histórico evolutivo e marcos de entrega do TCC
    ├── perguntas_banca.md          # Guia preparatório de perguntas e respostas para a banca
    ├── melhorias_futuras.md        # Roadmap de expansão pós-TCC
    ├── revisao_final_tcc.md        # Checklist de homologação para a apresentação
    └── modulos/                    # Manuais detalhados de cada módulo do sistema
        ├── navegacao_e_layout.md   # [NOVO v2.0] Especificação da Sidebar, Topbar, Popover e Paginação
        ├── etiquetas.md            # [NOVO v2.0] Manual de impressão de etiquetas térmicas e código de barras SVG
        ├── vendas_pdv.md           # Manual do PDV com atalhos, som e troco dinâmico
        ├── produtos.md             # Manual de produtos, estoque mínimo e Live Search
        ├── dashboard.md            # Manual do Dashboard e KPIs em tempo real
        ├── compras.md              # Manual de compras e controle de pedidos
        ├── clientes.md             # Manual de clientes e integração com WhatsApp
        ├── fornecedores.md         # Manual de fornecedores e cotações
        ├── movimentacoes.md        # Manual do livro-razão de estoque
        ├── historico_vendas.md     # Manual de histórico de vendas e cancelamentos
        ├── cupom.md                # Manual do cupom não-fiscal térmico
        ├── nfce.md                 # Manual da prévia de NFC-e
        ├── relatorios.md           # Manual de emissão de relatórios gerenciais
        ├── analise.md              # Manual do centro de inteligência e gráficos
        └── login.md                # Manual de autenticação e segurança de sessão
```

---

## 2. Detecção Híbrida de Ambiente em `config.php`

O arquivo `config.php` possui lógica dinâmica para resolução automática da constante `BASE_URL`:

```php
// Caminho absoluto no disco
define('ROOT_PATH', realpath(__DIR__));

// Detecção inteligente de documento raiz
$_projRoot = str_replace('\\', '/', ROOT_PATH);
$_docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));

if ($_docRoot && strpos($_projRoot, $_docRoot) === 0) {
    define('BASE_URL', rtrim(str_replace($_docRoot, '', $_projRoot), '/'));
} else {
    define('BASE_URL', '/' . basename(ROOT_PATH));
}
```

Essa implementação garante portabilidade imediata (*Plug-and-Play*) sem necessidade de editar caminhos manuais, seja executando localmente em `http://localhost/mrstock/` ou em hospedagens remotas (ex: `http://mrstock.unaux.com/`).