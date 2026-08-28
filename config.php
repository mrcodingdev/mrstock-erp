<?php
/**
 * MrStock ERP - Configurações Globais & Hardening de Segurança (OWASP ZAP)
 * Centraliza constantes de caminho, detecção dinâmica de ambiente (Local vs Nuvem),
 * credenciais de banco de dados e cabeçalhos defensivos.
 */

// 1. Caminho absoluto da raiz do projeto no filesystem
define('ROOT_PATH', realpath(__DIR__));

// ====================================================================
// DETECÇÃO DINÂMICA DE AMBIENTE (XAMPP LOCAL vs PRODUÇÃO UNAUX)
// ====================================================================
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = empty($httpHost) 
        || in_array($httpHost, ['localhost', '127.0.0.1', '::1']) 
        || strpos($httpHost, 'localhost:') === 0 
        || strpos($httpHost, '127.0.0.1:') === 0 
        || strpos($httpHost, '192.168.') === 0 
        || strpos($httpHost, '10.') === 0;

if ($isLocal) {
    // ── AMBIENTE LOCAL (XAMPP / DESENVOLVIMENTO) ─────────────────────
    define('ENVIRONMENT', 'development');
    
    // URL base do projeto detectada automaticamente no XAMPP
    $_projRoot = str_replace('\\', '/', ROOT_PATH);
    $_docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($_docRoot && strpos($_projRoot, $_docRoot) === 0) {
        define('BASE_URL', rtrim(str_replace($_docRoot, '', $_projRoot), '/'));
    } else {
        define('BASE_URL', '/' . basename(ROOT_PATH));
    }
    unset($_projRoot, $_docRoot);

    // Credenciais do MySQL Local (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'mrstock_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');

} else {
    // ── AMBIENTE REMOTO (PRODUÇÃO PROFEEHOST / UNAUX) ───────────────
    define('ENVIRONMENT', 'production');
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    define('BASE_URL', rtrim($protocol . $httpHost, '/'));

    // Credenciais do MySQL Remoto no ProFreeHost (configuradas no VistaPanel)
    define('DB_HOST', 'localhost');           // Host MySQL oficial do VistaPanel
    define('DB_NAME', 'mrstock_db');  // Nome real da base de dados
    define('DB_USER', 'root');             // Usuário MySQL oficial
    define('DB_PASS', '');             // Senha da conta (vPanel Password)
}

// 3. Supressão de Assinatura do Servidor (CWE-497)
@ini_set('expose_php', 'off');
if (function_exists('header_remove')) {
    @header_remove('X-Powered-By');
}

// 4. Configuração Defensiva de Cookies de Sessão (CWE-1004 & CWE-1275)
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'lifetime' => 0,          // Expira ao fechar o navegador
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,   // Condicional: true em HTTPS, false em HTTP local (XAMPP)
        'httponly' => true,       // CWE-1004: Impede acesso ao cookie de sessão via JavaScript
        'samesite' => 'Lax'       // CWE-1275: Proteção de contexto contra CSRF
    ]);

    session_start();
}

// 5. Injeção de Cabeçalhos HTTP de Segurança (CWE-693 & CWE-1021)
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=()");

    $csp = "default-src 'self'; "
         . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
         . "font-src 'self' data: https://fonts.gstatic.com; "
         . "img-src 'self' data:; "
         . "connect-src 'self'; "
         . "frame-ancestors 'self'; "
         . "base-uri 'self'; "
         . "form-action 'self';";

    header("Content-Security-Policy: " . $csp);
}

// 6. Carrega as funções utilitárias e de segurança globalmente
require_once ROOT_PATH . '/inc/functions.php';
