<?php
require_once __DIR__ . '/inc/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    registrar_log($pdo, 'LOGOUT', "Usuário " . ($_SESSION['user_name'] ?? 'Usuário') . " encerrou a sessão", 'usuarios', (int)$_SESSION['user_id']);
}

$_SESSION = [];
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: " . BASE_URL . "/login.php");
exit;
