<?php

define('MRSTOCK_PUBLIC_ENTRY', 'inicio');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/landing.php';

$destino = destino_sessao_landing();
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

if ($destino !== null) {
    header('Cache-Control: private, no-store');
    header('Location: ' . BASE_URL . $destino, true, 302);
    exit;
}

$caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (is_string($caminho) && str_ends_with($caminho, '/index.php')) {
    header('Cache-Control: private, no-store');
    header('Location: ' . BASE_URL . '/', true, 301);
    exit;
}

servir_landing('inicio.html', indexavel: true);
