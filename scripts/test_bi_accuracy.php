<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/database.php';

// Teste de Integridade: Soma do faturamento do BI vs Soma real de vendas
$stmtVendas = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM vendas");
$totalVendas = (float)$stmtVendas->fetchColumn();

$stmtItens = $pdo->query("SELECT COALESCE(SUM(quantidade * preco_unitario), 0) FROM vendas_itens");
$totalItens = (float)$stmtItens->fetchColumn();

// Consulta idêntica à de relatorios/analise.php
$stmtBI = $pdo->query("
    SELECT COALESCE(SUM(vi.quantidade * vi.preco_unitario), 0) AS faturamento_bi
    FROM vendas v
    LEFT JOIN vendas_itens vi ON v.id = vi.venda_id
    LEFT JOIN produtos p ON p.id = vi.produto_id
");
$totalBI = (float)$stmtBI->fetchColumn();

echo "Total Vendas: R$ " . number_format($totalVendas, 2, ',', '.') . "\n";
echo "Total Itens:  R$ " . number_format($totalItens, 2, ',', '.') . "\n";
echo "Total BI:     R$ " . number_format($totalBI, 2, ',', '.') . "\n";

if (abs($totalVendas - $totalBI) < 0.01 && abs($totalItens - $totalBI) < 0.01) {
    echo "\n>>> [SUCESSO] Faturamento do BI e Histórico de Vendas coincidem em 100.00%!\n";
    exit(0);
} else {
    echo "\n>>> [FALHA] Discrepância detectada no cálculo de faturamento!\n";
    exit(1);
}