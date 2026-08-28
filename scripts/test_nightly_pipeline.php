<?php
/**
 * Teste Automatizado do Super Pipeline Noturno — MrStock ERP
 */

echo "====================================================================\n";
echo "MRSTOCK ERP — SUÍTE DE TESTES DO PIPELINE NOTURNO E ETIQUETAS\n";
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

// 1. Barcode Helper
require_once __DIR__ . '/../inc/barcode_helper.php';
$svgSample = gerarBarcodeSVG('7891027101015', 200, 50, true);
assertCheck("Gerador SVG de Código de Barras (CODE128) Puro", strpos($svgSample, '<svg') !== false && strpos($svgSample, '7891027101015') !== false);

// 2. CSS Design System
$cssContent = file_get_contents(__DIR__ . '/../css/style.css');
assertCheck("CSS: Classe .so-card definida", strpos($cssContent, '.so-card') !== false);
assertCheck("CSS: Classe .so-table definida", strpos($cssContent, '.so-table') !== false);
assertCheck("CSS: Classe .so-search-box definida", strpos($cssContent, '.so-search-box') !== false);
assertCheck("CSS: Classe .so-actions-btn definida", strpos($cssContent, '.so-actions-btn') !== false);
assertCheck("CSS: Grid de Etiquetas .label-sheet-grid", strpos($cssContent, '.label-sheet-grid') !== false);

// 3. Renderização de Produtos
$_SESSION['user_id']  = 1;
$_SESSION['username'] = 'admin';
$_SESSION['perfil']   = 'admin';

ob_start();
include __DIR__ . '/../produtos/index.php';
$prodHtml = ob_get_clean();
assertCheck("Produtos: Live Search presente", strpos($prodHtml, 'id="liveSearchProdutos"') !== false);
assertCheck("Produtos: Menu de Ações (3 pontos) presente", strpos($prodHtml, 'so-actions-btn') !== false);
assertCheck("Produtos: Link para Impressão de Etiquetas", strpos($prodHtml, 'produtos/etiquetas.php') !== false);

// 4. Renderização de Clientes
ob_start();
include __DIR__ . '/../clientes/index.php';
$cliHtml = ob_get_clean();
assertCheck("Clientes: Live Search presente", strpos($cliHtml, 'id="liveSearchClientes"') !== false);
assertCheck("Clientes: Menu de Ações (3 pontos) presente", strpos($cliHtml, 'so-actions-btn') !== false);

// 5. Renderização de Fornecedores
ob_start();
include __DIR__ . '/../fornecedores/index.php';
$fornHtml = ob_get_clean();
assertCheck("Fornecedores: Live Search presente", strpos($fornHtml, 'id="liveSearchFornecedores"') !== false);
assertCheck("Fornecedores: Menu de Ações (3 pontos) presente", strpos($fornHtml, 'so-actions-btn') !== false);

// 6. Renderização de Compras
ob_start();
include __DIR__ . '/../compras/index.php';
$compHtml = ob_get_clean();
assertCheck("Compras: Live Search presente", strpos($compHtml, 'id="liveSearchCompras"') !== false);
assertCheck("Compras: Tabela Modular .so-table", strpos($compHtml, 'so-table') !== false);

// 7. Renderização de Histórico de Vendas
ob_start();
include __DIR__ . '/../vendas/historico.php';
$histHtml = ob_get_clean();
assertCheck("Histórico Vendas: Live Search presente", strpos($histHtml, 'id="liveSearchVendas"') !== false);
assertCheck("Histórico Vendas: Cards de KPI Modulares", strpos($histHtml, 'so-card') !== false);

// 8. Renderização do Módulo de Etiquetas
ob_start();
include __DIR__ . '/../produtos/etiquetas.php';
$etqHtml = ob_get_clean();
assertCheck("Etiquetas: Renderização como Admin", strpos($etqHtml, 'Gerador & Impressão de Etiquetas') !== false);

// 9. Renderização do Dashboard
ob_start();
include __DIR__ . '/../dashboard.php';
$dashHtml = ob_get_clean();
assertCheck("Dashboard: SalesOps Cards e Venda Rápida", strpos($dashHtml, 'so-card') !== false && strpos($dashHtml, 'Checkout Expresso') !== false);

// 10. Teste do PDV
ob_start();
include __DIR__ . '/../vendas/pdv.php';
$pdvHtml = ob_get_clean();
assertCheck("PDV: Atalhos F2-F9 e Modal de Troco Dinâmico", strpos($pdvHtml, 'pdv-shortcuts-bar') !== false && strpos($pdvHtml, 'trocoBox') !== false);

echo "\nResultado Final: $passCount de $totalTests testes passaram com sucesso.\n";

if ($passCount === $totalTests) {
    echo "🎉 TODOS OS TESTES DO PIPELINE NOTURNO FORAM HOMOLOGADOS COM 100% DE SUCESSO!\n";
    exit(0);
} else {
    echo "⚠️ Houve falhas na homologação.\n";
    exit(1);
}
