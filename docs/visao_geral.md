# 🏢 MrStock ERP v2.1.0 — Visão Geral do Sistema
**Edição Oficial:** Papelaria Real (*Sueli & Osnir — Sorocaba/SP*)  
**Versão Atual:** `v2.1.0 Enterprise`  
**Data de Homologação:** 01 de Setembro de 2026  
**Instituição:** ETEC Fernando Prestes (Centro Paula Souza / Sorocaba-SP)  
**Curso Técnico:** Desenvolvimento de Sistemas (TCC Oficial)

---

## 1. Proposta de Valor e Contexto Comercial
O **MrStock ERP** é uma plataforma corporativa integrada de **Gestão Comercial, Controle Inteligente de Estoque, Frente de Caixa (PDV de Alta Velocidade), Emissão Fiscal e Inteligência Analítica (BI)** desenvolvida sob medida para o varejo de papelarias e suprimentos corporativos, tendo como estudo de caso e beneficiária a **Papelaria Real**.

A operação tradicional da papelaria enfrentava desafios críticos:
- Perda de vendas em horários de pico escolar devido à lentidão no atendimento de balcão.
- Imprecisão no cálculo de Custo Médio Ponderado (CMP) em compras recorrentes com preços oscilantes.
- Risco de concessão de descontos excessivos sem validação de margem de lucro.
- Falta de visibilidade sobre produtos perecíveis próximos do vencimento (colas, tintas, corretivos líquidos).
- Insegurança na segregação de funções entre o operador de caixa e a administração.

O MrStock ERP v2.1.0 resolve integralmente esses gargalos através de engenharia de software robusta, interface ágil e regras fiscais automatizadas.

---

## 2. Pilares Arquiteturais da Versão 2.1.0

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        ARQUITETURA MODULAR DO MRSTOCK ERP v2.1.0                       │
├──────────────────────────┬─────────────────────────────┬───────────────────────────────┤
│ 🛒 Frente de Caixa (PDV) │ 📦 Estoque & Inteligência   │ 💼 Gestão & Governança        │
├──────────────────────────┼─────────────────────────────┼───────────────────────────────┤
│ • Bipagem em < 15ms      │ • 10 Famílias Funcionais    │ • RBAC Estrito (Admin vs Caixa)│
│ • Web Audio API Offline  │ • Custo Médio Ponderado     │ • Transações ACID (FOR UPDATE)│
│ • Atalhos F1 a F9        │ • Shelf-Life Dinâmico (30d) │ • DRE Gerencial & Curva ABC   │
│ • Trava Margem Negativa  │ • Código 128B em SVG Nativo │ • Simulação NFC-e (SEFAZ-SP)  │
│ • Troco Centesimal Exato │ • Rastreamento de Perdas    │ • Backup SQL em 1-Clique      │
└──────────────────────────┴─────────────────────────────┴───────────────────────────────┘
```

---

## 3. Principais Diferenciais Competitivos

1. **Arquitetura 100% Offline (Resiliência Soberana):**
   - Em caso de queda do link de internet, o PDV e todas as operações locais continuam funcionando normalmente no XAMPP, sem dependência de CDNs externas.
2. **Zero-Config Switch (Desenvolvimento vs Nuvem):**
   - O arquivo `config.php` detecta dinamicamente se o acesso ocorre via `localhost` (XAMPP local) ou via domínio público (`mrstock.unaux.com`), alternando automaticamente as credenciais de banco e rotas de base.
3. **Design System SalesOps v0 & 14 Zonas Anti-Slop:**
   - Botões com preenchimento sólido de fábrica, tipografia tabular (`tabular-nums`), Bento Grid contemporâneo e conformidade total com acessibilidade WCAG 2.1 AA.
4. **Precisão Financeira Centesimal:**
   - Erradicação de inconsistências de ponto flutuante em JavaScript e colunas `DECIMAL(10,2)` no MySQL, garantindo que o troco e o total a pagar fechem com exatidão centesimal.
