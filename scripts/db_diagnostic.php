<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Acesso negado: este script só pode ser executado via linha de comando (CLI).\n");
}
require_once __DIR__ . '/../inc/database.php';

echo "=== DIAGNÓSTICO DO BANCO DE DADOS (mrstock_db) ===\n";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Conexão estabelecida com sucesso!\n";
    echo "Total de Tabelas: " . count($tables) . "\n\n";

    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo sprintf("  %-20s : %4d registros\n", $t, $count);
    }

    echo "\n=== AUDITORIA DE PRODUTOS DA PAPELARIA REAL ===\n";
    $stmtProds = $pdo->query("SELECT id, nome, preco_compra, preco_venda, quantidade, estoque_minimo, validade, codigo_de_barra FROM produtos ORDER BY id ASC");
    $prods = $stmtProds->fetchAll(PDO::FETCH_ASSOC);
    echo sprintf("%-4s | %-42s | %-8s | %-8s | %-7s | %-5s | %-13s\n", "ID", "Nome", "Custo", "Venda", "Margem", "Saldo", "Cód. Barras");
    echo str_repeat("-", 100) . "\n";

    foreach ($prods as $p) {
        $custo = (float)$p['preco_compra'];
        $venda = (float)$p['preco_venda'];
        $margem = $custo > 0 ? (($venda - $custo) / $custo) * 100 : 0;
        echo sprintf("%-4d | %-42s | R$ %5.2f | R$ %5.2f | %5.1f%% | %4d  | %-13s\n",
            $p['id'],
            substr($p['nome'], 0, 42),
            $custo,
            $venda,
            $margem,
            $p['quantidade'],
            $p['codigo_de_barra'] ?? 'N/A'
        );
    }

} catch (Exception $e) {
    echo "Erro no diagnóstico: " . $e->getMessage() . "\n";
}
