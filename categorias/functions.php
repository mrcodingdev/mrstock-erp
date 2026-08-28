<?php
/**
 * MrStock ERP - Controlador de Ações de Categorias
 */

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Apenas Admin
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

$tipo = $_GET['tipo'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify(); // Proteção global CSRF

    if ($tipo === 'categoria') {
        $acao = $_POST['acao'] ?? '';

        if ($acao == 'salvar') {
            $id        = $_POST['id'] ?? '';
            $nome      = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if ($id) {
                $stmt = $pdo->prepare("UPDATE categorias SET nome=?, descricao=? WHERE id=?");
                $stmt->execute([$nome, $descricao, $id]);

                // Sincroniza a coluna legada textual 'categoria' na tabela produtos
                $stmtUpdProd = $pdo->prepare("UPDATE produtos SET categoria=? WHERE categoria_id=?");
                $stmtUpdProd->execute([$nome, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO categorias (nome, descricao) VALUES (?, ?)");
                $stmt->execute([$nome, $descricao]);
            }
            header("Location: " . BASE_URL . "/categorias/index.php?msg=sucesso");
            exit;

        } elseif ($acao == 'deletar') {
            $id = $_POST['id'] ?? '';
            if ($id) {
                try {
                    $pdo->beginTransaction();
                    // Ao deletar uma categoria, colocamos categoria_id como NULL nos produtos
                    $stmtUpd = $pdo->prepare("UPDATE produtos SET categoria_id = NULL WHERE categoria_id = ?");
                    $stmtUpd->execute([$id]);

                    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id=?");
                    $stmt->execute([$id]);
                    $pdo->commit();
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log("Erro ao excluir categoria ID {$id}: " . $e->getMessage());
                    header("Location: " . BASE_URL . "/categorias/index.php?msg=erro");
                    exit;
                }
            }
            header("Location: " . BASE_URL . "/categorias/index.php?msg=sucesso");
            exit;
        }
        header("Location: " . BASE_URL . "/categorias/index.php");
        exit;
    }
}

header("Location: " . BASE_URL . "/categorias/index.php");
exit;
