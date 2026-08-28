<?php
/**
 * MrStock ERP - Gestão de Compras com Paginação e Design System SalesOps
 */
$pageTitle  = 'MrStock ERP - Gestão de Compras';
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

// ── Paginação Centralizada ──────────────────────────────────────────────────
$limit = 10;
$page  = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($page - 1) * $limit;

$totalRows = (int)$pdo->query("SELECT COUNT(*) FROM compras")->fetchColumn();
$totalPages = ceil($totalRows / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

$stmt = $pdo->prepare("
    SELECT c.*, f.nome as fornecedor_nome, u.username 
    FROM compras c 
    LEFT JOIN fornecedores f ON c.fornecedor_id = f.id 
    LEFT JOIN usuarios u ON c.usuario_id = u.id 
    ORDER BY c.data_compra DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$compras = $stmt->fetchAll();

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Gestão de Compras (Entradas)</h2>
        <p class="text-muted m-0">Consulte o histórico de abastecimento e gerencie contas a pagar com fornecedores.</p>
    </div>
    <a href="<?= BASE_URL ?>/compras/nova.php" class="btn btn-primary fw-bold shadow-sm">
        <i class="fas fa-cart-plus me-1"></i> Registrar Nova Compra
    </a>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <strong>Sucesso!</strong> Compra registrada e estoque atualizado com sucesso. 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['msg'] == 'erro'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <strong>Erro!</strong> Ocorreu um problema ao registrar a compra. 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['msg'] == 'status_atualizado'): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm border-0">
            <strong>Atualizado!</strong> O status de pagamento foi alterado. 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ BARRA DE LIVE SEARCH ═════════════════════════════════════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-body p-3">
            <div class="so-search-box w-100" style="max-width:100%;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="liveSearchCompras" class="form-control" placeholder="Filtrar compras ao vivo por fornecedor, número ou nota fiscal..." onkeyup="filtrarCompras(this)">
            </div>
        </div>
    </div>

    <!-- ══ TABELA MODULAR DE COMPRAS ════════════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-receipt text-primary"></i> Pedidos de Compra</h5>
            <span class="so-badge so-badge-primary"><?= $totalRows ?> registros</span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaCompras">
                    <thead>
                        <tr>
                            <th width="10%">Nº Compra</th>
                            <th width="20%">Data / Operador</th>
                            <th width="25%">Fornecedor</th>
                            <th width="14%">Nota Fiscal</th>
                            <th width="15%">Valor Total</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="6%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($compras) > 0): ?>
                            <?php foreach ($compras as $c): 
                                $badgeClass = ($c['status'] == 'PAGA') ? 'so-badge-success' : 'so-badge-warning';
                            ?>
                            <tr class="linha-compra">
                                <td class="fw-bold text-muted font-monospace">#<?= str_pad((string)$c['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <i class="far fa-clock text-muted me-1"></i><?= date('d/m/Y H:i', strtotime($c['data_compra'])) ?>
                                    <br><small class="text-muted">Por: <?= htmlspecialchars($c['username'] ?? 'Sistema') ?></small>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($c['fornecedor_nome'] ?: 'Desconhecido') ?></strong>
                                </td>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($c['numero_nota'] ?: 'S/N') ?></span></td>
                                <td class="fw-bold text-success">R$ <?= number_format((float)$c['valor_total'], 2, ',', '.') ?></td>
                                <td class="text-center">
                                    <span class="so-badge <?= $badgeClass ?>"><?= htmlspecialchars($c['status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ações">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="font-size:0.85rem;">
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
                                                    <input type="hidden" name="novo_status" value="<?= $c['status'] == 'PAGA' ? 'PENDENTE' : 'PAGA' ?>">
                                                    <?php if ($c['status'] == 'PENDENTE'): ?>
                                                        <button type="submit" class="dropdown-item text-success py-1" onclick="return confirm('Confirmar pagamento desta compra?');">
                                                            <i class="fas fa-check-circle me-2"></i> Marcar como Paga
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
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice-dollar fs-1 d-block mb-3 opacity-50"></i>
                                    Nenhuma compra registrada ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ PAGINAÇÃO MODERNA INSTITUCIONAL ═════════════════════════ -->
        <?php
        $firstItem = $totalRows > 0 ? ($offset + 1) : 0;
        $lastItem  = min($offset + $limit, $totalRows);
        ?>
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Exibindo <strong><?= $firstItem ?></strong> a <strong><?= $lastItem ?></strong> de <strong><?= $totalRows ?></strong> compras
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação da listagem">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?pagina=<?= $page - 1 ?>" aria-label="Anterior">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        
                        <?php
                        $range = 2;
                        $startPage = max(1, $page - $range);
                        $endPage = min($totalPages, $page + $range);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="index.php?pagina=1">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?pagina=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="index.php?pagina=' . $totalPages . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?pagina=<?= $page + 1 ?>" aria-label="Próximo">
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
function filtrarCompras(input) {
    const termo = input.value.toLowerCase().trim();
    const linhas = document.querySelectorAll('#tabelaCompras tbody .linha-compra');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
