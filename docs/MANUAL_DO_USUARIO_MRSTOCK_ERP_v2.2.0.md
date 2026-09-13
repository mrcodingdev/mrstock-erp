# MANUAL DO USUÁRIO OFICIAL — MRSTOCK ERP v2.2.0
## Sistema Integrado de Gestão Comercial, Controle de Estoque com Validades PEPS/FIFO e Frente de Caixa (PDV) Ágil

---

```
========================================================================================
                          PAPELARIA REAL LTDA — SOROCABA/SP
                CENTRO ESTADUAL DE EDUCAÇÃO TECNOLÓGICA PAULA SOUZA
                       ETEC FERNANDO PRESTES — SOROCABA/SP
         CURSO TÉCNICO EM DESENVOLVIMENTO DE SISTEMAS — TRABALHO DE CONCLUSÃO DE CURSO
========================================================================================
```

### FICHA TÉCNICA E CRÉDITOS DO PROJETO

- **Sistema:** MrStock ERP — Versão 2.2.0 (SalesOps & QTS Edition — 100% Homologado)
- **Cliente Homologado:** Papelaria Real Ltda (Rua XV de Novembro, 250 - Centro, Sorocaba/SP)
- **Instituição de Ensino:** Escola Técnica Estadual Fernando Prestes (ETEC Fernando Prestes)
- **Mantenedora:** Centro Estadual de Educação Tecnológica Paula Souza (CPS) / Governo do Estado de São Paulo
- **Componente Curricular:** Trabalho de Conclusão de Curso (TCC) & Qualidade e Teste de Software (QTS) — Ano 2026
- **Orientadores Oficiais:** Prof. Luiz Flávio & Prof. Vinicius

#### Equipe Mr. Coding (Autores & Desenvolvedores):
1. **Douglas Moraes Braz:** Líder Técnico, Arquiteto de Software e Engenheiro Full-Stack.
2. **Nikolas Pires Brandão:** Modelagem de Banco de Dados Relacional, Diagrama DER e DBA MySQL.
3. **Cesar Augusto da Silva Junior:** Engenharia de Requisitos, Interface com o Cliente e Validação Comercial.
4. **Enzo de Oliveira Soares:** Redação Técnica, Documentação Acadêmica e Normas ABNT/CPS.
5. **Eduardo Sugahara Neto:** Navegação Operacional, Apresentação e Demonstração Prática na Banca.

---

## SUMÁRIO EXECUTIVO

- [Termo de Responsabilidade e Sigilo Operacional](#termo-de-responsabilidade-e-sigilo-operacional)
- [Tutorial "Comece Aqui" (Guia Rápido de 10 Minutos)](#tutorial-comece-aqui-guia-rápido-de-10-minutos)
- [Capítulo 1 — Acesso, Navegação Global e Segurança](#capítulo-1--acesso-navegação-global-e-segurança)
  - [1.1 Tela 01: Autenticação & Login (/login.php)](#11-tela-01-autenticação--login-loginphp)
  - [1.2 Tela 02: Encerramento Seguro de Sessão (/logout.php)](#12-tela-02-encerramento-seguro-de-sessão-logoutphp)
  - [1.3 Tela 24: Topbar & Sidebar Retrátil (inc/header.php)](#13-tela-24-topbar--sidebar-retrátil-incheaderphp)
- [Capítulo 2 — Gestão Estratégica e Dashboard Executivo](#capítulo-2--gestão-estratégica-e-dashboard-executivo)
  - [2.1 Tela 03: Dashboard Executivo & Venda Rápida (/dashboard.php)](#21-tela-03-dashboard-executivo--venda-rápida-dashboardphp)
- [Capítulo 3 — Frente de Caixa, Vendas e Operações Fiscais](#capítulo-3--frente-de-caixa-vendas-e-operações-fiscais)
  - [3.1 Tela 04: Ponto de Venda (PDV Ágil) (/vendas/pdv.php)](#31-tela-04-ponto-de-venda-pdv-ágil-vendaspdvphp)
  - [3.2 Tabela Oficial de Atalhos de Teclado do PDV](#32-tabela-oficial-de-atalhos-de-teclado-do-pdv)
  - [3.3 Tela 05: Histórico de Vendas (/vendas/historico.php)](#33-tela-05-histórico-de-vendas-vendashistoricophp)
  - [3.4 Tela 06: Cupom Térmico Não-Fiscal 80mm/58mm (/vendas/cupom.php)](#34-tela-06-cupom-térmico-não-fiscal-80mm58mm-vendascupomphp)
  - [3.5 Tela 07: Painel Fiscal e Simulação Acadêmica de NFC-e (/vendas/nfce.php)](#35-tela-07-painel-fiscal-e-simulação-acadêmica-de-nfc-e-vendasnfcephp)
- [Capítulo 4 — Gestão de Estoque, Produtos e Catalogação](#capítulo-4--gestão-de-estoque-produtos-e-catalogação)
  - [4.1 Tela 08: Catálogo & Gestão de Produtos (/produtos/index.php)](#41-tela-08-catálogo--gestão-de-produtos-produtosindexphp)
  - [4.2 Tela 09: Lotes Físicos & Controle de Validades PEPS/FIFO (/lotes/index.php)](#42-tela-09-lotes-físicos--controle-de-validades-pepsfifo-lotesindexphp)
  - [4.3 Tela 10: Gerador & Impressão de Etiquetas SVG (/produtos/etiquetas.php)](#43-tela-10-gerador--impressão-de-etiquetas-svg-produtosetiquetasphp)
  - [4.4 Tela 11: Categorias & as 10 Famílias Funcionais (/categorias/index.php)](#44-tela-11-categorias--as-10-famílias-funcionais-categoriasindexphp)
  - [4.5 Tela 12: Movimentações de Estoque & Kardex (/produtos/movimentacoes.php)](#45-tela-12-movimentações-de-estoque--kardex-produtosmovimentacoesphp)
- [Capítulo 5 — Relacionamento Comercial: Clientes e Fornecedores](#capítulo-5--relacionamento-comercial-clientes-e-fornecedores)
  - [5.1 Tela 13: Gestão de Clientes & Busca ViaCEP (/clientes/index.php)](#51-tela-13-gestão-de-clientes--busca-viacep-clientesindexphp)
  - [5.2 Tela 14: Gestão de Fornecedores & WhatsApp Direto (/fornecedores/index.php)](#52-tela-14-gestão-de-fornecedores--whatsapp-direto-fornecedoresindexphp)
- [Capítulo 6 — Abastecimento e Gestão de Compras](#capítulo-6--abastecimento-e-gestão-de-compras)
  - [6.1 Tela 15: Ordens de Compra & Histórico (/compras/index.php)](#61-tela-15-ordens-de-compra--histórico-comprasindexphp)
  - [6.2 Tela 16: Nova Ordem de Compra & Entrada de Mercadorias (/compras/nova.php)](#62-tela-16-nova-ordem-de-compra--entrada-de-mercadorias-comprasnovaphp)
  - [6.3 Tela 17: Conferência de Compra / Espelho do Pedido (/compras/visualizar.php)](#63-tela-17-conferência-de-compra--espelho-do-pedido-comprasvisualizarphp)
- [Capítulo 7 — Centro de Inteligência, BI e Relatórios Estratégicos](#capítulo-7--centro-de-inteligência-bi-e-relatórios-estratégicos)
  - [7.1 Tela 18: Central de Relatórios Gerenciais (/relatorios/index.php)](#71-tela-18-central-de-relatórios-gerenciais-relatoriosindexphp)
  - [7.2 Tela 19: Centro de Inteligência Comercial (BI / Chart.js) (/relatorios/analise.php)](#72-tela-19-centro-de-inteligência-comercial-bi--chartjs-relatoriosanalisephp)
  - [7.3 Tela 20: Trilha de Auditoria Forense & Logs (/relatorios/logs.php)](#73-tela-20-trilha-de-auditoria-forense--logs-relatorioslogsphp)
  - [7.4 Tela 21: Exportação de Relatórios para Excel XLSX (/relatorios/excel.php)](#74-tela-21-exportação-de-relatórios-para-excel-xlsx-relatoriosexcelphp)
- [Capítulo 8 — Administração do Sistema e Governança RBAC](#capítulo-8--administração-do-sistema-e-governança-rbac)
  - [8.1 Tela 23: Configurações da Empresa & Perfis de Acesso (/configuracoes.php)](#81-tela-23-configurações-da-empresa--perfis-de-acesso-configuracoesphp)
  - [8.2 Fechamento de Caixa Cego e Conciliação de Gaveta](#82-fechamento-de-caixa-cego-e-conciliação-de-gaveta)
- [Capítulo 9 — Suporte Operacional, FAQ e Protocolos de Contingência](#capítulo-9--suporte-operacional-faq-e-protocolos-de-contingência)
  - [9.1 Tela 22: Central de Ajuda & FAQ Interativo (/ajuda.php)](#91-tela-22-central-de-ajuda--faq-interativo-ajudaphp)
  - [9.2 As 5 Perguntas Mais Frequentes da Operação (FAQ Oficial)](#92-as-5-perguntas-mais-frequentes-da-operação-faq-oficial)
  - [9.3 Protocolo de Contingência Offline (Modo Local XAMPP)](#93-protocolo-de-contingência-offline-modo-local-xampp)
  - [9.4 Canais de Atendimento e SLA de Suporte (2 Horas Úteis)](#94-canais-de-atendimento-e-sla-de-suporte-2-horas-úteis)
- [Glossário de Termos Técnicos e Comerciais](#glossário-de-termos-técnicos-e-comerciais)
- [Notas para Revisão Acadêmica (Enzo e Nikolas)](#notas-para-revisão-acadêmica-enzo-e-nikolas)

---

## TERMO DE RESPONSABILIDADE E SIGILO OPERACIONAL

O **MrStock ERP v2.2.0** é propriedade de uso comercial da **Papelaria Real Ltda**. Cada operador de caixa e gestor possui credencial privativa intransferível. 

Todas as operações efetuadas — incluindo autenticação, abertura de venda, aplicação de desconto, cancelamento de itens, estorno de cupom e exportação de relatórios — são registradas com carimbo de data, horário, operador autenticado e IP de rede na tabela `logs_auditoria`. É terminantemente vedado o empréstimo de senhas. Divergências financeiras e operacionais serão imputadas diretamente ao operador cuja sessão estiver ativa no instante do evento.

---

## TUTORIAL "COMECE AQUI" (GUIA RÁPIDO DE 10 MINUTOS)

Se este é o seu primeiro dia de trabalho na Papelaria Real, execute este roteiro prático para dominar a rotina de vendas em 10 minutos:

1. **Acesse o Sistema:** Abra o navegador Google Chrome e digite `https://mrstock.com.br/login.php`. Digite seu usuário (`caixa` ou seu e-mail corporativo) e senha pessoal.
2. **Entenda a Sua Tela Inicial:** Se você for operador de caixa, o sistema abrirá diretamente na **Frente de Caixa (PDV)** (`/vendas/pdv.php`). Se for administrador, abrirá no **Dashboard** (`/dashboard.php`).
3. **Simule uma Venda no Balcão:**
   - No PDV, pressione a tecla **`F2`** do teclado. O cursor irá para o campo de código de barras.
   - Digite o código `7891027111223` (ou use o leitor de código de barras em um caderno) e pressione **`Enter`**.
   - O sistema emitirá um sinal sonoro de confirmação e inserirá o item no cupom.
4. **Finalize a Venda:**
   - Pressione **`F4`** para abrir o modal de pagamento.
   - Digite o valor em dinheiro entregue pelo cliente e confira o troco calculado na tela.
   - Pressione **`Enter`** para confirmar e **`Ctrl + P`** para imprimir a Simulação de Cupom Fiscal NFC-e.
5. **Consulte a Venda Realizada:**
   - Acesse o **Histórico de Vendas** em `/vendas/historico.php`. Sua venda recém-concluída estará listada no topo com status "Concluída".
---

# CAPÍTULO 1 — ACESSO, NAVEGAÇÃO GLOBAL E SEGURANÇA

### 1.1 Tela 01: Autenticação & Login (`/login.php`)

📋 RESUMO RÁPIDO — Tela de Login
- **Para que serve:** Permite aos operadores autenticarem-se com segurança no sistema via hash BCrypt, aplicando isolamento de perfil RBAC e proteção contra ataques CSRF e Session Fixation.
- **Quem pode acessar:** Todos os usuários (Administrador e Operador de Caixa).
- **Onde encontrar:** Página Inicial > Tela de Login (`/login.php`).

[INSERIR PRINT DE TELA: 01_login_autenticacao.png]

#### Passo a Passo Operacional
1. Acesse o endereço oficial no navegador: `https://mrstock.com.br/login.php` (ou `http://localhost/MrStock/login.php` em modo offline).
2. No campo **Usuário**, digite seu identificador ou e-mail corporativo (ex: `admin` ou `caixa`).
3. No campo **Senha**, digite sua senha de acesso.
4. Clique no botão sólido verde **Entrar** (ou pressione `Enter`).
5. O sistema validará a assinatura criptográfica e redirecionará automaticamente: Administrador para o Dashboard e Caixa diretamente para o PDV.

> 💡 **Dica:** Você pode pressionar `Tab` após digitar o usuário para pular diretamente ao campo de senha sem usar o mouse.

> ⚠️ **Atenção:** Cinco tentativas consecutivas de senha incorreta disparam bloqueio preventivo temporário por proteção contra força bruta.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** A tela exibe alerta em vermelho: *"Usuário ou senha incorretos."* (CT202 / UC001).  
> **Solução:** Certifique-se de que a tecla `Caps Lock` não está ativada. Digite a senha com calma. Se persistir, contate o Administrador para redefinição.

> ❌ **Erro comum:** O formulário não é enviado e os campos ficam com contorno vermelho (CT201 / UC001).  
> **Solução:** Os campos de usuário e senha são de preenchimento estritamente obrigatório. Preencha ambos antes de clicar em Entrar.

---

### 1.2 Tela 02: Encerramento Seguro de Sessão (`/logout.php`)

📋 RESUMO RÁPIDO — Logout do Sistema
- **Para que serve:** Destrói a sessão ativa em memória, revoga cookies de autenticação e blinda o terminal contra acessos retroativos via botão 'Voltar' do navegador.
- **Quem pode acessar:** Todos os usuários autenticados.
- **Onde encontrar:** Topbar > Menu do Usuário no canto superior direito > **Sair** (`/logout.php`).

[INSERIR PRINT DE TELA: 02_logout_encerramento.png]

#### Passo a Passo Operacional
1. No canto superior direito da tela, clique sobre seu nome/avatar.
2. No menu suspenso, clique na opção **Sair do Sistema**.
3. O sistema encerrará os tokens de sessão e redirecionará a página para a tela limpa de login com mensagem de sessão finalizada.

> 💡 **Dica:** Ao sair, o histórico de formulários em cache é limpo para evitar que outro colaborador veja dados da sua operação.

> ⚠️ **Atenção:** Sempre clique em "Sair" ao afastar-se do balcão. Fechar simplesmente a aba do navegador mantém a sessão temporariamente ativa.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O operador clica na seta 'Voltar' do navegador após sair e teme que a sessão continue aberta (CT102 / UC002).  
> **Solução:** O sistema MrStock ERP possui barreira de segurança em `inc/auth.php`. Ao tentar avançar em página protegida sem sessão ativa, o sistema bloqueia o acesso e força o redirecionamento imediato para `/login.php`.

---

### 1.3 Tela 24: Topbar & Sidebar Retrátil (`inc/header.php`)

📋 RESUMO RÁPIDO — Navegação Global
- **Para que serve:** Estrutura a navegação ergonômica em todo o ERP, oferecendo menu lateral retrátil, breadcrumbs limpos e acesso direto ao perfil do operador.
- **Quem pode acessar:** Todos os usuários (com itens adaptados dinamicamente ao perfil RBAC).
- **Onde encontrar:** Presente de forma fixa no topo e na lateral esquerda de todas as telas internas do sistema.

[INSERIR PRINT DE TELA: 24_topbar_sidebar.png]

#### Passo a Passo Operacional
1. **Recolher o Menu Lateral:** No canto inferior esquerdo da barra lateral, clique no botão **Recolher Menu**. A barra contrairá para o modo ícones, liberando até 20% mais espaço útil na tela para tabelas longas.
2. **Expandir o Menu Lateral:** Clique em qualquer ícone da barra contraída ou clique na seta de expansão para restaurar os rótulos de texto completos.
3. **Voltar ao Dashboard:** Clique sobre o logotipo oficial da Papelaria Real no topo da barra lateral para regressar instantaneamente à tela principal.
4. **Topbar Limpa:** A barra superior exibe exclusivamente o título da tela atual (ex: *Estoque & Produtos* ou *Ponto de Venda (PDV)*), sem poluição visual.

> 💡 **Dica:** Em computadores com telas compactas (laptops de 14 polegadas), mantenha a sidebar recolhida para visualizar todas as colunas de relatórios sem necessidade de rolagem horizontal.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O operador com perfil "Caixa" procura o botão de Configurações na sidebar e não o encontra (CN003 / UC020).  
> **Solução:** O menu do perfil Caixa oculta propositalmente os módulos administrativos e financeiros para segurança patrimonial da loja.
---

# CAPÍTULO 2 — GESTÃO ESTRATÉGICA E DASHBOARD EXECUTIVO

### 2.1 Tela 03: Dashboard Executivo & Venda Rápida (`/dashboard.php`)

📋 RESUMO RÁPIDO — Dashboard Executivo
- **Para que serve:** Centraliza os 4 KPIs vitais da Papelaria Real, o painel de alerta de validade em 30 dias (PEPS/FIFO), gráficos de tendência de vendas e o widget de venda expressa.
- **Quem pode acessar:** Administrador (acesso completo a faturamento e lucro); Operador de Caixa visualiza apenas atalhos operacionais.
- **Onde encontrar:** Menu Lateral > **Dashboard** (`/dashboard.php`).

[INSERIR PRINT DE TELA: 03_dashboard_executivo.png]

#### Passo a Passo Operacional
1. Acesse o menu **Dashboard**.
2. Inspecione os 4 cartões de indicadores no topo:
   - **Faturamento Hoje (R$):** Total faturado em vendas concluídas no dia.
   - **Total de Vendas:** Quantidade de clientes atendidos no caixa.
   - **Lucro Bruto Real (R$):** Lucro apurado com base no custo exato do lote físico vendido.
   - **Estoque Crítico:** Quantidade de produtos que atingiram a quantidade mínima de alerta.
3. Inspecione a tabela **Alertas de Vencimento**: itens vencendo em até 30 dias aparecem destacados com badge amarelo; itens vencidos em vermelho.
4. **Realizar Venda Rápida sem Abrir o PDV Completo:**
   - No card "Venda Rápida", selecione o produto no menu suspenso.
   - Informe a quantidade e a forma de pagamento (Dinheiro ou Pix).
   - Clique em **Lançar Venda**. O estoque é baixado na hora e os indicadores recalculam automaticamente.

> 💡 **Dica:** Clique no botão **Gerenciar Vencimentos** dentro do card de alerta para ser levado diretamente à tela de lotes filtrada apenas pelos produtos que exigem queima promocional de estoque.

> ⚠️ **Atenção:** O cálculo de Lucro Bruto Real no Dashboard reflete o custo real de cada lote (PEPS/FIFO). Se um item for vendido sem lote cadastrado, o sistema usará o custo de referência do produto.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O operador de caixa tenta acessar o Dashboard e é redirecionado diretamente para `/vendas/pdv.php` (CT102 / UC001).  
> **Solução:** Comportamento normal do sistema. Caixas não possuem permissão para ver dados de lucratividade global e faturamento da empresa.

> ❌ **Erro comum:** O card de Venda Rápida exibe mensagem *"Estoque insuficiente para a quantidade solicitada."*.  
> **Solução:** O produto escolhido está zerado ou o saldo atual é inferior à quantidade digitada. Lance uma nova ordem de compra ou ajuste o inventário.
---

# CAPÍTULO 3 — FRENTE DE CAIXA, VENDAS E OPERAÇÕES FISCAIS

### 3.1 Tela 04: Ponto de Venda (PDV Ágil) (`/vendas/pdv.php`)

📋 RESUMO RÁPIDO — Ponto de Venda (PDV)
- **Para que serve:** Interface ergonômica de alta velocidade para bipagem de itens por código de barras, cálculo automático de troco, atalhos de teclado e baixa física de estoque por PEPS/FIFO.
- **Quem pode acessar:** Operador de Caixa e Administrador.
- **Onde encontrar:** Menu Lateral > Vendas > **Ponto de Venda** (`/vendas/pdv.php`).

[INSERIR PRINT DE TELA: 04_pdv_frente_caixa.png]

#### Passo a Passo Operacional
1. Ao abrir o PDV, o cursor estará posicionado no campo de bipagem.
2. Bipe o código EAN-13 com o leitor óptico (ou tecle **`F2`** e digite o código). Pressione **`Enter`**.
3. O sistema emite sinal sonoro senoidal de 880Hz e insere o produto no carrinho com animação suave.
4. Para vender quantidade múltipla (ex: 10 cartolinas), digite `10*` antes do código ou use o seletor de quantidade.
5. Pressione **`F4`** para abrir o modal de pagamento.
6. Selecione a forma de pagamento (Dinheiro, Pix, Cartão de Débito ou Crédito).
7. Se o pagamento for em dinheiro, informe o valor recebido e confira o troco calculado na tela.
8. Pressione **`Enter`** para confirmar a venda. A tela do cupom fiscal simulado é aberta instantaneamente.

---

### 3.2 Tabela Oficial de Atalhos de Teclado do PDV

Esta tabela reúne os atalhos homologados nos Casos de Teste (CT101–CT105 do Caso de Uso UC004):

| Tecla / Atalho | Função Operacional | Comportamento Exato no Sistema |
| :---: | :--- | :--- |
| **`F1`** | Ajuda de Teclado | Abre modal na tela listando todos os atalhos rápidos disponíveis. |
| **`F2`** | Focar no Leitor / Busca | Move o foco para o campo de código de barras ou busca de produto. |
| **`F4`** | Finalizar Compra / Pagamento | Abre a janela modal de formas de pagamento e cálculo de troco. |
| **`F7`** | Conceder Desconto | Permite aplicar desconto percentual ou em reais (sujeito à trava de margem). |
| **`F9`** | Cancelar / Limpar Carrinho | Remove todos os itens do cupom aberto (requer confirmação rápida). |
| **`Esc`** | Voltar / Fechar Janelas | Fecha qualquer modal aberto e retorna o foco à lista de compras. |
| **`Ctrl + P`** | Imprimir Cupom Fiscal | Dispara o comando de impressão do cupom térmico ou folha A4. |

> 💡 **Dica:** Pressionar `F1` a qualquer momento no caixa exibe a colinha de atalhos sem interromper o atendimento.

> ⚠️ **Atenção (Trava de Margem Negativa):** Se um desconto concedido no PDV fizer o preço de venda unitário ficar menor que o custo de compra do lote correspondente, o sistema bloqueará a finalização exibindo aviso de margem negativa, protegendo a papelaria contra prejuízos involuntários.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O operador pressiona `F4` e surge um pop-up vermelho: *"É necessário ao menos 1 produto no carrinho para finalizar a compra."* (CT104 / UC004).  
> **Solução:** Bipe ao menos uma mercadoria antes de abrir a tela de fechamento financeiro.

> ❌ **Erro comum:** Ao tentar conceder desconto de R$ 10,00, o sistema impede a gravação informando *"Operação não permitida: Preço de venda abaixo do custo de aquisição do lote (R$ 15,20)."*.  
> **Solução:** O desconto solicitado viola a política de sustentabilidade financeira da Papelaria Real. Reduza o valor do desconto para manter margem positiva.

---

### 3.3 Tela 05: Histórico de Vendas (`/vendas/historico.php`)

📋 RESUMO RÁPIDO — Histórico de Vendas
- **Para que serve:** Consulta cronológica de todas as vendas processadas, filtros por operador e forma de pagamento, reimpressão de comprovantes e estorno gerencial de vendas.
- **Quem pode acessar:** Administrador (acesso pleno com botão de estorno); Caixa (consulta de suas próprias vendas).
- **Onde encontrar:** Menu Lateral > Vendas > **Histórico de Vendas** (`/vendas/historico.php`).

[INSERIR PRINT DE TELA: 05_historico_vendas.png]

#### Passo a Passo Operacional
1. Acesse `/vendas/historico.php`.
2. Utilize os filtros de cabeçalho: informe **Data Inicial**, **Data Final**, **Cliente** ou **Forma de Pagamento**.
3. A tabela filtra instantaneamente os resultados.
4. Para reimprimir, clique no botão **Imprimir Cupom 80mm**.
5. Para consultar a chave fiscal, clique no botão **Painel Fiscal NFC-e**.
6. Para estornar (apenas Administrador), clique no botão vermelho **Estornar Venda**, informe o motivo formal e confirme.

> 💡 **Dica:** Ao estornar uma venda, o estoque dos produtos vendidos é imediatamente devolvido aos seus respectivos lotes de origem (PEPS/FIFO), sem necessidade de ajuste manual no inventário.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O operador de caixa tenta clicar no botão de Estornar e recebe mensagem *"Acesso negado. Apenas o perfil Administrador pode autorizar o cancelamento de cupons já finalizados."*.  
> **Solução:** Chame a gerente/proprietária para autenticar e confirmar a devolução do dinheiro e o estorno da venda.

---

### 3.4 Tela 06: Cupom Térmico Não-Fiscal 80mm/58mm (`/vendas/cupom.php`)

📋 RESUMO RÁPIDO — Emissão de Cupom Térmico
- **Para que serve:** Gera o documento impresso de conferência para o cliente com layout limpo otimizado para bobinas térmicas de 80mm e 58mm (@media print).
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Disparado automaticamente ao término da venda ou via Histórico > **Imprimir Cupom** (`/vendas/cupom.php?id=...`).

[INSERIR PRINT DE TELA: 06_cupom_termico.png]

#### Passo a Passo Operacional
1. A tela abre com o cupom formatado contendo cabeçalho da Papelaria Real, CNPJ, itens com quantidade e valores, total e troco.
2. Pressione **`Ctrl + P`** (ou clique no botão **Imprimir Agora**).
3. Na janela de impressão do navegador, certifique-se de que o destino é a impressora térmica não-fiscal.
4. Clique em Imprimir e entregue o comprovante ao cliente.

> 💡 **Dica:** O cupom possui regras CSS limpas que removem automaticamente cabeçalhos e rodapés do navegador na impressão.

---

### 3.5 Tela 07: Painel Fiscal e Simulação Acadêmica de NFC-e (`/vendas/nfce.php`)

📋 RESUMO RÁPIDO — Painel Fiscal NFC-e
- **Para que serve:** Apresenta a simulação didática homologada de Nota Fiscal de Consumidor Eletrônica com Chave de Acesso de 44 dígitos, QR Code SEFAZ e discriminação de tributos (Lei 12.741/2012).
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Histórico de Vendas > Botão **Painel Fiscal NFC-e** (`/vendas/nfce.php?id=...`).

[INSERIR PRINT DE TELA: 07_painel_fiscal_nfce.png]

#### Passo a Passo Operacional
1. Acesse o painel da venda desejada.
2. Inspecione a **Chave de Acesso de 44 Dígitos** formatada com máscara oficial: `35-2609-12345678000190-65-001-000001042-1-12345678-9`.
3. Verifique o **QR Code vetorial** gerado para leitura por câmera de smartphone.
4. Confira o cálculo de tributos aproximados destacado na base do documento fiscal.
5. Clique em **Voltar para o PDV** ou **Imprimir DANFE NFC-e**.

> 💡 **Dica Acadêmica:** Este painel foi criado para a avaliação da banca da ETEC, demonstrando a perfeita conformidade com as regras fiscais do Estado de São Paulo sem dependência de certificados A1 pagos.
---

# CAPÍTULO 4 — GESTÃO DE ESTOQUE, PRODUTOS E CATALOGAÇÃO

### 4.1 Tela 08: Catálogo & Gestão de Produtos (`/produtos/index.php`)

📋 RESUMO RÁPIDO — Gestão de Produtos
- **Para que serve:** Cadastro central de mercadorias, controle de código EAN-13, cálculo automático de markup/margem de lucro e busca reativa (Live Search).
- **Quem pode acessar:** Administrador (cadastro/edição plena); Caixa (consulta de saldos).
- **Onde encontrar:** Menu Lateral > Estoque > **Catálogo de Produtos** (`/produtos/index.php`).

[INSERIR PRINT DE TELA: 08_produtos_catalogo.png]

#### Passo a Passo Operacional
1. Clique no botão **+ Adicionar Produto**.
2. Preencha o Nome Completo (ex: `Caneta Esferográfica BIC Cristal 1.0mm Azul`).
3. Informe o código de barras no campo EAN-13 (use o leitor ou digite).
4. Selecione a Família Funcional correspondente.
5. Preencha o **Preço de Custo** (ex: `R$ 1,20`) e o **Preço de Venda** (ex: `R$ 2,50`). O sistema calcula automaticamente a margem de lucro (`108,3%`).
6. Defina o Estoque Mínimo de Alerta e clique em **Salvar**.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O usuário tenta excluir um produto que já teve vendas registradas no caixa (CT103 / UC006).  
> **Solução:** O MrStock ERP adota soft-delete para garantir a integridade referencial ACID do banco de dados. O produto não é apagado fisicamente; ele é marcado como **Inativo**, preservando o histórico das vendas passadas e das auditorias contábeis.

---

### 4.2 Tela 09: Lotes Físicos & Controle de Validades PEPS/FIFO (`/lotes/index.php`)

📋 RESUMO RÁPIDO — Gestão de Lotes
- **Para que serve:** Rastreamento do shelf-life e custo de cada remessa de produtos químicos/perecíveis, aplicando o princípio PEPS/FIFO.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Estoque > **Lotes & Validades** (`/lotes/index.php`).

[INSERIR PRINT DE TELA: 09_lotes_validades.png]

#### Passo a Passo Operacional
1. Clique em **Novo Lote**.
2. Selecione o produto (ex: `Cola Branca Cascola 90g`).
3. Digite o número do lote gravado pelo fabricante (ex: `LOT-2026-CB`).
4. Informe a **Data de Validade** impressa no rótulo.
5. Digite a quantidade recebida e o custo unitário daquele lote.
6. Clique em **Salvar Lote**. O sistema passa a monitorar a janela de 30 dias automaticamente.

> ⚠️ **Atenção:** Produtos com lotes vencidos são sinalizados com badge vermelho e são bloqueados para inclusão no carrinho do PDV.

---

### 4.3 Tela 10: Gerador & Impressão de Etiquetas SVG (`/produtos/etiquetas.php`)

📋 RESUMO RÁPIDO — Gerador de Etiquetas SVG
- **Para que serve:** Gera folhas de etiquetas de gôndola e código de barras Code-128/EAN-13 em SVG vetorial puro com nitidez milimétrica para impressoras térmicas ou folhas A4 Pimaco.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu > Estoque > Produtos > Botão **Imprimir Etiquetas** (`/produtos/etiquetas.php`).

[INSERIR PRINT DE TELA: 10_gerador_etiquetas.png]

#### Passo a Passo Operacional
1. Na tela de etiquetas, selecione a **Categoria / Família** desejada ou escolha produtos individuais.
2. Defina o número de cópias de cada etiqueta (ex: 20 cópias para colar na prateleira de canetas).
3. Clique em **Visualizar Impressão**. A grade vetorial SVG de etiquetas é renderizada com código de barras, nome do item e preço em reais.
4. Pressione **`Ctrl + P`** (ou clique em **Imprimir Folha**). As margens são calibradas automaticamente para impressão limpa.

> 💡 **Dica:** Como as etiquetas são geradas em SVG puro, o código de barras não distorce nem borra, garantindo 100% de leitura pelo leitor óptico.

---

### 4.4 Tela 11: Categorias & as 10 Famílias Funcionais (`/categorias/index.php`)

📋 RESUMO RÁPIDO — Categorias & Famílias
- **Para que serve:** Organização taxonômica da Papelaria Real nas 10 Famílias Funcionais, garantindo acurácia na Curva ABC e facilitando filtros rápidos.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Estoque > **Categorias** (`/categorias/index.php`).

[INSERIR PRINT DE TELA: 11_categorias_familias.png]

#### Passo a Passo Operacional
1. Visualize as 10 famílias oficiais cadastradas no sistema.
2. Para adicionar uma subdivisão, clique em **Nova Categoria**, preencha Nome e Descrição e clique em Salvar.
3. Na listagem, clique no botão **Ver Produtos Vinculados** para abrir o catálogo filtrado apenas pelos itens daquela família.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O usuário tenta excluir uma Categoria que possui produtos vinculados no catálogo.  
> **Solução:** O sistema protege a integridade referencial: transfira os produtos para outra categoria antes de inativá-la.

---

### 4.5 Tela 12: Movimentações de Estoque & Kardex (`/produtos/movimentacoes.php`)

📋 RESUMO RÁPIDO — Movimentações & Kardex (Livro-Razão)
- **Para que serve:** Histórico auditável de todas as entradas, saídas manuais, devoluções, perdas por quebra e baixas por vencimento ocorridas no estoque.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Menu Lateral > Estoque > **Movimentações** (`/produtos/movimentacoes.php`).

[INSERIR PRINT DE TELA: 12_movimentacoes_kardex.png]

#### Passo a Passo Operacional
1. Acesse a tela de movimentações para auditar o fluxo cronológico das mercadorias.
2. Utilize a barra de filtro rápido para buscar por produto, motivo ou operador.
3. Para registrar uma perda física (ex: frasco de tinta derramou no chão), clique em **Nova Movimentação**, selecione o produto, o tipo "Perda / Avaria", informe a quantidade, a justificativa e confirme.

> ⚠️ **Atenção:** Toda movimentação manual subtrai saldo físico real e gera registro forense com seu usuário.
---

# CAPÍTULO 5 — RELACIONAMENTO COMERCIAL: CLIENTES E FORNECEDORES

### 5.1 Tela 13: Gestão de Clientes & Busca ViaCEP (`/clientes/index.php`)

📋 RESUMO RÁPIDO — Gestão de Clientes
- **Para que serve:** Cadastro completo de clientes físicos e jurídicos com validação de documento, preenchimento automático de endereço via CEP e histórico de compras.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Menu Lateral > Clientes > **Listagem de Clientes** (`/clientes/index.php`).

[INSERIR PRINT DE TELA: 13_clientes_cadastro.png]

#### Passo a Passo Operacional
1. Clique no botão **Cadastrar Cliente**.
2. Digite o Nome Completo e o CPF ou CNPJ.
3. No campo **CEP**, digite os 8 números: o sistema consulta o serviço ViaCEP e preenche automaticamente Logradouro, Bairro e Cidade. Preencha o número do imóvel.
4. Digite o Telefone/Celular e clique em **Salvar**.
5. Na listagem de clientes, clique no **botão circular verde do WhatsApp** para abrir contato imediato.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O sistema recusa o cadastro com o alerta *"CPF inválido. Verifique os dígitos verificadores."*.  
> **Solução:** O MrStock ERP possui algoritmo de validação matemática de CPF. Certifique-se de digitar o documento real do cliente.

---

### 5.2 Tela 14: Gestão de Fornecedores & WhatsApp Direto (`/fornecedores/index.php`)

📋 RESUMO RÁPIDO — Gestão de Fornecedores
- **Para que serve:** Cadastro de distribuidoras e indústrias parceiras (Tilibra, BIC, Chamex, Acrilex), com canal direto de cotação de compras via WhatsApp em 1 clique.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Compras e Contatos > **Fornecedores** (`/fornecedores/index.php`).

[INSERIR PRINT DE TELA: 14_fornecedores_whatsapp.png]

#### Passo a Passo Operacional
1. Clique em **Novo Fornecedor**.
2. Preencha Razão Social, Nome Fantasia, CNPJ e Telefone do representante comercial.
3. Clique em **Salvar Fornecedor**.
4. Na listagem, clique no botão circular verde **Abrir WhatsApp** para disparar cotação de reposição com o vendedor da fábrica sem precisar cadastrar o telefone na agenda do aparelho.
---

# CAPÍTULO 6 — ABASTECIMENTO E GESTÃO DE COMPRAS

### 6.1 Tela 15: Ordens de Compra & Histórico (`/compras/index.php`)

📋 RESUMO RÁPIDO — Histórico de Compras
- **Para que serve:** Acompanhamento de todas as aquisições de mercadorias efetuadas pela papelaria, status financeiro (Paga ou Pendente) e auditoria de reposição.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Compras > **Ordens de Compra** (`/compras/index.php`).

[INSERIR PRINT DE TELA: 15_compras_historico.png]

#### Passo a Passo Operacional
1. Acesse a listagem para conferir os pedidos faturados pelas distribuidoras.
2. Filtre por período ou por fornecedor.
3. Para consultar os produtos de determinado pedido, clique em **Ver Detalhes**.

---

### 6.2 Tela 16: Nova Ordem de Compra & Entrada de Mercadorias (`/compras/nova.php`)

📋 RESUMO RÁPIDO — Nova Ordem de Compra
- **Para que serve:** Lançamento de notas fiscais de fornecedor, incremento automático de saldo de estoque e criação dos lotes físicos com validade e custo real.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Ordens de Compra > Botão **Registrar Nova Compra** (`/compras/nova.php`).

[INSERIR PRINT DE TELA: 16_compras_nova_ordem.png]

#### Passo a Passo Operacional
1. Selecione o Fornecedor que emitiu a nota.
2. Adicione os itens comprados informando Quantidade e Custo Unitário que consta na Nota Fiscal.
3. Para produtos químicos/líquidos com validade, informe a Data de Validade e o número do Lote.
4. Clique em **Finalizar Compra**. O estoque é incrementado no banco e o novo lote entra na fila PEPS/FIFO.

> 💡 **Dica:** Sempre confira a quantidade física das caixas antes de confirmar a entrada no sistema.

---

### 6.3 Tela 17: Conferência de Compra / Espelho do Pedido (`/compras/visualizar.php`)

📋 RESUMO RÁPIDO — Conferência de Compra
- **Para que serve:** Exibe o espelho formal do pedido de compra para conferência de recebimento no almoxarifado e impressão em formato A4.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Histórico de Compras > Botão **Ver Detalhes** (`/compras/visualizar.php?id=...`).

[INSERIR PRINT DE TELA: 17_compras_visualizar_espelho.png]

#### Passo a Passo Operacional
1. Abra a tela de conferência do pedido desejado.
2. O sistema exibe o espelho com dados do fornecedor, data de recebimento, lista de itens com custos e valor total.
3. Clique em **Imprimir Ordem/Compra** para imprimir a folha de conferência de entrega para o estoquista assinar.
---

# CAPÍTULO 7 — CENTRO DE INTELIGÊNCIA, BI E RELATÓRIOS ESTRATÉGICOS

### 7.1 Tela 18: Central de Relatórios Gerenciais (`/relatorios/index.php`)

📋 RESUMO RÁPIDO — Central de Relatórios
- **Para que serve:** Ponto de comando para geração de DRE gerencial, Curva ABC de produtos, relatório de validades críticas e giro de estoque.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > **Relatórios** (`/relatorios/index.php`).

[INSERIR PRINT DE TELA: 18_relatorios_central.png]

#### Passo a Passo Operacional
1. Acesse `/relatorios/index.php`.
2. Escolha o relatório desejado (DRE Gerencial, Curva ABC, Giro de Estoque ou Validades).
3. Selecione o período de competência (Mês atual, Trimestre ou Ano).
4. Clique em **Gerar Relatório**.

---

### 7.2 Tela 19: Centro de Inteligência Comercial (BI / Chart.js) (`/relatorios/analise.php`)

📋 RESUMO RÁPIDO — Centro de Análise (BI)
- **Para que serve:** Dashboard analítico interativo com gráficos visuais em tempo real via Chart.js, cruzando faturamento, margem bruta e ticket médio.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Central de Relatórios > **Centro de Análise (BI)** (`/relatorios/analise.php`).

[INSERIR PRINT DE TELA: 19_relatorios_bi_graficos.png]

#### Passo a Passo Operacional
1. Alterne entre os filtros temporais: **7 Dias**, **Mês Corrente** ou **Ano Completo**.
2. Os gráficos de barra e pizza atualizam instantaneamente de forma reativa.
3. Inspecione o gráfico de Curva ABC para identificar os 20% de itens que geram 80% da receita da Papelaria Real.
4. Clique em **Imprimir Relatório Analítico** para exportar em formato A4 executivo.

---

### 7.3 Tela 20: Trilha de Auditoria Forense & Logs (`/relatorios/logs.php`)

📋 RESUMO RÁPIDO — Auditoria & Logs Forenses
- **Para que serve:** Registro imutável de todas as transações, alterações de preço, concessões de desconto, estornos e acessos de operadores com data, hora e IP.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Relatórios > **Logs de Auditoria** (`/relatorios/logs.php`).

[INSERIR PRINT DE TELA: 20_relatorios_logs_auditoria.png]

#### Passo a Passo Operacional
1. Abra a tela de logs para apurar divergências ou incidentes.
2. Utilize o filtro por operador ou por data.
3. Cada linha exibe o carimbo de data/hora, o operador responsável, a ação executada e os dados anteriores e posteriores.

---

### 7.4 Tela 21: Exportação de Relatórios para Excel XLSX (`/relatorios/excel.php`)

📋 RESUMO RÁPIDO — Exportação Excel XLSX
- **Para que serve:** Exporta dados completos de vendas, inventário e validades em planilhas padronizadas em exatamente 9 colunas reais (A-I) prontas para a contabilidade.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Central de Relatórios > Botões **Baixar Excel** (`/relatorios/excel.php`).

[INSERIR PRINT DE TELA: 21_relatorios_exportacao_excel.png]

#### Passo a Passo Operacional
1. Na Central de Relatórios, escolha o conjunto de dados (Inventário Completo, Estoque Baixo, Validades ou Vendas).
2. Clique no botão verde **Baixar Excel**.
3. O download do arquivo `.xlsx` é disparado automaticamente com células pré-formatadas para cálculo contábil.
---

# CAPÍTULO 8 — ADMINISTRAÇÃO DO SISTEMA E GOVERNANÇA RBAC

### 8.1 Tela 23: Configurações da Empresa & Perfis de Acesso (`/configuracoes.php`)

📋 RESUMO RÁPIDO — Painel de Configurações
- **Para que serve:** Configuração dos dados cadastrais da Papelaria Real, mensagem de rodapé do cupom fiscal, parâmetros tributários e gestão de operadores de caixa.
- **Quem pode acessar:** Administrador (acesso estritamente bloqueado para Caixa).
- **Onde encontrar:** Menu Lateral > Sistema > **Configurações** (`/configuracoes.php`).

[INSERIR PRINT DE TELA: 23_configuracoes_empresa.png]

#### Passo a Passo Operacional
1. Atualize a Razão Social, CNPJ, Inscrição Estadual, Telefone e Chave Pix da loja.
2. No campo **Mensagem de Rodapé do Cupom**, defina a frase promocional ou agradecimento que sairá nas bobinas térmicas.
3. Na aba **Operadores & Usuários**, cadastre novos operadores de caixa ou redefina senhas esquecidas.
4. Clique em **Salvar Configurações**.

#### ❌ Erros Comuns e Soluções
> ❌ **Erro comum:** O operador com perfil "Caixa" tenta digitar diretamente a URL `/configuracoes.php` no navegador (CT102 / UC004).  
> **Solução:** O sistema barra o acesso imediatamente via RBAC e redireciona o caixa de volta para o PDV com alerta de privilégio insuficiente.

---

### 8.2 Fechamento de Caixa Cego e Conciliação de Gaveta
1. No final do expediente no PDV, o operador clica em **Fechar Caixa**.
2. O sistema adota a **Conferência Cega:** a tela NÃO mostra o total faturado no dia, evitando indução de erro.
3. O operador conta as cédulas e moedas físicas da gaveta e digita o valor em espécie apurado.
4. Ao clicar em **Confirmar Fechamento**, o sistema cruza o valor digitado com o total calculado no banco e emite o demonstrativo de conciliação apontando se houve caixa exato, sobra ou falta.
---

# CAPÍTULO 9 — SUPORTE OPERACIONAL, FAQ E PROTOCOLOS DE CONTINGÊNCIA

### 9.1 Tela 22: Central de Ajuda & FAQ Interativo (`/ajuda.php`)

📋 RESUMO RÁPIDO — Central de Ajuda & FAQ
- **Para que serve:** Base de conhecimento interativa com busca em tempo real (Live Search), acordeão com as dúvidas operacionais mais frequentes e mesa de atalhos.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Topbar ou Sidebar > **Ajuda & FAQ** (`/ajuda.php`).

[INSERIR PRINT DE TELA: 22_central_ajuda_faq.png]

#### Passo a Passo Operacional
1. No campo *"Como podemos te ajudar hoje?"*, digite uma palavra-chave (ex: "estorno" ou "cupom"). Os tópicos correspondentes são destacados na hora.
2. Clique no título de qualquer dúvida do acordeão para expandir a explicação detalhada.
3. Se precisar de assistência técnica humana, clique no botão verde **Falar com Suporte** para abrir atendimento direto via WhatsApp com a equipe Mr. Coding.

---

### 9.2 As 5 Perguntas Mais Frequentes da Operação (FAQ Oficial)

#### 1. Como funciona a simulação acadêmica de NFC-e com QR Code no PDV?
A simulação acadêmica de NFC-e desenvolvida no MrStock ERP reproduz com exatidão técnica todas as exigências de layout e regras de negócio da Secretaria da Fazenda de São Paulo (SEFAZ SP), incluindo a formação algorítmica da Chave de Acesso de 44 dígitos com dígito verificador, número de protocolo simulado, cálculo estimado da carga tributária conforme a Lei Federal nº 12.741/2012 (*De Olho no Imposto*) e impressão de QR Code vetorial escaneável para bobinas térmicas de 80mm e 58mm. O recurso foi chancelado formalmente pelo orientador Prof. Vinicius como solução didática de engenharia para o TCC da ETEC Fernando Prestes, garantindo validação completa pela banca examinadora sem incorrer em custos com certificados digitais A1 corporativos ou burocracias de credenciamento em ambiente de produção da SEFAZ real.

#### 2. Como o sistema opera em caso de queda de internet (Modo Offline / Local XAMPP)?
O MrStock ERP foi arquitetado com alta disponibilidade operacional para que o atendimento de balcão da Papelaria Real nunca seja interrompido por oscilações do provedor de internet. Em condições normais, a equipe acessa o sistema na nuvem com criptografia SSL em `https://mrstock.com.br/`. Caso a internet externa caia durante o expediente, a papelaria conta com uma instância espelho idêntica configurada no servidor XAMPP local do computador do caixa: basta abrir uma nova aba no navegador e digitar `http://localhost/MrStock/`. As vendas continuam sendo realizadas normalmente na Frente de Caixa local com baixa de estoque em tempo real. Quando a conexão de internet for restabelecida, a sincronização unifica os bancos de dados, garantindo que nenhum cliente fique sem atendimento e nenhuma venda seja perdida.

#### 3. Qual o procedimento correto para estorno de venda e devolução ao estoque?
O procedimento formal de estorno deve ser executado exclusivamente por um colaborador com perfil de Administrador, por questões de segurança financeira. O gestor acessa o menu *Vendas > Histórico de Vendas* (`/vendas/historico.php`), localiza a transação pelo número do cupom ou horário, confere os itens e clica no botão sólido vermelho *Estornar Venda*. Uma janela de confirmação exige o preenchimento obrigatório da justificativa do cancelamento (ex: cliente desistiu da compra antes de retirar a mercadoria). Ao confirmar, o sistema altera o status da venda para cancelada, realiza a devolução física e automática das unidades aos seus exatos lotes de origem (preservando o controle PEPS/FIFO) e grava um registro imutável na trilha de auditoria forense do sistema com carimbo de data, horário, operador responsável e IP da máquina.

#### 4. Por que os produtos são organizados em 10 Famílias Funcionais em vez de categorias genéricas?
A classificação do catálogo em 10 Famílias Funcionais específicas da Papelaria Real (Cadernos & Blocos, Canetas & Marcadores, Lápis & Apontadores, Borrachas & Correção, Colas & Fitas Adesivas, Papéis & Folhas, Pastas & Organização, Corte & Medição, Tintas & Pintura, Grampeadores & Fixação) foi adotada para refletir fielmente a rotina operacional do comércio varejista físico. Categorias macro genéricas (como apenas "Escolar" ou "Escritório") causam distorções graves em papelarias, pois um mesmo caderno pode atender tanto a um estudante quanto a um escritório de advocacia. A separação por famílias funcionais de produtos garante acurácia científica no cálculo da Curva ABC, organiza os relatórios de reposição de compras, facilita o inventário físico nas prateleiras e permite ao atendente localizar rapidamente qualquer item no balcão de vendas.

#### 5. Como solicitar redefinição de senha ou gerenciar novos operadores de caixa?
O gerenciamento de credenciais e operadores de caixa é restrito ao perfil de Administrador da Papelaria Real. Para cadastrar um novo funcionário ou redefinir senhas, o administrador acessa o menu *Configurações > Gestão de Operadores* (`/configuracoes.php`), clica em *Adicionar Novo Usuário*, informa o nome completo, e-mail corporativo, define o perfil de acesso adequado (Caixa com permissões restritas de balcão ou Administrador com acesso total) e cadastra uma senha inicial, que é automaticamente criptografada pelo algoritmo seguro BCrypt (Cost 12). Caso um operador esqueça sua senha, o administrador pode acessar a mesma tela e gerar uma nova senha temporária com um clique, orientando o colaborador a alterá-la no primeiro acesso subsequente para preservar o sigilo pessoal.

---

### 9.3 Protocolo de Contingência Offline (Modo Local XAMPP)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                 PROTOCOLO OPERACIONAL DE CONTINGÊNCIA OFFLINE               │
├─────────────────────────────────────────────────────────────────────────────┤
│ 1. Identificou queda de internet externa (erro de carregamento na nuvem)?    │
│ 2. NÃO reinicie o computador. Mantenha o navegador aberto.                  │
│ 3. Abra uma nova aba e digite o endereço local: http://localhost/MrStock/    │
│ 4. Efetue login normalmente com seu usuário e senha de operador.             │
│ 5. Acesse a Frente de Caixa (PDV) e continue registrando as vendas.         │
│ 6. As vendas e baixas de lotes serão gravadas na base local segura.          │
│ 7. Quando a internet voltar, a sincronização restabelece a nuvem Hostinger. │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 9.4 Canais de Atendimento e SLA de Suporte (2 Horas Úteis)
- **Central de Ajuda Interna:** Disponível 24/7 na barra superior (`/ajuda.php`).
- **Suporte Técnico Equipe Mr. Coding:** Atendimento via canal direto de suporte.
- **SLA Operacional:** Triagem e resposta técnica em até **2 horas úteis** em horário comercial (segunda a sábado, das 08h às 18h).
---

# GLOSSÁRIO DE TERMOS TÉCNICOS E COMERCIAIS

- **PEPS / FIFO (Primeiro que Entra, Primeiro que Sai):** Princípio logístico e contábil onde os produtos adquiridos primeiro (ou com validade mais próxima) são os primeiros a serem consumidos no caixa.
- **NFC-e (Nota Fiscal de Consumidor Eletrônica):** Documento fiscal eletrônico padrão do varejo que registra a operação comercial com o consumidor final.
- **EAN-13:** Padrão internacional de código de barras composto por 13 dígitos numéricos presente nas embalagens comerciais.
- **Curva ABC:** Metodologia de classificação de estoque baseada na regra de Pareto (80/20) que divide produtos em vitais (A), intermediários (B) e baixo giro (C).
- **DRE (Demonstrativo do Resultado do Exercício):** Relatório contábil gerencial que confronta receitas e custos para revelar o lucro bruto da operação.
- **Markup:** Índice percentual ou multiplicador aplicado sobre o custo de compra para fixar o preço de venda de balcão.
- **Custo Médio Ponderado:** Média de custos ponderada pelas quantidades compradas em cada lote de fornecedor.
- **RBAC (Role-Based Access Control):** Mecanismo de governança que restringe o acesso aos recursos do sistema com base no cargo do colaborador (Administrador vs Caixa).
- **Token CSRF:** Identificador criptográfico de uso único para validação de segurança em formulários web.
- **Lote:** Conjunto homogêneo de mercadorias fabricado em um mesmo ciclo, compartilhando prazo de validade e custo de aquisição.
- **Ticket Médio:** Indicador comercial que mede o valor médio gasto por cliente em cada venda (`Faturamento ÷ Quantidade de Vendas`).
- **Estoque Mínimo:** Nível mínimo de mercadorias que aciona o alarme preventivo de reposição para que a loja não fique sem estoque.

---

## NOTAS PARA REVISÃO ACADÊMICA (ENZO E NIKOLAS)

Esta lista cataloga os 24 prints de tela reais correspondentes a cada uma das 24 telas homologadas no Roteiro de Testes de Software (QTS), prontos para substituição na diagramação final:

1. `[INSERIR PRINT DE TELA: 01_login_autenticacao.png]` — Tela de login limpa com campos de usuário e senha.
2. `[INSERIR PRINT DE TELA: 02_logout_encerramento.png]` — Confirmação de encerramento de sessão segura.
3. `[INSERIR PRINT DE TELA: 03_dashboard_executivo.png]` — Dashboard com os 4 KPIs e painel de alertas de validade de 30 dias.
4. `[INSERIR PRINT DE TELA: 04_pdv_frente_caixa.png]` — PDV com itens no carrinho, totalizador em destaque e atalhos F1-F9.
5. `[INSERIR PRINT DE TELA: 05_historico_vendas.png]` — Tabela de vendas filtrada em `/vendas/historico.php`.
6. `[INSERIR PRINT DE TELA: 06_cupom_termico.png]` — Modelo de cupom térmico não-fiscal formatado para 80mm.
7. `[INSERIR PRINT DE TELA: 07_painel_fiscal_nfce.png]` — Chave de acesso de 44 dígitos e QR Code em `/vendas/nfce.php`.
8. `[INSERIR PRINT DE TELA: 08_produtos_catalogo.png]` — Listagem do catálogo com busca reativa e cálculo de markup.
9. `[INSERIR PRINT DE TELA: 09_lotes_validades.png]` — Controle de lotes com alertas cromáticos de 30 dias.
10. `[INSERIR PRINT DE TELA: 10_gerador_etiquetas.png]` — Gerador de etiquetas de código de barras SVG em `/produtos/etiquetas.php`.
11. `[INSERIR PRINT DE TELA: 11_categorias_familias.png]` — As 10 famílias funcionais em `/categorias/index.php`.
12. `[INSERIR PRINT DE TELA: 12_movimentacoes_kardex.png]` — Livro-razão e auditoria de movimentações de estoque.
13. `[INSERIR PRINT DE TELA: 13_clientes_cadastro.png]` — Cadastro de clientes com autopreenchimento de CEP e botão WhatsApp.
14. `[INSERIR PRINT DE TELA: 14_fornecedores_whatsapp.png]` — Fornecedores homologados e botão WhatsApp direto.
15. `[INSERIR PRINT DE TELA: 15_compras_historico.png]` — Histórico de ordens de compra em `/compras/index.php`.
16. `[INSERIR PRINT DE TELA: 16_compras_nova_ordem.png]` — Formulário de entrada de nota fiscal em `/compras/nova.php`.
17. `[INSERIR PRINT DE TELA: 17_compras_visualizar_espelho.png]` — Espelho de conferência de compra em `/compras/visualizar.php`.
18. `[INSERIR PRINT DE TELA: 18_relatorios_central.png]` — Painel central de relatórios e DRE gerencial.
19. `[INSERIR PRINT DE TELA: 19_relatorios_bi_graficos.png]` — Gráficos estatísticos Chart.js em `/relatorios/analise.php`.
20. `[INSERIR PRINT DE TELA: 20_relatorios_logs_auditoria.png]` — Trilha forense de logs em `/relatorios/logs.php`.
21. `[INSERIR PRINT DE TELA: 21_relatorios_exportacao_excel.png]` — Painel de exportação em 9 colunas em `/relatorios/excel.php`.
22. `[INSERIR PRINT DE TELA: 22_central_ajuda_faq.png]` — Central de ajuda com acordeão em `/ajuda.php`.
23. `[INSERIR PRINT DE TELA: 23_configuracoes_empresa.png]` — Configurações da empresa e gestão de operadores em `/configuracoes.php`.
24. `[INSERIR PRINT DE TELA: 24_topbar_sidebar.png]` — Topbar limpa e sidebar retrátil em `inc/header.php`.
