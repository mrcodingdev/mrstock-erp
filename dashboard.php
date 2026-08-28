<?php
/**
 * MrStock ERP - Dashboard Operacional com Design System SalesOps
 */
$pageTitle  = 'Dashboard Operacional';
$activePage = 'dashboard';
require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/auth.php';

$stmtTotal       = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE status = 'ativo'");
$totalProdutos   = (int)$stmtTotal->fetch()['total'];

$stmtEstoqueBaixo  = $pdo->query("SELECT p.*, f.nome as fornecedor_nome FROM produtos p LEFT JOIN fornecedores f ON p.fornecedor_id = f.id WHERE p.quantidade <= p.estoque_minimo AND p.status = 'ativo' ORDER BY p.quantidade ASC LIMIT 5");
$produtosEstoqueBaixo = $stmtEstoqueBaixo->fetchAll();

$stmtTotalBaixo  = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE quantidade <= estoque_minimo AND status = 'ativo'");
$totalEstoqueBaixo = (int)$stmtTotalBaixo->fetch()['total'];

$stmtVencimento  = $pdo->query("SELECT * FROM produtos WHERE validade IS NOT NULL AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'ativo' ORDER BY validade ASC LIMIT 5");
$produtosVencimento = $stmtVencimento->fetchAll();

$stmtTotalVenc   = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE validade IS NOT NULL AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'ativo'");
$totalVencimento = (int)$stmtTotalVenc->fetch()['total'];

$stmtVendasHoje  = $pdo->query("SELECT SUM(total) as vendas_hoje FROM vendas WHERE DATE(data_venda) = CURDATE()");
$vendasHoje      = (float)($stmtVendasHoje->fetch()['vendas_hoje'] ?? 0);

$stmtUltimasVendas = $pdo->query("SELECT v.*, c.nome as cliente_nome FROM vendas v LEFT JOIN clientes c ON v.cliente_id = c.id ORDER BY v.id DESC LIMIT 5");
$ultimasVendas = $stmtUltimasVendas->fetchAll();

require_once __DIR__ . '/inc/header.php';
?>

<!-- Alert de feedback -->
<?php if (isset($_GET['erro']) && $_GET['erro'] === 'estoque'): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Estoque Insuficiente!</strong>
    Produto <strong><?= htmlspecialchars($_GET['produto'] ?? '') ?></strong>
    — solicitado: <strong><?= (int)($_GET['solicitado'] ?? 1) ?></strong>,
    disponível: <strong><?= (int)($_GET['disponivel'] ?? 0) ?></strong> unidade(s).
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-gauge-high text-primary me-2"></i>Dashboard Operacional</h2>
        <p class="text-muted m-0">Visão global do estoque, faturamento do dia e alertas operacionais.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/relatorios/pdf.php" target="_blank" class="btn btn-secondary fw-semibold shadow-sm">
            <i class="fas fa-file-pdf me-1"></i> Abrir Relatório PDF
        </a>
        <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-cash-register me-1"></i> Abrir PDV Frente de Caixa
        </a>
    </div>
</div>

<div class="content-body">
    <!-- ══ CARDS DE RESUMO (SALESOPS STAT CARDS) ═════════════════════════════ -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card so-stat-card--primary p-3 mb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold text-xs">Produtos Ativos</small>
                        <h3 class="mb-0 fw-bold text-dark"><?= $totalProdutos ?></h3>
                        <small class="text-muted">Itens no catálogo</small>
                    </div>
                    <div class="bg-light p-3 rounded-circle" style="color:var(--mr-bg-primary);">
                        <i class="fas fa-boxes-stacked fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card so-stat-card--success p-3 mb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold text-xs">Vendas Hoje</small>
                        <h3 class="mb-0 fw-bold text-success">R$ <?= number_format($vendasHoje, 2, ',', '.') ?></h3>
                        <small class="text-muted">Faturamento diário</small>
                    </div>
                    <div class="bg-light p-3 rounded-circle text-success">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card so-stat-card--warning p-3 mb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold text-xs">Estoque Baixo</small>
                        <h3 class="mb-0 fw-bold text-warning"><?= $totalEstoqueBaixo ?></h3>
                        <small class="text-muted">Abaixo do mínimo</small>
                    </div>
                    <div class="bg-light p-3 rounded-circle text-warning">
                        <i class="fas fa-triangle-exclamation fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card so-stat-card--danger p-3 mb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold text-xs">Vencimentos (30d)</small>
                        <h3 class="mb-0 fw-bold text-danger"><?= $totalVencimento ?></h3>
                        <small class="text-muted">Validade próxima</small>
                    </div>
                    <div class="bg-light p-3 rounded-circle text-danger">
                        <i class="fas fa-calendar-xmark fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Esquerda: Últimas Vendas Realizadas -->
        <div class="col-lg-6">
            <div class="so-card mb-4">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-dark m-0"><i class="fas fa-clock-rotate-left text-primary me-2"></i> Últimas Vendas Realizadas</h5>
                    <a href="<?= BASE_URL ?>/vendas/historico.php" class="btn btn-sm btn-primary">Ver Histórico</a>
                </div>
                <div class="so-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 so-table align-middle">
                            <thead>
                                <tr>
                                    <th width="20%">Cód / Hora</th>
                                    <th width="40%">Cliente</th>
                                    <th width="20%">Pgto</th>
                                    <th width="20%" class="text-end pe-3">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($ultimasVendas) > 0): ?>
                                    <?php foreach ($ultimasVendas as $v): ?>
                                    <tr>
                                        <td>
                                            <strong class="font-monospace text-primary">#<?= str_pad((string)$v['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                            <br><small class="text-muted"><?= date('H:i', strtotime($v['data_venda'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-semibold"><?= htmlspecialchars($v['cliente_nome'] ?? 'Consumidor Final') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border" style="font-size:0.75rem;"><?= htmlspecialchars($v['forma_pagamento']) ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <strong class="text-success">R$ <?= number_format((float)$v['total'], 2, ',', '.') ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-info-circle me-1"></i> Nenhuma venda registrada hoje.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ruptura de Estoque -->
            <div class="so-card">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-warning m-0"><i class="fas fa-triangle-exclamation text-warning me-2"></i> Ruptura de Estoque</h5>
                    <a href="<?= BASE_URL ?>/produtos/index.php?status=baixo_estoque" class="btn btn-sm btn-warning">Ajustar Estoque</a>
                </div>
                <div class="so-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 so-table align-middle">
                            <thead>
                                <tr>
                                    <th width="50%">Produto</th>
                                    <th width="25%" class="text-center">Qtd Atual</th>
                                    <th width="25%" class="text-center">Qtd Mínima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($produtosEstoqueBaixo) > 0): ?>
                                    <?php foreach ($produtosEstoqueBaixo as $p): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($p['nome']) ?></strong>
                                            <br><small class="text-muted"><i class="fas fa-truck me-1"></i> <?= htmlspecialchars($p['fornecedor_nome'] ?? 'Sem Fornecedor') ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($p['quantidade'] == 0): ?>
                                                <span class="so-badge so-badge-danger"><i class="fas fa-ban me-1"></i> Esgotado (0)</span>
                                            <?php else: ?>
                                                <span class="so-badge so-badge-warning"><?= (int)$p['quantidade'] ?> un</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center text-muted fw-bold"><?= (int)$p['estoque_minimo'] ?> un</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-1"></i> Todos os produtos estão com estoque ideal.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Vencimentos e Atalhos Operacionais -->
        <div class="col-lg-6">
            <!-- Perto do Vencimento -->
            <div class="so-card mb-4">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-danger m-0"><i class="fas fa-calendar-alt text-danger me-2"></i> Perto do Vencimento</h5>
                    <a href="<?= BASE_URL ?>/produtos/index.php?status=vencido" class="btn btn-sm btn-danger">Gerenciar Vencimentos</a>
                </div>
                <div class="so-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 so-table align-middle">
                            <thead>
                                <tr>
                                    <th width="50%">Produto</th>
                                    <th width="25%">Validade</th>
                                    <th width="25%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($produtosVencimento) > 0): ?>
                                    <?php foreach ($produtosVencimento as $p):
                                        $dv   = new DateTime($p['validade']);
                                        $hoje = new DateTime();
                                        $dias = (int)$hoje->diff($dv)->format('%r%a');
                                        $cls  = $dias < 0 ? 'so-badge-danger' : ($dias <= 15 ? 'so-badge-danger' : 'so-badge-warning');
                                        $lbl  = $dias < 0 ? 'Vencido' : "Faltam {$dias} dias";
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($p['nome']) ?></strong>
                                            <br><small class="text-muted">Estoque atual: <?= (int)$p['quantidade'] ?> un</small>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($p['validade'])) ?></td>
                                        <td class="text-center"><span class="so-badge <?= htmlspecialchars($cls) ?>"><?= htmlspecialchars($lbl) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-1"></i> Nenhum produto com validade crítica.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas do Sistema -->
            <div class="so-card">
                <div class="so-card-header bg-dark text-white">
                    <h5 class="so-card-title text-white m-0"><i class="fas fa-bolt text-warning me-2"></i> Atalhos & Ações Rápidas</h5>
                </div>
                <div class="so-card-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-success w-100 py-3 fw-bold text-start shadow-sm">
                                <i class="fas fa-cash-register fa-lg me-2"></i> Abrir PDV Caixa
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/produtos/index.php" class="btn btn-primary w-100 py-3 fw-bold text-start shadow-sm">
                                <i class="fas fa-boxes-stacked fa-lg me-2"></i> Gestão de Estoque
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/compras/nova.php" class="btn btn-secondary w-100 py-3 fw-bold text-start shadow-sm">
                                <i class="fas fa-cart-flatbed fa-lg me-2"></i> Nova Compra (Entrada)
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/relatorios/analise.php" class="btn btn-dark w-100 py-3 fw-bold text-start shadow-sm">
                                <i class="fas fa-chart-line fa-lg me-2"></i> Relatórios & DRE
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
