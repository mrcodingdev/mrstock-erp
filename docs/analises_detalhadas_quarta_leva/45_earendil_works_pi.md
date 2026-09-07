# 📘 Relatório Técnico de Engenharia & Arquitetura de Software: earendil-works/pi

**Data e Horário da Análise:** 07 de Setembro de 2026, 17:16 BRT  
**Repositório Oficial:** [earendil-works/pi](https://github.com/earendil-works/pi)  
**Autor / Organização:** Earendil Works  
**Popularidade & Relevância:** 102.734 Stars · O Maior Toolkit de Harness de Agentes de Código e TUI  
**Status no MrStock ERP:** `NOVO (Referência de Arquitetura Modular & Supply-Chain Hardening)`  

---

## 🎯 1. Objetivo Primário da Ferramenta
Prover um ecossistema completo de harness de agentes de código autônomos e extensíveis (`pi.dev`), combinando runtime de execução (`@earendil-works/pi-agent-core`), interface terminal TUI com renderização diferencial (`@earendil-works/pi-tui`), cliente CLI interativo e barramento de telemetria neutro.

---

## 🧠 2. Resumo Técnico Denso (Contexto de Alta Densidade para Agentes de IA)
O `pi` é o harness de maior escala da comunidade open source:
1. **Arquitetura em Camadas Desacopladas:**
   - `pi-ai`: Camada de abstração que unifica chamadas de LLM para Google Gemini, Anthropic, OpenAI e modelos locais.
   - `pi-agent-core`: Máquina de estados finitos que gerencia o loop de raciocínio, tool calling e persistência de sessões.
   - `pi-tui`: Motor de terminal com renderização diferencial (atualiza apenas os caracteres alterados na tela, eliminando flickering e lag de terminal).
   - `chord`: Runtime de composição com replicação de estado e RPC para comunicação distribuída.
2. **Blindagem Extrema de Cadeia de Suprimentos (Supply-Chain Hardening):**
   - `save-exact=true` no `.npmrc` e `min-release-age=2` dias: impede que pacotes recém-publicados (com risco de injeção de malware de dia zero) sejam baixados automaticamente.
   - Shrinkwrap estrito (`npm-shrinkwrap.json`) com hash SHA-256 e proibição de scripts de ciclo de vida (`--ignore-scripts`).
3. **Padrões de Sandboxing Gradual:**
   - Suporte a isolamento em micro-VMs Linux locais (Gondolin), containers Docker convencionais e OpenShell sandbox.

---

## 📦 3. Inventário de Componentes Nativos do Repositório
* **CLI:** `@earendil-works/pi-coding-agent`.
* **Motor TUI:** `@earendil-works/pi-tui`.
* **Telemetry Schema:** `@earendil-works/pi-telemetry`.
* **Scripts de Release Segura:** Pipeline de build offline com verificação determinística de checksum.

---

## 🔍 4. Diagnóstico de Uso no MrStock ERP (Atual vs. Oportunidade)
* **O que já usávamos:** Usamos o padrão de isolamento com XAMPP local e regras estritas de não alteração de arquivos de terceiros.
* **O que é novidade:** O conceito de **Supply-Chain Hardening**. No MrStock ERP, as bibliotecas frontend (Bootstrap, FontAwesome, Chart.js) devem ser mantidas locais em `css/` e `js/`, travadas em versões exatas sem depender de CDNs externas sujeitas a sequestro de DNS.

---

## 💎 5. Princípios Absorvíveis & Moldagem ao Varejo (Papelaria Real)
1. **Imunidade a Quedas de Conexão no PDV:** O caixa da Papelaria Real deve operar com zero dependências externas em tempo de execução; todas as fontes, scripts e ícones residem estritamente no disco local do servidor XAMPP.
2. **Integridade de Dependências de Software:** Nenhuma biblioteca de terceiros deve ser instalada sem validação de hash e inspeção de código fonte.

---

## 🛠️ 6. Metodologias Deriváveis e Melhorias no MrStock ERP
* **Auditoria de Dependências Locais:** Inclusão no laudo do TCC da seção de blindagem de supply-chain comprovando que o MrStock opera de forma 100% autossuficiente no ambiente local da Papelaria Real.
