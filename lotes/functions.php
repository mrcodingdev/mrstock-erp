<?php
/**
 * MrStock ERP - Controlador de Ações de Lotes e Validades
 * Gestão de Lotes, Validades e Rastreabilidade de Compras (PEPS / FIFO)
 */

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Exclusivo para Administradores
require_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify(); // Proteção global CSRF

    $tipo = $_GET['tipo'] ?? 'lote';
    $acao = $_POST['acao'] ?? '';

    if ($tipo === 'lote') {
        if ($acao === 'salvar') {
            $id              = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $produto_id      = filter_var($_POST['produto_id'] ?? null, FILTER_VALIDATE_INT);
            $numero_lote     = trim($_POST['numero_lote'] ?? '');
            $data_fabricacao = !empty($_POST['data_fabricacao']) ? trim($_POST['data_fabricacao']) : null;
            $data_validade   = trim($_POST['data_validade'] ?? '');
            $quantidade      = filter_var($_POST['quantidade'] ?? 0, FILTER_VALIDATE_INT);
            $rawPreco        = trim(str_replace(['R$', ' '], '', $_POST['preco_compra'] ?? '0'));
            if (strpos($rawPreco, ',') !== false) {
                $rawPreco = str_replace('.', '', $rawPreco);
                $rawPreco = str_replace(',', '.', $rawPreco);
            }
            $preco_compra    = max(0.0, (float)$rawPreco);
            $fornecedor_id   = filter_var($_POST['fornecedor_id'] ?? null, FILTER_VALIDATE_INT);
            if ($fornecedor_id !== false && $fornecedor_id <= 0) {
                $fornecedor_id = null;
            }

            // Validação de integridade dos campos essenciais
            if (!$produto_id || empty($numero_lote) || empty($data_validade) || $quantidade < 0) {
                $_SESSION['flash_error'] = "Por favor, preencha todos os campos obrigatórios corretamente.";
                header("Location: " . BASE_URL . "/lotes/index.php?msg=erro");
                exit;
            }

            try {
                $pdo->beginTransaction();

                if ($id && $id > 0) {
                    // ── EDIÇÃO DE LOTE EXISTENTE ──────────────────────────────
                    $stmtAtual = $pdo->prepare("SELECT * FROM lotes WHERE id = ? FOR UPDATE");
                    $stmtAtual->execute([$id]);
                    $loteAtual = $stmtAtual->fetch(PDO::FETCH_ASSOC);

                    if (!$loteAtual) {
                        throw new Exception("Lote #$id não localizado para edição.");
                    }

                    // Força a imutabilidade do produto vinculado na edição
                    $produto_id = (int)$loteAtual['produto_id'];
                    $qtdAntiga  = (int)$loteAtual['quantidade'];
                    $delta      = $quantidade - $qtdAntiga;
                    $prodIdAlvo = $produto_id;

                    // Se a quantidade do lote foi alterada, ajusta estoque do produto e gera movimentação
                    if ($delta !== 0) {
                        $stmtProd = $pdo->prepare("UPDATE produtos SET quantidade = GREATEST(0, quantidade + ?) WHERE id = ?");
                        $stmtProd->execute([$delta, $prodIdAlvo]);

                        $tipoMov = ($delta > 0) ? 'entrada_compra' : 'perda';
                        $deltaAbs = abs($delta);
                        $obsMov = "Ajuste manual de saldo no lote #$numero_lote (" . ($delta > 0 ? "+$delta" : "$delta") . " un.)";

                        $stmtMov = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, ?, ?, ?)");
                        $stmtMov->execute([$prodIdAlvo, $tipoMov, $deltaAbs, $obsMov]);
                    }

                    // Atualiza os dados cadastrais do lote
                    $stmtUpd = $pdo->prepare("
                        UPDATE lotes 
                        SET produto_id = ?, numero_lote = ?, data_fabricacao = ?, data_validade = ?, quantidade = ?, preco_compra = ?, fornecedor_id = ?
                        WHERE id = ?
                    ");
                    $stmtUpd->execute([
                        $produto_id,
                        $numero_lote,
                        $data_fabricacao ?: null,
                        $data_validade,
                        $quantidade,
                        $preco_compra,
                        $fornecedor_id ?: null,
                        $id
                    ]);

                    registrar_log(
                        $pdo,
                        'LOTE_EDITADO',
                        "Lote #$numero_lote (ID #$id) atualizado. Saldo: $quantidade un. (delta: " . ($delta >= 0 ? "+$delta" : "$delta") . ")",
                        'lotes'
                    );

                } else {
                    // ── CADASTRO DE NOVO LOTE ────────────────────────────────
                    $stmtIns = $pdo->prepare("
                        INSERT INTO lotes (produto_id, numero_lote, data_fabricacao, data_validade, quantidade, preco_compra, fornecedor_id, data_entrada)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtIns->execute([
                        $produto_id,
                        $numero_lote,
                        $data_fabricacao ?: null,
                        $data_validade,
                        $quantidade,
                        $preco_compra,
                        $fornecedor_id ?: null
                    ]);
                    $novoLoteId = (int)$pdo->lastInsertId();

                    // Incrementa o saldo do produto no catálogo
                    $stmtProd = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
                    $stmtProd->execute([$quantidade, $produto_id]);

                    // Registra a movimentação de auditoria
                    $stmtMov = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'entrada_compra', ?, ?)");
                    $stmtMov->execute([$produto_id, $quantidade, "Entrada por cadastro manual de lote #$numero_lote"]);

                    registrar_log(
                        $pdo,
                        'LOTE_CRIADO',
                        "Lote #$numero_lote (ID #$novoLoteId) cadastrado com $quantidade un. para o produto #$produto_id",
                        'lotes'
                    );
                }

                $pdo->commit();
                $_SESSION['flash_success'] = 'Registro de lote salvo com sucesso!';
                header('Location: ' . BASE_URL . '/lotes/index.php');
                exit;

            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Erro ao salvar lote: " . $e->getMessage());
                $_SESSION['flash_error'] = "Ocorreu um erro operacional ao processar o lote. Tente novamente ou contate o suporte.";
                header("Location: " . BASE_URL . "/lotes/index.php?msg=erro");
                exit;
            }

        } elseif ($acao === 'deletar') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if (!$id || $id <= 0) {
                header("Location: " . BASE_URL . "/lotes/index.php?msg=erro");
                exit;
            }

            try {
                $pdo->beginTransaction();

                $stmtLote = $pdo->prepare("SELECT * FROM lotes WHERE id = ? FOR UPDATE");
                $stmtLote->execute([$id]);
                $lote = $stmtLote->fetch(PDO::FETCH_ASSOC);

                if (!$lote) {
                    throw new Exception("Lote #$id não encontrado.");
                }

                $qtdLote  = (int)$lote['quantidade'];
                $prodId   = (int)$lote['produto_id'];
                $numLote  = $lote['numero_lote'];

                // Se havia quantidade positiva no lote, baixa o estoque do produto e gera saída por perda/ajuste
                if ($qtdLote > 0) {
                    $stmtProd = $pdo->prepare("UPDATE produtos SET quantidade = GREATEST(0, quantidade - ?) WHERE id = ?");
                    $stmtProd->execute([$qtdLote, $prodId]);

                    $stmtMov = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'perda', ?, ?)");
                    $stmtMov->execute([$prodId, $qtdLote, "Baixa de estoque por exclusão definitiva do lote #$numLote"]);
                }

                $stmtDel = $pdo->prepare("DELETE FROM lotes WHERE id = ?");
                $stmtDel->execute([$id]);

                registrar_log(
                    $pdo,
                    'LOTE_EXCLUIDO',
                    "Lote #$numLote (ID #$id) excluído. Saldo debitado do catálogo: $qtdLote un. do produto #$prodId",
                    'lotes'
                );

                $pdo->commit();
                $_SESSION['flash_success'] = "Lote excluído com sucesso!";
                header('Location: ' . BASE_URL . '/lotes/index.php');
                exit;

            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Erro ao excluir lote: " . $e->getMessage());
                $_SESSION['flash_error'] = "Ocorreu um erro operacional ao excluir o lote. Tente novamente ou contate o suporte.";
                header("Location: " . BASE_URL . "/lotes/index.php?msg=erro");
                exit;
            }
        }
    }
}

header("Location: " . BASE_URL . "/lotes/index.php");
exit;
