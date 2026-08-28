<?php
/**
 * MrStock ERP - Visualização Detalhada de Ordem de Compra
 */
$pageTitle  = 'MrStock ERP - Detalhes da Compra';
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

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header("Location: " . BASE_URL . "/compras/index.php?msg=compra_nao_encontrada");
    exit;
}

// 1. Cabeçalho da Compra (Master)
$stmtC = $pdo->prepare("
    SELECT c.*, 
           f.nome AS fornecedor_nome, 
           f.cnpj AS fornecedor_cnpj, 
           f.telefone AS fornecedor_telefone, 
           f.email AS fornecedor_email, 
           f.contato AS fornecedor_contato,
           u.username 
    FROM compras c 
    LEFT JOIN fornecedores f ON c.fornecedor_id = f.id 
    LEFT JOIN usuarios u ON c.usuario_id = u.id 
    WHERE c.id = ?
");
$stmtC->execute([$id]);
$compra = $stmtC->fetch();

if (!$compra) {
    header("Location: " . BASE_URL . "/compras/index.php?msg=compra_nao_encontrada");
    exit;
}

// 2. Itens da Compra (Detail)
$stmtI = $pdo->prepare("
    SELECT ic.*, p.nome AS produto_nome, p.codigo_de_barra
    FROM itens_compra ic
    LEFT JOIN produtos p ON ic.produto_id = p.id
    WHERE ic.compra_id = ?
    ORDER BY ic.id ASC
");
$stmtI->execute([$id]);
$itens = $stmtI->fetchAll();

$badgeStatus = match($compra['status']) {
    'PAGA'      => 'bg-success',
    'PENDENTE'  => 'bg-warning text-dark',
    'CANCELADA' => 'bg-danger',
    default     => 'bg-secondary'
};

require_once __DIR__ . '/../inc/header.php';
?>

<style>
@media print {
    .sidebar-wrapper, .topbar, .btn-no-print, .alert {
        display: none !important;
    }
    .main-panel {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .content-body {
        margin: 0 !important;
        box-shadow: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>

<div class="content-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold text-dark m-0">
            <i class="fas fa-file-invoice text-primary me-2"></i>Ordem de Compra #<?= str_pad((string)$compra['id'], 5, '0', STR_PAD_LEFT) ?>
        </h2>
        <p class="text-muted m-0">Detalhamento de entrada de mercadorias e composição de custos.</p>
    </div>
    <div class="btn-no-print d-flex gap-2">
        <a href="<?= BASE_URL ?>/compras/index.php" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <button type="button" class="btn btn-primary fw-bold shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir Ordem
        </button>
    </div>
</div>

<div class="content-body">
    <!-- Card Master: Informações Gerais da Nota -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light py-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-secondary text-uppercase" style="font-size:12px; letter-spacing:0.5px;">
                    <i class="fas fa-info-circle me-1"></i> Dados da Fatura / Entrada
                </span>
                <span class="badge <?= $badgeStatus ?> px-3 py-2 fs-6">
                    <?= htmlspecialchars($compra['status']) ?>
                </span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Fornecedor</small>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fas fa-truck text-muted me-1"></i> <?= htmlspecialchars($compra['fornecedor_nome'] ?: 'Não Identificado') ?>
                    </h5>
                    <?php if (!empty($compra['fornecedor_cnpj'])): ?>
                        <small class="text-muted d-block">CNPJ: <?= htmlspecialchars($compra['fornecedor_cnpj']) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($compra['fornecedor_contato'])): ?>
                        <small class="text-muted d-block">Contato: <?= htmlspecialchars($compra['fornecedor_contato']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Nota Fiscal & Pagamento</small>
                    <span class="d-block text-dark fw-bold">NF-e: <?= htmlspecialchars($compra['numero_nota'] ?: 'Sem Número') ?></span>
                    <small class="text-muted d-block">Forma: <?= htmlspecialchars($compra['tipo_pagamento'] ?: 'A Combinar') ?></small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Data de Registro</small>
                    <span class="d-block text-dark fw-bold">
                        <i class="fas fa-calendar-alt text-muted me-1"></i> <?= date('d/m/Y H:i', strtotime($compra['data_compra'])) ?>
                    </span>
                    <small class="text-muted d-block">Operador: <?= htmlspecialchars($compra['username'] ?: 'Sistema') ?></small>
                </div>
                <div class="col-md-2 text-md-end">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Valor Total</small>
                    <h4 class="text-success fw-bold m-0">
                        R$ <?= number_format((float)$compra['valor_total'], 2, ',', '.') ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Detail: Itens da Ordem de Compra -->
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-dark text-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold"><i class="fas fa-boxes me-2"></i> Itens Inclusos na Compra</h6>
            <span class="badge bg-primary rounded-pill"><?= count($itens) ?> item(ns)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover so-table align-middle mb-0" style="font-size:14px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;" class="text-center">#</th>
                            <th style="width: 45%;">Produto / Descrição</th>
                            <th style="width: 15%;" class="text-center">Quantidade</th>
                            <th style="width: 15%;" class="text-end">Custo Unitário</th>
                            <th style="width: 20%;" class="text-end pe-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($itens)): ?>
                            <?php foreach ($itens as $idx => $it): ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $idx + 1 ?></td>
                                <td>
                                    <span class="fw-bold text-dark d-block">
                                        <?= htmlspecialchars($it['produto_nome'] ?: ('Produto #' . $it['produto_id'])) ?>
                                    </span>
                                    <?php if (!empty($it['codigo_de_barra'])): ?>
                                        <small class="text-muted"><i class="fas fa-barcode me-1"></i><?= htmlspecialchars($it['codigo_de_barra']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold">
                                    <?= (float)$it['quantidade'] == (int)$it['quantidade'] ? (int)$it['quantidade'] : number_format((float)$it['quantidade'], 3, ',', '.') ?>
                                </td>
                                <td class="text-end">
                                    R$ <?= number_format((float)$it['preco_unitario'], 2, ',', '.') ?>
                                </td>
                                <td class="text-end pe-4 fw-bold text-primary">
                                    R$ <?= number_format((float)$it['subtotal'], 2, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Nenhum item discriminado para esta ordem de compra.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold text-dark fs-6">TOTAL DA NOTA:</td>
                            <td class="text-end pe-4 fw-bold text-success fs-5">
                                R$ <?= number_format((float)$compra['valor_total'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light border-0 py-3 text-muted text-center" style="font-size:12px;">
            Documento de controle de estoque interno — MrStock ERP
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
