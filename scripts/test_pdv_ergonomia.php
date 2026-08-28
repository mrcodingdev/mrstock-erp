<?php
/**
 * Teste Automatizado de Homologação: PDV Ergonomia, Atalhos de Teclado, Web Audio e Troco
 */

echo "=== INICIANDO TESTE DO PDV (ERGONOMIA, ATALHOS E TROCO) ===\n\n";

$_SESSION['user_id']  = 1;
$_SESSION['username'] = 'caixa';
$_SESSION['perfil']   = 'caixa';

ob_start();
include __DIR__ . '/../vendas/pdv.php';
$html = ob_get_clean();

echo "HTML gerado: " . strlen($html) . " bytes\n\n";

$checks = [
    'Toast Container' => strpos($html, 'id="toastContainer"') !== false,
    'Input de Bipe/Busca (F2)' => strpos($html, 'id="barcode_input"') !== false,
    'Barra de Atalhos (F2, F4, F8, F9, ESC)' => strpos($html, 'class="pdv-shortcuts-bar') !== false,
    'Atalho F2 Visível' => strpos($html, 'F2') !== false,
    'Atalho F4 Visível' => strpos($html, 'F4') !== false,
    'Atalho F8 Visível' => strpos($html, 'F8') !== false,
    'Atalho F9 Visível' => strpos($html, 'F9') !== false,
    'Atalho ESC Visível' => strpos($html, 'ESC') !== false,
    'Modal de Pagamento e Troco' => strpos($html, 'id="modalFinalizarVenda"') !== false,
    'Cédulas Rápidas (R$ 10 a R$ 200)' => strpos($html, 'btn-cedula') !== false,
    'Painel de Troco Dinâmico' => strpos($html, 'id="trocoBox"') !== false,
    'Sintetizador Web Audio API' => strpos($html, 'playBeep') !== false && strpos($html, 'AudioContext') !== false,
    'Micro-Toasts Dinâmicos' => strpos($html, 'showToast') !== false,
    'Listener Global de Teclado' => strpos($html, "addEventListener('keydown'") !== false,
    'Token CSRF no Formulário' => strpos($html, 'csrf_token') !== false,
];

$passCount = 0;
foreach ($checks as $name => $passed) {
    if ($passed) {
        echo "  ✅ [PASS] $name\n";
        $passCount++;
    } else {
        echo "  ❌ [FAIL] $name\n";
    }
}

echo "\nResultado: $passCount de " . count($checks) . " verificações aprovadas.\n";

if ($passCount === count($checks)) {
    echo "🎉 100% DOS TESTES DE ERGONOMIA E PDV FORAM APROVADOS COM SUCESSO!\n";
    exit(0);
} else {
    echo "⚠️ Houve falhas na validação do PDV.\n";
    exit(1);
}
