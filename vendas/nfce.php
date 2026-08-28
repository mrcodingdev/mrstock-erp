<?php
$pageTitle  = 'MrStock ERP - Painel Fiscal (NFC-e)';
$activePage = 'fiscal';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

$stmt = $pdo->query("SELECT cf.*, v.total, c.nome as cliente_nome FROM cupons_fiscais cf JOIN vendas v ON cf.venda_id = v.id LEFT JOIN clientes c ON v.cliente_id = c.id ORDER BY cf.data_emissao DESC");
$cupons = $stmt->fetchAll();
$totalCupons = count($cupons);

require_once __DIR__ . '/../inc/header.php';
?>

    <div class="content-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-receipt text-danger me-2"></i>Controle Fiscal Simulado</h2>
            <p class="text-muted m-0">Consulte os XMLs e Cupons NFC-e gerados nas Vendas.</p>
        </div>
        <a href="<?= BASE_URL ?>/vendas/pdv.php" class="btn btn-danger fw-bold shadow-sm">
            <i class="fas fa-shopping-cart me-1"></i> Ir para o PDV
        </a>
    </div>

    <div class="content-body">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3">
            <i class="fas fa-check-circle me-2"></i> <strong>Venda registrada e NFC-e emitido com sucesso!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="alert alert-info border-0 shadow-sm mb-4">
            <i class="fas fa-info-circle me-2"></i> Ambiente fiscal em modo de <strong>Homologação (Simulação TCC)</strong>. As chaves de acesso são geradas aleatoriamente e não possuem valor legal na SEFAZ.
        </div>

        <div class="card overflow-hidden shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 so-table align-middle" style="font-size:14px;">
                        <thead class="table-light">
                            <tr>
                                <th>Data da Emissão</th>
                                <th class="text-center">ID Venda</th>
                                <th>Chave de Acesso (44 dígitos)</th>
                                <th>Cliente</th>
                                <th>Total Cupom</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($totalCupons > 0): ?>
                                <?php foreach ($cupons as $cf): ?>
                                <tr>
                                    <td><span class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y H:i', strtotime($cf['data_emissao'])) ?></span></td>
                                    <td class="text-center fw-bold text-secondary">#<?= str_pad($cf['venda_id'],5,'0',STR_PAD_LEFT) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border p-2" style="font-family:'Courier New',monospace;letter-spacing:1px;font-size:13px;">
                                            <?= preg_replace('/(\d{4})/', '$1 ', $cf['chave_acesso']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($cf['cliente_nome'] ?? 'Consumidor Final') ?></td>
                                    <td class="fw-bold text-success">R$ <?= number_format($cf['total'],2,',','.') ?></td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/vendas/cupom.php?venda_id=<?= $cf['venda_id'] ?>" target="_blank" class="btn btn-sm btn-danger" title="Ver Cupom">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-receipt fs-1 d-block mb-3 text-light"></i>Nenhum cupom fiscal emitido até o momento.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
