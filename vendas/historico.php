<?php
/**
 * MrStock ERP - Histórico de Vendas com Filtros Avançados, KPIs e Design System SalesOps
 */
$pageTitle  = 'MrStock ERP - Histórico de Vendas';
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
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Aviso:</strong> Identificador de venda não especificado para emissão do cupom fiscal.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'venda_nao_encontrada'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i>
            <strong>Erro:</strong> Venda não localizada no banco de dados.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ CARDS DE KPI (RESUMO DAS VENDAS FILTRADAS) ════════════════════════ -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="so-card p-3 mb-0" style="border-left: 4px solid var(--mr-bg-primary);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Vendas Filtradas</small>
                        <h3 class="fw-bold text-dark m-0"><?= $totalVendasQtd ?></h3>
                        <small class="text-muted"><?= $totalItensVendidos ?> itens no total</small>
                    </div>
                    <div class="bg-light p-3 rounded-circle" style="color:var(--mr-bg-primary);">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="so-card p-3 mb-0" style="border-left: 4px solid #10b981;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Faturamento Filtrado</small>
                        <h3 class="fw-bold text-success m-0">R$ <?= number_format($faturamentoTotal, 2, ',', '.') ?></h3>
                        <small class="text-muted">Total líquido realizado</small>
                    </div>
                    <div class="bg-light p-3 rounded-circle text-success">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-12 col-md-4">
            <div class="so-card p-3 mb-0" style="border-left: 4px solid #0ea5e9;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Ticket Médio</small>
                        <h3 class="fw-bold text-info m-0">R$ <?= number_format($ticketMedio, 2, ',', '.') ?></h3>
                        <small class="text-muted">Média por cupom fiscal</small>
                    </div>
                    <div class="bg-light p-3 rounded-circle text-info">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ BARRA DE FILTROS AVANÇADOS E LIVE SEARCH ══════════════════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="so-card-title m-0" style="font-size:0.9rem;">
                <i class="fas fa-filter text-primary"></i> Filtros de Pesquisa
            </h6>
            <!-- Live Search rápido -->
            <div class="so-search-box" style="min-width: 200px;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="liveSearchVendas" class="form-control form-control-sm" placeholder="Filtrar ao vivo..." onkeyup="filtrarVendas(this)">
            </div>
        </div>
        <div class="so-card-body p-3">
            <form method="GET" action="<?= BASE_URL ?>/vendas/historico.php" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold text-muted small mb-1">Data Inicial</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold text-muted small mb-1">Data Final</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold text-muted small mb-1">Cliente</label>
                    <select name="cliente_id" class="form-select form-select-sm">
                        <option value="">-- Todos os Clientes --</option>
                        <?php foreach ($clientesLista as $cli): ?>
                            <option value="<?= $cli['id'] ?>" <?= ($cliente_id == $cli['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cli['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label fw-bold text-muted small mb-1">Forma Pagamento</label>
                    <select name="forma_pagamento" class="form-select form-select-sm">
                        <option value="">-- Todas --</option>
                        <option value="DINHEIRO" <?= ($forma_pagamento === 'DINHEIRO') ? 'selected' : '' ?>>Dinheiro</option>
                        <option value="PIX" <?= ($forma_pagamento === 'PIX') ? 'selected' : '' ?>>PIX</option>
                        <option value="CARTÃO DE CRÉDITO" <?= ($forma_pagamento === 'CARTÃO DE CRÉDITO') ? 'selected' : '' ?>>Cartão de Crédito</option>
                        <option value="CARTÃO DE DÉBITO" <?= ($forma_pagamento === 'CARTÃO DE DÉBITO') ? 'selected' : '' ?>>Cartão de Débito</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100 shadow-sm">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                    <a href="<?= BASE_URL ?>/vendas/historico.php" class="btn btn-secondary btn-sm w-100 shadow-sm" title="Limpar Filtros">
                        <i class="fas fa-undo me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ TABELA MODULAR DE HISTÓRICO DE VENDAS ═════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-receipt text-primary"></i> Vendas Registradas</h5>
            <span class="so-badge so-badge-primary"><?= $totalVendasQtd ?> cupons</span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaVendas">
                    <thead>
                        <tr>
                            <th width="10%">Nº Venda</th>
                            <th width="18%">Data / Hora</th>
                            <th width="28%">Cliente</th>
                            <th width="10%" class="text-center">Itens</th>
                            <th width="16%">Pagamento</th>
                            <th width="12%" class="text-end pe-3">Total (R$)</th>
                            <th width="6%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($vendas) > 0): ?>
                            <?php foreach ($vendas as $v): ?>
                            <tr class="linha-venda">
                                <td class="fw-bold text-muted font-monospace">
                                    #<?= str_pad((string)$v['id'], 6, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td>
                                    <i class="far fa-clock text-muted me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($v['data_venda'])) ?>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($v['cliente_nome'] ?? 'Consumidor Final') ?></strong>
                                    <?php if (!empty($v['chave_acesso'])): ?>
                                        <small class="text-muted font-monospace" style="font-size:10px;">
                                            NFC-e: <?= substr($v['chave_acesso'], 0, 18) ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <?= (int)$v['qtd_itens'] ?> item(ns)
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-credit-card me-1 text-primary"></i><?= htmlspecialchars($v['forma_pagamento']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3 fw-bold text-success">
                                    R$ <?= number_format((float)$v['total'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ações">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="font-size:0.85rem;">
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/vendas/cupom.php?venda_id=<?= $v['id'] ?>" target="_blank">
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
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fs-1 d-block mb-3 opacity-50"></i>
                                    Nenhuma venda localizada para os filtros selecionados.
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
                    Exibindo <strong><?= $firstItem ?></strong> a <strong><?= $lastItem ?></strong> de <strong><?= $totalVendasQtd ?></strong> vendas
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
                            echo '<li class="page-item"><a class="page-link" href="historico.php?' . http_build_query($queryParams) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                            $queryParams = $_GET;
                            $queryParams['pagina'] = $i;
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="historico.php?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            $queryParams = $_GET;
                            $queryParams['pagina'] = $totalPages;
                            echo '<li class="page-item"><a class="page-link" href="historico.php?' . http_build_query($queryParams) . '">' . $totalPages . '</a></li>';
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
function filtrarVendas(input) {
    const termo = input.value.toLowerCase().trim();
    const linhas = document.querySelectorAll('#tabelaVendas tbody .linha-venda');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
