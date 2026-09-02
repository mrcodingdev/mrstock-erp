# 🎓 Manual de Homologação & Revisão Final do TCC
**MrStock ERP v2.1.0** — Banca Examinadora da ETEC Fernando Prestes

---

## 1. Divisão Estratégica da Equipe na Apresentação

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                   DIVISÃO DE PAPÉIS & RESPONSABILIDADES NA BANCA                       │
├───────────────────┬───────────────────────────────┬────────────────────────────────────┤
│ Membro da Equipe  │ Especialidade / Foco          │ Ponto Alto da Fala                 │
├───────────────────┼───────────────────────────────┼────────────────────────────────────┤
│ 👨‍💻 Douglas        │ Direção Técnica & Arquitetura │ Transações ACID, PDV, Segurança    │
│ 🗄️ Nikolas        │ Banco de Dados & DER          │ Modelagem 14 Tabelas, Índices, CMP │
│ 👔 Cesar          │ Cliente & Requisitos de Varejo│ Papelaria Real, Dores do Negócio   │
│ 📄 Enzo           │ Documentação & Normas ABNT    │ PRDs, Relatórios e Metodologia     │
│ 🎤 Sugahara       │ Navegação & Demonstração Live │ Operação do PDV, Atalhos e BI      │
└───────────────────┴───────────────────────────────┴────────────────────────────────────┘
```

---

## 2. Estrutura dos 15 Minutos de Apresentação

1. **Minutos 0 a 3 (Cesar):** Apresentação da Papelaria Real (Sueli & Osnir), contexto de mercado, gargalos de estoque e proposta de valor.
2. **Minutos 3 a 6 (Nikolas & Enzo):** Arquitetura relacional (14 tabelas), Dicionário de Dados, integridade referencial e documentação formal.
3. **Minutos 6 a 12 (Sugahara & Douglas):** Demonstração ao vivo no navegador (Three-Tier Smoke Testing):
   - Tier 1: Venda rápida no PDV com bipagem sonora, desconto e emissão de cupom NFC-e.
   - Tier 2: Bloqueio de segurança RBAC (Caixa tentando ver custos/relatórios) e troca de senha BCrypt.
   - Tier 3: Apuração do DRE Gerencial, Curva ABC e Backup SQL em 1-clique.
4. **Minutos 12 a 15 (Douglas):** Conclusão, métricas Core Web Vitals (LCP 0.38s), trabalhos futuros (v3.0 Laravel) e abertura para a banca.
