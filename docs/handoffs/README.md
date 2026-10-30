# Handoff entre Codex e Antigravity

Protocolo aprovado pelo Douglas em 30/10/2026 para implementação cruzada e revisão independente no MrStock. As instruções atuais do usuário e os limites reais do ambiente prevalecem. Este arquivo define o formato dos pacotes; AGENTS.md e GEMINI.md apontam para ele.

## Responsabilidades e autorização

- Codex implementa preferencialmente as mudanças complexas. Antigravity revisa e valida no ambiente disponível. Ambos podem implementar e testar dentro do pedido autorizado; os papéis podem ser invertidos por tarefa.
- Em mudanças críticas, procurar revisão independente. Vendas, estoque, cancelamentos, RBAC, transações e migrações requerem validação compatível com o risco. Se a revisão ou um teste necessário estiver indisponível, registrar a pendência; não apresentar a alteração como validada nesse aspecto.
- Douglas define escopo e negócio. Commit, push, merge, deploy, alterações de banco e ações destrutivas precisam estar abrangidos pela autorização dele. A aprovação de um revisor não concede essa autorização.
- Somente um responsável edita cada arquivo de implementação por vez. O revisor lê a aplicação e escreve seu relatório/evidências. A execução de testes deve respeitar seus efeitos: escrita em banco ou serviços requer a autorização e o ambiente de testes apropriados.
- Subagentes são opcionais, escolhidos por benefício e risco; não há uma bateria fixa de seis revisores. Não presumir ferramentas, modelos, cotas, memória ou integrações disponíveis só porque foram descritos em documentação.
- Não abrir sessões adicionais do Codex pelo terminal nem iniciar loops entre serviços automaticamente. O acionamento entre Codex e Antigravity ocorre pelo prompt encaminhado pelo Douglas, salvo autorização específica posterior.

## Estrutura por tarefa

```text
docs/handoffs/AAAA-MM-DD-NN-descricao/
  entrega-r1.md
  alteracoes-r1.patch
  revisao-r1.md
  entrega-r2.md          # se houver correção após a revisão
  alteracoes-r2.patch
  revisao-r2.md
```

O implementador escreve entrega e patch; o revisor escreve revisão. Usar os modelos [entrega](templates/entrega.md) e [revisão](templates/revisao.md). Não criar revisao-rN.md como se houvesse revisão quando o outro agente ainda não trabalhou. Rodadas entregues não são sobrescritas: correções geram nova rodada e respondem aos IDs dos achados anteriores.

Perguntas conceituais e respostas sem mudanças não exigem pasta, patch ou testes fictícios. A próxima tarefa com mudanças produz seu pacote, inclusive quando a alteração é apenas nas regras.

## Identidade, base e preservação de alterações existentes

1. Antes de editar, registrar workspace, branch, commit-base quando existir, estado Git e alterações preexistentes nos arquivos tocados. Guardar uma base verificável dos bytes que serão modificados, sem incluir segredos ou material fora da tarefa.
2. Gerar patch somente da tarefa em relação a essa base. Se a base já contiver mudanças do Douglas, descrevê-la como base local; não dizer que o patch parte de HEAD. Listar arquivos novos, removidos, renomeados, ignorados e não rastreados. Não presumir que git diff captura arquivos novos ou ignorados.
3. Registrar SHA-256 do patch e dos arquivos de implementação entregues. Caminhos e estados de remoção devem ser explícitos. O próprio relatório e a revisão não precisam de hash autorreferente. Hash identifica bytes; não comprova autoria, correção ou aprovação.
4. O revisor confere a identidade antes dos testes e antes de concluir. Havendo mudanças desde a entrega, registrar divergência e solicitar entrega atualizada. Não aprovar uma versão diferente daquela examinada.
5. No mesmo workspace, o patch serve para inspeção; não reaplicá-lo sobre mudanças já presentes. Em outra cópia, verificar a base e conflitos antes de qualquer aplicação.

GEMINI.md e .agents/ são ignorados pelo Git local atualmente; AGENTS.md pode estar não rastreado. Incluir esses arquivos explicitamente no pacote quando afetados. Não alterar o .gitignore nem forçar rastreamento só para registrar um handoff.

## Conteúdo da entrega e contrato entre camadas

- Objetivo, critérios de aceitação e motivo da solução.
- Lista de arquivos e alterações, com referência ao patch.
- Nomes anteriores e novos de funções, variáveis, campos JSON, inputs, eventos e endpoints; tipos, valores possíveis, consumidores afetados e compatibilidade.
- Efeitos em permissões/RBAC e banco; distinguir script preparado de SQL realmente executado.
- Testes executados com comando ou procedimento, ambiente, resultado observado e evidência. Separar testes pendentes e não aplicáveis. php -l comprova somente sintaxe.
- Limitações, achados respondidos e próxima ação solicitada ao revisor.

O contrato pode resumir uma parte de backend ou frontend dentro da entrega; não exige duplicar todo o código nem gerar um relatório separado para cada especialidade.

## Revisão, correção e encerramento

O revisor confere o pedido, o patch e os caminhos de execução necessários. Para cada achado F001, F002 etc., informa gravidade, arquivo/linha, cenário reproduzível, esperado versus observado e evidência. Separa defeito confirmado, hipótese e sugestão. Não exige refatoração estética fora do escopo.

Estados da revisão: **com achados**, **sem achados no escopo** ou **incompleta**. Sem achados não significa ausência universal de defeitos; registrar cobertura e limitações. Se faltarem ferramentas, devolver o procedimento sugerido e a limitação, ou solicitar à sessão principal a execução/gravação do relatório. Não inventar ferramentas nem contornar permissões.

O implementador verifica os apontamentos e registra, por ID, correção, discordância fundamentada ou pendência. Mudanças corrigidas voltam para revisão focada, ampliada apenas quando novos impactos justificarem. Após três rodadas sem convergência ou com o mesmo impedimento, informar o impasse e a decisão necessária ao Douglas; não continuar consumindo cota em repetição automática. É permitido entregar código para revisão com estado pendente, sem alegar validação concluída.

## Privacidade e armazenamento

Excluir .env, credenciais, tokens, hashes de senha, dados pessoais, dumps reais e saídas sensíveis. Usar .env.example e database/schema_only.sql quando necessários. Sanitizar evidências antes de gravar.

A pasta está sob a raiz web do XAMPP; manter o .htaccess local bloqueando acesso HTTP aos relatórios, patches e anexos. Não remover essa proteção para compartilhar: o outro agente lê os arquivos no disco. Não publicar nem sincronizar automaticamente o pacote para Drive, Foundry, configurações globais ou Obsidian. Se essas cópias forem necessárias e autorizadas, listar e conferir destinos e conflitos; informar o que não foi sincronizado.

## Última frase: prompt para o outro agente

Toda resposta final do Codex no contexto do MrStock termina com um último parágrafo copiável, iniciado por **Prompt para o Agy:**, contendo a ação e, quando houver, o caminho absoluto real da entrega/rodada. Nada deve vir depois dele. Não encerrar apenas com uma oferta para continuar. Em perguntas sem mudanças, produzir um prompt curto de contexto ou próxima ação, sem alegar arquivos ou testes inexistentes.

Exemplo após implementação:

> Prompt para o Agy: "Leia C:\xampp\htdocs\MrStock\docs\handoffs\<ID>\entrega-r1.md e o patch indicado, confira a identidade do código e grave revisao-r1.md na mesma pasta, sem modificar a implementação; registre achados, evidências e limitações e devolva um prompt para o Codex."

Exemplo após revisão do Antigravity:

> Prompt para o Codex: "Leia C:\xampp\htdocs\MrStock\docs\handoffs\<ID>\revisao-r1.md, confira os achados e gere uma nova entrega com as correções procedentes e a resposta por ID."

Nos prompts efetivos, substituir <ID> e a rodada por valores reais. O Antigravity encerra o retorno com prompt para o Codex quando a próxima ação for dele. Os arquivos não despertam agentes nem sincronizam chats por si mesmos.
