<?php
/**
 * MrStock ERP - Controlador de Ações de Fornecedores
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
    csrf_verify(); // Proteção global CSRF para todas as ações POST

    if ($tipo === 'fornecedor') {
        $acao = $_POST['acao'] ?? '';

        if ($acao == 'salvar') {
            $id       = $_POST['id']       ?? '';
            $nome     = trim($_POST['nome']     ?? '');
            $cnpj     = trim($_POST['cnpj']     ?? '');
            $email    = trim($_POST['email']    ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $status   = $_POST['status']        ?? 'ativo';
            $contato  = trim($_POST['contato']  ?? '');
            $endereco = trim($_POST['endereco'] ?? '');
            $numero   = trim($_POST['numero']   ?? '');
            $bairro   = trim($_POST['bairro']   ?? '');
            $cidade   = trim($_POST['cidade']   ?? '');
            $estado   = trim($_POST['estado']   ?? '');
            $cep      = trim($_POST['cep']      ?? '');

            // Validação estrita: Razão Social/Nome obrigatório
            if (empty($nome)) {
                header("Location: " . BASE_URL . "/fornecedores/index.php?erro=nome_obrigatorio");
                exit;
            }

            // Validação estrita: E-mail deve possuir formato válido RFC com domínio e TLD
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: " . BASE_URL . "/fornecedores/index.php?erro=email_invalido");
                exit;
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE fornecedores SET nome=?, cnpj=?, email=?, telefone=?, status=?, contato=?, endereco=?, numero=?, bairro=?, cidade=?, estado=?, cep=? WHERE id=?");
                $stmt->execute([$nome, $cnpj, $email, $telefone, $status, $contato, $endereco, $numero, $bairro, $cidade, $estado, $cep, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj, email, telefone, status, contato, endereco, numero, bairro, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $cnpj, $email, $telefone, $status, $contato, $endereco, $numero, $bairro, $cidade, $estado, $cep]);
            }
            header("Location: " . BASE_URL . "/fornecedores/index.php?msg=sucesso");
            exit;

        } elseif ($acao == 'deletar') {
            $id = $_POST['id'] ?? '';
            if ($id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id=?");
                    $stmt->execute([$id]);
                    header("Location: " . BASE_URL . "/fornecedores/index.php?msg=sucesso");
                } catch (PDOException $e) {
                    $stmt = $pdo->prepare("UPDATE fornecedores SET status='inativo' WHERE id=?");
                    $stmt->execute([$id]);
                    header("Location: " . BASE_URL . "/fornecedores/index.php?msg=inativado");
                }
            } else {
                header("Location: " . BASE_URL . "/fornecedores/index.php");
            }
            exit;
        }
        header("Location: " . BASE_URL . "/fornecedores/index.php");
        exit;
    }
}

header("Location: " . BASE_URL . "/fornecedores/index.php");
exit;
