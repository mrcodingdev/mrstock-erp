<?php
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Apenas Admin
if (($_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? '') !== 'admin') {
    $_SESSION['flash_error'] = "Acesso negado: módulo restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

$tipo = $_GET['tipo'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify(); // Proteção CSRF

    if ($tipo === 'compra') {
        $acao = $_POST['acao'] ?? '';

        if ($acao == 'salvar') {
            $fornecedor_id  = filter_var($_POST['fornecedor_id'], FILTER_VALIDATE_INT);
            $numero_nota    = trim($_POST['numero_nota'] ?? '');
            $status         = in_array($_POST['status'] ?? '', ['PAGA', 'PENDENTE']) ? $_POST['status'] : 'PENDENTE';
            $tipo_pagamento = trim($_POST['tipo_pagamento'] ?? '');
            $usuario_id     = $_SESSION['user_id'];
            
            $itens_json     = $_POST['itens_json'] ?? '[]';
            $itens          = json_decode($itens_json, true);

            if (!$fornecedor_id || !is_array($itens) || empty($itens)) {
                header("Location: " . BASE_URL . "/compras/index.php?msg=erro");
                exit;
            }

            // Recalcula total real dos itens e valida quantidades
            $valor_total = 0.0;
            $itensValidados = [];
            foreach ($itens as $item) {
                $p_id  = filter_var($item['produto_id'] ?? 0, FILTER_VALIDATE_INT);
                $qtd   = filter_var($item['quantidade'] ?? 0, FILTER_VALIDATE_INT);
                $preco = (float)($item['preco_unitario'] ?? 0);

                if ($p_id > 0 && $qtd > 0 && $preco >= 0) {
                    $subtot = $qtd * $preco;
                    $valor_total += $subtot;
                    $itensValidados[] = [
                        'produto_id'     => $p_id,
                        'quantidade'     => $qtd,
                        'preco_unitario' => $preco,
                        'subtotal'       => $subtot
                    ];
                }
            }

            if (empty($itensValidados)) {
                header("Location: " . BASE_URL . "/compras/index.php?msg=erro");
                exit;
            }

            try {
                $pdo->beginTransaction();

                // 1. Gravar Compra Master
                $stmtC = $pdo->prepare("INSERT INTO compras (fornecedor_id, usuario_id, numero_nota, valor_total, tipo_pagamento, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtC->execute([$fornecedor_id, $usuario_id, $numero_nota, $valor_total, $tipo_pagamento, $status]);
                $compra_id = $pdo->lastInsertId();

                // Preparar queries para o loop de alta performance
                $stmtItem = $pdo->prepare("INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmtProd = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ?, preco_compra = ? WHERE id = ?");
                $stmtMov  = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'entrada_compra', ?, ?)");

                // 2. Loop Itens: Salva item, sobe estoque, gera movimentação
                foreach ($itensValidados as $item) {
                    $p_id   = $item['produto_id'];
                    $qtd    = $item['quantidade'];
                    $preco  = $item['preco_unitario'];
                    $subtot = $item['subtotal'];

                    // A. Salva Item da Compra
                    $stmtItem->execute([$compra_id, $p_id, $qtd, $preco, $subtot]);

                    // B. Sobe o Estoque e atualiza preço de custo atual
                    $stmtProd->execute([$qtd, $preco, $p_id]);

                    // C. Registra Movimentação de Auditoria
                    $obs = "Entrada de Compra #" . $compra_id . " - Nota: " . ($numero_nota ?: 'S/N');
                    $stmtMov->execute([$p_id, $qtd, $obs]);
                }

                registrar_log($pdo, 'COMPRA_REGISTRADA', "Ordem de Compra #$compra_id registrada. Valor: R$ " . number_format($valor_total, 2, ',', '.'), 'compras');

                $pdo->commit();
                header("Location: " . BASE_URL . "/compras/index.php?msg=sucesso");
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Erro ao salvar compra: " . $e->getMessage());
                header("Location: " . BASE_URL . "/compras/index.php?msg=erro");
            }
            exit;
        }
    } 
    elseif ($tipo === 'status') {
        $id          = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $novo_status = trim($_POST['novo_status'] ?? '');
        
        if ($id && in_array($novo_status, ['PAGA', 'PENDENTE', 'CANCELADA'])) {
            try {
                $pdo->beginTransaction();

                // Busca status atual da compra
                $stmtCur = $pdo->prepare("SELECT status FROM compras WHERE id = ? FOR UPDATE");
                $stmtCur->execute([$id]);
                $statusAtual = $stmtCur->fetchColumn();

                if ($statusAtual && $statusAtual !== $novo_status) {
                    // Busca itens da compra
                    $stmtItens = $pdo->prepare("SELECT produto_id, quantidade FROM itens_compra WHERE compra_id = ?");
                    $stmtItens->execute([$id]);
                    $itensCompra = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

                    // Se a compra estava ativa e foi CANCELADA: estorna o estoque
                    if ($novo_status === 'CANCELADA' && in_array($statusAtual, ['PAGA', 'PENDENTE'])) {
                        $stmtEstorno = $pdo->prepare("UPDATE produtos SET quantidade = GREATEST(0, quantidade - ?) WHERE id = ?");
                        $stmtMovEst  = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'devolucao_fornecedor', ?, ?)");
                        foreach ($itensCompra as $ic) {
                            $stmtEstorno->execute([$ic['quantidade'], $ic['produto_id']]);
                            $stmtMovEst->execute([$ic['produto_id'], $ic['quantidade'], "Estorno de estoque por Cancelamento da Compra #{$id}"]);
                        }
                    }
                    // Se a compra estava CANCELADA e foi reativada para PAGA/PENDENTE: reinsere no estoque
                    elseif ($statusAtual === 'CANCELADA' && in_array($novo_status, ['PAGA', 'PENDENTE'])) {
                        $stmtReativ  = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
                        $stmtMovReat = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'entrada_compra', ?, ?)");
                        foreach ($itensCompra as $ic) {
                            $stmtReativ->execute([$ic['quantidade'], $ic['produto_id']]);
                            $stmtMovReat->execute([$ic['produto_id'], $ic['quantidade'], "Reativação de estoque da Compra #{$id} ({$novo_status})"]);
                        }
                    }

                    $stmt = $pdo->prepare("UPDATE compras SET status = ? WHERE id = ?");
                    $stmt->execute([$novo_status, $id]);
                }

                $pdo->commit();
                header("Location: " . BASE_URL . "/compras/index.php?msg=status_atualizado");
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Erro ao atualizar status de compra: " . $e->getMessage());
                header("Location: " . BASE_URL . "/compras/index.php?msg=erro");
            }
            exit;
        }
        header("Location: " . BASE_URL . "/compras/index.php?msg=status_atualizado");
        exit;
    }
}
header("Location: " . BASE_URL . "/compras/index.php");
exit;
