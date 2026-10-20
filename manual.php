<?php
/**
 * MrStock ERP - Visualizador / Download do Manual do Usuário Oficial
 */
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';

$pdfPath = __DIR__ . '/docs/MRSTOCK_MANUAL_DO_USUARIO_v2.2.0_FINAL.pdf';

if (!file_exists($pdfPath)) {
    http_response_code(404);
    die('Arquivo do Manual do Usuário não localizado no servidor.');
}

$isDownload = isset($_GET['download']) && $_GET['download'] === '1';
$disposition = $isDownload ? 'attachment' : 'inline';

header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="MRSTOCK_MANUAL_DO_USUARIO_v2.2.0_FINAL.pdf"');
header('Content-Length: ' . filesize($pdfPath));
header('Cache-Control: private, max-age=86400, must-revalidate');
header('Pragma: public');

readfile($pdfPath);
exit;
