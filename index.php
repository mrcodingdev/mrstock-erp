<?php
/**
 * MrStock ERP - Redirecionador Principal
 */
// Caminho absoluto para config (inicializa headers de segurança e sessão blindada)
require_once __DIR__ . '/config.php';

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['user_perfil'] ?? '') === 'caixa') {
        header("Location: " . BASE_URL . "/vendas/pdv.php");
    } else {
        header("Location: " . BASE_URL . "/dashboard.php");
    }
} else {
    header("Location: " . BASE_URL . "/login.php");
}
exit;