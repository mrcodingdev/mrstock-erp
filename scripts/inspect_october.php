<?php
require_once __DIR__ . '/../inc/database.php';

echo "--- VENDAS POR DIA EM OUTUBRO/2026 ---\n";
$stmt = $pdo->query("
    SELECT DATE(data_venda) AS dia, 
           COUNT(*) AS qtd_vendas, 
           SUM(total) AS total_faturado
    FROM vendas 
    WHERE MONTH(data_venda) = 10 AND YEAR(data_venda) = 2026 
    GROUP BY DATE(data_venda) 
    ORDER BY dia ASC
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['dia'] . " (" . date('D', strtotime($r['dia'])) . "): " . $r['qtd_vendas'] . " vendas | R$ " . number_format($r['total_faturado'], 2, ',', '.') . "\n";
}

echo "\n--- FATURAMENTO POR CATEGORIA EM OUTUBRO/2026 ---\n";
$stmtCat = $pdo->query("
    SELECT COALESCE(c.nome, p.categoria, 'Geral') AS categoria_nome,
           SUM(vi.quantidade) AS total_itens,
           SUM(vi.quantidade * vi.preco_unitario) AS total_faturado
    FROM vendas_itens vi
    JOIN produtos p ON p.id = vi.produto_id
    LEFT JOIN categorias c ON c.id = p.categoria_id
    JOIN vendas v ON v.id = vi.venda_id
    WHERE MONTH(v.data_venda) = 10 AND YEAR(v.data_venda) = 2026
    GROUP BY COALESCE(c.nome, p.categoria, 'Geral')
    ORDER BY total_faturado DESC
");
while ($r = $stmtCat->fetch(PDO::FETCH_ASSOC)) {
    echo str_pad($r['categoria_nome'], 25) . ": " . str_pad($r['total_itens'] . " un", 10) . " | R$ " . number_format($r['total_faturado'], 2, ',', '.') . "\n";
}
