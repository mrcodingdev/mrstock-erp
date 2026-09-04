<?php
/**
 * MrStock ERP - Dashboard Operacional com Design System SalesOps (v0)
 */
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/auth.php';

// ── 1. Total de Produtos Ativos ─────────────────────────────────────────────
$stmtTotal     = $pdo->query("SELECT COUNT(*) AS total FROM produtos WHERE status = 'ativo'");
$totalProdutos = (int)($stmtTotal->fetchColumn() ?: 0);

// ── 2. Vendas do Dia (Total Faturado e Quantidade de Transações) ─────────────
$stmtVendasHoje  = $pdo->query("
    SELECT COALESCE(SUM(total), 0) AS total, 
           COUNT(*)                AS qtd 
    FROM vendas 
    WHERE DATE(data_venda) = CURDATE()
");
$rowVendasHoje   = $stmtVendasHoje->fetch();
$vendasHojeTotal = (float)($rowVendasHoje['total'] ?? 0);
$vendasHojeQtd   = (int)($rowVendasHoje['qtd'] ?? 0);

// ── 3. Produtos com Ruptura / Estoque Baixo ─────────────────────────────────
$stmtEstoqueBaixo = $pdo->query("
    SELECT p.id, p.nome, p.quantidade, p.estoque_minimo, f.nome AS fornecedor_nome 
    FROM produtos p 
    LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
    WHERE p.quantidade <= p.estoque_minimo AND p.status = 'ativo' 
    ORDER BY p.quantidade ASC, p.nome ASC 
    LIMIT 5
");
$produtosEstoqueBaixo = $stmtEstoqueBaixo->fetchAll();

$stmtTotalBaixo    = $pdo->query("SELECT COUNT(*) AS total FROM produtos WHERE quantidade <= estoque_minimo AND status = 'ativo'");
$totalEstoqueBaixo = (int)($stmtTotalBaixo->fetchColumn() ?: 0);

// ── 4. Produtos Próximos ao Vencimento ───────────────────────────────────────
$diasAlertaVenc = (int)get_app_config($pdo, 'alerta_vencimento_dias', '30');

$dataLimiteVenc = date('Y-m-d', strtotime("+{$diasAlertaVenc} days"));
$stmtVencimento = $pdo->prepare("
    SELECT l.id, p.nome, l.numero_lote, l.quantidade, l.data_validade, 
           DATEDIFF(l.data_validade, CURDATE()) AS dias_para_vencer 
    FROM lotes l 
    INNER JOIN produtos p ON l.produto_id = p.id 
    WHERE l.quantidade > 0 
      AND l.data_validade <= ? 
      AND p.status = 'ativo' 
    ORDER BY l.data_validade ASC, l.id ASC 
    LIMIT 5
");
$stmtVencimento->execute([$dataLimiteVenc]);
$produtosVencimento = $stmtVencimento->fetchAll(PDO::FETCH_ASSOC);

$stmtTotalVenc = $pdo->prepare("
    SELECT COUNT(*) AS total 
    FROM lotes l 
    INNER JOIN produtos p ON l.produto_id = p.id 
    WHERE l.quantidade > 0 
      AND l.data_validade <= ? 
      AND p.status = 'ativo'
");
$stmtTotalVenc->execute([$dataLimiteVenc]);
$totalVencimento = (int)($stmtTotalVenc->fetchColumn() ?: 0);

// ── 5. Últimas Vendas Realizadas ─────────────────────────────────────────────
$stmtUltimasVendas = $pdo->query("
    SELECT v.id, v.data_venda, v.total, v.forma_pagamento, 
           COALESCE(c.nome, 'Consumidor Final') AS cliente_nome 
    FROM vendas v 
    LEFT JOIN clientes c ON v.cliente_id = c.id 
    ORDER BY v.data_venda DESC, v.id DESC 
    LIMIT 5
");
$ultimasVendas = $stmtUltimasVendas->fetchAll();

// ── 6. Formatação Limpa de Formas de Pagamento ───────────────────────────────
if (!function_exists('render_forma_pagamento')) {
    function render_forma_pagamento(string $forma): string {
        $formaUpper = mb_strtoupper(trim($forma), 'UTF-8');
        if (strpos($formaUpper, 'DINHEIRO') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-1"><i class="fas fa-money-bill-wave text-success"></i> <span>Dinheiro</span></span>';
        } elseif (strpos($formaUpper, 'PIX') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-1"><i class="fas fa-bolt text-warning"></i> <span>PIX</span></span>';
        } elseif (strpos($formaUpper, 'CRÉDITO') !== false || strpos($formaUpper, 'CREDITO') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-1"><i class="fas fa-credit-card text-primary"></i> <span>Cartão de Crédito</span></span>';
        } elseif (strpos($formaUpper, 'DÉBITO') !== false || strpos($formaUpper, 'DEBITO') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-1"><i class="fas fa-credit-card text-info"></i> <span>Cartão de Débito</span></span>';
        }
        return '<span class="text-dark d-inline-flex align-items-center gap-1"><i class="fas fa-wallet text-secondary"></i> <span>' . htmlspecialchars($forma, ENT_QUOTES, 'UTF-8') . '</span></span>';
    }
}

require_once __DIR__ . '/inc/header.php';
?>

<!-- Alert de feedback para estoque insuficiente -->
<?php if (isset($_GET['erro']) && $_GET['erro'] === 'estoque'): 
    $disp = (int)($_GET['disponivel'] ?? 0);
    $solic = (int)($_GET['solicitado'] ?? 1);
?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
    <i class="fas fa-triangle-exclamation me-2"></i>
    <strong>Estoque Insuficiente!</strong>
    Produto <strong><?= htmlspecialchars($_GET['produto'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
    — solicitado: <strong class="tabular-nums"><?= $solic ?></strong> <?= ($solic === 1 ? 'unidade' : 'unidades') ?>,
    disponível: <strong class="tabular-nums"><?= $disp ?></strong> <?= ($disp === 1 ? 'unidade' : 'unidades') ?>.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar alerta"></button>
</div>
<?php elseif (isset($_GET['erro']) && $_GET['erro'] === 'lote_vencido'): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-triangle-exclamation me-2 text-danger"></i> <strong>Bloqueio Sanitário (CDC Art. 18):</strong> O produto '<?= htmlspecialchars($_GET['produto'] ?? '') ?>' não possui saldo em lotes válidos (venceu ou está esgotado).
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<?php endif; ?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-gauge-high text-primary me-2"></i>Dashboard Operacional</h2>
        <p class="text-muted m-0">Visão global do estoque, faturamento do dia e alertas operacionais.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/relatorios/pdf.php" target="_blank" rel="noopener noreferrer" class="btn btn-secondary fw-semibold shadow-sm">
            <i class="fas fa-file-pdf me-1"></i> Abrir Relatório PDF
        </a>
        <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-cash-register me-1"></i> Abrir PDV Frente de Caixa
        </a>
    </div>
</div>

<div class="content-body">
    <!-- ══ CARDS DE RESUMO (BENTO GRID SALESOPS DE ELITE) ════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Produtos Ativos -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Produtos Ativos</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $totalProdutos ?></h3>
                        <small class="text-muted"><?= ($totalProdutos === 1) ? '1 item no catálogo' : "$totalProdutos itens no catálogo" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Vendas Hoje -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Vendas Hoje</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($vendasHojeTotal, 2, ',', '.') ?></h3>
                        <small class="text-muted"><?= ($vendasHojeQtd === 1 ? '1 transação hoje' : "$vendasHojeQtd transações hoje") ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Estoque Baixo -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Estoque Baixo</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $totalEstoqueBaixo ?></h3>
                        <small class="text-muted">Abaixo do mínimo</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--warning">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Vencimentos (<?= $diasAlertaVenc ?>d) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Vencimentos (<?= $diasAlertaVenc ?>d)</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $totalVencimento ?></h3>
                        <small class="text-muted">Validade próxima</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--danger">
                        <i class="fas fa-calendar-xmark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ LAYOUT DE 2 COLUNAS ALINHADAS ═════════════════════════════════════ -->
    <div class="row g-4">
        <!-- ── COLUNA ESQUERDA ─────────────────────────────────────────────── -->
        <div class="col-lg-6">
            <!-- Card: Últimas Vendas Realizadas -->
            <div class="so-card mb-4">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-dark m-0">
                        <i class="fas fa-clock-rotate-left text-primary me-2"></i> Últimas Vendas Realizadas
                    </h5>
                    <a href="<?= BASE_URL ?>/vendas/historico.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i> Ver Histórico
                    </a>
                </div>
                <div class="so-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 so-table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" width="22%">Cód / Hora</th>
                                    <th scope="col" width="38%">Cliente</th>
                                    <th scope="col" width="22%">Pagamento</th>
                                    <th scope="col" width="18%" class="text-end pe-3">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($ultimasVendas) > 0): ?>
                                    <?php foreach ($ultimasVendas as $v): ?>
                                    <tr>
                                        <td class="tabular-nums">
                                            <strong class="font-monospace text-primary tabular-nums">#<?= str_pad((string)$v['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                                            <br><small class="text-muted tabular-nums"><?= date('H:i', strtotime($v['data_venda'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-semibold d-block"><?= htmlspecialchars($v['cliente_nome'] ?? 'Consumidor Final', ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <td>
                                            <?= render_forma_pagamento($v['forma_pagamento'] ?? '') ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <strong class="text-dark fw-bold tabular-nums">R$ <?= number_format((float)$v['total'], 2, ',', '.') ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> Nenhuma venda registrada hoje.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Card: Ruptura de Estoque -->
            <div class="so-card">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-warning m-0">
                        <i class="fas fa-triangle-exclamation text-warning me-2"></i> Ruptura de Estoque
                    </h5>
                    <a href="<?= BASE_URL ?>/produtos/index.php?status=baixo_estoque" class="btn btn-sm btn-warning">
                        <i class="fas fa-sliders me-1"></i> Ajustar Estoque
                    </a>
                </div>
                <div class="so-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 so-table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" width="40%">Produto</th>
                                    <th scope="col" width="28%">Fornecedor</th>
                                    <th scope="col" width="16%" class="text-center">Qtd Atual</th>
                                    <th scope="col" width="16%" class="text-center">Qtd Mínima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($produtosEstoqueBaixo) > 0): ?>
                                    <?php foreach ($produtosEstoqueBaixo as $p): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small class="text-muted font-monospace tabular-nums">#<?= str_pad((string)$p['id'], 4, '0', STR_PAD_LEFT) ?></small>
                                        </td>
                                        <td>
                                            <span class="text-secondary"><?= htmlspecialchars($p['fornecedor_nome'] ?? 'Sem Fornecedor', ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <td class="text-center tabular-nums">
                                            <?php if ((int)$p['quantidade'] <= 0): ?>
                                                <span class="so-badge so-badge-danger tabular-nums"><i class="fas fa-ban me-1"></i> 0 un</span>
                                            <?php else: ?>
                                                <span class="so-badge so-badge-warning tabular-nums"><?= (int)$p['quantidade'] ?> un</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center text-muted fw-bold tabular-nums">
                                            <?= (int)$p['estoque_minimo'] ?> un
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-circle-check text-success me-1"></i> Todos os produtos estão com estoque ideal.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── COLUNA DIREITA ──────────────────────────────────────────────── -->
        <div class="col-lg-6">
            <!-- Card: Perto do Vencimento -->
            <div class="so-card mb-4">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-danger m-0">
                        <i class="fas fa-calendar-alt text-danger me-2"></i> Perto do Vencimento
                    </h5>
                    <a href="<?= BASE_URL ?>/lotes/index.php" class="btn btn-sm btn-danger">
                        <i class="fas fa-clock me-1"></i> Gerenciar Vencimentos
                    </a>
                </div>
                <div class="so-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 so-table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" width="45%">Produto / Lote</th>
                                    <th scope="col" width="25%">Validade</th>
                                    <th scope="col" width="30%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($produtosVencimento) > 0): ?>
                                    <?php foreach ($produtosVencimento as $p):
                                        $dias = (int)$p['dias_para_vencer'];

                                        if ($dias < 0) {
                                            $cls = 'so-badge-danger';
                                            $lbl = 'Vencido (' . abs($dias) . ($dias === -1 ? ' dia atrás)' : ' dias atrás)');
                                        } elseif ($dias === 0) {
                                            $cls = 'so-badge-danger';
                                            $lbl = 'Vence hoje';
                                        } elseif ($dias === 1) {
                                            $cls = 'so-badge-danger';
                                            $lbl = 'Vence amanhã (1 dia)';
                                        } elseif ($dias <= 15) {
                                            $cls = 'so-badge-danger';
                                            $lbl = "Faltam {$dias} dias";
                                        } else {
                                            $cls = 'so-badge-warning';
                                            $lbl = "Faltam {$dias} dias";
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">
                                                    <i class="fas fa-barcode text-muted me-1"></i>Lote: <?= htmlspecialchars($p['numero_lote'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                                <small class="text-muted">Estoque: <span class="tabular-nums fw-semibold"><?= (int)$p['quantidade'] ?></span> un.</small>
                                            </div>
                                        </td>
                                        <td class="tabular-nums">
                                            <span class="fw-semibold text-dark tabular-nums"><?= date('d/m/Y', strtotime($p['data_validade'])) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="so-badge <?= htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="fas fa-circle-check text-success me-1"></i> Nenhum produto com validade crítica.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Card: Atalhos & Ações Rápidas -->
            <div class="so-card">
                <div class="so-card-header d-flex align-items-center">
                    <h5 class="so-card-title text-dark m-0">
                        <i class="fas fa-bolt text-warning me-2"></i> Atalhos & Ações Rápidas
                    </h5>
                </div>
                <div class="so-card-body p-3">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-success w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <i class="fas fa-cash-register fa-lg"></i> Abrir PDV Caixa
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/produtos/index.php" class="btn btn-primary w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <i class="fas fa-boxes-stacked fa-lg"></i> Gestão de Estoque
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/compras/nova.php" class="btn btn-secondary w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <i class="fas fa-cart-flatbed fa-lg"></i> Nova Compra
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="<?= BASE_URL ?>/relatorios/analise.php" class="btn btn-dark w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <i class="fas fa-chart-line fa-lg"></i> Relatórios & DRE
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
