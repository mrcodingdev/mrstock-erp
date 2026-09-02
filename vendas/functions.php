<?php
/**
 * MrStock ERP - Controlador de Ações de Vendas
 */

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

$tipo = $_GET['tipo'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify(); // Proteção global CSRF para todas as ações POST

    if ($tipo === 'venda') {
        $acao = $_POST['acao'] ?? 'venda_completa';

        $pdo->beginTransaction();
        try {
            if ($acao === 'venda_rapida') {
                // ── Venda Rápida (Dashboard) ─────────────────────────────────
                $produto_id      = (int)($_POST['produto_id'] ?? 0);
                $quantidade      = (int)($_POST['quantidade'] ?? 0);
                $forma_pagamento = trim($_POST['forma_pagamento'] ?? 'DINHEIRO');

                if ($produto_id <= 0 || $quantidade <= 0) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    header("Location: " . BASE_URL . "/dashboard.php?erro=dados_invalidos");
                    exit;
                }

                // Lock pessimista para garantir saldo e evitar race condition
                $stmtChk = $pdo->prepare("SELECT id, nome, preco_venda, quantidade, status FROM produtos WHERE id = ? FOR UPDATE");
                $stmtChk->execute([$produto_id]);
                $prodInfo = $stmtChk->fetch();

                if (!$prodInfo || $prodInfo['status'] !== 'ativo' || (int)$prodInfo['quantidade'] < $quantidade) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    $dispParam = $prodInfo ? (int)$prodInfo['quantidade'] : 0;
                    $nomParam  = $prodInfo ? $prodInfo['nome']            : 'Produto';
                    header("Location: " . BASE_URL . "/dashboard.php?erro=estoque&produto=" . urlencode($nomParam) . "&disponivel=" . $dispParam);
                    exit;
                }

                // Preço oficial do banco de dados
                $precVenda = (float)$prodInfo['preco_venda'];
                $total     = $precVenda * $quantidade;

                $stmtV = $pdo->prepare("INSERT INTO vendas (cliente_id, total, forma_pagamento) VALUES (NULL, ?, ?)");
                $stmtV->execute([$total, $forma_pagamento]);
                $venda_id = (int)$pdo->lastInsertId();

                $stmtI = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmtI->execute([$venda_id, $produto_id, $quantidade, $precVenda]);

                $stmtE = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
                $stmtE->execute([$quantidade, $produto_id]);

                $stmtM = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'saida_venda', ?, ?)");
                $stmtM->execute([$produto_id, $quantidade, "Venda Rápida PDV #$venda_id"]);

            } else {
                // ── Venda Completa (PDV) ──────────────────────────────────────
                $forma_pagamento = trim($_POST['forma_pagamento'] ?? 'DINHEIRO');
                $cliente_id      = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
                $cartRaw         = $_POST['cart_data'] ?? '[]';
                $cart            = json_decode($cartRaw, true);

                if (empty($cart) || !is_array($cart)) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    header("Location: " . BASE_URL . "/vendas/pdv.php?erro=carrinho_vazio");
                    exit;
                }

                // ══ VALIDAÇÃO COM LOCK PESSIMISTA & CÁLCULO OFICIAL DE PREÇO ══
                $itensValidados = [];
                $totalCalculado = 0.0;

                foreach ($cart as $item) {
                    $item_id  = (int)($item['id'] ?? 0);
                    $item_qtd = (int)($item['quantidade'] ?? 0);

                    if ($item_id <= 0 || $item_qtd <= 0) {
                        if ($pdo->inTransaction()) { $pdo->rollBack(); }
                        header("Location: " . BASE_URL . "/vendas/pdv.php?erro=dados_invalidos");
                        exit;
                    }

                    // Consulta com Lock Pessimista (FOR UPDATE)
                    $stmtChk = $pdo->prepare("SELECT id, nome, preco_venda, quantidade, status FROM produtos WHERE id = ? FOR UPDATE");
                    $stmtChk->execute([$item_id]);
                    $prodInfo = $stmtChk->fetch();

                    if (!$prodInfo || $prodInfo['status'] !== 'ativo' || (int)$prodInfo['quantidade'] < $item_qtd) {
                        if ($pdo->inTransaction()) { $pdo->rollBack(); }
                        $disponivel = $prodInfo ? (int)$prodInfo['quantidade'] : 0;
                        $nomeProd   = $prodInfo ? $prodInfo['nome']            : ('Produto #' . $item_id);
                        header("Location: " . BASE_URL . "/vendas/pdv.php?erro=estoque&produto=" . urlencode($nomeProd) . "&disponivel=" . $disponivel . "&solicitado=" . $item_qtd);
                        exit;
                    }

                    // Preço seguro vindo exclusivamente do banco de dados (ignora adulterações do client-side)
                    $precoOficial = (float)$prodInfo['preco_venda'];
                    $subtotalItem = $precoOficial * $item_qtd;
                    $totalCalculado += $subtotalItem;

                    $itensValidados[] = [
                        'id'             => $item_id,
                        'nome'           => $prodInfo['nome'],
                        'quantidade'     => $item_qtd,
                        'preco_unitario' => $precoOficial,
                        'subtotal'       => $subtotalItem
                    ];
                }

                // Tratamento de desconto opcional
                $desconto      = max(0.0, (float)($_POST['desconto'] ?? 0));
                $totalLiquido  = max(0.0, $totalCalculado - $desconto);

                // ══ GRAVAÇÃO DA VENDA ═════════════════════════════════════════
                $stmtV = $pdo->prepare("INSERT INTO vendas (cliente_id, total, forma_pagamento) VALUES (?, ?, ?)");
                $stmtV->execute([$cliente_id, $totalLiquido, $forma_pagamento]);
                $venda_id = (int)$pdo->lastInsertId();

                // ══ GRAVAÇÃO DOS ITENS, DÉBITO DE ESTOQUE E AUDITORIA ═════════
                $stmtI = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmtE = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
                $stmtM = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'saida_venda', ?, ?)");

                foreach ($itensValidados as $itemVal) {
                    $stmtI->execute([$venda_id, $itemVal['id'], $itemVal['quantidade'], $itemVal['preco_unitario']]);
                    $stmtE->execute([$itemVal['quantidade'], $itemVal['id']]);
                    $stmtM->execute([$itemVal['id'], $itemVal['quantidade'], "Venda PDV #$venda_id"]);
                }
            }

            // ── Emissão de Cupom Fiscal Simulado (NFC-e) ──────────────────────
            $chave = "3526030000000000000065001" . str_pad((string)$venda_id, 9, '0', STR_PAD_LEFT) . "100000000";
            $chave = str_pad(substr($chave . rand(111, 999), 0, 44), 44, '0');

            $stmtF = $pdo->prepare("INSERT INTO cupons_fiscais (venda_id, chave_acesso) VALUES (?, ?)");
            $stmtF->execute([$venda_id, $chave]);

            $pdo->commit();

            if ($acao === 'venda_rapida') {
                registrar_log($pdo, 'VENDA_RAPIDA', "Venda Rápida #$venda_id registrada. Total: R$ " . number_format($total, 2, ',', '.') . " ($forma_pagamento)", 'vendas');
            } else {
                registrar_log($pdo, 'VENDA_PDV', "Venda PDV #$venda_id finalizada. Total: R$ " . number_format($totalLiquido, 2, ',', '.') . " ($forma_pagamento)", 'vendas');
            }

            $redirect = ($acao === 'venda_rapida')
                ? BASE_URL . "/dashboard.php?msg=sucesso"
                : BASE_URL . "/vendas/nfce.php?msg=sucesso&venda_id=" . $venda_id;

            header("Location: $redirect");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $origemErro = ($acao === 'venda_rapida')
                ? BASE_URL . "/dashboard.php?erro=processamento"
                : BASE_URL . "/vendas/pdv.php?erro=processamento";
            header("Location: $origemErro");
            exit;
        }
    }
}

header("Location: " . BASE_URL . "/vendas/pdv.php");
exit;
