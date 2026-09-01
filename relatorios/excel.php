<?php
/**
 * MrStock ERP - Exportação de Relatórios em Excel (.xls)
 * Trava estrita em 9 colunas reais (A até I) para inventário e produtos.
 */
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Bloqueio de Acesso RBAC
require_admin();

$tipo        = trim($_GET['tipo'] ?? 'completo');
$data_inicio = trim($_GET['data_inicio'] ?? '');
$data_fim    = trim($_GET['data_fim'] ?? '');

if ($data_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio)) {
    $data_inicio = '';
}
if ($data_fim && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    $data_fim = '';
}

if ($tipo === 'baixo') {
    $nome_arquivo    = "estoque_baixo_" . date('Ymd_H_i') . ".xls";
    $tituloRelatorio = "Alerta de Estoque Baixo (Reposição Crítica)";
    $sql = "
        SELECT p.id AS Codigo, 
               p.nome AS Produto, 
               p.categoria AS Categoria, 
               p.quantidade AS Estoque_Atual, 
               p.estoque_minimo AS Estoque_Minimo, 
               COALESCE(DATE_FORMAT(p.validade, '%d/%m/%Y'), 'N/A') AS Data_Vencimento, 
               p.preco_venda AS Preco_Venda_Unitario, 
               COALESCE(p.preco_compra, 0) AS Preco_Custo, 
               COALESCE(f.nome, 'Sem Fornecedor') AS Fornecedor 
        FROM produtos p 
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
        WHERE p.quantidade <= p.estoque_minimo AND p.status = 'ativo'
        ORDER BY p.quantidade ASC, p.nome ASC
    ";
    $stmt  = $pdo->query($sql);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($tipo === 'validade') {
    $nome_arquivo    = "vencimentos_" . date('Ymd_H_i') . ".xls";
    $tituloRelatorio = "Validades e Vencimentos (Próximos 30 dias)";
    $sql = "
        SELECT p.id AS Codigo, 
               p.nome AS Produto, 
               p.categoria AS Categoria, 
               p.quantidade AS Estoque_Atual, 
               p.estoque_minimo AS Estoque_Minimo, 
               COALESCE(DATE_FORMAT(p.validade, '%d/%m/%Y'), 'N/A') AS Data_Vencimento, 
               p.preco_venda AS Preco_Venda_Unitario, 
               COALESCE(p.preco_compra, 0) AS Preco_Custo, 
               COALESCE(f.nome, 'Sem Fornecedor') AS Fornecedor 
        FROM produtos p 
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
        WHERE p.validade IS NOT NULL AND p.validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND p.status = 'ativo'
        ORDER BY p.validade ASC, p.nome ASC
    ";
    $stmt  = $pdo->query($sql);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($tipo === 'vendas') {
    $nome_arquivo    = "relatorio_vendas_" . date('Ymd_H_i') . ".xls";
    $tituloRelatorio = "Histórico de Vendas Realizadas (PDV)";
    
    $whereV = ["1=1"];
    $paramsV = [];
    if (!empty($data_inicio)) {
        $whereV[] = "DATE(v.data_venda) >= :data_inicio";
        $paramsV[':data_inicio'] = $data_inicio;
    }
    if (!empty($data_fim)) {
        $whereV[] = "DATE(v.data_venda) <= :data_fim";
        $paramsV[':data_fim'] = $data_fim;
    }
    if (!empty($data_inicio) && !empty($data_fim)) {
        $tituloRelatorio .= " (" . date('d/m/Y', strtotime($data_inicio)) . " a " . date('d/m/Y', strtotime($data_fim)) . ")";
    }

    $whereVSql = implode(' AND ', $whereV);
    $sql = "
        SELECT v.id AS Codigo_Venda, 
               COALESCE(c.nome, 'Consumidor Final') AS Cliente, 
               DATE_FORMAT(v.data_venda, '%d/%m/%Y %H:%i') AS Data_Venda, 
               (SELECT COALESCE(SUM(vi.quantidade), 0) FROM vendas_itens vi WHERE vi.venda_id = v.id) AS Qtd_Itens,
               v.forma_pagamento AS Forma_Pagamento, 
               v.total AS Valor_Total 
        FROM vendas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        WHERE {$whereVSql}
        ORDER BY v.data_venda DESC, v.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($paramsV);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    $nome_arquivo    = "inventario_completo_" . date('Ymd_H_i') . ".xls";
    $tituloRelatorio = "Inventário Completo de Produtos";
    $sql = "
        SELECT p.id AS Codigo, 
               p.nome AS Produto, 
               p.categoria AS Categoria, 
               p.quantidade AS Estoque_Atual, 
               p.estoque_minimo AS Estoque_Minimo, 
               COALESCE(DATE_FORMAT(p.validade, '%d/%m/%Y'), 'N/A') AS Data_Vencimento, 
               p.preco_venda AS Preco_Venda_Unitario, 
               COALESCE(p.preco_compra, 0) AS Preco_Custo, 
               COALESCE(f.nome, 'Sem Fornecedor') AS Fornecedor 
        FROM produtos p 
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
        WHERE p.status = 'ativo'
        ORDER BY p.nome ASC
    ";
    $stmt  = $pdo->query($sql);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

ob_clean();
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$nome_arquivo\"");
header("Pragma: no-cache");
header("Expires: 0");

$totalCols = !empty($dados) ? count(array_keys($dados[0])) : 6;

echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th colspan='{$totalCols}' style='background-color:#284936;color:#ffffff;text-align:center;font-size:15px;padding:8px;'><strong>Papelaria Real — MrStock ERP — " . htmlspecialchars($tituloRelatorio) . "</strong> (Gerado em " . date('d/m/Y H:i') . ")</th></tr>";

if (count($dados) > 0) {
    echo "<tr>";
    foreach (array_keys($dados[0]) as $chave) {
        echo "<th style='background-color:#1e293b;color:#ffffff;text-align:center;font-weight:bold;'>" . htmlspecialchars(ucwords(str_replace('_', ' ', $chave))) . "</th>";
    }
    echo "</tr>";
    foreach ($dados as $row) {
        echo "<tr>";
        foreach ($row as $k => $valor) {
            $align = (in_array($k, ['Codigo', 'Codigo_Venda', 'Estoque_Atual', 'Estoque_Minimo', 'Data_Vencimento', 'Data_Venda', 'Qtd_Itens'])) ? 'text-align:center;' : ((in_array($k, ['Preco_Venda_Unitario', 'Preco_Custo', 'Valor_Total'])) ? 'text-align:right;' : 'text-align:left;');
            $valFormatado = (in_array($k, ['Preco_Venda_Unitario', 'Preco_Custo', 'Valor_Total']) && is_numeric($valor)) ? 'R$ ' . number_format((float)$valor, 2, ',', '.') : htmlspecialchars((string)($valor ?? '--'));
            echo "<td style='{$align}'>" . $valFormatado . "</td>";
        }
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='{$totalCols}' style='text-align:center;padding:15px;'>Nenhum registro encontrado para este relatório.</td></tr>";
}
echo "</table>";
exit;

