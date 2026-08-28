<?php
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Bloqueio de Acesso RBAC
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

$tipo = $_GET['tipo'] ?? 'completo';

if ($tipo == 'baixo') {
    $titulo    = "Relatório de Produtos com Estoque Baixo";
    $descricao = "Listagem de itens que atingiram ou estão abaixo do estoque mínimo cadastrado.";
    $stmt = $pdo->query("SELECT p.*, f.nome as fornecedor_nome FROM produtos p LEFT JOIN fornecedores f ON p.fornecedor_id = f.id WHERE p.quantidade <= p.estoque_minimo AND p.status = 'ativo' ORDER BY p.quantidade ASC");
} elseif ($tipo == 'validade') {
    $titulo    = "Relatório de Produtos: Vencimentos";
    $descricao = "Listagem de itens vencidos ou a vencer em até 30 dias.";
    $stmt = $pdo->query("SELECT p.*, f.nome as fornecedor_nome FROM produtos p LEFT JOIN fornecedores f ON p.fornecedor_id = f.id WHERE p.validade IS NOT NULL AND p.validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND p.status = 'ativo' ORDER BY p.validade ASC");
} elseif ($tipo == 'vendas') {
    $titulo    = "Relatório de Vendas (Histórico)";
    $descricao = "Listagem de todas as saídas geradas pelo PDV.";
    $stmt = $pdo->query("SELECT v.*, c.nome as cliente_nome FROM vendas v LEFT JOIN clientes c ON v.cliente_id = c.id ORDER BY v.data_venda DESC");
} else {
    $titulo    = "Relatório de Inventário Completo";
    $descricao = "Listagem completa do estoque atual com preço de custo e venda.";
    $stmt = $pdo->query("SELECT p.*, f.nome as fornecedor_nome FROM produtos p LEFT JOIN fornecedores f ON p.fornecedor_id = f.id WHERE p.status = 'ativo' ORDER BY p.nome ASC");
}
$dados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css">
    <style>
        body { background:#525659; font-family:'Arial',sans-serif; display:flex; justify-content:center; padding:20px; }
        .a4-page { width:21cm; min-height:29.7cm; padding:2cm; margin:0 auto; border:1px #D3D3D3 solid; border-radius:5px; background:white; box-shadow:0 0 5px rgba(0,0,0,0.1); }
        .header { text-align:center; margin-bottom:20px; border-bottom:2px solid #333; padding-bottom:15px; }
        .header h2 { margin:0; font-weight:bold; color:#333; }
        .footer { margin-top:30px; border-top:1px solid #ccc; padding-top:10px; font-size:11px; color:#777; text-align:center; }
        table { font-size:12px; }
        th { background-color:#f2f2f2 !important; color:#333 !important; }
        @media print {
            body { background:white; padding:0; }
            .a4-page { width:auto; min-height:auto; margin:0; padding:0.5cm; border:none; box-shadow:none; }
            .no-print { display:none !important; }
        }
    </style>
</head>
<body>
    <div class="position-fixed top-0 start-0 m-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow"><i class="fas fa-print"></i> Imprimir PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg shadow ms-2"><i class="fas fa-times"></i> Fechar</button>
        <div class="dropdown mt-3">
            <button class="btn btn-success dropdown-toggle shadow" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/relatorios/excel.php?tipo=completo">Inventário Padrão</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/relatorios/excel.php?tipo=vendas">Relatório de Vendas</a></li>
            </ul>
        </div>
    </div>

    <div class="a4-page">
        <div class="header">
            <h2><i class="fas fa-cubes"></i> MrStock ERP</h2>
            <h4 class="mt-2 text-primary"><?= $titulo ?></h4>
            <p class="text-muted mb-0"><?= $descricao ?></p>
            <small>Gerado em: <?= date('d/m/Y H:i:s') ?></small>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <?php if ($tipo == 'vendas'): ?>
                        <th class="text-center">ID Venda</th><th>Data</th><th>Cliente</th><th>Pagamento</th><th class="text-end">Total (R$)</th>
                    <?php else: ?>
                        <th>Cód</th><th>Produto</th><th>Fornecedor</th><th class="text-center">Qtd</th>
                        <?php if ($tipo == 'baixo')   echo '<th class="text-center">Mínimo</th>'; ?>
                        <?php if ($tipo == 'validade') echo '<th>Validade</th>'; ?>
                        <?php if ($tipo == 'completo') echo '<th class="text-end">Venda (R$)</th>'; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dados) > 0): ?>
                    <?php foreach ($dados as $d): ?>
                    <tr>
                        <?php if ($tipo == 'vendas'): ?>
                            <td class="text-center">#<?= str_pad($d['id'],5,'0',STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($d['data_venda'])) ?></td>
                            <td><?= htmlspecialchars($d['cliente_nome'] ?? 'Consumidor Final') ?></td>
                            <td><?= htmlspecialchars($d['forma_pagamento']) ?></td>
                            <td class="text-end fw-bold text-success"><?= number_format($d['total'],2,',','.') ?></td>
                        <?php else: ?>
                            <td><?= str_pad($d['id'],3,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($d['nome']) ?></td>
                            <td><?= htmlspecialchars($d['fornecedor_nome'] ?? '--') ?></td>
                            <td class="text-center fw-bold"><?= $d['quantidade'] ?></td>
                            <?php if ($tipo == 'baixo'):   ?><td class="text-center text-danger"><?= $d['estoque_minimo'] ?></td><?php endif; ?>
                            <?php if ($tipo == 'validade'):?><td class="text-danger fw-bold"><?= date('d/m/Y', strtotime($d['validade'])) ?></td><?php endif; ?>
                            <?php if ($tipo == 'completo'):?><td class="text-end"><?= number_format($d['preco_venda'],2,',','.') ?></td><?php endif; ?>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">Nenhum registro encontrado para este relatório.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer">
            MrStock ERP — Exclusivo para TCC &copy; <?= date('Y') ?>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
</body>
</html>
