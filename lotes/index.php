<?php
/**
 * MrStock ERP - Gestão de Lotes, Validades e Rastreabilidade de Compras
 * Design System SalesOps v0 (Bento Grid, PEPS / FIFO e Badges de Shelf-Life)
 */

$pageTitle  = 'Lotes & Validades';
$activePage = 'lotes';

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Exclusivo para Administradores
require_admin();

// ── 1. Ingestão de Parâmetros de Filtro e Busca via GET ───────────────────────
$busca           = trim($_GET['busca'] ?? '');
$status_validade = trim($_GET['status_validade'] ?? '');
$fornecedor_id   = filter_var($_GET['fornecedor_id'] ?? '', FILTER_VALIDATE_INT);
if ($fornecedor_id !== false && $fornecedor_id <= 0) {
    $fornecedor_id = null;
}
$ordem = trim($_GET['ordem'] ?? 'validade_asc');

$hasActiveFilters = !empty($busca) || !empty($status_validade) || !empty($fornecedor_id) || ($ordem !== 'validade_asc');

// ── 2. Listas de Apoio para Selects e Modais ──────────────────────────────────
$fornecedores = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$produtos     = $pdo->query("SELECT id, nome, codigo_de_barra, preco_compra FROM produtos WHERE status = 'ativo' ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$produtosJson = json_encode($produtos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

// ── 3. KPIs Estratégicos Bento Grid (Totais Globais) ──────────────────────────
$sqlKpis = "
    SELECT 
        COUNT(CASE WHEN l.quantidade > 0 THEN 1 END) AS total_ativos,
        COUNT(CASE WHEN l.quantidade > 0 AND l.data_validade < CURDATE() THEN 1 END) AS total_vencidos,
        COUNT(CASE WHEN l.quantidade > 0 AND l.data_validade >= CURDATE() AND l.data_validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) AS total_vencendo,
        COALESCE(SUM(CASE WHEN l.quantidade > 0 THEN l.quantidade * l.preco_compra ELSE 0 END), 0) AS capital_imobilizado
    FROM lotes l
    INNER JOIN produtos p ON l.produto_id = p.id
";
$stmtKpis = $pdo->query($sqlKpis);
$kpis = $stmtKpis ? $stmtKpis->fetch(PDO::FETCH_ASSOC) : [];

$kpiTotalAtivos   = (int)($kpis['total_ativos'] ?? 0);
$kpiTotalVencidos = (int)($kpis['total_vencidos'] ?? 0);
$kpiTotalVencendo = (int)($kpis['total_vencendo'] ?? 0);
$kpiCapitalImob   = (float)($kpis['capital_imobilizado'] ?? 0.0);

// ── 4. Construção Dinâmica da Query Filtrada ──────────────────────────────────
$where = ["1=1"];
$params = [];

if (!empty($busca)) {
    $where[] = "(l.numero_lote LIKE :busca OR p.nome LIKE :busca OR p.codigo_de_barra LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

if ($status_validade === 'vencido') {
    $where[] = "l.data_validade < CURDATE() AND l.quantidade > 0";
} elseif ($status_validade === 'alerta') {
    $where[] = "l.data_validade >= CURDATE() AND l.data_validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND l.quantidade > 0";
} elseif ($status_validade === 'valido') {
    $where[] = "l.data_validade > DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND l.quantidade > 0";
} elseif ($status_validade === 'esgotado') {
    $where[] = "l.quantidade <= 0";
}

if (!empty($fornecedor_id)) {
    $where[] = "l.fornecedor_id = :fornecedor_id";
    $params[':fornecedor_id'] = $fornecedor_id;
}

$whereSql = implode(' AND ', $where);

// Total de Linhas Filtradas para Paginação
$sqlCount = "
    SELECT COUNT(l.id) 
    FROM lotes l 
    INNER JOIN produtos p ON l.produto_id = p.id 
    LEFT JOIN fornecedores f ON l.fornecedor_id = f.id 
    WHERE $whereSql
";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalLotesFiltrados = (int)$stmtCount->fetchColumn();

// ── 5. Paginação Centralizada ─────────────────────────────────────────────────
$limit = 12;
$page  = max(1, (int)($_GET['pagina'] ?? 1));
$totalPages = $limit > 0 ? (int)ceil($totalLotesFiltrados / $limit) : 1;
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $limit;

$queryParamsBase = $_GET;
unset($queryParamsBase['pagina']);

// Ordenação com suporte nativo a PEPS (Primeiro que Expira, Primeiro que Sai)
$orderBy = match ($ordem) {
    'validade_desc'   => "l.data_validade DESC, l.id DESC",
    'quantidade_desc' => "l.quantidade DESC, l.id DESC",
    'lote_asc'        => "l.numero_lote ASC",
    'recente_desc'    => "l.id DESC",
    default           => "(CASE WHEN l.quantidade > 0 THEN 0 ELSE 1 END), l.data_validade ASC, l.id ASC", // PEPS / FIFO
};

// ── 6. Query Principal de Lotes ───────────────────────────────────────────────
$sqlLotes = "
    SELECT 
        l.*,
        p.nome AS produto_nome,
        p.codigo_de_barra AS produto_codigo_barras,
        p.preco_venda AS produto_preco_venda,
        f.nome AS fornecedor_nome,
        DATEDIFF(l.data_validade, CURDATE()) AS dias_para_vencer
    FROM lotes l
    INNER JOIN produtos p ON l.produto_id = p.id
    LEFT JOIN fornecedores f ON l.fornecedor_id = f.id
    WHERE $whereSql
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
";
$stmtLotes = $pdo->prepare($sqlLotes);
foreach ($params as $k => $v) {
    $stmtLotes->bindValue($k, $v);
}
$stmtLotes->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtLotes->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtLotes->execute();
$lotes = $stmtLotes->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0">
            <i class="fa-solid fa-calendar-days text-primary me-2"></i>Lotes e Validades
        </h2>
        <p class="text-muted m-0">Rastreabilidade de compras, gestão de shelf-life e controle PEPS (FIFO).</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoLote" onclick="limparModalNovoLote()">
            <i class="fa-solid fa-plus-circle me-1"></i> Novo Lote
        </button>
    </div>
</div>

<div class="content-body">

    <!-- ══ CARDS DE RESUMO (BENTO GRID SALESOPS DE ELITE) ════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Lotes Ativos -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Lotes Ativos (Saldo > 0)</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $kpiTotalAtivos ?></h3>
                        <small class="text-muted"><?= $kpiTotalAtivos === 1 ? '1 lote em estoque' : "$kpiTotalAtivos lotes em estoque" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Vencendo em 30 Dias -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Vencendo em 30 Dias</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $kpiTotalVencendo ?></h3>
                        <small class="text-warning fw-semibold"><i class="fa-solid fa-clock me-1"></i>Atenção ao Shelf-Life</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--warning">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Lotes Vencidos -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Lotes Vencidos</span>
                        <h3 class="fw-bold text-danger m-0 tabular-nums"><?= $kpiTotalVencidos ?></h3>
                        <small class="text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Bloqueados para venda</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--danger">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Capital Imobilizado em Lotes -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Capital Imobilizado</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($kpiCapitalImob, 2, ',', '.') ?></h3>
                        <small class="text-success fw-semibold"><i class="fa-solid fa-coins me-1"></i>Custo total em estoque</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ FILTROS AVANÇADOS COM DESIGN SYSTEM SALESOPS ══════════════════════ -->
    <div class="so-card mb-4">
        <div class="so-card-body p-3">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <!-- Busca textual -->
                <div class="col-12 col-md-4">
                    <label for="filtroBusca" class="form-label fw-bold text-xs text-dark mb-1">Buscar Lote / Produto / Código</label>
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-sm ps-4" id="filtroBusca" name="busca" value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: L2026-001, Papel A4, 789...">
                        <i class="fa-solid fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 0.8rem;"></i>
                    </div>
                </div>

                <!-- Status de Validade -->
                <div class="col-6 col-md-3">
                    <label for="filtroStatus" class="form-label fw-bold text-xs text-dark mb-1">Status de Shelf-Life</label>
                    <select class="form-select form-select-sm" id="filtroStatus" name="status_validade">
                        <option value="">Todos os Lotes</option>
                        <option value="vencido" <?= $status_validade === 'vencido' ? 'selected' : '' ?>>Vencidos (Crítico)</option>
                        <option value="alerta" <?= $status_validade === 'alerta' ? 'selected' : '' ?>>Vencendo em 30 Dias</option>
                        <option value="valido" <?= $status_validade === 'valido' ? 'selected' : '' ?>>Válidos (> 30 Dias)</option>
                        <option value="esgotado" <?= $status_validade === 'esgotado' ? 'selected' : '' ?>>Esgotados (Saldo 0)</option>
                    </select>
                </div>

                <!-- Fornecedor -->
                <div class="col-6 col-md-2">
                    <label for="filtroFornecedor" class="form-label fw-bold text-xs text-dark mb-1">Fornecedor</label>
                    <select class="form-select form-select-sm" id="filtroFornecedor" name="fornecedor_id">
                        <option value="">Todos</option>
                        <?php foreach ($fornecedores as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($fornecedor_id === (int)$f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ordenação PEPS / Outros -->
                <div class="col-6 col-md-2">
                    <label for="filtroOrdem" class="form-label fw-bold text-xs text-dark mb-1">Ordenação</label>
                    <select class="form-select form-select-sm" id="filtroOrdem" name="ordem">
                        <option value="validade_asc" <?= $ordem === 'validade_asc' ? 'selected' : '' ?>>PEPS (Validade mais próxima)</option>
                        <option value="validade_desc" <?= $ordem === 'validade_desc' ? 'selected' : '' ?>>Validade mais distante</option>
                        <option value="quantidade_desc" <?= $ordem === 'quantidade_desc' ? 'selected' : '' ?>>Maior saldo</option>
                        <option value="lote_asc" <?= $ordem === 'lote_asc' ? 'selected' : '' ?>>Número do Lote (A-Z)</option>
                        <option value="recente_desc" <?= $ordem === 'recente_desc' ? 'selected' : '' ?>>Mais recentes</option>
                    </select>
                </div>

                <!-- Ações de Filtro -->
                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Aplicar Filtros">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    <?php if ($hasActiveFilters): ?>
                    <a href="index.php" class="btn btn-secondary btn-sm" title="Limpar Filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ TABELA DE LOTES E VALIDADES (ANTI-SLOP) ═══════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header">
            <h5 class="so-card-title">
                <i class="fa-solid fa-list-check text-primary"></i> Lotes Cadastrados
            </h5>
            <span class="so-badge so-badge-primary tabular-nums">
                <?= $totalLotesFiltrados === 1 ? '1 lote localizado' : "$totalLotesFiltrados lotes localizados" ?>
            </span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle">
                    <thead>
                        <tr>
                            <th scope="col" width="16%">Lote / Entrada</th>
                            <th scope="col" width="24%">Produto / SKU</th>
                            <th scope="col" width="16%">Fornecedor</th>
                            <th scope="col" width="14%">Validade</th>
                            <th scope="col" width="14%" class="text-center">Shelf-Life</th>
                            <th scope="col" width="8%" class="text-center">Saldo</th>
                            <th scope="col" width="8%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($lotes) > 0): ?>
                            <?php foreach ($lotes as $l):
                                $qtd = (int)$l['quantidade'];
                                $dias = (int)$l['dias_para_vencer'];

                                if ($qtd <= 0) {
                                    $badge = [
                                        'classe' => 'so-badge-secondary',
                                        'texto'  => 'Esgotado',
                                        'icone'  => 'fa-box-open',
                                        'aria'   => 'Lote Esgotado',
                                    ];
                                } elseif ($dias < 0) {
                                    $badge = [
                                        'classe' => 'so-badge-danger',
                                        'texto'  => 'Vencido (' . abs($dias) . ($dias === -1 ? ' dia atrás)' : ' dias atrás)'),
                                        'icone'  => 'fa-circle-xmark',
                                        'aria'   => 'Lote vencido há ' . abs($dias) . ($dias === -1 ? ' dia' : ' dias'),
                                    ];
                                } elseif ($dias === 0) {
                                    $badge = [
                                        'classe' => 'so-badge-danger',
                                        'texto'  => 'Vence hoje',
                                        'icone'  => 'fa-triangle-exclamation',
                                        'aria'   => 'Lote vence hoje',
                                    ];
                                } elseif ($dias <= 30) {
                                    $badge = [
                                        'classe' => 'so-badge-warning',
                                        'texto'  => "Vence em {$dias}d",
                                        'icone'  => 'fa-clock',
                                        'aria'   => 'Lote vence em ' . $dias . ($dias === 1 ? ' dia' : ' dias'),
                                    ];
                                } else {
                                    $badge = [
                                        'classe' => 'so-badge-success',
                                        'texto'  => "Válido ({$dias}d)",
                                        'icone'  => 'fa-circle-check',
                                        'aria'   => 'Lote válido por mais ' . $dias . ($dias === 1 ? ' dia' : ' dias'),
                                    ];
                                }

                                $dataEntradaFmt = !empty($l['data_entrada']) ? date('d/m/Y H:i', strtotime($l['data_entrada'])) : '—';
                                $dataValidadeFmt = !empty($l['data_validade']) ? date('d/m/Y', strtotime($l['data_validade'])) : '—';
                                $dataFabFmt = !empty($l['data_fabricacao']) ? date('d/m/Y', strtotime($l['data_fabricacao'])) : '—';
                            ?>
                            <tr>
                                <td>
                                    <strong class="font-monospace text-dark tabular-nums d-block"><?= htmlspecialchars($l['numero_lote'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small class="text-muted tabular-nums d-block mt-1"><?= $dataEntradaFmt ?></small>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($l['produto_nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small class="text-muted font-monospace tabular-nums">
                                        <?= !empty($l['produto_codigo_barras']) ? ('EAN: ' . htmlspecialchars($l['produto_codigo_barras'], ENT_QUOTES, 'UTF-8')) : 'ID Prod: #' . $l['produto_id'] ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="text-dark"><?= htmlspecialchars($l['fornecedor_nome'] ?: 'Não vinculado', ENT_QUOTES, 'UTF-8') ?></span>
                                    <small class="text-muted d-block tabular-nums">Fab: <?= $dataFabFmt ?></small>
                                </td>
                                <td class="tabular-nums">
                                    <div class="fw-semibold text-dark"><?= $dataValidadeFmt ?></div>
                                    <small class="text-muted">Custo: R$ <?= number_format((float)$l['preco_compra'], 2, ',', '.') ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="so-badge <?= $badge['classe'] ?>" aria-label="<?= htmlspecialchars($badge['aria'] ?? $badge['texto'], ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid <?= $badge['icone'] ?> me-1"></i><?= $badge['texto'] ?></span>
                                </td>
                                <td class="text-center tabular-nums">
                                    <span class="fw-bold text-dark tabular-nums"><?= (int)$l['quantidade'] ?></span> <small class="text-muted d-block"><?= ((int)$l['quantidade'] === 1 ? 'unidade' : 'unidades') ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ações para o lote <?= htmlspecialchars($l['numero_lote'], ENT_QUOTES, 'UTF-8') ?>" title="Ações">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border py-2" style="border-color: #cbd5e1 !important; font-size:0.85rem;">
                                            <li>
                                                <button type="button" class="dropdown-item py-1" onclick='abrirModalEditar(<?= json_encode([
                                                    'id'              => (int)$l['id'],
                                                    'produto_id'      => (int)$l['produto_id'],
                                                    'produto_nome'    => $l['produto_nome'],
                                                    'numero_lote'     => $l['numero_lote'],
                                                    'data_fabricacao' => $l['data_fabricacao'],
                                                    'data_validade'   => $l['data_validade'],
                                                    'quantidade'      => (int)$l['quantidade'],
                                                    'preco_compra'    => (float)$l['preco_compra'],
                                                    'fornecedor_id'   => $l['fornecedor_id'] ? (int)$l['fornecedor_id'] : ''
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Editar Lote
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider my-1" style="border-color: #cbd5e1;"></li>
                                            <li>
                                                <form action="<?= BASE_URL ?>/lotes/functions.php?tipo=lote" method="POST" onsubmit="return confirmarExclusao(<?= (int)$l['id'] ?>, '<?= htmlspecialchars($l['numero_lote'], ENT_QUOTES, 'UTF-8') ?>', <?= $qtd ?>)" class="m-0">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="acao" value="deletar">
                                                    <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger py-1">
                                                        <i class="fa-solid fa-trash-can me-2"></i> Excluir Lote
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="p-0 border-0">
                                    <div class="text-center py-5">
                                        <i class="fa-solid fa-boxes-packing fs-1 d-block mb-3 text-muted opacity-50"></i>
                                        <h6 class="fw-bold text-dark mb-1">Nenhum lote localizado</h6>
                                        <p class="text-muted small mb-0">Não encontramos registros correspondentes aos filtros selecionados.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ PAGINAÇÃO INSTITUCIONAL ═══════════════════════════════════════════ -->
        <?php
        $firstItem = $totalLotesFiltrados > 0 ? ($offset + 1) : 0;
        $lastItem  = min($offset + $limit, $totalLotesFiltrados);
        ?>
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Exibindo <strong class="tabular-nums text-dark"><?= $firstItem ?></strong> a <strong class="tabular-nums text-dark"><?= $lastItem ?></strong> de <strong class="tabular-nums text-dark"><?= $totalLotesFiltrados ?></strong> <?= ($totalLotesFiltrados === 1) ? 'lote' : 'lotes' ?>
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação da listagem de lotes">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?<?= http_build_query($queryParamsBase + ['pagina' => $page - 1]) ?>" aria-label="Anterior">
                                <i class="fa-solid fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        
                        <?php
                        $range = 2;
                        $startPage = max(1, $page - $range);
                        $endPage = min($totalPages, $page + $range);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link tabular-nums" href="index.php?' . http_build_query($queryParamsBase + ['pagina' => 1]) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link tabular-nums" href="index.php?<?= http_build_query($queryParamsBase + ['pagina' => $i]) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link tabular-nums" href="index.php?' . http_build_query($queryParamsBase + ['pagina' => $totalPages]) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?<?= http_build_query($queryParamsBase + ['pagina' => $page + 1]) ?>" aria-label="Próximo">
                                Próximo <i class="fa-solid fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL NOVO LOTE ═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalNovoLote" tabindex="-1" aria-labelledby="modalNovoLoteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border" style="border-radius: 8px !important; border-color: #cbd5e1 !important;">
            <div class="modal-header bg-white border-bottom py-3" style="border-color: #cbd5e1 !important;">
                <h5 class="modal-title fw-bold text-dark" id="modalNovoLoteLabel">
                    <i class="fa-solid fa-circle-plus text-primary me-2"></i> Cadastrar Novo Lote de Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form action="<?= BASE_URL ?>/lotes/functions.php?tipo=lote" method="POST">
                <?= csrf_input() ?>
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" value="">
                <div class="modal-body bg-light p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label for="novo_produto_id" class="form-label fw-bold text-dark">Produto <span class="text-danger">*</span></label>
                            <select class="form-select" id="novo_produto_id" name="produto_id" required onchange="aoSelecionarProdutoNovoLote(this)">
                                <option value="">Selecione o produto no catálogo...</option>
                                <?php foreach ($produtos as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-preco="<?= (float)$p['preco_compra'] ?>">
                                        <?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?> (ID #<?= $p['id'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="novo_numero_lote" class="form-label fw-bold text-dark">Nº do Lote <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" id="novo_numero_lote" name="numero_lote" required placeholder="Ex: L20260901-01">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="novo_fornecedor_id" class="form-label fw-bold text-dark">Fornecedor (Origem)</label>
                            <select class="form-select" id="novo_fornecedor_id" name="fornecedor_id">
                                <option value="">Não informado / Compra Avulsa</option>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="novo_data_fabricacao" class="form-label fw-bold text-dark">Fabricação</label>
                            <input type="date" class="form-control" id="novo_data_fabricacao" name="data_fabricacao" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="novo_data_validade" class="form-label fw-bold text-dark">Validade <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="novo_data_validade" name="data_validade" required value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                        </div>

                        <div class="col-6 col-md-6">
                            <label for="novo_quantidade" class="form-label fw-bold text-dark">Quantidade Inicial (Saldo) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" class="form-control font-monospace tabular-nums" id="novo_quantidade" name="quantidade" value="1" required>
                                <span class="input-group-text bg-white text-muted">unidades</span>
                            </div>
                            <small class="text-muted">O estoque geral do produto será incrementado automaticamente.</small>
                        </div>

                        <div class="col-6 col-md-6">
                            <label for="novo_preco_compra" class="form-label fw-bold text-dark">Custo Unitário (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted">R$</span>
                                <input type="number" step="0.01" min="0" class="form-control font-monospace tabular-nums" id="novo_preco_compra" name="preco_compra" value="0.00">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top p-3" style="border-color: #cbd5e1 !important;">
                    <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Salvar Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ MODAL EDITAR LOTE ═════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditarLote" tabindex="-1" aria-labelledby="modalEditarLoteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border" style="border-radius: 8px !important; border-color: #cbd5e1 !important;">
            <div class="modal-header bg-white border-bottom py-3" style="border-color: #cbd5e1 !important;">
                <h5 class="modal-title fw-bold text-dark" id="modalEditarLoteLabel">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Editar Lote & Saldo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form action="<?= BASE_URL ?>/lotes/functions.php?tipo=lote" method="POST">
                <?= csrf_input() ?>
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" id="edit_id" value="">
                <input type="hidden" name="produto_id" id="edit_produto_id" value="">
                
                <div class="modal-body bg-light p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label for="edit_produto_nome" class="form-label fw-bold text-dark">Produto Vinculado</label>
                            <input type="text" class="form-control bg-white" id="edit_produto_nome" readonly style="cursor: not-allowed;">
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="edit_numero_lote" class="form-label fw-bold text-dark">Nº do Lote <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" id="edit_numero_lote" name="numero_lote" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="edit_fornecedor_id" class="form-label fw-bold text-dark">Fornecedor</label>
                            <select class="form-select" id="edit_fornecedor_id" name="fornecedor_id">
                                <option value="">Não informado / Compra Avulsa</option>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="edit_data_fabricacao" class="form-label fw-bold text-dark">Fabricação</label>
                            <input type="date" class="form-control" id="edit_data_fabricacao" name="data_fabricacao">
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="edit_data_validade" class="form-label fw-bold text-dark">Validade <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_data_validade" name="data_validade" required>
                        </div>

                        <div class="col-6 col-md-6">
                            <label for="edit_quantidade" class="form-label fw-bold text-dark">Saldo do Lote <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="1" min="0" class="form-control font-monospace tabular-nums" id="edit_quantidade" name="quantidade" required>
                                <span class="input-group-text bg-white text-muted">unidades</span>
                            </div>
                            <small class="text-muted">A diferença (delta) será aplicada automaticamente ao estoque do produto.</small>
                        </div>

                        <div class="col-6 col-md-6">
                            <label for="edit_preco_compra" class="form-label fw-bold text-dark">Custo Unitário (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted">R$</span>
                                <input type="number" step="0.01" min="0" class="form-control font-monospace tabular-nums" id="edit_preco_compra" name="preco_compra">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top p-3" style="border-color: #cbd5e1 !important;">
                    <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Atualizar Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const produtosCat = <?= $produtosJson ?>;

function aoSelecionarProdutoNovoLote(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    const preco = opt ? opt.getAttribute('data-preco') : '0.00';
    document.getElementById('novo_preco_compra').value = parseFloat(preco || 0).toFixed(2);
    
    // Sugestão de número de lote automático
    if (selectEl.value && !document.getElementById('novo_numero_lote').value) {
        const d = new Date();
        const ymd = d.getFullYear() + String(d.getMonth() + 1).padStart(2, '0') + String(d.getDate()).padStart(2, '0');
        document.getElementById('novo_numero_lote').value = 'L' + ymd + '-' + selectEl.value;
    }
}

function limparModalNovoLote() {
    document.getElementById('novo_produto_id').value = '';
    document.getElementById('novo_numero_lote').value = '';
    document.getElementById('novo_fornecedor_id').value = '';
    document.getElementById('novo_quantidade').value = '1';
    document.getElementById('novo_preco_compra').value = '0.00';
}

function abrirModalEditar(dados) {
    document.getElementById('edit_id').value = dados.id;
    document.getElementById('edit_produto_id').value = dados.produto_id;
    document.getElementById('edit_produto_nome').value = dados.produto_nome + ' (ID #' + dados.produto_id + ')';
    document.getElementById('edit_numero_lote').value = dados.numero_lote;
    document.getElementById('edit_data_fabricacao').value = dados.data_fabricacao || '';
    document.getElementById('edit_data_validade').value = dados.data_validade || '';
    document.getElementById('edit_quantidade').value = dados.quantidade;
    document.getElementById('edit_preco_compra').value = parseFloat(dados.preco_compra || 0).toFixed(2);
    document.getElementById('edit_fornecedor_id').value = dados.fornecedor_id || '';

    const modalEl = document.getElementById('modalEditarLote');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function confirmarExclusao(id, numeroLote, qtd) {
    const qtdTxt = qtd === 1 ? '1 unidade' : (qtd + ' unidades');
    return confirm('Tem certeza que deseja excluir este lote?\n\nATENÇÃO: O saldo de ' + qtdTxt + ' deste lote será DEBITADO do estoque geral do produto no catálogo.');
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
