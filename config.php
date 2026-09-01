<?php
/**
 * MrStock ERP - Configurações Globais & Hardening de Segurança (OWASP ZAP)
 * Centraliza constantes de caminho, detecção dinâmica de ambiente (Local vs Nuvem),
 * credenciais de banco de dados e cabeçalhos defensivos.
 */

// 1. Caminho absoluto da raiz do projeto no filesystem
define('ROOT_PATH', realpath(__DIR__));

// ====================================================================
// 2. IDENTIFICAÇÃO E VERSIONAMENTO DO PROJETO (SEMANTIC VERSIONING)
// ====================================================================
if (!defined('MRSTOCK_VERSION')) {
    define('MRSTOCK_VERSION', 'v2.1.0');
    define('MRSTOCK_EDITION', 'Papelaria Real');
    define('MRSTOCK_BUILD_DATE', '01/09/2026');
}

// 2. Carregador Nativo de Variáveis de Ambiente (.env)
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($envName, $envVal) = explode('=', $line, 2);
            $envName = trim($envName);
            $envVal  = trim($envVal);
            if ((strpos($envVal, '"') === 0 && substr($envVal, -1) === '"') || 
                (strpos($envVal, "'") === 0 && substr($envVal, -1) === "'")) {
                $envVal = substr($envVal, 1, -1);
            }
            if (!array_key_exists($envName, $_SERVER) && !array_key_exists($envName, $_ENV)) {
                putenv("{$envName}={$envVal}");
                $_ENV[$envName] = $envVal;
                $_SERVER[$envName] = $envVal;
            }
        }
    }
}

// ====================================================================
// 3. DETECÇÃO DINÂMICA DE AMBIENTE E ROTAS (Local vs Nuvem)
// ====================================================================
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = empty($httpHost) 
        || in_array($httpHost, ['localhost', '127.0.0.1', '::1']) 
        || strpos($httpHost, 'localhost:') === 0 
        || strpos($httpHost, '127.0.0.1:') === 0 
        || strpos($httpHost, '192.168.') === 0 
        || strpos($httpHost, '10.') === 0;

define('ENVIRONMENT', getenv('APP_ENV') ?: ($isLocal ? 'development' : 'production'));

// Rota base (BASE_URL)
if (getenv('APP_URL')) {
    define('BASE_URL', rtrim(getenv('APP_URL'), '/'));
} elseif ($isLocal) {
    $_projRoot = str_replace('\\', '/', ROOT_PATH);
    $_docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($_docRoot && strpos($_projRoot, $_docRoot) === 0) {
        define('BASE_URL', rtrim(str_replace($_docRoot, '', $_projRoot), '/'));
    } else {
        define('BASE_URL', '/' . basename(ROOT_PATH));
    }
    unset($_projRoot, $_docRoot);
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    define('BASE_URL', rtrim($protocol . $httpHost, '/'));
}

// ====================================================================
// 4. CREDENCIAIS DE BANCO DE DADOS (Injetadas via .env)
// ====================================================================
define('DB_HOST', getenv('DB_HOST') ?: ($isLocal ? 'localhost' : 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: ($isLocal ? 'mrstock_db' : 'mrstock_db'));
define('DB_USER', getenv('DB_USER') ?: ($isLocal ? 'root' : 'root'));
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

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
