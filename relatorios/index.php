<?php
/**
 * MrStock ERP - Central de Relatórios Oficiais & Auditoria Empresarial
 * Design System SalesOps (v0) - 14 Zonas Anti-Slop
 */
$pageTitle  = 'Central de Relatórios';
$activePage = 'relatorios';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Bloqueio de Acesso RBAC (Exclusivo Administradores)
require_admin();

// ── 1. Total de Produtos Ativos & Volume Total de Peças em Estoque ─────────────
$stmtEstoque = $pdo->query("
    SELECT COUNT(*) AS total_produtos, 
           COALESCE(SUM(quantidade), 0) AS total_itens 
    FROM produtos 
    WHERE status = 'ativo'
");
$rowEstoque         = $stmtEstoque->fetch(PDO::FETCH_ASSOC);
$totalProdutos      = (int)($rowEstoque['total_produtos'] ?? 0);
$totalItensEstoque  = (int)($rowEstoque['total_itens'] ?? 0);

// ── 2. Patrimônio em Estoque (Preço de Venda, Custo e Lucro Projetado) ─────────
$stmtPatrimonio = $pdo->query("
    SELECT COALESCE(SUM(quantidade * preco_venda), 0) AS patrimonio_venda,
           COALESCE(SUM(quantidade * COALESCE(preco_compra, 0)), 0) AS patrimonio_custo
    FROM produtos 
    WHERE status = 'ativo'
");
$rowPatrimonio          = $stmtPatrimonio->fetch(PDO::FETCH_ASSOC);
$patrimonioTotalEstoque = (float)($rowPatrimonio['patrimonio_venda'] ?? 0);
$patrimonioCustoTotal   = (float)($rowPatrimonio['patrimonio_custo'] ?? 0);
$lucroProjetado         = $patrimonioTotalEstoque - $patrimonioCustoTotal;

// ── 3. Alertas de Reposição (Estoque Baixo / Ruptura) ─────────────────────────
$stmtBaixo = $pdo->query("
    SELECT COUNT(*) 
    FROM produtos 
    WHERE quantidade <= estoque_minimo AND status = 'ativo'
");
$produtosEstoqueBaixo = (int)($stmtBaixo->fetchColumn() ?: 0);

// ── 4. Alertas de Shelf-Life & Vencimentos (Janela de 30 Dias) ─────────────────
$stmtValidade = $pdo->query("
    SELECT COUNT(*) 
    FROM produtos 
    WHERE validade IS NOT NULL 
      AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
      AND status = 'ativo'
");
$produtosVencendo = (int)($stmtValidade->fetchColumn() ?: 0);

// ── 5. Métricas Comerciais de Vendas (Consolidado Geral PDV) ──────────────────
$stmtVendas = $pdo->query("
    SELECT COUNT(*) AS total_vendas,
           COALESCE(SUM(total), 0) AS faturamento_total
    FROM vendas
");
$rowVendas              = $stmtVendas->fetch(PDO::FETCH_ASSOC);
$totalVendasGeral       = (int)($rowVendas['total_vendas'] ?? 0);
$faturamentoVendasGeral = (float)($rowVendas['faturamento_total'] ?? 0);

require_once __DIR__ . '/../inc/header.php';
?>

<!-- ══ CABEÇALHO DA PÁGINA (TOPBAR SALESOPS) ═════════════════════════════════ -->
<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Central de Relatórios</h2>
        <p class="text-muted m-0">Emissão de relatórios oficiais em PDF A4 de alta fidelidade e exportação de planilhas em Excel.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/relatorios/analise.php" class="btn btn-secondary fw-semibold shadow-sm text-white">
            <i class="fa-solid fa-chart-line me-1"></i> Centro de Análise BI
        </a>
        <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-primary fw-bold shadow-sm text-white">
            <i class="fa-solid fa-cash-register me-1"></i> Abrir PDV
        </a>
    </div>
</div>

<div class="content-body">
    <!-- ══ 4 STAT CARDS NO TOPO (BENTO GRID SALESOPS) ═════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Catálogo Ativo -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Catálogo Ativo</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($totalProdutos, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">produtos</span></h3>
                        <small class="text-muted tabular-nums"><?= number_format($totalItensEstoque, 0, ',', '.') ?> unidades em estoque</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Patrimônio em Estoque -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Patrimônio em Estoque</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($patrimonioTotalEstoque, 2, ',', '.') ?></h3>
                        <small class="text-muted tabular-nums">Lucro projetado: R$ <?= number_format($lucroProjetado, 2, ',', '.') ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Alertas de Reposição -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Alertas de Reposição</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($produtosEstoqueBaixo, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted"><?= $produtosEstoqueBaixo === 1 ? 'crítico' : 'críticos' ?></span></h3>
                        <small class="text-danger fw-semibold">Ruptura / abaixo do mínimo</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Atenção de Validade -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Atenção de Validade</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($produtosVencendo, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted"><?= $produtosVencendo === 1 ? 'lote' : 'lotes' ?></span></h3>
                        <small class="text-warning fw-semibold">Vencidos ou em 30 dias</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--warning">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ 4 CENTRAIS DE RELATÓRIOS ESPECIALIZADAS (GRID 2x2) ═════════════════ -->
    <div class="row g-4">
        <!-- Card A: Inventário Completo & Saldos de Estoque -->
        <div class="col-12 col-lg-6">
            <div class="so-card h-100 d-flex flex-column mb-0">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-dark m-0">
                        <i class="fa-solid fa-warehouse text-primary me-2"></i> Inventário Completo & Saldos
                    </h5>
                    <span class="badge bg-primary text-white">Catálogo Integral</span>
                </div>
                <div class="so-card-body d-flex flex-column flex-grow-1 justify-content-between">
                    <div>
                        <p class="text-muted fs-sm mb-3">
                            Demonstrativo técnico completo de todos os produtos cadastrados, contendo quantidades físicas em estoque, estoque de segurança, custos de aquisição, preços de venda praticados e avaliação patrimonial consolidada.
                        </p>

                        <!-- Painel de Resumo Operacional em Tempo Real -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="row g-2 text-center">
                                <div class="col-4 border-end">
                                    <small class="text-muted d-block text-xs text-uppercase fw-bold">Produtos</small>
                                    <strong class="text-dark tabular-nums fs-6"><?= number_format($totalProdutos, 0, ',', '.') ?></strong>
                                </div>
                                <div class="col-4 border-end">
                                    <small class="text-muted d-block text-xs text-uppercase fw-bold">Volume Total</small>
                                    <strong class="text-dark tabular-nums fs-6"><?= number_format($totalItensEstoque, 0, ',', '.') ?> un</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block text-xs text-uppercase fw-bold">Patrimônio</small>
                                    <strong class="text-dark fw-bold tabular-nums fs-6">R$ <?= number_format($patrimonioTotalEstoque, 2, ',', '.') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação 100% Sólidos -->
                    <div class="d-flex gap-2 flex-wrap pt-2">
                        <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=completo" target="_blank" class="btn btn-primary fw-bold shadow-sm flex-fill text-white">
                            <i class="fa-solid fa-file-pdf me-1"></i> Visualizar / Imprimir PDF
                        </a>
                        <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=completo" class="btn btn-success fw-bold shadow-sm flex-fill text-white">
                            <i class="fa-solid fa-file-excel me-1"></i> Baixar Planilha Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card B: Ruptura & Estoque Crítico -->
        <div class="col-12 col-lg-6">
            <div class="so-card h-100 d-flex flex-column mb-0">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-dark m-0">
                        <i class="fa-solid fa-arrow-down-wide-short text-danger me-2"></i> Ruptura & Estoque Crítico
                    </h5>
                    <span class="badge bg-danger text-white">Reposição Imediata</span>
                </div>
                <div class="so-card-body d-flex flex-column flex-grow-1 justify-content-between">
                    <div>
                        <p class="text-muted fs-sm mb-3">
                            Listagem emergencial de itens com saldo físico igual ou inferior ao estoque mínimo estipulado. Essencial para emissão de ordens de compra assertivas e prevenção de desabastecimento na loja física.
                        </p>

                        <!-- Painel de Resumo Operacional em Tempo Real -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation text-danger fs-5"></i>
                                    <div>
                                        <strong class="text-dark d-block">Status de Abastecimento</strong>
                                        <small class="text-muted">Itens com quantidade menor ou igual ao estoque mínimo</small>
                                    </div>
                                </div>
                                <span class="badge bg-danger fs-6 px-3 py-2 tabular-nums fw-bold text-white"><?= $produtosEstoqueBaixo ?> <?= $produtosEstoqueBaixo === 1 ? 'item crítico' : 'itens críticos' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação 100% Sólidos -->
                    <div class="d-flex gap-2 flex-wrap pt-2">
                        <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=baixo" target="_blank" class="btn btn-primary fw-bold shadow-sm flex-fill text-white">
                            <i class="fa-solid fa-file-pdf me-1"></i> Visualizar / Imprimir PDF
                        </a>
                        <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=baixo" class="btn btn-success fw-bold shadow-sm flex-fill text-white">
                            <i class="fa-solid fa-file-excel me-1"></i> Baixar Planilha Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card C: Validades, Vencimentos & Shelf-Life -->
        <div class="col-12 col-lg-6">
            <div class="so-card h-100 d-flex flex-column mb-0">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-dark m-0">
                        <i class="fa-solid fa-calendar-xmark text-warning me-2"></i> Validades & Vencimentos (Shelf-Life)
                    </h5>
                    <span class="badge bg-warning text-dark">Janela de 30 Dias</span>
                </div>
                <div class="so-card-body d-flex flex-column flex-grow-1 justify-content-between">
                    <div>
                        <p class="text-muted fs-sm mb-3">
                            Mapeamento preventivo de itens perecíveis e materiais de papelaria técnica com prazo de validade expirado ou com expiração nos próximos 30 dias. Permite planejar queimas de estoque ou baixa contábil de perdas.
                        </p>

                        <!-- Painel de Resumo Operacional em Tempo Real -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-hourglass-half text-warning fs-5"></i>
                                    <div>
                                        <strong class="text-dark d-block">Monitoramento de Perecibilidade</strong>
                                        <small class="text-muted">Lotes com vencimento expirado ou nos próximos 30 dias</small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2 tabular-nums fw-bold"><?= $produtosVencendo ?> <?= $produtosVencendo === 1 ? 'produto' : 'produtos' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação 100% Sólidos -->
                    <div class="d-flex gap-2 flex-wrap pt-2">
                        <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=validade" target="_blank" class="btn btn-primary fw-bold shadow-sm flex-fill text-white">
                            <i class="fa-solid fa-file-pdf me-1"></i> Visualizar / Imprimir PDF
                        </a>
                        <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=validade" class="btn btn-success fw-bold shadow-sm flex-fill text-white">
                            <i class="fa-solid fa-file-excel me-1"></i> Baixar Planilha Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card D: Histórico Comercial de Vendas -->
        <div class="col-12 col-lg-6">
            <div class="so-card h-100 d-flex flex-column mb-0">
                <div class="so-card-header d-flex justify-content-between align-items-center">
                    <h5 class="so-card-title text-dark m-0">
                        <i class="fa-solid fa-cash-register text-success me-2"></i> Histórico Comercial de Vendas
                    </h5>
                    <span class="badge bg-success text-white">Frente de Caixa</span>
                </div>
                <div class="so-card-body d-flex flex-column flex-grow-1 justify-content-between">
                    <div>
                        <p class="text-muted fs-sm mb-3">
                            Extrato analítico de todas as transações emitidas na frente de caixa, registrando clientes associados, formas de pagamento utilizadas, volume de itens faturados e receita bruta consolidada.
                        </p>

                        <!-- Painel de Resumo Operacional em Tempo Real -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="row g-2 text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block text-xs text-uppercase fw-bold">Total de Vendas</small>
                                    <strong class="text-dark tabular-nums fs-6"><?= number_format($totalVendasGeral, 0, ',', '.') ?> transações</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block text-xs text-uppercase fw-bold">Faturamento Global</small>
                                    <strong class="text-dark fw-bold tabular-nums fs-6">R$ <?= number_format($faturamentoVendasGeral, 2, ',', '.') ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Botões Rápidos e Collapse de Filtro Personalizado -->
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=vendas" target="_blank" class="btn btn-primary fw-bold shadow-sm flex-fill text-white">
                                <i class="fa-solid fa-file-pdf me-1"></i> PDF Geral
                            </a>
                            <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=vendas" class="btn btn-success fw-bold shadow-sm flex-fill text-white">
                                <i class="fa-solid fa-file-excel me-1"></i> Excel Geral
                            </a>
                            <button class="btn btn-secondary fw-bold shadow-sm text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltroVendas" aria-expanded="false" aria-controls="collapseFiltroVendas">
                                <i class="fa-solid fa-calendar-days me-1"></i> Filtrar Datas <i class="fa-solid fa-chevron-down ms-1 text-xs"></i>
                            </button>
                        </div>

                        <!-- Collapse do Formulário de Filtro por Intervalo de Datas -->
                        <div class="collapse" id="collapseFiltroVendas">
                            <div class="p-3 bg-white rounded-3 border shadow-sm mt-2">
                                <h6 class="fw-bold text-dark mb-2 text-xs text-uppercase"><i class="fa-solid fa-filter text-primary me-1"></i> Filtro por Período Personalizado</h6>
                                <form method="GET" class="row g-2 align-items-end">
                                    <input type="hidden" name="tipo" value="vendas">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_data_inicio_vendas">Data Inicial</label>
                                        <input type="date" id="filtro_data_inicio_vendas" name="data_inicio" class="form-control form-control-sm tabular-nums" value="<?= date('Y-m-01') ?>">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_data_fim_vendas">Data Final</label>
                                        <input type="date" id="filtro_data_fim_vendas" name="data_fim" class="form-control form-control-sm tabular-nums" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-12 d-flex gap-2 mt-2">
                                        <button type="submit" formaction="<?= BASE_URL ?>/relatorios/pdf.php" formtarget="_blank" class="btn btn-sm btn-primary fw-bold flex-fill text-white">
                                            <i class="fa-solid fa-file-pdf me-1"></i> Gerar PDF do Período
                                        </button>
                                        <button type="submit" formaction="<?= BASE_URL ?>/relatorios/excel.php" class="btn btn-sm btn-success fw-bold flex-fill text-white">
                                            <i class="fa-solid fa-file-excel me-1"></i> Baixar Excel do Período
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

