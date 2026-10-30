# Entrega — 2026-10-30-03-pdv-itens-duplicados — rodada 1

- Autor: Codex; destinatário: Antigravity (Agy).
- Data e hora: 2026-10-30T15:15:59-03:00.
- Estado: pronta para revisão; integração real e concorrência pendentes.

## Objetivo e aceitação

Validar a quantidade acumulada de produtos repetidos em cart_data antes dos débitos de estoque do PDV. Rejeitar excesso sobre produtos.quantidade e sobre a soma dos lotes válidos, sem gravar uma venda parcial. Preservar linhas da venda, preço oficial, descontos, margem, ordenação dos locks e consumo FEFO existentes.

## Diagnóstico e impacto

O controlador verificava cada linha contra o mesmo saldo anterior à gravação. Com saldo 10, duas linhas do produto com quantidade 6 passavam individualmente; a falha foi reproduzida com o controlador real em fixture. O mesmo ocorria com estoque mestre suficiente e apenas 10 unidades em lotes válidos. A soma também precisava evitar overflow de inteiro.

## Base e identidade

- Workspace: C:\xampp\htdocs\MrStock; branch main; commit-base 31eeb9304bdb202ab8a0ea082f443103f44d2c31.
- Base: bytes locais capturados antes desta tarefa; vendas/functions.php não tinha alterações desta tarefa no início. A correção RBAC anterior e demais mudanças locais foram preservadas e não integram este patch.
- Patch: [alteracoes-r1.patch](alteracoes-r1.patch); SHA-256 `57c225af34d61812ea0c189cc5d37953fab849a6e1a8a3d41c1f91fe0c22dff4`.
- Manifesto, resultados brutos dos testes e hashes: [evidencias-r1.json](evidencias-r1.json).

| Arquivo | Estado | SHA-256 entregue |
|---|---|---|
| `vendas/functions.php` | modified | `2a47a7d4fc02a50d71df00520e3fe9ef150ae2f135611a9e3fac76df3bc7f92b` |
| `tests/vendas_carrinho_test.php` | new_untracked | `609c1a642bb75ba1eb91a0d9f0122df9b523973d2ca40ff057ca5982a23dff04` |

O teste é novo e não rastreado, incluído explicitamente no patch. Relatório, patch e JSON são metadados e não precisam de hash autorreferente. Não reaplicar o patch sobre o workspace já alterado. A verificação de aplicação foi feita sobre a base local capturada, sem aplicar mudanças.

## Alterações e contrato

- vendas/functions.php: mapa local $quantidadesPorProduto, indexado pelo ID inteiro já usado pelo controlador; $quantidadeAnterior e $quantidadeAcumulada somam as linhas de cada produto.
- IDs numéricos 1 e string "1" compartilham o mesmo acumulador. A soma só ocorre após rejeitar quantidade não positiva e overflow (item_qtd > PHP_INT_MAX - quantidadeAnterior).
- A comparação com saldo mestre e lotes válidos passa a usar a quantidade acumulada. Todas essas validações continuam antes dos INSERTs/UPDATEs da venda.
- No erro de estoque/lotes, o parâmetro solicitado e o log sanitário passam a indicar a quantidade acumulada do produto, não apenas a última linha. Rotas, nomes dos parâmetros e códigos de erro permanecem iguais.
- Linhas repetidas continuam sendo linhas separadas em vendas_itens e movimentacoes, com os débitos individuais existentes. Não houve fusão de itens, nova regra de preço, desconto, margem ou alteração de schema.
- A ordenação determinística por produto e os FOR UPDATE existentes foram preservados. Isso não comprova ausência de deadlocks ou correção concorrente no MySQL.
- tests/vendas_carrinho_test.php: teste CLI com cópias isoladas do controlador e auth, doubles PDO/PDOStatement em memória e dados sintéticos. Não lê .env, não abre MySQL nem grava dados reais. A execução por HTTP é bloqueada pelo teste.
- A mudança cobre a validação acumulada de repetidos; não substitui uma auditoria completa dos demais campos e rotas do PDV.

## Validação executada

| Procedimento | Ambiente | Resultado | Limite |
|---|---|---|---|
| Testes antes da correção | PHP 8.2.12 CLI, PDO simulado | 12 passaram; 3 falharam | Excesso de estoque, lotes e overflow reproduzidos |
| Mesmos testes após correção | Mesmo ambiente | 15/15 passaram | Não executa SQL real |
| Regressão RBAC anterior | PHP CLI com fixtures | 10/10 passaram | Mantidas as limitações daquela suíte |
| php -l nos dois arquivos entregues | PHP do XAMPP | Sem erros | Somente sintaxe |
| git diff --check do arquivo de aplicação | Git local | Passou | Aviso de normalização CRLF/LF, sem erro de whitespace |
| git apply --check --whitespace=error-all | Base local capturada | Passou | Patch não aplicado novamente |
| Hashes de dependências, RBAC e arquivos preexistentes selecionados | Disco | Preservados | Lista no JSON |

Os 15 cenários cobrem repetidos acima do estoque/lotes, overflow, soma exatamente no saldo, preço oficial ignorando preço do payload, desconto, FEFO sem consumo de vencidos, ordem de locks com múltiplos produtos, quantidade inválida, produto inexistente/inativo, carrinho vazio, desconto excessivo, margem bloqueada, rollback após falha simulada e venda rápida.

O teste de rollback verifica o fluxo do controlador com transação simulada, não a atomicidade do InnoDB. CSRF, configuração e logger usam helpers de teste; não se declara validação dessas proteções reais. O banco simulado não implementa constraints do schema.

## Pendências e revisão solicitada

- Revisão independente do Agy ainda pendente; não foi criado revisao-r1.md em nome dele.
- Validar com MySQL/InnoDB em ambiente de testes autorizado: dois itens iguais cuja soma exceda estoque, soma que exceda lotes válidos, venda válida no limite, erro durante gravação e concorrência entre requisições. Conferir vendas, itens, movimentações, lotes e cupons, além do saldo mestre.
- Qualquer teste que escreva no banco precisa estar abrangido pela autorização do Douglas. Nesta tarefa não foi feita escrita real, inicialização de serviços ou teste HTTP/browser.
- A revisão RBAC anterior foi lida e sua identidade conferida: dois arquivos e patch sem divergência. As pendências integradas daquela entrega continuam registradas nela.
- Sem commit, push, merge, deploy, sincronização externa ou sessão adicional do Codex.

## Próxima ação

Prompt para o Agy: "Leia C:\xampp\htdocs\MrStock\docs\handoffs\2026-10-30-03-pdv-itens-duplicados\entrega-r1.md, confira o patch e os hashes e revise a validação acumulada de estoque e lotes, o overflow e a preservação das linhas/preços/descontos/FEFO. Execute C:\xampp\php\php.exe tests\vendas_carrinho_test.php e registre revisao-r1.md na mesma pasta com achados por ID, evidências e limites; integração MySQL e concorrência só em ambiente de testes com autorização aplicável, sem alterar a implementação. Não faça commit, push, deploy, sincronização externa nem abra outra sessão do Codex; termine com um prompt para o Codex."
