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

- **Sistema:** MrStock ERP — Versão 2.2.0 (Build Homologada 2026)
- **Cliente Homologado:** Papelaria Real Ltda (Rua XV de Novembro, 250 - Centro, Sorocaba/SP)
- **Instituição:** Escola Técnica Estadual Fernando Prestes (ETEC Fernando Prestes)
- **Órgão Mantenedor:** Centro Paula Souza (CPS) / Governo do Estado de São Paulo
- **Eixo Tecnológico:** Informação e Comunicação
- **Orientador Acadêmico Oficial:** Prof. Vinicius

#### Equipe de Engenharia e Desenvolvimento (Alunos):
1. **Douglas Moraes:** Líder Técnico, Direção de Arquitetura de Software e Engenharia de Soluções.
2. **Nikolas:** Modelagem de Banco de Dados Relacional, Diagrama DER e Otimização SQL.
3. **Cesar:** Engenharia de Requisitos, Interface com o Cliente e Validação Comercial.
4. **Enzo:** Redação Técnica, Compilação Documental e Adequação às Normas ABNT/CPS.
5. **Sugahara:** Apresentador Oficial do Sistema e Demonstração de Navegação na Banca.

---

## SUMÁRIO EXECUTIVO

- [Termo de Responsabilidade e Sigilo Operacional](#termo-de-responsabilidade-e-sigilo-operacional)
- [Capítulo 1 — Primeiros Passos e Acesso ao Sistema](#capítulo-1--primeiros-passos-e-acesso-ao-sistema)
  - [1.1 Requisitos Mínimos de Funcionamento](#11-requisitos-mínimos-de-funcionamento)
  - [1.2 Procedimento de Login no Sistema](#12-procedimento-de-login-no-sistema)
  - [1.3 Recuperação de Senha de Acesso](#13-recuperação-de-senha-de-acesso)
  - [1.4 Boas Práticas de Segurança e Encerramento de Sessão](#14-boas-práticas-de-segurança-e-encerramento-de-sessão)
- [Capítulo 2 — Visão Geral e Navegação do Dashboard](#capítulo-2--visão-geral-e-navegação-do-dashboard)
  - [2.1 Indicadores Chave de Desempenho (KPIs do Dia)](#21-indicadores-chave-de-desempenho-kpis-do-dia)
  - [2.2 Painel de Alertas de Validade PEPS/FIFO (Janela de 30 Dias)](#22-painel-de-alertas-de-validade-pepsfifo-janela-de-30-dias)
  - [2.3 Gráfico de Tendência de Vendas e Acesso Rápido](#23-gráfico-de-tendência-de-vendas-e-acesso-rápido)
- [Capítulo 3 — Cadastros Fundamentais da Papelaria](#capítulo-3--cadastros-fundamentais-da-papelaria)
  - [3.1 Gestão do Catálogo de Produtos](#31-gestão-do-catálogo-de-produtos)
  - [3.2 Gestão de Lotes Físicos e Controle de Validades](#32-gestão-de-lotes-físicos-e-controle-de-validades)
  - [3.3 Cadastro e Gestão de Clientes](#33-cadastro-e-gestão-de-clientes)
  - [3.4 Cadastro e Gestão de Fornecedores Homologados](#34-cadastro-e-gestão-de-fornecedores-homologados)
- [Capítulo 4 — Operação de Frente de Caixa (PDV Ágil) — O Guia do Operador](#capítulo-4--operação-de-frente-de-caixa-pdv-ágil--o-guia-do-operador)
  - [4.1 Fluxo Operacional Completo de Venda](#41-fluxo-operacional-completo-de-venda)
  - [4.2 Tabela Oficial de Atalhos de Teclado do PDV](#42-tabela-oficial-de-atalhos-de-teclado-do-pdv)
  - [4.3 Formas de Pagamento e Cálculo de Troco](#43-formas-de-pagamento-e-cálculo-de-troco)
  - [4.4 Emissão da Simulação Acadêmica de Cupom Fiscal NFC-e](#44-emissão-da-simulação-acadêmica-de-cupom-fiscal-nfc-e)
  - [4.5 Trava de Segurança Contra Margem Negativa (Prejuízo)](#45-trava-de-segurança-contra-margem-negativa-prejuízo)
- [Capítulo 5 — Operações Pós-Venda, Reimpressão e Estorno](#capítulo-5--operações-pós-venda-reimpressão-e-estorno)
  - [5.1 Consulta ao Histórico de Vendas Realizadas](#51-consulta-ao-histórico-de-vendas-realizadas)
  - [5.2 Reimpressão de Cupom Fiscal](#52-reimpressão-de-cupom-fiscal)
  - [5.3 Procedimento Gerencial de Estorno e Devolução ao Estoque](#53-procedimento-gerencial-de-estorno-e-devolução-ao-estoque)
- [Capítulo 6 — Entrada de Mercadorias e Gestão de Compras](#capítulo-6--entrada-de-mercadorias-e-gestão-de-compras)
  - [6.1 Passo a Passo para Lançamento de Nota de Compra](#61-passo-a-passo-para-lançamento-de-nota-de-compra)
  - [6.2 Associação Obrigatória de Lote e Data de Validade](#62-associação-obrigatória-de-lote-e-data-de-validade)
- [Capítulo 7 — Relatórios Estratégicos e Análise Financeira (Guia do Gerente)](#capítulo-7--relatórios-estratégicos-e-análise-financeira-guia-do-gerente)
  - [7.1 Análise de Giro por Curva ABC (Princípio de Pareto 80/20)](#71-análise-de-giro-por-curva-abc-princípio-de-pareto-8020)
  - [7.2 Demonstrativo do Resultado do Exercício (DRE Gerencial)](#72-demonstrativo-do-resultado-do-exercício-dre-gerencial)
  - [7.3 Relatório de Giro de Estoque e Produtos Parados](#73-relatório-de-giro-de-estoque-e-produtos-parados)
  - [7.4 Trilha de Auditoria Forense e Logs do Sistema](#74-trilha-de-auditoria-forense-e-logs-do-sistema)
- [Capítulo 8 — Fechamento de Caixa Cego e Prestação de Contas](#capítulo-8--fechamento-de-caixa-cego-e-prestação-de-contas)
  - [8.1 O Conceito e a Importância da Conferência Cega](#81-o-conceito-e-a-importância-da-conferência-cega)
  - [8.2 Roteiro Prático de Fechamento de Turno](#82-roteiro-prático-de-fechamento-de-turno)
  - [8.3 Tratamento de Divergências (Sobras e Faltas de Caixa)](#83-tratamento-de-divergências-sobras-e-faltas-de-caixa)
- [Capítulo 9 — Solução de Dúvidas, FAQ Operacional e Suporte Técnico](#capítulo-9--solução-de-dúvidas-faq-operacional-e-suporte-técnico)
  - [9.1 As 5 Perguntas Mais Frequentes da Operação](#91-as-5-perguntas-mais-frequentes-da-operação)
  - [9.2 Protocolo de Contingência Offline (Modo Local XAMPP)](#92-protocolo-de-contingência-offline-modo-local-xampp)
  - [9.3 Canais de Atendimento e SLA de Suporte](#93-canais-de-atendimento-e-sla-de-suporte)
- [Glossário de Termos Técnicos e Comerciais](#glossário-de-termos-técnicos-e-comerciais)
- [Notas para Revisão Acadêmica (Enzo e Nikolas)](#notas-para-revisão-acadêmica-enzo-e-nikolas)

---

## TERMO DE RESPONSABILIDADE E SIGILO OPERACIONAL

O **MrStock ERP v2.2.0** é um instrumento de gestão comercial e controle patrimonial da **Papelaria Real Ltda**. Cada operador de caixa e administrador cadastrado recebe uma credencial individual intransferível (composta por e-mail institucional e senha privativa criptografada). 

Todas as ações executadas no sistema — incluindo abertura de caixa, registro de itens, cancelamentos, descontos concedidos, estornos e consultas a relatórios — são carimbadas digitalmente na base de dados com identificação unívoca de data, horário, operador e endereço IP. É expressamente proibido o compartilhamento de senhas entre colaboradores. Qualquer divergência de caixa ou operação indevida será de responsabilidade legal e funcional do titular da conta autenticada no momento da ocorrência.

---

# CAPÍTULO 1 — PRIMEIROS PASSOS E ACESSO AO SISTEMA

Este capítulo orienta o colaborador sobre como inicializar o MrStock ERP, verificar a compatibilidade de seu computador e realizar a autenticação segura no ambiente de trabalho.

[INSERIR PRINT DE TELA: login-tela-inicial.png]

### 1.1 Requisitos Mínimos de Funcionamento
O MrStock ERP foi concebido para ser extremamente leve e rápido, dispensando a instalação de softwares pesados na máquina do operador:
* **Computador:** Desktop ou notebook com processador dual-core (ou superior), 2 GB de memória RAM e resolução de tela mínima de 1024x768 pixels.
* **Navegador de Internet:** Google Chrome, Microsoft Edge, Mozilla Firefox ou Opera em versões atualizadas com suporte a JavaScript e cookies habilitados.
* **Periféricos Recomendados no Caixa:** Leitor óptico de código de barras (USB ou Bluetooth) padrão 1D/2D e impressora térmica não-fiscal de cupom (bobinas de 58mm ou 80mm).
* **Conexão:** Acesso à internet banda larga (mínimo 2 Mbps estável) para operação em nuvem, ou rede local para contingência offline.

### 1.2 Procedimento de Login no Sistema
Para entrar no sistema, siga rigorosamente os passos abaixo:
1. Abra o navegador de internet e digite o endereço oficial na nuvem: `https://mrstock.com.br/login.php` (ou o endereço local de contingência `http://localhost/MrStock/login.php`).
2. No campo **E-mail**, digite seu endereço de e-mail corporativo cadastrado (exemplo: `operador@papelariareal.com.br`).
3. No campo **Senha**, digite sua senha pessoal secreta.
4. Clique no botão sólido verde **Acessar Sistema**.
5. Caso as credenciais estejam corretas, o sistema inicializará a sessão segura e redirecionará automaticamente:
   - Administradores: para o **Dashboard Gerencial** (`dashboard.php`).
   - Caixas/Operadores: diretamente para a **Frente de Caixa (PDV)** (`vendas/pdv.php`).

> 💡 **Dica:** Você pode salvar a página de login na barra de favoritos do navegador (pressione `Ctrl + D`) para agilizar a abertura do sistema no início do seu turno de trabalho.

> ⚠️ **Atenção:** Nunca compartilhe sua senha com outro funcionário, mesmo que seja por poucos minutos. O sistema registra cada venda e estorno no seu nome na trilha de auditoria. Caso precise se ausentar do caixa, encerre sua sessão clicando no botão **Sair** no menu superior direito.

> ❌ **Erro comum:** O sistema exibe a mensagem *"Credenciais inválidas ou usuário inativo"*.  
> **Solução:** Verifique se a tecla `Caps Lock` (Fixa) do teclado está acionada por engano. Se o erro persistir, solicite ao Administrador que verifique se seu cadastro está ativo no menu de configurações.

### 1.3 Recuperação de Senha de Acesso
Caso tenha esquecido sua senha:
1. Na tela de login, clique no link **Esqueceu sua senha?**.
2. Digite seu e-mail corporativo cadastrado e clique em **Solicitar Redefinição**.
3. Uma notificação será enviada ao administrador do sistema para que uma nova senha temporária seja gerada de forma segura com criptografia BCrypt.

### 1.4 Boas Práticas de Segurança e Encerramento de Sessão
* **Bloqueio Automático:** Por diretriz de segurança bancária e comercial, a sessão será encerrada automaticamente após período prolongado de inatividade.
* **Desconexão Segura:** Ao encerrar o expediente ou trocar de operador no caixa, clique sempre no seu nome no canto superior direito e selecione a opção **Sair do Sistema** (`logout.php`).

---

# CAPÍTULO 2 — VISÃO GERAL E NAVEGAÇÃO DO DASHBOARD (`dashboard.php`)

O Dashboard é o painel de instrumentos executivo da Papelaria Real. Ele consolida em tempo real o ritmo de vendas da loja, a saúde financeira diária e os alertas preventivos de estoque.

[INSERIR PRINT DE TELA: dashboard-visao-geral.png]

### 2.1 Indicadores Chave de Desempenho (KPIs do Dia)
No topo do painel, quatro cartões estratégicos sintetizam o dia da loja:
1. **Faturamento do Dia (R$):** Exibe a soma de todas as vendas concluídas hoje na loja física. Permite à gerência saber instantaneamente se a meta diária foi atingida.
2. **Vendas Realizadas:** Quantidade absoluta de clientes atendidos e compras finalizadas no caixa no dia corrente.
3. **Ticket Médio (R$):** Valor médio gasto por cliente em cada compra (`Faturamento Total ÷ Número de Vendas`). Um ticket médio em elevação sinaliza que a equipe de balcão está obtendo sucesso em vendas agregadas (ex: oferecer borracha e apontador a quem compra lápis).
4. **Estoque Crítico (Itens em Alerta):** Número de produtos cujo saldo físico atingiu ou caiu abaixo da margem de segurança configurada. Requer reposição imediata junto aos fornecedores homologados.

### 2.2 Painel de Alertas de Validade PEPS/FIFO (Janela de 30 Dias)
A Papelaria Real comercializa dezenas de itens que possuem shelf-life restrito e degradam com o tempo (tintas líquidas, colas bastão, colas brancas, canetas em gel e fitas adesivas). O MrStock ERP varre permanentemente todos os lotes cadastrados e apresenta um painel de alerta cromático:

| Indicador Visual | Status do Lote | Prazo de Validade | Ação Operacional Recomendada |
| :---: | :---: | :---: | :--- |
| 🟢 **Verde** | Regular | Superior a 30 dias | Manter no fluxo normal de vendas das prateleiras. |
| 🟡 **Amarelo** | Atenção Crítica | Vencimento em até 30 dias | **Ação Imediata:** Posicionar na gôndola frontal ou criar queima de estoque/promoção relâmpago. |
| 🔴 **Vermelho** | Vencido / Expirado | Prazo de validade esgotado | **Bloqueio de Venda:** Recolher o produto físico da área de vendas e acionar o fornecedor para troca ou descarte. |

> ⚠️ **Atenção:** Vender produtos com prazo de validade vencido é infração grave prevista no Código de Defesa do Consumidor (art. 18, § 6º, I). Consulte este painel todas as manhãs antes da abertura das portas.

### 2.3 Gráfico de Tendência de Vendas e Acesso Rápido
- **Gráfico de Evolução (Últimos 7 Dias):** Mostra a curva de faturamento diário da semana, facilitando a visualização dos dias de maior movimento (sextas-feiras e sábados).
- **Últimas Transações:** Tabela na parte inferior que lista as últimas 5 vendas processadas no balcão, permitindo conferência rápida de valores recebidos pelo operador.

---

# CAPÍTULO 3 — CADASTROS FUNDAMENTAIS DA PAPELARIA

Para que o estoque funcione de forma automatizada e o caixa opere com velocidade máxima, os cadastros da loja devem ser mantidos completos e padronizados.

[INSERIR PRINT DE TELA: produtos-cadastro-formulario.png]

### 3.1 Gestão do Catálogo de Produtos (`produtos/`)
O cadastro de produtos reúne os dados comerciais de cada artigo comercializado na loja.

#### Passo a Passo para Cadastrar um Novo Produto:
1. No menu lateral, clique em **Estoque & Produtos** e selecione **Cadastrar Novo Produto** (`produtos/novo.php`).
2. Aponte o leitor de código de barras para a embalagem ou digite manualmente o código no campo **Código de Barras (EAN-13)**.
3. Preencha a **Descrição do Produto** de forma completa e clara (Exemplo correto: `Caderno Espiral Universitário 10 Matérias 200 Fls Tilibra Happy`).
4. Selecione a **Família Funcional** correspondente no menu suspenso (veja a relação das 10 famílias abaixo).
5. Defina a **Unidade de Medida** (UN para unidade, CX para caixa, PCT para pacote, RL para rolo).
6. Informe o **Preço de Venda** praticado no balcão.
7. Defina o **Estoque Mínimo de Alerta** (quantidade mínima que deve haver na gaveta antes de soar o alerta de reposição).
8. Indique a **Localização Física** na loja (exemplo: `Corredor 1, Prateleira B`).
9. Clique no botão sólido verde **Salvar Produto**.

#### Tabela de Campos Obrigatórios — Cadastro de Produtos:
| Campo | Tipo / Formato | Obrigatório? | Finalidade no Sistema |
| :--- | :--- | :---: | :--- |
| **Código de Barras** | Numérico (EAN-13) | Sim | Identificação rápida no leitor óptico do PDV. |
| **Nome / Descrição** | Texto (até 150 caracteres) | Sim | Descrição impressa no cupom e visível no caixa. |
| **Família Funcional** | Seleção (1 das 10 opções) | Sim | Agrupamento correto para Curva ABC e filtros. |
| **Unidade de Medida** | Sigla (UN, CX, PCT, RL) | Sim | Padronização de estoque e fracionamento. |
| **Preço de Venda (R$)** | Decimal (ex: 24,90) | Sim | Valor cobrado do consumidor final no PDV. |
| **Preço de Custo (R$)** | Decimal (ex: 14,50) | Sim | Base para cálculo de lucro (visível apenas ao Administrador). |
| **Estoque Mínimo** | Inteiro (ex: 5) | Sim | Gatilho para emissão de alertas no Dashboard. |
| **Localização Física** | Texto livre | Não | Auxilia atendentes novatos a acharem o item na loja. |

#### As 10 Famílias Funcionais da Papelaria Real:
O catálogo do MrStock ERP adota 10 Famílias Funcionais especializadas:
1. `Cadernos & Blocos` (cadernos universitários, brochuras, blocos autoadesivos, refis, agendas).
2. `Canetas & Marcadores` (esferográficas, hidrográficas, marcadores de texto, canetas em gel, permanentes).
3. `Lápis & Apontadores` (lápis grafite, caixas de lápis de cor, lapiseiras técnicas, grafites, apontadores).
4. `Borrachas & Correção` (borrachas brancas escolares, ponteiras, fitas corretivas, corretivos líquidos).
5. `Colas & Fitas Adesivas` (colas brancas escolares, colas bastão, colas de silicone, fitas crepe e transparentes).
6. `Papéis & Folhas` (resmas sulfite A4/A3, cartolinas, papel cartão, papel vegetal, papel crepom, folhas com pauta).
7. `Pastas & Organização` (pastas catálogo, pastas sanfonadas, pastas aba elástico, arquivos de mesa).
8. `Corte & Medição` (tesouras escolares e profissionais, estiletes, réguas plásticas e de aço, transferidores).
9. `Tintas & Pintura` (tintas guache escolares, tintas acrílicas para artesanato, godês, pincéis chatos e redondos).
10. `Grampeadores & Fixação` (grampeadores manuais, caixas de grampos 26/6, perfuradores, clipes de papel).

> 💡 **Dica:** Nunca cadastre produtos com nomes genéricos como apenas "Caneta" ou "Caderno". Sempre inclua a marca, modelo e cor (ex: `Caneta Esferográfica BIC Cristal 1.0mm Azul`). Isso evita confusão no balcão e divergências no inventário.

---

### 3.2 Gestão de Lotes Físicos e Controle de Validades (`lotes/`)
O módulo de lotes é o pilar que garante a saúde financeira e a conformidade legal da Papelaria Real, aplicando a metodologia **PEPS/FIFO**.

[INSERIR PRINT DE TELA: lotes-listagem-validade.png]

#### Passo a Passo para Cadastrar um Lote Manualmente:
1. No menu lateral, acesse **Lotes & Validades** (`lotes/index.php`) e clique em **Novo Lote**.
2. Selecione o produto correspondente.
3. Digite o **Código do Lote** (conforme impresso na embalagem do fabricante, ex: `LOT-2026-TNB`).
4. Selecione o **Fornecedor** de onde a mercadoria foi adquirida.
5. Digite a **Data de Fabricação** e a **Data de Validade** impressas na caixa ou tubo.
6. Informe a **Quantidade Recebida** e o **Custo Unitário de Compra** deste lote específico.
7. Clique em **Salvar Lote**.

#### Tabela de Campos Obrigatórios — Cadastro de Lotes:
| Campo | Tipo / Formato | Obrigatório? | Finalidade no Sistema |
| :--- | :--- | :---: | :--- |
| **Produto Vinculado** | Seleção de Catálogo | Sim | Associa a remessa física ao item do sistema. |
| **Número / Código do Lote** | Alfanumérico | Sim | Rastreabilidade do fabricante na embalagem. |
| **Fornecedor** | Seleção de Parceiro | Sim | Identifica a distribuidora responsável pela remessa. |
| **Data de Fabricação** | Data (`DD/MM/AAAA`) | Sim | Histórico de shelf-life e tempo de armazenagem. |
| **Data de Validade** | Data (`DD/MM/AAAA`) | Sim | Critério prioritário de consumo automático no PDV. |
| **Quantidade Inicial** | Numérico inteiro | Sim | Volume total recebido da distribuidora. |
| **Custo Unitário (R$)** | Decimal (ex: 8,50) | Sim | Base para o cálculo do Lucro Bruto Real na venda. |

> ⚠️ **Atenção:** Se um mesmo produto tiver lotes com custos diferentes (exemplo: comprou um lote a R$ 10,00 e outro lote a R$ 12,00), **nunca misture em um único lote**. Cadastre cada remessa em seu lote próprio para que o sistema consiga aplicar o custo exato quando o item for vendido.

---

### 3.3 Cadastro e Gestão de Clientes (`clientes/`)
O cadastro de clientes permite manter histórico de consumo, fidelização e identificação na emissão do cupom fiscal.

#### Passo a Passo:
1. Acesse o menu **Clientes** e clique em **Cadastrar Cliente** (`clientes/novo.php`).
2. Digite o **Nome Completo** ou Razão Social.
3. Preencha o **CPF ou CNPJ** (o sistema valida automaticamente os dígitos verificadores).
4. Informe o **Celular com DDD**.
5. No campo **CEP**, digite os 8 números do CEP do cliente: o sistema busca o endereço automaticamente na base dos Correios via API ViaCEP e preenche Rua, Bairro, Cidade e Estado. Digite apenas o número da residência e complemento.
6. Clique em **Salvar Cliente**.

> 💡 **Dica:** Na tabela de clientes, clique no **botão circular verde do WhatsApp** ao lado do número do cliente para abrir imediatamente uma conversa direta no WhatsApp Web sem precisar salvar o contato na agenda do aparelho.

---

### 3.4 Cadastro e Gestão de Fornecedores Homologados (`fornecedores/`)
Permite registrar as distribuidoras parceiras da papelaria (ex: Tilibra, BIC, Faber-Castell, Chamex, Acrilex).

#### Tabela de Campos Obrigatórios — Fornecedores:
| Campo | Tipo / Formato | Obrigatório? | Finalidade no Sistema |
| :--- | :--- | :---: | :--- |
| **Razão Social** | Texto | Sim | Nome empresarial que consta na Nota Fiscal. |
| **Nome Fantasia** | Texto | Sim | Nome popular pelo qual a marca é conhecida. |
| **CNPJ** | 14 dígitos formatados | Sim | Identificação fiscal e emissão de pedidos. |
| **Telefone / WhatsApp** | Numérico com DDD | Sim | Canal ágil de reposição de estoque. |
| **E-mail de Pedidos** | E-mail corporativo | Sim | Envio formal de ordens de compra. |

---

# CAPÍTULO 4 — OPERAÇÃO DE FRENTE DE CAIXA (PDV ÁGIL) — O GUIA DO OPERADOR

O módulo de Frente de Caixa (`vendas/pdv.php`) é a tela onde o operador de caixa passa a maior parte do seu turno. Ela foi construída com foco em agilidade, ergonomia e eliminação de cliques desnecessários.

[INSERIR PRINT DE TELA: pdv-frente-de-caixa.png]

### 4.1 Fluxo Operacional Completo de Venda
Siga este roteiro de 7 etapas simples para atender cada cliente no balcão:

1. **Localizar o Produto:**
   - Com o cliente no balcão, aponte o leitor de código de barras para o produto (ou pressione `F2` e digite o código ou nome do item).
   - O item aparecerá destacado na tela com quantidade padrão `1`.
2. **Adicionar Item ao Cupom:**
   - Pressione `Enter` para adicionar o produto à lista de compras do lado direito da tela.
   - Para lançar múltiplos itens do mesmo produto (ex: 5 canetas azuis), digite `5*` antes do código ou ajuste o campo quantidade e tecle `Enter`.
3. **Conferir Totais:**
   - O painel exibirá automaticamente o **Subtotal**, eventuais descontos autorizados e o **Total a Pagar** em números grandes e de fácil visualização pelo cliente.
4. **Identificação do Consumidor (Opcional):**
   - Pergunte amigavelmente ao cliente: *"Deseja incluir CPF na nota fiscal?"*.
   - Se o cliente desejar, pressione `F8`, digite o CPF (apenas números) e tecle `Enter`. Se o cliente recusar, basta prosseguir.
5. **Abrir a Tela de Pagamento:**
   - Pressione a tecla de atalho **`F4`** (ou clique no grande botão sólido verde **Finalizar Venda**).
6. **Registrar o Pagamento:**
   - Escolha a forma de pagamento informada pelo cliente (Dinheiro, Pix, Cartão de Débito, Cartão de Crédito ou Múltiplos).
   - Se for em **Dinheiro**, digite o valor que o cliente entregou em mãos: o sistema calcula na hora e exibe o **Troco** em destaque.
7. **Concluir a Venda e Emitir a NFC-e:**
   - Clique em **Confirmar Venda** (ou tecle `Enter`).
   - A venda é gravada, as unidades são baixadas automaticamente do lote mais antigo (PEPS/FIFO) e uma janela apresenta o Cupom Fiscal Simulado na tela.
   - Pressione **`Ctrl + P`** para disparar a impressão na impressora térmica do balcão e entregue o cupom ao cliente com um agradecimento.

---

### 4.2 Tabela Oficial de Atalhos de Teclado do PDV
Para que você não precise tirar as mãos do teclado e ganhe segundos preciosos em cada atendimento, memorize estes atalhos:

| Tecla / Atalho | Função Operacional no PDV | Comportamento Exato no Sistema |
| :---: | :--- | :--- |
| **`F2`** | Buscar Produto / Ativar Leitor | Move o cursor diretamente para o campo de busca de código de barras ou nome. |
| **`Enter`** | Inserir Item no Cupom | Confirma a inclusão do produto e quantidade na lista de compras. |
| **`F4`** | Abrir Pagamento / Fechar Venda | Abre a janela modal de escolha da forma de pagamento e cálculo de troco. |
| **`F7`** | Limpar / Cancelar Cupom Aberto | Cancela a venda inteira em andamento antes de receber o pagamento (requer confirmação). |
| **`F8`** | Inserir CPF do Consumidor | Foca imediatamente no campo de CPF para registro do cliente. |
| **`Esc`** | Fechar Janelas / Voltar | Fecha qualquer janela de confirmação ou modal aberta e retorna ao cupom. |
| **`Tab`** | Alternar Campos de Pagamento | Navega ágilmente entre as opções de Dinheiro, Pix, Cartão e Valor Recebido. |
| **`Ctrl + P`** | Imprimir Cupom Fiscal | Dispara o comando nativo de impressão da NFC-e em bobina térmica ou papel A4. |

> 💡 **Dica:** Treine operar o caixa utilizando exclusivamente o teclado numérico e as teclas de atalho (`F2`, `F4`, `Enter`). Operadores treinados atendem clientes até três vezes mais rápido do que usando o mouse.

---

### 4.3 Formas de Pagamento e Cálculo de Troco
O MrStock ERP aceita as seguintes modalidades comerciais:
* **Dinheiro:** Ao digitar o valor entregue pelo cliente (ex: compra deu R$ 37,00 e o cliente deu uma nota de R$ 50,00), o sistema calcula automaticamente `Troco: R$ 13,00` em fonte destacada.
* **Pix:** O sistema exibe na tela o QR Code oficial e a chave Pix da Papelaria Real. Aguarde a confirmação de recebimento no aplicativo do banco da loja antes de clicar em confirmar.
* **Cartão de Débito / Crédito:** Insira o valor na maquininha física de cartão do balcão. Após a emissão do comprovante impresso pela maquininha ("Aprovada"), confirme a transação no sistema.
* **Múltiplos Pagamentos:** Permite fracionar o total (ex: R$ 50,00 em Dinheiro e o restante de R$ 35,00 no Cartão de Débito).

---

### 4.4 Emissão da Simulação Acadêmica de Cupom Fiscal NFC-e
Ao término de cada venda, o sistema gera a **Simulação Acadêmica da Nota Fiscal de Consumidor Eletrônica (NFC-e)**:
* **Chave de Acesso Oficial (44 Dígitos):** Gerada segundo a fórmula padrão da Receita Estadual da SEFAZ SP:
  `[UF: 35] [AAMM: Ano/Mês] [CNPJ Papelaria Real] [Mod: 65] [Série: 001] [Número NFe] [Tipo: 1] [Código Aleatório] [DV]`.
* **Protocolo de Autorização:** Código de autenticação simulado para validação didática.
* **Carga Tributária Estimada:** Discrimina o percentual de impostos aproximados incidentes sobre a compra (em estrito cumprimento à Lei Federal nº 12.741/2012 — *De Olho no Imposto*).
* **QR Code Vetorial:** Código bidimensional escaneável por câmeras de celular para demonstração prática.
* **Formatação de Saída:** O cupom pode ser impresso em bobinas térmicas de 58mm, 80mm ou em meia folha A4.

> 💡 **Nota Didática:** A simulação de NFC-e foi chancelada pelo orientador Prof. Vinicius como solução de engenharia para o TCC, simulando com precisão de 100% o layout e a matemática fiscal exigidos pelo Fisco Paulista sem incorrer em custos com certificados digitais de pessoas jurídicas reais.

---

### 4.5 Trava de Segurança Contra Margem Negativa (Prejuízo)
Se um operador tentar aplicar um desconto excessivo na venda que faça o preço final ficar abaixo do custo que a Papelaria Real pagou pelo lote do produto, o MrStock ERP acionará a **Trava de Margem Negativa**:
- O sistema emitirá um aviso visual informando: *"Desconto não permitido: O valor de venda (R$ X) é inferior ao custo de aquisição (R$ Y). Margem de lucro negativa."*
- Essa trava impede que a loja tenha prejuízos operacionais causados por erros de digitação ou concessão indevida de descontos no balcão.

---

# CAPÍTULO 5 — OPERAÇÕES PÓS-VENDA, REIMPRESSÃO E ESTORNO (`vendas/`)

Este capítulo detalha como localizar transações já finalizadas, reimprimir comprovantes e executar cancelamentos controlados.

[INSERIR PRINT DE TELA: vendas-historico-listagem.png]

### 5.1 Consulta ao Histórico de Vendas Realizadas
1. No menu superior ou lateral, clique em **Vendas** e selecione **Histórico de Vendas** (`vendas/index.php`).
2. Utilize os filtros no topo da tela para refinar sua pesquisa:
   - Filtro por **Data Inicial e Data Final** (exemplo: ver apenas as vendas de hoje).
   - Filtro por **Operador de Caixa** responsável.
   - Filtro por **Forma de Pagamento** (Dinheiro, Pix ou Cartão).
3. A tabela exibirá: Número da Venda, Horário, Cliente, Total da Venda, Forma de Pagamento e Status (Concluída ou Estornada).

### 5.2 Reimpressão de Cupom Fiscal
Se a bobina de papel da impressora tiver acabado durante a emissão ou se o cliente retornar à loja solicitando uma segunda via de seu comprovante:
1. Localize a venda no **Histórico de Vendas**.
2. Na coluna de ações, clique no botão azul com o ícone de impressora **Reimprimir Cupom**.
3. A janela modal da NFC-e será aberta com todos os dados idênticos aos da emissão original.
4. Pressione `Ctrl + P` e imprima a segunda via.

### 5.3 Procedimento Gerencial de Estorno e Devolução ao Estoque
O estorno de venda é uma operação de exceção (permitida apenas a usuários com perfil de Administrador/Gerente), utilizada em casos de desistência imediata do cliente ou troca de mercadoria.

#### Passo a Passo para Estornar uma Venda:
1. Faça login com conta de Administrador.
2. Acesse o **Histórico de Vendas** e localize o registro da venda a ser cancelada.
3. Clique no botão sólido vermelho **Estornar Venda**.
4. Uma tela de confirmação exigirá a justificativa do cancelamento (exemplo: *"Cliente desistiu da compra antes de retirar a mercadoria"*).
5. Digite o motivo e clique em **Confirmar Estorno**.

#### O que o Sistema Faz Automaticamente ao Estornar:
- O status da venda muda imediatamente para **Cancelada / Estornada**.
- **Retorno Físico ao Estoque:** O sistema repõe automaticamente as unidades vendidas nos exatos lotes de onde elas haviam saído, mantendo o controle PEPS/FIFO intacto.
- **Registro no Log Forense:** A ação é gravada na tabela `logs_auditoria` com data, horário, operador que solicitou e a justificativa preenchida.

> ⚠️ **Atenção:** O estorno é uma operação definitiva e não pode ser revertido. Certifique-se de que o dinheiro físico foi devolvido ao cliente ou que a transação no cartão foi cancelada na maquininha antes de confirmar no sistema.

---

# CAPÍTULO 6 — ENTRADA DE MERCADORIAS E GESTÃO DE COMPRAS (`compras/`)

A entrada de mercadorias é a porta de entrada de novos produtos e novos lotes no MrStock ERP. Uma conferência bem-feita evita furos no inventário e garante o controle de validade dos produtos.

[INSERIR PRINT DE TELA: compras-entrada-mercadorias.png]

### 6.1 Passo a Passo para Lançamento de Nota de Compra
1. Acesse o menu **Compras** e clique em **Nova Entrada de Mercadoria** (`compras/nova.php`).
2. Selecione o **Fornecedor** emitente da Nota Fiscal de Compra.
3. No campo **Número da Nota Fiscal**, digite o número do documento fiscal recebido.
4. Adicione os itens comprados:
   - Selecione o produto no catálogo.
   - Informe a **Quantidade de Unidades** compradas.
   - Digite o **Custo Unitário de Compra** (valor líquido que constou na nota por unidade).
5. Clique em **Adicionar Item**. Repita o procedimento para todos os produtos da nota.
6. Confira o valor total da nota no sistema com o total da nota de papel.
7. Clique no botão sólido verde **Confirmar Entrada de Mercadorias**.

### 6.2 Associação Obrigatória de Lote e Data de Validade
Para produtos das famílias de químicos, colas e tintas, uma janela solicitará os dados de rastreio:
- **Código do Lote do Fabricante** (gravado na caixa ou carimbado no frasco).
- **Data de Validade** (mês e ano de expiração).
- Ao confirmar, o estoque é alimentado imediatamente e o novo lote entra na fila prioritária de consumo do PDV.

> 💡 **Dica:** Sempre confira fisicamente a data de validade impressa nos tubos e frascos antes de guardá-los no estoque. Se o fornecedor entregou um lote com menos de 60 dias para o vencimento, recuse o recebimento ou solicite a troca antes de dar entrada no sistema.

---

# CAPÍTULO 7 — RELATÓRIOS ESTRATÉGICOS E ANÁLISE FINANCEIRA (GUIA DO GERENTE)

Os relatórios do MrStock ERP transformam os dados brutos de vendas em inteligência comercial para a proprietária da Papelaria Real tomar decisões com base em números.

[INSERIR PRINT DE TELA: relatorios-curva-abc.png]

### 7.1 Análise de Giro por Curva ABC (Princípio de Pareto 80/20)
A Curva ABC agrupa os produtos da papelaria em três faixas de importância financeira sobre o faturamento global da loja:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       DISTRIBUIÇÃO DA CURVA ABC                             │
├────────┬─────────────────┬─────────────────┬────────────────────────────────┤
│ Classe │ % do Faturamento│ % dos Produtos  │ Estratégia Comercial           │
├────────┼─────────────────┼─────────────────┼────────────────────────────────┤
│ **A**  │ **~ 80%**       │ **~ 20%**       │ **Vital:** Nunca pode faltar!  │
│ **B**  │ **~ 15%**       │ **~ 30%**       │ **Intermediário:** Repor regular│
│ **C**  │ **~ 5%**        │ **~ 50%**       │ **Cauda Longa:** Cuidado!      │
└────────┴─────────────────┴─────────────────┴────────────────────────────────┘
```

- **Classe A (Produtos Estrela):** Representam 80% do dinheiro que entra no caixa da Papelaria Real, embora correspondam a apenas 20% do catálogo (exemplo: resmas de sulfite Chamex A4, cadernos universitários 10 matérias e canetas esferográficas azuis e pretas). **Regra de Ouro:** O estoque de produtos da Classe A deve ser monitorado diariamente; a falta desses itens representa perda direta e imediata de vendas.
- **Classe B (Produtos de Apoio):** Representam 15% do faturamento da loja (ex: lápis de cor 12 cores, pastas sanfonadas, tesouras escolares). Requerem compras semanais ou quinzenais programadas.
- **Classe C (Produtos de Baixo Giro):** Representam apenas 5% da receita, ocupando cerca de metade do espaço físico da loja (ex: tintas a óleo profissionais, réguas técnicas sofisticadas, compassos caros). **Regra de Ouro:** Compre em lotes mínimos para não imobilizar dinheiro parado na prateleira.

---

### 7.2 Demonstrativo do Resultado do Exercício (DRE Gerencial)
O DRE consolida o resultado econômico da Papelaria Real em determinado período (mês, trimestre ou ano), demonstrando se a operação deu lucro ou prejuízo:

```
   ESTRUTURA DO DRE GERENCIAL — PAPELARIA REAL LTDA
   (+) Receita Bruta de Vendas ...................... R$ 45.800,00
   (-) Devoluções e Vendas Estornadas ............... R$    650,00
   (=) Receita Líquida de Vendas .................... R$ 45.150,00
   (-) Custo das Mercadorias Vendidas (CMV Real) .... R$ 24.832,50  (Custo dos lotes PEPS)
   (=) LUCRO BRUTO OPERACIONAL ...................... R$ 20.317,50
   ================================================================
   MARGEM BRUTA PERCENTUAL (%) ......................        45,00 %
```

- **Como Interpretar o Lucro Bruto:** No exemplo acima, a cada R$ 100,00 que entram na loja, sobram R$ 45,00 brutos para pagar as contas fixas (aluguel, salários, energia) e gerar o lucro líquido da proprietária.
- **Acurácia Máxima com PEPS:** Como o MrStock ERP utiliza o custo exato do lote físico que saiu na venda, o valor do CMV e do Lucro Bruto não é uma estimativa fantasiosa, mas sim um dado financeiro auditável.

---

### 7.3 Relatório de Giro de Estoque e Produtos Parados
Lista os produtos que estão sem nenhuma venda registrada há mais de 60 ou 90 dias. Permite à gerência identificar mercadorias "encalhadas" e planejar kits promocionais antes que percam o valor de mercado ou vençam.

### 7.4 Trilha de Auditoria Forense e Logs do Sistema
Permite ao Administrador auditar todas as ações operacionais da loja com filtros por data e usuário. Revela quem deu desconto, quem alterou preços, quem estornou cupons e quem alterou cadastros, assegurando integridade e transparência na empresa.

---

# CAPÍTULO 8 — FECHAMENTO DE CAIXA CEGO E PRESTAÇÃO DE CONTAS

O fechamento de caixa é o momento de conciliar os valores físicos da gaveta com os registros do sistema.

[INSERIR PRINT DE TELA: caixa-fechamento-cego.png]

### 8.1 O Conceito e a Importância da Conferência Cega
Tradicionalmente, sistemas antigos mostram na tela quanto dinheiro o operador "deveria" ter na gaveta antes de fechar o caixa. Esse modelo facilita acomodação e omissão de pequenas faltas ou sobras.

O **MrStock ERP adota o Fechamento Cego:**
- O sistema **NÃO** exibe ao operador o total esperado em dinheiro.
- O operador é instruído a abrir a gaveta, contar todas as notas e moedas físicas presentes, e digitar no sistema exatamente o valor contado em espécie.
- Somente após a confirmação da contagem é que o sistema compara o valor digitado com o total calculado nas vendas e emite o demonstrativo de conciliação.

### 8.2 Roteiro Prático de Fechamento de Turno
Siga estes 5 passos no encerramento do expediente:
1. No menu superior da Frente de Caixa, clique no botão **Encerrar Turno / Fechar Caixa**.
2. Abra a gaveta física e organize o dinheiro por cédulas (R$ 100, R$ 50, R$ 20, R$ 10, R$ 5, R$ 2) e moedas.
3. Conte o valor total em dinheiro vivo existente na gaveta.
4. Digite o valor apurado no campo **Valor Apurado em Dinheiro (R$)**.
5. Clique em **Confirmar Fechamento de Caixa**.
6. O sistema emitirá o **Relatório de Fechamento de Turno**, dividindo as vendas do dia por:
   - Total em Dinheiro (apurado vs esperado).
   - Total em Pix (conferido via extrato bancário).
   - Total em Cartões de Débito e Crédito (conferido pelas filipetas da maquininha).
7. Assine o relatório impresso e guarde-o junto aos valores no malote da gerência.

### 8.3 Tratamento de Divergências (Sobras e Faltas de Caixa)
Ao fechar o caixa, três cenários podem ocorrer:
* **Caixa Correto (Divergência Zero):** O valor contado bateu exatamente com o sistema.
* **Sobra de Caixa (Valor Positivo):** Há mais dinheiro na gaveta do que o registrado. Costuma ocorrer quando o operador esquece de registrar alguma venda de valor pequeno ou recebe valor a mais do cliente por engano.
* **Falta de Caixa (Valor Negativo):** Há menos dinheiro na gaveta do que o esperado. Ocorre geralmente por erro ao passar troco ao cliente ou venda não recebida.

> ⚠️ **Atenção:** Em caso de divergência superior a R$ 5,00 (falta ou sobra), chame imediatamente o Administrador da loja antes de fechar o malote. Nunca tente "compensar" a diferença retirando ou colocando dinheiro pessoal na gaveta.

---

# CAPÍTULO 9 — SOLUÇÃO DE DÚVIDAS, FAQ OPERACIONAL E SUPORTE TÉCNICO

Este capítulo esclarece as dúvidas mais comuns dos colaboradores da Papelaria Real e detalha o que fazer em caso de instabilidades.

[INSERIR PRINT DE TELA: ajuda-faq-acordeao.png]

### 9.1 As 5 Perguntas Mais Frequentes da Operação (FAQ Oficial)

#### 1. Como funciona a simulação acadêmica de NFC-e com QR Code no PDV?
A simulação acadêmica de NFC-e desenvolvida no MrStock ERP reproduz com exatidão técnica todas as exigências de layout e regras de negócio da Secretaria da Fazenda de São Paulo (SEFAZ SP), incluindo a formação algorítmica da Chave de Acesso de 44 dígitos com dígito verificador, número de protocolo simulado, cálculo estimado da carga tributária conforme a Lei Federal nº 12.741/2012 (*De Olho no Imposto*) e impressão de QR Code vetorial escaneável para bobinas térmicas de 80mm e 58mm. O recurso foi chancelado formalmente pelo orientador Prof. Vinicius como solução didática de engenharia para o TCC da ETEC Fernando Prestes, garantindo validação completa pela banca examinadora sem incorrer em custos com certificados digitais A1 corporativos ou burocracias de credenciamento em ambiente de produção da SEFAZ real.

#### 2. Como o sistema opera em caso de queda de internet (Modo Offline / Local XAMPP)?
O MrStock ERP foi arquitetado com alta disponibilidade operacional para que o atendimento de balcão da Papelaria Real nunca seja interrompido por oscilações do provedor de internet. Em condições normais, a equipe acessa o sistema na nuvem com criptografia SSL em `https://mrstock.com.br/`. Caso a internet externa caia durante o expediente, a papelaria conta com uma instância espelho idêntica configurada no servidor XAMPP local do computador do caixa: basta abrir uma nova aba no navegador e digitar `http://localhost/MrStock/`. As vendas continuam sendo realizadas normalmente na Frente de Caixa local com baixa de estoque em tempo real. Quando a conexão de internet for restabelecida, a sincronização unifica os bancos de dados, garantindo que nenhum cliente fique sem atendimento e nenhuma venda seja perdida.

#### 3. Qual o procedimento correto para estorno de venda e devolução ao estoque?
O procedimento formal de estorno deve ser executado exclusivamente por um colaborador com perfil de Administrador, por questões de segurança financeira. O gestor acessa o menu *Vendas > Histórico de Vendas*, localiza a transação pelo número do cupom ou horário, confere os itens e clica no botão sólido vermelho *Estornar Venda*. Uma janela de confirmação exige o preenchimento obrigatório da justificativa do cancelamento (ex: cliente desistiu da compra antes de retirar a mercadoria). Ao confirmar, o sistema altera o status da venda para cancelada, realiza a devolução física e automática das unidades aos seus exatos lotes de origem (preservando o controle PEPS/FIFO) e grava um registro imutável na trilha de auditoria forense do sistema com carimbo de data, horário, operador responsável e IP da máquina.

#### 4. Por que os produtos são organizados em 10 Famílias Funcionais em vez de categorias genéricas?
A classificação do catálogo em 10 Famílias Funcionais específicas da Papelaria Real (Cadernos & Blocos, Canetas & Marcadores, Lápis & Apontadores, Borrachas & Correção, Colas & Fitas Adesivas, Papéis & Folhas, Pastas & Organização, Corte & Medição, Tintas & Pintura, Grampeadores & Fixação) foi adotada para refletir fielmente a rotina operacional do comércio varejista físico. Categorias macro genéricas (como apenas "Escolar" ou "Escritório") causam distorções graves em papelarias, pois um mesmo caderno pode atender tanto a um estudante quanto a um escritório de advocacia. A separação por famílias funcionais de produtos garante acurácia científica no cálculo da Curva ABC, organiza os relatórios de reposição de compras, facilita o inventário físico nas prateleiras e permite ao atendente localizar rapidamente qualquer item no balcão de vendas.

#### 5. Como solicitar redefinição de senha ou gerenciar novos operadores de caixa?
O gerenciamento de credenciais e operadores de caixa é restrito ao perfil de Administrador da Papelaria Real. Para cadastrar um novo funcionário ou redefinir senhas, o administrador acessa o menu *Configurações > Gestão de Operadores*, clica em *Adicionar Novo Usuário*, informa o nome completo, e-mail corporativo, define o perfil de acesso adequado (Caixa com permissões restritas de balcão ou Administrador com acesso total) e cadastra uma senha inicial, que é automaticamente criptografada pelo algoritmo seguro BCrypt (Cost 12). Caso um operador esqueça sua senha, o administrador pode acessar a mesma tela e gerar uma nova senha temporária com um clique, orientando o colaborador a alterá-la no primeiro acesso subsequente para preservar o sigilo pessoal.

---

### 9.2 Protocolo de Contingência Offline (Modo Local XAMPP)
Em caso de interrupção da internet banda larga na loja física, siga este procedimento de emergência:

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

### 9.3 Canais de Atendimento e SLA de Suporte
Caso enfrente dúvidas operacionais não solucionadas neste manual ou identifique comportamentos anômalos no sistema:
- **Central de Ajuda Interna:** Disponível 24/7 na barra de navegação superior (`ajuda.php`).
- **Contato de Suporte Técnico da Equipe:** Contato via e-mail e canal de suporte dedicado.
- **SLA de Atendimento:** Resposta e triagem operacional em até **2 horas úteis** em horário comercial (segunda a sábado, das 08h às 18h).

---

# GLOSSÁRIO DE TERMOS TÉCNICOS E COMERCIAIS

- **PEPS / FIFO (Primeiro que Entra, Primeiro que Sai):** Princípio contábil e logístico onde os produtos adquiridos primeiro (ou com validade mais próxima) são os primeiros a serem vendidos no caixa.
- **NFC-e (Nota Fiscal de Consumidor Eletrônica):** Documento fiscal eletrônico emitido no varejo físico para registrar compras do consumidor final.
- **EAN-13:** Padrão internacional de código de barras composto por 13 dígitos numéricos presente nas embalagens dos produtos.
- **Curva ABC:** Metodologia de gestão de estoque baseada na regra de Pareto (80/20) que classifica itens em classes A (vitais), B (intermediários) e C (baixo giro).
- **DRE (Demonstrativo do Resultado do Exercício):** Relatório financeiro contábil que confronta receitas e custos para evidenciar se a loja obteve lucro bruto ou prejuízo.
- **Markup:** Índice multiplicador aplicado sobre o custo de compra do produto para formar o preço de venda e cobrir despesas e margem de lucro.
- **Custo Médio Ponderado:** Média aritmética dos custos de aquisição ponderada pelas quantidades compradas em cada lote.
- **RBAC (Role-Based Access Control):** Controle de acesso baseado em papéis que restringe permissões no sistema de acordo com o cargo (Administrador vs Caixa).
- **Token CSRF:** Código criptográfico de uso único inserido nos formulários para garantir que a requisição de venda ou exclusão partiu legitimamente do operador logado.
- **Lote:** Remessa física de determinado produto fabricada em um mesmo ciclo, compartilhando a mesma data de validade e custo de compra.
- **Ticket Médio:** Valor médio faturado em cada atendimento (`Faturamento Total ÷ Quantidade de Vendas`).
- **Estoque Mínimo:** Quantidade mínima estipulada de segurança que um produto deve manter na prateleira para não romper o estoque antes da chegada de um novo pedido.

---

## NOTAS PARA REVISÃO ACADÊMICA (ENZO E NIKOLAS)

Esta seção lista os pontos de checagem documental e os prints de tela reais que devem ser capturados e inseridos na diagramação final do TCC:

1. `[INSERIR PRINT DE TELA: login-tela-inicial.png]` — Capturar a tela de login limpa com o formulário centralizado e o rodapé institucional.
2. `[INSERIR PRINT DE TELA: dashboard-visao-geral.png]` — Capturar o Dashboard com dados simulados, mostrando os 4 cards de KPIs e a tabela com badges coloridos de validade (verde, amarelo e vermelho).
3. `[INSERIR PRINT DE TELA: produtos-cadastro-formulario.png]` — Capturar a tela de cadastro de produto aberta, destacando a seleção de uma das 10 famílias funcionais.
4. `[INSERIR PRINT DE TELA: lotes-listagem-validade.png]` — Capturar a listagem de lotes mostrando os alertas de validade de 30 dias.
5. `[INSERIR PRINT DE TELA: pdv-frente-de-caixa.png]` — Capturar o PDV com itens inseridos no cupom da Papelaria Real, o total destacado e o modal de NFC-e aberto com o QR Code.
6. `[INSERIR PRINT DE TELA: vendas-historico-listagem.png]` — Capturar a listagem de histórico de vendas do dia com o botão vermelho de estorno e botão azul de reimpressão.
7. `[INSERIR PRINT DE TELA: compras-entrada-mercadorias.png]` — Capturar a tela de entrada de notas fiscais de fornecedor.
8. `[INSERIR PRINT DE TELA: relatorios-curva-abc.png]` — Capturar o gráfico ou tabela da Curva ABC dividida nas classes A, B e C.
9. `[INSERIR PRINT DE TELA: caixa-fechamento-cego.png]` — Capturar a tela de fechamento de caixa cego com o campo de contagem física em branco.
10. `[INSERIR PRINT DE TELA: ajuda-faq-acordeao.png]` — Capturar a central de ajuda com uma das perguntas da FAQ expandida no acordeão.
