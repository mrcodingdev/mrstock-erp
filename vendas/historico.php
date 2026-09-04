<?php
/**
 * MrStock ERP - Histórico de Vendas com Filtros Avançados, KPIs e Design System SalesOps
 */
$pageTitle  = 'Histórico de Vendas';
$activePage = 'historico';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Apenas Admin
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

// ── 1. Filtros Recebidos via GET ─────────────────────────────────────────────
$data_inicio     = trim($_GET['data_inicio'] ?? '');
$data_fim        = trim($_GET['data_fim'] ?? '');
$cliente_id      = filter_var($_GET['cliente_id'] ?? '', FILTER_VALIDATE_INT);
$forma_pagamento = trim($_GET['forma_pagamento'] ?? '');
$busca           = trim($_GET['busca'] ?? '');
$hasActiveFilters = !empty($busca) || !empty($data_inicio) || !empty($data_fim) || !empty($cliente_id) || !empty($forma_pagamento);

// ── 2. Lista de Clientes para o Select ───────────────────────────────────────
$stmtClientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
$clientesLista = $stmtClientes->fetchAll();

// ── 3. Construção Dinâmica da Query SQL com PDO ──────────────────────────────
$where = ["1=1"];
$params = [];

if (!empty($data_inicio)) {
    $where[] = "DATE(v.data_venda) >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
}
if (!empty($data_fim)) {
    $where[] = "DATE(v.data_venda) <= :data_fim";
    $params[':data_fim'] = $data_fim;
}
if ($cliente_id) {
    $where[] = "v.cliente_id = :cliente_id";
    $params[':cliente_id'] = $cliente_id;
}
if (!empty($forma_pagamento)) {
    $where[] = "v.forma_pagamento = :forma_pagamento";
    $params[':forma_pagamento'] = $forma_pagamento;
}
if (!empty($busca)) {
    $where[] = "(c.nome LIKE :busca OR v.id = :busca_id)";
    $params[':busca'] = "%$busca%";
    $params[':busca_id'] = is_numeric($busca) ? (int)$busca : 0;
}

$whereSql = implode(' AND ', $where);

// ── 4. KPIs das Vendas Filtradas (Totalizador Global dos Filtros) ─────────────
$sqlKpi = "
    SELECT v.id, v.total, COALESCE(SUM(vi.quantidade), 0) AS qtd_itens
    FROM vendas v
    LEFT JOIN clientes c ON v.cliente_id = c.id
    LEFT JOIN vendas_itens vi ON v.id = vi.venda_id
    WHERE $whereSql
    GROUP BY v.id
";
$stmtKpi = $pdo->prepare($sqlKpi);
$stmtKpi->execute($params);
$vendasKpi = $stmtKpi->fetchAll();

$totalVendasQtd     = count($vendasKpi);
$faturamentoTotal   = 0.0;
$totalItensVendidos = 0;

foreach ($vendasKpi as $vk) {
    $faturamentoTotal   += (float)$vk['total'];
    $totalItensVendidos += (int)$vk['qtd_itens'];
}

$ticketMedio = $totalVendasQtd > 0 ? ($faturamentoTotal / $totalVendasQtd) : 0.0;

// ── 5. Paginação Centralizada ───────────────────────────────────────────────
$limit = 10;
$page  = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($page - 1) * $limit;
$totalPages = ceil($totalVendasQtd / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

$sql = "
    SELECT v.id, v.data_venda, v.total, v.forma_pagamento, v.cliente_id,
           c.nome AS cliente_nome, 
           COUNT(vi.id) AS qtd_itens,
           cf.chave_acesso
    FROM vendas v 
    LEFT JOIN clientes c ON v.cliente_id = c.id 
    LEFT JOIN vendas_itens vi ON v.id = vi.venda_id 
    LEFT JOIN cupons_fiscais cf ON cf.venda_id = v.id
    WHERE $whereSql
    GROUP BY v.id 
    ORDER BY v.data_venda DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$vendas = $stmt->fetchAll();

/**
 * Renderiza forma de pagamento em texto limpo com ícone contextual colorido
 */
function render_forma_pagamento(string $forma): string {
    $formaUpper = mb_strtoupper(trim($forma), 'UTF-8');
    if (strpos($formaUpper, 'DINHEIRO') !== false) {
        return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-money-bill-wave text-success"></i><span>Dinheiro</span></span>';
    } elseif (strpos($formaUpper, 'PIX') !== false) {
        return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-bolt text-warning"></i><span>PIX</span></span>';
    } elseif (strpos($formaUpper, 'CRÉDITO') !== false || strpos($formaUpper, 'CREDITO') !== false) {
        return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-credit-card text-primary"></i><span>Cartão de Crédito</span></span>';
    } elseif (strpos($formaUpper, 'DÉBITO') !== false || strpos($formaUpper, 'DEBITO') !== false) {
        return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-credit-card text-info"></i><span>Cartão de Débito</span></span>';
    }
    return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-wallet text-secondary"></i><span>' . htmlspecialchars($forma, ENT_QUOTES, 'UTF-8') . '</span></span>';
}

function render_forma_pagamento_badge(string $forma): string {
    return render_forma_pagamento($forma);
}

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-clock-rotate-left text-primary me-2"></i>Histórico de Vendas</h2>
        <p class="text-muted m-0">Consulte, filtre e audite todas as vendas realizadas pelo PDV.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-cash-register me-1"></i> Abrir PDV
        </a>
    </div>
</div>

<div class="content-body">
    <?php if (isset($_GET['erro'])): ?>
        <?php if ($_GET['erro'] === 'cupom_invalido'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border border-warning mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Aviso:</strong> Identificador de venda não especificado para emissão do cupom fiscal.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'venda_nao_encontrada'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i>
            <strong>Erro:</strong> Venda não localizada no banco de dados.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ CARDS DE KPI (BENTO GRID MODERNO - SEM 4PX BORDER) ═══════════════ -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Vendas Filtradas</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalVendasQtd, 0, ',', '.') ?></h3>
                        <small class="text-muted"><span class="tabular-nums"><?= $totalItensVendidos ?></span> <?= ($totalItensVendidos === 1) ? 'item no total' : 'itens no total' ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Faturamento Filtrado</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($faturamentoTotal, 2, ',', '.') ?></h3>
                        <small class="text-muted">Total líquido realizado</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-12 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Ticket Médio</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($ticketMedio, 2, ',', '.') ?></h3>
                        <small class="text-muted">Média por cupom fiscal</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ BARRA DE FILTROS UNIFICADA (BENTO GRID COM LABELS ACESSÍVEIS) ═════ -->
    <div class="so-card p-3 mb-3">
        <form method="GET" action="<?= BASE_URL ?>/vendas/historico.php" class="row g-2 align-items-end">
            
            <!-- 1. Busca por Texto (Venda, NFC-e ou Cliente) -->
            <div class="col-12 col-md-4 col-lg-3">
                <label for="filtro_busca" class="form-label fw-bold text-dark text-xs mb-1">Buscar Venda / Cliente</label>
                <div class="position-relative">
                    <input type="text" id="filtro_busca" name="busca" class="form-control ps-4 shadow-none" placeholder="Nº da venda ou cliente..." value="<?= htmlspecialchars($busca) ?>" aria-label="Buscar Nº da venda, NFC-e ou cliente">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
                </div>
            </div>

            <!-- 2. Cliente -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label for="filtro_cliente_id" class="form-label fw-bold text-dark text-xs mb-1">Cliente</label>
                <select id="filtro_cliente_id" name="cliente_id" class="form-select shadow-none" aria-label="Filtrar por Cliente">
                    <option value="">-- Todos os Clientes --</option>
                    <?php foreach ($clientesLista as $cli): ?>
                        <option value="<?= $cli['id'] ?>" <?= ($cliente_id == $cli['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cli['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 3. Data Inicial -->
            <div class="col-6 col-sm-3 col-md-2 col-lg-2">
                <label for="filtro_data_inicio" class="form-label fw-bold text-dark text-xs mb-1">Data Inicial</label>
                <input type="date" id="filtro_data_inicio" name="data_inicio" class="form-control tabular-nums shadow-none px-2" value="<?= htmlspecialchars($data_inicio) ?>" aria-label="Data Inicial">
            </div>

            <!-- 4. Data Final -->
            <div class="col-6 col-sm-3 col-md-2 col-lg-2">
                <label for="filtro_data_fim" class="form-label fw-bold text-dark text-xs mb-1">Data Final</label>
                <input type="date" id="filtro_data_fim" name="data_fim" class="form-control tabular-nums shadow-none px-2" value="<?= htmlspecialchars($data_fim) ?>" aria-label="Data Final">
            </div>

            <!-- 5. Forma de Pagamento -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-1">
                <label for="filtro_forma_pagamento" class="form-label fw-bold text-dark text-xs mb-1">Pagamento</label>
                <select id="filtro_forma_pagamento" name="forma_pagamento" class="form-select shadow-none" aria-label="Filtrar por Forma de Pagamento">
                    <option value="">-- Todas --</option>
                    <option value="DINHEIRO" <?= ($forma_pagamento === 'DINHEIRO') ? 'selected' : '' ?>>Dinheiro</option>
                    <option value="PIX" <?= ($forma_pagamento === 'PIX') ? 'selected' : '' ?>>PIX</option>
                    <option value="CARTÃO DE CRÉDITO" <?= ($forma_pagamento === 'CARTÃO DE CRÉDITO') ? 'selected' : '' ?>>Cartão Crédito</option>
                    <option value="CARTÃO DE DÉBITO" <?= ($forma_pagamento === 'CARTÃO DE DÉBITO') ? 'selected' : '' ?>>Cartão Débito</option>
                </select>
            </div>

            <!-- 6. Botões de Ação -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex gap-2 justify-content-lg-end">
                <button type="submit" class="btn btn-primary fw-bold flex-fill shadow-sm" title="Aplicar Filtros" aria-label="Aplicar Filtros">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if ($hasActiveFilters): ?>
                <a href="<?= BASE_URL ?>/vendas/historico.php" class="btn btn-secondary px-3 shadow-sm" title="Limpar Filtros" aria-label="Limpar Filtros">
                    <i class="fas fa-undo"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ══ TABELA MODULAR DE HISTÓRICO DE VENDAS ═════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <h5 class="so-card-title m-0"><i class="fas fa-receipt text-primary"></i> Vendas Registradas</h5>
            <span class="text-muted small tabular-nums fw-semibold">
                <?= $totalVendasQtd ?> <?= ($totalVendasQtd === 1) ? 'venda registrada' : 'vendas registradas' ?>
            </span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaVendas">
                    <thead>
                        <tr>
                            <th width="10%">Nº Venda</th>
                            <th width="16%">Data / Hora</th>
                            <th width="28%">Cliente</th>
                            <th width="12%" class="text-center">Qtd. Itens</th>
                            <th width="16%">Pagamento</th>
                            <th width="12%" class="text-end pe-3">Total (R$)</th>
                            <th width="6%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($vendas) > 0): ?>
                            <?php foreach ($vendas as $v): ?>
                            <tr class="linha-venda">
                                <td class="fw-bold text-dark font-monospace tabular-nums">
                                    #<?= str_pad((string)$v['id'], 6, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td class="tabular-nums">
                                    <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($v['data_venda'])) ?></div>
                                    <div class="text-muted small"><?= date('H:i', strtotime($v['data_venda'])) ?></div>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($v['cliente_nome'] ?? 'Consumidor Final', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($v['chave_acesso'])): ?>
                                        <span class="badge bg-light text-secondary border font-monospace text-2xs tabular-nums mt-1 d-inline-flex align-items-center gap-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Chave NFC-e: <?= htmlspecialchars($v['chave_acesso'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas fa-qrcode text-muted"></i> NFC-e <?= substr($v['chave_acesso'], 0, 8) ?>...<?= substr($v['chave_acesso'], -4) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center tabular-nums text-secondary">
                                    <?= ((int)$v['qtd_itens'] === 1) ? '1 item' : (int)$v['qtd_itens'] . ' itens' ?>
                                </td>
                                <td>
                                    <?= render_forma_pagamento($v['forma_pagamento'] ?? '') ?>
                                </td>
                                <td class="text-end pe-3 fw-bold text-dark tabular-nums">
                                    R$ <?= number_format((float)$v['total'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ações da venda #<?= $v['id'] ?>" title="Ações da venda #<?= $v['id'] ?>">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border py-2">
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/vendas/cupom.php?venda_id=<?= $v['id'] ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-print text-primary me-2"></i> Imprimir Cupom 80mm
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/vendas/nfce.php">
                                                    <i class="fas fa-receipt text-success me-2"></i> Painel Fiscal NFC-e
                                                </a>
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
                                        <i class="fas fa-receipt fs-1 d-block mb-3 text-muted opacity-50"></i>
                                        <h6 class="fw-bold text-dark mb-1">Nenhuma venda localizada</h6>
                                        <p class="text-muted small mb-0">Não encontramos registros correspondentes aos filtros selecionados.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ PAGINAÇÃO MODERNA INSTITUCIONAL ═════════════════════════ -->
        <?php
        $firstItem = $totalVendasQtd > 0 ? ($offset + 1) : 0;
        $lastItem  = min($offset + $limit, $totalVendasQtd);
        ?>
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Exibindo <strong class="tabular-nums text-dark"><?= $firstItem ?></strong> a <strong class="tabular-nums text-dark"><?= $lastItem ?></strong> de <strong class="tabular-nums text-dark"><?= $totalVendasQtd ?></strong> <?= ($totalVendasQtd === 1) ? 'venda' : 'vendas' ?>
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação da listagem">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <?php
                                $queryParams = $_GET;
                                $queryParams['pagina'] = $page - 1;
                            ?>
                            <a class="page-link" href="historico.php?<?= http_build_query($queryParams) ?>" aria-label="Anterior">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        
                        <?php
                        $range = 2;
                        $startPage = max(1, $page - $range);
                        $endPage = min($totalPages, $page + $range);
                        
                        if ($startPage > 1) {
                            $queryParams = $_GET;
                            $queryParams['pagina'] = 1;
                            echo '<li class="page-item"><a class="page-link tabular-nums" href="historico.php?' . http_build_query($queryParams) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                            $queryParams = $_GET;
                            $queryParams['pagina'] = $i;
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link tabular-nums" href="historico.php?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            $queryParams = $_GET;
                            $queryParams['pagina'] = $totalPages;
                            echo '<li class="page-item"><a class="page-link tabular-nums" href="historico.php?' . http_build_query($queryParams) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                           <?php
                               $queryParams = $_GET;
                               $queryParams['pagina'] = $page + 1;
                           ?>
                            <a class="page-link" href="historico.php?<?= http_build_query($queryParams) ?>" aria-label="Próximo">
                                Próximo <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicialização de tooltips Bootstrap 5 para chaves fiscais
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
