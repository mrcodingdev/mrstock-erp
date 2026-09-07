# 📘 Relatório Técnico de Engenharia & Arquitetura de Software: thedotmack/claude-mem

**Data e Horário da Análise:** 07 de Setembro de 2026, 15:40 BRT  
**Repositório Oficial:** [thedotmack/claude-mem](https://github.com/thedotmack/claude-mem) (Grok Mem)  
**Autor / Organização:** Alex Newman (thedotmack)  
**Popularidade & Relevância:** Framework Pioneiro de Memória Persistente Híbrida e Revelação Progressiva  
**Status no MrStock ERP:** `NOVO (Paradigma de Progressive Disclosure e Memória Persistente)`  

---

## 🎯 1. Objetivo Primário da Ferramenta
Prover memória persistente contínua entre sessões de chats e ferramentas de linha de comando para agentes de IA (Claude Code, Antigravity CLI, Cursor, OpenCode), mantendo o histórico de decisões arquiteturais, bugs corrigidos e notas operacionais através de um banco SQLite FTS5 e Chroma Vector DB, minimizando o consumo de tokens via **Revelação Progressiva em 3 Camadas**.

---

## 🧠 2. Resumo Técnico Denso (Contexto de Alta Densidade para Agentes de IA)
O `claude-mem` resolve o esquecimento crônico de agentes de código ao reiniciar a sessão:
1. **O Padrão Progressive Disclosure (Revelação Progressiva em 3 Camadas):**
   - **Camada 1 (`search`):** Retorna apenas um índice compacto com IDs e resumos de uma linha (~50 a 100 tokens por resultado).
   - **Camada 2 (`timeline`):** Permite inspecionar o contexto cronológico antes e depois de uma observação específica sem carregar todo o arquivo.
   - **Camada 3 (`get_observations`):** Busca os detalhes completos, diffs e arquivos afetados exclusivamente para os IDs pré-selecionados (~500 a 1.000 tokens por item).
   - *Impacto:* Redução drástica de até 90% na queima de tokens de contexto em projetos longos.
2. **Lifecycle Hooks de Agente:** Captura eventos em 5 pontos do ciclo de vida: `SessionStart`, `UserPromptSubmit`, `PostToolUse`, `Stop` e `SessionEnd`, indexando alterações sem exigir esforço manual do desenvolvedor.
3. **Privacidade e Proteção de Segredos:** Suporte nativo à tag `<private>...</private>` para impedir o armazenamento acidental de senhas de banco ou chaves de API nos registros de memória.
4. **Worker Local Desacoplado:** Executado sob runtime Bun/Node com API HTTP local e visualizador Web em tempo real.

---

## 📦 3. Inventário de Componentes Nativos do Repositório
* **MCP Tools:** `search`, `timeline`, `get_observations`, `remember`.
* **Banco de Dados:** SQLite 3 local com extensão FTS5 para busca textual + Chroma DB para busca semântica vetorial.
* **Interface:** Web Viewer embutido para navegação e auditoria visual da memória do projeto.
* **CLI Installer:** Scripts de bootstrap para Claude Code, Antigravity CLI e gateways OpenClaw.

---

## 🔍 4. Diagnóstico de Uso no MrStock ERP (Atual vs. Oportunidade)
* **O que já usávamos:** Mantemos o `GLOBAL_BRAIN_MANIFEST.md` e a pasta `07_Megabrain_Obsidian/MEMORIA_GLOBAL_MRSTOCK.md` como repositório documental estático.
* **O que é novidade:** O protocolo de recuperação em 3 camadas (*search* -> *timeline* -> *get_observations*). Podemos adotar essa exata filosofia na consulta aos logs contábeis do MrStock (`relatorios/logs.php`) e na gestão de contexto entre as sessões do Antigravity, evitando despejos massivos de texto desnecessários.

---

## 💎 5. Princípios Absorvíveis & Moldagem ao Varejo (Papelaria Real)
1. **Consulta Histórica no Caixa em 3 Camadas:** Ao auditar uma venda antiga na Papelaria Real, o PDV não precisa carregar todos os 50 itens da compra de uma vez; lista primeiro o cabeçalho (ID, data, valor total) e só carrega a composição do carrinho sob clique explícito.
2. **Proteção Anti-Vazamento (Regra #19 do GEMINI.md):** Utilizar a convenção de marcação de privacidade para garantir que credenciais de produção nunca transitem para históricos de chat.

---

## 🛠️ 6. Metodologias Deriváveis e Melhorias no MrStock ERP
* **Nova Metodologia Derivável — *Progressive Context PRIMING (PCP)*:** Estruturar as respostas e relatórios do assistente em camadas progressivas (Sumário Executivo -> Tabela de Itens -> Código Cirúrgico), otimizando a cota Pro e o foco do usuário.
* **Aprimoramento da Skill `metodo-fundido`:** Inserir instrução para os subagentes Verifiers realizarem inspeções pontuais por amostragem estruturada antes de lerem árvores de código gigantes.