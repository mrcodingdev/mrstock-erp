<?php
/**
 * MrStock ERP - Emissão e Consulta de Cupom Fiscal NFC-e
 * Padrão Térmico 80mm/58mm — Simulação Acadêmica SEFAZ SP (Lei 12.741/2012)
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

$stmtItens = $pdo->prepare("SELECT vi.*, p.nome, p.codigo_de_barra FROM vendas_itens vi JOIN produtos p ON vi.produto_id = p.id WHERE vi.venda_id = ?");
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

// Cálculo de tributos simulados Lei 12.741/2012 (Fonte: IBPT)
$totalVenda    = (float)$venda['total'];
$tribFederal   = $totalVenda * 0.1345;
$tribEstadual  = $totalVenda * 0.1800;
$tribTotal     = $tribFederal + $tribEstadual;

// Protocolo de autorização e dados de emissão SEFAZ SP
$dataEmissaoRaw = $cupomFiscal['data_emissao'] ?? $venda['data_venda'];
$dataEmissaoFmt = date('d/m/Y H:i:s', strtotime($dataEmissaoRaw));
$protocoloAut   = '13526' . str_pad((string)$venda['id'], 10, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom Fiscal NFC-e #<?= str_pad((string)$venda['id'], 6, '0', STR_PAD_LEFT) ?> — <?= htmlspecialchars($lojaNome, ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Design System Assets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap.min.css?v=2.2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css?v=2.2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/inter.css?v=2.2.0">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" type="image/x-icon">

    <style>
        /* ==========================================================================
           MrStock ERP — Layout do Cupom Fiscal NFC-e (Térmica 80mm/58mm)
           SalesOps v0 Design System & WCAG 2.1 AA Compliance
           ========================================================================== */
        
        :root {
            --mr-bg-primary: #284936;
            --mr-bg-hover: #1e3628;
            --mr-secondary: #475569;
            --mr-secondary-hover: #334155;
            --mr-focus-ring: #2563eb;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            background-color: #334155;
            color: #1e293b;
            margin: 0;
            padding: 32px 16px 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Barra de Ações Fixa/Centralizada (Tela) ────────────────────────── */
        .toolbar-container {
            width: 100%;
            max-width: 360px;
            margin-bottom: 20px;
        }

        .toolbar-actions {
            display: flex;
            gap: 12px;
            width: 100%;
        }

        .btn-action-print {
            flex: 1.2;
            background-color: var(--mr-bg-primary);
            color: #ffffff !important;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            transition: background-color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-action-print:hover {
            background-color: var(--mr-bg-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
            color: #ffffff !important;
        }

        .btn-action-print:active {
            transform: translateY(0);
        }

        .btn-action-back {
            flex: 1;
            background-color: var(--mr-secondary);
            color: #ffffff !important;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            transition: background-color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-action-back:hover {
            background-color: var(--mr-secondary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
            color: #ffffff !important;
        }

        .btn-action-back:active {
            transform: translateY(0);
        }

        .btn-action-print:focus-visible,
        .btn-action-back:focus-visible {
            outline: 2px solid var(--mr-focus-ring) !important;
            outline-offset: 2px !important;
        }

        /* ── Papel Térmico Comercial (80mm) ─────────────────────────────────── */
        .thermal-receipt {
            width: 100%;
            max-width: 360px;
            background-color: #ffffff;
            color: #000000;
            padding: 24px 20px;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            font-size: 12px;
            line-height: 1.35;
            font-variant-numeric: tabular-nums;
        }

        .receipt-header,
        .receipt-footer {
            text-align: center;
        }

        .receipt-title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .receipt-subtitle {
            font-size: 11px;
            margin: 2px 0;
            color: #000000;
        }

        .receipt-divider {
            border-top: 1px dashed #000000;
            margin: 8px 0;
        }

        .receipt-divider-double {
            border-top: 1px dashed #000000;
            border-bottom: 1px dashed #000000;
            height: 3px;
            margin: 8px 0;
        }

        .receipt-danfe-badge {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
        }

        .receipt-disclaimer {
            font-size: 10px;
            margin: 2px 0;
        }

        /* ── Tabela Tabular de Itens ────────────────────────────────────────── */
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: 11px;
            font-variant-numeric: tabular-nums;
        }

        .receipt-table th,
        .receipt-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .receipt-table th {
            font-weight: bold;
            border-bottom: 1px dashed #000000;
            padding-bottom: 4px;
            text-align: left;
        }

        .receipt-table .col-num {
            width: 9%;
            text-align: left;
        }

        .receipt-table .col-desc {
            width: 43%;
            text-align: left;
            word-break: break-word;
        }

        .receipt-table .col-qtd {
            width: 12%;
            text-align: center;
        }

        .receipt-table .col-un {
            width: 18%;
            text-align: right;
        }

        .receipt-table .col-tot {
            width: 18%;
            text-align: right;
        }

        /* ── Totais e Pagamentos ────────────────────────────────────────────── */
        .receipt-totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 4px 0;
            font-variant-numeric: tabular-nums;
        }

        .receipt-totals-table td {
            padding: 2px 0;
        }

        .receipt-total-highlight {
            font-size: 14px;
            font-weight: bold;
        }

        .receipt-total-highlight td {
            padding: 4px 0;
        }

        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-left {
            text-align: left !important;
        }

        .fw-bold {
            font-weight: bold !important;
        }

        /* ── Box de Tributos (Lei 12.741/2012) ──────────────────────────────── */
        .tax-box {
            font-size: 10px;
            text-align: left;
            line-height: 1.35;
            margin: 4px 0;
        }

        /* ── QR Code Vetorial SVG ───────────────────────────────────────────── */
        .qrcode-container {
            width: 120px;
            height: 120px;
            margin: 10px auto 6px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qrcode-container svg {
            width: 120px !important;
            height: 120px !important;
            display: block;
        }

        /* ── Chave de Acesso 44 Dígitos ─────────────────────────────────────── */
        .access-key-box {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.6px;
            word-break: break-all;
            text-align: center;
            margin: 4px 0;
            line-height: 1.4;
        }

        .sefaz-info {
            font-size: 10px;
            line-height: 1.35;
            margin: 4px 0;
        }

        .consumer-info {
            font-size: 11px;
            text-align: left;
            margin: 4px 0;
            line-height: 1.35;
        }

        /* ══ REGRAS ESTRITAS DE IMPRESSÃO (@media print) ══════════════════════ */
        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                min-height: auto !important;
                width: 100% !important;
            }

            .no-print {
                display: none !important;
            }

            .thermal-receipt {
                width: 100% !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 4mm 2mm !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                font-size: 11px !important;
            }

            .receipt-title {
                font-size: 13px !important;
            }

            .receipt-table {
                font-size: 10px !important;
            }

            .receipt-totals-table {
                font-size: 10px !important;
            }

            .receipt-total-highlight {
                font-size: 13px !important;
            }

            .tax-box, .sefaz-info, .access-key-box, .receipt-disclaimer {
                font-size: 9px !important;
            }
        }
    </style>
</head>
<body>

    <!-- ══ BARRA DE AÇÕES (NO-PRINT) ══════════════════════════════════════════ -->
    <div class="toolbar-container no-print">
        <div class="toolbar-actions">
            <button type="button" class="btn-action-print" id="btnImprimirCupom" onclick="window.print()" title="Imprimir Cupom Térmico (Ctrl+P)">
                <i class="fas fa-print"></i>
                <span>Imprimir Cupom</span>
            </button>
            <a href="<?= htmlspecialchars($voltarUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-action-back" id="btnVoltarSistema" title="Retornar ao sistema (ESC)">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
    </div>

    <!-- ══ CONTAINER DO CUPOM FISCAL TÉRMICO (80MM / 58MM) ═════════════════════ -->
    <main class="thermal-receipt" id="cupomFiscalPaper">
        
        <!-- 1. Cabeçalho do Estabelecimento -->
        <header class="receipt-header">
            <h1 class="receipt-title"><?= htmlspecialchars($lojaNome, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="receipt-subtitle"><?= htmlspecialchars($lojaRazao, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="receipt-subtitle">CNPJ: <?= htmlspecialchars($lojaCnpj, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="receipt-subtitle"><?= htmlspecialchars($lojaEndereco, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="receipt-subtitle">Tel: <?= htmlspecialchars($lojaTelefone, ENT_QUOTES, 'UTF-8') ?></p>
            
            <div class="receipt-divider"></div>
            
            <div class="receipt-danfe-badge">DANFE NFC-e — Documento Auxiliar</div>
            <div class="receipt-subtitle">da Nota Fiscal de Consumidor Eletrônica</div>
            <div class="receipt-disclaimer">Não permite aproveitamento de crédito ICMS</div>
        </header>

        <div class="receipt-divider"></div>

        <!-- 2. Tabela Tabular de Itens Vendidos -->
        <table class="receipt-table" aria-label="Itens do Cupom Fiscal">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th class="col-desc">DESCRIÇÃO</th>
                    <th class="col-qtd">QTD</th>
                    <th class="col-un">VL UN</th>
                    <th class="col-tot">VL TOT</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $qtdTotal = 0; 
                $itemIndex = 1;
                foreach ($itens as $i): 
                    $qtdItem = (int)$i['quantidade'];
                    $precoUn = (float)$i['preco_unitario'];
                    $subItem = $qtdItem * $precoUn;
                    $qtdTotal += $qtdItem;
                ?>
                <tr>
                    <td class="col-num tabular-nums"><?= str_pad((string)$itemIndex, 3, '0', STR_PAD_LEFT) ?></td>
                    <td class="col-desc"><?= htmlspecialchars($i['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="col-qtd tabular-nums"><?= $qtdItem ?></td>
                    <td class="col-un tabular-nums"><?= number_format($precoUn, 2, ',', '.') ?></td>
                    <td class="col-tot tabular-nums"><?= number_format($subItem, 2, ',', '.') ?></td>
                </tr>
                <?php 
                    $itemIndex++;
                endforeach; 
                ?>
            </tbody>
        </table>

        <div class="receipt-divider"></div>

        <!-- 3. Totalizadores e Pagamentos -->
        <table class="receipt-totals-table" aria-label="Totalizadores da Venda">
            <tr>
                <td class="text-left fw-bold">QTD. TOTAL DE ITENS</td>
                <td class="text-right fw-bold tabular-nums"><?= (int)$qtdTotal ?></td>
            </tr>
            <tr class="receipt-total-highlight">
                <td class="text-left">VALOR TOTAL R$</td>
                <td class="text-right tabular-nums"><?= number_format($totalVenda, 2, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-left">FORMA DE PAGAMENTO</td>
                <td class="text-right">VALOR PAGO R$</td>
            </tr>
            <tr>
                <td class="text-left fw-bold"><?= htmlspecialchars($venda['forma_pagamento'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right fw-bold tabular-nums"><?= number_format($totalVenda, 2, ',', '.') ?></td>
            </tr>
        </table>

        <div class="receipt-divider"></div>

        <!-- 4. Informação dos Tributos Totais Incidentes (Lei 12.741/2012) -->
        <section class="tax-box">
            <div><strong>Tributos Totais Incidentes (Lei 12.741/2012):</strong> R$ <?= number_format($tribTotal, 2, ',', '.') ?> (31,45%)</div>
            <div>Federal: R$ <?= number_format($tribFederal, 2, ',', '.') ?> | Estadual: R$ <?= number_format($tribEstadual, 2, ',', '.') ?></div>
            <div style="font-size:9px;">Fonte: IBPT / Empresômetro</div>
        </section>

        <div class="receipt-divider"></div>

        <!-- 5. Dados Fiscais da Emissão e SEFAZ SP -->
        <footer class="receipt-footer">
            <div class="sefaz-info">
                <strong>EMISSÃO EM AMBIENTE DE HOMOLOGAÇÃO</strong><br>
                <strong>SEM VALOR FISCAL</strong>
            </div>

            <div class="sefaz-info">
                <strong>NFC-e Nº:</strong> <?= str_pad((string)$venda['id'], 6, '0', STR_PAD_LEFT) ?> &nbsp;|&nbsp; <strong>Série:</strong> 001<br>
                <strong>Emissão:</strong> <?= $dataEmissaoFmt ?><br>
                <strong>Protocolo de Autorização:</strong> <?= $protocoloAut ?><br>
                <strong>Data de Autorização:</strong> <?= $dataEmissaoFmt ?>
            </div>

            <div class="receipt-divider"></div>

            <!-- 6. Identificação do Consumidor -->
            <div class="consumer-info">
                <strong>CONSUMIDOR:</strong> 
                <?= !empty($venda['cliente_nome']) ? htmlspecialchars($venda['cliente_nome'], ENT_QUOTES, 'UTF-8') : 'NÃO IDENTIFICADO' ?>
                <?php if (!empty($venda['cliente_documento'])): ?>
                    <br><strong>CPF/CNPJ:</strong> <?= htmlspecialchars($venda['cliente_documento'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>

            <div class="receipt-divider"></div>

            <!-- 7. Chave de Acesso de 44 Dígitos -->
            <div class="sefaz-info">
                <strong>Consulte pela Chave de Acesso em:</strong><br>
                <span style="font-size:9px;">https://www.nfce.fazenda.sp.gov.br/consulta</span>
            </div>

            <div class="access-key-box tabular-nums" title="Chave de Acesso NFC-e">
                <?= htmlspecialchars($chaveAcesso, ENT_QUOTES, 'UTF-8') ?>
            </div>

            <!-- 8. QR Code Vetorial SVG SEFAZ SP -->
            <div class="qrcode-container" role="img" aria-label="QR Code para consulta na SEFAZ SP">
                <?= $qrCodeSvg ?>
            </div>
            <div style="font-size:9px; margin-bottom: 6px;">Consulta via leitor de QR Code</div>

            <div class="receipt-divider"></div>

            <div style="font-size:9px; color:#333333;">
                <strong>MrStock ERP</strong> — Sistema de Gestão Comercial Integrado<br>
                Simulação Acadêmica TCC
            </div>
        </footer>
    </main>

    <!-- ══ SCRIPT DE ATALHOS DE TECLADO E ACESSIBILIDADE ═══════════════════════ -->
    <script>
        document.addEventListener('keydown', function(e) {
            // Tecla ESC: Retorna ao PDV ou Histórico
            if (e.key === 'Escape') {
                e.preventDefault();
                window.location.href = <?= json_encode($voltarUrl) ?>;
            }
        });
    </script>
</body>
</html>