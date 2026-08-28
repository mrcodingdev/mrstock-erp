<?php
/**
 * MrStock ERP - Emissão de Cupom NFC-e (Simulação Acadêmica)
 * 100% Offline com QR Code vetorial SVG e dados dinâmicos da loja.
 */
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/barcode_helper.php';

// Determina URL de retorno com base no perfil do usuário ativo
$voltarUrl = (isset($_SESSION['user_perfil']) && $_SESSION['user_perfil'] === 'caixa')
    ? BASE_URL . '/vendas/pdv.php'
    : BASE_URL . '/vendas/historico.php';

$venda_id = $_GET['venda_id'] ?? $_GET['id'] ?? null;
if (!$venda_id || !is_numeric($venda_id)) {
    header("Location: " . $voltarUrl . "?erro=cupom_invalido");
    exit;
}

$stmtVenda = $pdo->prepare("SELECT v.*, c.nome as cliente_nome, c.cpf_cnpj as cliente_documento FROM vendas v LEFT JOIN clientes c ON v.cliente_id = c.id WHERE v.id = ?");
$stmtVenda->execute([$venda_id]);
$venda = $stmtVenda->fetch();

if (!$venda) {
    header("Location: " . $voltarUrl . "?erro=venda_nao_encontrada");
    exit;
}

$stmtItens = $pdo->prepare("SELECT vi.*, p.nome FROM vendas_itens vi JOIN produtos p ON vi.produto_id = p.id WHERE vi.venda_id = ?");
$stmtItens->execute([$venda_id]);
$itens = $stmtItens->fetchAll();

// Carrega parâmetros da loja dinamicamente da tabela configuracoes
$stmtConfig = $pdo->query("SELECT chave, valor FROM configuracoes");
$configMap = $stmtConfig->fetchAll(PDO::FETCH_KEY_PAIR);

$lojaNome     = $configMap['empresa_nome']     ?? 'Papelaria Real';
$lojaRazao    = $configMap['empresa_razao']    ?? 'Papelaria Real (Sueli & Osnir)';
$lojaCnpj     = $configMap['empresa_cnpj']     ?? '50.334.808/0001-38';
$lojaEndereco = $configMap['empresa_endereco'] ?? 'Rua da Papelaria, 123 - Centro - Sorocaba/SP';
$lojaTelefone = $configMap['empresa_telefone'] ?? '(15) 3232-0000';

// Busca chave fiscal real gravada no banco
$stmtCupom = $pdo->prepare("SELECT chave_acesso, data_emissao FROM cupons_fiscais WHERE venda_id = ?");
$stmtCupom->execute([$venda_id]);
$cupomFiscal = $stmtCupom->fetch();

if ($cupomFiscal && !empty($cupomFiscal['chave_acesso'])) {
    $rawKey = preg_replace('/[^0-9]/', '', $cupomFiscal['chave_acesso']);
    $chaveAcesso = trim(chunk_split($rawKey, 4, ' '));
} else {
    // Fallback didático padronizado
    $chaveAcesso = "3526 03" . preg_replace('/[^0-9]/', '', $lojaCnpj) . " 6500 1000 0120 4" . str_pad((string)$venda['id'], 3, '0', STR_PAD_LEFT) . " 1234 5678";
}

$qrPayload   = "https://www.fazenda.sp.gov.br/nfce/qrcode?p=" . preg_replace('/\s+/', '', $chaveAcesso) . "|2|1|1|" . md5($venda['id'] . $venda['total']);
$qrCodeSvg   = gerarQRCodeSVG($qrPayload, 120);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom Fiscal — NFC-e #<?= str_pad((string)$venda['id'], 6, '0', STR_PAD_LEFT) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css">
    <style>
        body { background:#555; display:flex; justify-content:center; padding:20px; font-family:'Courier New', Courier, monospace; }
        .cupom { width:320px; background:#fdfaf6; padding:15px; box-shadow:0 4px 8px rgba(0,0,0,0.5); font-size:13px; color:#000; }
        .header, .footer { text-align:center; }
        .header h3 { margin:0; font-size:16px; text-transform:uppercase; font-weight:bold; }
        .header p { margin:2px 0; font-size:11px; }
        .divider { border-top:1px dashed #000; margin:10px 0; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:2px 0; font-size:12px; }
        th { font-weight:normal; border-bottom:1px dashed #000; padding-bottom:5px; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .total { font-weight:bold; font-size:15px; }
        .qrcode-container { width:120px; height:120px; margin:12px auto; display:flex; align-items:center; justify-content:center; }
        @media print {
            body { background:none; padding:0; display:block; }
            .cupom { box-shadow:none; width:100%; max-width:80mm; margin:0 auto; padding:0; }
            .no-print { display:none !important; }
        }
        .btn-print { background:#284936; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:5px; font-family:Arial, sans-serif; font-weight:bold; margin-bottom:10px; display:block; width:100%; text-align:center; font-size:15px; }
        .btn-back { margin-top:6px; background:#475569; }
    </style>
</head>
<body>
    <div>
        <button class="no-print btn-print" onclick="window.print()"><i class="fas fa-print me-1"></i> Imprimir Cupom</button>
        <button class="no-print btn-print btn-back" onclick="window.location='<?= $voltarUrl ?>'"><i class="fas fa-arrow-left me-1"></i> Voltar</button>

        <div class="cupom">
            <div class="header">
                <h3><?= htmlspecialchars($lojaNome, ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($lojaRazao, ENT_QUOTES, 'UTF-8') ?></p>
                <p>CNPJ: <?= htmlspecialchars($lojaCnpj, ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars($lojaEndereco, ENT_QUOTES, 'UTF-8') ?></p>
                <p>Tel: <?= htmlspecialchars($lojaTelefone, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="divider"></div>
                <p><strong>DANFE NFC-e — Documento Auxiliar</strong></p>
                <p><strong>da Nota Fiscal de Consumidor Eletrônica</strong></p>
                <p style="font-size:10px;">Não permite aproveitamento de crédito ICMS</p>
            </div>

            <div class="divider"></div>

            <table>
                <thead>
                    <tr>
                        <th width="10%">QTD</th>
                        <th width="40%">DESCRIÇÃO</th>
                        <th width="25%" class="text-right">VL UN</th>
                        <th width="25%" class="text-right">VL TOT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $qtdTotal = 0; foreach ($itens as $i):
                        $qtdTotal += (int)$i['quantidade'];
                        $sub = (int)$i['quantidade'] * (float)$i['preco_unitario'];
                    ?>
                    <tr>
                        <td><?= str_pad((string)$i['quantidade'], 2, '0', STR_PAD_LEFT) ?></td>
                        <td><?= substr(htmlspecialchars($i['nome'], ENT_QUOTES, 'UTF-8'), 0, 15) ?>.</td>
                        <td class="text-right"><?= number_format((float)$i['preco_unitario'], 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format((float)$sub, 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="divider"></div>

            <table>
                <tr><td><strong>QTD. TOTAL DE ITENS</strong></td><td class="text-right"><strong><?= (int)$qtdTotal ?></strong></td></tr>
                <tr class="total"><td>VALOR TOTAL R$</td><td class="text-right"><?= number_format((float)$venda['total'], 2, ',', '.') ?></td></tr>
                <tr><td>FORMA PAGAMENTO</td><td class="text-right">VALOR PAGO R$</td></tr>
                <tr><td><?= htmlspecialchars($venda['forma_pagamento'], ENT_QUOTES, 'UTF-8') ?></td><td class="text-right"><?= number_format((float)$venda['total'], 2, ',', '.') ?></td></tr>
            </table>

            <div class="divider"></div>

            <div class="footer">
                <p><strong>Consulte pela Chave de Acesso em</strong></p>
                <p style="font-size:10px;">http://nfce.fazenda.sp.gov.br/consulta</p>
                <p style="font-size:10px;margin:4px 0;letter-spacing:0.5px;"><?= $chaveAcesso ?></p>
                
                <p class="mt-2" style="margin-top:6px;">
                    <strong>CONSUMIDOR:</strong> 
                    <?= !empty($venda['cliente_nome']) ? htmlspecialchars($venda['cliente_nome'], ENT_QUOTES, 'UTF-8') : 'NÃO IDENTIFICADO' ?>
                    <?php if (!empty($venda['cliente_documento'])): ?>
                        <br><small>Doc: <?= htmlspecialchars($venda['cliente_documento'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </p>
                
                <div class="qrcode-container">
                    <?= $qrCodeSvg ?>
                </div>

                <p><strong>Emissão:</strong> <?= date('d/m/Y H:i:s', strtotime($venda['data_venda'])) ?></p>
                <p><strong>Número:</strong> <?= str_pad((string)$venda['id'], 6, '0', STR_PAD_LEFT) ?> <strong>Série:</strong> 1</p>
                <div class="divider"></div>
                <p style="font-size:10px;"><strong>Ambiente de Homologação — Sem Valor Fiscal</strong></p>
            </div>
        </div>
    </div>
</body>
</html>