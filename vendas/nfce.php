<?php
/**
 * MrStock ERP - Consulta e Painel Fiscal NFC-e
 * Módulo de Consulta de Cupons Fiscais, Chaves de Acesso e Auditoria Fiscal
 * Padrão SalesOps v0 - Design System & Anti-Slop (14 Zonas)
 */
$pageTitle  = 'Consulta Fiscal NFC-e';
$activePage = 'fiscal';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/functions.php';

// ── 1. Estatísticas Globais para os Stat Cards (Bento Grid) ─────────────────
$stmtStats = $pdo->query("
    SELECT 
        COUNT(cf.id) AS total_cupons,
        COALESCE(SUM(v.total), 0) AS total_faturamento
    FROM cupons_fiscais cf
    JOIN vendas v ON cf.venda_id = v.id
");
$statsGlobal = $stmtStats->fetch(PDO::FETCH_ASSOC);
$totalCuponsEmitidos    = (int)($statsGlobal['total_cupons'] ?? 0);
$totalFaturamentoFiscal = (float)($statsGlobal['total_faturamento'] ?? 0.0);

// Estatísticas do dia atual (Hoje)
$stmtHoje = $pdo->query("
    SELECT 
        COUNT(cf.id) AS cupons_hoje,
        COALESCE(SUM(v.total), 0) AS faturamento_hoje
    FROM cupons_fiscais cf
    JOIN vendas v ON cf.venda_id = v.id
    WHERE DATE(cf.data_emissao) = CURDATE()
");
$statsHoje = $stmtHoje->fetch(PDO::FETCH_ASSOC);
$cuponsHoje      = (int)($statsHoje['cupons_hoje'] ?? 0);
$faturamentoHoje = (float)($statsHoje['faturamento_hoje'] ?? 0.0);

// ── 2. Filtros Recebidos via GET ─────────────────────────────────────────────
$busca           = trim($_GET['busca'] ?? '');
$cliente_id      = filter_var($_GET['cliente_id'] ?? '', FILTER_VALIDATE_INT) ?: null;
$data_inicio     = trim($_GET['data_inicio'] ?? '');
$data_fim        = trim($_GET['data_fim'] ?? '');
$forma_pagamento = trim($_GET['forma_pagamento'] ?? '');
$hasActiveFilters = !empty($busca) || !empty($data_inicio) || !empty($data_fim) || !empty($cliente_id) || !empty($forma_pagamento);

// ── 3. Carregamento da Lista de Clientes para o Select ───────────────────────
$stmtClientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
$clientesLista = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// ── 4. Construção Dinâmica da Query SQL com PDO ──────────────────────────────
$where = ["1=1"];
$params = [];

if (!empty($busca)) {
    $where[] = "(cf.chave_acesso LIKE :busca OR c.nome LIKE :busca OR c.cpf_cnpj LIKE :busca OR CAST(cf.venda_id AS CHAR) LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}
if ($cliente_id) {
    $where[] = "v.cliente_id = :cliente_id";
    $params[':cliente_id'] = $cliente_id;
}
if (!empty($data_inicio)) {
    $where[] = "DATE(cf.data_emissao) >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
}
if (!empty($data_fim)) {
    $where[] = "DATE(cf.data_emissao) <= :data_fim";
    $params[':data_fim'] = $data_fim;
}
if (!empty($forma_pagamento)) {
    $where[] = "v.forma_pagamento = :forma_pagamento";
    $params[':forma_pagamento'] = $forma_pagamento;
}

$whereSql = implode(" AND ", $where);

// ── 5. Contagem para Paginação ───────────────────────────────────────────────
$countSql = "
    SELECT COUNT(cf.id) 
    FROM cupons_fiscais cf 
    JOIN vendas v ON cf.venda_id = v.id 
    LEFT JOIN clientes c ON v.cliente_id = c.id 
    WHERE $whereSql
";
$stmtCount = $pdo->prepare($countSql);
foreach ($params as $k => $v) {
    if (is_int($v)) {
        $stmtCount->bindValue($k, $v, PDO::PARAM_INT);
    } else {
        $stmtCount->bindValue($k, $v, PDO::PARAM_STR);
    }
}
$stmtCount->execute();
$totalCupons = (int)$stmtCount->fetchColumn();

// ── 6. Paginação Centralizada (10 itens por página) ──────────────────────────
$limit = 10;
$page  = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($page - 1) * $limit;
$totalPages = ceil($totalCupons / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// ── 7. Busca Paginada de Cupons Fiscais ──────────────────────────────────────
$sql = "
    SELECT cf.*, v.total, v.forma_pagamento, c.nome AS cliente_nome, c.cpf_cnpj AS cliente_documento
    FROM cupons_fiscais cf 
    JOIN vendas v ON cf.venda_id = v.id 
    LEFT JOIN clientes c ON v.cliente_id = c.id 
    WHERE $whereSql 
    ORDER BY cf.data_emissao DESC 
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    if (is_int($v)) {
        $stmt->bindValue($k, $v, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── 8. Helper de Formatação de Forma de Pagamento ────────────────────────────
if (!function_exists('render_forma_pagamento')) {
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
}
if (!function_exists('render_forma_pagamento_badge')) {
    function render_forma_pagamento_badge(string $forma): string {
        return render_forma_pagamento($forma);
    }
}

require_once __DIR__ . '/../inc/header.php';
?>

    <!-- ══ HEADER DA PÁGINA ═════════════════════════════════════════════════ -->
    <div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-receipt text-primary me-2"></i>Consulta Fiscal NFC-e</h2>
            <p class="text-muted m-0">Consulte os cupons fiscais e chaves de acesso NFC-e gerados nas vendas.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-primary fw-bold shadow-sm">
                <i class="fas fa-cash-register me-1"></i> Abrir PDV
            </a>
        </div>
    </div>

    <div class="content-body">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>Venda registrada e NFC-e emitido com sucesso!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>

        <div class="alert alert-info border-0 shadow-sm mb-3">
            <i class="fas fa-info-circle me-2"></i> Ambiente fiscal em modo de <strong>Homologação (Simulação TCC)</strong>. As chaves de acesso de 44 dígitos e a assinatura digital seguem o padrão técnico da SEFAZ SP para fins didáticos.
        </div>

        <!-- ══ 3 STAT CARDS NO TOPO (BENTO GRID SALESOPS) ═══════════════════════ -->
        <div class="row g-3 mb-3">
            <!-- Card 1: Total de Cupons Emitidos -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="so-card p-3 mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Total de Cupons</span>
                            <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalCuponsEmitidos, 0, ',', '.') ?></h3>
                            <small class="text-muted"><?= ($totalCuponsEmitidos === 1) ? '1 cupom emitido' : 'Cupons emitidos no total' ?></small>
                        </div>
                        <div class="kpi-icon-box kpi-icon-box--primary">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Faturamento Fiscal Documentado -->
            <div class="col-12 col-sm-6 col-md-4">
                <div class="so-card p-3 mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Faturamento Fiscal</span>
                            <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($totalFaturamentoFiscal, 2, ',', '.') ?></h3>
                            <small class="text-muted">Total acobertado por NFC-e</small>
                        </div>
                        <div class="kpi-icon-box kpi-icon-box--success">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Cupons Emitidos Hoje -->
            <div class="col-12 col-sm-12 col-md-4">
                <div class="so-card p-3 mb-0 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Emitidos Hoje</span>
                            <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($cuponsHoje, 0, ',', '.') ?></h3>
                            <small class="text-muted"><span class="tabular-nums">R$ <?= number_format($faturamentoHoje, 2, ',', '.') ?></span> <?= ($cuponsHoje === 1) ? 'faturado hoje' : 'faturados hoje' ?></small>
                        </div>
                        <div class="kpi-icon-box kpi-icon-box--danger">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ BARRA DE FILTROS UNIFICADA (ACESSIBILIDADE WCAG 2.1 AA) ═════════ -->
        <div class="so-card p-3 mb-3">
            <form method="GET" action="<?= BASE_URL ?>/vendas/nfce.php" class="row g-2 align-items-end">
                <!-- 1. Campo de Busca -->
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="buscaNfce" class="form-label fw-bold text-dark text-xs mb-1">Buscar por Chave / Cliente / ID Venda</label>
                    <div class="position-relative">
                        <input type="text" name="busca" id="buscaNfce" class="form-control ps-4 shadow-none" placeholder="Chave, cliente ou ID da venda..." value="<?= htmlspecialchars($busca) ?>" aria-label="Buscar por chave de acesso, cliente ou ID da venda">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
                    </div>
                </div>

                <!-- 2. Cliente -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="clienteIdNfce" class="form-label fw-bold text-dark text-xs mb-1">Cliente</label>
                    <select id="clienteIdNfce" name="cliente_id" class="form-select shadow-none" aria-label="Filtrar por Cliente">
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
                    <label for="dataInicioNfce" class="form-label fw-bold text-dark text-xs mb-1">Data Inicial</label>
                    <input type="date" name="data_inicio" id="dataInicioNfce" class="form-control tabular-nums shadow-none px-2" value="<?= htmlspecialchars($data_inicio) ?>" aria-label="Data Inicial">
                </div>

                <!-- 4. Data Final -->
                <div class="col-6 col-sm-3 col-md-2 col-lg-2">
                    <label for="dataFimNfce" class="form-label fw-bold text-dark text-xs mb-1">Data Final</label>
                    <input type="date" name="data_fim" id="dataFimNfce" class="form-control tabular-nums shadow-none px-2" value="<?= htmlspecialchars($data_fim) ?>" aria-label="Data Final">
                </div>

                <!-- 5. Forma de Pagamento -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-1">
                    <label for="formaPagamentoNfce" class="form-label fw-bold text-dark text-xs mb-1">Pagamento</label>
                    <select id="formaPagamentoNfce" name="forma_pagamento" class="form-select shadow-none" aria-label="Filtrar por Forma de Pagamento">
                        <option value="">-- Todas --</option>
                        <option value="DINHEIRO" <?= ($forma_pagamento === 'DINHEIRO') ? 'selected' : '' ?>>Dinheiro</option>
                        <option value="PIX" <?= ($forma_pagamento === 'PIX') ? 'selected' : '' ?>>PIX</option>
                        <option value="CARTÃO DE CRÉDITO" <?= ($forma_pagamento === 'CARTÃO DE CRÉDITO') ? 'selected' : '' ?>>Cartão Crédito</option>
                        <option value="CARTÃO DE DÉBITO" <?= ($forma_pagamento === 'CARTÃO DE DÉBITO') ? 'selected' : '' ?>>Cartão Débito</option>
                    </select>
                </div>

                <!-- 6. Botões de Ação -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-primary fw-bold flex-fill shadow-sm" title="Filtrar Cupons" aria-label="Filtrar Cupons">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <?php if ($hasActiveFilters): ?>
                    <a href="<?= BASE_URL ?>/vendas/nfce.php" class="btn btn-secondary px-3 shadow-sm" title="Limpar Filtros" aria-label="Limpar Filtros">
                        <i class="fas fa-undo"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ══ TABELA DE CUPONS FISCAIS ═════════════════════════════════════════ -->
        <div class="so-card">
            <div class="so-card-header d-flex justify-content-between align-items-center">
                <h5 class="so-card-title m-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Cupons NFC-e Emitidos</h5>
                <span class="badge bg-secondary-subtle text-secondary border tabular-nums fw-semibold"><?= $totalCupons === 1 ? '1 cupom' : "$totalCupons cupons" ?></span>
            </div>
            <div class="so-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 so-table align-middle">
                        <thead>
                            <tr>
                                <th scope="col" width="14%">Data da Emissão</th>
                                <th scope="col" width="8%" class="text-center">ID Venda</th>
                                <th scope="col" width="34%">Chave de Acesso (44 dígitos)</th>
                                <th scope="col" width="16%">Cliente</th>
                                <th scope="col" width="12%">Pagamento</th>
                                <th scope="col" width="10%" class="text-end">Total Cupom</th>
                                <th scope="col" width="6%" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($totalCupons > 0 && !empty($cupons)): ?>
                                <?php foreach ($cupons as $cf): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <span class="text-dark d-block fw-semibold tabular-nums"><?= date('d/m/Y', strtotime($cf['data_emissao'])) ?></span>
                                            <span class="text-muted text-xs tabular-nums"><?= date('H:i:s', strtotime($cf['data_emissao'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-secondary font-monospace tabular-nums">#<?= str_pad((string)$cf['venda_id'], 5, '0', STR_PAD_LEFT) ?></span>
                                    </td>
                                    <td>
                                        <code class="bg-light text-dark border px-2 py-1 rounded d-inline-block font-monospace tabular-nums" style="letter-spacing: 0.5px; font-size: 11px;">
                                            <?= preg_replace('/(\d{4})/', '$1 ', $cf['chave_acesso']) ?>
                                        </code>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-medium d-block"><?= htmlspecialchars($cf['cliente_nome'] ?? 'Consumidor Final') ?></span>
                                        <?php if (!empty($cf['cliente_documento'])): ?>
                                            <small class="text-muted tabular-nums"><?= htmlspecialchars(formatar_cpf_cnpj($cf['cliente_documento'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= render_forma_pagamento($cf['forma_pagamento'] ?? '') ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-dark fw-bold tabular-nums">R$ <?= number_format((float)$cf['total'], 2, ',', '.') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/vendas/cupom.php?venda_id=<?= $cf['venda_id'] ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary shadow-sm" title="Visualizar Cupom Fiscal da Venda #<?= $cf['venda_id'] ?>" aria-label="Visualizar Cupom Fiscal da Venda #<?= $cf['venda_id'] ?>">
                                            <i class="fas fa-receipt me-1"></i> Ver Cupom
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                        <span class="fw-semibold d-block text-dark mb-1">Nenhum cupom fiscal encontrado</span>
                                        <span class="text-muted text-xs"><?= $hasActiveFilters ? 'Nenhum cupom fiscal corresponde aos filtros informados.' : 'Nenhum cupom fiscal emitido até o momento.' ?></span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ══ PAGINAÇÃO INSTITUCIONAL COM PROPAGAÇÃO GET ═══════════════════ -->
            <?php
            $firstItem = $totalCupons > 0 ? ($offset + 1) : 0;
            $lastItem  = min($offset + $limit, $totalCupons);
            ?>
            <div class="card-footer bg-white border-top p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <span class="text-muted small">
                        Exibindo <strong class="tabular-nums"><?= $firstItem ?></strong> a <strong class="tabular-nums"><?= $lastItem ?></strong> de <strong class="tabular-nums"><?= $totalCupons ?></strong> <?= ($totalCupons === 1) ? 'cupom' : 'cupons' ?>
                    </span>
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navegação de cupons fiscais">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <?php
                                    $queryParams = $_GET;
                                    $queryParams['pagina'] = $page - 1;
                                ?>
                                <a class="page-link" href="nfce.php?<?= http_build_query($queryParams) ?>" aria-label="Página Anterior">
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
                                echo '<li class="page-item"><a class="page-link tabular-nums" href="nfce.php?' . http_build_query($queryParams) . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++): 
                                $queryParams = $_GET;
                                $queryParams['pagina'] = $i;
                            ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link tabular-nums" href="nfce.php?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                $queryParams = $_GET;
                                $queryParams['pagina'] = $totalPages;
                                echo '<li class="page-item"><a class="page-link tabular-nums" href="nfce.php?' . http_build_query($queryParams) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <?php
                                    $queryParams = $_GET;
                                    $queryParams['pagina'] = $page + 1;
                                ?>
                                <a class="page-link" href="nfce.php?<?= http_build_query($queryParams) ?>" aria-label="Próxima Página">
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
