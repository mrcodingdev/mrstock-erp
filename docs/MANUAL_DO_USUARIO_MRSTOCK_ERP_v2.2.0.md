# MANUAL DO USUÁRIO OFICIAL — MRSTOCK ERP v2.2.0
## Sistema Integrado de Gestão Comercial, Controle de Estoque PEPS/FIFO e Frente de Caixa (PDV) Ágil

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
- **Mantenedora:** Centro Estadual de Educação Tecnológica Paula Souza (CPS) / Governo de SP
- **Componente Curricular:** Trabalho de Conclusão de Curso (TCC) & Qualidade e Teste de Software (QTS)
- **Orientadores Oficiais:** Prof. Luiz Flávio & Prof. Vinicius

#### Equipe Mr. Coding (Autores & Desenvolvedores):
1. **Douglas Moraes Braz:** Líder Técnico, Arquiteto de Software e Engenheiro Full-Stack.
2. **Nikolas Pires Brandão:** Modelagem de Banco de Dados Relacional, Diagrama DER e DBA MySQL.
3. **Cesar Augusto da Silva Junior:** Engenharia de Requisitos, Interface com o Cliente e Validação Comercial.
4. **Enzo de Oliveira Soares:** Redação Técnica, Documentação Acadêmica e Normas ABNT/CPS.
5. **Eduardo Sugahara Neto:** Navegação Operacional, Apresentação e Demonstração Prática na Banca.

---

## TERMO DE RESPONSABILIDADE E SIGILO OPERACIONAL

O **MrStock ERP v2.2.0** é propriedade de uso comercial da **Papelaria Real Ltda**. Cada operador e gestor possui credencial privativa intransferível. Todas as operações efetuadas — incluindo abertura de venda, aplicação de desconto, cancelamento de itens, estorno de cupom e exportação de relatórios — são registradas com carimbo de data, horário, operador e IP na tabela `logs_auditoria`. É expressamente vedado o empréstimo de senhas. Divergências financeiras e de inventário serão imputadas diretamente ao operador com sessão ativa no instante do evento.

---

## TUTORIAL "COMECE AQUI" (GUIA RÁPIDO DE 10 MINUTOS)

1. **Acesso & Login:** Abra o navegador em `https://mrstock.com.br/login.php`. Digite seu usuário (`caixa`) e senha pessoal. O sistema abre diretamente no PDV.
2. **Bipagem de Itens:** No PDV, pressione **F2** (ou use o leitor óptico). Bipe o caderno ou caneta. O sistema emite sinal sonoro de 880Hz e adiciona ao carrinho.
3. **Fechamento de Venda:** Pressione **F4** para abrir a tela de pagamento. Escolha Dinheiro ou Pix. Se dinheiro, informe o valor recebido e confira o troco calculado.
4. **Emissão de Cupom:** Pressione **Enter** para confirmar e **Ctrl + P** para imprimir o cupom térmico na impressora de 80mm.
5. **Conferência:** Acesse `https://mrstock.com.br/vendas/historico.php` para auditar a venda concluída com carimbo de data, hora e valor líquido.

---

## SUMÁRIO DAS 24 TELAS HOMOLOGADAS (ROTEIRO DE TESTES QTS)

> ℹ️ **Nota de Organização Editorial:** As telas estão identificadas e numeradas conforme o Roteiro de Testes QTS oficial (1 a 24), porém foram agrupadas neste manual por afinidade temática para proporcionar uma leitura fluida e coerente (ex: a Tela 24 aparece no Capítulo 1 por tratar da navegação global; as Telas 22 e 23 figuram nos capítulos finais por tratarem de administração e suporte operacional).

| Código | Tela / Módulo | Rota / Arquivo Físico | Perfil de Acesso |
| :---: | :--- | :--- | :--- |
| **Tela 01** | Autenticação & Login de Usuários | `/login.php` | Todos (Administrador e Operador de Caixa) |
| **Tela 02** | Encerramento Seguro de Sessão (Logout) | `/logout.php` | Todos os operadores autenticados |
| **Tela 24** | Topbar & Sidebar Retrátil (Navegação Ergonômica) | `inc/header.php` | Todos (Itens condicionados ao perfil RBAC) |
| **Tela 03** | Dashboard Executivo & Venda Rápida | `/dashboard.php` | Administrador |
| **Tela 04** | Ponto de Venda (PDV Ágil de Balcão) | `/vendas/pdv.php` | Operador de Caixa e Administrador |
| **Tela 05** | Histórico de Vendas & Estorno Gerencial | `/vendas/historico.php` | Administrador (Estorno pleno) | Caixa (Consulta) |
| **Tela 06** | Comprovante de Venda / Cupom Térmico 80mm | `/vendas/cupom.php` | Administrador e Operador de Caixa |
| **Tela 07** | Painel Fiscal & Simulação Acadêmica de NFC-e | `/vendas/nfce.php` | Administrador e Operador de Caixa |
| **Tela 08** | Catálogo Geral de Produtos & Markup | `/produtos/index.php` | Administrador (Gestão plena) | Caixa (Consulta de saldo) |
| **Tela 09** | Lotes Físicos & Controle de Validades PEPS/FIFO | `/lotes/index.php` | Administrador |
| **Tela 10** | Gerador de Etiquetas SVG (Code 128 / EAN-13) | `/produtos/etiquetas.php` | Administrador e Operador de Caixa |
| **Tela 11** | Categorias & as 10 Famílias Funcionais | `/categorias/index.php` | Administrador |
| **Tela 12** | Movimentações de Estoque & Kardex (Livro-Razão) | `/produtos/movimentacoes.php` | Administrador e Operador de Caixa |
| **Tela 13** | Gestão de Clientes & Busca ViaCEP | `/clientes/index.php` | Administrador e Operador de Caixa |
| **Tela 14** | Gestão de Fornecedores & WhatsApp Direto | `/fornecedores/index.php` | Administrador |
| **Tela 15** | Ordens de Compra & Histórico de Abastecimento | `/compras/index.php` | Administrador |
| **Tela 16** | Nova Ordem de Compra & Entrada de Mercadorias | `/compras/nova.php` | Administrador |
| **Tela 17** | Conferência de Compra / Espelho do Pedido | `/compras/visualizar.php` | Administrador |
| **Tela 18** | Central de Relatórios Gerenciais & DRE | `/relatorios/index.php` | Administrador |
| **Tela 19** | Centro de Inteligência Comercial (BI / Chart.js) | `/relatorios/analise.php` | Administrador |
| **Tela 20** | Trilha de Auditoria Forense & Logs Imutáveis | `/relatorios/logs.php` | Administrador |
| **Tela 21** | Exportação de Relatórios para Excel (XLSX) | `/relatorios/excel.php` | Administrador |
| **Tela 23** | Configurações da Empresa & Gestão de Operadores | `/configuracoes.php` | Administrador |
| **Tela 22** | Central de Ajuda, Teclas de Atalho & FAQ | `/ajuda.php` | Administrador e Operador de Caixa |

---


# 1. ACESSO, NAVEGAÇÃO GLOBAL & SEGURANÇA

### Tela 01: Autenticação & Login de Usuários (`/login.php`)

📋 **RESUMO RÁPIDO — Autenticação & Login de Usuários**
- **Para que serve:** Identificação segura de cada operador via hash BCrypt, aplicando isolamento de perfil RBAC e proteção contra CSRF e Session Fixation.
- **Quem pode acessar:** Administrador e Operador de Caixa com credenciais ativas.
- **Onde encontrar:** Página Inicial > Tela de Login (/login.php).

#### Passo a Passo Operacional
1. Acesse o endereço oficial no navegador: https://mrstock.com.br/login.php (ou http://localhost/MrStock/login.php em contingência offline).
2. No campo Usuário, digite seu identificador ou e-mail corporativo (ex: admin ou caixa).
3. No campo Senha, digite sua senha de acesso confidencial.
4. Clique no botão sólido verde Entrar (ou pressione a tecla Enter).
5. O sistema autentica o hash criptográfico e redireciona: Administradores para o Dashboard (/dashboard.php) e Caixas para o PDV (/vendas/pdv.php).

> 💡 **Dica de Balcão:** Pressione a tecla Tab após digitar o usuário para pular diretamente ao campo de senha sem retirar as mãos do teclado.

> ⚠️ **Ponto de Atenção:** Cinco tentativas consecutivas com senha incorreta ativam bloqueio preventivo temporário por proteção contra ataques de força bruta.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Alerta vermelho: 'Usuário ou senha incorretos' (CT202 / UC001)  
> 💡 **Solução:** Verifique se a tecla Caps Lock está ativada. Digite a senha com calma. Caso persista, solicite redefinição ao Administrador.

> ❌ **Erro:** Formulário não submete e campos ganham contorno vermelho (CT201 / UC001)  
> 💡 **Solução:** Ambos os campos são obrigatórios. Preencha usuário e senha antes de submeter.

[REFERÊNCIA DE PRINT: `01_login_autenticacao.png`]

---

### Tela 02: Encerramento Seguro de Sessão (Logout) (`/logout.php`)

📋 **RESUMO RÁPIDO — Encerramento Seguro de Sessão (Logout)**
- **Para que serve:** Destrói a sessão em memória no servidor, revoga cookies de autenticação e blinda o terminal contra acessos retroativos via histórico do navegador.
- **Quem pode acessar:** Qualquer colaborador com sessão ativa.
- **Onde encontrar:** Topbar Superior > Avatar de Perfil no canto superior direito > Sair (/logout.php).

#### Passo a Passo Operacional
1. No canto superior direito da tela, clique sobre seu avatar ou nome de operador.
2. No menu suspenso aberto, clique na opção 'Sair do Sistema'.
3. O servidor executa session_destroy(), expira os tokens e redireciona imediatamente para /login.php com aviso de sessão encerrada.

> 💡 **Dica de Balcão:** Sempre efetue logout formal ao afastar-se do caixa para garantir que nenhuma transação seja imputada indevidamente à sua matrícula.

> ⚠️ **Ponto de Atenção:** Apenas fechar a aba do navegador mantém o cookie de sessão ativo por até 24 horas; sempre clique em Sair.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Operador clica em 'Voltar' no navegador após sair e teme vazamento (CT102 / UC002)  
> 💡 **Solução:** O arquivo inc/auth.php detecta a ausência de sessão e bloqueia a renderização, forçando redirecionamento instantâneo para /login.php.

[REFERÊNCIA DE PRINT: `02_logout_encerramento.png`]

---

### Tela 24: Topbar & Sidebar Retrátil (Navegação Ergonômica) (`inc/header.php`)

📋 **RESUMO RÁPIDO — Topbar & Sidebar Retrátil (Navegação Ergonômica)**
- **Para que serve:** Estrutura ergonômica de navegação em todo o ERP com menu lateral retrátil, logotipo institucional e barra superior limpa.
- **Quem pode acessar:** Todos os operadores autenticados.
- **Onde encontrar:** Componente estrutural fixo no topo e na lateral esquerda de todas as telas internas.

#### Passo a Passo Operacional
1. Para recolher a barra lateral e obter até 20% mais espaço útil na tela: clique no botão 'Recolher Menu' no rodapé da sidebar.
2. Para expandir os rótulos de texto: clique sobre qualquer ícone da barra contraída ou na seta de expansão.
3. Para voltar à tela principal: clique sobre o logotipo oficial da Papelaria Real no topo da barra lateral.

> 💡 **Dica de Balcão:** Em notebooks de 14 polegadas no balcão de vendas, mantenha a sidebar recolhida para visualizar todas as colunas de relatórios sem rolagem.

> ⚠️ **Ponto de Atenção:** A barra superior exibe estritamente o título limpo da página atual (ex: Ponto de Venda ou Histórico de Vendas), sem poluição visual.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Operador Caixa não localiza o menu de Configurações na sidebar  
> 💡 **Solução:** Comportamento nativo de segurança: usuários Caixa visualizam apenas módulos de balcão (PDV, Catálogo e Ajuda).

[REFERÊNCIA DE PRINT: `24_topbar_sidebar.png`]

---


# 2. GESTÃO ESTRATÉGICA E DASHBOARD EXECUTIVO

### Tela 03: Dashboard Executivo & Venda Rápida (`/dashboard.php`)

📋 **RESUMO RÁPIDO — Dashboard Executivo & Venda Rápida**
- **Para que serve:** Centraliza os 4 KPIs vitais do negócio, monitoramento de produtos vencendo em 30 dias (PEPS/FIFO), gráficos e checkout expresso.
- **Quem pode acessar:** Administrador (acesso pleno a lucratividade, custos e alertas de validade).
- **Onde encontrar:** Menu Lateral > Dashboard (/dashboard.php).

#### Passo a Passo Operacional
1. Acesse o Dashboard para inspecionar os cartões superiores: Faturamento Hoje (R$), Total de Vendas, Lucro Bruto Real (R$) e Estoque Crítico.
2. Consulte o quadro 'Alertas de Vencimento': mercadorias vencendo em até 30 dias recebem badge amarelo e vencidas recebem badge vermelho.
3. Para efetuar venda expressa sem abrir o PDV completo: selecione o produto no card 'Venda Rápida', informe quantidade e clique em 'Lançar Venda'.

> 💡 **Dica de Balcão:** Clique no botão 'Gerenciar Vencimentos' no card de alerta para abrir a tela de lotes já filtrada pelos produtos com prazo crítico.

> ⚠️ **Ponto de Atenção:** O Lucro Bruto Real no Dashboard calcula a margem sobre o custo de compra exato do lote físico consumido no atendimento.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Tentativa de acesso direto ao Dashboard por operador com perfil Caixa  
> 💡 **Solução:** Comportamento previsto no RBAC: caixas não possuem privilégios de visualização de margens financeiras e faturamento global, sendo redirecionados compulsoriamente para o PDV (/vendas/pdv.php).

> ❌ **Erro:** Card de Venda Rápida acusa 'Estoque insuficiente para a quantidade solicitada'  
> 💡 **Solução:** O produto está com saldo zerado ou abaixo da quantidade informada. Efetue entrada de mercadorias via Compras.

[REFERÊNCIA DE PRINT: `03_dashboard_executivo.png`]

---


# 3. FRENTE DE CAIXA, VENDAS E OPERAÇÕES FISCAIS

### Tela 04: Ponto de Venda (PDV Ágil de Balcão) (`/vendas/pdv.php`)

📋 **RESUMO RÁPIDO — Ponto de Venda (PDV Ágil de Balcão)**
- **Para que serve:** Frente de caixa de alta performance para bipagem óptica de códigos de barras, cálculo instantâneo de troco e atalhos F1–F9.
- **Quem pode acessar:** Operador de Caixa e Administrador.
- **Onde encontrar:** Menu Lateral > Vendas > Ponto de Venda (PDV) (/vendas/pdv.php).

#### Passo a Passo Operacional
1. Ao abrir a tela, o cursor foca imediatamente no campo de código de barras.
2. Bipe o código EAN-13 com o leitor óptico (ou pressione F2 para buscar pelo nome). O sistema emite sinal sonoro de 880Hz e adiciona ao carrinho.
3. Para lançar quantidade múltipla (ex: 10 cartolinas), digite '10*' antes do código ou utilize o seletor numérico.
4. Pressione F4 para abrir o modal de fechamento financeiro.
5. Selecione a forma de pagamento (Dinheiro, Pix, Cartão de Débito ou Crédito). Se em dinheiro, informe o valor pago e confira o troco.
6. Pressione Enter para concluir. O cupom fiscal simulado é emitido na hora.

> 💡 **Dica de Balcão:** Pressione a tecla F1 a qualquer momento para abrir o pop-up com o resumo de todos os atalhos de teclado do caixa.

> ⚠️ **Ponto de Atenção:** Trava de Margem Negativa: O sistema impede concessão de descontos que rebaixem o preço de venda abaixo do custo do lote.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Pop-up ao teclar F4: 'É necessário ao menos 1 produto no carrinho para finalizar' (CT201 / UC004)  
> 💡 **Solução:** Bipe ao menos uma mercadoria válida antes de disparar o fechamento da compra.

> ❌ **Erro:** Trava de desconto: 'Operação não permitida: Preço de venda abaixo do custo do lote' (UC004)  
> 💡 **Solução:** O desconto solicitado gera prejuízo na mercadoria. Ajuste o valor para manter margem financeira positiva.

[REFERÊNCIA DE PRINT: `04_pdv_frente_caixa.png`]

---

### Tela 05: Histórico de Vendas & Estorno Gerencial (`/vendas/historico.php`)

📋 **RESUMO RÁPIDO — Histórico de Vendas & Estorno Gerencial**
- **Para que serve:** Consulta cronológica das vendas processadas, filtros por operador e forma de pagamento, reimpressão de cupons e cancelamento gerencial.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Menu Lateral > Vendas > Histórico de Vendas (/vendas/historico.php).

#### Passo a Passo Operacional
1. Acesse /vendas/historico.php para auditar o fluxo de caixas.
2. Utilize os filtros superiores por período, cliente ou forma de pagamento.
3. Clique em 'Imprimir Cupom 80mm' para reemitir o cupom para o cliente.
4. Para estorno (exclusivo Administrador): clique no botão vermelho 'Estornar Venda', informe a justificativa formal e confirme.
5. O sistema estorna a venda e devolve as quantidades físicas aos seus lotes de origem (PEPS/FIFO) de forma 100% automática.

> 💡 **Dica de Balcão:** O cancelamento de venda estorna o valor financeiro e repõe o saldo em estoque no exato lote em que foi retirado.

> ⚠️ **Ponto de Atenção:** Operadores com perfil Caixa conseguem consultar suas vendas, mas não possuem permissão para executar estorno.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Caixa tenta clicar no botão de Estornar e recebe mensagem de Acesso Negado  
> 💡 **Solução:** Solicite à gerência que faça o estorno através de suas credenciais de Administrador.

[REFERÊNCIA DE PRINT: `05_historico_vendas.png`]

---

### Tela 06: Comprovante de Venda / Cupom Térmico 80mm (`/vendas/cupom.php`)

📋 **RESUMO RÁPIDO — Comprovante de Venda / Cupom Térmico 80mm**
- **Para que serve:** Exibição e impressão do comprovante de conferência do cliente otimizado para bobinas térmicas não-fiscais de 80mm e 58mm.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Aberto automaticamente após a venda ou via Histórico > Imprimir Cupom.

#### Passo a Passo Operacional
1. A tela apresenta o cupom com cabeçalho da Papelaria Real, CNPJ, lista de itens, subtotais, troco e mensagem de rodapé institucional.
2. Pressione Ctrl + P (ou clique em 'Imprimir Agora').
3. Na caixa de diálogo do navegador, selecione a impressora térmica de bobina.
4. Confirme a impressão e entregue o cupom impresso ao consumidor.

> 💡 **Dica de Balcão:** O cupom possui regras CSS limpas que removem automaticamente cabeçalhos e rodapés padrão do navegador na impressão.

> ⚠️ **Ponto de Atenção:** Certifique-se de que a bobina térmica possui papel suficiente antes de liberar a impressão para evitar filas no caixa.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Impressão sai com margens desconfiguradas ou texto cortado  
> 💡 **Solução:** Na janela de impressão do Chrome, certifique-se de selecionar 'Papel de 80mm' e desmarcar a opção 'Cabeçalho e Rodapé'.

[REFERÊNCIA DE PRINT: `06_cupom_termico.png`]

---

### Tela 07: Painel Fiscal & Simulação Acadêmica de NFC-e (`/vendas/nfce.php`)

📋 **RESUMO RÁPIDO — Painel Fiscal & Simulação Acadêmica de NFC-e**
- **Para que serve:** Demonstração acadêmica e fiscal de conformidade com a Nota Fiscal de Consumidor Eletrônica (NFC-e), Chave de 44 dígitos e QR Code SEFAZ.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Histórico de Vendas > Botão 'Painel Fiscal NFC-e' (/vendas/nfce.php?id=...).

#### Passo a Passo Operacional
1. Abra o painel fiscal da venda selecionada.
2. Inspecione a Chave de Acesso formatada em 44 dígitos com máscara oficial (ex: 35-2609-12345678000190-65-001-000001042-1-12345678-9).
3. Verifique a renderização vetorial do QR Code de consulta tributária para leitura por smartphones.
4. Confira o demonstrativo de impostos aproximados discriminados conforme a Lei Federal 12.741/2012.
5. Clique em 'Imprimir DANFE NFC-e' ou 'Voltar ao PDV'.

> 💡 **Dica de Balcão:** O painel foi chancelado pela banca da ETEC para comprovar a prontidão arquitetural tributária sem necessidade de certificado digital A1 pago.

> ⚠️ **Ponto de Atenção:** Trata-se de uma simulação acadêmica homologada para fins didáticos e demonstração de conformidade com os padrões da SEFAZ/SP.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** QR Code não é lido pelo celular  
> 💡 **Solução:** Aproxime o smartphone a cerca de 15cm do monitor garantindo foco nítido sobre o código vetorial.

[REFERÊNCIA DE PRINT: `07_painel_fiscal_nfce.png`]

---


# 4. GESTÃO DE ESTOQUE, PRODUTOS E CATALOGAÇÃO

### Tela 08: Catálogo Geral de Produtos & Markup (`/produtos/index.php`)

📋 **RESUMO RÁPIDO — Catálogo Geral de Produtos & Markup**
- **Para que serve:** Cadastro central de produtos, controle de código EAN-13, cálculo de markup/margem bruta, estoque mínimo e busca reativa.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Menu Lateral > Estoque > Catálogo de Produtos (/produtos/index.php).

#### Passo a Passo Operacional
1. Para cadastrar: clique no botão sólido verde '+ Adicionar Produto'.
2. Preencha o Nome Completo (ex: Caneta Esferográfica BIC Cristal 1.0mm Azul).
3. Informe o Código EAN-13 (use o leitor óptico sobre a embalagem ou digite).
4. Selecione uma das 10 Famílias Funcionais da Papelaria Real.
5. Informe Preço de Custo (R$) e Preço de Venda (R$): o sistema calcula automaticamente o percentual de markup e a margem de lucro.
6. Defina o Estoque Mínimo de Alerta e clique em Salvar.

> 💡 **Dica de Balcão:** Utilize a barra de Live Search no topo da listagem para encontrar qualquer item digitando apenas parte do nome ou código de barras.

> ⚠️ **Ponto de Atenção:** O sistema utiliza soft-delete (marcação como Inativo): produtos já vendidos não podem ser excluídos fisicamente para preservar a integridade contábil.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Tentativa de excluir produto com histórico de vendas no caixa (CT201 / UC008)  
> 💡 **Solução:** O sistema bloqueia a remoção física e altera o status para 'Inativo', mantendo o histórico de vendas passado 100% íntegro.

> ❌ **Erro:** Código de barras duplicado: 'EAN-13 já cadastrado para outro produto'  
> 💡 **Solução:** Cada produto deve possuir código de barras exclusivo. Verifique se o item já não foi cadastrado anteriormente.

[REFERÊNCIA DE PRINT: `08_produtos_catalogo.png`]

---

### Tela 09: Lotes Físicos & Controle de Validades PEPS/FIFO (`/lotes/index.php`)

📋 **RESUMO RÁPIDO — Lotes Físicos & Controle de Validades PEPS/FIFO**
- **Para que serve:** Rastreamento do shelf-life e custo de cada remessa de produtos químicos/perecíveis (colas, tintas, corretivos), garantindo consumo PEPS/FIFO.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Estoque > Lotes & Validades (/lotes/index.php).

#### Passo a Passo Operacional
1. Acesse a tela para visualizar a lista de todos os lotes ativos no almoxarifado.
2. Para lançar novo lote avulso: clique em 'Novo Lote'.
3. Selecione o produto, informe o número do lote do fabricante e a Data de Validade impressa na embalagem.
4. Informe a quantidade física e o custo de compra daquele lote específico.
5. Clique em Salvar. O lote entra na fila de prioridade de consumo PEPS/FIFO.

> 💡 **Dica de Balcão:** Lotes com vencimento em até 30 dias aparecem destacados com badge amarelo; lotes vencidos recebem badge vermelho.

> ⚠️ **Ponto de Atenção:** Produtos com lotes vencidos são automaticamente travados no PDV, impedindo sua comercialização no caixa.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Lote vencido surge no PDV ao tentar vender  
> 💡 **Solução:** O sistema bloqueia a venda de produtos vencidos com alerta de segurança sanitária. Providencie a queima ou descarte no Kardex.

[REFERÊNCIA DE PRINT: `09_lotes_validades.png`]

---

### Tela 10: Gerador de Etiquetas SVG (Code 128 / EAN-13) (`/produtos/etiquetas.php`)

📋 **RESUMO RÁPIDO — Gerador de Etiquetas SVG (Code 128 / EAN-13)**
- **Para que serve:** Geração e impressão de folhas de etiquetas de gôndola e códigos de barras vetoriais SVG nítidos para impressoras térmicas ou folhas A4 Pimaco.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Estoque > Catálogo de Produtos > Botão 'Imprimir Etiquetas' (/produtos/etiquetas.php).

#### Passo a Passo Operacional
1. Selecione a Família de Produtos ou filtre por itens específicos que receberão novas etiquetas nas prateleiras.
2. Informe a quantidade de etiquetas a gerar para cada item (ex: 15 etiquetas).
3. Clique em 'Visualizar Folha de Etiquetas': o sistema renderiza os códigos vetoriais SVG acompanhados de nome e preço de balcão.
4. Pressione Ctrl + P para disparar a impressão calibrada para etiquetas autoadesivas.

> 💡 **Dica de Balcão:** Por utilizar gráficos SVG vetoriais puros, os códigos não sofrem distorção ou borrão, garantindo 100% de leitura pelos leitores de mão.

> ⚠️ **Ponto de Atenção:** Verifique o alinhamento das margens na janela de impressão para casar perfeitamente com a folha de etiquetas adesivas utilizada.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Código de barras sai ilegível ou muito pequeno  
> 💡 **Solução:** Certifique-se de configurar a escala de impressão em '100%' ou 'Padrão' no navegador, sem ajuste automático de encolhimento.

[REFERÊNCIA DE PRINT: `10_gerador_etiquetas.png`]

---

### Tela 11: Categorias & as 10 Famílias Funcionais (`/categorias/index.php`)

📋 **RESUMO RÁPIDO — Categorias & as 10 Famílias Funcionais**
- **Para que serve:** Organização taxonômica do catálogo nas 10 Famílias Funcionais da Papelaria Real, prevenindo distorções na Curva ABC.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Estoque > Categorias (/categorias/index.php).

#### Passo a Passo Operacional
1. Consulte as 10 Famílias Funcionais oficiais: Cadernos & Blocos, Canetas & Marcadores, Lápis & Apontadores, Borrachas & Correção, Colas & Fitas Adesivas, Papéis & Folhas, Pastas & Organização, Corte & Medição, Tintas & Pintura, Grampeadores & Fixação.
2. Para criar uma subdivisão, clique em 'Nova Categoria', informe Nome e Descrição e confirme.
3. Clique em 'Ver Produtos Vinculados' para filtrar os itens da família selecionada.

> 💡 **Dica de Balcão:** A classificação em 10 Famílias Funcionais reflete a organização física das prateleiras da Papelaria Real, acelerando o inventário.

> ⚠️ **Ponto de Atenção:** Nunca utilize macro-categorias genéricas (como apenas 'Escolar' ou 'Escritório') para não descalibrar a Curva ABC de reposição.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Tentativa de excluir categoria com produtos vinculados  
> 💡 **Solução:** O sistema bloqueia a exclusão por integridade referencial: reclassifique os produtos antes de remover a categoria.

[REFERÊNCIA DE PRINT: `11_categorias_familias.png`]

---

### Tela 12: Movimentações de Estoque & Kardex (Livro-Razão) (`/produtos/movimentacoes.php`)

📋 **RESUMO RÁPIDO — Movimentações de Estoque & Kardex (Livro-Razão)**
- **Para que serve:** Livro-razão e auditoria detalhada de todas as entradas, saídas manuais, devoluções, perdas por quebra e baixas por vencimento de mercadorias.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Menu Lateral > Estoque > Movimentações (/produtos/movimentacoes.php).

#### Passo a Passo Operacional
1. Acesse a tela de movimentações para auditar o fluxo físico de estoques.
2. Utilize os filtros por período, produto ou operador responsável.
3. Para registrar perda física (ex: vidro de tinta que quebrou no estoque): clique em 'Nova Movimentação', selecione o produto, escolha o tipo 'Avaria / Perda', informe a quantidade e digite a justificativa obrigatória.
4. Confirme o lançamento: o estoque físico é abatido e um registro forense imutável é gravado na auditoria.

> 💡 **Dica de Balcão:** Audite semanalmente o Kardex para confrontar as perdas físicas com a meta de quebra operacional da loja.

> ⚠️ **Ponto de Atenção:** Toda movimentação manual de estoque exige justificativa textual obrigatória e registra o usuário logado e IP de origem.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Data final anterior à data inicial no filtro de período  
> 💡 **Solução:** O sistema acusa erro de validação temporal: corrija o intervalo de datas para gerar a listagem.

[REFERÊNCIA DE PRINT: `12_movimentacoes_kardex.png`]

---


# 5. RELACIONAMENTO COMERCIAL: CLIENTES E FORNECEDORES

### Tela 13: Gestão de Clientes & Busca ViaCEP (`/clientes/index.php`)

📋 **RESUMO RÁPIDO — Gestão de Clientes & Busca ViaCEP**
- **Para que serve:** Cadastro completo de clientes (Pessoa Física e Jurídica), validação matemática de CPF/CNPJ, autopreenchimento de CEP e WhatsApp direto.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Menu Lateral > Clientes > Listagem de Clientes (/clientes/index.php).

#### Passo a Passo Operacional
1. Clique no botão sólido verde 'Cadastrar Cliente'.
2. Preencha Nome Completo e CPF (ou Razão Social e CNPJ).
3. No campo CEP, digite os 8 números: o sistema consulta o serviço ViaCEP e preenche Logradouro, Bairro e Cidade instantaneamente. Complete com o número predial.
4. Informe o Telefone / WhatsApp e clique em Salvar.
5. Na listagem de clientes, clique no botão circular oficial do WhatsApp para iniciar atendimento imediato.

> 💡 **Dica de Balcão:** O botão circular do WhatsApp permite conversar com o cliente com 1 clique, sem necessidade de cadastrá-lo na agenda do telefone da loja.

> ⚠️ **Ponto de Atenção:** O sistema aplica validação matemática oficial dos dígitos verificadores de CPF e CNPJ, prevenindo cadastros fictícios.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Alerta de CPF inválido: 'Dígitos verificadores não conferem'  
> 💡 **Solução:** Confira o número do documento junto ao documento físico apresentado pelo cliente.

[REFERÊNCIA DE PRINT: `13_clientes_cadastro.png`]

---

### Tela 14: Gestão de Fornecedores & WhatsApp Direto (`/fornecedores/index.php`)

📋 **RESUMO RÁPIDO — Gestão de Fornecedores & WhatsApp Direto**
- **Para que serve:** Cadastro de distribuidoras e fábricas homologadas (Tilibra, BIC, Faber-Castell, Chamex), prazos de entrega e canal direto de cotação.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Compras > Fornecedores (/fornecedores/index.php).

#### Passo a Passo Operacional
1. Clique em 'Novo Fornecedor'.
2. Informe Razão Social, Nome Fantasia, CNPJ e Telefone do vendedor/representante.
3. Clique em Salvar Fornecedor.
4. Na listagem, clique no botão circular verde do WhatsApp para disparar cotação de reposição diretamente com o representante da indústria.

> 💡 **Dica de Balcão:** Mantenha o telefone do televendas do fornecedor atualizado para agilizar a cotação de itens com estoque crítico no Dashboard.

> ⚠️ **Ponto de Atenção:** O CNPJ cadastrado deve ser o da matriz ou filial exata emissora das notas fiscais de compra.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Tentativa de cadastrar fornecedor sem CNPJ ou com CNPJ incompleto  
> 💡 **Solução:** O campo CNPJ é obrigatório para emissão e conciliação de notas fiscais de entrada.

[REFERÊNCIA DE PRINT: `14_fornecedores_whatsapp.png`]

---


# 6. ABASTECIMENTO E GESTÃO DE COMPRAS

### Tela 15: Ordens de Compra & Histórico de Abastecimento (`/compras/index.php`)

📋 **RESUMO RÁPIDO — Ordens de Compra & Histórico de Abastecimento**
- **Para que serve:** Acompanhamento das aquisições de mercadorias, conciliação de faturas, status de entrega e auditoria de compras da Papelaria Real.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Compras > Ordens de Compra (/compras/index.php).

#### Passo a Passo Operacional
1. Acesse a tela para auditar os pedidos faturados pelas distribuidoras.
2. Consulte os filtros por período ou por fornecedor.
3. Clique em 'Ver Detalhes' para abrir o espelho de conferência de mercadorias recebidas.

> 💡 **Dica de Balcão:** Ordens de compra com status 'Pendente' indicam pedidos despachados pelo fornecedor aguardando conferência física de recebimento.

> ⚠️ **Ponto de Atenção:** Apenas pedidos conferidos e aprovados creditam unidades físicas nos saldos de estoque do ERP.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Tentativa de excluir ordem de compra já concluída  
> 💡 **Solução:** Ordens concluídas geraram movimentações fiscais e contábeis imutáveis e não podem ser apagadas.

[REFERÊNCIA DE PRINT: `15_compras_historico.png`]

---

### Tela 16: Nova Ordem de Compra & Entrada de Mercadorias (`/compras/nova.php`)

📋 **RESUMO RÁPIDO — Nova Ordem de Compra & Entrada de Mercadorias**
- **Para que serve:** Lançamento de notas fiscais de entrada, incremento automático de estoque e criação dos lotes físicos com custo de aquisição e validade.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Ordens de Compra > Botão 'Registrar Nova Compra' (/compras/nova.php).

#### Passo a Passo Operacional
1. Selecione o Fornecedor que emitiu a Nota Fiscal.
2. Adicione os produtos informando a Quantidade Comprada e o Custo Unitário líquido.
3. Para produtos perecíveis/químicos: informe o Lote do Fabricante e a Data de Validade da caixa.
4. Clique em 'Finalizar Compra': o sistema credita as quantidades no estoque e insere o lote na fila PEPS/FIFO.

> 💡 **Dica de Balcão:** Sempre confira os valores unitários com a DANFE física para garantir que o Custo Médio e o Lucro Bruto Real reflitam a realidade.

> ⚠️ **Ponto de Atenção:** A entrada de mercadorias atualiza o custo base do produto e recalcula as margens sugeridas de markup no catálogo.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Finalização sem nenhum produto adicionado na lista  
> 💡 **Solução:** Adicione ao menos um item informando quantidade e custo unitário antes de confirmar o recebimento.

[REFERÊNCIA DE PRINT: `16_compras_nova_ordem.png`]

---

### Tela 17: Conferência de Compra / Espelho do Pedido (`/compras/visualizar.php`)

📋 **RESUMO RÁPIDO — Conferência de Compra / Espelho do Pedido**
- **Para que serve:** Espelho formal Master-Detail da ordem de compra para conferência de entrega no almoxarifado e impressão em folha A4.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Histórico de Compras > Botão 'Ver Detalhes' (/compras/visualizar.php?id=...).

#### Passo a Passo Operacional
1. Abra o espelho da compra para confrontar as quantidades faturadas com as caixas entregues pela transportadora.
2. Clique em 'Imprimir Espelho de Compra' para gerar folha de conferência de carga assinada pelo conferente do almoxarifado.

> 💡 **Dica de Balcão:** Utilize o espelho impresso para marcar com caneta cada caixa inspecionada antes de liberar o motorista da transportadora.

> ⚠️ **Ponto de Atenção:** Divergências de quantidade entre a nota e a entrega física devem ser registradas na hora como ressalva no canhoto da nota fiscal.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Pedido com número de ID inexistente  
> 💡 **Solução:** O sistema emite aviso de 'Ordem de compra não encontrada' e redireciona para a listagem.

[REFERÊNCIA DE PRINT: `17_compras_visualizar_espelho.png`]

---


# 7. CENTRO DE INTELIGÊNCIA, BI E RELATÓRIOS ESTRATÉGICOS

### Tela 18: Central de Relatórios Gerenciais & DRE (`/relatorios/index.php`)

📋 **RESUMO RÁPIDO — Central de Relatórios Gerenciais & DRE**
- **Para que serve:** Central executiva para emissão de Demonstração do Resultado do Exercício (DRE), Curva ABC de produtos e relatórios contábeis.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Relatórios (/relatorios/index.php).

#### Passo a Passo Operacional
1. Acesse a Central de Relatórios.
2. Escolha o tipo de relatório desejado: DRE Gerencial, Curva ABC, Giro de Estoque ou Validades Críticas.
3. Defina o período de competência (Mês Corrente, Trimestre ou Intervalo Customizado).
4. Clique em 'Gerar Relatório' para visualizar os números consolidados na tela.

> 💡 **Dica de Balcão:** O DRE confronta a Receita Bruta com o CMV (Custo das Mercadorias Vendidas) apurado via PEPS/FIFO, revelando a margem operacional real.

> ⚠️ **Ponto de Atenção:** Relatórios consolidados exigem perfil Administrador e registram evento formal na trilha de logs por compliance.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Período sem movimentações registradas  
> 💡 **Solução:** O sistema informa 'Nenhum registro encontrado para os filtros selecionados', mantendo os totalizadores zerados.

[REFERÊNCIA DE PRINT: `18_relatorios_central.png`]

---

### Tela 19: Centro de Inteligência Comercial (BI / Chart.js) (`/relatorios/analise.php`)

📋 **RESUMO RÁPIDO — Centro de Inteligência Comercial (BI / Chart.js)**
- **Para que serve:** Dashboard analítico interativo com gráficos visuais em tempo real via Chart.js, cruzando faturamento, CMV, ticket médio e Curva ABC.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Central de Relatórios > Centro de Análise (BI) (/relatorios/analise.php).

#### Passo a Passo Operacional
1. Alterne entre as abas temporais: 'Últimos 7 Dias', 'Mês Corrente' ou 'Ano Completo'.
2. Os gráficos de barra e pizza se recalculam instantaneamente com animação fluida.
3. Analise o gráfico da Curva ABC para identificar os 20% de produtos vitais que respondem por 80% do faturamento da Papelaria Real.
4. Clique em 'Imprimir Relatório Analítico' para gerar documento executivo A4.

> 💡 **Dica de Balcão:** Passe o cursor sobre as barras dos gráficos para inspecionar tooltips detalhados com os valores exatos de receita e custo de cada dia.

> ⚠️ **Ponto de Atenção:** Gráficos de BI demandam conexão estável para carregar bibliotecas visuais ou usam cache local em contingência.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Gráfico em branco após troca de período  
> 💡 **Solução:** Clique no botão 'Atualizar Dados' para forçar o recálculo dos arrays JSON no frontend.

[REFERÊNCIA DE PRINT: `19_relatorios_bi_graficos.png`]

---

### Tela 20: Trilha de Auditoria Forense & Logs Imutáveis (`/relatorios/logs.php`)

📋 **RESUMO RÁPIDO — Trilha de Auditoria Forense & Logs Imutáveis**
- **Para que serve:** Registro imutável de todas as transações, alterações de preço, concessão de desconto, estornos e acessos com data, hora, usuário e IP.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Relatórios > Logs de Auditoria (/relatorios/logs.php).

#### Passo a Passo Operacional
1. Acesse a trilha de auditoria para apurar qualquer divergência financeira ou operacional.
2. Filtre por Operador, por Ação (Ex: Login, Venda, Estorno, Alteração de Preço) ou por Data.
3. Cada registro exibe o carimbo de data/hora oficial, o IP da máquina e o snapshot dos dados anteriores e posteriores.

> 💡 **Dica de Balcão:** A tabela logs_auditoria possui índice B-Tree sargable garantindo buscas instantâneas mesmo com milhares de linhas gravadas.

> ⚠️ **Ponto de Atenção:** Os logs são estritamente imutáveis: nem mesmo o Administrador possui permissão no sistema para alterar ou expurgar registros.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Busca sem correspondência de registros  
> 💡 **Solução:** Verifique se os filtros de data não estão invertidos ou se o operador selecionado realizou ações no período.

[REFERÊNCIA DE PRINT: `20_relatorios_logs_auditoria.png`]

---

### Tela 21: Exportação de Relatórios para Excel (XLSX) (`/relatorios/excel.php`)

📋 **RESUMO RÁPIDO — Exportação de Relatórios para Excel (XLSX)**
- **Para que serve:** Exportação analítica de dados de vendas, estoque e validades em planilhas XLSX padronizadas em 9 colunas reais prontas para a contabilidade.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Central de Relatórios > Botões 'Baixar Excel' (/relatorios/excel.php).

#### Passo a Passo Operacional
1. Na Central de Relatórios, escolha o conjunto de dados a exportar (Inventário, Vendas, Validades ou Movimentações).
2. Clique no botão verde 'Baixar Planilha Excel'.
3. O arquivo .xlsx é gerado e baixado no computador com formatação monetária (R$), numerais tabulares e cabeçalhos fixos.

> 💡 **Dica de Balcão:** As 9 colunas do arquivo XLSX casam perfeitamente com os sistemas contábeis utilizados por escritórios de contabilidade parceiros.

> ⚠️ **Ponto de Atenção:** O arquivo gerado é compatível com Microsoft Excel, LibreOffice Calc e Google Planilhas.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Bloqueio de pop-up pelo navegador  
> 💡 **Solução:** Permita o download de arquivos automáticos originados de https://mrstock.com.br nas configurações do Chrome.

[REFERÊNCIA DE PRINT: `21_relatorios_exportacao_excel.png`]

---


# 8. ADMINISTRAÇÃO DO SISTEMA E GOVERNANÇA RBAC

### Tela 23: Configurações da Empresa & Gestão de Operadores (`/configuracoes.php`)

📋 **RESUMO RÁPIDO — Configurações da Empresa & Gestão de Operadores**
- **Para que serve:** Parâmetros cadastrais da Papelaria Real, alíquotas tributárias, mensagem institucional do cupom e gestão de operadores de caixa.
- **Quem pode acessar:** Administrador.
- **Onde encontrar:** Menu Lateral > Configurações (/configuracoes.php).

#### Passo a Passo Operacional
1. Acesse o painel de configurações gerais.
2. Atualize Razão Social, CNPJ, Inscrição Estadual, Endereço e Telefone da Papelaria Real.
3. Personalize a Mensagem de Rodapé do Cupom de Venda (ex: 'Obrigado pela preferência! Volte sempre!').
4. Na aba 'Gestão de Usuários': cadastre novos operadores informando nome, e-mail e perfil (Administrador ou Caixa) e gerencie redefinições de senha.
5. Clique em 'Salvar Configurações'.

> 💡 **Dica de Balcão:** Defina o perfil de novos atendentes de balcão rigorosamente como 'Caixa' para proteger relatórios financeiros e custos de mercadoria.

> ⚠️ **Ponto de Atenção:** Senhas cadastradas são processadas por hash BCrypt com fator de custo 12; nunca utilize senhas óbvias ou compartilhadas.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Tentativa de cadastrar usuário com e-mail já existente  
> 💡 **Solução:** O sistema impede duplicidade de login: informe um e-mail exclusivo para cada colaborador.

[REFERÊNCIA DE PRINT: `23_configuracoes_empresa.png`]

---


# 9. SUPORTE OPERACIONAL, FAQ E CONTINGÊNCIA

### Tela 22: Central de Ajuda, Teclas de Atalho & FAQ (`/ajuda.php`)

📋 **RESUMO RÁPIDO — Central de Ajuda, Teclas de Atalho & FAQ**
- **Para que serve:** Manual interativo onboard com busca reativa, acordeões explicativos, mesa de atalhos de teclado e canais de suporte com SLA de 2 horas.
- **Quem pode acessar:** Administrador e Operador de Caixa.
- **Onde encontrar:** Topbar Superior > Ícone de Ajuda (?) ou Menu Lateral > Ajuda (/ajuda.php).

#### Passo a Passo Operacional
1. Acesse a Central de Ajuda para esclarecer dúvidas operacionais rápidas.
2. Utilize a barra de busca no topo para encontrar orientações sobre estorno, atalhos, leitor óptico ou fechamento de caixa.
3. Clique nos acordeões retráteis para expandir as respostas técnicas ilustradas.
4. Consulte os telefones e canais oficiais de suporte técnico da equipe Mr. Coding.

> 💡 **Dica de Balcão:** A Central de Ajuda é totalmente acessível por teclado via teclas Tab e Enter conforme as normas internacionais WAI-ARIA.

> ⚠️ **Ponto de Atenção:** O SLA de suporte técnico prioritário para a Papelaria Real é de até 2 horas úteis em horário comercial.

#### ❌ Erros Comuns e Soluções (Casos de Teste QTS)
> ❌ **Erro:** Dúvida não localizada na busca  
> 💡 **Solução:** Acione o suporte técnico da equipe Mr. Coding diretamente pelo canal de WhatsApp institucional.

[REFERÊNCIA DE PRINT: `22_central_ajuda_faq.png`]

---

## TABELA OFICIAL DE ATALHOS DE TECLADO DO PDV

| Tecla / Atalho | Função Operacional | Comportamento no Sistema (Homologado QTS & PDV) |
| :---: | :--- | :--- |
| **`F1`** | Ajuda de Teclado | Abre pop-up na tela com o resumo de todos os comandos rápidos do PDV. |
| **`F2`** | Foco no Leitor / Busca | Posiciona o cursor no campo de código de barras ou busca de itens. |
| **`F4`** | Finalizar Venda | Abre a janela de fechamento financeiro, cálculo de troco e formas de pagamento. |
| **`F7 *`** | Focar Campo de Desconto | Move o cursor diretamente para o campo de desconto (sujeito à trava de margem). |
| **`F8 *`** | Alternar Forma de Pagamento | Alterna ciclicamente entre Dinheiro, Pix, Cartão de Débito e Cartão de Crédito. |
| **`F9`** | Cancelar / Limpar Carrinho | Limpa todos os itens do cupom aberto mediante confirmação rápida. |
| **`Esc`** | Fechar Janelas / Voltar | Fecha qualquer modal ativo e retorna o foco à bipagem de compras. |
| **`Ctrl + P`** | Imprimir Cupom | Dispara o comando de impressão do cupom térmico ou DANFE NFC-e. |

*Os atalhos F7 (focar campo de desconto) e F8 (alternar forma de pagamento) foram confirmados por inspeção direta do código-fonte do PDV em vendas/pdv.php: linhas 1028–1047 (event listener global de teclado) e linhas 334–339 (interface do modal de atalhos do caixa).

---

## FECHAMENTO DE CAIXA CEGO E CONCILIAÇÃO DE GAVETA

O operador de caixa efetua a contagem física de todo o dinheiro, comprovantes de cartão e comprovantes de Pix presentes na gaveta sem visualizar o saldo esperado pelo sistema. Em seguida, digita os valores contados. O sistema confronta as informações com os registros de vendas da sessão e aponta eventuais sobras ou quebras na tela do Administrador, garantindo lisura absoluta e prevenindo apropriações indébitas.

> ℹ️ **Nota de Rastreabilidade QTS & Roadmap:** Este procedimento descreve uma rotina operacional/manual de conferência física de gaveta da Papelaria Real. Não constitui uma tela de software isolada no Roteiro de Testes QTS da versão 2.2.0, estando o módulo automatizado de gestão de turnos, suprimentos e sangrias posicionado como evolução arquitetural no Roadmap da Versão 3.0.

---

## FAQ OFICIAL — AS 5 PERGUNTAS MAIS FREQUENTES DA PAPELARIA REAL

### 1. Como proceder quando o leitor de código de barras não reconhece a embalagem?
Pressione a tecla F2 do teclado para focar a barra de pesquisa, digite os primeiros caracteres do nome do produto (ex: 'caderno tili') ou digite os números do EAN-13 manualmente. O sistema realiza busca em tempo real e exibe os itens correspondentes com preço e estoque para seleção imediata via tecla Enter.

### 2. O que fazer quando um produto acusa 'Estoque Insuficiente' ou lote vencido no PDV?
O sistema MrStock ERP impede a venda de mercadorias com saldo físico zerado ou cujo lote esteja com a data de validade expirada, em estrito cumprimento às normas de defesa do consumidor. Nesse caso, chame o Administrador para conferir o estoque físico no almoxarifado, dar entrada na nota fiscal via módulo de Compras ou efetuar o descarte/ajuste no Kardex.

### 3. Como cancelar uma venda incorreta ou estornar um cupom após a finalização?
O cancelamento formal de cupons já emitidos é restrito ao Administrador por segurança contábil. Acesse Vendas > Histórico de Vendas (/vendas/historico.php), localize a venda, clique no botão vermelho 'Estornar Venda', informe a justificativa formal e confirme. O sistema cancela a venda e devolve as unidades físicas aos seus lotes exatos de origem (PEPS/FIFO) de forma automática.

### 4. Por que os produtos são organizados em 10 Famílias Funcionais em vez de categorias genéricas?
A separação por 10 Famílias Funcionais (Cadernos & Blocos, Canetas & Marcadores, Lápis & Apontadores, Borrachas & Correção, Colas & Fitas Adesivas, Papéis & Folhas, Pastas & Organização, Corte & Medição, Tintas & Pintura, Grampeadores & Fixação) reflete com precisão a rotina varejista física da Papelaria Real, prevenindo distorções na Curva ABC e facilitando a conferência nas prateleiras.

### 5. Como redefinir senhas de operadores de caixa ou cadastrar novos funcionários?
O Administrador acessa o menu Configurações > Gestão de Usuários (/configuracoes.php), onde pode criar novos colaboradores com perfil 'Caixa' (permissões restritas de balcão) ou 'Administrador' (acesso total). Caso um operador esqueça sua senha, o Administrador pode redefini-la instantaneamente através do painel, com criptografia BCrypt de alta segurança.

---

## PROTOCOLO OPERACIONAL DE CONTINGÊNCIA OFFLINE (MODO LOCAL XAMPP)

1. Caso a internet externa caia, o sistema na nuvem Hostinger não carregará. **NÃO reinicie o computador.**
2. Abra uma nova aba no Google Chrome e digite: `http://localhost/MrStock/login.php`.
3. Efetue login com suas credenciais normais de operador de caixa.
4. Continue atendendo os clientes e registrando vendas normalmente no PDV local.
5. O banco de dados local MySQL gravará as transações e as baixas de lotes com segurança.
6. Quando o link de internet for restabelecido, o pipeline de sincronização atualiza a nuvem Hostinger sem perda de dados.

---

## GLOSSÁRIO DE TERMOS TÉCNICOS E COMERCIAIS

- **PEPS / FIFO:** Primeiro que Entra, Primeiro que Sai: princípio contábil onde mercadorias adquiridas primeiro (ou com validade mais próxima) são consumidas prioritariamente no caixa.
- **NFC-e:** Nota Fiscal de Consumidor Eletrônica: documento fiscal digital padrão do varejo que registra a operação comercial com o consumidor final perante a SEFAZ.
- **EAN-13:** Padrão internacional de código de barras composto por 13 dígitos numéricos presente nas embalagens industriais.
- **Curva ABC:** Classificação de estoque baseada no princípio de Pareto (80/20), categorizando itens em alta rotatividade (A), intermediários (B) e baixo giro (C).
- **DRE Gerencial:** Demonstração do Resultado do Exercício: relatório que confronta receitas brutas e CMV para revelar o lucro bruto real da operação.
- **Markup:** Índice percentual aplicado sobre o custo de compra para determinar o preço de venda de balcão.
- **RBAC:** Role-Based Access Control: modelo de segurança que restringe acessos e funções com base no cargo do colaborador (Administrador vs Caixa).
- **Bcrypt:** Algoritmo criptográfico adaptativo e seguro utilizado para armazenamento irreversível de senhas no banco de dados.
- **Kardex:** Livro-razão e registro cronológico formal de todas as entradas, saídas, avarias e transferências físicas de mercadorias no estoque.
- **Trava de Margem:** Bloqueio preventivo no PDV que impede a concessão de descontos que rebaixem o preço de venda abaixo do custo real de aquisição.

---

## TERMO DE HOMOLOGAÇÃO ACADÊMICA & ENCERRAMENTO TÉCNICO

O presente Manual do Usuário (Versão 2.2.0) consolida as 24 telas homologadas no Roteiro de Testes de Software (QTS), atestando a plena prontidão do sistema para implantação na **Papelaria Real Ltda** e avaliação pela banca examinadora da **ETEC Fernando Prestes**.

**Equipe Técnica Mr. Coding — ETEC Fernando Prestes**  
Douglas Moraes Braz • Cesar Augusto da Silva Junior • Eduardo Sugahara Neto • Enzo de Oliveira Soares • Nikolas Pires Brandão  
*Orientadores: Prof. Luiz Flávio & Prof. Vinicius*  