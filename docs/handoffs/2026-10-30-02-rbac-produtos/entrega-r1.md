# Entrega — 2026-10-30-02-rbac-produtos — rodada 1

- Autor: Codex; destinatário: Antigravity (Agy).
- Data e hora: 2026-10-30T15:06:16-03:00.
- Estado: pronta para revisão; validação HTTP/browser/MySQL pendente.

## Objetivo e critérios de aceitação

Impedir que o perfil Caixa receba custo ou edição administrativa na página de produtos, preservando a consulta de estoque/preço de venda e o fluxo de cadastro/edição do Administrador.

- Caixa: listagem permanece; modal, scripts do formulário e ações administrativas não são renderizados. O parâmetro edit é ignorado antes da query de edição.
- Admin: cadastro/edição, preenchimento de custo, ações e abertura automática do modal permanecem disponíveis.
- Controlador de escrita: manter a proteção de perfil existente; não alterar banco ou regra comercial.

## Diagnóstico e impacto

Antes da correção, o template renderizava o modal administrativo em todas as respostas autenticadas; edit=37 carregava o registro completo e incluía o custo no atributo value do HTML recebido pelo Caixa. A consulta da listagem também usava p.*. A correção faz a autorização no servidor, antes da query de edição e da emissão do HTML.

## Base e identidade

- Workspace: C:\xampp\htdocs\MrStock; branch main; commit-base 31eeb9304bdb202ab8a0ea082f443103f44d2c31.
- Base: bytes locais de produtos/index.php capturados antes da alteração; o arquivo estava sem diff contra HEAD no início. O teste é novo e não rastreado.
- Alterações anteriores em .gitignore, README.md, docs/banco_de_dados.md e docs/perguntas_banca.md foram preservadas, com hashes antes/depois conferidos. A rodada de governança anterior não foi modificada.
- Patch: [alteracoes-r1.patch](alteracoes-r1.patch); SHA-256 `db324b3367338bf112ef30bfb2f946b9f62ecb74e671236e179706929cb9b88e`.
- Manifesto e evidências: [evidencias-r1.json](evidencias-r1.json), com hashes da base e da entrega, resultados e dependências preservadas.

| Arquivo | Estado | SHA-256 entregue |
|---|---|---|
| `produtos/index.php` | modified | `1142a70683462272928fd975741732f3f93d69858fcec0bdf7ee0e1564ba9407` |
| `tests/produtos_rbac_test.php` | new_untracked | `a4599080eb96d42fd9d0271f95a58f6a49fc1f1e1937063062e4c4f44677f4e3` |

O relatório, o JSON e o patch são metadados desta entrega e não precisam de hash autorreferente. Não reaplicar o patch no workspace atual: a mudança já está presente.

## Alterações e contrato

- produtos/index.php: nova variável local booleana $podeGerenciarProdutos, obtida de is_admin() já existente em inc/auth.php.
- Query da listagem: projeção explícita dos campos usados na tabela, sem preco_compra. Query de edição com SELECT * restrita ao Admin para preservar o formulário completo.
- Modal #modalProduto, botão de cadastro/etiquetas e coluna de ações: emitidos somente para Admin. A tabela do Caixa tem seis colunas; o estado vazio ajusta o colspan. Nenhuma coluna de custo foi adicionada ao catálogo do Admin.
- JavaScript: filtrarAoVivo continua disponível para ambos; clearForm e abertura automática do modal existem apenas para Admin. Nomes/IDs do formulário administrativo permanecem iguais.
- tests/produtos_rbac_test.php: teste CLI sem dependências adicionais, com fixtures sintéticas, execução PHP em subprocessos e cópias isoladas do template, auth e controlador. Não inclui inc/database.php real nem lê .env. Bloqueia execução pelo servidor web com HTTP 403 quando PHP_SAPI não é cli.
- Endpoints, payloads POST, funções públicas e schema não foram renomeados ou modificados.
- Precisão sobre a revisão anterior: produtos/functions.php tem uma verificação manual de perfil antes das ações POST, equivalente ao bloqueio administrativo pretendido; ele não chama literalmente require_admin(). Esse controlador e inc/auth.php permaneceram com os mesmos hashes.

## Validação executada

| Procedimento | Ambiente | Resultado observado | Limite |
|---|---|---|---|
| Regressão antes da correção | PHP 8.2.12 CLI com fixtures | 5 passaram, 5 falharam | Reproduziu emissão de modal administrativo para Caixa |
| Mesmos testes após a correção | Mesmo ambiente isolado | 10/10 passaram | Banco e header/footer simulados |
| php -l nos dois arquivos PHP entregues | PHP do XAMPP | Sem erros de sintaxe | Não comprova integração |
| git diff --check limitado a produtos/index.php | Git local | Passou | Outros arquivos preexistentes ficam fora do patch |
| git apply --check --whitespace=error-all | Base local capturada | Passou sem aplicar | Inclui o teste novo explicitamente |
| Hashes das dependências e arquivos anteriores selecionados | Disco local | Preservados | Lista no JSON |

Casos: Caixa com catálogo normal, edit direto e filtros/paginação; lista vazia; perfil autenticado sem privilégio; Admin cadastro/edição/ID inexistente; visitante sem sessão; POST de Caixa no controlador real copiado. Os testes inspecionam o HTML renderizado e as consultas registradas pela fixture. Não há execução SQL real, persistência MySQL, navegador ou validação criptográfica de CSRF; o helper visual de CSRF é simulado.

## Pendências e revisão solicitada

- Agy deve revisar o diff e os critérios e produzir revisao-r1.md. Não existe aprovação externa desta tarefa ainda.
- Reexecutar o teste CLI se necessário para a revisão e validar no navegador/HTTP com sessões reais Caixa/Admin quando o ambiente estiver disponível. Conferir o corpo da resposta e o acesso direto com edit, não apenas a visibilidade do modal.
- Verificar a integração de leitura com o schema existente. INSERT/UPDATE reais não foram testados: o controlador não mudou, e testes que escrevam no banco exigem autorização e ambiente apropriado.
- Manter a revisão nesta página e seus caminhos afetados. Isto não é auditoria completa dos demais endpoints de produtos ou do ERP.
- Nenhum commit, push, merge, deploy, operação de banco, espelhamento externo ou sessão adicional do Codex foi executado.

## Próxima ação

Prompt para o Agy: "Leia C:\xampp\htdocs\MrStock\docs\handoffs\2026-10-30-02-rbac-produtos\entrega-r1.md, confira o patch e os hashes e revise a proteção de custo/modal/edit por perfil, preservando a implementação. Execute C:\xampp\php\php.exe tests\produtos_rbac_test.php a partir do workspace e complemente com HTTP/browser se houver ambiente disponível; não escreva no banco sem autorização. Grave revisao-r1.md na mesma pasta com achados por ID, evidências e limitações e termine com um prompt para o Codex; não faça commit, push, deploy, sincronização externa nem abra outra sessão do Codex."
