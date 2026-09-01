<?php
/**
 * MrStock ERP - Centro de Inteligência e Análise Gerencial
 * Painel com filtros temporais (7 dias, Mês Atual, Ano Atual), KPIs de margem e gráficos Chart.js
 */
$pageTitle  = 'Centro de Análise';
$activePage = 'analise';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// ── 1. Bloqueio de Acesso RBAC (Apenas Admin) ───────────────────────────────
require_admin();

// ── 2. Seletor de Período ───────────────────────────────────────────────────
$periodo = $_GET['periodo'] ?? '7dias';
if (!in_array($periodo, ['7dias', 'mes_atual', 'ano_atual'], true)) {
    $periodo = '7dias';
}

$periodoLabels = [
    '7dias'     => 'Últimos 7 Dias',
    'mes_atual' => 'Mês Atual (' . date('m/Y') . ')',
    'ano_atual' => 'Ano Atual (' . date('Y') . ' - 12 Meses)'
];
$periodoNome = $periodoLabels[$periodo];

// ── 3. Construção dos Intervalos e Queries por Período ───────────────────────
$labelsEvolucao   = [];
$dataFaturamento  = [];
$dataLucro        = [];

if ($periodo === '7dias') {
    $dateCondition = "v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
    
    // Inicializa os 7 dias com 0 para garantir continuidade nos gráficos
    $diasMap = [];
    for ($i = 6; $i >= 0; $i--) {
        $key = date('Y-m-d', strtotime("-$i days"));
        $lbl = date('d/m', strtotime("-$i days"));
        $diasMap[$key] = ['label' => $lbl, 'faturamento' => 0.0, 'lucro' => 0.0];
    }

    $stmtEvolucao = $pdo->query("
        SELECT DATE(v.data_venda) AS dia, 
               COALESCE(SUM(vi.quantidade * vi.preco_unitario), 0) AS total_faturamento,
               COALESCE(SUM(vi.quantidade * (vi.preco_unitario - COALESCE(p.preco_compra, 0))), 0) AS total_lucro
        FROM vendas v
        JOIN vendas_itens vi ON v.id = vi.venda_id
        JOIN produtos p ON p.id = vi.produto_id
        WHERE v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(v.data_venda)
    ");
    while ($row = $stmtEvolucao->fetch(PDO::FETCH_ASSOC)) {
        if (isset($diasMap[$row['dia']])) {
            $diasMap[$row['dia']]['faturamento'] = (float)$row['total_faturamento'];
            $diasMap[$row['dia']]['lucro']       = (float)$row['total_lucro'];
        }
    }

    foreach ($diasMap as $item) {
        $labelsEvolucao[]  = $item['label'];
        $dataFaturamento[] = round($item['faturamento'], 2);
        $dataLucro[]       = round($item['lucro'], 2);
    }

} elseif ($periodo === 'mes_atual') {
    $dateCondition = "MONTH(v.data_venda) = MONTH(CURDATE()) AND YEAR(v.data_venda) = YEAR(CURDATE())";
    
    $diasNoMes = (int)date('t');
    $diasMap = [];
    for ($d = 1; $d <= $diasNoMes; $d++) {
        $key = date('Y-m-') . str_pad((string)$d, 2, '0', STR_PAD_LEFT);
        $lbl = str_pad((string)$d, 2, '0', STR_PAD_LEFT) . '/' . date('m');
        $diasMap[$key] = ['label' => $lbl, 'faturamento' => 0.0, 'lucro' => 0.0];
    }

    $stmtEvolucao = $pdo->query("
        SELECT DATE(v.data_venda) AS dia, 
               COALESCE(SUM(vi.quantidade * vi.preco_unitario), 0) AS total_faturamento,
               COALESCE(SUM(vi.quantidade * (vi.preco_unitario - COALESCE(p.preco_compra, 0))), 0) AS total_lucro
        FROM vendas v
        JOIN vendas_itens vi ON v.id = vi.venda_id
        JOIN produtos p ON p.id = vi.produto_id
        WHERE MONTH(v.data_venda) = MONTH(CURDATE()) AND YEAR(v.data_venda) = YEAR(CURDATE())
        GROUP BY DATE(v.data_venda)
    ");
    while ($row = $stmtEvolucao->fetch(PDO::FETCH_ASSOC)) {
        if (isset($diasMap[$row['dia']])) {
            $diasMap[$row['dia']]['faturamento'] = (float)$row['total_faturamento'];
            $diasMap[$row['dia']]['lucro']       = (float)$row['total_lucro'];
        }
    }

    foreach ($diasMap as $item) {
        $labelsEvolucao[]  = $item['label'];
        $dataFaturamento[] = round($item['faturamento'], 2);
        $dataLucro[]       = round($item['lucro'], 2);
    }

} else { // ano_atual
    $dateCondition = "YEAR(v.data_venda) = YEAR(CURDATE())";
    
    $mesesNomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    $mesesMap = [];
    for ($m = 1; $m <= 12; $m++) {
        $mesesMap[$m] = ['label' => $mesesNomes[$m-1], 'faturamento' => 0.0, 'lucro' => 0.0];
    }

    $stmtEvolucao = $pdo->query("
        SELECT MONTH(v.data_venda) AS mes_num, 
               COALESCE(SUM(vi.quantidade * vi.preco_unitario), 0) AS total_faturamento,
               COALESCE(SUM(vi.quantidade * (vi.preco_unitario - COALESCE(p.preco_compra, 0))), 0) AS total_lucro
        FROM vendas v
        JOIN vendas_itens vi ON v.id = vi.venda_id
        JOIN produtos p ON p.id = vi.produto_id
        WHERE YEAR(v.data_venda) = YEAR(CURDATE())
        GROUP BY MONTH(v.data_venda)
    ");
    while ($row = $stmtEvolucao->fetch(PDO::FETCH_ASSOC)) {
        $mNum = (int)$row['mes_num'];
        if (isset($mesesMap[$mNum])) {
            $mesesMap[$mNum]['faturamento'] = (float)$row['total_faturamento'];
            $mesesMap[$mNum]['lucro']       = (float)$row['total_lucro'];
        }
    }

    foreach ($mesesMap as $item) {
        $labelsEvolucao[]  = $item['label'];
        $dataFaturamento[] = round($item['faturamento'], 2);
        $dataLucro[]       = round($item['lucro'], 2);
    }
}

// ── 4. Totais e KPIs do Período Ativo ─────────────────────────────────────────
$stmtKpiPeriodo = $pdo->query("
    SELECT COUNT(DISTINCT v.id) AS total_vendas,
           COALESCE(SUM(vi.quantidade * vi.preco_unitario), 0) AS faturamento,
           COALESCE(SUM(vi.quantidade * (vi.preco_unitario - COALESCE(p.preco_compra, 0))), 0) AS lucro_bruto
    FROM vendas v
    LEFT JOIN vendas_itens vi ON v.id = vi.venda_id
    LEFT JOIN produtos p ON p.id = vi.produto_id
    WHERE $dateCondition
");
$kpiPeriodo = $stmtKpiPeriodo->fetch(PDO::FETCH_ASSOC);

$faturamentoPeriodo = (float)($kpiPeriodo['faturamento'] ?? 0.0);
$lucroPeriodo       = (float)($kpiPeriodo['lucro_bruto'] ?? 0.0);
$qtdVendasPeriodo   = (int)($kpiPeriodo['total_vendas'] ?? 0);
$ticketMedioPeriodo = $qtdVendasPeriodo > 0 ? ($faturamentoPeriodo / $qtdVendasPeriodo) : 0.0;
$margemPercentual   = $faturamentoPeriodo > 0 ? (($lucroPeriodo / $faturamentoPeriodo) * 100) : 0.0;

// ── 5. KPIs Patrimoniais de Estoque (Otimizado em Consulta Única) ───────────
$stmtEstoque = $pdo->query("
    SELECT 
        COALESCE(SUM(quantidade * preco_venda), 0) AS patrimonio_estoque,
        COALESCE(SUM(quantidade * COALESCE(preco_compra, 0)), 0) AS custo_estoque
    FROM produtos 
    WHERE status = 'ativo'
");
$estoqueData = $stmtEstoque->fetch(PDO::FETCH_ASSOC);

$patrimonioEstoque = (float)($estoqueData['patrimonio_estoque'] ?? 0.0);
$custoEstoque      = (float)($estoqueData['custo_estoque'] ?? 0.0);
$lucroEstoque      = $patrimonioEstoque - $custoEstoque;

// ── 6. Top 5 Produtos Mais Vendidos no Período ───────────────────────────────
$stmtTopProd = $pdo->query("
    SELECT p.id,
           p.nome, 
           SUM(vi.quantidade) AS total_vendido,
           SUM(vi.quantidade * vi.preco_unitario) AS total_faturado
    FROM vendas_itens vi 
    JOIN produtos p ON p.id = vi.produto_id 
    JOIN vendas v ON v.id = vi.venda_id
    WHERE $dateCondition
    GROUP BY p.id, p.nome 
    ORDER BY total_vendido DESC 
    LIMIT 5
");
$topProdutos = $stmtTopProd->fetchAll(PDO::FETCH_ASSOC);
$labelsTopProd = [];
$dataTopProd   = [];
foreach ($topProdutos as $tp) {
    $labelsTopProd[] = mb_strimwidth((string)$tp['nome'], 0, 20, '...');
    $dataTopProd[]   = (int)$tp['total_vendido'];
}

// ── 7. Top Categorias Mais Vendidas no Período (com JOIN e Fallback) ─────────
$stmtTopCat = $pdo->query("
    SELECT COALESCE(c.nome, p.categoria, 'Geral') AS categoria_nome,
           SUM(vi.quantidade) AS total_itens,
           SUM(vi.quantidade * vi.preco_unitario) AS total_faturado
    FROM vendas_itens vi
    JOIN produtos p ON p.id = vi.produto_id
    LEFT JOIN categorias c ON c.id = p.categoria_id
    JOIN vendas v ON v.id = vi.venda_id
    WHERE $dateCondition
    GROUP BY COALESCE(c.nome, p.categoria, 'Geral')
    ORDER BY total_faturado DESC
    LIMIT 5
");
$topCategorias = $stmtTopCat->fetchAll(PDO::FETCH_ASSOC);
$labelsTopCat = [];
$dataTopCat   = [];
foreach ($topCategorias as $tc) {
    $labelsTopCat[] = (string)$tc['categoria_nome'];
    $dataTopCat[]   = round((float)$tc['total_faturado'], 2);
}

$extraHead = '<script src="' . BASE_URL . '/js/chart.min.js"></script><style>.chart-container{position:relative;height:300px;width:100%;}</style>';
require_once __DIR__ . '/../inc/header.php';
?>

    <div class="content-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">
                <i class="fas fa-chart-pie text-primary me-2"></i>Centro de Inteligência & Análise
            </h2>
            <p class="text-muted m-0">Gráficos de vendas, margens de lucro e desempenho por período.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Seletor de Período Sólido -->
            <div class="btn-group shadow-sm" role="group" aria-label="Seletor de Período">
                <a href="<?= BASE_URL ?>/relatorios/analise.php?periodo=7dias" 
                   class="btn btn-sm <?= $periodo === '7dias' ? 'btn-primary active fw-bold text-white' : 'btn-secondary text-white' ?>">
                    <i class="fas fa-calendar-day me-1"></i> 7 Dias
                </a>
                <a href="<?= BASE_URL ?>/relatorios/analise.php?periodo=mes_atual" 
                   class="btn btn-sm <?= $periodo === 'mes_atual' ? 'btn-primary active fw-bold text-white' : 'btn-secondary text-white' ?>">
                    <i class="fas fa-calendar-alt me-1"></i> Mês Atual
                </a>
                <a href="<?= BASE_URL ?>/relatorios/analise.php?periodo=ano_atual" 
                   class="btn btn-sm <?= $periodo === 'ano_atual' ? 'btn-primary active fw-bold text-white' : 'btn-secondary text-white' ?>">
                    <i class="fas fa-calendar me-1"></i> 12 Meses (Ano)
                </a>
            </div>
            <!-- Botão de Impressão Sólido -->
            <button type="button" class="btn btn-sm btn-secondary text-white shadow-sm fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir Relatório
            </button>
        </div>
    </div>

    <div class="content-body">
        <!-- ══ CARDS DE KPIS DO PERÍODO SELECIONADO (BENTO GRID SALESOPS) ════════════ -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Faturamento do Período -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="so-card p-3 mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Faturamento (<?= htmlspecialchars($periodoNome, ENT_QUOTES, 'UTF-8') ?>)</span>
                            <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($faturamentoPeriodo, 2, ',', '.') ?></h3>
                            <small class="text-muted">
                                <?= ($qtdVendasPeriodo === 1 ? '<span class="tabular-nums">1</span> venda registrada' : '<span class="tabular-nums">' . number_format($qtdVendasPeriodo, 0, ',', '.') . '</span> vendas registradas') ?>
                            </small>
                        </div>
                        <div class="kpi-icon-box kpi-icon-box--primary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Lucro Bruto Estimado -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="so-card p-3 mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Lucro Bruto Estimado</span>
                            <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($lucroPeriodo, 2, ',', '.') ?></h3>
                            <small class="text-muted">
                                Margem: <span class="fw-bold tabular-nums text-success"><?= number_format($margemPercentual, 1, ',', '.') ?>%</span>
                            </small>
                        </div>
                        <div class="kpi-icon-box kpi-icon-box--success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Ticket Médio -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="so-card p-3 mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Ticket Médio</span>
                            <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($ticketMedioPeriodo, 2, ',', '.') ?></h3>
                            <small class="text-muted">Média por venda no período</small>
                        </div>
                        <div class="kpi-icon-box kpi-icon-box--info">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Patrimônio em Estoque -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="so-card p-3 mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Patrimônio em Estoque</span>
                            <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($patrimonioEstoque, 2, ',', '.') ?></h3>
                            <small class="text-muted">
                                Lucro projetado: <span class="tabular-nums">R$ <?= number_format($lucroEstoque, 2, ',', '.') ?></span>
                            </small>
                        </div>
                        <div class="kpi-icon-box kpi-icon-box--warning">
                            <i class="fas fa-boxes-stacked"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ LINHA 1 DE GRÁFICOS: EVOLUÇÃO E CATEGORIAS ════════════════════ -->
        <div class="row g-4 mb-4">
            <!-- Gráfico 1: Evolução Financeira (Faturamento e Lucro) -->
            <div class="col-lg-8">
                <div class="so-card h-100 mb-0">
                    <div class="so-card-header">
                        <h5 class="so-card-title">
                            <i class="fas fa-chart-area text-primary"></i> Evolução Financeira: Faturamento vs. Lucro (<?= htmlspecialchars($periodoNome, ENT_QUOTES, 'UTF-8') ?>)
                        </h5>
                    </div>
                    <div class="so-card-body">
                        <div class="chart-container">
                            <canvas id="chartEvolucao"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico 2: Top Categorias (Doughnut) -->
            <div class="col-lg-4">
                <div class="so-card h-100 mb-0">
                    <div class="so-card-header">
                        <h5 class="so-card-title">
                            <i class="fas fa-chart-pie text-primary"></i> Faturamento por Categoria
                        </h5>
                    </div>
                    <div class="so-card-body">
                        <div class="chart-container">
                            <canvas id="chartCategorias"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ LINHA 2 DE GRÁFICOS & TABELA DE DESTAQUES ═════════════════════ -->
        <div class="row g-4">
            <!-- Gráfico 3: Top 5 Produtos Mais Vendidos -->
            <div class="col-lg-6">
                <div class="so-card h-100 mb-0">
                    <div class="so-card-header">
                        <h5 class="so-card-title">
                            <i class="fas fa-trophy text-primary"></i> Top 5 Produtos Mais Vendidos (Qtd)
                        </h5>
                    </div>
                    <div class="so-card-body">
                        <div class="chart-container">
                            <canvas id="chartTopProdutos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela Resumo do Período -->
            <div class="col-lg-6">
                <div class="so-card h-100 mb-0">
                    <div class="so-card-header">
                        <h5 class="so-card-title">
                            <i class="fas fa-list-check text-primary"></i> Destaques de Venda no Período
                        </h5>
                    </div>
                    <div class="so-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 so-table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Produto</th>
                                        <th scope="col" class="text-center" style="width: 28%;">Qtd Vendida</th>
                                        <th scope="col" class="text-end pe-3" style="width: 28%;">Total Faturado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($topProdutos)): ?>
                                        <?php foreach ($topProdutos as $tp): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($tp['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="text-center">
                                                <span class="tabular-nums fw-semibold text-dark"><?= (int)$tp['total_vendido'] ?></span>
                                                <span class="text-muted text-xs"><?= ((int)$tp['total_vendido'] === 1 ? 'unidade' : 'unidades') ?></span>
                                            </td>
                                            <td class="text-end pe-3 text-dark fw-bold tabular-nums">
                                                R$ <?= number_format((float)$tp['total_faturado'], 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                                Sem movimentações de venda no período selecionado.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
const labelsEvolucao  = <?= json_encode($labelsEvolucao, JSON_UNESCAPED_UNICODE) ?>;
const dataFaturamento = <?= json_encode($dataFaturamento) ?>;
const dataLucro       = <?= json_encode($dataLucro) ?>;

const labelsTopCat    = <?= json_encode(!empty($labelsTopCat) ? $labelsTopCat : ['Sem dados'], JSON_UNESCAPED_UNICODE) ?>;
const dataTopCat      = <?= json_encode(!empty($dataTopCat) ? $dataTopCat : [0]) ?>;

const labelsTopProd   = <?= json_encode(!empty($labelsTopProd) ? $labelsTopProd : ['Sem dados'], JSON_UNESCAPED_UNICODE) ?>;
const dataTopProd     = <?= json_encode(!empty($dataTopProd) ? $dataTopProd : [0]) ?>;

Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
Chart.defaults.color = "#64748b";
Chart.defaults.borderColor = "#f1f5f9";

// 1. Gráfico de Evolução Financeira
new Chart(document.getElementById('chartEvolucao'), {
    type: 'bar',
    data: {
        labels: labelsEvolucao,
        datasets: [
            {
                label: 'Faturamento (R$)',
                data: dataFaturamento,
                backgroundColor: 'rgba(40, 73, 54, 0.85)',
                borderColor: '#284936',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Lucro Bruto (R$)',
                data: dataLucro,
                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                borderColor: '#10b981',
                borderWidth: 1,
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 12,
                    font: { weight: '600', size: 12 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(c) {
                        return ' ' + c.dataset.label + ': R$ ' + c.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: {
                    callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); }
                }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// 2. Gráfico de Categorias (Doughnut)
const catColors = ['#284936', '#0284c7', '#f59e0b', '#10b981', '#6366f1', '#ec4899', '#8b5cf6'];
new Chart(document.getElementById('chartCategorias'), {
    type: 'doughnut',
    data: {
        labels: labelsTopCat,
        datasets: [{
            data: dataTopCat,
            backgroundColor: catColors.slice(0, Math.max(labelsTopCat.length, 1)),
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 12,
                    font: { size: 11 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(c) {
                        return ' ' + c.label + ': R$ ' + c.parsed.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        },
        cutout: '65%'
    }
});

// 3. Gráfico Top Produtos (Horizontal Bar)
new Chart(document.getElementById('chartTopProdutos'), {
    type: 'bar',
    data: {
        labels: labelsTopProd,
        datasets: [{
            label: 'Unidades Vendidas',
            data: dataTopProd,
            backgroundColor: 'rgba(40, 73, 54, 0.85)',
            borderColor: '#284936',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(c) {
                        var val = c.parsed.x;
                        return ' ' + val + (val === 1 ? ' unidade' : ' unidades');
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { precision: 0 }
            },
            y: {
                grid: { display: false }
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

