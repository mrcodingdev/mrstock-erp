<?php
/**
 * MrStock ERP - Controlador de Ações de Vendas
 */

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/venda_envio.php';

/**
 * Abate estoque de lotes sequencialmente via estratégia PEPS / FIFO.
 * Prioriza lotes com data de validade mais próxima com lock pessimista.
 */
if (!function_exists('abater_lotes_fifo')) {
    function abater_lotes_fifo(PDO $pdo, int $produto_id, int $qtd_solicitada): void {
        if ($qtd_solicitada <= 0 || $produto_id <= 0) {
            return;
        }

        $stmtLotes = $pdo->prepare("
            SELECT id, quantidade 
            FROM lotes 
            WHERE produto_id = ? AND quantidade > 0 AND data_validade >= CURDATE() 
            ORDER BY data_validade ASC, id ASC 
            FOR UPDATE
        ");
        $stmtLotes->execute([$produto_id]);
        $lotes = $stmtLotes->fetchAll(PDO::FETCH_ASSOC);

        $restante = $qtd_solicitada;
        $stmtUpdateLote = $pdo->prepare("UPDATE lotes SET quantidade = ? WHERE id = ?");

        foreach ($lotes as $lote) {
            if ($restante <= 0) {
                break;
            }

            $saldoLote = (int)$lote['quantidade'];
            if ($saldoLote <= $restante) {
                $stmtUpdateLote->execute([0, $lote['id']]);
                $restante -= $saldoLote;
            } else {
                $novoSaldo = $saldoLote - $restante;
                $stmtUpdateLote->execute([$novoSaldo, $lote['id']]);
                $restante = 0;
            }
        }
    }
}

$tipo = $_GET['tipo'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify(); // Proteção global CSRF para todas as ações POST

    if ($tipo === 'venda') {
        $acao = $_POST['acao'] ?? 'venda_completa';
        if (!in_array($acao, ['venda_completa', 'venda_rapida'], true)) {
            http_response_code(422);
            exit;
        }
        $chaveEnvio = $_POST['chave_envio'] ?? null;
        $confirmandoMargem = isset($_POST['confirmacao_margem']);
        $pdvJson = $acao !== 'venda_rapida' && str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        $responderPdv = static function (string $destino, array $dados, int $codigo = 200) use ($pdvJson): never {
            if ($pdvJson) {
                http_response_code($codigo);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode($dados, JSON_THROW_ON_ERROR);
            } else {
                header('Location: ' . $destino);
            }
            exit;
        };
        $origem = $acao === 'venda_rapida' ? '/dashboard.php' : '/vendas/pdv.php';
        $responderErro = static function (string $erro, string $detalheUrl = '', int $codigo = 422) use ($responderPdv, $origem): never {
            $responderPdv(BASE_URL . $origem . '?erro=' . $erro . $detalheUrl, ['status' => 'erro', 'erro' => $erro], $codigo);
        };
        if (!chave_envio_venda_autorizada($chaveEnvio, $acao)) {
            $responderErro('chave_envio_invalida');
        }

        $responderSeConcluida = static function () use ($pdo, $chaveEnvio, $acao, $confirmandoMargem, $responderPdv): void {
            $vendaExistente = localizar_venda_por_envio($pdo, $chaveEnvio);
            if (!$vendaExistente) {
                return;
            }
            concluir_chave_envio_venda($chaveEnvio, $acao);
            $destino = BASE_URL . '/vendas/cupom.php?venda_id=' . $vendaExistente;
            $status = $confirmandoMargem ? 'margem_ja_confirmada' : 'ok';
            $responderPdv($destino, ['status' => $status, 'redirect' => $destino], $confirmandoMargem ? 409 : 200);
        };
        $erroPdv = static function (string $erro, string $detalheUrl = '') use ($responderSeConcluida, $responderErro): never {
            // A regra pode ter falhado porque o primeiro envio já debitou o estoque.
            $responderSeConcluida();
            $responderErro($erro, $detalheUrl);
        };

        try {
            $responderSeConcluida();
            $pdo->beginTransaction();
            if ($acao === 'venda_rapida') {
                // ── Venda Rápida (Dashboard) ─────────────────────────────────
                $produto_id      = (int)($_POST['produto_id'] ?? 0);
                $quantidade      = (int)($_POST['quantidade'] ?? 0);
                $forma_pagamento = trim($_POST['forma_pagamento'] ?? 'DINHEIRO');

                if ($produto_id <= 0 || $quantidade <= 0) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    $erroPdv('dados_invalidos');
                }

                // Lock pessimista para garantir saldo e evitar race condition
                $stmtChk = $pdo->prepare("SELECT id, nome, preco_venda, quantidade, status FROM produtos WHERE id = ? FOR UPDATE");
                $stmtChk->execute([$produto_id]);
                $prodInfo = $stmtChk->fetch();

                if (!$prodInfo || $prodInfo['status'] !== 'ativo' || (int)$prodInfo['quantidade'] < $quantidade) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    $dispParam = $prodInfo ? (int)$prodInfo['quantidade'] : 0;
                    $nomParam  = $prodInfo ? $prodInfo['nome']            : 'Produto';
                    $erroPdv('estoque', '&produto=' . urlencode($nomParam) . '&disponivel=' . $dispParam);
                }

                // Preço oficial do banco de dados
                $precVenda = (float)$prodInfo['preco_venda'];
                $total     = $precVenda * $quantidade;

                // [G-01] Trava Sanitária CDC Art. 18: Validação pessimista de lotes válidos
                $stmtTemLotes = $pdo->prepare("SELECT COUNT(*) FROM lotes WHERE produto_id = ?");
                $stmtTemLotes->execute([$produto_id]);
                $temLotes = (int)$stmtTemLotes->fetchColumn() > 0;

                if ($temLotes) {
                    $stmtLoteVal = $pdo->prepare("SELECT COALESCE(SUM(quantidade), 0) FROM lotes WHERE produto_id = ? AND quantidade > 0 AND data_validade >= CURDATE() FOR UPDATE");
                    $stmtLoteVal->execute([$produto_id]);
                    $qtdLotesValidos = (int)$stmtLoteVal->fetchColumn();

                    if ($qtdLotesValidos < $quantidade) {
                        if ($pdo->inTransaction()) { $pdo->rollBack(); }
                        $nomParam = $prodInfo['nome'] ?? 'Produto';
                        registrar_log($pdo, 'BLOQUEIO_SANITARIO', "Venda bloqueada (CDC Art. 18): Produto #$produto_id ($nomParam) possui apenas $qtdLotesValidos un. em lotes válidos (solicitado: $quantidade)", 'lotes');
                        $erroPdv('lote_vencido', '&produto=' . urlencode($nomParam) . '&disponivel=' . $qtdLotesValidos . '&solicitado=' . $quantidade);
                    }
                }

                $stmtV = $pdo->prepare("INSERT INTO vendas (cliente_id, total, forma_pagamento, chave_envio) VALUES (NULL, ?, ?, ?)");
                $stmtV->execute([$total, $forma_pagamento, $chaveEnvio]);
                $venda_id = (int)$pdo->lastInsertId();

                $stmtI = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmtI->execute([$venda_id, $produto_id, $quantidade, $precVenda]);

                $stmtE = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
                $stmtE->execute([$quantidade, $produto_id]);

                // Abate de lotes via estratégia PEPS / FIFO
                abater_lotes_fifo($pdo, $produto_id, $quantidade);

                $stmtM = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'saida_venda', ?, ?)");
                $stmtM->execute([$produto_id, $quantidade, "Venda Rápida PDV #$venda_id"]);

            } else {
                // ── Venda Completa (PDV) ──────────────────────────────────────
                $forma_pagamento = trim($_POST['forma_pagamento'] ?? 'DINHEIRO');
                $cliente_id      = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
                $cartRaw         = $_POST['cart_data'] ?? '[]';
                $cart            = json_decode($cartRaw, true);
                $tokenEnviado    = $_POST['confirmacao_margem'] ?? null;
                $confirmacaoMargem = $_SESSION['pdv_confirmacoes_margem'][$chaveEnvio] ?? null;
                unset($_SESSION['pdv_confirmacoes_margem'][$chaveEnvio]);
                $margemConfirmada = false;

                if (empty($cart) || !is_array($cart)) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    $erroPdv('carrinho_vazio');
                }

                // [G-02] Prevenção de Deadlock: Ordenação determinística de produtos por ID
                usort($cart, fn($a, $b) => (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0));

                // ══ VALIDAÇÃO COM LOCK PESSIMISTA & CÁLCULO OFICIAL DE PREÇO ══
                $itensValidados = [];
                $quantidadesPorProduto = [];
                $valoresPorProduto = [];
                $totalCalculado = 0.0;
                $custoTotal     = 0.0;

                foreach ($cart as $item) {
                    $item_id  = (int)($item['id'] ?? 0);
                    $item_qtd = (int)($item['quantidade'] ?? 0);

                    $quantidadeAnterior = $quantidadesPorProduto[$item_id] ?? 0;
                    if ($item_id <= 0 || $item_qtd <= 0 || $item_qtd > PHP_INT_MAX - $quantidadeAnterior) {
                        if ($pdo->inTransaction()) { $pdo->rollBack(); }
                        $erroPdv('dados_invalidos');
                    }

                    // Duplicatas compartilham o saldo, mas preservam suas linhas na venda.
                    $quantidadeAcumulada = $quantidadeAnterior + $item_qtd;
                    $quantidadesPorProduto[$item_id] = $quantidadeAcumulada;

                    // Consulta com Lock Pessimista (FOR UPDATE)
                    $stmtChk = $pdo->prepare("SELECT id, nome, preco_venda, preco_compra, quantidade, status FROM produtos WHERE id = ? FOR UPDATE");
                    $stmtChk->execute([$item_id]);
                    $prodInfo = $stmtChk->fetch();

                    if (!$prodInfo || $prodInfo['status'] !== 'ativo' || (int)$prodInfo['quantidade'] < $quantidadeAcumulada) {
                        if ($pdo->inTransaction()) { $pdo->rollBack(); }
                        $disponivel = $prodInfo ? (int)$prodInfo['quantidade'] : 0;
                        $nomeProd   = $prodInfo ? $prodInfo['nome']            : ('Produto #' . $item_id);
                        $erroPdv('estoque', '&produto=' . urlencode($nomeProd) . '&disponivel=' . $disponivel . '&solicitado=' . $quantidadeAcumulada);
                    }

                    // [G-01] Trava Sanitária CDC Art. 18: Validação pessimista de lotes válidos
                    $stmtTemLotes = $pdo->prepare("SELECT COUNT(*) FROM lotes WHERE produto_id = ?");
                    $stmtTemLotes->execute([$item_id]);
                    $temLotes = (int)$stmtTemLotes->fetchColumn() > 0;

                    if ($temLotes) {
                        $stmtLoteVal = $pdo->prepare("SELECT COALESCE(SUM(quantidade), 0) FROM lotes WHERE produto_id = ? AND quantidade > 0 AND data_validade >= CURDATE() FOR UPDATE");
                        $stmtLoteVal->execute([$item_id]);
                        $qtdLotesValidos = (int)$stmtLoteVal->fetchColumn();

                        if ($qtdLotesValidos < $quantidadeAcumulada) {
                            if ($pdo->inTransaction()) { $pdo->rollBack(); }
                            $nomeProd = $prodInfo['nome'] ?? ('Produto #' . $item_id);
                            registrar_log($pdo, 'BLOQUEIO_SANITARIO', "Venda bloqueada (CDC Art. 18): Produto #$item_id ($nomeProd) possui apenas $qtdLotesValidos un. em lotes válidos (solicitado: $quantidadeAcumulada)", 'lotes');
                            $erroPdv('lote_vencido', '&produto=' . urlencode($nomeProd) . '&disponivel=' . $qtdLotesValidos . '&solicitado=' . $quantidadeAcumulada);
                        }
                    }

                    // Preço seguro vindo exclusivamente do banco de dados (ignora adulterações do client-side)
                    $precoOficial = (float)$prodInfo['preco_venda'];
                    $precoCusto   = (float)($prodInfo['preco_compra'] ?? 0);
                    $subtotalItem = $precoOficial * $item_qtd;
                    $totalCalculado += $subtotalItem;
                    $custoTotal     += ($precoCusto * $item_qtd);
                    $valoresPorProduto[$item_id] = [$precoOficial, $precoCusto];

                    $itensValidados[] = [
                        'id'             => $item_id,
                        'nome'           => $prodInfo['nome'],
                        'quantidade'     => $item_qtd,
                        'preco_unitario' => $precoOficial,
                        'subtotal'       => $subtotalItem
                    ];
                }

                // [G-03] Tratamento de desconto e Validações Server-Side de Margem
                $desconto      = max(0.0, (float)($_POST['desconto'] ?? 0));
                $totalLiquido  = max(0.0, $totalCalculado - $desconto);

                $pdvDescMax = (float)(get_app_config($pdo, 'pdv_desconto_maximo', '15.0'));
                $maxDescontoPermitido = round(($totalCalculado * $pdvDescMax) / 100.0, 2);

                if ($desconto > $maxDescontoPermitido) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    $erroPdv('desconto_excedido', '&max=' . $maxDescontoPermitido . '&tentado=' . $desconto);
                }

                $pdvTravaMargem = get_app_config($pdo, 'pdv_trava_margem', 'aviso');
                $totalLiquidoCentavos = (int)round($totalLiquido * 100);
                $custoTotalCentavos = (int)round($custoTotal * 100);
                $margemNegativa = $totalLiquidoCentavos < $custoTotalCentavos;
                if ($pdvTravaMargem === 'bloquear' && $margemNegativa) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    $erroPdv('margem_negativa');
                }

                if ($pdvTravaMargem === 'aviso' && $margemNegativa) {
                    // As chaves são inseridas em ordem de ID e as quantidades repetidas são somadas.
                    $assinatura = hash('sha256', serialize([
                        session_id(), $_SESSION['user_id'], $chaveEnvio, $quantidadesPorProduto, $valoresPorProduto,
                        $cliente_id, $desconto, $forma_pagamento, $pdvTravaMargem, $pdvDescMax,
                    ]));
                    $margemConfirmada = is_string($tokenEnviado) && is_array($confirmacaoMargem)
                        && $confirmacaoMargem['expira'] > time()
                        && hash_equals($confirmacaoMargem['token'], $tokenEnviado)
                        && hash_equals($confirmacaoMargem['assinatura'], $assinatura);
                    $resumoMargem = sprintf('Custo total: R$ %.2f. Total tentado: R$ %.2f.', $custoTotal, $totalLiquido);

                    if (!$margemConfirmada) {
                        $pdo->rollBack();
                        $responderSeConcluida();
                        if (!registrar_log($pdo, 'MARGEM_AVISO_PDV', $resumoMargem, 'vendas')) {
                            $responderErro('processamento', '', 500);
                        }
                        $token = bin2hex(random_bytes(32));
                        $_SESSION['pdv_confirmacoes_margem'][$chaveEnvio] = ['token' => $token, 'assinatura' => $assinatura, 'expira' => time() + 120];
                        $responderPdv(BASE_URL . '/vendas/pdv.php?erro=margem_aviso', ['status' => 'margem_aviso', 'token' => $token], 409);
                    }
                } elseif ($tokenEnviado !== null) {
                    $pdo->rollBack();
                    $erroPdv('confirmacao_desatualizada');
                }

                // ══ GRAVAÇÃO DA VENDA ═════════════════════════════════════════
                $stmtV = $pdo->prepare("INSERT INTO vendas (cliente_id, total, forma_pagamento, chave_envio) VALUES (?, ?, ?, ?)");
                $stmtV->execute([$cliente_id, $totalLiquido, $forma_pagamento, $chaveEnvio]);
                $venda_id = (int)$pdo->lastInsertId();

                // ══ GRAVAÇÃO DOS ITENS, DÉBITO DE ESTOQUE E AUDITORIA ═════════
                $stmtI = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmtE = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
                $stmtM = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'saida_venda', ?, ?)");

                foreach ($itensValidados as $itemVal) {
                    $stmtI->execute([$venda_id, $itemVal['id'], $itemVal['quantidade'], $itemVal['preco_unitario']]);
                    $stmtE->execute([$itemVal['quantidade'], $itemVal['id']]);
                    $stmtM->execute([$itemVal['id'], $itemVal['quantidade'], "Venda PDV #$venda_id"]);

                    // Abate de lotes via estratégia PEPS / FIFO
                    abater_lotes_fifo($pdo, $itemVal['id'], $itemVal['quantidade']);
                }
            }

            // ── Emissão de Cupom Fiscal Simulado (NFC-e) ──────────────────────
            $chave = "3526030000000000000065001" . str_pad((string)$venda_id, 9, '0', STR_PAD_LEFT) . "100000000";
            $chave = str_pad(substr($chave . rand(111, 999), 0, 44), 44, '0');

            $stmtF = $pdo->prepare("INSERT INTO cupons_fiscais (venda_id, chave_acesso) VALUES (?, ?)");
            $stmtF->execute([$venda_id, $chave]);

            if ($acao !== 'venda_rapida' && $margemConfirmada
                && !registrar_log($pdo, 'MARGEM_CONFIRMADA_PDV', "Venda #$venda_id. " . $resumoMargem, 'vendas')) {
                throw new RuntimeException('Falha na auditoria da confirmação de margem do PDV.');
            }
            $pdo->commit();
            concluir_chave_envio_venda($chaveEnvio, $acao);

            if ($acao === 'venda_rapida') {
                registrar_log($pdo, 'VENDA_RAPIDA', "Venda Rápida #$venda_id registrada. Total: R$ " . number_format($total, 2, ',', '.') . " ($forma_pagamento)", 'vendas');
            } else {
                registrar_log($pdo, 'VENDA_PDV', "Venda PDV #$venda_id finalizada. Total: R$ " . number_format($totalLiquido, 2, ',', '.') . " ($forma_pagamento)", 'vendas');
            }

            if ($acao === 'venda_rapida') {
                header('Location: ' . BASE_URL . '/dashboard.php?msg=sucesso');
                exit;
            }
            $redirect = BASE_URL . '/vendas/cupom.php?venda_id=' . $venda_id;
            $responderPdv($redirect, ['status' => 'ok', 'redirect' => $redirect]);

        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e instanceof PDOException && (int)($e->errorInfo[1] ?? 0) === 1062) {
                try {
                    $responderSeConcluida();
                } catch (PDOException $consultaFalhou) {
                    error_log('Falha ao consultar o resultado do envio da venda.');
                }
            }
            // A mensagem do banco pode incluir a chave do envio.
            error_log('Erro no processamento da venda MrStock. Código: ' . $e->getCode());
            $responderErro('processamento', '', 500);
        }
    }
}

header("Location: " . BASE_URL . "/vendas/pdv.php");
exit;
