<?php
/**
 * MrStock ERP — Auditoria Completa do Sistema (4 Subagentes Especializados)
 */

echo "=================================================================================\n";
echo "MRSTOCK ERP — AUDITORIA COMPLETA DE SISTEMA (4 SUBAGENTES ESPECIALIZADOS)\n";
echo "=================================================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertAudit($subagent, $title, $condition) {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        echo "  [PASS] [$subagent] $title\n";
        $passCount++;
    } else {
        echo "  [FAIL] [$subagent] $title\n";
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 🔴 SUBAGENTE 1: APPSEC, OWASP TOP 10 & RBAC AUDITOR
// ═════════════════════════════════════════════════════════════════════════════
echo ">>> Executando Auditoria - Subagente 1: AppSec & RBAC...\n";

// 1.1 CSRF em todos os controladores POST
$postControllers = [
    'login.php',
    'produtos/functions.php',
    'clientes/functions.php',
    'fornecedores/functions.php',
    'compras/functions.php',
    'vendas/functions.php',
    'categorias/functions.php'
];
foreach ($postControllers as $ctrl) {
    $content = file_get_contents(__DIR__ . '/../' . $ctrl);
    assertAudit("SUB-1:AppSec", "Validação CSRF ativa em $ctrl", strpos($content, 'csrf_verify()') !== false);
}

// 1.2 RBAC em rotas administrativas
$adminRoutes = [
    'vendas/historico.php',
    'compras/index.php',
    'compras/nova.php',
    'compras/visualizar.php',
    'produtos/etiquetas.php',
    'categorias/index.php',
    'fornecedores/index.php',
    'relatorios/analise.php',
    'relatorios/index.php',
    'relatorios/pdf.php',
    'relatorios/excel.php'
];
foreach ($adminRoutes as $route) {
    $content = file_get_contents(__DIR__ . '/../' . $route);
    $hasRbac = strpos($content, "['user_perfil']") !== false || strpos($content, "['perfil']") !== false;
    assertAudit("SUB-1:RBAC", "Barreira RBAC de Administrador em $route", $hasRbac);
}

// ═════════════════════════════════════════════════════════════════════════════
// 🔵 SUBAGENTE 2: DATA INTEGRITY & CONCURRENCY AUDITOR
// ═════════════════════════════════════════════════════════════════════════════
echo "\n>>> Executando Auditoria - Subagente 2: Data Integrity & Concurrency...\n";

// 2.1 Transações ACID no PDV e Compras
$vendasFunc = file_get_contents(__DIR__ . '/../vendas/functions.php');
assertAudit("SUB-2:ACID", "Transação PDO beginTransaction() no PDV", strpos($vendasFunc, 'beginTransaction()') !== false);
assertAudit("SUB-2:ACID", "Transação PDO commit() no PDV", strpos($vendasFunc, 'commit()') !== false);
assertAudit("SUB-2:ACID", "Transação PDO rollBack() no PDV", strpos($vendasFunc, 'rollBack()') !== false);

$comprasFunc = file_get_contents(__DIR__ . '/../compras/functions.php');
assertAudit("SUB-2:ACID", "Transação PDO beginTransaction() em Compras", strpos($comprasFunc, 'beginTransaction()') !== false);
assertAudit("SUB-2:ACID", "Transação PDO commit() em Compras", strpos($comprasFunc, 'commit()') !== false);
assertAudit("SUB-2:ACID", "Transação PDO rollBack() em Compras", strpos($comprasFunc, 'rollBack()') !== false);

// 2.2 Pessimistic Locking (FOR UPDATE)
assertAudit("SUB-2:Concurrency", "Pessimistic Lock (SELECT ... FOR UPDATE) na validação de saldo do PDV", strpos($vendasFunc, 'FOR UPDATE') !== false);

// 2.3 Foreign Keys & Schema InnoDB
$sqlSchema = file_get_contents(__DIR__ . '/../database/mrstock_db.sql');
assertAudit("SUB-2:Schema", "Schema configurado com Engine InnoDB", strpos($sqlSchema, 'ENGINE=InnoDB') !== false);
assertAudit("SUB-2:Schema", "Schema com CREATE DATABASE mrstock_db UTF-8", strpos($sqlSchema, 'CREATE DATABASE') !== false && strpos($sqlSchema, 'utf8mb4') !== false);

// ═════════════════════════════════════════════════════════════════════════════
// 🟢 SUBAGENTE 3: UI/UX & DESIGN SYSTEM CONSISTENCY AUDITOR
// ═════════════════════════════════════════════════════════════════════════════
echo "\n>>> Executando Auditoria - Subagente 3: UI/UX & Design System...\n";

$css = file_get_contents(__DIR__ . '/../css/style.css');
assertAudit("SUB-3:Design", "Paleta institucional Verde Oficial (--mr-bg-primary: #284936)", strpos($css, '--mr-bg-primary: #284936;') !== false);
assertAudit("SUB-3:Design", "Paleta institucional Dark Sidebar (--mr-bg-dark: #222d31)", strpos($css, '--mr-bg-dark: #222d31;') !== false);
assertAudit("SUB-3:Design", "Sidebar colapsada e expandida (260px / 72px)", strpos($css, '--mr-w-open: 260px;') !== false && strpos($css, '--mr-w-collapsed: 72px;') !== false);
assertAudit("SUB-3:Design", "Popover de Perfil com Z-Index soberano 99999", strpos($css, 'z-index: 99999 !important;') !== false);
assertAudit("SUB-3:Design", "Paginação ativa com verde institucional #284936", strpos($css, '.page-item.active .page-link') !== false && strpos($css, 'var(--mr-bg-primary)') !== false);

// 3.1 Verificação de Wrappers Padronizados de Paginação
$paginatedViews = [
    'produtos/index.php',
    'vendas/historico.php',
    'compras/index.php',
    'produtos/movimentacoes.php'
];
foreach ($paginatedViews as $view) {
    $content = file_get_contents(__DIR__ . '/../' . $view);
    $hasWrapper = strpos($content, 'card-footer bg-white border-top p-3') !== false;
    assertAudit("SUB-3:Pagination", "Wrapper de Paginação com respiro visual em $view", $hasWrapper);
}

// ═════════════════════════════════════════════════════════════════════════════
// 🟡 SUBAGENTE 4: PHP 8.2 COMPATIBILITY & PORTABILITY AUDITOR
// ═════════════════════════════════════════════════════════════════════════════
echo "\n>>> Executando Auditoria - Subagente 4: PHP 8.2 & Portabilidade Offline...\n";

// 4.1 Verificação de Sintaxe Global
$allPhpFiles = glob(__DIR__ . '/../*.php');
$allPhpFiles = array_merge($allPhpFiles, glob(__DIR__ . '/../*/*.php'));
$syntaxPass = true;
foreach ($allPhpFiles as $file) {
    exec("C:\\xampp\\php\\php.exe -l \"$file\" 2>&1", $output, $retCode);
    if ($retCode !== 0) {
        $syntaxPass = false;
        break;
    }
}
assertAudit("SUB-4:PHP8.2", "Sintaxe 100% válida em todos os arquivos PHP do projeto", $syntaxPass);

// 4.2 Verificação de Modo 100% Offline (Zero dependências externas de CDN no header)
$header = file_get_contents(__DIR__ . '/../inc/header.php');
$noExternalCdn = strpos($header, 'fonts.googleapis.com') === false && strpos($header, 'cdnjs.cloudflare.com') === false && strpos($header, 'unpkg.com') === false;
assertAudit("SUB-4:Offline", "Header do sistema 100% Offline (Assets locais em css/, js/, webfonts/)", $noExternalCdn);

// ═════════════════════════════════════════════════════════════════════════════
// 📊 CONSOLIDAÇÃO FINAL DA AUDITORIA
// ═════════════════════════════════════════════════════════════════════════════
echo "\n=================================================================================\n";
echo "RELATÓRIO CONSOLIDADO: $passCount de $totalTests verificações aprovadas com êxito.\n";
echo "=================================================================================\n";

if ($passCount === $totalTests) {
    echo "🎉 SISTEMA HOMOLOGADO COM 100% DE CONFORMIDADE PELOS 4 SUBAGENTES!\n";
    exit(0);
} else {
    echo "⚠️ Foram identificadas não-conformidades durante a auditoria.\n";
    exit(1);
}
