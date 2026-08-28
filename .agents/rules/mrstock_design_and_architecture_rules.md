---
trigger: always_on
description: "Diretrizes estritas de UI/UX, Design System, Topbar, WhatsApp, Hospedagem e Fluxo de Desenvolvimento do MrStock ERP"
---

# Diretrizes Mandatórias do MrStock ERP v2.0

## 1. Padronização Global de Botões Sólidos (Design System)
- **Cores Sólidas de Fábrica:** Todos os botões de ação (`.btn-danger`, `.btn-success`, `.btn-primary`, `.btn-secondary`, `.btn-warning`, `.btn-dark`) DEVEM possuir preenchimento de cor sólida fixa e texto/ícone em branco.
- **Proibido Outline em Botões Principais:** NUNCA utilizar botões com fundo transparente e borda colorida (`btn-outline-*`) para ações principais ou de perigo.
- **Comportamento Hover:** Ao passar o mouse (`:hover`), o botão NUNCA deve inverter cores ou mudar para transparente; ele deve apenas ESCURECER suavemente com leve sombra.
- **Integridade do style.css:** O arquivo `css/style.css` deve sempre manter o bloco completo de 1.300 linhas com a seção `PADRONIZAÇÃO GLOBAL DE BOTÕES SÓLIDOS (MRSTOCK DESIGN SYSTEM)` e nunca ser truncado.

## 2. Topbar e Título de Página Limpos
- **Título Direto:** A barra superior (`inc/header.php`) deve exibir EXCLUSIVAMENTE o nome limpo da página atual (ex: `Histórico de Vendas`, `Estoque & Produtos`, `Dashboard`, `Gestão de Fornecedores`).
- **Sem Prefixos ou Badges:** NUNCA incluir o prefixo redundante `MrStock ERP - ` no texto visível da topbar nem exibir o badge `[• ERP Ativo]`.

## 3. Botão de WhatsApp Circular Padronizado
- **Visual Circular:** Em todas as tabelas e listagens (`clientes/index.php`, `fornecedores/index.php`, etc.), o botão de contato do WhatsApp DEVE ser um botão circular verde oficial (`.btn-whatsapp` com `border-radius: 50%`, 22x22px e ícone branco `<i class="fab fa-whatsapp"></i>`).
- **Alinhamento:** O número de telefone deve ser exibido em texto limpo com ícone discreto, e o botão circular do WhatsApp posicionado ao lado. NUNCA utilizar pílulas esticadas com o número dentro.

## 4. Classificação de Categorias por Famílias Funcionais de Produtos
- **Micro-Categorias:** As categorias no `mrstock_db` representam Famílias Específicas de Produtos da Papelaria Real (`Cadernos & Blocos`, `Canetas & Marcadores`, `Lápis & Apontadores`, `Borrachas & Correção`, `Colas & Fitas Adesivas`, `Papéis & Folhas`, `Pastas & Organização`, `Corte & Medição`, `Tintas & Pintura`, `Grampeadores & Fixação`).
- **NÃO usar Macro-Categorias:** NUNCA regredir para macro-categorias genéricas como apenas "Escolar" ou "Escritório".

## 5. Fluxo de Desenvolvimento Local, Nuvem e Preservação do MrStockBackup
- **Ambiente de Desenvolvimento Ativo:** Todas as alterações de código, novos recursos e testes no XAMPP devem ser executados EXCLUSIVAMENTE em `C:\xampp\htdocs\MrStock\`.
- **MrStockBackup como Ponto de Restauração e Comparação Visual:** A pasta `C:\xampp\htdocs\MrStockBackup\` DEVE permanecer intacta como a versão estável anterior ("Save Point"). O desenvolvedor utiliza ambas as instâncias abertas no navegador (`http://localhost/MrStock/` vs `http://localhost/MrStockBackup/`) para comparar visualmente as diferenças e validar antes de aprovar.
- **Sincronização / Git Checkpoint sob Confirmação:** O `MrStockBackup` NUNCA deve ser sobrescrito automaticamente durante a fase de desenvolvimento/testes, apenas quando o usuário validar o resultado visualmente e solicitar o salvamento da versão.
- **Ambiente de Produção na Nuvem:** O sistema de produção final é hospedado na web (atualmente ProFreeHost em `https://mrstock.unaux.com/`, com migração futura para a Hostinger). O ambiente local XAMPP serve para testes, desenvolvimento e contingência em caso de queda de internet.

## 6. Protocolo de Compilação de Documentos e PDFs (Edge Headless)
- **Validação Prévia do HTML:** Antes de invocar o comando do Microsoft Edge headless (`--headless --print-to-pdf`), o arquivo HTML de origem deve ser gravado e ter sua existência validada no disco.
- **Liberação de Fluxo:** Aguardar a finalização síncrona da escrita do arquivo PDF antes de efetuar cópias ou abrir o documento, prevenindo que páginas de erro de navegação sejam impressas no documento final.

## 7. Estratégia Arquitetural para o TCC (PHP Nativo vs. Laravel)
- **Sistema Oficial do TCC:** O produto oficial final entregue e demonstrado para a banca examinadora da ETEC permanece em PHP 8.2 Nativo / PDO / MySQL.
- **Roadmap de Evolução:** A migração para o framework Laravel é posicionada como Trabalhos Futuros / Evolução da Arquitetura (Versão 3.0) na documentação escrita e nos slides da apresentação.

## 8. Consenso e Alinhamento do Grupo de TCC (Matriz das 50 Perguntas)
- **Hospedagem & Nuvem:** Hostinger em produção com SSL/HTTPS obrigatório e deploy via cPanel/FTP. XAMPP local permanece como contingência operacional.
- **UI Limpa e Sem Fotos de Produtos:** O sistema NÃO utilizará fotos pesadas para cada produto, exibindo apenas o logotipo oficial da Papelaria Real para garantir velocidade máxima e interface profissional.
- **Operação do PDV & NFC-e:** Digitação ágil de quantidade via teclado/mouse, CPF opcional e Simulação Acadêmica de NFC-e com QR Code (chancelada oficialmente pelo orientador Prof. Vinicius).
- **Perfis de Acesso (RBAC):** Administrador (acesso irrestrito) e Caixa (PDV e consulta rápida; Caixa NUNCA visualiza preço de custo/compra nem lucro).
- **Travas Financeiras:** Alerta visual no Dashboard para produtos vencendo em até 30 dias, cálculo de Lucro Bruto Real e trava de aviso para vendas com prejuízo.
- **Apresentação na Banca:** Demonstração ao vivo no navegador com vídeo gravado de 3 minutos de contingência. Divisão de papéis: Nikolas (Banco/DER), Cesar (Cliente/Requisitos), Enzo (Documentação), Sugahara (Apresentador da Navegação do Sistema - treinado por Douglas) e Douglas (Direção Técnica e Arquitetura).

## 9. Atalhos de Comandos Rápidos de Skills (/Slash Commands Customizados)
Sempre que o usuário digitar comandos com barra no chat, o assistente deve executar a skill/script correspondente imediatamente:
- `/ui-ux-pro-max [busca ou termo]` -> Executa o script do motor `ui-ux-pro-max` (`python "G:\Meu Drive\TCC_MrStock\.agents\skills\ui-ux-pro-max\scripts\search.py" "<busca>"`) para gerar diretrizes de design, paleta de cores e regras UX.
- `/design-system [estilo/requisito]` -> Ativa a skill `design-system` gerando tokens de cores, tipografia e especificações de componentes.
- `/brand [tema/perfil]` -> Ativa a skill `brand` definindo diretrizes de identidade de marca e tom de voz.
- `/slides [tema]` -> Ativa a skill `slides` para estruturar apresentações em HTML/Chart.js para a banca da ETEC.
- `/banner [plataforma/estilo]` -> Ativa a skill `banner-design` para gerar layouts visuais e artes.
- `/spec [recurso]` -> Ativa a skill `spec-driven-development` para criar especificações técnicas formais antes de programar.
- `/planning [tarefa]` -> Ativa a skill `planning-and-task-breakdown` para decompor demandas em tarefas pequenas e atômicas.
- `/build [fatia]` -> Ativa a skill `incremental-implementation` para construir o código passo a passo.
- `/test [cenário]` -> Ativa a skill `test-driven-development` para gerar e rodar testes de prova.
- `/review` -> Ativa a skill `code-review-and-quality` para executar auditoria em 5 eixos antes de atualizar o MrStockBackup.
- `/code-simplify` -> Ativa a skill `code-simplification` para enxugar e limpar código sem alterar comportamento.
- `/webperf [tela]` -> Ativa o subagente `web-performance-auditor` para auditar Core Web Vitals e tempo de carregamento.
- `/ship` -> Ativa a skill `shipping-and-launch` para checklist de pré-lançamento e deploy seguro.

## 10. Protocolo de Instalação e Indexação de Novas Skills e Plugins
- **Estrutura Dupla Obrigatória:** Toda nova skill adicionada deve ser gravada simultaneamente em:
  1. `G:\Meu Drive\TCC_MrStock\.agents\skills\<nome-da-skill>\` (para persistência no workspace do Google Drive).
  2. `C:\Users\Douglas\.gemini\config\plugins\<plugin-name>\skills\` com `plugin.json` e `installed_version.json` (para registro no sistema da IDE).
- **Aviso Obrigatório de Reinício para Autocomplete:** O assistente deve sempre informar proativamente ao usuário que, embora a execução via prompt seja imediata, o menu visual de autocompletar da barra (`/`) só exibirá o novo comando após a reinicialização/recarregamento do Antigravity.

## 11. Protocolo de Memória Global e Consciência Permanente de Sessões
- **Fonte Única da Verdade:** Toda nova sessão do Antigravity iniciada no repositório `G:\Meu Drive\TCC_MrStock\` compartilha e absorve a Memória Global do projeto registrada em `GLOBAL_BRAIN_MANIFEST.md` e `07_Megabrain_Obsidian/MEMORIA_GLOBAL_MRSTOCK.md`.
- **Consciência do Ecossistema:** O assistente deve sempre reconhecer a existência dos 5 subagentes especialistas (`chief-erp-architect`, `code-reviewer`, `security-auditor`, `test-engineer`, `web-performance-auditor`), das 41+ skills instaladas e do acervo completo de Livros Didáticos (`03_Livros_Didaticos/`) e Pareceres Técnicos (`04_Analises_e_Estudos_Tecnicos/`).

## 12. Ativação Proativa de Ferramentas & Máxima Profundidade Técnica (Uso Pleno da Cota Pro)
- **Consulta Mental e Ativação Proativa:** Em TODA interação, o assistente DEVE analisar proativamente se existem skills, subagentes ou diretrizes do projeto aplicáveis à solicitação do usuário e acioná-los/citá-los proativamente sem depender de lembretes manuais.
- **Proibição de Respostas Superficiais:** O usuário possui assinatura Gemini Pro com ampla folga de quota semanal. O assistente NUNCA deve economizar tokens ou resumir explicações essenciais; deve sempre priorizar respostas completas, aprofundadas, didáticas, com exemplos de código, tabelas comparativas e fundamentação técnica rigorosa.

## 13. Protocolo Mandatório de Git Commits Atômicos & Semânticos (Conventional Commits)
- **Commit Imediato ao Final de Toda Alteração:** Ao concluir qualquer alteração de código, refinamento visual, correção de bug, ajuste em documentação ou nova funcionalidade (e validar sua integridade/sintaxe), o assistente DEVE executar IMEDIATAMENTE um `git commit` semântico no repositório ativo (`C:\xampp\htdocs\MrStock\` e manter sincronizado com `G:\Meu Drive\TCC_MrStock\`).
- **Padrão Semântico Obrigatório (Conventional Commits):**
  - `feat(<escopo>): <descrição>` -> Novos recursos ou páginas (ex: `feat(pdv): ...`).
  - `fix(<escopo>): <descrição>` -> Correções de bugs, travas e lógica (ex: `fix(estoque): ...`).
  - `style(<escopo>): <descrição>` -> Ajustes de design, CSS, espaçamentos e UI (ex: `style(css): ...`).
  - `refactor(<escopo>): <descrição>` -> Melhorias e limpeza de código sem alterar comportamento (ex: `refactor(auth): ...`).
  - `docs(<escopo>): <descrição>` -> Atualizações de documentação, manuais, PRDs e notas técnicas (ex: `docs(readme): ...`).
  - `test(<escopo>): <descrição>` -> Baterias de testes de prova real e auditorias (ex: `test(qa): ...`).
- **Árvore de Trabalho Sempre Limpa:** O assistente deve garantir que `git status` permaneça limpo (`working tree clean`) após cada ciclo de entrega para que o repositório esteja sempre pronto para `git push`.


