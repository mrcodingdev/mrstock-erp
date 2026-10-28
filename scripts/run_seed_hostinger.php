<?php
/**
 * MrStock ERP - Script Web Seguro de Povoamento de Dados para Produção (Hostinger)
 * Protegido por Token Secreto para execução controlada em produção (u949540250_mrstock)
 */

$tokenEsperado = 'mrstock_seeding_tcc_2026_sorocaba';
$tokenRecebido = $_GET['token'] ?? '';

if (!hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>403 Acesso Negado - MrStock ERP</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <style>
            body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .card-auth { max-width: 480px; width: 100%; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        </style>
    </head>
    <body>
        <div class="card card-auth p-4 text-center bg-white">
            <h4 class="text-danger fw-bold mb-2">403 - Acesso Proibido</h4>
            <p class="text-muted small mb-0">Token de segurança ausente ou inválido. A execução do seeding em produção requer autorização explícita.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

@set_time_limit(300);
@ini_set('memory_limit', '256M');

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/seed_core.php';

$inicio = microtime(true);
$resultado = executarSeedingTCC($pdo, true);
$tempoExec = round(microtime(true) - $inicio, 2);

$catNomes = [
    1  => 'Cadernos & Blocos',
    6  => 'Papéis & Folhas',
    2  => 'Canetas & Marcadores',
    7  => 'Pastas & Organização',
    9  => 'Tintas & Pintura',
    3  => 'Lápis & Apontadores',
    10 => 'Grampeadores & Fixação',
    8  => 'Corte & Medição',
    5  => 'Colas & Fitas Adesivas',
    4  => 'Borrachas & Correção'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Povoamento Oficial TCC - MrStock ERP</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 30px 15px; }
        .container-seed { max-width: 860px; margin: 0 auto; }
        .card-panel { background: #ffffff; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
        .header-strip { background-color: #1a4231; color: #ffffff; border-radius: 11px 11px 0 0; padding: 20px 24px; }
        .badge-kpi { font-size: 0.85rem; padding: 6px 12px; border-radius: 6px; }
        .btn-solid-green { background-color: #1a4231; color: #ffffff; font-weight: 600; border: none; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-solid-green:hover { background-color: #143326; color: #ffffff; }
        pre.terminal-log { background-color: #0f172a; color: #38bdf8; padding: 15px; border-radius: 8px; font-size: 0.8rem; max-height: 280px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container-seed">
        <div class="card card-panel">
            <div class="header-strip d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold m-0"><i class="fas fa-database me-2 text-warning"></i>MrStock ERP - Povoamento Concluído</h4>
                    <span class="small opacity-75">Demonstração Oficial ETEC Sorocaba (Papelaria Real)</span>
                </div>
                <div>
                    <span class="badge bg-success badge-kpi"><i class="fas fa-check-circle me-1"></i> 100% SUCESSO</span>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded border text-center">
                            <span class="text-muted d-block small fw-bold">TOTAL DE VENDAS</span>
                            <span class="fs-4 fw-bold text-dark"><?= number_format($resultado['total_vendas'] ?? 0, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded border text-center">
                            <span class="text-muted d-block small fw-bold">FATURAMENTO TOTAL</span>
                            <span class="fs-4 fw-bold text-success">R$ <?= number_format($resultado['faturamento'] ?? 0, 2, ',', '.') ?></span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded border text-center">
                            <span class="text-muted d-block small fw-bold">TEMPO DE EXECUÇÃO</span>
                            <span class="fs-4 fw-bold text-primary"><?= $tempoExec ?> segundos</span>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-chart-pie me-2 text-primary"></i>Faturamento por Categoria (Outubro/2026 - Mês Atual):</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>Micro-Categoria</th>
                                <th class="text-end">Faturamento no Mês</th>
                                <th class="text-center">Status Hierárquico</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            $fatOut = $resultado['faturado_outubro'] ?? [];
                            arsort($fatOut);
                            foreach ($fatOut as $catId => $val): 
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $rank ?>º</td>
                                <td class="fw-semibold text-dark"><?= $catNomes[$catId] ?? "Cat #{$catId}" ?></td>
                                <td class="text-end fw-bold">R$ <?= number_format($val, 2, ',', '.') ?></td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Em Conformidade</span></td>
                            </tr>
                            <?php 
                            $rank++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-terminal me-2 text-secondary"></i>Log de Execução:</h6>
                <pre class="terminal-log mb-4"><?= htmlspecialchars(implode("\n", $resultado['logs'] ?? [])) ?></pre>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-muted small">Ambiente: <?= defined('ENVIRONMENT') ? strtoupper(ENVIRONMENT) : 'LOCAL' ?></span>
                    <a href="../relatorios/analise.php?periodo=mes_atual" class="btn-solid-green">
                        <i class="fas fa-arrow-right"></i> Abrir Painel de Análise
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
