<?php
/**
 * MrStock ERP - Endpoint Web Seguro de Povoamento de Dados (Demonstração TCC)
 * Uso temporário para sincronização remota do banco de dados na Hostinger
 */

declare(strict_types=1);

// Apenas requisições autorizadas com token criptográfico seguro
$tokenEsperado = 'mrstock_seeding_tcc_2026_sorocaba';
$tokenRecebido = $_GET['token'] ?? '';

if (!hash_equals($tokenEsperado, (string)$tokenRecebido)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Acesso negado: Token de autorizacao invalido ou ausente.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Configurações de execução web prolongada
set_time_limit(300);
ini_set('memory_limit', '256M');

header('Content-Type: application/json; charset=UTF-8');

try {
    require_once __DIR__ . '/../inc/database.php';
    require_once __DIR__ . '/seed_core.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('Falha na conexao com o banco de dados PDO.');
    }

    $resultado = executarSeedingTCC($pdo, true);

    if ($resultado['sucesso']) {
        http_response_code(200);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Povoamento determinístico do TCC concluído com sucesso!',
            'periodo' => '01/07/2026 a 28/10/2026 (120 dias consecutivos)',
            'total_vendas' => $resultado['total_vendas'],
            'faturamento_total' => $resultado['faturamento'],
            'faturado_outubro' => $resultado['faturado_outubro'],
            'venda_real_hoje' => [
                'id' => 580,
                'data_venda' => '2026-10-28 06:33:55',
                'produto' => 'Aquarela em Pastilha 12 Cores Pentel Arts',
                'total' => 31.90,
                'forma_pagamento' => 'DINHEIRO',
                'status' => 'Concluida - Topo da lista #1'
            ],
            'proximo_auto_increment' => 581,
            'data_execucao' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'erro' => $resultado['erro'] ?? 'Erro desconhecido durante o seeding.',
            'logs' => $resultado['logs'] ?? []
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
