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
    error_log("Erro de Conexão com o Banco: " . $e->getMessage());
    $protocolo = 'ERR-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
    http_response_code(503);
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>MrStock ERP - 503 Serviço Temporariamente Indisponível</title>
    <link rel="icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/mr_stock_logo_branca.ico" type="image/x-icon">
    <style>
        :root {
            --brand-primary: #1a4231;
            --brand-secondary: #284936;
            --brand-accent: #6ae49b;
            --bg-color: #e8f3ee;
            --text-dark: #1e293b;
            --text-muted: #475569;
        }
        * { box-sizing: border-box; }
        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .card-503 {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
        }
        .logo-box {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1a4231, #284936);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 6px 18px rgba(40, 73, 54, 0.25);
        }
        .logo-box img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        h1 {
            color: #1a4231;
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            letter-spacing: -1px;
        }
        h2 {
            color: #1e293b;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 16px 0;
        }
        p {
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        .protocol-box {
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            font-family: monospace;
            font-size: 0.875rem;
            color: #334155;
            margin-bottom: 24px;
        }
        .btn-retry {
            display: inline-block;
            background-color: #284936;
            color: #ffffff;
            border: none;
            padding: 12px 28px;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(40, 73, 54, 0.25);
            transition: background-color 0.2s ease;
        }
        .btn-retry:hover {
            background-color: #1a4231;
        }
        .btn-retry:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(106, 228, 155, 0.45); }
        .footer-note {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="card-503">
        <div class="logo-box">
            <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/mr_stock_logo_branca.ico" alt="MrStock ERP">
        </div>
        <h1>503</h1>
        <h2>Serviço Temporariamente Indisponível</h2>
        <p>Estamos enfrentando uma instabilidade técnica momentânea na comunicação com o banco de dados. A equipe técnica já foi notificada.</p>
        <div class="protocol-box">
            Protocolo de Rastreamento: <strong><?= htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div>
            <button onclick="window.location.reload();" class="btn-retry">Tentar Novamente</button>
        </div>
        <div class="footer-note">
            MrStock ERP • Papelaria Real Ltda • Sorocaba/SP
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}
