<?php
/**
 * MrStock ERP - Controlador de Ações de Clientes
 */

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/auth.php';

$tipo = $_GET['tipo'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify(); // Proteção global CSRF para todas as ações POST

    if ($tipo === 'cliente') {
        $acao = $_POST['acao'] ?? '';

        if ($acao == 'salvar') {
            $id       = $_POST['id'] ?? '';
            $nome     = trim($_POST['nome']     ?? '');
            $email    = trim($_POST['email']    ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $status   = $_POST['status']        ?? 'ativo';
            $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
            $endereco = trim($_POST['endereco'] ?? '');
            $numero   = trim($_POST['numero']   ?? '');
            $bairro   = trim($_POST['bairro']   ?? '');
            $cidade   = trim($_POST['cidade']   ?? '');
            $estado   = trim($_POST['estado']   ?? '');
            $cep      = trim($_POST['cep']      ?? '');

            // Validação estrita: Nome não pode conter dígitos numéricos
            if (empty($nome) || preg_match('/[0-9]/', $nome)) {
                header("Location: " . BASE_URL . "/clientes/index.php?erro=nome_invalido");
                exit;
            }

            // Validação estrita: E-mail deve possuir formato válido RFC com domínio e TLD
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: " . BASE_URL . "/clientes/index.php?erro=email_invalido");
                exit;
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, status=?, cpf_cnpj=?, endereco=?, numero=?, bairro=?, cidade=?, estado=?, cep=? WHERE id=?");
                $stmt->execute([$nome, $email, $telefone, $status, $cpf_cnpj, $endereco, $numero, $bairro, $cidade, $estado, $cep, $id]);
                registrar_log($pdo, 'CLIENTE_EDITADO', "Cliente #$id ($nome) atualizado. Telefone: $telefone, Status: $status", 'clientes');
            } else {
                $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, status, cpf_cnpj, endereco, numero, bairro, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $email, $telefone, $status, $cpf_cnpj, $endereco, $numero, $bairro, $cidade, $estado, $cep]);
                $novoClienteId = (int)$pdo->lastInsertId();
                registrar_log($pdo, 'CLIENTE_CRIADO', "Novo cliente cadastrado: $nome (#$novoClienteId). CPF/CNPJ: $cpf_cnpj, Telefone: $telefone", 'clientes');
            }
            header("Location: " . BASE_URL . "/clientes/index.php?msg=sucesso");
            exit;

        } elseif ($acao == 'deletar') {
            // Proteção RBAC: Apenas Administrador pode excluir clientes
            $userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
            if ($userPerfil !== 'admin') {
                $_SESSION['flash_error'] = "Acesso negado: apenas administradores podem excluir clientes.";
                header("Location: " . BASE_URL . "/clientes/index.php");
                exit;
            }

            $id = $_POST['id'] ?? '';
            if ($id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id=?");
                    $stmt->execute([$id]);
                    registrar_log($pdo, 'CLIENTE_EXCLUIDO', "Cliente #$id excluído do cadastro", 'clientes');
                    header("Location: " . BASE_URL . "/clientes/index.php?msg=sucesso");
                } catch (PDOException $e) {
                    $stmt = $pdo->prepare("UPDATE clientes SET status='inativo' WHERE id=?");
                    $stmt->execute([$id]);
                    registrar_log($pdo, 'CLIENTE_INATIVADO', "Cliente #$id inativado devido a vínculos históricos com vendas", 'clientes');
                    header("Location: " . BASE_URL . "/clientes/index.php?msg=inativado");
                    exit;
                }
            } else {
                header("Location: " . BASE_URL . "/clientes/index.php");
            }
            exit;
        }
        header("Location: " . BASE_URL . "/clientes/index.php");
        exit;
    }
}

header("Location: " . BASE_URL . "/clientes/index.php");
exit;
