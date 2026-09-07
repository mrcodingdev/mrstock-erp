# 📘 Relatório Técnico de Engenharia & Arquitetura de Software: snarktank/ralph

**Data e Horário da Análise:** 07 de Setembro de 2026, 17:18 BRT  
**Repositório Oficial:** [snarktank/ralph](https://github.com/snarktank/ralph)  
**Autor / Organização:** Ryan Carson / Snarktank (baseado no Ralph Pattern de Geoffrey Huntley)  
**Popularidade & Relevância:** 21.728 Stars · O Loop Autônomo mais Famoso para Execução de PRDs Item por Item  
**Status no MrStock ERP:** `NOVO (Paradigma de Fresh Context por Iteração & Persistência em Disco)`  

---

## 🎯 1. Objetivo Primário da Ferramenta
Executar planos de requisitos de produto complexos de forma 100% autônoma através de um loop contínuo onde **cada iteração é uma instância nova e limpa (Fresh Context)**, garantindo que o agente nunca sofra de fadiga de contexto, alucinações cumulativas ou poluição de memória.

---

## 🧠 2. Resumo Técnico Denso (Contexto de Alta Densidade para Agentes de IA)
O padrão **Ralph** é uma das maiores inovações pragmáticas da engenharia com LLMs:
1. **O Princípio de Fresh Context por Tarefa:**
   - Em vez de manter uma sessão de chat gigante com 50 turnos (que fica lenta, cara e propensa a alucinar), o loop encerra a instância do agente a cada história de usuário concluída.
   - A nova iteração acorda com contexto 100% limpo, lê o estado no disco e executa o próximo passo.
2. **A Tríade de Persistência no Disco:**
   - `prd.json`: A lista de histórias de usuário do requisito com status booleano (`passes: false` / `passes: true`).
   - `progress.txt`: Arquivo append-only onde cada iteração anota aprendizados, armadilhas encontradas e padrões descobertos.
   - `Git History`: O histórico de commits semânticos atômicos serve como a fonte viva da verdade do código já consolidado.
3. **Dimensionamento Atômico de Tarefas (*Right-Sized Stories*):**
   - O segredo do sucesso do Ralph reside em dividir o trabalho em fatias atômicas (ex: *"Criar migração da tabela"*, *"Criar componente visual de botão"*, *"Adicionar endpoint PHP com PDO"*), proibindo tarefas gigantes como *"Criar todo o sistema de vendas"*.
4. **Atualização Contínua de Diretrizes (`AGENTS.md`):**
   - Ao final de cada ciclo, se o agente descobriu uma convenção nova ou uma armadilha na aplicação, ele atualiza o arquivo de regras do repositório para que as próximas instâncias já acordem cientes da regra.
5. **Condição Determinística de Término:**
   - O loop só encerra quando 100% das histórias contidas no `prd.json` estiverem marcadas com `passes: true`, emitindo a promessa formal `<promise>COMPLETE</promise>`.

---

## 📦 3. Inventário de Componentes Nativos do Repositório
* **Loop Core:** Script shell `ralph.sh` com controle de iterações máximas.
* **Formatos de Estado:** `prd.json` e `progress.txt`.
* **Skills Integradas:** `/prd` (geração de requisitos) e `/ralph` (conversão estruturada para JSON).
* **Flowchart Interativo:** Visualizador animado em React dos passos do loop.

---

## 🔍 4. Diagnóstico de Uso no MrStock ERP (Atual vs. Oportunidade)
* **O que já usávamos:** No MrStock, a Regra #14 (Leis de Andrej Karpathy) e a Regra #16 já impõem tarefas atômicas e cirúrgicas.
* **O que é novidade:** O uso formal de um `progress.txt` e `prd.json` diretamente no repositório. Quando temos refatorações extensas no ERP, manter uma lista rastreável de histórias no disco garante que qualquer nova sessão retome o trabalho instantaneamente no ponto exato onde a anterior parou, sem perda de continuidade.

---

## 💎 5. Princípios Absorvíveis & Moldagem ao Varejo (Papelaria Real)
1. **Entregas Fatiadas por Família de Produtos:** Ao cadastrar ou importar as 10 famílias de produtos da Papelaria Real, o processamento deve ser fatiado lote a lote com persistência de status, impedindo que falhas em um item quebrem a importação inteira.
2. **Memória de Erros Operacionais:** Registrar no `progress.txt` do projeto os detalhes operacionais descobertos na entrevista presencial com os proprietários da Papelaria Real.

---

## 🛠️ 6. Metodologias Deriváveis e Melhorias no MrStock ERP
* **Adoção do Padrão Ralph no `implementation_plan.md`:** Estruturar os planos de implementação futuros com checkboxes estritas e rastreamento atômico de critérios de aceite, assegurando entregas determinísticas.
