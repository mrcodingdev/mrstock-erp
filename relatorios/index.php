<?php
$pageTitle  = 'MrStock ERP - Emissão de Relatórios';
$activePage = 'relatorios';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Bloqueio de Acesso RBAC
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

require_once __DIR__ . '/../inc/header.php';
?>

    <div class="content-header">
        <h2 class="fw-bold text-dark"><i class="fas fa-file-pdf text-secondary me-2"></i> Emissão de Relatórios</h2>
        <p class="text-muted">Selecione o tipo de relatório para exportar ou gerar PDF.</p>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-3 text-center">
                        <h4 class="card-title fw-bold text-primary"><i class="fas fa-cogs me-2"></i> Gerador de Relatórios</h4>
                        <p class="text-muted mb-0">Selecione o tipo de relatório que deseja exportar para o seu TCC</p>
                    </div>
                    <div class="card-body p-4 bg-light">

                        <div class="mb-4 bg-white p-3 rounded shadow-sm border">
                            <h5 class="text-success"><i class="fas fa-boxes me-2"></i> Inventário Completo</h5>
                            <p class="text-muted" style="font-size:14px;">Lista detalhada com todos os produtos, quantidades, preços e fornecedores.</p>
                            <div class="d-flex gap-2 mt-3">
                                <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=completo" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> Gerar PDF</a>
                                <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=completo" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Baixar Excel</a>
                            </div>
                        </div>

                        <div class="mb-4 bg-white p-3 rounded shadow-sm border">
                            <h5 class="text-warning text-dark"><i class="fas fa-exclamation-circle me-2"></i> Alerta de Estoque Baixo</h5>
                            <p class="text-muted" style="font-size:14px;">Produtos com quantidade igual ou inferior ao estoque mínimo configurado.</p>
                            <div class="d-flex gap-2 mt-3">
                                <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=baixo" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> Gerar PDF</a>
                                <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=baixo" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Baixar Excel</a>
                            </div>
                        </div>

                        <div class="mb-4 bg-white p-3 rounded shadow-sm border">
                            <h5 class="text-danger"><i class="fas fa-calendar-times me-2"></i> Validades e Vencimentos</h5>
                            <p class="text-muted" style="font-size:14px;">Produtos vencidos ou a vencer nos próximos 30 dias.</p>
                            <div class="d-flex gap-2 mt-3">
                                <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=validade" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> Gerar PDF</a>
                                <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=validade" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Baixar Excel</a>
                            </div>
                        </div>

                        <div class="mb-2 bg-white p-3 rounded shadow-sm border">
                            <h5 class="text-primary"><i class="fas fa-shopping-bag me-2"></i> Relatório de Vendas</h5>
                            <p class="text-muted" style="font-size:14px;">Listagem de todas as saídas geradas pelo PDV.</p>
                            <div class="d-flex gap-2 mt-3">
                                <a href="<?= BASE_URL ?>/relatorios/pdf.php?tipo=vendas" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> Gerar PDF</a>
                                <a href="<?= BASE_URL ?>/relatorios/excel.php?tipo=vendas" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Baixar Excel</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
