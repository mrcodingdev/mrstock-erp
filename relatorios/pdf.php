<?php
/**
 * MrStock ERP - Emissor de Relatórios Oficiais em PDF / Impressão Executiva A4
 * Padrão Corporativo Papelaria Real — Design System SalesOps (v0)
 */
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Bloqueio de Acesso RBAC (Exclusivo Administradores)
require_admin();

// ── Ingestão de Parâmetros e Filtros ────────────────────────────────────────
$tipo        = trim($_GET['tipo'] ?? 'completo');
$data_inicio = trim($_GET['data_inicio'] ?? '');
$data_fim    = trim($_GET['data_fim'] ?? '');

$dataEmissao    = date('d/m/Y H:i:s');
$nomeOperador   = htmlspecialchars($_SESSION['user_name'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');
$perfilOperador = strtoupper(htmlspecialchars($_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? 'ADMIN', ENT_QUOTES, 'UTF-8'));

// ── Roteamento e Queries Especializadas por Tipo de Relatório ───────────────
if ($tipo === 'baixo') {
    $titulo        = "Relatório de Ruptura & Estoque Crítico";
    $tituloCurto   = "Ruptura de Estoque";
    $descricao     = "Listagem técnica de itens com saldo físico igual ou inferior ao estoque mínimo de segurança cadastrado.";
    $tagBadge      = "Prioridade de Reposição";
    $tagClass      = "badge-danger";

    $stmt = $pdo->query("
        SELECT p.id, p.nome, p.categoria, p.quantidade, p.estoque_minimo, 
               COALESCE(p.preco_compra, 0) as preco_compra, p.preco_venda,
               f.nome AS fornecedor_nome,
               GREATEST(0, (p.estoque_minimo - p.quantidade)) AS deficit,
               (GREATEST(0, (p.estoque_minimo - p.quantidade)) * COALESCE(p.preco_compra, 0)) AS custo_reposicao
        FROM produtos p 
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
        WHERE p.quantidade <= p.estoque_minimo AND p.status = 'ativo' 
        ORDER BY p.quantidade ASC, p.nome ASC
    ");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cálculos de KPIs
    $totalRegistros   = count($dados);
    $totalDeficit     = array_sum(array_column($dados, 'deficit'));
    $totalCustoRepor  = array_sum(array_column($dados, 'custo_reposicao'));
    $totalEstoqueReal = array_sum(array_column($dados, 'quantidade'));

} elseif ($tipo === 'validade') {
    $titulo        = "Relatório de Validades & Vencimentos (Shelf-Life)";
    $tituloCurto   = "Validades & Vencimentos";
    $descricao     = "Monitoramento preventivo de mercadorias com prazo de validade expirado ou com expiração nos próximos 30 dias.";
    $tagBadge      = "Controle Sanitário";
    $tagClass      = "badge-warning";

    $stmt = $pdo->query("
        SELECT p.id, p.nome, p.categoria, p.quantidade, p.validade,
               COALESCE(p.preco_compra, 0) as preco_compra, p.preco_venda,
               f.nome AS fornecedor_nome,
               DATEDIFF(p.validade, CURDATE()) AS dias_restantes,
               (p.quantidade * p.preco_venda) AS valor_em_risco
        FROM produtos p 
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
        WHERE p.validade IS NOT NULL 
          AND p.validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
          AND p.status = 'ativo' 
        ORDER BY p.validade ASC, p.nome ASC
    ");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cálculos de KPIs
    $totalRegistros   = count($dados);
    $totalItensRisco  = array_sum(array_column($dados, 'quantidade'));
    $totalValorRisco  = array_sum(array_column($dados, 'valor_em_risco'));
    $totalVencidos    = 0;
    foreach ($dados as $item) {
        if ((int)$item['dias_restantes'] < 0) {
            $totalVencidos++;
        }
    }

} elseif ($tipo === 'vendas') {
    $titulo      = "Relatório Comercial de Vendas (PDV)";
    $tituloCurto = "Histórico de Vendas";
    $tagBadge    = "Frente de Caixa";
    $tagClass    = "badge-success";

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
        $descricao = "Listagem analítica de transações comerciais emitidas entre " . date('d/m/Y', strtotime($data_inicio)) . " e " . date('d/m/Y', strtotime($data_fim)) . ".";
    } elseif (!empty($data_inicio)) {
        $descricao = "Listagem analítica de transações comerciais emitidas a partir de " . date('d/m/Y', strtotime($data_inicio)) . ".";
    } else {
        $descricao = "Consolidado cronológico de todas as transações comerciais realizadas no Ponto de Venda (PDV).";
    }

    $whereVSql = implode(' AND ', $whereV);
    $stmt = $pdo->prepare("
        SELECT v.id, v.data_venda, v.forma_pagamento, v.total, v.desconto,
               COALESCE(c.nome, 'Consumidor Final') AS cliente_nome,
               COALESCE(u.username, 'Caixa') AS operador_nome
        FROM vendas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        LEFT JOIN usuarios u ON v.usuario_id = u.id 
        WHERE {$whereVSql} 
        ORDER BY v.data_venda DESC, v.id DESC
    ");
    $stmt->execute($paramsV);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cálculos de KPIs
    $totalRegistros     = count($dados);
    $totalFaturado      = array_sum(array_column($dados, 'total'));
    $totalDescontos     = array_sum(array_column($dados, 'desconto'));
    $ticketMedio        = $totalRegistros > 0 ? ($totalFaturado / $totalRegistros) : 0;

} else {
    // tipo === 'completo' (Inventário Geral)
    $tipo          = 'completo';
    $titulo        = "Relatório de Inventário Geral & Saldos Físicos";
    $tituloCurto   = "Inventário Geral";
    $descricao     = "Posição completa do catálogo de produtos ativos, demonstrando estoque físico, custos de compra, preços de venda e patrimônio líquido.";
    $tagBadge      = "Posição Global";
    $tagClass      = "badge-primary";

    $stmt = $pdo->query("
        SELECT p.id, p.nome, p.categoria, p.quantidade, p.estoque_minimo, 
               COALESCE(p.preco_compra, 0) as preco_compra, p.preco_venda,
               f.nome AS fornecedor_nome,
               (p.quantidade * p.preco_venda) AS total_venda,
               (p.quantidade * COALESCE(p.preco_compra, 0)) AS total_custo
        FROM produtos p 
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
        WHERE p.status = 'ativo' 
        ORDER BY p.nome ASC
    ");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cálculos de KPIs
    $totalRegistros     = count($dados);
    $totalVolumeFisico  = array_sum(array_column($dados, 'quantidade'));
    $totalPatrimonio    = array_sum(array_column($dados, 'total_venda'));
    $totalCustoEstoque  = array_sum(array_column($dados, 'total_custo'));
    $totalLucroEstimado = $totalPatrimonio - $totalCustoEstoque;
}

// Parâmetros para link de exportação Excel com os mesmos filtros
$excelParams = ['tipo' => $tipo];
if (!empty($data_inicio)) $excelParams['data_inicio'] = $data_inicio;
if (!empty($data_fim))    $excelParams['data_fim']    = $data_fim;
$excelUrl = BASE_URL . '/relatorios/excel.php?' . http_build_query($excelParams);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?> — MrStock ERP</title>
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css">
    <style>
        :root {
            --mr-bg-primary: #284936;
            --mr-bg-dark: #222d31;
            --mr-bg-darker: #1a2421;
            --mr-accent: #6ae49b;
        }

        body {
            background-color: #334155;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tabular-nums {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        /* Barra Flutuante de Ações (No-Print) */
        .report-action-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: rgba(26, 36, 33, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 10000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
        }

        .report-action-bar .brand-text {
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .report-action-bar .brand-badge {
            background: var(--mr-bg-primary);
            color: var(--mr-accent);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid rgba(106, 228, 155, 0.3);
        }

        /* Documento A4 Executivo */
        .a4-document-wrapper {
            padding: 80px 15px 40px 15px;
            display: flex;
            justify-content: center;
        }

        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 16mm;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            position: relative;
        }

        /* Cabeçalho Institucional */
        .doc-header {
            border-bottom: 2px solid var(--mr-bg-primary);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .doc-brand-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--mr-bg-primary);
            letter-spacing: -0.02em;
            margin: 0;
            line-height: 1.2;
        }

        .doc-brand-sub {
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
        }

        .doc-meta-box {
            font-size: 0.72rem;
            color: #475569;
            text-align: right;
            line-height: 1.35;
        }

        .doc-meta-box strong {
            color: #0f172a;
        }

        .doc-title-block {
            margin-top: 10px;
        }

        .doc-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 3px 0;
            letter-spacing: -0.02em;
        }

        .doc-desc {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0;
            line-height: 1.3;
        }

        /* Barra de KPIs do Relatório */
        .kpi-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .kpi-mini-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            border-left: 3.5px solid var(--mr-bg-primary);
        }

        .kpi-mini-card.kpi-danger {
            border-left-color: #ef4444;
        }

        .kpi-mini-card.kpi-warning {
            border-left-color: #f59e0b;
        }

        .kpi-mini-card.kpi-success {
            border-left-color: #10b981;
        }

        .kpi-mini-card .kpi-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.04em;
            display: block;
            margin-bottom: 2px;
        }

        .kpi-mini-card .kpi-value {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.1;
        }

        .kpi-mini-card .kpi-sub {
            font-size: 0.65rem;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }

        /* Tabela Executiva de Alta Legibilidade */
        .table-doc {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            margin-bottom: 15px;
        }

        .table-doc thead th {
            background-color: var(--mr-bg-primary) !important;
            color: #ffffff !important;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 7px 8px;
            border: 1px solid #1a3325;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-doc tbody td {
            padding: 5.5px 8px;
            border: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
            line-height: 1.25;
        }

        .table-doc tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .table-doc tfoot td {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-top: 2px solid var(--mr-bg-primary);
        }

        /* Status Badges no Documento */
        .doc-badge {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .doc-badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .doc-badge-warning {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
        }
        .doc-badge-success {
            background-color: #d1fae5;
            color: #047857;
            border: 1px solid #6ee7b7;
        }
        .doc-badge-primary {
            background-color: rgba(40, 73, 54, 0.12);
            color: var(--mr-bg-primary);
            border: 1px solid rgba(40, 73, 54, 0.25);
        }

        /* Rodapé Institucional */
        .doc-footer {
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            margin-top: 20px;
            font-size: 0.65rem;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* @media print — Regras Estritas para Papel A4 */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .report-action-bar, .no-print {
                display: none !important;
            }

            .a4-document-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            .a4-sheet {
                width: 100% !important;
                min-height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr {
                page-break-inside: avoid;
            }

            .kpi-summary-grid, .doc-header, .table-doc {
                page-break-inside: avoid;
            }

            @page {
                size: A4 portrait;
                margin: 12mm 10mm 15mm 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- ══ BARRA FLUTUANTE DE AÇÕES RÁPIDAS (NO-PRINT) ═════════════════════════ -->
    <div class="report-action-bar no-print">
        <div class="brand-text">
            <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
            <span>MrStock ERP</span>
            <span class="brand-badge">OFICIAL A4</span>
            <span class="text-white-50 d-none d-md-inline">| <?= htmlspecialchars($tituloCurto, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary fw-bold shadow-sm text-white">
                <i class="fa-solid fa-print me-1"></i> Imprimir / Salvar PDF
            </button>
            <a href="<?= $excelUrl ?>" class="btn btn-success fw-bold shadow-sm text-white">
                <i class="fa-solid fa-file-excel me-1"></i> Exportar Excel
            </a>
            <a href="<?= BASE_URL ?>/relatorios/index.php" class="btn btn-secondary fw-semibold shadow-sm text-white">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar à Central
            </a>
        </div>
    </div>

    <!-- ══ FOLHA A4 CORPORATIVA ═══════════════════════════════════════════════ -->
    <div class="a4-document-wrapper">
        <div class="a4-sheet">
            <div>
                <!-- Cabeçalho Institucional -->
                <div class="doc-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:42px;height:42px;background:var(--mr-bg-primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#6ae49b;font-size:1.3rem;box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                                <i class="fa-solid fa-cubes"></i>
                            </div>
                            <div>
                                <h1 class="doc-brand-title">PAPELARIA REAL LTDA. — MRSTOCK ERP</h1>
                                <p class="doc-brand-sub">Sistema Integrado de Gestão Empresarial & Frente de Caixa PDV</p>
                            </div>
                        </div>
                        <div class="doc-meta-box">
                            <div><strong>Emissão:</strong> <span class="tabular-nums"><?= $dataEmissao ?></span></div>
                            <div><strong>Operador:</strong> <?= $nomeOperador ?> (<?= $perfilOperador ?>)</div>
                            <div><strong>Documento:</strong> <span class="tabular-nums">#REL-<?= strtoupper(substr($tipo, 0, 3)) ?>-<?= date('ymd') ?></span></div>
                        </div>
                    </div>

                    <div class="doc-title-block">
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="doc-title"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h2>
                            <span class="doc-badge doc-<?= $tagClass ?>"><?= $tagBadge ?></span>
                        </div>
                        <p class="doc-desc"><?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <!-- ══ BARRA DE KPIS DE RESUMO EXECUTIVO ══════════════════════ -->
                <div class="kpi-summary-grid">
                    <?php if ($tipo === 'baixo'): ?>
                        <div class="kpi-mini-card kpi-danger">
                            <span class="kpi-label">Itens Críticos</span>
                            <div class="kpi-value tabular-nums"><?= $totalRegistros ?></div>
                            <span class="kpi-sub">produtos abaixo do mín.</span>
                        </div>
                        <div class="kpi-mini-card kpi-danger">
                            <span class="kpi-label">Estoque Físico Atual</span>
                            <div class="kpi-value tabular-nums"><?= number_format($totalEstoqueReal, 0, ',', '.') ?> un</div>
                            <span class="kpi-sub">soma das unidades em saldo</span>
                        </div>
                        <div class="kpi-mini-card kpi-warning">
                            <span class="kpi-label">Déficit de Reposição</span>
                            <div class="kpi-value tabular-nums"><?= number_format($totalDeficit, 0, ',', '.') ?> un</div>
                            <span class="kpi-sub">unidades para atingir o mín.</span>
                        </div>
                        <div class="kpi-mini-card kpi-danger">
                            <span class="kpi-label">Custo Estimado Repor</span>
                            <div class="kpi-value tabular-nums">R$ <?= number_format($totalCustoRepor, 2, ',', '.') ?></div>
                            <span class="kpi-sub">investimento para regularizar</span>
                        </div>

                    <?php elseif ($tipo === 'validade'): ?>
                        <div class="kpi-mini-card kpi-warning">
                            <span class="kpi-label">Lotes Monitorados</span>
                            <div class="kpi-value tabular-nums"><?= $totalRegistros ?></div>
                            <span class="kpi-sub">itens na janela de atenção</span>
                        </div>
                        <div class="kpi-mini-card kpi-danger">
                            <span class="kpi-label">Lotes Já Vencidos</span>
                            <div class="kpi-value tabular-nums"><?= $totalVencidos ?></div>
                            <span class="kpi-sub">itens com baixa contábil</span>
                        </div>
                        <div class="kpi-mini-card kpi-warning">
                            <span class="kpi-label">Volume Físico em Risco</span>
                            <div class="kpi-value tabular-nums"><?= number_format($totalItensRisco, 0, ',', '.') ?> un</div>
                            <span class="kpi-sub">unidades afetadas</span>
                        </div>
                        <div class="kpi-mini-card kpi-danger">
                            <span class="kpi-label">Patrimônio em Risco</span>
                            <div class="kpi-value tabular-nums">R$ <?= number_format($totalValorRisco, 2, ',', '.') ?></div>
                            <span class="kpi-sub">valor potencial de perda</span>
                        </div>

                    <?php elseif ($tipo === 'vendas'): ?>
                        <div class="kpi-mini-card kpi-success">
                            <span class="kpi-label">Transações Realizadas</span>
                            <div class="kpi-value tabular-nums"><?= $totalRegistros ?></div>
                            <span class="kpi-sub">pedidos emitidos no PDV</span>
                        </div>
                        <div class="kpi-mini-card kpi-success">
                            <span class="kpi-label">Faturamento Total</span>
                            <div class="kpi-value tabular-nums">R$ <?= number_format($totalFaturado, 2, ',', '.') ?></div>
                            <span class="kpi-sub">receita bruta de vendas</span>
                        </div>
                        <div class="kpi-mini-card">
                            <span class="kpi-label">Ticket Médio</span>
                            <div class="kpi-value tabular-nums">R$ <?= number_format($ticketMedio, 2, ',', '.') ?></div>
                            <span class="kpi-sub">média por transação</span>
                        </div>
                        <div class="kpi-mini-card kpi-warning">
                            <span class="kpi-label">Descontos Concedidos</span>
                            <div class="kpi-value tabular-nums">R$ <?= number_format($totalDescontos, 2, ',', '.') ?></div>
                            <span class="kpi-sub">abatimentos comerciais</span>
                        </div>

                    <?php else: ?>
                        <!-- Completo / Inventário -->
                        <div class="kpi-mini-card">
                            <span class="kpi-label">Mix de Produtos</span>
                            <div class="kpi-value tabular-nums"><?= $totalRegistros ?></div>
                            <span class="kpi-sub">itens ativos cadastrados</span>
                        </div>
                        <div class="kpi-mini-card">
                            <span class="kpi-label">Volume Total Físico</span>
                            <div class="kpi-value tabular-nums"><?= number_format($totalVolumeFisico, 0, ',', '.') ?> un</div>
                            <span class="kpi-sub">unidades estocadas</span>
                        </div>
                        <div class="kpi-mini-card kpi-success">
                            <span class="kpi-label">Patrimônio em Venda</span>
                            <div class="kpi-value tabular-nums">R$ <?= number_format($totalPatrimonio, 2, ',', '.') ?></div>
                            <span class="kpi-sub">valor bruto estimado</span>
                        </div>
                        <div class="kpi-mini-card">
                            <span class="kpi-label">Custo do Estoque</span>
                            <div class="kpi-value tabular-nums">R$ <?= number_format($totalCustoEstoque, 2, ',', '.') ?></div>
                            <span class="kpi-sub">Lucro teór.: R$ <?= number_format($totalLucroEstimado, 2, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ══ TABELA DE DADOS DO RELATÓRIO ════════════════════════════ -->
                <table class="table-doc">
                    <thead>
                        <tr>
                            <?php if ($tipo === 'vendas'): ?>
                                <th scope="col" style="width:10%;" class="text-center">ID Venda</th>
                                <th scope="col" style="width:18%;">Data / Hora</th>
                                <th scope="col" style="width:28%;">Cliente</th>
                                <th scope="col" style="width:18%;">Operador</th>
                                <th scope="col" style="width:14%;">Pagamento</th>
                                <th scope="col" style="width:12%;" class="text-end">Total (R$)</th>

                            <?php elseif ($tipo === 'baixo'): ?>
                                <th scope="col" style="width:8%;" class="text-center">Cód</th>
                                <th scope="col" style="width:34%;">Descrição do Produto</th>
                                <th scope="col" style="width:20%;">Fornecedor</th>
                                <th scope="col" style="width:10%;" class="text-center">Saldo Atual</th>
                                <th scope="col" style="width:10%;" class="text-center">Est. Mín</th>
                                <th scope="col" style="width:8%;" class="text-center">Déficit</th>
                                <th scope="col" style="width:10%;" class="text-end">Custo Reposição</th>

                            <?php elseif ($tipo === 'validade'): ?>
                                <th scope="col" style="width:8%;" class="text-center">Cód</th>
                                <th scope="col" style="width:34%;">Descrição do Produto</th>
                                <th scope="col" style="width:18%;">Fornecedor</th>
                                <th scope="col" style="width:10%;" class="text-center">Qtd Lote</th>
                                <th scope="col" style="width:12%;" class="text-center">Data Validade</th>
                                <th scope="col" style="width:8%;" class="text-center">Status</th>
                                <th scope="col" style="width:10%;" class="text-end">Valor em Risco</th>

                            <?php else: ?>
                                <!-- Completo -->
                                <th scope="col" style="width:7%;" class="text-center">Cód</th>
                                <th scope="col" style="width:35%;">Descrição do Produto</th>
                                <th scope="col" style="width:18%;">Fornecedor</th>
                                <th scope="col" style="width:10%;" class="text-center">Estoque</th>
                                <th scope="col" style="width:10%;" class="text-end">Custo Unit.</th>
                                <th scope="col" style="width:10%;" class="text-end">Venda Unit.</th>
                                <th scope="col" style="width:10%;" class="text-end">Total Venda</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dados)): ?>
                            <?php foreach ($dados as $d): ?>
                            <tr>
                                <?php if ($tipo === 'vendas'): ?>
                                    <td class="text-center fw-bold tabular-nums">#<?= str_pad((string)$d['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td class="tabular-nums"><?= date('d/m/Y H:i', strtotime($d['data_venda'])) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars((string)$d['cliente_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-muted"><?= htmlspecialchars((string)$d['operador_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="doc-badge doc-badge-primary"><?= htmlspecialchars((string)$d['forma_pagamento'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="text-end fw-bold tabular-nums text-dark">R$ <?= number_format((float)$d['total'], 2, ',', '.') ?></td>

                                <?php elseif ($tipo === 'baixo'): ?>
                                    <td class="text-center tabular-nums">#<?= str_pad((string)$d['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-muted"><?= htmlspecialchars((string)($d['fornecedor_nome'] ?? '--'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-center fw-bold text-danger tabular-nums"><?= (int)$d['quantidade'] ?> un</td>
                                    <td class="text-center tabular-nums"><?= (int)$d['estoque_minimo'] ?> un</td>
                                    <td class="text-center fw-bold text-danger tabular-nums">+<?= (int)$d['deficit'] ?> un</td>
                                    <td class="text-end fw-bold tabular-nums">R$ <?= number_format((float)$d['custo_reposicao'], 2, ',', '.') ?></td>

                                <?php elseif ($tipo === 'validade'): ?>
                                    <?php 
                                        $dias = (int)$d['dias_restantes'];
                                        $statusClass = $dias < 0 ? 'doc-badge-danger' : 'doc-badge-warning';
                                        $statusText  = $dias < 0 ? 'VENCIDO (' . abs($dias) . 'd)' : ($dias === 0 ? 'VENCE HOJE' : 'EM ' . $dias . ' DIAS');
                                    ?>
                                    <td class="text-center tabular-nums">#<?= str_pad((string)$d['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-muted"><?= htmlspecialchars((string)($d['fornecedor_nome'] ?? '--'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-center fw-bold tabular-nums"><?= (int)$d['quantidade'] ?> un</td>
                                    <td class="text-center tabular-nums fw-semibold"><?= date('d/m/Y', strtotime($d['validade'])) ?></td>
                                    <td class="text-center"><span class="doc-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td class="text-end fw-bold tabular-nums">R$ <?= number_format((float)$d['valor_em_risco'], 2, ',', '.') ?></td>

                                <?php else: ?>
                                    <!-- Completo -->
                                    <td class="text-center tabular-nums">#<?= str_pad((string)$d['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-muted"><?= htmlspecialchars((string)($d['fornecedor_nome'] ?? '--'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-center fw-bold tabular-nums"><?= (int)$d['quantidade'] ?> un</td>
                                    <td class="text-end tabular-nums text-muted">R$ <?= number_format((float)$d['preco_compra'], 2, ',', '.') ?></td>
                                    <td class="text-end tabular-nums">R$ <?= number_format((float)$d['preco_venda'], 2, ',', '.') ?></td>
                                    <td class="text-end fw-bold tabular-nums text-dark">R$ <?= number_format((float)$d['total_venda'], 2, ',', '.') ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                    Nenhum registro encontrado para os critérios selecionados neste relatório.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <?php if ($tipo === 'vendas'): ?>
                                <td colspan="5" class="text-end fw-bold">TOTAL GERAL FATURADO:</td>
                                <td class="text-end fw-bold tabular-nums">R$ <?= number_format((float)$totalFaturado, 2, ',', '.') ?></td>

                            <?php elseif ($tipo === 'baixo'): ?>
                                <td colspan="5" class="text-end fw-bold">INVESTIMENTO TOTAL ESTIMADO PARA REPOSIÇÃO:</td>
                                <td class="text-center fw-bold tabular-nums">+<?= number_format($totalDeficit, 0, ',', '.') ?> un</td>
                                <td class="text-end fw-bold tabular-nums">R$ <?= number_format((float)$totalCustoRepor, 2, ',', '.') ?></td>

                            <?php elseif ($tipo === 'validade'): ?>
                                <td colspan="6" class="text-end fw-bold">VALOR PATRIMONIAL TOTAL EM RISCO (30 DIAS / EXPIRADOS):</td>
                                <td class="text-end fw-bold tabular-nums">R$ <?= number_format((float)$totalValorRisco, 2, ',', '.') ?></td>

                            <?php else: ?>
                                <td colspan="3" class="text-end fw-bold">TOTAIS CONSOLIDADOS DO INVENTÁRIO:</td>
                                <td class="text-center fw-bold tabular-nums"><?= number_format($totalVolumeFisico, 0, ',', '.') ?> un</td>
                                <td colspan="2" class="text-end fw-bold">PATRIMÔNIO GLOBAL ESTIMADO:</td>
                                <td class="text-end fw-bold tabular-nums">R$ <?= number_format((float)$totalPatrimonio, 2, ',', '.') ?></td>
                            <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Rodapé Institucional A4 -->
            <div class="doc-footer">
                <div>
                    <strong>MrStock ERP v2.0</strong> — Sistema Especialista em Gestão de Varejo & Papelaria • Papelaria Real Ltda.
                </div>
                <div>
                    Documento Oficial • Autenticação de Auditoria: <span class="tabular-nums">MR-<?= strtoupper(substr(md5($dataEmissao . $tipo), 0, 8)) ?></span>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
</body>
</html>

