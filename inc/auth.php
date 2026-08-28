<?php
/**
 * MrStock ERP - Verificação de Autenticação
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Impede que o navegador guarde cache das páginas protegidas (importante em laboratórios compartilhados)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

/**
 * Helpers Globais de Controle de Acesso Baseado em Papéis (RBAC)
 */
function is_admin(): bool {
    $perfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
    return strtolower((string)$perfil) === 'admin';
}

function require_admin(): void {
    if (!is_admin()) {
        $_SESSION['flash_error'] = "Acesso restrito. Este recurso requer privilégios de Administrador.";
        header("Location: " . BASE_URL . "/dashboard.php");
        exit;
    }
}
