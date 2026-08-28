<?php
/**
 * Teste de Renderização da UI SalesOps v0 (Admin e Caixa)
 */

echo "=== TESTE 1: RENDERIZAÇÃO COM PERFIL ADMIN (SALESOPS v0) ===\n";
$_SESSION = [
    'user_id'       => 1,
    'user_name'     => 'admin',
    'user_perfil'   => 'admin',
    'usuario_nivel' => 'admin',
    'csrf_token'    => 'test_token_123'
];

ob_start();
require __DIR__ . '/../dashboard.php';
$htmlAdmin = ob_get_clean();

echo "HTML gerado para Admin: " . strlen($htmlAdmin) . " bytes\n";
assert(strpos($htmlAdmin, 'so-sidebar') !== false, "Classe so-sidebar presente");
assert(strpos($htmlAdmin, 'so-brand') !== false, "Classe so-brand presente");
assert(strpos($htmlAdmin, 'menuVendas') !== false, "Grupo menuVendas presente");
assert(strpos($htmlAdmin, 'menuEstoque') !== false, "Grupo menuEstoque presente");
assert(strpos($htmlAdmin, 'menuCompras') !== false, "Grupo menuCompras presente");
assert(strpos($htmlAdmin, 'menuRelatorios') !== false, "Grupo menuRelatorios presente para admin");
assert(strpos($htmlAdmin, 'so-avatar--admin') !== false, "Avatar AD de admin presente");
assert(strpos($htmlAdmin, 'so-collapse-btn') !== false, "Botão de alternância de collapse presente");
echo "✅ Todos os testes de Admin passaram com sucesso!\n\n";

echo "=== TESTE 2: RENDERIZAÇÃO COM PERFIL CAIXA (RBAC TEST) ===\n";
$_SESSION = [
    'user_id'       => 2,
    'user_name'     => 'caixa',
    'user_perfil'   => 'caixa',
    'usuario_nivel' => 'caixa',
    'csrf_token'    => 'test_token_123'
];

ob_start();
$pageTitle = 'MrStock ERP - PDV';
$activePage = 'pdv';
require __DIR__ . '/../inc/header.php';
$htmlCaixa = ob_get_clean();

echo "HTML gerado para Caixa: " . strlen($htmlCaixa) . " bytes\n";
assert(strpos($htmlCaixa, 'menuRelatorios') === false, "Grupo menuRelatorios oculto para caixa");
assert(strpos($htmlCaixa, 'Categorias') === false, "Item Categorias oculto para caixa");
assert(strpos($htmlCaixa, 'Histórico de Vendas') === false, "Item Histórico de Vendas oculto para caixa");
assert(strpos($htmlCaixa, 'Ordens de Compra') === false, "Item Ordens de Compra oculto para caixa");
assert(strpos($htmlCaixa, 'so-avatar--caixa') !== false, "Avatar CX de caixa presente");
echo "✅ Todos os testes de Caixa (RBAC) passaram com sucesso!\n\n";

echo "=== TESTE 3: ATIVAÇÃO INTELIGENTE DO GRUPO (ACTIVE ACCORDION) ===\n";
$_SESSION = [
    'user_id'       => 1,
    'user_name'     => 'admin',
    'user_perfil'   => 'admin',
    'usuario_nivel' => 'admin',
    'csrf_token'    => 'test_token_123'
];

ob_start();
$pageTitle = 'MrStock ERP - Movimentações';
$activePage = 'movimentacoes';
require __DIR__ . '/../inc/header.php';
$htmlMov = ob_get_clean();

assert(strpos($htmlMov, '<li class="so-nav__item is-open" id="menuEstoque">') !== false, "Grupo menuEstoque renderizou aberto (is-open) em movimentacoes.php");
echo "✅ Ativação inteligente de grupo em rota ativa funcionou perfeitamente!\n\n";

echo "=== RESUMO: 100% DOS TESTES SALESOPS v0 HOMOLOGADOS COM SUCESSO! ===\n";
