<?php
/**
 * Teste Automatizado de Prova: Modernização de UI/UX (MrStock ERP v2.0)
 */

echo "====================================================================\n";
echo "MRSTOCK ERP — SUÍTE DE PROVA: MODERNIZAÇÃO UI/UX & MICROINTERAÇÕES\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck($name, $condition, &$pass, &$fail) {
    if ($condition) {
        echo "  ✅ [PASS] $name\n";
        $pass++;
    } else {
        echo "  ❌ [FAIL] $name\n";
        $fail++;
    }
}

// 1. Verificação do CSS
$css = file_get_contents(__DIR__ . '/../css/style.css');
assertCheck("CSS: @keyframes salesOpsSlideInLeft (Slide-In Staggered) presente", strpos($css, '@keyframes salesOpsSlideInLeft') !== false, $passCount, $failCount);
assertCheck("CSS: .btn:active com transform scale(0.98)", strpos($css, 'transform: scale(0.98)') !== false, $passCount, $failCount);
assertCheck("CSS: .badge-live-pulse declarada", strpos($css, '.badge-live-pulse') !== false, $passCount, $failCount);
assertCheck("CSS: .so-empty-state declarada", strpos($css, '.so-empty-state') !== false, $passCount, $failCount);
assertCheck("CSS: .table-density-compact declarada", strpos($css, '.table-density-compact') !== false, $passCount, $failCount);
assertCheck("CSS: .font-size-comfort declarada", strpos($css, '.font-size-comfort') !== false, $passCount, $failCount);

// 2. Verificação de Injeção no Header
$header = file_get_contents(__DIR__ . '/../inc/header.php');
assertCheck("Header: Injeção de body classes dinâmicas", strpos($header, '<body class="<?= $_bodyClassStr ?>">') !== false, $passCount, $failCount);

// 3. Verificação de Indicadores e Sidebar
$footer = file_get_contents(__DIR__ . '/../inc/footer.php');
assertCheck("Footer: Sidebar Accordion e Dropdowns nativos estáveis", strpos($footer, 'data-accordion-toggle') !== false, $passCount, $failCount);

$dash = file_get_contents(__DIR__ . '/../dashboard.php');
assertCheck("Dashboard: Título limpo e direto sem badge intrusivo", strpos($dash, 'badge-live-pulse') === false, $passCount, $failCount);

// 4. Verificação de Empty States
$funcs = file_get_contents(__DIR__ . '/../inc/functions.php');
assertCheck("Functions: Helper render_empty_state() presente", strpos($funcs, 'function render_empty_state') !== false, $passCount, $failCount);

$prod = file_get_contents(__DIR__ . '/../produtos/index.php');
assertCheck("Produtos: Chamada de render_empty_state() na tabela", strpos($prod, 'render_empty_state') !== false, $passCount, $failCount);

// 5. Verificação de Abas em Configurações
$cfg = file_get_contents(__DIR__ . '/../configuracoes.php');
assertCheck("Configurações: Abas full-width .settings-nav-tabs", strpos($cfg, 'settings-nav-tabs') !== false, $passCount, $failCount);
assertCheck("Configurações: Opção densidade_tabela", strpos($cfg, 'name="densidade_tabela"') !== false, $passCount, $failCount);
assertCheck("Configurações: Opção tamanho_fonte", strpos($cfg, 'name="tamanho_fonte"') !== false, $passCount, $failCount);

echo "\n====================================================================\n";
echo "Resultado Final: $passCount de " . ($passCount + $failCount) . " verificações aprovadas com êxito.\n";
if ($failCount === 0) {
    echo "🎉 TODAS AS PROVAS DA MODERNIZAÇÃO DE UI/UX FORAM HOMOLOGADAS COM 100% DE SUCESSO!\n";
    exit(0);
} else {
    echo "❌ Foram encontradas falhas na suíte de testes.\n";
    exit(1);
}