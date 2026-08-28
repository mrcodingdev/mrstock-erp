# ESPECIFICAÇÃO TÉCNICA FORMAL: MODERNIZAÇÃO DE UI/UX & MICROINTERAÇÕES (MrStock ERP v2.0)
**Documento de Especificação (Spec-Driven Development — Metodologia Addy Osmani)**  
**Status:** VALIDADO VIA ENTREVISTA | **Data:** 25 de Agosto de 2026 | **Versão:** 2.1  
**Módulo ID:** `ui-ux-modernization`

---

## 1. Objetivo & Visão Geral
Elevar a qualidade visual, a modernidade estética e a fluidez interativa do **MrStock ERP v2.0** aos padrões de ERPs e SaaS de classe mundial (*Linear, Stripe Dashboard, Conta Azul e Tiny ERP*), preservando rigorosamente a identidade visual da Papelaria Real, o tempo de resposta instantâneo (0ms) e a portabilidade 100% offline.

### 👤 Usuários-Alvo
- **Operador de Caixa (Sr. Osnir):** Necessita de foco extremo, ergonomia tátil no teclado e clareza instantânea de troco e status de caixa.
- **Gestora / Administradora (Dona Sueli):** Necessita de leitura confortável sem fadiga visual, relatórios limpos e personalização ergonômica de tabelas e fontes.
- **Banca Examinadora da ETEC:** Avaliará o profissionalismo, a maturidade do Design System, a responsividade e o refinamento das microinterações.

---

## 2. Invariantes & O que NÃO Irá Mudar (Guardrails)
1. **Paleta Institucional Protegida:**
   - Verde Oficial Primário: `#284936`
   - Fundo Dark Sidebar: `#222d31`
   - Destaque Accent Menta: `#6ae49b`
   - Canvas Neutro Anti-Fadiga: `#f1f5f9`
2. **Botões Sólidos Mandatórios:** Proibido o uso de botões transparentes com borda colorida (`btn-outline-*`) para ações principais ou perigosas.
3. **Topbar Limpa:** NUNCA reintroduzir o prefixo redundante `MrStock ERP -` no título visível.
4. **Premissa 100% Offline:** Nenhuma biblioteca externa via CDN (Tailwind CDN, Google Fonts externo, etc.) pode ser introduzida. Todos os assets e efeitos devem rodar em CSS e JS puros locais.
5. **MrStockBackup Preservado:** A pasta `C:\xampp\htdocs\MrStockBackup\` permanece intacta até aprovação visual final.

---

## 3. Especificação Detalhada das Mudanças

### 3.1. Microinterações e Transições Globais (`css/style.css`)
- **Transição de Página SPA Suave (`@keyframes pageEnter`):**
  - Efeito de entrada suave com elevação de 4px e fade-in de 0.22s cúbico (`cubic-bezier(0.16, 1, 0.3, 1)`) aplicado no elemento `.content-body` e `.content-header`.
  - Proporciona a percepção de uma Single-Page Application (SPA) moderna ao navegar entre rotas PHP tradicionais.
- **Feedback Tátil em Botões (`.btn:active`):**
  - Adição do efeito físico de pressão: `transform: scale(0.98);` ao clicar.
  - Anel de foco suave e acessível: `box-shadow: 0 0 0 3px rgba(40, 73, 54, 0.18);`.
- **Pílula de Status em Tempo Real (`.badge-live-pulse`):**
  - Componente de badge institucional com ponto verde animado por `@keyframes livePulse` (efeito de radar) para indicar "• Caixa Aberto" no PDV e "• Sistema Operando" no Dashboard.
- **Higiene e Desduplicação do CSS:**
  - Fundir o bloco `.so-actions-btn` das linhas 803 e 1271 em uma única regra limpa e sem redundâncias.

### 3.2. Empty States Ilustrados em SVG (`inc/functions.php` & Templates)
- **Componente `.so-empty-state`:**
  - Exibido quando buscas em tempo real ou consultas retornarem zero registros.
  - Contém ilustração vetorial SVG minimalista em tons de cinza/verde institucional, texto empático ("Nenhum produto encontrado com este termo") e botão sólido "Limpar Filtros".

### 3.3. Rework da Aba de Aparência (`configuracoes.php`)
- **Abas Segmentadas 100% Esticadas e Responsivas:**
  - Grid flexível (`.settings-nav-tabs`) que ocupa 100% da largura do card, garantindo equilíbrio visual em qualquer resolução.
- **Painel de Aparência Funcional:**
  - **Densidade de Tabela:** Seletor visual entre *Padrão (Espaçamento Confortável)* e *Compacta (Mais linhas por tela)*.
  - **Tamanho da Fonte da Interface:** Seletor entre *Normal (14px)* e *Conforto Visual (16px)*.
  - **Linhas Zebradas nas Tabelas:** Switch Toggle ON/OFF com persistência no banco e na classe do `body`.
  - Remoção de seletores genéricos sem utilidade prática.

---

## 4. Matriz de Arquivos Impactados

| Arquivo | Ação | Responsabilidade |
|---|:---:|---|
| `css/style.css` | **[MODIFY]** | Animação `@keyframes pageEnter`, `.btn:active scale(0.98)`, `.badge-live-pulse`, `.so-empty-state`, desduplicação do `.so-actions-btn`. |
| `inc/header.php` | **[MODIFY]** | Inclusão de classes dinâmicas de densidade/fonte no `<body>` e suporte ao efeito de transição. |
| `configuracoes.php` | **[MODIFY]** | Rework visual das abas esticadas e novo formulário funcional de Aparência (Densidade, Fonte, Zebrado). |
| `vendas/pdv.php` | **[MODIFY]** | Adição do indicador pulsante de status de caixa (`.badge-live-pulse`) no cabeçalho do balcão. |
| `dashboard.php` | **[MODIFY]** | Adição do indicador de integridade operacional do sistema. |

---

## 5. Critérios de Aceitação & Testes de Homologação

1. **[Critério 1 - Transição]:** Ao clicar em qualquer menu da barra lateral, a página destino carrega com transição suave (fade-in + elevação) sem "piscar" em branco.
2. **[Critério 2 - Clique Tátil]:** Ao clicar em qualquer botão de ação (`.btn-primary`, `.btn-success`, `.btn-danger`), o botão encolhe suavemente para 98% da escala e retorna ao soltar.
3. **[Critério 3 - Indicador Live Pulse]:** O PDV e o Dashboard exibem uma pílula com um ponto verde pulsando suavemente no cabeçalho.
4. **[Critério 4 - Configurações]:** A alteração de densidade de tabela ou tamanho de fonte em `configuracoes.php` aplica a mudança visual imediatamente e persiste no banco.
5. **[Critério 5 - Suíte de Testes]:** Todas as suítes de testes automatizados (`test_full_system_audit.php`, `test_nightly_pipeline.php`, `test_pdv_ergonomia.php`, `test_bi_accuracy.php`) continuam aprovadas com 100% de sucesso.