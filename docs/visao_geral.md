# MrStock ERP v2.0 — Visão Geral do Sistema

> **Trabalho de Conclusão de Curso (TCC)**  
> Sistema Integrado de Gestão de Estoque, Frente de Caixa (PDV SalesOps), Compras e Inteligência Comercial  
> **Cenário de Negócio:** Papelaria Real (Sueli & Osnir)  
> **Instituição:** ETEC Fernando Prestes — Sorocaba/SP (Centro Paula Souza)  
> **Equipe Mr. Coding:** Douglas, Cesar, Eduardo, Enzo e Nikolas  
> **Orientadores:** Prof. Luiz Flávio & Prof. Vinicius  
> **Stack:** PHP 8.2+ · MySQL 8.0+ (InnoDB `utf8mb4`) · PDO ACID · Bootstrap 5.3 · Design System SalesOps · Web Audio API · SVG Nativo

---

## 1. O que é o MrStock ERP?

O **MrStock ERP** é uma plataforma moderna e completa de gestão empresarial (*Enterprise Resource Planning*) desenvolvida especificamente para o segmento de papelarias, livrarias, materiais de escritório e bazares.

Projetado para solucionar os gargalos operacionais da **Papelaria Real**, o sistema unifica controle de estoque em tempo real, automação de compras com cálculo de custo médio, emissão instantânea de cupons não-fiscais no PDV com atalhos de alta velocidade, geração autônoma de etiquetas de código de barras em SVG e relatórios executivos com curva ABC e DRE simplificado.

---

## 2. Destaques da Versão 2.0 (SalesOps Edition)

A Versão 2.0 representa um salto qualitativo estrutural, elevando o software do padrão acadêmico para o nível de mercado corporativo (*Enterprise SalesOps*):

1. **Design System SalesOps:**
   - Paleta institucional verde `#284936`, grafite `#222d31` e verde esmeralda `#6ae49b`.
   - **Sidebar Retrátil Inteligente (260px $\leftrightarrow$ 72px):** Alternância fluida com persistência no `localStorage` e script síncrono **Anti-FOUC** no `<head>`, eliminando saltos de layout na carga de página.
   - **Topbar com Avatar Dinâmico e Popover Soberano:** Iniciais `AD`/`CX` com menu flutuante em `z-index: 99999 !important`.
   - **Tabelas Padronizadas (.so-table):** Live Search instantâneo em JavaScript, menu de ações de 3 pontos (`.so-actions-btn`) e paginação institucional verde.

2. **PDV de Alta Performance e Ergonomia:**
   - **Atalhos Globais de Teclado:** `F2` (Bipe/Foco rápido), `F4` (Consulta de Produtos), `F8` (Concluir Venda/Pagamento), `F9` (Descontos) e `ESC` (Cancelar/Fechar).
   - **Web Audio API Nativa:** Síntese sonora senoidal em **880Hz** no bipe do scanner ótico, sem requisições de áudio externo.
   - **Modal de Troco Dinâmico:** Botões de cédula rápida (R$ 10, R$ 20, R$ 50, R$ 100, R$ 200, Valor Exato) e cálculo de troco reativo em milissegundos.
   - **Bloqueio Pessimista de Concorrência:** `SELECT ... FOR UPDATE` no banco de dados para eliminar qualquer risco de *race condition* ou venda com estoque furado.

3. **Módulo Vetorial Autônomo de Etiquetas SVG:**
   - Renderização nativa de códigos de barras **Code-128 (B)** e **EAN-13** diretamente em SVG puro (`inc/barcode_helper.php`), sem dependência de internet ou bibliotecas pesadas de terceiros.
   - Folhas de impressão A4 e etiquetas de gôndola formatadas via CSS `@media print`.

4. **Hardening OWASP e Arquitetura Híbrida:**
   - 100% de consultas em Prepared Statements PDO com emulação desativada (`ATTR_EMULATE_PREPARES => false`).
   - Proteção CSRF com `hash_equals()` e cookies de sessão blindados (`HttpOnly`, `SameSite=Lax`, `use_strict_mode=1`).
   - Suporte híbrido automático: detecção transparente entre ambiente local (XAMPP `http://localhost/mrstock/`) e produção na nuvem (ProFreeHost `http://mrstock.unaux.com/`).

---

## 3. Ficha Técnica do Projeto

| Propriedade | Detalhes da Especificação |
| :--- | :--- |
| **Curso / Habilitação** | Técnico em Desenvolvimento de Sistemas |
| **Instituição de Ensino** | ETEC Fernando Prestes — Sorocaba/SP |
| **Equipe de Engenharia** | Douglas, Cesar, Eduardo, Enzo, Nikolas (Equipe *Mr. Coding*) |
| **Orientação Acadêmica** | Prof. Luiz Flávio & Prof. Vinicius |
| **Cliente Referência** | Papelaria Real (Sueli & Osnir) |
| **Engine de Banco de Dados** | MySQL 8.0+ / MariaDB 10.4+ (`InnoDB`, `utf8mb4_general_ci`) |
| **Linguagem & Runtime** | PHP 8.2+ (Tipagem estrita, sem recursos obsoletos) |
| **Ambientes Homologados** | Local: XAMPP (`localhost/mrstock/`) \| Nuvem: ProFreeHost (`mrstock.unaux.com`) |

---

## 4. Arquitetura de Permissões (RBAC Estrito)

```
Perfil Administrador (admin)
  ├── 📊 Dashboard Geral: KPIs de faturamento, fita de alertas e venda rápida
  ├── 📦 Catálogo de Produtos: CRUD, badges de estoque mínimo, custo/venda e markup
  ├── 🏷️ Gerador de Etiquetas: Emissão em lote de códigos de barras SVG (A4 / Térmica)
  ├── 📂 Categorias de Produtos: Classificação e taxonomia mercadológica
  ├── 📈 Movimentações de Estoque: Livro-razão de entradas, saídas, perdas e ajustes
  ├── 👥 Clientes: Cadastro completo com CPF/CNPJ, limite de crédito e WhatsApp direto
  ├── 🚚 Fornecedores: Gestão de parceiros comerciais e link da API WhatsApp
  ├── 🛒 Compras e Entradas: Registro de pedidos, contas a pagar e atualização de saldo
  ├── 💳 Ponto de Venda (PDV): Checkout com atalhos F2-F9, Web Audio API e troco dinâmico
  ├── 📑 Histórico de Vendas: Filtros de período, cancelamentos, KPIs e reimpressão de cupom
  ├── 🖨️ Relatórios Gerenciais: Exportação PDF térmico e planilha Excel padronizada
  └── 💡 Centro de Inteligência (BI): Curva ABC, margens brutas e gráficos Chart.js

Perfil Caixa (caixa)
  └── 💳 PDV Completo: Frente de caixa e emissão de cupons não-fiscais
       ├── Consulta rápida de estoque e bipe por scanner
       └── Bloqueio Automático (RBAC): Redirecionamento seguro ao tentar acessar rotas administrativas
```

---

## 5. Matriz de Módulos e Tecnologias

| Módulo | Arquivos Principais | Inovações Técnicas da v2.0 |
| :--- | :--- | :--- |
| **Autenticação & Segurança** | `login.php`, `config.php`, `inc/auth.php` | Bcrypt com cost 12, CSRF token por sessão, isolamento de rota RBAC, cookies `SameSite=Lax` e `HttpOnly`. |
| **Layout & Navegação** | `inc/header.php`, `inc/footer.php`, `css/style.css` | Sidebar 260px/72px Anti-FOUC, popover com `z-index: 99999`, tabelas `.so-table` e paginação verde `#284936`. |
| **PDV & Checkout** | `vendas/pdv.php`, `vendas/functions.php` | Atalhos `F2`-`F9`, síntese sonora Web Audio API (880Hz), modal de troco dinâmico e `SELECT ... FOR UPDATE`. |
| **Etiquetas Térmicas/A4** | `produtos/etiquetas.php`, `inc/barcode_helper.php` | Algoritmo autônomo puro em PHP para renderização vetorial SVG de Code-128 e EAN-13 sem internet. |
| **Produtos & Estoque** | `produtos/index.php`, `produtos/functions.php` | Live Search instantâneo, menu flutuante de 3 pontos, badges semânticos e controle de markup. |
| **Movimentações** | `produtos/movimentacoes.php` | Rastreamento auditável de 5 tipos de fluxo (`entrada_compra`, `saida_venda`, `devolucao_cliente`, `devolucao_fornecedor`, `perda`). |
| **Compras & Fornecedores** | `compras/index.php`, `fornecedores/index.php` | Vínculo dinâmico de notas, baixa de contas a pagar (`PAGA`/`PENDENTE`) e link direto WhatsApp API (`wa.me`). |
| **Histórico & Cupons** | `vendas/historico.php`, `vendas/cupom.php` | Reimpressão térmica de 80mm/58mm com hash SHA-256 e visualização de KPIs de ticket médio. |
| **Inteligência Comercial** | `relatorios/analise.php`, `relatorios/excel.php` | Análise temporal (7 dias, mês atual, 12 meses), margem bruta e exportação XLS sem colunas fantasmas. |

---

## 6. Credenciais de Acesso Homologadas

| Perfil | Usuário | Senha | Rota de Destino | Permissões |
| :--- | :--- | :--- | :--- | :--- |
| **Administrador** | `admin` | `admin` | `dashboard.php` | Acesso Irrestrito (Todas as funções) |
| **Operador de Caixa** | `caixa` | `caixa` | `vendas/pdv.php` | Frente de Caixa / PDV Exclusivo |

---

## 7. Roteiro Rápido de Demonstração para Banca

1. **Abertura & Login:** Autentique como `admin` e demonstre o dashboard com fita de alertas e KPIs SalesOps.
2. **Ergonomia do PDV:** Abra o PDV, utilize o atalho `F2` para bipe automático (ouvindo o som senoidal de 880Hz), pressione `F8` para abrir o modal de troco e pague com atalho de cédula rápida de R$ 50.
3. **Cupom & Integridade:** Mostre a geração instantânea do cupom fiscal com hash de segurança e a baixa automática no estoque com bloqueio pessimista.
4. **Módulo de Etiquetas:** Navegue até `produtos/etiquetas.php`, selecione itens e abra o preview de impressão térmica/A4 com SVG vetorial.
5. **Barreira de Segurança (RBAC):** Faça logout, entre como `caixa`, tente acessar `/relatorios/analise.php` e comprove o redirecionamento defensivo.