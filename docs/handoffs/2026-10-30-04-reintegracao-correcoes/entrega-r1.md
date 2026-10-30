# Entrega r1 — Reintegração de RBAC e validação acumulada no PDV

Data: 30/10/2026, America/Sao_Paulo. Workspace: C:\xampp\htdocs\MrStock. Branch main, base Git 31eeb9304bdb202ab8a0ea082f443103f44d2c31.

## Autorização e escopo
Douglas autorizou reintegrar as duas correções arquivadas e executar as três suítes simuladas. A melhoria de pagamento já presente foi preservada, assim como arquivos de contexto/handoffs não rastreados preexistentes. Sem commit, staging, push, deploy, alteração de .env, SQL ou banco real. MrStockBackup não foi editado.

## Base e recuperação
Conferidos hashes dos patches e dos quatro arquivos arquivados contra os manifestos das tarefas 2026-10-30-02-rbac-produtos e 2026-10-30-03-pdv-itens-duplicados. Testes recuperados da preservação em base-github-2026-10-30/preservacao/xampp/arquivos, fora da raiz web.
Antes de editar, cópias dos dois arquivos a restaurar, do PDV já corrigido e de seu teste foram guardadas em base-local/; identidade em base-local.json. Patches de aplicação aprovados por git apply --check e limitados aos dois arquivos de aplicação.

## Mudanças e contratos
- produtos/index.php: is_admin define podeGerenciarProdutos; consulta de listagem usa colunas explícitas sem preco_compra; consulta edit, modal administrativo, ações e JS administrativo somente para Admin; Caixa mantém catálogo e colspan adequado. Sem alteração em produtos/functions.php ou inc/auth.php. Escopo é essa tela, não auditoria completa de todas as rotas do sistema.
- vendas/functions.php: quantidadesPorProduto acumula quantidades por ID com proteção de overflow; valida estoque e lotes válidos contra o acumulado antes das gravações. Preserva linhas de venda, preço oficial, descontos, FEFO, ordenação de locks e fluxo de venda rápida. Parâmetro solicitado nos erros passa a representar quantidade acumulada.
- Recuperados tests/produtos_rbac_test.php e tests/vendas_carrinho_test.php, idênticos aos arquivos arquivados.
- vendas/pdv.php e tests/pdv_pagamento_test.cjs: melhoria de pagamento preexistente, bytes preservados nesta tarefa.
- Sem novos endpoints, alterações de esquema ou mudanças de formato no payload. Custo não é ocultado por CSS: o modal e os dados de edição deixam de ser entregues ao Caixa nessa tela.

## Patches e evidências
- alteracoes-r1.patch: somente esta reintegração (dois arquivos de aplicação + dois testes novos).
- pacote-consolidado.patch: as três melhorias e as três suítes em relação ao HEAD. Útil para revisão final do conjunto; inclui o pagamento preexistente, não é atribuído à presente reintegração.
- Ambos passaram em git apply --check --reverse contra o workspace atual. Não reaplicar sobre arquivos já corrigidos.
- evidencias-r1.json contém hashes dos seis arquivos, dos dois patches, comandos e resultados.

## Testes executados
Antes da aplicação: RBAC 5/10 e carrinho12/15; logs antes-rbac.txt e antes-carrinho.txt documentam as falhas reproduzidas.
Depois:
- C:\xampp\php\php.exe tests/produtos_rbac_test.php — 10/10.
- C:\xampp\php\php.exe tests/vendas_carrinho_test.php — 15/15.
- node tests/pdv_pagamento_test.cjs — 9/9.
- Total: 34/34 cenários simulados; logs depois-*.txt.
- php -l aprovado nos três arquivos de aplicação e dois testes PHP.
- git diff --check aprovado. Pagamento e seu teste mantiveram hashes anteriores.

## Limitações e testes pendentes
Fixtures/doubles não usam .env nem MySQL; o teste Node usa DOM simulado. Esses resultados não homologam concorrência real, rollback no motor InnoDB, duas sessões simultâneas ou comportamento HTTP autenticado após reintegração.
Os seis testes manuais do pagamento foram relatados pelo Douglas antes desta tarefa e o código de pagamento foi preservado; isso não constitui teste manual das correções recém-recuperadas.
Banco permanece na configuração anterior mrstock_test; não houve acesso ou mutação do banco nesta tarefa. RBAC global e outras rotas não foram auditados.

## Próxima ação
Agy: conferir identidade dos seis arquivos, ambos patches, executar três suítes isoladas e revisar interação das mudanças. Registrar revisao-r1.md nesta pasta; preservar implementação. Sem operações reais de venda/compra/banco, commit, push ou deploy.
Douglas: após revisão, conferir Produtos com Admin e Caixa em sessões separadas: Caixa consulta catálogo sem ações/modal administrativo mesmo com ?edit=ID; Admin mantém modal de cadastro/edição. Evitar enviar ao chat HTML que contenha dados sensíveis. Testes reais de concorrência deverão ter plano e autorização próprios.
