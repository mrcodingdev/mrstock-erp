<?php
/**
 * MrStock ERP - Conexão com o Banco de Dados (PDO/MySQL)
 * Padrão XAMPP: root sem senha. Altere se necessário.
 */
require_once __DIR__ . '/../config.php';

$host   = defined('DB_HOST') ? DB_HOST : 'localhost';
$dbname = defined('DB_NAME') ? DB_NAME : 'mrstock_db';
$user   = defined('DB_USER') ? DB_USER : 'root';
$pass   = defined('DB_PASS') ? DB_PASS : '';

try {
    $dbTimezone = (defined('APP_TIMEZONE') && APP_TIMEZONE === 'America/Sao_Paulo') ? '-03:00' : '-03:00';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '{$dbTimezone}'"
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET time_zone = '{$dbTimezone}'");
} catch (PDOException $e) {
    error_log("Erro de conexao MySQL MrStock: " . $e->getMessage());
    $protocolo = 'ERR-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
    http_response_code(500);
    die("<div style='font-family:Arial,sans-serif;padding:25px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;max-width:600px;margin:50px auto;color:#2d3748;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);'>
        <h3 style='color:#c53030;margin-top:0;'>Serviço Temporariamente Indisponível</h3>
        <p>Não foi possível estabelecer conexão segura com a base de dados do MrStock ERP. O incidente foi registrado para resolução da equipe técnica.</p>
        <p style='background:#edf2f7;padding:10px;border-radius:4px;font-family:monospace;font-size:0.9em;'>Protocolo de Rastreamento: <b>" . htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8') . "</b></p>
        <small style='color:#718096;'>Verifique se o serviço MySQL está ativo no painel do XAMPP e se a base <b>mrstock_db</b> está configurada.</small>
    </div>");
}
