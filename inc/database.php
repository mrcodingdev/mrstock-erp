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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='font-family:Arial;padding:20px;background:#fee;border:1px solid #f00;border-radius:8px;max-width:600px;margin:40px auto'>
        <h3>Erro de Conexão com o Banco de Dados</h3>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <small>Verifique se o MySQL está rodando no painel do XAMPP e o banco <b>mrstock_db</b> foi criado e importado.</small>
    </div>");
}
