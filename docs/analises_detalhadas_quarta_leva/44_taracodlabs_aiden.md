# 📘 Relatório Técnico de Engenharia & Arquitetura de Software: taracodlabs/aiden

**Data e Horário da Análise:** 07 de Setembro de 2026, 17:15 BRT  
**Repositório Oficial:** [taracodlabs/aiden](https://github.com/taracodlabs/aiden)  
**Autor / Organização:** Taracod Labs (White Lotus)  
**Popularidade & Relevância:** 810 Stars · Motor de Trabalho Autônomo com Prova de Execução e Tolerância a Falhas  
**Status no MrStock ERP:** `NOVO (Paradigma de Proof & Evidence over Declared Done)`  

---

## 🎯 1. Objetivo Primário da Ferramenta
Prover um motor de execução autônoma orientado a metas que opera o computador (arquivos, terminal, navegador via Playwright, APIs e serviços conectados) com um ciclo determinístico de 10 estágios: `Goal → Job → Plan & Claims → Attempt → Effect → Approval → Execution → Evidence → Verification → Verdict → Proof`.

---

## 🧠 2. Resumo Técnico Denso (Contexto de Alta Densidade para Agentes de IA)
O `aiden` resolve uma das maiores falhas de agentes de codificação: assumir que um comando terminando com `exit code 0` ou um modelo dizendo "pronto" significa que a tarefa foi cumprida com sucesso.
1. **O Princípio "Proof & Evidence over Declared Done":** O sistema separa a tentativa de execução da prova real. Uma tarefa só é considerada concluída se houver um artefato de *Evidence* (evidência durável, screenshot, log de teste com asserção real ou checagem de integridade) validado por um veredicto.
2. **Mecanismo de Recuperação de Erro TCE (Transient / Context / Execution):**
   - *Transient Errors:* Falhas de rede, timeouts ou bloqueios temporários com retry exponencial e jitter.
   - *Context Errors:* Saturação de tokens resolvida com compactação de janela e pruning cirúrgico.
   - *Execution Errors:* Quebras de compilação/sintaxe resolvidas com tentativas alternativas e isolamento de dependências.
3. **Níveis Escalonados de Aprovação (*Risk-Tiered Approvals*):** Operações de leitura e testes rodam de forma autônoma; operações de escrita, exclusão ou gastos de rede escalam para controle humano com geração de travas (*generation fencing*).
4. **Armazenamento e Memória Local:** SQLite com extensão FTS5 para recuperação textual sem depender de nuvens pagas.

---

## 📦 3. Inventário de Componentes Nativos do Repositório
* **Runtime Core:** Node.js 20/22 ESM com empacotamento via esbuild (`aiden-runtime`).
* **Automação de Navegador:** Playwright / Chromium headless para validações de interface web.
* **Sandbox:** Suporte a Docker e isolamento de processos.
* **Canais de Notificação:** Webhooks, Telegram, Slack, Discord e Email.

---

## 🔍 4. Diagnóstico de Uso no MrStock ERP (Atual vs. Oportunidade)
* **O que já usávamos:** No MrStock, a Regra #18 do `GEMINI.md` já estabelece o *Protocolo Anti-Falso Positivo & Validação Empírica Rigorosa* ("Código Escrito NÃO Significa Código Executado").
* **O que é novidade:** A formalização da cadeia de 10 passos e a exigência de **Proof Bundle** (pacote de evidências). No MrStock, podemos exigir que todo subagente Worker anexe o hash do commit e o log de saída do `php -l` e do teste de unidade como prova mandatória no relatório de entrega.

---

## 💎 5. Princípios Absorvíveis & Moldagem ao Varejo (Papelaria Real)
1. **Fechamento de Caixa com Veredicto Real:** No PDV, o operador de caixa não pode apenas "clicar em fechar". O sistema deve reconciliar as formas de pagamento (dinheiro, PIX, cartão) gerando a evidência de soma física antes de gravar o status no `mrstock_db`.
2. **Defesa Ativa contra Falsos Sucessos:** Se um script de migração SQL rodar, o MrStock deve inspecionar o schema pós-execução para provar que a coluna/tabela realmente existe.

---

## 🛠️ 6. Metodologias Deriváveis e Melhorias no MrStock ERP
* **Aprimoramento do Protocolo de Gatekeepers:** Subagentes Verifiers (`@code-reviewer` e `@test-engineer`) adotam a exigência formal do artefato de *Proof* antes de assinar a aprovação `[ 🟢 PASS ]`.
