<?php
/**
 * MrStock ERP - Rastreabilidade de Movimentações de Estoque
 * Design System SalesOps v0, KPIs Bento Grid e Acessibilidade WCAG 2.1 AA
 */
$pageTitle  = 'Movimentações de Estoque';
$activePage = 'movimentacoes';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// ── 1. Controle de Acesso (RBAC) ────────────────────────────────────────────
require_admin();

// ── 2. Ingestão de Filtros via GET ──────────────────────────────────────────
$buscaFiltro      = trim($_GET['busca'] ?? '');
$tipoFiltro       = trim($_GET['tipo'] ?? '');
$dataInicioFiltro = trim($_GET['data_inicio'] ?? '');
$dataFimFiltro    = trim($_GET['data_fim'] ?? '');
$produtoIdFiltro  = filter_var($_GET['produto_id'] ?? '', FILTER_VALIDATE_INT);
if ($produtoIdFiltro !== false && $produtoIdFiltro <= 0) $produtoIdFiltro = null;

$hasActiveFilters = !empty($buscaFiltro) || !empty($tipoFiltro) || !empty($dataInicioFiltro) || !empty($dataFimFiltro) || !empty($produtoIdFiltro);

// ── 3. Construção Dinâmica da Query SQL com PDO ─────────────────────────────
$where = ["1=1"];
$params = [];

if (!empty($buscaFiltro)) {
    $where[] = "(p.nome LIKE :busca OR p.codigo_de_barra LIKE :busca OR m.observacao LIKE :busca OR CAST(m.id AS CHAR) LIKE :busca)";
    $params[':busca'] = "%{$buscaFiltro}%";
}

if (!empty($tipoFiltro)) {
    if ($tipoFiltro === 'entradas') {
        $where[] = "m.tipo IN ('entrada_compra', 'devolucao_cliente')";
    } elseif ($tipoFiltro === 'saidas') {
        $where[] = "m.tipo IN ('saida_venda', 'devolucao_fornecedor')";
    } elseif ($tipoFiltro === 'perdas') {
        $where[] = "m.tipo = 'perda'";
    } else {
        $where[] = "m.tipo = :tipo";
        $params[':tipo'] = $tipoFiltro;
    }
}

if (!empty($dataInicioFiltro)) {
    $where[] = "DATE(m.data_movimento) >= :data_inicio";
    $params[':data_inicio'] = $dataInicioFiltro;
}

if (!empty($dataFimFiltro)) {
    $where[] = "DATE(m.data_movimento) <= :data_fim";
    $params[':data_fim'] = $dataFimFiltro;
}

if (!empty($produtoIdFiltro)) {
    $where[] = "m.produto_id = :produto_id";
    $params[':produto_id'] = $produtoIdFiltro;
}

$whereSql = implode(' AND ', $where);

// ── 4. KPIs Agregados das Movimentações Filtradas (Bento Grid) ──────────────
$sqlKpis = "
    SELECT 
        COUNT(*) as total_movimentacoes,
        COALESCE(SUM(CASE WHEN m.tipo IN ('entrada_compra', 'devolucao_cliente') THEN m.quantidade ELSE 0 END), 0) as total_entradas,
        COALESCE(SUM(CASE WHEN m.tipo IN ('saida_venda', 'devolucao_fornecedor') THEN m.quantidade ELSE 0 END), 0) as total_saidas,
        COALESCE(SUM(CASE WHEN m.tipo = 'perda' THEN m.quantidade ELSE 0 END), 0) as total_perdas
    FROM movimentacoes m
    JOIN produtos p ON m.produto_id = p.id
    WHERE $whereSql
";
$stmtKpi = $pdo->prepare($sqlKpis);
$stmtKpi->execute($params);
$kpiData = $stmtKpi->fetch(PDO::FETCH_ASSOC);

$totalMovimentacoes = (int)($kpiData['total_movimentacoes'] ?? 0);
$totalEntradasQtd   = (int)($kpiData['total_entradas'] ?? 0);
$totalSaidasQtd     = (int)($kpiData['total_saidas'] ?? 0);
$totalPerdasQtd     = (int)($kpiData['total_perdas'] ?? 0);

// ── 5. Paginação Parametrizada e Base de Query ─────────────────────────────
$limit = 15;
$page  = max(1, (int)($_GET['pagina'] ?? 1));
$totalRows = $totalMovimentacoes;
$totalPages = $limit > 0 ? (int)ceil($totalRows / $limit) : 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
$offset = ($page - 1) * $limit;

$queryParamsBase = $_GET;
unset($queryParamsBase['pagina']);

// ── 6. Query Principal de Movimentações Paginada ───────────────────────────
$sqlMov = "
    SELECT m.*, p.nome as produto_nome, p.codigo_de_barra, p.quantidade as produto_saldo_atual
    FROM movimentacoes m 
    JOIN produtos p ON m.produto_id = p.id 
    WHERE $whereSql 
    ORDER BY m.data_movimento DESC, m.id DESC 
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sqlMov);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── 7. Lista Auxiliar de Produtos para Filtros e Modal ─────────────────────
$stmtProd = $pdo->query("SELECT id, nome, quantidade, codigo_de_barra FROM produtos WHERE status = 'ativo' ORDER BY nome ASC");
$produtosLista = $stmtProd ? $stmtProd->fetchAll(PDO::FETCH_ASSOC) : [];

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-arrow-right-arrow-left text-primary me-2"></i>Movimentações de Estoque</h2>
        <p class="text-muted m-0">Rastreabilidade completa de Entradas, Saídas, Ajustes, Devoluções e Perdas.</p>
    </div>
    <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMovimentacao">
        <i class="fas fa-plus-circle me-1"></i> Nova Movimentação
    </button>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border border-success mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>Sucesso!</strong> Movimentação registrada e saldo de estoque atualizado. 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'erro_dados'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i> <strong>Erro!</strong> Dados inválidos. Verifique os campos informados no formulário. 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'erro_saldo_insuficiente'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Saldo Insuficiente!</strong> A quantidade solicitada para saída é maior que o saldo disponível em estoque (Disponível: <strong class="tabular-nums"><?= (int)($_GET['disponivel'] ?? 0) ?> un</strong>).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'erro_banco'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i> <strong>Erro no Banco de Dados!</strong> Não foi possível processar a movimentação. Tente novamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ 4 STAT CARDS NO TOPO (BENTO GRID SALESOPS) ═══════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total de Movimentações -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Total de Movimentações</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalMovimentacoes, 0, ',', '.') ?></h3>
                        <small class="text-muted"><?= $totalMovimentacoes === 1 ? '1 registro' : "$totalMovimentacoes registros" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Entradas Registradas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Entradas Registradas</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalEntradasQtd, 0, ',', '.') ?></h3>
                        <small class="text-muted"><?= $totalEntradasQtd === 1 ? '1 unidade somada' : "$totalEntradasQtd unidades somadas" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Saídas Registradas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Saídas Registradas</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalSaidasQtd, 0, ',', '.') ?></h3>
                        <small class="text-muted"><?= $totalSaidasQtd === 1 ? '1 unidade baixada' : "$totalSaidasQtd unidades baixadas" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 4: Perdas & Avarias -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Perdas &amp; Avarias</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalPerdasQtd, 0, ',', '.') ?></h3>
                        <small class="text-muted"><?= $totalPerdasQtd === 1 ? '1 item descartado' : "$totalPerdasQtd itens descartados" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--danger">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ BARRA DE FILTROS ACESSÍVEL (WCAG 2.1 AA) ═════════════════════════ -->
    <div class="so-card p-3 mb-4">
        <form method="GET" action="<?= BASE_URL ?>/produtos/movimentacoes.php" class="row g-2 align-items-end">
            
            <!-- 1. Buscar Produto / Cód / Motivo -->
            <div class="col-12 col-md-6 col-xl-3">
                <label for="filtro_busca" class="form-label fw-bold text-dark text-xs mb-1">Buscar Produto / Cód / Motivo</label>
                <div class="position-relative">
                    <input type="text" 
                           id="filtro_busca" 
                           name="busca" 
                           class="form-control ps-4 shadow-none" 
                           placeholder="Nome, cód de barras, motivo..." 
                           value="<?= htmlspecialchars($buscaFiltro) ?>" 
                           aria-label="Buscar Produto / Cód / Motivo">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
                </div>
            </div>

            <!-- 2. Tipo de Movimento -->
            <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                <label for="filtro_tipo" class="form-label fw-bold text-dark text-xs mb-1">Tipo de Movimento</label>
                <select id="filtro_tipo" name="tipo" class="form-select shadow-none" aria-label="Tipo de Movimento">
                    <option value="">-- Todos os Tipos --</option>
                    <optgroup label="Entradas (Soma no Estoque)">
                        <option value="entrada_compra" <?= ($tipoFiltro === 'entrada_compra') ? 'selected' : '' ?>>Entrada (Compra)</option>
                        <option value="devolucao_cliente" <?= ($tipoFiltro === 'devolucao_cliente') ? 'selected' : '' ?>>Devolução Cliente</option>
                    </optgroup>
                    <optgroup label="Saídas (Subtrai do Estoque)">
                        <option value="saida_venda" <?= ($tipoFiltro === 'saida_venda') ? 'selected' : '' ?>>Saída (Venda / Ajuste)</option>
                        <option value="devolucao_fornecedor" <?= ($tipoFiltro === 'devolucao_fornecedor') ? 'selected' : '' ?>>Devolução Fornecedor</option>
                        <option value="perda" <?= ($tipoFiltro === 'perda') ? 'selected' : '' ?>>Perda / Avaria</option>
                    </optgroup>
                </select>
            </div>

            <!-- 3. Produto Específico -->
            <div class="col-12 col-sm-6 col-md-3 col-xl-3">
                <label for="filtro_produto_id" class="form-label fw-bold text-dark text-xs mb-1">Produto Específico</label>
                <select id="filtro_produto_id" name="produto_id" class="form-select shadow-none" aria-label="Produto Específico">
                    <option value="">-- Todos os Produtos --</option>
                    <?php foreach ($produtosLista as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($produtoIdFiltro === (int)$p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 4. Data Inicial -->
            <div class="col-6 col-sm-3 col-md-3 col-xl-1">
                <label for="filtro_data_inicio" class="form-label fw-bold text-dark text-xs mb-1">Data Inicial</label>
                <input type="date" 
                       id="filtro_data_inicio" 
                       name="data_inicio" 
                       class="form-control tabular-nums shadow-none px-2" 
                       value="<?= htmlspecialchars($dataInicioFiltro) ?>" 
                       aria-label="Data Inicial">
            </div>

            <!-- 5. Data Final -->
            <div class="col-6 col-sm-3 col-md-3 col-xl-1">
                <label for="filtro_data_fim" class="form-label fw-bold text-dark text-xs mb-1">Data Final</label>
                <input type="date" 
                       id="filtro_data_fim" 
                       name="data_fim" 
                       class="form-control tabular-nums shadow-none px-2" 
                       value="<?= htmlspecialchars($dataFimFiltro) ?>" 
                       aria-label="Data Final">
            </div>

            <!-- 6. Botões de Ação -->
            <div class="col-12 col-sm-6 col-md-6 col-xl-2 d-flex gap-2 justify-content-xl-end">
                <button type="submit" class="btn btn-primary fw-bold flex-fill shadow-sm" title="Filtrar movimentações" aria-label="Filtrar movimentações">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if ($hasActiveFilters): ?>
                <a href="<?= BASE_URL ?>/produtos/movimentacoes.php" class="btn btn-secondary px-3 shadow-sm" title="Limpar Filtros" aria-label="Limpar Filtros">
                    <i class="fas fa-undo"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ══ TABELA ANTI-SLOP DE MOVIMENTAÇÕES ═════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <h5 class="so-card-title m-0">
                <i class="fas fa-clock-rotate-left text-primary me-2"></i>Registro Cronológico de Movimentações
            </h5>
            <span class="so-badge so-badge-primary tabular-nums">
                <?= $totalRows === 1 ? '1 registro' : number_format($totalRows, 0, ',', '.') . ' registros' ?>
            </span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaMovimentacoes">
                    <thead>
                        <tr>
                            <th scope="col" width="14%">Data / Hora</th>
                            <th scope="col" width="18%">Tipo de Movimento</th>
                            <th scope="col" width="28%">Produto</th>
                            <th scope="col" width="10%" class="text-center">Qtd</th>
                            <th scope="col" width="10%" class="text-center">Saldo Atual</th>
                            <th scope="col" width="20%">Observação / Justificativa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($movimentacoes) > 0): ?>
                            <?php foreach ($movimentacoes as $m):
                                $tipoMap = [
                                    'entrada_compra'       => ['so-badge-success', 'fa-arrow-down',   'Entrada (Compra)'],
                                    'devolucao_cliente'    => ['so-badge-info',    'fa-rotate-left',  'Devolução Cliente'],
                                    'saida_venda'          => ['so-badge-primary', 'fa-arrow-up',     'Saída (Venda)'],
                                    'devolucao_fornecedor' => ['so-badge-warning', 'fa-reply',        'Devolução Fornec.'],
                                    'perda'                => ['so-badge-danger',  'fa-circle-xmark', 'Perda / Avaria'],
                                ];
                                [$badgeCls, $icone, $label] = $tipoMap[$m['tipo']] ?? ['so-badge-primary', 'fa-question', 'Desconhecido'];
                                $isEntrada = in_array($m['tipo'], ['entrada_compra', 'devolucao_cliente']);
                            ?>
                            <tr class="linha-movimentacao">
                                <td class="tabular-nums">
                                    <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($m['data_movimento'])) ?></div>
                                    <div class="text-muted small"><?= date('H:i', strtotime($m['data_movimento'])) ?></div>
                                </td>
                                <td>
                                    <span class="so-badge <?= htmlspecialchars($badgeCls) ?>">
                                        <i class="fas <?= htmlspecialchars($icone) ?>"></i> <?= htmlspecialchars($label) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($m['produto_nome']) ?></strong>
                                    <?php if (!empty($m['codigo_de_barra'])): ?>
                                        <code class="text-muted font-monospace tabular-nums text-xs d-inline-flex align-items-center gap-1 mt-1">
                                            <i class="fas fa-barcode"></i> <?= htmlspecialchars($m['codigo_de_barra']) ?>
                                        </code>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isEntrada): ?>
                                        <span class="text-success fw-bold tabular-nums">+<?= (int)$m['quantidade'] ?></span>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold tabular-nums">-<?= (int)$m['quantidade'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border tabular-nums fw-semibold px-2 py-1">
                                        <?= (int)($m['produto_saldo_atual'] ?? 0) ?> un
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($m['observacao'] ?: '—') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-0 border-0">
                                    <div class="so-empty-state my-4 text-center py-5 text-muted">
                                        <i class="fas fa-arrow-right-arrow-left fs-1 d-block mb-3 opacity-50"></i>
                                        <h5 class="fw-bold text-dark">Nenhuma movimentação localizada</h5>
                                        <p class="text-muted">Não encontramos registros de movimentações para os filtros informados.</p>
                                        <?php if ($hasActiveFilters): ?>
                                        <a href="<?= BASE_URL ?>/produtos/movimentacoes.php" class="btn btn-secondary shadow-sm">
                                            <i class="fas fa-undo me-1"></i> Limpar Filtros
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMovimentacao">
                                            <i class="fas fa-plus-circle me-1"></i> Registrar Primeira Movimentação
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ PAGINAÇÃO INSTITUCIONAL SALESOPS ═══════════════════════════════ -->
        <?php
        $firstItem = $totalRows > 0 ? ($offset + 1) : 0;
        $lastItem  = min($offset + $limit, $totalRows);
        ?>
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Exibindo <strong><?= $firstItem ?></strong> a <strong><?= $lastItem ?></strong> de <strong><?= number_format($totalRows, 0, ',', '.') ?></strong> <?= $totalRows === 1 ? 'registro' : 'registros' ?>
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação da listagem de movimentações">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="movimentacoes.php?<?= http_build_query($queryParamsBase + ['pagina' => $page - 1]) ?>" aria-label="Anterior">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        
                        <?php
                        $range = 2;
                        $startPage = max(1, $page - $range);
                        $endPage = min($totalPages, $page + $range);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="movimentacoes.php?' . http_build_query($queryParamsBase + ['pagina' => 1]) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="movimentacoes.php?<?= http_build_query($queryParamsBase + ['pagina' => $i]) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="movimentacoes.php?' . http_build_query($queryParamsBase + ['pagina' => $totalPages]) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="movimentacoes.php?<?= http_build_query($queryParamsBase + ['pagina' => $page + 1]) ?>" aria-label="Próximo">
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

<!-- ══ MODAL NOVA MOVIMENTAÇÃO (SALESOPS CLEAN HEADER & WCAG AA) ═════════ -->
<div class="modal fade" id="modalMovimentacao" tabindex="-1" aria-labelledby="modalMovimentacaoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border shadow-lg" style="border-radius: var(--mr-radius); border-color: #cbd5e1 !important;">
            <div class="modal-header bg-white border-bottom py-3" style="border-color: #cbd5e1 !important;">
                <h5 class="modal-title fw-bold text-dark" id="modalMovimentacaoLabel">
                    <i class="fas fa-arrow-right-arrow-left text-primary me-2"></i>Registrar Movimentação
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form action="<?= BASE_URL ?>/produtos/functions.php?tipo=movimentacao" method="POST">
                <?= csrf_input() ?>
                <input type="hidden" name="acao" value="registrar">
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label for="mov_tipo" class="form-label fw-bold text-dark text-xs mb-1">
                            Tipo de Movimento <span class="text-danger">*</span>
                        </label>
                        <select class="form-select bg-white shadow-none" id="mov_tipo" name="tipo" required>
                            <option value="" disabled selected>Escolha o tipo...</option>
                            <optgroup label="Entradas (Soma no Estoque)">
                                <option value="entrada_compra">Entrada de Compra (Fornecedor)</option>
                                <option value="devolucao_cliente">Devolução de Cliente</option>
                            </optgroup>
                            <optgroup label="Saídas (Subtrai do Estoque)">
                                <option value="saida_venda">Saída Avulsa (Ajuste)</option>
                                <option value="devolucao_fornecedor">Devolução para Fornecedor</option>
                                <option value="perda">Perda / Avaria</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="mov_produto_id" class="form-label fw-bold text-dark text-xs mb-1">
                            Produto <span class="text-danger">*</span>
                        </label>
                        <select class="form-select bg-white shadow-none" id="mov_produto_id" name="produto_id" required>
                            <option value="" disabled selected>Selecione um produto...</option>
                            <?php foreach ($produtosLista as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['nome']) ?> (Estoque Atual: <?= (int)$p['quantidade'] ?> un)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="mov_quantidade" class="form-label fw-bold text-dark text-xs mb-1">
                            Quantidade <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control bg-white shadow-none tabular-nums" id="mov_quantidade" name="quantidade" min="1" required placeholder="Ex: 5">
                        <small class="text-muted text-xs d-block mt-1">Informe números inteiros positivos. O sistema soma ou subtrai conforme a regra do Tipo.</small>
                    </div>

                    <div class="mb-0">
                        <label for="mov_observacao" class="form-label fw-bold text-dark text-xs mb-1">
                            Observação / Motivo / NFe
                        </label>
                        <textarea class="form-control bg-white shadow-none" id="mov_observacao" name="observacao" rows="2" placeholder="Ex: Ajuste de contagem física de estoque ou NFe 1234..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-white p-3" style="border-color: #cbd5e1 !important;">
                    <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1"></i> Confirmar Movimentação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
