<?php
/**
 * MrStock ERP - Gestão de Ordens de Compra
 * Design System SalesOps v0 (14 Zonas Anti-Slop)
 * Consome contrato de backend com filtros avançados, KPIs e paginação
 */
$pageTitle  = 'Ordens de Compra';
$activePage = 'compras';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Apenas Admin
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

// ── 1. Ingestão de Parâmetros de Filtro via GET ─────────────────────────────
$busca          = trim($_GET['busca'] ?? '');
$fornecedor_id  = filter_var($_GET['fornecedor_id'] ?? '', FILTER_VALIDATE_INT);
if ($fornecedor_id !== false && $fornecedor_id <= 0) {
    $fornecedor_id = null;
}
$status         = trim($_GET['status'] ?? '');
$data_inicio    = trim($_GET['data_inicio'] ?? '');
$data_fim       = trim($_GET['data_fim'] ?? '');

$hasActiveFilters = !empty($busca) || !empty($fornecedor_id) || !empty($status) || !empty($data_inicio) || !empty($data_fim);

// ── 2. Lista de Fornecedores para o Select ──────────────────────────────────
$stmtForn = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome ASC");
$fornecedoresLista = $stmtForn ? $stmtForn->fetchAll(PDO::FETCH_ASSOC) : [];

// ── 3. Construção Dinâmica da Query SQL com PDO ──────────────────────────────
$where = ["1=1"];
$params = [];

if (!empty($busca)) {
    $where[] = "(c.numero_nota LIKE :busca OR f.nome LIKE :busca OR CAST(c.id AS CHAR) LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}
if (!empty($fornecedor_id)) {
    $where[] = "c.fornecedor_id = :fornecedor_id";
    $params[':fornecedor_id'] = $fornecedor_id;
}
if (!empty($status) && in_array($status, ['PAGA', 'PENDENTE'])) {
    $where[] = "c.status = :status";
    $params[':status'] = $status;
}
if (!empty($data_inicio)) {
    $where[] = "DATE(c.data_compra) >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
}
if (!empty($data_fim)) {
    $where[] = "DATE(c.data_compra) <= :data_fim";
    $params[':data_fim'] = $data_fim;
}

$whereSql = implode(' AND ', $where);

// ── 4. KPIs das Ordens de Compra Filtradas ──────────────────────────────────
$sqlKpi = "
    SELECT 
        COUNT(c.id) AS total_compras,
        COALESCE(SUM(c.valor_total), 0) AS total_valor,
        COALESCE(SUM(CASE WHEN c.status = 'PAGA' THEN c.valor_total ELSE 0 END), 0) AS total_pagas,
        COALESCE(SUM(CASE WHEN c.status = 'PENDENTE' THEN c.valor_total ELSE 0 END), 0) AS total_pendentes
    FROM compras c
    LEFT JOIN fornecedores f ON c.fornecedor_id = f.id
    WHERE $whereSql
";
$stmtKpi = $pdo->prepare($sqlKpi);
$stmtKpi->execute($params);
$kpiData = $stmtKpi->fetch(PDO::FETCH_ASSOC);

$totalCompras          = (int)($kpiData['total_compras'] ?? 0);
$totalValorCompras     = (float)($kpiData['total_valor'] ?? 0.0);
$totalComprasPagas     = (float)($kpiData['total_pagas'] ?? 0.0);
$totalComprasPendentes = (float)($kpiData['total_pendentes'] ?? 0.0);

// ── 5. Paginação Centralizada ───────────────────────────────────────────────
$limit = 10;
$page  = max(1, (int)($_GET['pagina'] ?? 1));
$totalRows = $totalCompras;
$totalPages = $limit > 0 ? (int)ceil($totalRows / $limit) : 1;
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $limit;

// Parâmetros Base para Paginação (preservando filtros ativos)
$queryParamsBase = $_GET;
unset($queryParamsBase['pagina']);

// ── 6. Query Principal de Ordens de Compra ──────────────────────────────────
$sql = "
    SELECT c.*, f.nome AS fornecedor_nome, u.username 
    FROM compras c 
    LEFT JOIN fornecedores f ON c.fornecedor_id = f.id 
    LEFT JOIN usuarios u ON c.usuario_id = u.id 
    WHERE $whereSql 
    ORDER BY c.data_compra DESC 
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Ordens de Compra</h2>
        <p class="text-muted m-0">Consulte o histórico de abastecimento e gerencie contas a pagar com fornecedores.</p>
    </div>
    <a href="<?= BASE_URL ?>/compras/nova.php" class="btn btn-primary fw-bold shadow-sm">
        <i class="fas fa-cart-plus me-1"></i> Registrar Nova Compra
    </a>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border border-success mb-3" role="alert">
            <i class="fas fa-circle-check me-2"></i>
            <strong>Sucesso!</strong> Compra registrada e estoque atualizado com sucesso.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'erro'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i>
            <strong>Erro!</strong> Ocorreu um problema ao processar a operação de compra.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'status_atualizado'): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm border border-info mb-3" role="alert">
            <i class="fas fa-circle-info me-2"></i>
            <strong>Atualizado!</strong> O status de pagamento foi alterado com sucesso.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'compra_nao_encontrada'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border border-warning mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Aviso:</strong> Ordem de compra não localizada no banco de dados.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ 4 STAT CARDS NO TOPO (BENTO GRID SALESOPS) ════════════════════════ -->
    <div class="row g-3 mb-3">
        <!-- Card 1: Total de Compras -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Total de Compras</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalCompras, 0, ',', '.') ?></h3>
                        <small class="text-muted"><?= $totalCompras === 1 ? '1 ordem de compra' : "$totalCompras ordens registradas" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Volume de Compras -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Volume de Compras</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($totalValorCompras, 2, ',', '.') ?></h3>
                        <small class="text-muted">Valor total acumulado</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--info">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Compras Pagas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Compras Pagas</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($totalComprasPagas, 2, ',', '.') ?></h3>
                        <small class="text-muted">Liquidadas com fornecedor</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Compras Pendentes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Compras Pendentes</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($totalComprasPendentes, 2, ',', '.') ?></h3>
                        <small class="text-muted">Contas a liquidar</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ BARRA DE FILTROS ACESSÍVEL (WCAG 2.1 AA) ══════════════════════════ -->
    <div class="so-card p-3 mb-3">
        <form method="GET" action="<?= BASE_URL ?>/compras/index.php" class="row g-2 align-items-end">
            
            <!-- 1. Busca por Texto (Compra, Fornecedor ou Nota) -->
            <div class="col-12 col-md-4 col-lg-3">
                <label for="filtro_busca" class="form-label fw-bold text-dark text-xs mb-1">Buscar Compra / Nota</label>
                <div class="position-relative">
                    <input type="text" id="filtro_busca" name="busca" class="form-control ps-4 shadow-none" placeholder="Nº da compra, fornecedor ou nota..." value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>" aria-label="Buscar Compra / Nota">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
                </div>
            </div>

            <!-- 2. Fornecedor -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <label for="filtro_fornecedor_id" class="form-label fw-bold text-dark text-xs mb-1">Fornecedor</label>
                <select id="filtro_fornecedor_id" name="fornecedor_id" class="form-select shadow-none" aria-label="Filtrar por Fornecedor">
                    <option value="">-- Todos os Fornecedores --</option>
                    <?php foreach ($fornecedoresLista as $forn): ?>
                        <option value="<?= $forn['id'] ?>" <?= ($fornecedor_id == $forn['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($forn['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 3. Status -->
            <div class="col-6 col-sm-3 col-md-2 col-lg-2">
                <label for="filtro_status" class="form-label fw-bold text-dark text-xs mb-1">Status</label>
                <select id="filtro_status" name="status" class="form-select shadow-none" aria-label="Filtrar por Status">
                    <option value="">-- Todos --</option>
                    <option value="PAGA" <?= ($status === 'PAGA') ? 'selected' : '' ?>>Paga</option>
                    <option value="PENDENTE" <?= ($status === 'PENDENTE') ? 'selected' : '' ?>>Pendente</option>
                </select>
            </div>

            <!-- 4. Data Inicial -->
            <div class="col-6 col-sm-3 col-md-2 col-lg-1">
                <label for="filtro_data_inicio" class="form-label fw-bold text-dark text-xs mb-1">Data Inicial</label>
                <input type="date" id="filtro_data_inicio" name="data_inicio" class="form-control tabular-nums shadow-none px-2" value="<?= htmlspecialchars($data_inicio, ENT_QUOTES, 'UTF-8') ?>" aria-label="Data Inicial">
            </div>

            <!-- 5. Data Final -->
            <div class="col-6 col-sm-3 col-md-2 col-lg-1">
                <label for="filtro_data_fim" class="form-label fw-bold text-dark text-xs mb-1">Data Final</label>
                <input type="date" id="filtro_data_fim" name="data_fim" class="form-control tabular-nums shadow-none px-2" value="<?= htmlspecialchars($data_fim, ENT_QUOTES, 'UTF-8') ?>" aria-label="Data Final">
            </div>

            <!-- 6. Botões de Ação -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex gap-2 justify-content-lg-end">
                <button type="submit" class="btn btn-primary fw-bold flex-fill shadow-sm" title="Aplicar Filtros" aria-label="Aplicar Filtros">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if ($hasActiveFilters): ?>
                <a href="<?= BASE_URL ?>/compras/index.php" class="btn btn-secondary px-3 shadow-sm" title="Limpar Filtros" aria-label="Limpar Filtros">
                    <i class="fas fa-undo me-1"></i> Limpar
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ══ TABELA MODULAR ANTI-SLOP DE COMPRAS ═══════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <h5 class="so-card-title m-0"><i class="fas fa-receipt text-primary me-2"></i>Ordens de Compra</h5>
            <span class="text-muted small tabular-nums fw-semibold">
                <?= $totalRows === 1 ? '1 compra encontrada' : number_format($totalRows, 0, ',', '.') . ' compras encontradas' ?>
            </span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaCompras">
                    <thead>
                        <tr>
                            <th scope="col" width="12%">Nº Compra</th>
                            <th scope="col" width="18%">Data / Operador</th>
                            <th scope="col" width="26%">Fornecedor</th>
                            <th scope="col" width="14%">Nota Fiscal</th>
                            <th scope="col" width="14%" class="text-end pe-3">Valor Total</th>
                            <th scope="col" width="10%" class="text-center">Status</th>
                            <th scope="col" width="6%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($compras) > 0): ?>
                            <?php foreach ($compras as $c): ?>
                            <tr class="linha-compra">
                                <td class="fw-bold text-dark font-monospace tabular-nums">
                                    #<?= str_pad((string)$c['id'], 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td class="tabular-nums">
                                    <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($c['data_compra'])) ?></div>
                                    <div class="text-muted small"><?= date('H:i', strtotime($c['data_compra'])) ?> &bull; <?= htmlspecialchars($c['username'] ?? 'Sistema', ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($c['fornecedor_nome'] ?: 'Desconhecido', ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace tabular-nums"><?= htmlspecialchars($c['numero_nota'] ?: 'S/N', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="text-end pe-3 fw-bold text-dark tabular-nums">
                                    R$ <?= number_format((float)$c['valor_total'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($c['status'] === 'PAGA'): ?>
                                        <span class="so-badge so-badge-success"><i class="fas fa-circle-check me-1"></i>PAGA</span>
                                    <?php else: ?>
                                        <span class="so-badge so-badge-warning"><i class="fas fa-clock me-1"></i>PENDENTE</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ações da compra #<?= $c['id'] ?>" title="Ações">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border py-2" style="font-size:0.85rem; border-color:#cbd5e1;">
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/compras/visualizar.php?id=<?= $c['id'] ?>">
                                                    <i class="fas fa-eye text-primary me-2"></i> Ver Detalhes
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="<?= BASE_URL ?>/compras/functions.php?tipo=status" method="POST" class="m-0">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="novo_status" value="<?= $c['status'] === 'PAGA' ? 'PENDENTE' : 'PAGA' ?>">
                                                    <?php if ($c['status'] === 'PENDENTE'): ?>
                                                        <button type="submit" class="dropdown-item text-success py-1" onclick="return confirm('Confirmar pagamento desta compra?');">
                                                            <i class="fas fa-circle-check me-2"></i> Marcar como Paga
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="dropdown-item text-warning py-1" onclick="return confirm('Reverter para pendente?');">
                                                            <i class="fas fa-undo me-2"></i> Reverter p/ Pendente
                                                        </button>
                                                    <?php endif; ?>
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
                                        <i class="fas fa-file-invoice-dollar fs-1 d-block mb-3 text-muted opacity-50"></i>
                                        <h6 class="fw-bold text-dark mb-1">Nenhuma compra localizada</h6>
                                        <p class="text-muted small mb-0">Não encontramos registros correspondentes aos filtros selecionados.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ PAGINAÇÃO MODERNA INSTITUCIONAL ═════════════════════════════════ -->
        <?php
        $firstItem = $totalRows > 0 ? ($offset + 1) : 0;
        $lastItem  = min($offset + $limit, $totalRows);
        ?>
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Exibindo <strong class="tabular-nums text-dark"><?= $firstItem ?></strong> a <strong class="tabular-nums text-dark"><?= $lastItem ?></strong> de <strong class="tabular-nums text-dark"><?= $totalRows ?></strong> <?= ($totalRows === 1) ? 'compra' : 'compras' ?>
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação da listagem de compras">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?<?= http_build_query($queryParamsBase + ['pagina' => $page - 1]) ?>" aria-label="Anterior">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
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

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
