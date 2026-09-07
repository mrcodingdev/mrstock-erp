# 📘 Relatório Técnico de Engenharia & Arquitetura de Software: FlashML-org/FreeToken

**Data e Horário da Análise:** 07 de Setembro de 2026, 15:46 BRT  
**Repositório Oficial:** [FlashML-org/FreeToken](https://github.com/FlashML-org/FreeToken)  
**Artigo Científico:** [arXiv:2608.16157](https://arxiv.org/abs/2608.16157) — *FreeToken: Efficient Edge-Native MoE Serving with Bandwidth-Adaptive Execution*  
**Autor / Organização:** FlashML Org (UC Berkeley, MIT, Stanford, Shuo Yang, Matei Zaharia, Ion Stoica)  
**Popularidade & Relevância:** Engine Revolucionária de Inferência Local para Modelos MoE de Fronteira (290B+) em PCs Comuns  
**Status no MrStock ERP:** `NOVO (Infraestrutura de Contingência Offline & Semantic-Aware Caching)`  

---

## 🎯 1. Objetivo Primário da Ferramenta
Permitir a execução local e eficiente de modelos abertos gigantes de arquitetura Mixture-of-Experts (MoE) — como DeepSeek-V4-Flash, Qwen3.6-35B-A3B e GLM-5.2 — em computadores pessoais e notebooks gamers comuns equipados com GPUs NVIDIA RTX (séries 30, 40 e 50), com velocidade interativa ultrarrápida, custo zero de tokens e total privacidade.

---

## 🧠 2. Resumo Técnico Denso (Contexto de Alta Densidade para Agentes de IA)
O `FreeToken` rompe a barreira de hardware para inteligência de data-center no desktop pessoal:
1. **Co-Execução Adaptativa CPU-GPU (Política $q^\star$):** Supera o gargalo de largura de banda do barramento PCIe distribuindo dinamicamente as camadas de cálculo entre memória RAM do computador e VRAM da placa de vídeo.
2. **Semantic-Aware Caching (Cache KV com Âncoras Semânticas):** Cria checkpoints de ancoragem no estado recorrente da atenção. Quando agentes de código realizam edições no contexto (ex: blocos de raciocínio `<thinking>`, saídas de ferramentas ou novos diffs), o FreeToken evita recalcular todo o histórico, reaproveitando o cache de tokens prévios.
3. **Gestão Elástica de Memória:** Realocação dinâmica de VRAM entre o cache de experts MoE e a memória de contexto KV sem necessidade de reiniciar a engine ou recarregar pesos do modelo.
4. **Interface Padronizada OpenAI / Anthropic:** Expõe endpoints HTTP locais compatíveis que podem ser plugados diretamente em Claude Code, Cursor, Antigravity CLI e OpenCode.

---

## 📦 3. Inventário de Componentes Nativos do Repositório
* **Runtime:** Core acelerado em C++ / CUDA integrado ao ecossistema vLLM, SGLang e FlashInfer.
* **Formatos Suportados:** FTW (Fast Weight Format), quantizações modernas MXFP4, NVFP4, FP8 e BF16.
* **Aplicações:** Aplicativo Desktop executável com painel de controle para Windows e Linux, além de biblioteca CLI via `uv pip install "freetoken[accel]"`.

---

## 🔍 4. Diagnóstico de Uso no MrStock ERP (Atual vs. Oportunidade)
* **O que já usávamos:** Desenvolvemos com XAMPP local e deploy contínuo em produção na Hostinger, utilizando modelos remotos via API.
* **O que é novidade:** O FreeToken viabiliza um ambiente de desenvolvimento e auditoria **100% autônomo e offline** para a equipe da ETEC. Em caso de falta de internet ou esgotamento de cotas de APIs comerciais, a equipe pode rodar modelos MoE de alta capacidade na própria máquina local, mantendo o pipeline de subagentes ativo.

---

## 💎 5. Princípios Absorvíveis & Moldagem ao Varejo (Papelaria Real)
1. **Princípio da Ancoragem de Contexto (KV Cache Optimization):** Posicionar diretrizes estáticas (como regras do GEMINI.md e esquema de banco) sempre no topo das instruções, garantindo máximo aproveitamento de cache de prompt e reduzindo latência.

---

## 🛠️ 6. Metodologias Deriváveis e Melhorias no MrStock ERP
* **Plano de Contingência Offline para Demonstração na Banca:** Garantir que o ambiente local do MrStock possua documentação clara de como operar sem conectividade externa.