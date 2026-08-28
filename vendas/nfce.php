<?php
$pageTitle  = 'MrStock ERP - Painel Fiscal (NFC-e)';
$activePage = 'fiscal';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// ── 1. Filtros e Parâmetros de Busca ─────────────────────────────────────────
$busca = trim($_GET['busca'] ?? '');
$where = [];
$params = [];

if ($busca !== '') {
    $where[] = "(cf.chave_acesso LIKE :busca OR c.nome LIKE :busca OR CAST(cf.venda_id AS CHAR) LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// ── 2. Contagem para Paginação ───────────────────────────────────────────────
$countSql = "
    SELECT COUNT(*) 
    FROM cupons_fiscais cf 
    JOIN vendas v ON cf.venda_id = v.id 
    LEFT JOIN clientes c ON v.cliente_id = c.id 
    $whereSql
";
$stmtCount = $pdo->prepare($countSql);
foreach ($params as $k => $v) {
    $stmtCount->bindValue($k, $v);
}
$stmtCount->execute();
$totalCupons = (int)$stmtCount->fetchColumn();

// ── 3. Paginação Centralizada (10 itens por página) ──────────────────────────
$limit = 10;
$page  = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($page - 1) * $limit;
$totalPages = ceil($totalCupons / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// ── 4. Busca Paginada de Cupons Fiscais ──────────────────────────────────────
$sql = "
    SELECT cf.*, v.total, c.nome as cliente_nome 
    FROM cupons_fiscais cf 
    JOIN vendas v ON cf.venda_id = v.id 
    LEFT JOIN clientes c ON v.cliente_id = c.id 
    $whereSql 
    ORDER BY cf.data_emissao DESC 
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$cupons = $stmt->fetchAll();

require_once __DIR__ . '/../inc/header.php';
?>

    <div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-receipt text-danger me-2"></i>Controle Fiscal Simulado</h2>
            <p class="text-muted m-0">Consulte os XMLs e Cupons NFC-e gerados nas Vendas.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-danger fw-bold shadow-sm">
                <i class="fas fa-shopping-cart me-1"></i> Ir para o PDV
            </a>
        </div>
    </div>

    <div class="content-body">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>Venda registrada e NFC-e emitido com sucesso!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="alert alert-info border-0 shadow-sm mb-4">
            <i class="fas fa-info-circle me-2"></i> Ambiente fiscal em modo de <strong>Homologação (Simulação TCC)</strong>. As chaves de acesso são geradas aleatoriamente e não possuem valor legal na SEFAZ.
        </div>

        <!-- ══ BARRA DE FILTROS E BUSCA ═══════════════════════════════════════ -->
        <div class="so-card mb-3 p-3">
            <form method="GET" action="<?= BASE_URL ?>/vendas/nfce.php" class="row g-2 align-items-center">
                <div class="col-md-9 col-12">
                    <div class="so-search-box w-100" style="max-width:100%;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="busca" id="buscaNfce" class="form-control" placeholder="Buscar por chave de acesso, cliente ou ID da venda..." value="<?= htmlspecialchars($busca) ?>">
                    </div>
                </div>
                <div class="col-md-3 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <?php if ($busca !== ''): ?>
                    <a href="<?= BASE_URL ?>/vendas/nfce.php" class="btn btn-secondary fw-bold" title="Limpar Filtro">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ══ TABELA MODULAR DE CUPONS FISCAIS ═══════════════════════════════ -->
        <div class="so-card">
            <div class="so-card-header d-flex justify-content-between align-items-center">
                <h5 class="so-card-title m-0"><i class="fas fa-file-invoice-dollar text-danger me-2"></i>Cupons NFC-e Emitidos</h5>
                <span class="so-badge so-badge-primary"><?= $totalCupons ?> registros</span>
            </div>
            <div class="so-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 so-table align-middle">
                        <thead>
                            <tr>
                                <th width="18%">Data da Emissão</th>
                                <th width="10%" class="text-center">ID Venda</th>
                                <th width="42%">Chave de Acesso (44 dígitos)</th>
                                <th width="15%">Cliente</th>
                                <th width="10%" class="text-end">Total Cupom</th>
                                <th width="5%" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($totalCupons > 0 && !empty($cupons)): ?>
                                <?php foreach ($cupons as $cf): ?>
                                <tr>
                                    <td>
                                        <span class="text-dark d-block fw-semibold">
                                            <i class="far fa-calendar-alt text-muted me-1 small"></i> <?= date('d/m/Y', strtotime($cf['data_emissao'])) ?>
                                        </span>
                                        <small class="text-muted"><i class="far fa-clock me-1"></i><?= date('H:i:s', strtotime($cf['data_emissao'])) ?></small>
                                    </td>
                                    <td class="text-center fw-bold text-secondary font-monospace">#<?= str_pad((string)$cf['venda_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <code class="bg-light text-dark border px-2 py-1 rounded d-inline-block font-monospace" style="letter-spacing:0.5px; font-size:12px;">
                                            <?= preg_replace('/(\d{4})/', '$1 ', $cf['chave_acesso']) ?>
                                        </code>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-medium"><?= htmlspecialchars($cf['cliente_nome'] ?? 'Consumidor Final') ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-success font-monospace">
                                        R$ <?= number_format((float)$cf['total'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/vendas/cupom.php?venda_id=<?= $cf['venda_id'] ?>" target="_blank" class="btn btn-sm btn-danger shadow-sm" title="Imprimir Cupom Fiscal NFC-e">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fs-1 d-block mb-3 text-light"></i>
                                        Nenhum cupom fiscal NFC-e localizado <?= $busca !== '' ? 'para o termo buscado.' : 'até o momento.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ══ PAGINAÇÃO MODERNA INSTITUCIONAL ════════════════════════════ -->
            <?php
            $firstItem = $totalCupons > 0 ? ($offset + 1) : 0;
            $lastItem  = min($offset + $limit, $totalCupons);
            ?>
            <div class="card-footer bg-white border-top p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <span class="text-muted small">
                        Exibindo <strong><?= $firstItem ?></strong> a <strong><?= $lastItem ?></strong> de <strong><?= $totalCupons ?></strong> cupons
                    </span>
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navegação de cupons">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <?php
                                    $queryParams = $_GET;
                                    $queryParams['pagina'] = $page - 1;
                                ?>
                                <a class="page-link" href="nfce.php?<?= http_build_query($queryParams) ?>" aria-label="Anterior">
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
                                echo '<li class="page-item"><a class="page-link" href="nfce.php?' . http_build_query($queryParams) . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++): 
                                $queryParams = $_GET;
                                $queryParams['pagina'] = $i;
                            ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="nfce.php?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                $queryParams = $_GET;
                                $queryParams['pagina'] = $totalPages;
                                echo '<li class="page-item"><a class="page-link" href="nfce.php?' . http_build_query($queryParams) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                               <?php
                                   $queryParams = $_GET;
                                   $queryParams['pagina'] = $page + 1;
                               ?>
                                <a class="page-link" href="nfce.php?<?= http_build_query($queryParams) ?>" aria-label="Próximo">
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
