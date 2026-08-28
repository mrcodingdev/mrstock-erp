<?php
/**
 * Teste Automatizado: Correção de Redirecionamentos RBAC, Popover Z-Index e Paginação Verde
 */

echo "====================================================================\n";
echo "MRSTOCK ERP — SUÍTE DE TESTES RBAC, POPOVER Z-INDEX E PAGINAÇÃO VERDE\n";
echo "====================================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertCheck($title, $condition) {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        echo "  ✅ [PASS] $title\n";
        $passCount++;
    } else {
        echo "  ❌ [FAIL] $title\n";
    }
}

// 1. Verificação de CSS: Z-Index e Contraste do Popover
$css = file_get_contents(__DIR__ . '/../css/style.css');
assertCheck("CSS: .so-header com z-index: 1050", strpos($css, 'z-index: 1050;') !== false);
assertCheck("CSS: .so-dropdown com z-index: 99999 !important", strpos($css, 'z-index: 99999 !important;') !== false);
assertCheck("CSS: .so-dropdown__link contraste aprimorado", strpos($css, '.so-dropdown__link i') !== false);
assertCheck("CSS: Paginação Verde Institucional", strpos($css, '.page-item.active .page-link') !== false && strpos($css, 'var(--mr-bg-primary)') !== false);

// 2. Verificação de Acesso Admin (Perfil Admin)
$_SESSION['user_id']       = 1;
$_SESSION['user_name']     = 'admin';
$_SESSION['username']      = 'admin';
$_SESSION['user_perfil']   = 'admin';
$_SESSION['usuario_nivel'] = 'admin';
$_SESSION['perfil']        = 'admin';

$adminRoutes = [
    'vendas/historico.php'   => 'Histórico de Vendas',
    'compras/index.php'      => 'Gestão de Compras',
    'compras/nova.php'       => 'Nova Compra',
    'produtos/etiquetas.php' => 'Gerador & Impressão de Etiquetas',
    'categorias/index.php'   => 'Gestão de Categorias',
    'fornecedores/index.php' => 'Gestão de Fornecedores',
    'relatorios/analise.php' => 'Centro de Inteligência & Análise',
    'relatorios/index.php'   => 'Emissão de Relatórios',
];

foreach ($adminRoutes as $file => $label) {
    ob_start();
    include __DIR__ . '/../' . $file;
    $html = ob_get_clean();
    assertCheck("Admin Route Aprovada: $label ($file)", strpos($html, $label) !== false);
}

// 3. Verificação de Componentes em Categorias e Movimentações
ob_start();
include __DIR__ . '/../categorias/index.php';
$catHtml = ob_get_clean();
assertCheck("Categorias: Menu 3 Pontos (.so-actions-btn)", strpos($catHtml, 'so-actions-btn') !== false);
assertCheck("Categorias: Tabela Modular (.so-table)", strpos($catHtml, 'so-table') !== false);
assertCheck("Categorias: Live Search presente", strpos($catHtml, 'id="liveSearchCategorias"') !== false);

ob_start();
include __DIR__ . '/../produtos/movimentacoes.php';
$movHtml = ob_get_clean();
assertCheck("Movimentações: Tabela Modular (.so-table)", strpos($movHtml, 'so-table') !== false);
assertCheck("Movimentações: Live Search presente", strpos($movHtml, 'id="liveSearchMovimentacoes"') !== false);

// 4. Verificação de Paginação em Produtos
ob_start();
include __DIR__ . '/../produtos/index.php';
$prodHtml = ob_get_clean();
assertCheck("Produtos: Paginação Institucional presente", strpos($prodHtml, 'aria-label="Navegação da listagem"') !== false);

echo "\nResultado Final: $passCount de $totalTests testes aprovados com sucesso.\n";

if ($passCount === $totalTests) {
    echo "🎉 100% DOS TESTES DE RBAC, POPOVER E PAGINAÇÃO FORAM HOMOLOGADOS COM SUCESSO!\n";
    exit(0);
} else {
    echo "⚠️ Houve falhas na homologação.\n";
    exit(1);
}
