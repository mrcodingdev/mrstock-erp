# PRD DE CONTEXTO TÉCNICO, ARQUITETURAL E OPERACIONAL: MRSTOCK ERP v2.2.0
## Documento Mestre de Alinhamento para Criação do Manual do Usuário (TCC ETEC Fernando Prestes)

---

### 📌 META-INSTRUÇÃO PARA O CLAUDE (PROMPT ENGINEER)
**Prezado Claude:**
Você está atuando neste momento exclusivamente como **Engenheiro de Prompt Sênior (Prompt Engineer)**. 

O desenvolvedor **Douglas Moraes** (Líder Técnico e Arquiteto do projeto) está fornecendo a você este documento de especificação técnica e funcional (PRD) que contém **100% de todo o contexto, arquitetura, regras de negócio, dados do cliente real, telas e diretrizes do MrStock ERP**.

**Sua Missão como Prompt Engineer:**
1. Leia e analise minuciosamente todas as seções deste documento.
2. Formule e devolva para o Douglas um **Master Prompt de Engenharia Reversa (Prompt Otimizado de Alta Precisão)**.
3. O prompt que você gerar será entregue por Douglas ao assistente de desenvolvimento **Antigravity** (que atuará como **Manual Engineer**), para que o Antigravity escreva o **Manual do Usuário Oficial e Completo do MrStock ERP**.
4. O Manual que o Antigravity gerará servirá como a base estrutural para os colegas de equipe de Douglas (**Enzo**, responsável pela documentação acadêmica do TCC, e **Nikolas**, responsável pelo banco de dados) finalizarem e incorporarem no relatório final entregue à banca examinadora da **ETEC Fernando Prestes** e à proprietária da **Papelaria Real Ltda**.

Por favor, elabore o prompt garantindo que o Manual do Usuário seja:
- Extremamente didático, com linguagem clara tanto para um operador de caixa leigo quanto para o gerente da loja.
- Estruturado passo a passo, tela por tela, com tabelas de atalhos, caixas de atenção/dicas e tratamento de erros comuns.
- Alinhado com as normas formais de manuais técnicos de software do Centro Paula Souza / ETEC.

---

# PARTE I: CONTEXTO GERAL E ACADÊMICO DO TCC

## 1. Identificação do Projeto
- **Nome do Sistema:** MrStock ERP v2.2.0
- **Subtítulo Oficial:** Sistema Integrado de Gestão Comercial, Controle de Estoque com Validades PEPS/FIFO e Frente de Caixa (PDV) Ágil.
- **Instituição de Ensino:** ETEC Fernando Prestes — Sorocaba/SP (Centro Estadual de Educação Tecnológica Paula Souza - CPS).
- **Curso:** Habilitação Técnica Profissional em Desenvolvimento de Sistemas (Eixo Tecnológico de Informação e Comunicação).
- **Ano Letivo / Ciclo:** 2026 — Trabalho de Conclusão de Curso (TCC).
- **Orientador Oficial:** Prof. Vinicius.

## 2. Equipe de Desenvolvimento e Matriz de Responsabilidades
O projeto foi desenvolvido por uma equipe de 5 alunos, com funções bem delimitadas:
1. **Douglas Moraes (Líder Técnico & Engenharia de Software):** Direção de arquitetura, codificação full-stack em PHP 8.2 nativo, implementação de segurança defensiva, esteira de agentes autônomos, infraestrutura na Hostinger e sincronização de ambientes.
2. **Nikolas (Modelagem de Dados & DBA):** Diagrama de Entidade-Relacionamento (DER), modelagem das 14 tabelas no MySQL (`mrstock_db`), integridade referencial, chaves estrangeiras e otimização de queries de vendas.
3. **Cesar (Engenharia de Requisitos & Interface com Cliente):** Levantamento de regras de negócio, contato de campo com a Papelaria Real, definição de requisitos funcionais e validação da usabilidade no comércio varejista.
4. **Enzo (Documentação Acadêmica & Normas ABNT):** Estruturação dos relatórios escritos, compilação dos manuais de usuário e instalação, fundamentação teórica e adequação às normas da ABNT/CPS.
5. **Sugahara (Navegador & Apresentador do Sistema):** Responsável por demonstrar a navegação prática do software ao vivo no navegador durante a apresentação da banca examinadora (treinado operacionalmente por Douglas).

---

# PARTE II: O CLIENTE REAL — ESTUDO DE CASO DA PAPELARIA REAL LTDA

## 1. Perfil da Empresa
- **Razão Social:** Papelaria Real Ltda.
- **Endereço:** Rua XV de Novembro, 250 - Centro, Sorocaba/SP (região comercial central com alto fluxo de pedestres, estudantes e escritórios).
- **Porte / Regime:** Comércio varejista de pequeno/médio porte (Simples Nacional).
- **Mix de Produtos:** Materiais escolares (cadernos, mochilas, estojos), material para escritório (pastas, formulários, papéis A4/A3), artigos de escrita (canetas técnicas, marcadores permanentes), arte e pintura (tintas guache, acrílicas, telas, pincéis) e produtos químicos de fixação (colas brancas, colas bastão, fitas adesivas, corretivos líquidos).

## 2. Diagnóstico das Dores e Problemas Reais do Negócio
Antes da implantação do MrStock ERP, a Papelaria Real operava com controles manuais em cadernos, anotações de balcão e planilhas eletrônicas desconexas, enfrentando graves gargalos operacionais:

1. **Perda Financeira por Vencimento Silencioso de Produtos Químicos e Líquidos:**
   - Produtos como colas líquidas, colas bastão, corretivos líquidos, tintas para artesanato e canetas em gel possuem prazo de validade determinado (shelf-life).
   - Sem rastreamento de lotes, produtos novos eram empilhados na frente dos antigos nas prateleiras. Os itens do fundo da gaveta venciam sem que a equipe percebesse, resultando em descarte forçado, perda total do capital investido e risco de autuação pelo PROCON por exposição de itens vencidos.

2. **Distorção de Lucratividade por Falta de Controle PEPS/FIFO (First-In, First-Out):**
   - Com a inflação e variações de preços dos fornecedores, a papelaria comprava, por exemplo, 100 cadernos em janeiro por R$ 12,00 cada e outros 100 cadernos em fevereiro por R$ 15,00 cada.
   - Ao vender no caixa por R$ 22,00, a proprietária não sabia qual lote estava sendo consumido. O cálculo de lucro era impreciso, muitas vezes mascarando prejuízos ou gerando precificação equivocada.

3. **Lentidão Crítica no Balcão de Atendimento em Horários de Pico:**
   - Nos períodos de volta às aulas (janeiro/fevereiro) e nos horários de pico (início da manhã e final da tarde), formavam-se filas extensas no caixa.
   - Operadores precisavam consultar preços manualmente em listas impressas ou digitar descrições longas, gerando frustração nos clientes e desistência de compras.

4. **Fechamento de Caixa Vulnerável a Erros Humanos e Divergências:**
   - O encerramento do dia financeiro era realizado em calculadoras de mesa e papel. Faltavam relatórios automatizados de divisão de valores por forma de pagamento (Dinheiro, Pix, Cartão de Débito, Cartão de Crédito), dificultando a conciliação bancária e facilitando pequenas perdas diárias.

5. **Inexistência de Visão Estratégica de Giro de Estoque (Curva ABC):**
   - A gerência não sabia com precisão científica quais eram os 20% dos produtos que respondiam por 80% do faturamento da loja (Curva A). Como consequência, faltava capital para repor produtos de altíssimo giro enquanto o dinheiro ficava imobilizado em itens de baixíssima saída (Curva C).

---

# PARTE III: ARQUITETURA TÉCNICA E INFRAESTRUTURA

## 1. Stack Tecnológica
- **Backend:** PHP 8.2 Nativo puro, orientado a objetos e estruturado em arquitetura modular.
  - **Decisão Arquitetural Acadêmica:** O grupo optou deliberadamente por PHP nativo com PDO em vez de frameworks modernos (como Laravel) na entrega do TCC para demonstrar à banca examinadora da ETEC o domínio pleno dos fundamentos de programação web, segurança de requisições, manipulação de sessões, tratamento de erros e consultas SQL puras. A migração para o framework Laravel foi posicionada formalmente no plano pedagógico como o *Roadmap de Evolução Arquitetural (Versão 3.0)*.
- **Banco de Dados:** MySQL (`mrstock_db`) estruturado em 14 tabelas relacionais em Terceira Forma Normal (3FN), com índices estratégicos em chaves estrangeiras, código EAN-13 e colunas de busca textual.
- **Segurança de Dados:** 100% das operações SQL utilizam PDO Prepared Statements parametrizados (bloqueio total contra SQL Injection). Senhas criptografadas com algoritmo BCrypt (Cost 12 via `password_hash`). Validação estrita de tokens anti-CSRF em requisições de alteração de estado. Sanitização de saída HTML com `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` contra XSS.
- **Frontend & Design System:** HTML5 semântico, Bootstrap 5 customizado (MrStock Design System), JavaScript ES6 nativo (modular, sem dependências pesadas de frameworks front-end), CSS3 puro estruturado em variáveis e tokens visuais.

## 2. Princípios Estritos de Design System e UI/UX
- **Padronização Global de Botões Sólidos:** Todos os botões de ação (`.btn-primary`, `.btn-success`, `.btn-danger`, `.btn-warning`, `.btn-dark`, `.btn-secondary`) possuem preenchimento de cor sólida fixa e texto/ícone em branco. É terminantemente proibido o uso de botões transparentes com borda colorida (`btn-outline-*`) para ações principais. No hover, os botões apenas escurecem suavemente com sombra elegante.
- **Paleta de Cores Institucional:** 
  - Verde Floresta Corporativo (Primário): `#1a4231`
  - Verde Médio Real (Secundário): `#284936`
  - Verde Esmeralda Neon (Acento e Destaques): `#6ae49b`
  - Fundo de Aplicação Suave: `#e8f3ee` e `#f8fafc`
  - Superfícies de Cards: `#ffffff` com bordas sutis em `#cbd5e1`
- **Animações Institucionais Suaves:** Todos os cards, tabelas, KPIs e painéis possuem a animação fluida `@keyframes mrStockSlideInLeft` com aceleração cúbica natural (`cubic-bezier(0.16, 1, 0.3, 1)`), transmitindo aspecto de software desktop profissional.
- **Topbar Limpa:** A barra de navegação superior exibe exclusivamente o título limpo da tela atual (ex: `PDV - Ponto de Venda`, `Estoque & Produtos`, `Dashboard Gerencial`), eliminando badges redundantes ou prefixos poluídos.
- **Botão de WhatsApp Circular Padronizado:** Nas tabelas de clientes e fornecedores, o botão de acionamento do WhatsApp é um botão circular verde oficial (`.btn-whatsapp`, 22x22px, border-radius 50%, ícone branco `<i class="fab fa-whatsapp"></i>`) posicionado ao lado do número em texto limpo.
- **UI Rápida e Sem Fotos Pesadas de Produtos:** Por decisão de consenso do grupo e alinhamento com a banca da ETEC, o catálogo NÃO utiliza fotos pesadas individuais de produtos. O sistema exibe o logotipo oficial da Papelaria Real e ícones funcionais por família de produto, garantindo carregamento instantâneo em conexões lentas de balcão.

## 3. Topologia de Ambientes e Nuvem (O Pentágono Sagrado)
O projeto opera sob uma esteira de 5 vértices de sincronização contínua:
1. **Ambiente Local de Desenvolvimento (XAMPP):** `C:\xampp\htdocs\MrStock\` (ambiente onde o código é implementado e testado com contingência operacional para operação offline da papelaria).
2. **Repositório Central no GitHub:** `mrcodingdev/mrstock-erp` (versionamento semântico contínuo na branch `main`).
3. **Ambiente de Produção Oficial na Nuvem (Hostinger):** `https://mrstock.com.br/` (hospedagem em nuvem com certificado SSL/HTTPS obrigatório, proteção de cabeçalhos de segurança A+, regras defensivas de `.htaccess` e deploy contínuo automático via Git Webhook disparado a cada `git push origin main`).
4. **Espelho Mandatório no Google Drive:** `G:\Meu Drive\TCC_MrStock\` (cópia síncrona de segurança para backup acadêmico de toda a equipe).
5. **Segundo Cérebro (Megabrain Obsidian):** Base de conhecimento técnico e registro de decisões arquiteturais (ADRs) em `04_Base_de_Conhecimento_e_Obsidian/07_Megabrain_Obsidian/`.

## 4. Ecossistema Expandido (Automação n8n e Cockpit Pixel Art)
- **Hub de Automações n8n Cloud:** Conectado na nuvem (`https://dgzin.app.n8n.cloud/`) com 3 fluxos ativos:
  1. `W6OPYIpqKMAxprS8`: Monitor diário de estoque mínimo e validades PEPS/FIFO (Cron das 08h).
  2. `S5t6jewbvpRLhvll`: Webhook de telemetria de subagentes e quality gates (`/webhook/antigravity-telemetry`).
  3. `0z3WNCATmmpayi4k`: Webhook de auditoria de eventos de PDV e alerta anti-fraude de estornos (`/webhook/mrstock-pdv`).
- **Antigravity Office 2D Pixel Art:** Servidor Express na porta `4444` (`tools/antigravity-office/`) que renderiza um cockpit retrô em Canvas 2D exibindo os agentes em tempo real com HUD e badge `N8N HUB ONLINE`.

---

# PARTE IV: AS 6 REGRAS DE NEGÓCIO MANDATÓRIAS DO MRSTOCK ERP

O Manual do Usuário DEVE detalhar e enfatizar as seguintes regras de governança comercial implementadas no código:

### Regra 1: Gestão de Lotes por Princípio PEPS/FIFO (Primeiro que Entra, Primeiro que Sai)
- Todo produto cadastrado possui seus estoques fracionados em lotes físicos.
- Cada lote registra: Código/Número do Lote, Fornecedor de Origem, Data de Fabricação, Data de Validade, Quantidade Comprada, Quantidade Atual e Custo Unitário de Aquisição.
- **Consumo Automático no PDV:** Ao realizar uma venda no caixa, o sistema baixa automaticamente as unidades do lote mais antigo (menor data de validade/entrada). O operador não precisa escolher manualmente o lote no caixa, prevenindo erros operacionais e garantindo giro perfeito do estoque.
- **Painel Visual de Validades (Alerta 30 Dias):** O Dashboard e a tela de lotes destacam visualmente com badges amarelos e vermelhos qualquer lote que esteja a 30 dias ou menos do vencimento, ou que já esteja expirado, recomendando queima de estoque ou devolução ao fornecedor.

### Regra 2: Cálculo de Lucro Bruto Real e Trava de Margem Negativa
- O sistema calcula o **Lucro Bruto Real** de cada venda cruzando o valor pago pelo cliente com o custo exato do lote consumido:
  $$\text{Lucro Bruto} = \text{Valor da Venda} - \text{Custo do Lote Físico Consumido}$$
- **Trava Financeira do PDV:** O sistema possui uma trava de segurança que bloqueia ou alerta o operador caso um desconto concedido reduza o preço de venda para um valor inferior ao custo de aquisição daquele produto, prevenindo vendas com prejuízo involuntário.

### Regra 3: Classificação por 10 Famílias Funcionais Especializadas
Para evitar a desorganização de categorias genéricas (como apenas "Escolar" ou "Escritório"), o catálogo do MrStock ERP adota 10 Famílias Funcionais específicas da rotina da Papelaria Real:
1. `Cadernos & Blocos` (cadernos espirais, universitários, blocos adesivos, agendas, refis de fichário).
2. `Canetas & Marcadores` (canetas esferográficas, ponta fina, marcadores de texto, canetas em gel, marcadores permanentes).
3. `Lápis & Apontadores` (lápis grafite pretos, caixas de lápis de cor, lapiseiras técnicas, grafites, apontadores com depósito).
4. `Borrachas & Correção` (borrachas brancas escolares, ponteiras, borrachas técnicas, fitas corretivas, corretivos líquidos).
5. `Colas & Fitas Adesivas` (colas brancas líquidas, colas bastão, colas de silicone, fitas crepe, fitas adesivas transparentes, dupla-face).
6. `Papéis & Folhas` (resmas de papel sulfite A4 75g, cartolinas, papel cartão, papel vegetal, papel crepom, folhas pautadas).
7. `Pastas & Organização` (pastas catálogo, pastas sanfonadas, pastas aba elástico, organizadores de mesa, canaletas).
8. `Corte & Medição` (tesouras escolares sem ponta, estiletes profissionais, réguas metálicas e plásticas, esquadros, transferidores).
9. `Tintas & Pintura` (tintas guache escolares, tintas acrílicas para artesanato, godês, pincéis escolares e artísticos).
10. `Grampeadores & Fixação` (grampeadores de mesa, caixas de grampos 26/6, perfuradores de papel, clipes niquelados, tachinhas).

### Regra 4: Simulação Acadêmica de NFC-e com QR Code Homologada
- O módulo de emissão fiscal do PDV gera uma **Simulação Acadêmica Completa de Cupom Fiscal Eletrônico (NFC-e)**:
  - Chave de Acesso estruturada no padrão nacional de 44 dígitos numéricos:
    `[UF: 35] [AAMM] [CNPJ Papelaria Real] [Mod: 65] [Série: 001] [Número NFe] [Tipo: 1] [Código Numérico Aleatório] [Dígito Verificador]`.
  - Número de Protocolo de Autorização simulado no padrão SEFAZ.
  - Cálculo de Carga Tributária Estimada aproximada conforme a Lei Federal nº 12.741/2012 (*De Olho no Imposto*), aplicando alíquotas médias sobre o valor total da venda.
  - Geração de QR Code vetorial escaneável para fins didáticos de conferência pela banca examinadora.
  - Formatação pronta para impressão em impressoras térmicas não-fiscais (bobinas de 80mm e 58mm) e em folhas A4.
  - *Nota:* O orientador Prof. Vinicius chancelou oficialmente essa abordagem de simulação acadêmica para o TCC, eliminando custos de certificados digitais A1 e credenciamento na SEFAZ real.

### Regra 5: Controle de Acesso Baseado em Perfis (RBAC - Role-Based Access Control)
O sistema divide os usuários em dois níveis estritos de privilégio:
1. **Administrador (Gerência / Proprietária):** Acesso irrestrito a todos os menus: Dashboard de faturamento, cadastro e edição de produtos, histórico de custos de compra, margem de lucro, DRE gerencial, gestão de compras com fornecedores, Curva ABC, relatórios de auditoria e cadastro de novos operadores.
2. **Caixa / Operador de Balcão:** Acesso focado exclusivamente na rotina de vendas: Frente de Caixa (PDV), Histórico e reimpressão das vendas do dia, e Consulta Rápida de Preços. O perfil Caixa é **terminantemente bloqueado de visualizar preços de custo de compra, relatórios de lucro líquido e configurações gerais da empresa**, preservando o sigilo financeiro da papelaria.

### Regra 6: Auditoria Forense Transacional e Fechamento Cego de Caixa
- Todas as operações críticas (estorno de vendas, cancelamento de itens, exclusão de registros, alteração de senhas) gravam automaticamente uma trilha de auditoria na tabela `logs_auditoria` com data, hora, IP do operador e descrição detalhada do evento.
- O fechamento de caixa opera sob o modelo de **Conferência Cega**: ao encerrar o turno, o operador conta as cédulas e moedas físicas da gaveta e digita o valor apurado. O sistema compara esse valor com o montante registrado nas vendas do dia e aponta automaticamente eventuais sobras ou faltas de caixa, gerando relatório de prestação de contas.

---

# PARTE V: MAPA COMPLETO DE MÓDULOS E ROTAS DO SISTEMA
(Este mapeamento é o esqueleto que deve guiar cada capítulo do Manual do Usuário)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                             MRSTOCK ERP v2.2.0                              │
│                    MAPA DE MÓDULOS & ROTAS DE NAVEGAÇÃO                     │
├────────────────────────┬────────────────────────────────────────────────────┤
│ Módulo                 │ Arquivos & Telas Envolvidas                        │
├────────────────────────┼────────────────────────────────────────────────────┤
│ 1. Acesso & Segurança  │ login.php, logout.php, recuperar_senha.php         │
│ 2. Painel Gerencial    │ dashboard.php                                      │
│ 3. Estoque & Produtos  │ produtos/index.php, novo.php, editar.php           │
│ 4. Categorias          │ categorias/index.php, nova.php, editar.php         │
│ 5. Gestão de Lotes     │ lotes/index.php, novo.php, editar.php              │
│ 6. Clientes            │ clientes/index.php, novo.php, editar.php           │
│ 7. Fornecedores        │ fornecedores/index.php, novo.php, editar.php       │
│ 8. Compras / Entradas  │ compras/index.php, nova.php, visualizar.php        │
│ 9. Frente de Caixa     │ vendas/pdv.php (PDV Ágil & Cupom Fiscal NFC-e)     │
│ 10. Histórico Vendas   │ vendas/index.php, detalhes.php, estorno.php        │
│ 11. Relatórios & DRE   │ relatorios/index.php, curva_abc.php, dre.php, logs │
│ 12. Configurações      │ configuracoes.php (Dados da Loja, Operadores RBAC) │
│ 13. Central de Ajuda   │ ajuda.php (Manuais Operacionais & FAQ 5 Dúvidas)   │
│ 14. Termos & Políticas │ privacidade.php, termos.php, 404.php               │
└────────────────────────┴────────────────────────────────────────────────────┘
```

## Detalhamento Tela a Tela para o Manual:

### 1. Módulo de Autenticação (`login.php`)
- **Objetivo:** Garantir acesso seguro e autenticado aos funcionários da papelaria.
- **Campos:** E-mail corporativo e Senha.
- **Recursos de Segurança:** Verificação de hash BCrypt, regeneração de ID de sessão contra session fixation, bloqueio por força bruta temporário após tentativas consecutivas inválidas.
- **Links Úteis:** Link para Termos de Uso e Política de Privacidade (conformidade LGPD).
- **Recuperação de Senha:** Fluxo para redefinição via e-mail corporativo ou intervenção do Administrador.

### 2. Dashboard Gerencial (`dashboard.php`)
- **Objetivo:** Apresentar em uma única tela o termômetro em tempo real do negócio.
- **Cards de Métricas (KPIs):**
  1. *Faturamento do Dia:* Total bruto faturado hoje no PDV em R$.
  2. *Vendas Realizadas:* Quantidade de transações concluídas no dia.
  3. *Ticket Médio:* Faturamento dividido pelo número de vendas.
  4. *Estoque Crítico:* Quantidade de produtos que atingiram ou estão abaixo do estoque mínimo.
- **Painel de Alertas de Validade (PEPS/FIFO):** Tabela que lista produtos com lotes a vencer em menos de 30 dias, destacando data de validade, quantidade em risco e lote.
- **Gráficos e Tabelas:** Gráfico de evolução de vendas dos últimos 7 dias e lista das 5 últimas vendas do caixa.

### 3. Gestão de Produtos (`produtos/index.php`)
- **Objetivo:** Manter o cadastro detalhado de todo o catálogo físico da loja.
- **Formulário de Cadastro/Edição (`novo.php` / `editar.php`):**
  - Nome do Produto (descrição completa, ex: `Caderno Espiral Universitário 10 Matérias 200 Folhas Tilibra`).
  - Código de Barras EAN-13 (suporte a leitor de código de barras ou geração automática).
  - Família Funcional / Categoria (seleção entre as 10 famílias).
  - Unidade de Medida (UN, CX, PCT, RL, FD).
  - Preço de Venda (em R$).
  - Preço de Custo de Referência (em R$ - visível apenas para Admin).
  - Estoque Mínimo de Alerta (dispara o alerta do dashboard quando o saldo físico atinge esse número).
  - Localização no Estoque / Prateleira (ex: `Corredor 2 - Prateleira B`).
- **Listagem e Filtros:** Busca textual rápida por nome, código EAN ou família, exibição de badge de status de estoque (Em Estoque, Baixo Estoque, Esgotado) e ações de edição/inativação.

### 4. Gestão de Lotes & Validades (`lotes/index.php`)
- **Objetivo:** Rastrear a data de fabricação, shelf-life e custo de cada remessa de produtos químicos/líquidos.
- **Campos:** Código do Lote (gerado pelo fornecedor ou interno), Produto vinculado, Fornecedor associado, Quantidade Inicial, Quantidade Atual, Data de Fabricação, Data de Validade e Custo Unitário.
- **Indicadores de Status:**
  - `🟢 Regular:` Validade superior a 30 dias.
  - `🟡 Atenção (Próximo do Vencimento):` Vence em até 30 dias (recomenda ação promocional de queima de estoque).
  - `🔴 Vencido:` Produto expirado (bloqueado para venda no PDV para evitar infrações sanitárias e de consumo).

### 5. Clientes & Fornecedores (`clientes/` e `fornecedores/`)
- **Cadastro de Clientes:** Nome completo, CPF/CNPJ, Telefone/Celular, E-mail, CEP (com preenchimento automático de logradouro, bairro e cidade via integração com API ViaCEP) e histórico de compras realizadas no PDV.
- **Cadastro de Fornecedores:** Razão Social, Nome Fantasia, CNPJ, Contato Comercial, Telefone e Botão de WhatsApp oficial para pedidos rápidos de reposição.
- **Ação Rápida WhatsApp:** Botão verde circular padronizado que abre diretamente o WhatsApp Web / Desktop com o fornecedor ou cliente sem precisar salvar o contato no celular.

### 6. Operação de Compras & Entrada de Mercadorias (`compras/`)
- **Objetivo:** Registrar a aquisição de mercadorias junto às distribuidoras e alimentar automaticamente os saldos de estoque e a criação de novos lotes.
- **Fluxo Operacional:**
  1. O usuário seleciona o Fornecedor.
  2. Adiciona os produtos comprados, informando quantidade e valor unitário da nota.
  3. Para itens com validade, informa a data de fabricação, validade e código do lote da nota fiscal.
  4. Ao clicar em **Confirmar Entrada**, o sistema atualiza o estoque físico, cria os lotes correspondentes e grava o registro financeiro na base.

### 7. Frente de Caixa / PDV Ágil (`vendas/pdv.php`)
- **Objetivo:** Ponto focal do operador de caixa para atendimento rápido, sem travamentos e com emissão de cupom fiscal em segundos.
- **Interface e Elementos de Tela:**
  - Campo de Busca Rápida: Aceita leitura de leitor óptico de código de barras USB/Bluetooth ou digitação de nome/código.
  - Grade/Lista de Itens do Cupom: Exibe item, descrição, quantidade, preço unitário, subtotal e botão de exclusão de item.
  - Painel de Totais em Destaque: Subtotal, Desconto em R$, Acréscimo e Total a Pagar em fontes grandes de alto contraste.
  - Campo de Identificação do Consumidor: CPF opcional (o consumidor pode escolher incluir ou não seu CPF para créditos na nota).
- **Formas de Pagamento Homologadas:**
  1. *Dinheiro:* Campo para digitar o valor entregue pelo cliente com cálculo automático e em destaque do **Troco**.
  2. *Pix:* Exibição de QR Code estático ou chave Pix da Papelaria Real com confirmação de recebimento.
  3. *Cartão de Débito:* Confirmação de maquininha de cartão.
  4. *Cartão de Crédito:* Opção de registro à vista ou parcelado.
  5. *Múltiplos Pagamentos:* Divisão da compra em mais de uma forma de pagamento (ex: R$ 50,00 no dinheiro + R$ 30,00 no Pix).
- **Finalização e Impressão de Cupom (Simulação NFC-e):**
  - Ao concluir a venda, uma janela modal estilizada apresenta a Simulação Acadêmica do Cupom Fiscal Eletrônico NFC-e com Chave de Acesso de 44 dígitos, protocolo SEFAZ, QR Code vetorial e discriminação de tributos (Lei 12.741/2012).
  - Botão de impressão com 1 clique (`Ctrl + P` nativo) formatado para impressoras térmicas (80mm/58mm) e impressoras convencionais A4.

### 8. Histórico de Vendas, Reimpressão & Estorno (`vendas/index.php`)
- **Histórico:** Tabela que lista todas as vendas realizadas por período, com filtro por data, operador de caixa e forma de pagamento.
- **Reimpressão de Cupom:** Qualquer cupom já emitido pode ser reaberto e reimpresso a qualquer momento.
- **Procedimento de Estorno / Cancelamento:**
  - Em caso de desistência do cliente ou erro operacional, o Administrador pode estornar a venda.
  - **Ação Automática de Estoque:** As unidades dos produtos vendidos retornam automaticamente para os seus respectivos lotes de origem.
  - **Trilha de Auditoria:** O estorno é registrado com carimbo de data, hora, motivo justificado e usuário responsável, prevenindo fraudes.

### 9. Relatórios Gerenciais & Curva ABC (`relatorios/`)
- **Relatório de Curva ABC:** Classifica todos os itens da Papelaria Real em 3 categorias baseadas na metodologia de Pareto (80/20):
  - *Classe A:* Os produtos mais vitais que representam ~80% do faturamento da loja (itens prioritários que nunca podem faltar no estoque, ex: cadernos em janeiro, sulfites e canetas pretas/azuis).
  - *Classe B:* Produtos de relevância intermediária (~15% do faturamento).
  - *Classe C:* Produtos de baixo giro (~5% do faturamento, que demandam cautela na reposição para não imobilizar capital).
- **DRE Gerencial (Demonstrativo do Resultado do Exercício):**
  - Receita Bruta de Vendas
  - (-) Devoluções e Estornos
  - (=) Receita Líquida
  - (-) Custo das Mercadorias Vendidas (CMV Real baseado nos lotes)
  - (=) Lucro Bruto Operacional
  - Margem Bruta Percentual (%)
- **Relatório de Giro de Estoque & Produtos Parados:** Identifica itens sem movimentação há mais de 60 ou 90 dias.
- **Logs Forenses de Auditoria:** Listagem cronológica de todas as ações executadas no sistema para prestação de contas.

### 10. Configurações do Sistema (`configuracoes.php`)
- **Dados da Empresa:** Razão Social, Nome Fantasia, CNPJ, Inscrição Estadual, Endereço, Telefone, Chave Pix padrão para o PDV e alíquota de tributação para a simulação da NFC-e.
- **Gestão de Usuários & Operadores:** Criação de novos operadores, definição de perfil (Administrador vs Caixa), redefinição de senhas e ativação/desativação de acesso.

### 11. Central de Ajuda & FAQ Operacional (`ajuda.php`)
- **Manuais Rápidos em Acordeão:** Guias condensados de como abrir o caixa, realizar uma venda e estornar cupom.
- **FAQ Operacional (As 5 Dúvidas Reais dos Operadores):**
  1. *Como funciona a simulação acadêmica de NFC-e com QR Code no PDV?*
  2. *Como o sistema opera em caso de queda de internet (Modo Offline / Local XAMPP)?*
  3. *Qual o procedimento correto para estorno de venda e devolução ao estoque?*
  4. *Por que os produtos são organizados em 10 Famílias Funcionais em vez de categorias genéricas?*
  5. *Como solicitar redefinição de senha ou gerenciar novos operadores de caixa?*
- **SLA de Atendimento:** Canal de suporte com promessa operacional de resposta em até 2 horas úteis.

---

# PARTE VI: TABELA DE ATALHOS DE TECLADO DO PDV (FRENTE DE CAIXA)
Para que o operador de caixa não dependa do mouse e mantenha velocidade máxima de atendimento no balcão:

| Tecla / Atalho | Função Operacional no PDV | Comportamento no Sistema |
| :--- | :--- | :--- |
| **`F2`** | Buscar Produto / Ativar Leitor | Move o cursor diretamente para o campo de busca de código de barras ou nome do produto. |
| **`Enter`** | Inserir Item no Cupom | Adiciona a quantidade digitada e o produto selecionado na lista de compras. |
| **`F4`** | Abrir Pagamento / Fechar Venda | Abre a janela modal de seleção da forma de pagamento e cálculo de troco. |
| **`F7`** | Limpar / Cancelar Cupom Aberto | Cancela a venda em andamento antes de receber o pagamento (requer confirmação). |
| **`F8`** | Inserir CPF do Consumidor | Foca no campo de CPF para inclusão opcional de dados do cliente na nota. |
| **`Esc`** | Fechar Janelas / Voltar | Fecha qualquer janela de confirmação ou modal aberta e retorna ao cupom. |
| **`Tab`** | Alternar Campos de Pagamento | Navega ágilmente entre os campos de Dinheiro, Pix, Cartão e Valor Recebido. |
| **`Ctrl + P`** | Imprimir Cupom Fiscal | Dispara a impressão imediata da NFC-e térmica ou comprovante A4. |

---

# PARTE VII: PROTOCOLO DE CONTINGÊNCIA (MODO OFFLINE XAMPP)
Um dos maiores riscos no varejo é a queda da conexão de internet banda larga no meio do expediente comercial. O MrStock ERP foi projetado com uma arquitetura de alta disponibilidade que deve constar em destaque no Manual do Usuário:

1. **Operação Normal (Nuvem Hostinger):** Operadores acessam `https://mrstock.com.br/` com SSL ativo de qualquer computador, tablet ou smartphone conectado à internet.
2. **Queda da Conexão Externa (Contingência Imediata):**
   - Caso o provedor de internet local fique indisponível, a papelaria mantém uma instância espelho do MrStock configurada no XAMPP local do computador do caixa.
   - O operador simplesmente abre uma nova aba no navegador e digita: `http://localhost/MrStock/`.
   - As vendas continuam sendo realizadas localmente sem qualquer interrupção no atendimento ao cliente de balcão.
   - Assim que a conexão externa for restabelecida, a sincronização do banco de dados local com a nuvem consolida o faturamento e as baixas de estoque.

---

# PARTE VIII: INSTRUÇÕES DE FORMATAÇÃO E ESTRUTURA PARA O CLAUDE (PROMPT ENGINEER)

Ao elaborar o Master Prompt para o Antigravity, garanta que ele exija a seguinte estrutura formal para o **Manual do Usuário Oficial**:

1. **Capa Institucional e Ficha Técnica:**
   - Logotipo textual, identificação da Papelaria Real, ETEC Fernando Prestes, Centro Paula Souza e dados dos 5 alunos e orientador.
2. **Sumário Executivo e Termo de Responsabilidade:**
   - Índice detalhado de capítulos e seções, política de sigilo de senhas e responsabilidade do operador.
3. **Capítulo 1: Primeiros Passos e Acesso ao Sistema:**
   - Requisitos mínimos de hardware/navegador, procedimento de login, recuperação de senha e dicas de segurança de credenciais.
4. **Capítulo 2: Visão Geral e Navegação do Dashboard:**
   - Interpretação dos 4 KPIs, leitura do termômetro de faturamento e monitoramento dos alertas de validade de 30 dias.
5. **Capítulo 3: Cadastros Fundamentais (Produtos, Famílias, Lotes, Clientes, Fornecedores):**
   - Passo a passo com campos obrigatórios, regras de código de barras, cadastro de lotes PEPS/FIFO e uso do botão de WhatsApp.
6. **Capítulo 4: Operação de Frente de Caixa (PDV Ágil) - O Guia do Operador:**
   - Fluxo completo da venda passo a passo, tabela de atalhos de teclado (F2, F4, F7, F8), conferência de troco e impressão da NFC-e simulada.
7. **Capítulo 5: Operações Pós-Venda, Reimpressão e Estorno:**
   - Consulta de vendas do dia, reimpressão de cupons a pedido do cliente e procedimento formal de estorno gerencial com devolução ao estoque.
8. **Capítulo 6: Entrada de Mercadorias e Gestão de Compras:**
   - Como alimentar novos lotes a partir de notas fiscais de fornecedores e conferência física de shelf-life.
9. **Capítulo 7: Relatórios Estratégicos e Análise Financeira (Guia do Gerente):**
   - Como interpretar a Curva ABC de produtos para compras eficientes, análise do DRE e monitoramento de logs de auditoria.
10. **Capítulo 8: Fechamento de Caixa Cego e Prestação de Contas:**
    - Procedimento de encerramento de turno, contagem de gaveta e resolução de divergências de caixa.
11. **Capítulo 9: Solução de Dúvidas, FAQ Operacional e Suporte:**
    - As 5 dúvidas mais frequentes respondidas didaticamente, contato de suporte técnico (SLA 2h) e protocolo de contingência offline.
12. **Glossário de Termos Técnicos e Comerciais:**
    - Definição clara de termos como PEPS/FIFO, NFC-e, EAN-13, Curva ABC, DRE, Markup, Custo Médio, RBAC e Token CSRF.
