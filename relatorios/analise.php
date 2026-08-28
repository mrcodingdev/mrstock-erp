<?php
/**
 * MrStock ERP - Centro de Inteligência e Análise Gerencial
 * Painel com filtros temporais (7 dias, Mês Atual, Ano Atual), KPIs de margem e gráficos Chart.js
 */
$pageTitle  = 'MrStock ERP - Análise & Dashboards';
$activePage = 'analise';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// ── 1. Bloqueio de Acesso RBAC (Apenas Admin) ───────────────────────────────
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

// ── 2. Seletor de Período ───────────────────────────────────────────────────
$periodo = $_GET['periodo'] ?? '7dias';
if (!in_array($periodo, ['7dias', 'mes_atual', 'ano_atual'])) {
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
               SUM(vi.quantidade * vi.preco_unitario) AS total_faturamento,
               SUM(vi.quantidade * (vi.preco_unitario - p.preco_compra)) AS total_lucro
        FROM vendas v
        JOIN vendas_itens vi ON v.id = vi.venda_id
        JOIN produtos p ON p.id = vi.produto_id
        WHERE v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(v.data_venda)
    ");
    while ($row = $stmtEvolucao->fetch()) {
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
               SUM(vi.quantidade * vi.preco_unitario) AS total_faturamento,
               SUM(vi.quantidade * (vi.preco_unitario - p.preco_compra)) AS total_lucro
        FROM vendas v
        JOIN vendas_itens vi ON v.id = vi.venda_id
        JOIN produtos p ON p.id = vi.produto_id
        WHERE MONTH(v.data_venda) = MONTH(CURDATE()) AND YEAR(v.data_venda) = YEAR(CURDATE())
        GROUP BY DATE(v.data_venda)
    ");
    while ($row = $stmtEvolucao->fetch()) {
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
               SUM(vi.quantidade * vi.preco_unitario) AS total_faturamento,
               SUM(vi.quantidade * (vi.preco_unitario - p.preco_compra)) AS total_lucro
        FROM vendas v
        JOIN vendas_itens vi ON v.id = vi.venda_id
        JOIN produtos p ON p.id = vi.produto_id
        WHERE YEAR(v.data_venda) = YEAR(CURDATE())
        GROUP BY MONTH(v.data_venda)
    ");
    while ($row = $stmtEvolucao->fetch()) {
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
           COALESCE(SUM(vi.quantidade * (vi.preco_unitario - p.preco_compra)), 0) AS lucro_bruto
    FROM vendas v
    LEFT JOIN vendas_itens vi ON v.id = vi.venda_id
    LEFT JOIN produtos p ON p.id = vi.produto_id
    WHERE $dateCondition
");
$kpiPeriodo = $stmtKpiPeriodo->fetch();

$faturamentoPeriodo = (float)$kpiPeriodo['faturamento'];
$lucroPeriodo       = (float)$kpiPeriodo['lucro_bruto'];
$qtdVendasPeriodo   = (int)$kpiPeriodo['total_vendas'];
$ticketMedioPeriodo = $qtdVendasPeriodo > 0 ? ($faturamentoPeriodo / $qtdVendasPeriodo) : 0.0;
$margemPercentual   = $faturamentoPeriodo > 0 ? (($lucroPeriodo / $faturamentoPeriodo) * 100) : 0.0;

// ── 5. KPIs Patrimoniais de Estoque ──────────────────────────────────────────
$patrimonioEstoque = (float)$pdo->query("SELECT COALESCE(SUM(quantidade * preco_venda), 0) FROM produtos WHERE status = 'ativo'")->fetchColumn();
$custoEstoque      = (float)$pdo->query("SELECT COALESCE(SUM(quantidade * preco_compra), 0) FROM produtos WHERE status = 'ativo'")->fetchColumn();
$lucroEstoque      = $patrimonioEstoque - $custoEstoque;

// ── 6. Top 5 Produtos Mais Vendidos no Período ───────────────────────────────
$stmtTopProd = $pdo->query("
    SELECT p.nome, 
           SUM(vi.quantidade) AS total_vendido,
           SUM(vi.quantidade * vi.preco_unitario) AS total_faturado
    FROM vendas_itens vi 
    JOIN produtos p ON p.id = vi.produto_id 
    JOIN vendas v ON v.id = vi.venda_id
    WHERE $dateCondition
    GROUP BY p.id 
    ORDER BY total_vendido DESC 
    LIMIT 5
");
$topProdutos = $stmtTopProd->fetchAll();
$labelsTopProd = [];
$dataTopProd   = [];
foreach ($topProdutos as $tp) {
    $labelsTopProd[] = mb_strimwidth($tp['nome'], 0, 20, '...');
    $dataTopProd[]   = (int)$tp['total_vendido'];
}

// ── 7. Top Categorias Mais Vendidas no Período ───────────────────────────────
$stmtTopCat = $pdo->query("
    SELECT COALESCE(p.categoria, 'Geral') AS categoria_nome,
           SUM(vi.quantidade) AS total_itens,
           SUM(vi.quantidade * vi.preco_unitario) AS total_faturado
    FROM vendas_itens vi
    JOIN produtos p ON p.id = vi.produto_id
    JOIN vendas v ON v.id = vi.venda_id
    WHERE $dateCondition
    GROUP BY p.categoria
    ORDER BY total_faturado DESC
    LIMIT 5
");
$topCategorias = $stmtTopCat->fetchAll();
$labelsTopCat = [];
$dataTopCat   = [];
foreach ($topCategorias as $tc) {
    $labelsTopCat[] = $tc['categoria_nome'];
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
            <!-- Seletor de Período -->
            <div class="btn-group shadow-sm" role="group" aria-label="Seletor de Período">
                <a href="<?= BASE_URL ?>/relatorios/analise.php?periodo=7dias" 
                   class="btn btn-sm <?= $periodo === '7dias' ? 'btn-primary active fw-bold' : 'btn-secondary bg-white' ?>">
                    <i class="fas fa-calendar-day me-1"></i> 7 Dias
                </a>
                <a href="<?= BASE_URL ?>/relatorios/analise.php?periodo=mes_atual" 
                   class="btn btn-sm <?= $periodo === 'mes_atual' ? 'btn-primary active fw-bold' : 'btn-secondary bg-white' ?>">
                    <i class="fas fa-calendar-alt me-1"></i> Mês Atual
                </a>
                <a href="<?= BASE_URL ?>/relatorios/analise.php?periodo=ano_atual" 
                   class="btn btn-sm <?= $periodo === 'ano_atual' ? 'btn-primary active fw-bold' : 'btn-secondary bg-white' ?>">
                    <i class="fas fa-calendar me-1"></i> 12 Meses (Ano)
                </a>
            </div>
            <button class="btn btn-sm btn-dark bg-white shadow-sm fw-bold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="content-body">
        <!-- ══ CARDS DE KPIS DO PERÍODO SELECIONADO ══════════════════════════ -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Faturamento (<?= $periodoNome ?>)</small>
                            <h3 class="fw-bold text-dark m-0">R$ <?= number_format($faturamentoPeriodo, 2, ',', '.') ?></h3>
                            <small class="text-primary fw-bold"><?= $qtdVendasPeriodo ?> venda(s) registrada(s)</small>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-primary">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Lucro Bruto Estimado</small>
                            <h3 class="fw-bold text-success m-0">R$ <?= number_format($lucroPeriodo, 2, ',', '.') ?></h3>
                            <small class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i>Margem: <?= number_format($margemPercentual, 1, ',', '.') ?>%</small>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-success">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Ticket Médio</small>
                            <h3 class="fw-bold text-info m-0">R$ <?= number_format($ticketMedioPeriodo, 2, ',', '.') ?></h3>
                            <small class="text-muted">Média por pedido</small>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-info">
                            <i class="fas fa-shopping-bag fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Patrimônio em Estoque</small>
                            <h3 class="fw-bold text-warning m-0">R$ <?= number_format($patrimonioEstoque, 2, ',', '.') ?></h3>
                            <small class="text-muted">Lucro projetado: R$ <?= number_format($lucroEstoque, 2, ',', '.') ?></small>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-warning">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ GRÁFICOS INTERATIVOS CHART.JS ═════════════════════════════════ -->
        <div class="row g-4 mb-4">
            <!-- Gráfico 1: Evolução Financeira (Faturamento e Lucro) -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-chart-area text-primary me-2"></i>Evolução Financeira: Faturamento vs. Lucro (<?= $periodoNome ?>)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartEvolucao"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico 2: Top Categorias (Doughnut) -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 bg-white">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-pie-chart text-info me-2"></i>Faturamento por Categoria
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartCategorias"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Gráfico 3: Top 5 Produtos Mais Vendidos -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 bg-white h-100">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-trophy text-warning me-2"></i>Top 5 Produtos Mais Vendidos (Qtd)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTopProdutos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela Resumo do Período -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 bg-white h-100">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <h6 class="fw-bold text-dark m-0">
                            <i class="fas fa-list-check text-success me-2"></i>Destaques de Venda no Período
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped so-table align-middle mb-0" style="font-size:13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th class="text-center">Qtd Vendida</th>
                                        <th class="text-end pe-3">Faturado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($topProdutos)): ?>
                                        <?php foreach ($topProdutos as $tp): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($tp['nome']) ?></td>
                                            <td class="text-center"><span class="badge bg-primary rounded-pill"><?= (int)$tp['total_vendido'] ?> un</span></td>
                                            <td class="text-end pe-3 fw-bold text-success">R$ <?= number_format((float)$tp['total_faturado'], 2, ',', '.') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">Sem movimentações de venda no período.</td></tr>
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
const labelsEvolucao  = <?= json_encode($labelsEvolucao) ?>;
const dataFaturamento = <?= json_encode($dataFaturamento) ?>;
const dataLucro       = <?= json_encode($dataLucro) ?>;

const labelsTopCat    = <?= json_encode(!empty($labelsTopCat) ? $labelsTopCat : ['Sem dados']) ?>;
const dataTopCat      = <?= json_encode(!empty($dataTopCat) ? $dataTopCat : [0]) ?>;

const labelsTopProd   = <?= json_encode(!empty($labelsTopProd) ? $labelsTopProd : ['Sem dados']) ?>;
const dataTopProd     = <?= json_encode(!empty($dataTopProd) ? $dataTopProd : [0]) ?>;

Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.color = "#6c757d";

// 1. Gráfico de Evolução Financeira
new Chart(document.getElementById('chartEvolucao'), {
    type: 'bar',
    data: {
        labels: labelsEvolucao,
        datasets: [
            {
                label: 'Faturamento (R$)',
                data: dataFaturamento,
                backgroundColor: 'rgba(13, 110, 253, 0.75)',
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 4
            },
            {
                label: 'Lucro Bruto (R$)',
                data: dataLucro,
                backgroundColor: 'rgba(25, 135, 84, 0.75)',
                borderColor: '#198754',
                borderWidth: 1,
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: function(c) {
                        return c.dataset.label + ': R$ ' + c.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(v) { return 'R$ ' + v; }
                }
            }
        }
    }
});

// 2. Gráfico de Categorias (Doughnut)
new Chart(document.getElementById('chartCategorias'), {
    type: 'doughnut',
    data: {
        labels: labelsTopCat,
        datasets: [{
            data: dataTopCat,
            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6f42c1', '#fd7e14'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12 } },
            tooltip: {
                callbacks: {
                    label: function(c) {
                        return c.label + ': R$ ' + c.parsed.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        cutout: '60%'
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
            backgroundColor: 'rgba(255, 193, 7, 0.85)',
            borderColor: '#ffc107',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
