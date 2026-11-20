<?php

function emitir_chave_envio_venda(string $acao): string
{
    $chave = bin2hex(random_bytes(16));
    $_SESSION['venda_envios_pendentes'][$chave] = [(int)$_SESSION['user_id'], $acao];
    $_SESSION['venda_envios_pendentes'] = array_slice($_SESSION['venda_envios_pendentes'], -32, null, true);
    $_SESSION['pdv_confirmacoes_margem'] = array_intersect_key(
        $_SESSION['pdv_confirmacoes_margem'] ?? [],
        $_SESSION['venda_envios_pendentes']
    );
    return $chave;
}

function chave_envio_venda_autorizada(mixed $chave, string $acao): bool
{
    if (!is_string($chave) || preg_match('/\A[0-9a-f]{32}\z/D', $chave) !== 1) {
        return false;
    }

    $vinculo = [(int)$_SESSION['user_id'], $acao];
    return ($_SESSION['venda_envios_pendentes'][$chave] ?? null) === $vinculo
        || ($_SESSION['venda_envios_concluidos'][hash('sha256', $chave)] ?? null) === $vinculo;
}

function concluir_chave_envio_venda(string $chave, string $acao): void
{
    unset($_SESSION['venda_envios_pendentes'][$chave], $_SESSION['pdv_confirmacoes_margem'][$chave]);
    $_SESSION['venda_envios_concluidos'][hash('sha256', $chave)] = [(int)$_SESSION['user_id'], $acao];
    $_SESSION['venda_envios_concluidos'] = array_slice($_SESSION['venda_envios_concluidos'], -32, null, true);
}

function localizar_venda_por_envio(PDO $pdo, string $chave): int
{
    $consulta = $pdo->prepare('SELECT id FROM vendas WHERE chave_envio = ?');
    $consulta->execute([$chave]);
    return (int)$consulta->fetchColumn();
}
