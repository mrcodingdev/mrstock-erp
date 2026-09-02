<?php
/**
 * MrStock ERP - Controlador de Ações de Produtos e Movimentações
 */

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção RBAC: Apenas Administradores podem cadastrar, alterar produtos ou lançar movimentações manuais
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso negado: módulo restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

$tipo = $_GET['tipo'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify(); // Proteção global CSRF para todas as ações POST

    // ==========================================
    // TIPO: PRODUTO
    // ==========================================
    if ($tipo === 'produto') {
        $acao = $_POST['acao'] ?? '';

        if ($acao == 'salvar') {
            $id             = $_POST['id']             ?? '';
            $nome           = $_POST['nome']           ?? '';
            $categoria_id   = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
            $categoria      = 'Geral';
            if ($categoria_id) {
                $stmtC = $pdo->prepare("SELECT nome FROM categorias WHERE id = ?");
                $stmtC->execute([$categoria_id]);
                if ($cat = $stmtC->fetchColumn()) {
                    $categoria = $cat;
                }
            } else {
                $categoria = $_POST['categoria'] ?? 'Geral';
            }
            $validade       = !empty($_POST['validade']) ? $_POST['validade'] : null;
            $fornecedor_id  = !empty($_POST['fornecedor_id']) ? $_POST['fornecedor_id'] : null;
            $preco_venda    = $_POST['preco_venda']    ?? 0.00;
            $preco_compra   = $_POST['preco_compra']   ?? 0.00;
            $quantidade     = $_POST['quantidade']     ?? 0;
            $estoque_minimo = $_POST['estoque_minimo'] ?? 5;

            $codigo_de_barra = !empty($_POST['codigo_de_barra']) ? trim($_POST['codigo_de_barra']) : null;

            // Validação de unicidade do Código de Barras
            if (!empty($codigo_de_barra)) {
                if ($id) {
                    $stmtChkBar = $pdo->prepare("SELECT id FROM produtos WHERE codigo_de_barra = ? AND id != ?");
                    $stmtChkBar->execute([$codigo_de_barra, $id]);
                } else {
                    $stmtChkBar = $pdo->prepare("SELECT id FROM produtos WHERE codigo_de_barra = ?");
                    $stmtChkBar->execute([$codigo_de_barra]);
                }
                if ($stmtChkBar->fetch()) {
                    header("Location: " . BASE_URL . "/produtos/index.php?erro=barcode_duplicado");
                    exit;
                }
            }

            $diff_quantidade = 0;

            try {
                $pdo->beginTransaction();

                if ($id) {
                    $stmtAnt = $pdo->prepare("SELECT quantidade FROM produtos WHERE id = ? FOR UPDATE");
                    $stmtAnt->execute([$id]);
                    $qtdAnterior     = $stmtAnt->fetchColumn();
                    $diff_quantidade = $quantidade - $qtdAnterior;

                    $sql  = "UPDATE produtos SET nome=?, codigo_de_barra=?, categoria=?, categoria_id=?, validade=?, fornecedor_id=?, preco_venda=?, preco_compra=?, quantidade=?, estoque_minimo=? WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nome, $codigo_de_barra, $categoria, $categoria_id, $validade, $fornecedor_id, $preco_venda, $preco_compra, $quantidade, $estoque_minimo, $id]);
                    $prod_id = $id;

                    registrar_log($pdo, 'PRODUTO_EDITADO', "Produto #$id ($nome) atualizado. Preço Venda: R$ $preco_venda, Saldo: $quantidade", 'produtos');
                } else {
                    $sql  = "INSERT INTO produtos (nome, codigo_de_barra, categoria, categoria_id, validade, fornecedor_id, preco_venda, preco_compra, quantidade, estoque_minimo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nome, $codigo_de_barra, $categoria, $categoria_id, $validade, $fornecedor_id, $preco_venda, $preco_compra, $quantidade, $estoque_minimo]);
                    $prod_id         = (int)$pdo->lastInsertId();
                    $diff_quantidade = $quantidade;

                    registrar_log($pdo, 'PRODUTO_CRIADO', "Novo produto cadastrado: $nome. Preço Venda: R$ $preco_venda, Estoque Inicial: $quantidade", 'produtos');
                }

                if ($diff_quantidade > 0) {
                    $stmtMov = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'entrada_compra', ?, 'Ajuste Manual via Cadastro')");
                    $stmtMov->execute([$prod_id, $diff_quantidade]);
                } elseif ($diff_quantidade < 0) {
                    $stmtMov = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'perda', ?, 'Ajuste Manual via Cadastro')");
                    $stmtMov->execute([$prod_id, abs($diff_quantidade)]);
                }

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Erro ao persistir produto: " . $e->getMessage());
                header("Location: " . BASE_URL . "/produtos/index.php?erro=banco");
                exit;
            }

            header("Location: " . BASE_URL . "/produtos/index.php?msg=sucesso");
            exit;

        } elseif ($acao == 'deletar') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            if ($id) {
                // Pré-check: Verifica se o produto possui histórico de vendas ou compras vinculadas
                $stmtCheck = $pdo->prepare("SELECT 
                    (SELECT COUNT(*) FROM vendas_itens WHERE produto_id = ?) +
                    (SELECT COUNT(*) FROM itens_compra WHERE produto_id = ?) AS total_vinc");
                $stmtCheck->execute([$id, $id]);
                $hasHistory = (int)$stmtCheck->fetchColumn() > 0;
                
                if ($hasHistory) {
                    // Possui vendas ou compras vinculadas: Inativa logicamente (Soft Delete)
                    $stmt = $pdo->prepare("UPDATE produtos SET status = 'inativo' WHERE id = ?");
                    $stmt->execute([$id]);
                    registrar_log($pdo, 'PRODUTO_EXCLUIDO', "Produto #$id inativado/removido do catálogo", 'produtos');
                    header("Location: " . BASE_URL . "/produtos/index.php?msg=inativado");
                    exit;
                } else {
                    // Sem histórico: Exclusão física protegida com fallback
                    try {
                        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
                        $stmt->execute([$id]);
                    } catch (PDOException $e) {
                        $stmt = $pdo->prepare("UPDATE produtos SET status = 'inativo' WHERE id = ?");
                        $stmt->execute([$id]);
                    }
                    registrar_log($pdo, 'PRODUTO_EXCLUIDO', "Produto #$id inativado/removido do catálogo", 'produtos');
                    header("Location: " . BASE_URL . "/produtos/index.php?msg=sucesso");
                    exit;
                }
            }
            header("Location: " . BASE_URL . "/produtos/index.php?msg=sucesso");
            exit;
        } elseif ($acao == 'reativar') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            if ($id) {
                $stmt = $pdo->prepare("UPDATE produtos SET status = 'ativo' WHERE id = ?");
                $stmt->execute([$id]);
                registrar_log($pdo, 'PRODUTO_REATIVADO', "Produto #$id reativado no catálogo", 'produtos');
                header("Location: " . BASE_URL . "/produtos/index.php?msg=reativado");
                exit;
            }
        }
        header("Location: " . BASE_URL . "/produtos/index.php");
        exit;
    }

    // ==========================================
    // TIPO: MOVIMENTACAO
    // ==========================================
    elseif ($tipo === 'movimentacao') {
        if (($_POST['acao'] ?? '') == 'registrar') {
            $produto_id = filter_var($_POST['produto_id'], FILTER_VALIDATE_INT);
            $tipo_mov   = $_POST['tipo']       ?? '';
            $quantidade = filter_var($_POST['quantidade'], FILTER_VALIDATE_INT);
            $observacao = trim($_POST['observacao'] ?? '');

            if (!$produto_id || !$quantidade || $quantidade <= 0 || empty($tipo_mov)) {
                header("Location: " . BASE_URL . "/produtos/movimentacoes.php?msg=erro_dados");
                exit;
            }

            $pdo->beginTransaction();
            try {
                // Se for saída, valida se há saldo suficiente no estoque com lock pessimista
                if (!in_array($tipo_mov, ['entrada_compra', 'devolucao_cliente'])) {
                    $stmtStock = $pdo->prepare("SELECT quantidade, nome FROM produtos WHERE id = ? FOR UPDATE");
                    $stmtStock->execute([$produto_id]);
                    $prodAtual = $stmtStock->fetch(PDO::FETCH_ASSOC);

                    if (!$prodAtual || (int)$prodAtual['quantidade'] < $quantidade) {
                        $pdo->rollBack();
                        header("Location: " . BASE_URL . "/produtos/movimentacoes.php?msg=erro_saldo_insuficiente&disponivel=" . ((int)($prodAtual['quantidade'] ?? 0)));
                        exit;
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, ?, ?, ?)");
                $stmt->execute([$produto_id, $tipo_mov, $quantidade, $observacao]);

                if (in_array($tipo_mov, ['entrada_compra', 'devolucao_cliente'])) {
                    $stmtUpd = $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
                } else {
                    $stmtUpd = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
                }
                $stmtUpd->execute([$quantidade, $produto_id]);

                $motivo = !empty($observacao) ? $observacao : 'Ajuste manual';
                registrar_log($pdo, 'AJUSTE_ESTOQUE', "Movimentação manual ($tipo_mov): $quantidade unidades no Produto #$produto_id. Motivo: $motivo", 'movimentacoes');

                $pdo->commit();
                header("Location: " . BASE_URL . "/produtos/movimentacoes.php?msg=sucesso");
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                header("Location: " . BASE_URL . "/produtos/movimentacoes.php?msg=erro_banco");
            }
            exit;
        }
        header("Location: " . BASE_URL . "/produtos/movimentacoes.php");
        exit;
    }
}

header("Location: " . BASE_URL . "/produtos/index.php");
exit;
