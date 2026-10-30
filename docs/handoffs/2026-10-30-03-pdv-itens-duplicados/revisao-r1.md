# Revisão — 2026-10-30-03-pdv-itens-duplicados — rodada 1

- Revisor / data e hora com fuso: Antigravity (Agy) / 2026-10-30T15:20:00-03:00.
- Entrega e patch examinados: `entrega-r1.md` e `alteracoes-r1.patch` (SHA-256 `57c225af34d61812ea0c189cc5d37953fab849a6e1a8a3d41c1f91fe0c22dff4`).
- Identidade conferida antes e depois dos testes: `vendas/functions.php` e `tests/vendas_carrinho_test.php` conferidos contra `evidencias-r1.json`; hashes e `git apply --check --reverse` 100% coincidentes.
- Resultado: **sem achados no escopo**.

## Cobertura

- Arquivos examinados:
  - `vendas/functions.php`: introdução cirúrgica do acumulador `$quantidadesPorProduto`, prevenção de overflow em inteiros de 64 bits (`PHP_INT_MAX`), validação acumulada contra `produtos.quantidade` e contra a soma de lotes válidos via query de lote, e ajuste do parâmetro `solicitado` na URL e log sanitário.
  - `tests/vendas_carrinho_test.php`: suíte de 15 testes de regressão com doubles de PDO em memória, cobrindo excesso de estoque, lotes válidos, limite exato, overflow, preços oficiais, descontos, margens, locks determinísticos e rollback.
  - `docs/handoffs/2026-10-30-03-pdv-itens-duplicados/*`: entrega, patch e manifesto de evidências.
- Requisitos e caminhos conferidos:
  - Validação cumulativa por produto: duas ou mais linhas do mesmo item no carrinho têm suas quantidades somadas antes da comparação com o estoque mestre e lotes válidos;
  - Proteção contra overflow: verificação `item_qtd > PHP_INT_MAX - quantidadeAnterior` impedindo estouro de inteiros;
  - Preservação da granularidade da venda: itens repetidos permanecem como linhas distintas em `vendas_itens` e movimentações, garantindo rastreabilidade contábil;
  - Preservação de regras de negócio: preços oficiais do cadastro, cálculo de margem e consumo FEFO de lotes intactos;
  - Ausência de regressão: execução da suíte de RBAC de produtos (`tests/produtos_rbac_test.php`) com 10/10 cenários mantidos.

## Achados

Sem defeitos ou incoerências detectados no escopo examinado. A implementação foi pontual (+12, -6 linhas) e soluciona a vulnerabilidade de duplicatas sem efeitos colaterais.

### Observações Técnicas Não-Bloqueantes
- **O001 — Concorrência Real no MySQL/InnoDB:** Os testes foram executados com sucesso no PHP 8.2 CLI com doubles em memória. A validação de concorrência com requisições paralelas simultâneas e locks pessimistas reais (`SELECT ... FOR UPDATE`) no motor InnoDB depende de testes de carga em ambiente com MySQL ativo, sob autorização do Douglas.

## Testes e limitações

| Teste/procedimento | Ambiente | Resultado observado | Evidência |
|---|---|---|---|
| Suíte de validação de carrinho (15 cenários) | PHP 8.2.12 CLI / XAMPP | Passou (15/15) | `C:\xampp\php\php.exe tests\vendas_carrinho_test.php` |
| Suíte de regressão de RBAC (10 cenários) | PHP 8.2.12 CLI / XAMPP | Passou (10/10) | `C:\xampp\php\php.exe tests\produtos_rbac_test.php` |
| Verificação de sintaxe PHP (`php -l`) | PHP 8.2.12 CLI | Passou | Zero erros em `vendas/functions.php` e `tests/vendas_carrinho_test.php` |
| Verificação de hashes SHA-256 | Disco local | Passou | Arquivos idênticos ao manifesto `evidencias-r1.json` |
| Verificação de reversibilidade do patch | Git local | Passou | `git apply --check --reverse alteracoes-r1.patch` retornou código 0 |
| Preservação de dependências preexistentes | Disco local | Passou | Arquivos fora do escopo permaneceram intactos |
| Testes com escrita concorrente no MySQL | Motor InnoDB real | Não executado | MySQL desligado; sem escrita em banco sem autorização |

- O que não foi testado: Concorrência multi-thread e deadlocks no MySQL real (os testes usam doubles de PDO em memória).
- Ações externas restritas: Nenhum comando de commit, push, deploy, migração de banco ou sincronização externa foi realizado.
- Esta revisão não autoriza commit, push, deploy ou alterações no banco de dados.

## Próxima ação

Prompt para o Codex: "Leia `C:\xampp\htdocs\MrStock\docs\handoffs\2026-10-30-03-pdv-itens-duplicados\revisao-r1.md`; confira o parecer sem achados no escopo e o encerramento com sucesso da rodada 1 da tarefa de validação acumulada no PDV; as duas correções prioritárias de código (RBAC em `produtos/index.php` e itens duplicados no carrinho em `vendas/functions.php`) estão implementadas, testadas e revisadas no XAMPP local; apresente a síntese do estado atual do repositório para o Douglas avaliar os próximos passos ou autorizações necessárias (ex: testes integrados com MySQL, preparação de commit ou próxima demanda funcional)."
