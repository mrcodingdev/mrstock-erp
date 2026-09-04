<?php
/**
 * MrStock ERP - Proxy de Consulta de CEP (ViaCEP / BrasilAPI)
 * Fornece consulta de CEP resiliente e com fallback automático para o frontend.
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

$cep = preg_replace('/\D/', '', $_GET['cep'] ?? '');

if (strlen($cep) !== 8) {
    echo json_encode(['erro' => true, 'mensagem' => 'CEP deve conter exatamente 8 dígitos.']);
    exit;
}

// 1. Tentativa Primária: ViaCEP
$ctx = stream_context_create([
    'http' => [
        'timeout' => 3,
        'header'  => "User-Agent: MrStockERP/2.0\r\n"
    ],
    'ssl' => [
        'verify_peer'      => true,
        'verify_peer_name' => true
    ]
]);

$response = @file_get_contents("https://viacep.com.br/ws/{$cep}/json/", false, $ctx);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && empty($data['erro'])) {
        echo json_encode([
            'erro'       => false,
            'logradouro' => $data['logradouro'] ?? '',
            'bairro'     => $data['bairro'] ?? '',
            'localidade' => $data['localidade'] ?? '',
            'uf'         => strtoupper($data['uf'] ?? 'SP')
        ]);
        exit;
    }
}

// 2. Tentativa Secundária (Fallback): BrasilAPI
$responseFallback = @file_get_contents("https://brasilapi.com.br/api/cep/v1/{$cep}", false, $ctx);

if ($responseFallback !== false) {
    $dataFallback = json_decode($responseFallback, true);
    if ($dataFallback && !isset($dataFallback['errors']) && !empty($dataFallback['city'])) {
        echo json_encode([
            'erro'       => false,
            'logradouro' => $dataFallback['street'] ?? '',
            'bairro'     => $dataFallback['neighborhood'] ?? '',
            'localidade' => $dataFallback['city'] ?? '',
            'uf'         => strtoupper($dataFallback['state'] ?? 'SP')
        ]);
        exit;
    }
}

// Se nenhuma base encontrou o CEP
echo json_encode([
    'erro'     => true,
    'mensagem' => 'CEP não encontrado nas bases oficiais.'
]);
