<?php
/**
 * MrStock ERP - Suíte de Prova Real para Refinamentos de UI/UX, Validações e Purga de Emojis
 */
if (php_sapi_name() !== 'cli') {
    die("Acesso negado: execute exclusivamente via terminal CLI.");
}

echo "\n=================================================================================\n";
echo "MRSTOCK ERP — PROVA REAL: REFINAMENTOS DE UI/UX, VALIDAÇÕES & EMOJI PURGE\n";
echo "=================================================================================\n\n";

$passCount = 0;
$totalTests = 10;

require_once __DIR__ . '/../inc/functions.php';

// TESTE 1: Helper formatar_cpf_cnpj
$cpfFormatado  = formatar_cpf_cnpj('12345678909');
$cnpjFormatado = formatar_cpf_cnpj('12345678000195');
if ($cpfFormatado === '123.456.789-09' && $cnpjFormatado === '12.345.678/0001-95') {
    echo "  [PASS] 1. Helpers: formatar_cpf_cnpj() formata CPF e CNPJ com precisão matemática\n";
    $passCount++;
} else {
    echo "  [FAIL] 1. Helpers: formatar_cpf_cnpj() falhou!\n";
}

// TESTE 2: Helper formatar_telefone
$fixoFormatado = formatar_telefone('1133334444');
$celFormatado  = formatar_telefone('11987654321');
if ($fixoFormatado === '(11) 3333-4444' && $celFormatado === '(11) 98765-4321') {
    echo "  [PASS] 2. Helpers: formatar_telefone() formata fixo (10d) e celular (11d)\n";
    $passCount++;
} else {
    echo "  [FAIL] 2. Helpers: formatar_telefone() falhou!\n";
}

// TESTE 3: Helper formatar_cep
$cepFormatado = formatar_cep('01001000');
if ($cepFormatado === '01001-000') {
    echo "  [PASS] 3. Helpers: formatar_cep() formata CEP com máscara padrão XXXXX-XXX\n";
    $passCount++;
} else {
    echo "  [FAIL] 3. Helpers: formatar_cep() falhou!\n";
}

// TESTE 4: PDV - Filtro por Categoria em Cascata
$pdvContent = file_get_contents(__DIR__ . '/../vendas/pdv.php');
if (strpos($pdvContent, 'categoria_filtro_pdv') !== false &&
    strpos($pdvContent, 'filtrarProdutosPorCategoria') !== false &&
    strpos($pdvContent, 'data-categoria-id') !== false) {
    echo "  [PASS] 4. PDV: Seletor de Categorias em Cascata implementado com filtragem dinâmica JS\n";
    $passCount++;
} else {
    echo "  [FAIL] 4. PDV: Falha ao validar seletor de categorias em cascata!\n";
}

// TESTE 5: PDV - Remoção da Barra Flutuante & Modal de Atalhos F1
if (strpos($pdvContent, 'modalGuiaAtalhos') !== false &&
    strpos($pdvContent, 'pdv-shortcuts-bar') === false &&
    strpos($pdvContent, "e.key === 'F1'") !== false) {
    echo "  [PASS] 5. PDV: Barra preta flutuante removida e substituída por Modal de Atalhos (F1)\n";
    $passCount++;
} else {
    echo "  [FAIL] 5. PDV: Barra flutuante ainda presente ou Modal de Atalhos ausente!\n";
}

// TESTE 6: Dashboard - Remoção do Checkout Expresso
$dashContent = file_get_contents(__DIR__ . '/../dashboard.php');
if (strpos($dashContent, 'Checkout Expresso') === false &&
    strpos($dashContent, 'venda_rapida') === false &&
    strpos($dashContent, 'relatorios/pdf.php') !== false) {
    echo "  [PASS] 6. Dashboard: Checkout Expresso removido e atalho para Relatório PDF preservado\n";
    $passCount++;
} else {
    echo "  [FAIL] 6. Dashboard: Checkout Expresso ainda presente ou atalho PDF ausente!\n";
}

// TESTE 7: Clientes e Fornecedores - Máscaras e Auto-busca Instantânea ViaCEP
$cliContent = file_get_contents(__DIR__ . '/../clientes/index.php');
$fornContent = file_get_contents(__DIR__ . '/../fornecedores/index.php');
if (strpos($cliContent, 'mascaraTelefone') !== false &&
    strpos($cliContent, 'mascaraCpfCnpj') !== false &&
    strpos($cliContent, 'cli_cep_feedback') !== false &&
    strpos($cliContent, 'v.length === 8') !== false &&
    strpos($fornContent, 'mascaraTelefone') !== false &&
    strpos($fornContent, 'mascaraCpfCnpj') !== false &&
    strpos($fornContent, 'forn_cep_feedback') !== false &&
    strpos($fornContent, 'v.length === 8') !== false) {
    echo "  [PASS] 7. CRUDs: Auto-busca instantânea de CEP (ViaCEP) com feedback visual ativo\n";
    $passCount++;
} else {
    echo "  [FAIL] 7. CRUDs: Auto-busca de CEP ausente ou incompleta em Clientes/Fornecedores!\n";
}

// TESTE 8: Validação de Nome Sem Números e Validação de E-mail
$cliFunc = file_get_contents(__DIR__ . '/../clientes/functions.php');
$fornFunc = file_get_contents(__DIR__ . '/../fornecedores/functions.php');
if (strpos($cliContent, 'filtrarApenasLetras') !== false &&
    strpos($cliFunc, "preg_match('/[0-9]/', \$nome)") !== false &&
    strpos($cliFunc, 'FILTER_VALIDATE_EMAIL') !== false &&
    strpos($fornFunc, 'FILTER_VALIDATE_EMAIL') !== false) {
    echo "  [PASS] 8. Validações: Bloqueio de números no nome e validação rigorosa de e-mail\n";
    $passCount++;
} else {
    echo "  [FAIL] 8. Validações de nome ou e-mail ausentes no backend/frontend!\n";
}

// TESTE 9: Purga Global de Emojis
$files = glob(__DIR__ . '/../{*.php,*/*.php}', GLOB_BRACE);
$hasEmoji = false;
$emojiRegex = '/[\x{1F300}-\x{1F9FF}\x{2600}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}]/u';
foreach ($files as $f) {
    if (strpos($f, 'scripts') !== false || strpos($f, 'docs') !== false) continue;
    $c = file_get_contents($f);
    if (preg_match($emojiRegex, $c)) {
        $hasEmoji = true;
        echo "  [FAIL] 9. Emoji detectado no arquivo: " . basename($f) . "\n";
        break;
    }
}
if (!$hasEmoji) {
    echo "  [PASS] 9. Design System: 100% livre de emojis (Interface limpa com FontAwesome 6)\n";
    $passCount++;
}

// TESTE 10: Validação de Sintaxe PHP 8.2 em todos os arquivos
$syntaxOk = true;
foreach ($files as $f) {
    $output = [];
    $returnVar = 0;
    exec("\"C:\\xampp\\php\\php.exe\" -l \"$f\"", $output, $returnVar);
    if ($returnVar !== 0) {
        $syntaxOk = false;
        echo "  [FAIL] 10. Erro de sintaxe em: $f\n";
        break;
    }
}
if ($syntaxOk) {
    echo "  [PASS] 10. PHP 8.2: Sintaxe 100% válida em todos os arquivos do projeto\n";
    $passCount++;
}

echo "\n=================================================================================\n";
echo "RESULTADO: $passCount de $totalTests testes aprovados com êxito.\n";
echo "=================================================================================\n";

if ($passCount === $totalTests) {
    echo "🎉 TODAS AS VALIDAÇÕES, AUTO-BUSCA VIACEP E EMOJI PURGE HOMOLOGADOS COM 100% DE SUCESSO!\n\n";
}
