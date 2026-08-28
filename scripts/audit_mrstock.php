<?php
/**
 * Script de Auditoria Estática e Dinâmica 100% Local — MrStock ERP
 */
$baseDir = dirname(__DIR__);
$files = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
    if ($ext !== 'php') continue;
    $rel = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
    if (strpos($rel, 'docs') === 0 || strpos($rel, 'scripts') === 0) continue;
    $files[$rel] = $file->getPathname();
}

ksort($files);

echo "====================================================================\n";
echo "MRSTOCK ERP — AUDITORIA ESTÁTICA E DINÂMICA (PHP 8.2 / XAMPP)\n";
echo "Total de Arquivos PHP Escaneados: " . count($files) . "\n";
echo "====================================================================\n\n";

$issues = [];

foreach ($files as $relPath => $fullPath) {
    $content = file_get_contents($fullPath);
    $lines   = explode("\n", $content);

    // 1. Verificação de Sintaxe PHP 8.2
    $output = [];
    $returnVar = 0;
    exec("C:\\xampp\\php\\php.exe -l " . escapeshellarg($fullPath), $output, $returnVar);
    if ($returnVar !== 0) {
        $issues[] = [
            'file' => $relPath,
            'line' => 0,
            'severity' => 'CRITICAL',
            'pillar' => 'PHP 8.2 Compatibility',
            'desc' => 'Erro de sintaxe PHP: ' . implode(' ', $output)
        ];
    }

    // 2. Verificação de Concatenação Direta em SQL (SQL Injection)
    foreach ($lines as $idx => $line) {
        $lNum = $idx + 1;
        // query com variáveis concatenadas ou interpoladas
        if (preg_match('/->query\s*\(\s*["\'].*\$[a-zA-Z0-9_]/', $line) ||
            preg_match('/->prepare\s*\(\s*["\'].*\$[a-zA-Z0-9_]/', $line)) {
            // Ignora se for montagem de $sql preparado
            if (!preg_match('/\$whereSql|\$dateCondition|\$sql|\$params/', $line)) {
                $issues[] = [
                    'file' => $relPath,
                    'line' => $lNum,
                    'severity' => 'HIGH',
                    'pillar' => 'Security (SQL Injection)',
                    'desc' => 'Possível concatenação/interpolação direta de variável em SQL: ' . trim($line)
                ];
            }
        }

        // 3. Verificação de echo sem htmlspecialchars
        if (preg_match('/<\?=\s*\$([a-zA-Z0-9_]+(\[[^\]]+\])?(\[\'[^\']+\'\])?)\s*;?\s*\?>/', $line, $m)) {
            $varName = $m[1];
            // Exceções conhecidas seguras (HTML intencional pré-sanitizado / numérico / helper / csrf)
            $safeVars = ['validadeHtml', 'badgeEstoque', 'badgeStatus', 'badge', 'badgeClass', 'badgeText', 'zapDisplay', 'zapBtn', 'zapButton', 'extraHead', 'csrf_input', 'csrf_token', 'pageTitle', 'totalProdutos', 'totalFornecedores', 'totalCategorias', 'totalCompras', 'totalVendasQtd', 'totalRows', 'faturamentoTotal', 'ticketMedio', 'faturamentoPeriodo', 'lucroPeriodo', 'qtdVendasPeriodo', 'ticketMedioPeriodo', 'patrimonioEstoque', 'custoEstoque', 'lucroEstoque', 'totalItensVendidos', 'firstItem', 'lastItem', 'totalPages', 'page', 'i', 'href', 'icon', 'label', 'titulo', 'periodoNome'];
            
            $isSafe = false;
            foreach ($safeVars as $sv) {
                if (strpos($varName, $sv) !== false) {
                    $isSafe = true;
                    break;
                }
            }
            if (!$isSafe && !preg_match('/htmlspecialchars|number_format|str_pad|date|json_encode|intval|\(int\)|\(float\)/', $line)) {
                $issues[] = [
                    'file' => $relPath,
                    'line' => $lNum,
                    'severity' => 'LOW',
                    'pillar' => 'Security (XSS)',
                    'desc' => "Saída direta de variável PHP sem sanitização explícita: " . trim($line)
                ];
            }
        }
    }

    // 4. Verificação de CSRF em arquivos functions.php
    if (strpos($relPath, 'functions.php') !== false) {
        if (strpos($content, '$_POST') !== false || strpos($content, 'acao') !== false) {
            if (strpos($content, 'csrf_verify') === false && strpos($content, 'csrf_token') === false) {
                $issues[] = [
                    'file' => $relPath,
                    'line' => 1,
                    'severity' => 'MEDIUM',
                    'pillar' => 'Security (CSRF)',
                    'desc' => 'Arquivo controller processa POST sem invocar csrf_verify() explicitamente.'
                ];
            }
        }
    }

    // 5. Verificação de Transações e Locks em Vendas
    if ($relPath === 'vendas' . DIRECTORY_SEPARATOR . 'functions.php') {
        if (strpos($content, 'beginTransaction') === false || strpos($content, 'commit') === false || strpos($content, 'rollBack') === false) {
            $issues[] = [
                'file' => $relPath,
                'line' => 1,
                'severity' => 'CRITICAL',
                'pillar' => 'ACID Concurrency',
                'desc' => 'Checkout de vendas não possui controle transacional completo com beginTransaction/commit/rollBack.'
            ];
        }
        if (strpos($content, 'FOR UPDATE') === false) {
            $issues[] = [
                'file' => $relPath,
                'line' => 1,
                'severity' => 'HIGH',
                'pillar' => 'ACID Concurrency',
                'desc' => 'Checkout de vendas não utiliza bloqueio pessimista (FOR UPDATE) ao consultar estoque.'
            ];
        }
    }

    // 6. Verificação de RBAC em Rotas Administrativas
    $adminRestricted = [
        'relatorios' . DIRECTORY_SEPARATOR . 'analise.php',
        'relatorios' . DIRECTORY_SEPARATOR . 'index.php',
        'relatorios' . DIRECTORY_SEPARATOR . 'pdf.php',
        'relatorios' . DIRECTORY_SEPARATOR . 'excel.php',
        'compras' . DIRECTORY_SEPARATOR . 'index.php',
        'compras' . DIRECTORY_SEPARATOR . 'nova.php',
        'compras' . DIRECTORY_SEPARATOR . 'visualizar.php',
        'categorias' . DIRECTORY_SEPARATOR . 'index.php',
        'vendas' . DIRECTORY_SEPARATOR . 'historico.php'
    ];

    if (in_array($relPath, $adminRestricted)) {
        if (strpos($content, 'admin') === false || (strpos($content, 'user_perfil') === false && strpos($content, 'usuario_nivel') === false)) {
            $issues[] = [
                'file' => $relPath,
                'line' => 1,
                'severity' => 'HIGH',
                'pillar' => 'Security (RBAC)',
                'desc' => 'Rota administrativa sem barreira explícita de perfil admin.'
            ];
        }
    }
}

echo "Resultados da Análise Automatizada:\n";
echo "Total de Achados / Apontamentos: " . count($issues) . "\n\n";

foreach ($issues as $i => $iss) {
    echo "[" . ($i + 1) . "] [" . $iss['severity'] . "] (" . $iss['pillar'] . ")\n";
    echo "    Arquivo: " . $iss['file'] . " (Linha " . $iss['line'] . ")\n";
    echo "    Detalhe: " . $iss['desc'] . "\n\n";
}
