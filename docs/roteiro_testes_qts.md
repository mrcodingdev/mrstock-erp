# Roteiro de Testes de Software — MrStock ERP v2.0 (18 Telas Completas)

**Instituição:** ETEC Fernando Prestes (Centro Paula Souza — Sorocaba/SP)  
**Curso:** Técnico em Desenvolvimento de Sistemas  
**Componente Curricular:** Qualidade e Teste de Software (QTS) / TCC  
**Equipe Mr. Coding:** Douglas, Cesar, Eduardo, Enzo e Nikolas  
**Orientadores:** Prof. Luiz Flávio & Prof. Vinicius  
**Cenário de Negócio:** Papelaria Real  
**Versão Homologada:** 2.0 (SalesOps Edition)

---

# Sumário dos Casos de Uso e Roteiros de Teste

1. [UC001 / RT.001 — Tela de Login & Autenticação (`/login.php`)](#uc001--rt001--tela-de-login--autenticação)
2. [UC002 / RT.002 — Tela de Dashboard Geral & Venda Rápida (`/dashboard.php`)](#uc002--rt002--tela-de-dashboard-geral--venda-rápida)
3. [UC003 / RT.003 — Tela de Catálogo & Gestão de Produtos (`/produtos/index.php`)](#uc003--rt003--tela-de-catálogo--gestão-de-produtos)
4. [UC004 / RT.004 — Tela de Gerador de Etiquetas SVG (`/produtos/etiquetas.php`)](#uc004--rt004--tela-de-gerador-de-etiquetas-svg)
5. [UC005 / RT.005 — Tela de Movimentações de Estoque (`/produtos/movimentacoes.php`)](#uc005--rt005--tela-de-movimentações-de-estoque)
6. [UC006 / RT.006 — Tela de Categorias de Produtos (`/categorias/index.php`)](#uc006--rt006--tela-de-categorias-de-produtos)
7. [UC007 / RT.007 — Tela de Gestão de Clientes (`/clientes/index.php`)](#uc007--rt007--tela-de-gestão-de-clientes)
8. [UC008 / RT.008 — Tela de Gestão de Fornecedores (`/fornecedores/index.php`)](#uc008--rt008--tela-de-gestão-de-fornecedores)
9. [UC009 / RT.009 — Tela de Listagem de Compras (`/compras/index.php`)](#uc009--rt009--tela-de-listagem-de-compras)
10. [UC010 / RT.010 — Tela de Nova Compra & Entrada de Mercadorias (`/compras/nova.php`)](#uc010--rt010--tela-de-nova-compra--entrada-de-mercadorias)
11. [UC011 / RT.011 — Tela de Visualizar Pedido de Compra (`/compras/visualizar.php`)](#uc011--rt011--tela-de-visualizar-pedido-de-compra)
12. [UC012 / RT.012 — Tela de Frente de Caixa / PDV SalesOps (`/vendas/pdv.php`)](#uc012--rt012--tela-de-frente-de-caixa--pdv-salesops)
13. [UC013 / RT.013 — Tela de Histórico de Vendas (`/vendas/historico.php`)](#uc013--rt013--tela-de-histórico-de-vendas)
14. [UC014 / RT.014 — Tela de Emissão de Cupom Térmico (`/vendas/cupom.php`)](#uc014--rt014--tela-de-emissão-de-cupom-térmico)
15. [UC015 / RT.015 — Tela de Demonstração de NFC-e (`/vendas/nfce.php`)](#uc015--rt015--tela-de-demonstração-de-nfc-e)
16. [UC016 / RT.016 — Tela Central de Relatórios (`/relatorios/index.php`)](#uc016--rt016--tela-central-de-relatórios)
17. [UC017 / RT.017 — Tela do Centro de Inteligência / BI (`/relatorios/analise.php`)](#uc017--rt017--tela-do-centro-de-inteligência--bi)
18. [UC018 / RT.018 — Tela de Impressão de Relatório PDF (`/relatorios/pdf.php`)](#uc018--rt018--tela-de-impressão-de-relatório-pdf)

---

# UC001 / RT.001 — Tela de Login & Autenticação

### Descrição
Permite aos operadores cadastrados autenticarem-se com segurança no sistema via Bcrypt, aplicando isolamento de perfil RBAC (`admin` e `caixa`).

### Perfil
Administrador (`admin`), Operador de Caixa (`caixa`)

### Pré-condições
Usuário cadastrado na tabela `usuarios`.

### Caminho no Sistema
Página Inicial > **Tela de Login** (`/login.php`)

---

## RT.001 — Roteiro de Teste: Login & Autenticação

### CN001 — Entrar com Sucesso (Admin e Caixa)
- **Localização:** `/login.php`
- **Pré-condições:** Usuário cadastrado no banco de dados.

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Preencher Usuário: `admin` e Senha: `admin`.<br>2. Clicar em **'Entrar'**. | Autenticação autorizada e redirecionamento para `dashboard.php`. | ✅ **Aprovado** |
| **CT102** | 1. Preencher Usuário: `caixa` e Senha: `caixa`.<br>2. Clicar em **'Entrar'**. | Autenticação autorizada e redirecionamento direto para o PDV (`vendas/pdv.php`). | ✅ **Aprovado** |

### CN002 — Entrar com Dados Inválidos ou em Branco
- **Localização:** `/login.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT201** | 1. Deixar os campos em branco.<br>2. Clicar em **'Entrar'**. | Submissão bloqueada com alerta de preenchimento obrigatório. | ✅ **Aprovado** |
| **CT202** | 1. Informar Usuário: `admin` e Senha errada: `9999`.<br>2. Clicar em **'Entrar'**. | Exibe mensagem de erro: *"Usuário ou senha incorretos."* | ✅ **Aprovado** |

### CN003 — Encerramento Seguro de Sessão (Logout)
- **Localização:** Topbar > Botão **'Sair'** (`/logout.php`)

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT301** | 1. Clicar no botão **'Sair'**.<br>2. Tentar acessar página protegida pela URL. | Sessão destruída e redirecionamento imediato para a tela de login. | ✅ **Aprovado** |

---

# UC002 / RT.002 — Tela de Dashboard Geral & Venda Rápida

### Descrição
Apresenta os indicadores de faturamento diário e mensal, alertas de estoque crítico, produtos próximos da validade, gráficos Chart.js e widget de Venda Rápida.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Dashboard** (`/dashboard.php`)

---

## RT.002 — Roteiro de Teste: Dashboard

### CN001 — Visualização de KPIs e Alertas em Tempo Real
- **Localização:** `/dashboard.php`
- **Pré-condições:** Usuário logado como `admin`.

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Acessar o Dashboard.<br>2. Inspecionar os 4 cards de KPI superiores. | Exibe faturamento hoje, vendas do mês, total de alertas de estoque e SKUs ativos. | ✅ **Aprovado** |
| **CT102** | 1. Verificar a fita de alertas de estoque mínimo. | Exibe os produtos com quantidade igual ou inferior ao estoque mínimo. | ✅ **Aprovado** |

### CN002 — Operação de Venda Rápida no Dashboard
- **Localização:** `/dashboard.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT201** | 1. No card de Venda Rápida, selecionar um produto com estoque.<br>2. Escolher pagamento: `PIX` e clicar em **'Lançar Venda'**. | Venda gravada, estoque baixado e KPIs do dashboard atualizados em tempo real. | ✅ **Aprovado** |

---

# UC003 / RT.003 — Tela de Catálogo & Gestão de Produtos

### Descrição
Permite cadastrar, editar, excluir e pesquisar produtos em tempo real com Live Search, controlando preço de custo, markup, preço de venda e estoque mínimo.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Produtos** > **Catálogo de Produtos** (`/produtos/index.php`)

---

## RT.003 — Roteiro de Teste: Produtos

### CN001 — Cadastro de Novo Produto com Margem Automática
- **Localização:** `/produtos/index.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Clicar em **'Adicionar Produto'**.<br>2. Preencher Nome, EAN-13, Custo (10.00) e Venda (25.00).<br>3. Salvar. | Produto cadastrado com sucesso, margem calculada (150%) e item inserido no inventário. | ✅ **Aprovado** |

### CN002 — Busca Dinâmica via Live Search e Menu de 3 Pontos
- **Localização:** `/produtos/index.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT201** | 1. Digitar o nome do produto no campo de busca. | A tabela filtra instantaneamente sem recarregar a página. | ✅ **Aprovado** |
| **CT202** | 1. Clicar no menu de 3 pontos (`.so-actions-btn`) e selecionar **'Editar'**. | Abre modal com os dados carregados prontos para alteração. | ✅ **Aprovado** |

---

# UC004 / RT.004 — Tela de Gerador de Etiquetas SVG

### Descrição
Permite gerar lotes de etiquetas com códigos de barras **Code-128** ou **EAN-13** renderizados em **SVG vetorial puro** sem internet, formatadas para folhas A4 e impressoras térmicas.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Produtos** > **Gerador de Etiquetas** (`/produtos/etiquetas.php`)

---

## RT.004 — Roteiro de Teste: Gerador de Etiquetas

### CN001 — Configuração e Emissão de Lote de Etiquetas
- **Localização:** `/produtos/etiquetas.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Selecionar Categoria: *Escolar*.<br>2. Marcar produtos e definir 4 cópias.<br>3. Clicar em **'Visualizar Impressão'**. | Gera grade de etiquetas com códigos de barras vetoriais SVG nítidos e preços. | ✅ **Aprovado** |
| **CT102** | 1. Clicar no botão **'Imprimir'** ou pressionar `Ctrl + P`. | Dispara diálogo do sistema operacional com folha limpa sem cabeçalhos (`@media print`). | ✅ **Aprovado** |

---

# UC005 / RT.005 — Tela de Movimentações de Estoque

### Descrição
Exibe o livro-razão de auditoria de estoque com histórico detalhado de todas as entradas por compras, saídas por vendas, perdas e devoluções.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Estoque** > **Movimentações** (`/produtos/movimentacoes.php`)

---

## RT.005 — Roteiro de Teste: Movimentações

### CN001 — Consulta e Auditoria de Entradas e Saídas
- **Localização:** `/produtos/movimentacoes.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Acessar a tela de movimentações.<br>2. Inspecionar as linhas da tabela. | Exibe data/hora, produto, tipo (`entrada_compra`, `saida_venda`), quantidade e operador. | ✅ **Aprovado** |
| **CT102** | 1. Utilizar o campo de busca rápida. | Filtra instantaneamente as movimentações por nome de item ou motivo. | ✅ **Aprovado** |

---

# UC006 / RT.006 — Tela de Categorias de Produtos

### Descrição
Gerencia a classificação mercadológica da papelaria (ex: *Escolar*, *Escrita*, *Artes*, *Organização*).

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Produtos** > **Categorias** (`/categorias/index.php`)

---

## RT.006 — Roteiro de Teste: Categorias

### CN001 — Cadastro e Edição de Categorias
- **Localização:** `/categorias/index.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Clicar em **'Nova Categoria'**.<br>2. Informar Nome: `Informática & Cabos`.<br>3. Salvar. | Categoria criada e disponibilizada no cadastro de produtos. | ✅ **Aprovado** |
| **CT102** | 1. Editar categoria existente e salvar. | Atualiza o nome da categoria na listagem imediatamente. | ✅ **Aprovado** |

---

# UC007 / RT.007 — Tela de Gestão de Clientes

### Descrição
Permite cadastrar clientes físicos ou jurídicos (com CPF/CNPJ) e acionar conversas diretas de atendimento via link da API do WhatsApp (`wa.me`).

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Clientes** (`/clientes/index.php`)

---

## RT.007 — Roteiro de Teste: Clientes

### CN001 — Cadastro de Cliente e Acionamento do WhatsApp
- **Localização:** `/clientes/index.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Cadastrar cliente com Nome: `Colégio Objetivo` e Telefone: `(15) 99123-4567`.<br>2. Salvar. | Cliente registrado com status ativo. | ✅ **Aprovado** |
| **CT102** | 1. Clicar no botão verde do WhatsApp na linha do cliente. | Abre nova aba no navegador com a URL `https://wa.me/5515991234567` pronta para envio. | ✅ **Aprovado** |

---

# UC008 / RT.008 — Tela de Gestão de Fornecedores

### Descrição
Gerencia as distribuidoras parceiras da Papelaria Real (Tilibra, Bic, Acrilex), CNPJs, contatos e link para cotação no WhatsApp.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Fornecedores** (`/fornecedores/index.php`)

---

## RT.008 — Roteiro de Teste: Fornecedores

### CN001 — Cadastro e Proteção de Chave Estrangeira
- **Localização:** `/fornecedores/index.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Cadastrar fornecedor com Razão Social: `Chamex Papéis` e CNPJ válido.<br>2. Salvar. | Fornecedor gravado e vinculado aos produtos de papelaria. | ✅ **Aprovado** |
| **CT102** | 1. Excluir fornecedor vinculado a produtos. | Ação executada com `ON DELETE SET NULL`, preservando os produtos no catálogo. | ✅ **Aprovado** |

---

# UC009 / RT.009 — Tela de Listagem de Compras

### Descrição
Exibe o histórico de pedidos de compra e abastecimento, valores totais e controle de status de contas a pagar (`PENDENTE` ou `PAGA`).

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Compras** (`/compras/index.php`)

---

## RT.009 — Roteiro de Teste: Listagem de Compras

### CN001 — Acompanhamento e Alternância de Status Financeiro
- **Localização:** `/compras/index.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Acessar a listagem de compras.<br>2. Inspecionar os pedidos registrados. | Exibe data, fornecedor, número de nota, valor total e badge de status financeiro. | ✅ **Aprovado** |
| **CT102** | 1. Clicar no botão para alternar status de `PENDENTE` para `PAGA`. | O status é atualizado imediatamente no banco com confirmação visual. | ✅ **Aprovado** |

---

# UC010 / RT.010 — Tela de Nova Compra & Entrada de Mercadorias

### Descrição
Permite lançar pedidos de compra com múltiplos itens, calculando subtotais e incrementando o saldo físico do estoque automaticamente.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Compras** > **Nova Compra** (`/compras/nova.php`)

---

## RT.010 — Roteiro de Teste: Nova Compra

### CN001 — Lançamento de Ordem de Compra Completa
- **Localização:** `/compras/nova.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Selecionar Fornecedor: `Tilibra S.A`.<br>2. Adicionar 20 unidades de Cadernos a R$ 12,00 cada.<br>3. Clicar em **'Finalizar Registro de Compra'**. | Compra gravada, estoque do produto incrementado em +20 un e registro lançado em movimentações. | ✅ **Aprovado** |

---

# UC011 / RT.011 — Tela de Visualizar Pedido de Compra

### Descrição
Exibe o espelho formal da ordem de compra no padrão Master-Detail com itens detalhados e botão para impressão de conferência no depósito.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Compras > Ações > **Visualizar Compra** (`/compras/visualizar.php?id=...`)

---

## RT.011 — Roteiro de Teste: Visualizar Pedido

### CN001 — Abertura e Impressão do Pedido de Compra
- **Localização:** `/compras/visualizar.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Clicar em **'Visualizar Detalhes'** de uma compra na listagem. | Página abre sem erro 404, listando fornecedor, itens, custos unitários e valor total. | ✅ **Aprovado** |
| **CT102** | 1. Clicar no botão **'Imprimir Ordem de Compra'**. | Envia formulário limpo para a impressora para conferência física no recebimento. | ✅ **Aprovado** |

---

# UC012 / RT.012 — Tela de Frente de Caixa / PDV SalesOps

### Descrição
Permite ao operador realizar vendas com velocidade máxima no teclado (`F2`, `F4`, `F8`, `F9`, `ESC`), com bipe sonoro de 880Hz via Web Audio API, modal de troco com cédulas e baixa de estoque com lock pessimista (`SELECT ... FOR UPDATE`).

### Perfil
Operador de Caixa (`caixa`), Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **PDV (Frente de Caixa)** (`/vendas/pdv.php`)

---

## RT.012 — Roteiro de Teste: PDV SalesOps

### CN001 — Venda Completa com Atalhos e Troco Dinâmico
- **Localização:** `/vendas/pdv.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Pressionar `F2`.<br>2. Digitar código `7891027101015` e teclar Enter. | Leitor emite bip de 880Hz, o item entra no carrinho e o total é recalculado. | ✅ **Aprovado** |
| **CT102** | 1. Pressionar `F8`.<br>2. Clicar na cédula de **R$ 50,00**.<br>3. Confirmar a venda. | Modal exibe troco exato em verde, processa a transação e redireciona para o cupom. | ✅ **Aprovado** |

### CN002 — Validação de Concorrência e Estoque Esgotado
- **Localização:** `/vendas/pdv.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT201** | 1. Tentar vender quantidade maior do que o saldo físico no banco. | O backend detecta no `FOR UPDATE`, executa `rollBack()` e bloqueia a venda. | ✅ **Aprovado** |

---

# UC013 / RT.013 — Tela de Histórico de Vendas

### Descrição
Permite consultar vendas anteriores por período ou cliente, exibir cards de KPI (Faturamento, Total de Vendas, Ticket Médio) e cancelar vendas com estorno automático no estoque.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Histórico de Vendas** (`/vendas/historico.php`)

---

## RT.013 — Roteiro de Teste: Histórico de Vendas

### CN001 — Filtros de Período e Reimpressão de Cupom
- **Localização:** `/vendas/historico.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Informar Data Inicial e Data Final.<br>2. Clicar em **'Filtrar'**. | Tabela filtra as vendas do intervalo e os cards de KPI recalculam instantaneamente. | ✅ **Aprovado** |
| **CT102** | 1. Clicar no menu 3 pontos de uma venda e escolher **'Reimprimir Cupom'**. | Abre o cupom térmico original com todos os itens da venda. | ✅ **Aprovado** |

---

# UC014 / RT.014 — Tela de Emissão de Cupom Térmico

### Descrição
Renderiza o comprovante de venda não-fiscal formatado para bobinas térmicas de 80mm e 58mm, com Hash SHA-256 e botão de nova venda.

### Perfil
Operador de Caixa (`caixa`), Administrador (`admin`)

### Caminho no Sistema
Redirecionamento pós-venda (`/vendas/cupom.php?id=...`)

---

## RT.014 — Roteiro de Teste: Cupom Térmico

### CN001 — Renderização e Retorno Rápido ao Caixa
- **Localização:** `/vendas/cupom.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Inspecionar o cupom emitido na tela. | Exibe cabeçalho da Papelaria Real, itens, total, forma de pagamento e Hash SHA-256. | ✅ **Aprovado** |
| **CT102** | 1. Clicar no botão **'Nova Venda'**. | Retorna imediatamente ao PDV com foco pronto no leitor de código de barras. | ✅ **Aprovado** |

---

# UC015 / RT.015 — Tela de Demonstração de NFC-e

### Descrição
Exibe o layout demonstrativo de Nota Fiscal de Consumidor Eletrônica com chave de acesso de 44 dígitos e QR Code para homologação futura na SEFAZ.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Vendas > **Prévia NFC-e** (`/vendas/nfce.php`)

---

## RT.015 — Roteiro de Teste: Demonstração de NFC-e

### CN001 — Visualização da DANFE NFC-e
- **Localização:** `/vendas/nfce.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Acessar a tela de prévia de NFC-e. | Exibe a DANFE com chave de acesso formatada de 44 dígitos e QR Code demonstrativo. | ✅ **Aprovado** |

---

# UC016 / RT.016 — Tela Central de Relatórios

### Descrição
Centraliza a seleção e filtros dos relatórios executivos do ERP e disponibiliza a exportação para planilhas Excel formatadas em 9 colunas estritas.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Relatórios** (`/relatorios/index.php`)

---

## RT.016 — Roteiro de Teste: Central de Relatórios

### CN001 — Exportação de Planilha Excel (9 Colunas)
- **Localização:** `/relatorios/index.php` e `/relatorios/excel.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Clicar no botão **'Exportar para Excel'**. | Faz download da planilha `.xls` perfeitamente estruturada em 9 colunas reais (A-I). | ✅ **Aprovado** |

---

# UC017 / RT.017 — Tela do Centro de Inteligência / BI

### Descrição
Apresenta gráficos interativos em Chart.js com cálculo dinâmico de faturamento, Custo das Mercadorias Vendidas (CMV), lucro bruto e margem comercial percentual com seletor de período (7 dias, mês atual, 12 meses).

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Principal > **Relatórios** > **Centro de Inteligência** (`/relatorios/analise.php`)

---

## RT.017 — Roteiro de Teste: Centro de Inteligência (BI)

### CN001 — Análise Temporal e Gráficos de Rentabilidade
- **Localização:** `/relatorios/analise.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Acessar o centro de inteligência.<br>2. Alterne entre os botões **'7 Dias'**, **'Mês Atual'** e **'12 Meses'**. | Gráficos Chart.js e métricas de Lucro Bruto e Margem atualizam de forma reativa. | ✅ **Aprovado** |

---

# UC018 / RT.018 — Tela de Impressão de Relatório PDF

### Descrição
Renderiza relatórios formatados em folha A4 com totalizadores, dados da Papelaria Real e cabeçalhos escuros prontos para envio à impressora.

### Perfil
Administrador (`admin`)

### Caminho no Sistema
Menu Relatórios > **Gerar PDF / Imprimir** (`/relatorios/pdf.php`)

---

## RT.018 — Roteiro de Teste: Relatório PDF

### CN001 — Impressão de Relatório A4
- **Localização:** `/relatorios/pdf.php`

| ID | Passos | Resultado Esperado | Execução |
| :---: | :--- | :--- | :---: |
| **CT101** | 1. Acessar `/relatorios/pdf.php`.<br>2. Inspecionar o layout gerado. | Exibe relatório limpo em folha A4 com totalizadores de estoque e vendas. | ✅ **Aprovado** |
| **CT102** | 1. Clicar no botão **'Imprimir Relatório'**. | Abre diálogo de impressão do navegador sem barras de navegação ou menus. | ✅ **Aprovado** |

---

## 🎯 Resumo Estatístico do Roteiro de Testes
- **Total de Telas Mapeadas:** 18 Telas Reais do Sistema.
- **Total de Casos de Uso:** 18 Casos de Uso (UC001 a UC018).
- **Total de Roteiros de Teste:** 18 Roteiros Estruturados (RT.001 a RT.018).
- **Total de Casos de Teste (CTs):** 36 Casos de Teste detalhados.
- **Taxa de Conformidade:** **100% de Aprovação em Ambiente Real**.