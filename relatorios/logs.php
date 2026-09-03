<?php
/**
 * MrStock ERP - Consulta e Auditoria de Logs Operacionais
 * MrStock Design System (Papelaria Real) & Auditoria Operacional
 */

$pageTitle  = 'Auditoria de Logs';
$activePage = 'logs';

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Bloqueio RBAC: Exclusivo para Administradores
require_admin();

// ── 1. Parâmetros e Filtros de Busca (GET) ──────────────────────────────────
$busca         = trim($_GET['busca'] ?? '');
$filtroAcao    = trim($_GET['acao'] ?? '');
$filtroUser    = filter_var($_GET['usuario_id'] ?? '', FILTER_VALIDATE_INT) ?: null;
$dataInicio    = trim($_GET['data_inicio'] ?? '');
$dataFim       = trim($_GET['data_fim'] ?? '');
$filtroRapido  = trim($_GET['quick'] ?? $_GET['grupo'] ?? '');
$filtroCritico = !empty($_GET['critico']) || ($filtroAcao === '__CRITICAS__') || ($filtroRapido === 'criticas');
$ordem         = trim($_GET['ordem'] ?? 'recente'); // 'recente' ou 'severidade'
$pagina        = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$itensPorPag   = 20;

// Validação de formato de data (YYYY-MM-DD)
$validaData = function(string $data): bool {
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
};

if (!empty($dataInicio) && !$validaData($dataInicio)) {
    $dataInicio = '';
}
if (!empty($dataFim) && !$validaData($dataFim)) {
    $dataFim = '';
}

// ── 2. Montagem da Query Dinâmica com Prepared Statements ───────────────────
$whereClauses = ["1=1"];
$params       = [];

if ($busca !== '') {
    $whereClauses[] = "(l.descricao LIKE ? OR l.ip_usuario LIKE ? OR u.username LIKE ? OR l.acao LIKE ? OR l.tabela_afetada LIKE ?)";
    $term = "%{$busca}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($filtroCritico) {
    $whereClauses[] = "l.acao IN ('FALHA_LOGIN', 'PRODUTO_EXCLUIDO', 'AJUSTE_ESTOQUE')";
} elseif ($filtroAcao !== '') {
    $whereClauses[] = "l.acao = ?";
    $params[] = $filtroAcao;
} elseif ($filtroRapido === 'vendas') {
    $whereClauses[] = "l.acao IN ('VENDA_PDV', 'VENDA_RAPIDA')";
} elseif ($filtroRapido === 'logins') {
    $whereClauses[] = "l.acao IN ('LOGIN_SUCESSO', 'LOGOUT', 'FALHA_LOGIN')";
} elseif ($filtroRapido === 'estoque') {
    $whereClauses[] = "l.acao IN ('AJUSTE_ESTOQUE', 'COMPRA_REGISTRADA', 'PRODUTO_CRIADO', 'PRODUTO_EDITADO', 'PRODUTO_EXCLUIDO', 'PRODUTO_REATIVADO')";
}

if (!empty($filtroUser)) {
    $whereClauses[] = "l.usuario_id = ?";
    $params[] = $filtroUser;
}

if (!empty($dataInicio)) {
    $whereClauses[] = "l.data_log >= ?";
    $params[] = $dataInicio . ' 00:00:00';
}

if (!empty($dataFim)) {
    $whereClauses[] = "l.data_log <= ?";
    $params[] = $dataFim . ' 23:59:59';
}

$whereSql = implode(' AND ', $whereClauses);

// Ordenação: Recente (padrão) ou Severidade Crítica Implícita
if ($ordem === 'severidade') {
    $orderBySql = "
        CASE 
            WHEN l.acao IN ('FALHA_LOGIN', 'PRODUTO_EXCLUIDO') THEN 1
            WHEN l.acao IN ('AJUSTE_ESTOQUE', 'CONFIGURACAO_ALTERADA') THEN 2
            WHEN l.acao IN ('VENDA_PDV', 'VENDA_RAPIDA', 'COMPRA_REGISTRADA') THEN 3
            WHEN l.acao IN ('LOGIN_SUCESSO', 'LOGOUT') THEN 4
            ELSE 5
        END ASC,
        l.id DESC
    ";
} else {
    $orderBySql = "l.id DESC";
}

// ── EXPORTAÇÃO CSV DE LOGS ──────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (ob_get_length()) {
        ob_clean();
    }

    $filename = "mrstock_logs_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // Escreve BOM UTF-8 (\xEF\xBB\xBF) para compatibilidade nativa com Excel
    fwrite($out, "\xEF\xBB\xBF");

    // Cabeçalho do CSV
    fputcsv($out, ['ID', 'Data/Hora', 'Operador', 'Perfil', 'Ação', 'Tabela', 'Descrição', 'IP'], ';');

    // Sanitização contra CSV Injection (CWE-1236 / OWASP Top 10)
    $sanitizeCsv = function(?string $value): string {
        $str = (string)($value ?? '');
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $str;
        }
        return $str;
    };

    $sqlCsv = "
        SELECT l.id, l.usuario_id, l.acao, l.descricao, l.tabela_afetada, l.ip_usuario, l.data_log,
               COALESCE(u.username, 'Sistema/Excluído') AS username,
               COALESCE(u.perfil, 'sistema') AS perfil
        FROM logs l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        WHERE {$whereSql}
        ORDER BY {$orderBySql}
    ";
    $stmtCsv = $pdo->prepare($sqlCsv);
    $stmtCsv->execute($params);

    while ($row = $stmtCsv->fetch(PDO::FETCH_ASSOC)) {
        $dt = !empty($row['data_log']) ? date('d/m/Y H:i:s', strtotime($row['data_log'])) : '-';
        fputcsv($out, [
            $row['id'],
            $dt,
            $sanitizeCsv($row['username']),
            $sanitizeCsv($row['perfil']),
            $sanitizeCsv($row['acao']),
            $sanitizeCsv($row['tabela_afetada'] ?? ''),
            $sanitizeCsv($row['descricao'] ?? ''),
            $sanitizeCsv($row['ip_usuario'] ?? '')
        ], ';');
    }

    fclose($out);
    exit;
}

// ── 3. Contagem Total para Paginação ────────────────────────────────────────
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) 
    FROM logs l
    LEFT JOIN usuarios u ON l.usuario_id = u.id
    WHERE {$whereSql}
");
$stmtCount->execute($params);
$totalRegistros = (int)$stmtCount->fetchColumn();
$totalPaginas   = max(1, (int)ceil($totalRegistros / $itensPorPag));

if ($pagina > $totalPaginas && $totalRegistros > 0) {
    $pagina = $totalPaginas;
}
$offset = ($pagina - 1) * $itensPorPag;

// ── 4. Busca Paginada de Logs com JOIN em Usuários ──────────────────────────
$sqlLogs = "
    SELECT l.id, l.usuario_id, l.acao, l.descricao, l.tabela_afetada, l.ip_usuario, l.data_log,
           COALESCE(u.username, 'Sistema/Excluído') AS username,
           COALESCE(u.perfil, 'sistema') AS perfil
    FROM logs l
    LEFT JOIN usuarios u ON l.usuario_id = u.id
    WHERE {$whereSql}
    ORDER BY {$orderBySql}
    LIMIT {$itensPorPag} OFFSET {$offset}
";
$stmtLogs = $pdo->prepare($sqlLogs);
$stmtLogs->execute($params);
$logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

// ── 5. KPIs de Auditoria em Tempo Real (Hoje) ───────────────────────────────
try {
    $sqlKpis = "
        SELECT 
            COUNT(*) AS total_hoje,
            SUM(CASE WHEN acao IN ('VENDA_RAPIDA', 'VENDA_PDV') THEN 1 ELSE 0 END) AS vendas_hoje,
            SUM(CASE WHEN acao = 'LOGIN_SUCESSO' THEN 1 ELSE 0 END) AS logins_hoje,
            SUM(CASE WHEN acao IN ('AJUSTE_ESTOQUE', 'PRODUTO_EXCLUIDO', 'COMPRA_REGISTRADA', 'FALHA_LOGIN') THEN 1 ELSE 0 END) AS ajustes_hoje
        FROM logs 
        WHERE data_log >= CURDATE() AND data_log < CURDATE() + INTERVAL 1 DAY
    ";
    $kpiData = $pdo->query($sqlKpis)->fetch(PDO::FETCH_ASSOC) ?: [];

    $kpiTotalHoje   = (int)($kpiData['total_hoje'] ?? 0);
    $kpiVendasHoje  = (int)($kpiData['vendas_hoje'] ?? 0);
    $kpiLoginsHoje  = (int)($kpiData['logins_hoje'] ?? 0);
    $kpiAjustesHoje = (int)($kpiData['ajustes_hoje'] ?? 0);
} catch (Exception $e) {
    $kpiTotalHoje   = 0;
    $kpiVendasHoje  = 0;
    $kpiLoginsHoje  = 0;
    $kpiAjustesHoje = 0;
}

// ── 6. Listas para Seleção dos Filtros ──────────────────────────────────────
try {
    $usuariosFiltro = $pdo->query("SELECT id, username, perfil FROM usuarios ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
    $acoesFiltro    = $pdo->query("SELECT DISTINCT acao FROM logs ORDER BY acao ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $usuariosFiltro = [];
    $acoesFiltro    = [];
}

// ── 7. Helpers de Formatação e Estilização de Badges ────────────────────────
$getBadgeAcao = function(string $acao): array {
    return match($acao) {
        'FALHA_LOGIN'           => ['class' => 'bg-danger text-white',        'icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Falha de Login'],
        'CONFIGURACAO_ALTERADA' => ['class' => 'badge-purple text-white',     'icon' => 'fa-solid fa-sliders',              'label' => 'Configuração'],
        'LOGIN_SUCESSO'         => ['class' => 'bg-success text-white',       'icon' => 'fa-solid fa-right-to-bracket',     'label' => 'Login Sucesso'],
        'LOGOUT'                => ['class' => 'bg-secondary text-white',     'icon' => 'fa-solid fa-right-from-bracket',   'label' => 'Logout'],
        'VENDA_RAPIDA'          => ['class' => 'bg-primary text-white',       'icon' => 'fa-solid fa-bolt',                 'label' => 'Venda Rápida'],
        'VENDA_PDV'             => ['class' => 'badge-emerald text-white',    'icon' => 'fa-solid fa-cash-register',         'label' => 'Venda PDV'],
        'PRODUTO_CRIADO'        => ['class' => 'bg-info text-dark',           'icon' => 'fa-solid fa-circle-plus',          'label' => 'Produto Criado'],
        'PRODUTO_EDITADO'       => ['class' => 'bg-warning text-dark',        'icon' => 'fa-solid fa-pen-to-square',        'label' => 'Produto Editado'],
        'PRODUTO_EXCLUIDO'      => ['class' => 'badge-rose text-white',       'icon' => 'fa-solid fa-trash-can',            'label' => 'Produto Excluído'],
        'PRODUTO_REATIVADO'     => ['class' => 'bg-info text-white',          'icon' => 'fa-solid fa-rotate-left',          'label' => 'Produto Reativado'],
        'AJUSTE_ESTOQUE'        => ['class' => 'bg-warning text-dark',        'icon' => 'fa-solid fa-boxes-packing',        'label' => 'Ajuste de Estoque'],
        'COMPRA_REGISTRADA'     => ['class' => 'badge-indigo text-white',     'icon' => 'fa-solid fa-truck-ramp-box',        'label' => 'Compra Registrada'],
        'CLIENTE_CRIADO'        => ['class' => 'badge-emerald text-white',    'icon' => 'fa-solid fa-user-plus',             'label' => 'Cliente Cadastrado'],
        'CLIENTE_EDITADO'       => ['class' => 'badge-indigo text-white',     'icon' => 'fa-solid fa-user-pen',              'label' => 'Cliente Atualizado'],
        'CLIENTE_EXCLUIDO'      => ['class' => 'bg-danger text-white',        'icon' => 'fa-solid fa-user-xmark',            'label' => 'Cliente Excluído'],
        'CLIENTE_INATIVADO'     => ['class' => 'bg-warning text-dark',        'icon' => 'fa-solid fa-user-slash',           'label' => 'Cliente Inativado'],
        'FORNECEDOR_CRIADO'     => ['class' => 'bg-info text-white',          'icon' => 'fa-solid fa-truck-ramp-box',        'label' => 'Fornecedor Cadastrado'],
        'FORNECEDOR_EDITADO'    => ['class' => 'bg-warning text-dark',        'icon' => 'fa-solid fa-truck-arrow-right',     'label' => 'Fornecedor Atualizado'],
        'FORNECEDOR_EXCLUIDO'   => ['class' => 'bg-danger text-white',        'icon' => 'fa-solid fa-truck-front',           'label' => 'Fornecedor Excluído'],
        'FORNECEDOR_INATIVADO'  => ['class' => 'bg-warning text-dark',        'icon' => 'fa-solid fa-truck-droplet',        'label' => 'Fornecedor Inativado'],
        'CATEGORIA_CRIADA'      => ['class' => 'badge-purple text-white',     'icon' => 'fa-solid fa-tags',                  'label' => 'Categoria Criada'],
        'CATEGORIA_EDITADA'     => ['class' => 'bg-warning text-dark',        'icon' => 'fa-solid fa-pen-ruler',             'label' => 'Categoria Atualizada'],
        'CATEGORIA_EXCLUIDA'    => ['class' => 'bg-danger text-white',        'icon' => 'fa-solid fa-tag',                   'label' => 'Categoria Excluída'],
        default                 => ['class' => 'bg-light text-dark border',   'icon' => 'fa-solid fa-shield',               'label' => $acao]
    };
};

// Constrói URL mantendo parâmetros de filtro na paginação
$buildPaginationUrl = function(int $p) use ($busca, $filtroAcao, $filtroUser, $dataInicio, $dataFim, $filtroCritico, $ordem, $filtroRapido): string {
    $params = array_filter([
        'busca'       => $busca,
        'acao'        => $filtroAcao,
        'usuario_id'  => $filtroUser,
        'data_inicio' => $dataInicio,
        'data_fim'    => $dataFim,
        'critico'     => ($filtroCritico && $filtroRapido !== 'criticas') ? '1' : null,
        'quick'       => $filtroRapido ?: null,
        'ordem'       => $ordem !== 'recente' ? $ordem : null,
        'pagina'      => $p
    ], fn($val) => $val !== '' && $val !== null);
    return BASE_URL . '/relatorios/logs.php?' . http_build_query($params);
};

// Constrói URL de exportação CSV com os mesmos filtros ativos
$csvExportParams = array_filter([
    'busca'       => $busca,
    'acao'        => $filtroAcao,
    'usuario_id'  => $filtroUser,
    'data_inicio' => $dataInicio,
    'data_fim'    => $dataFim,
    'critico'     => ($filtroCritico && $filtroRapido !== 'criticas') ? '1' : null,
    'quick'       => $filtroRapido ?: null,
    'ordem'       => $ordem !== 'recente' ? $ordem : null,
    'export'      => 'csv'
], fn($val) => $val !== '' && $val !== null);
$exportCsvUrl = BASE_URL . '/relatorios/logs.php?' . http_build_query($csvExportParams);

require_once __DIR__ . '/../inc/header.php';
?>

<!-- ══ CABEÇALHO DA PÁGINA (TOPBAR SALESOPS) ═════════════════════════════════ -->
<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0">
            <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Auditoria & Logs Operacionais
        </h2>
        <p class="text-muted m-0">Rastreabilidade completa de autenticações, vendas, movimentações de estoque e cadastros do ERP.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/relatorios/index.php" class="btn btn-secondary fw-semibold shadow-sm text-white">
            <i class="fa-solid fa-arrow-left me-1"></i> Central de Relatórios
        </a>
        <a href="<?= $exportCsvUrl ?>" class="btn btn-success fw-semibold shadow-sm text-white" title="Exportar dados filtrados para planilha">
            <i class="fa-solid fa-file-excel me-1"></i> Exportar CSV
        </a>
        <a href="<?= BASE_URL ?>/relatorios/logs.php" class="btn btn-primary fw-semibold shadow-sm text-white">
            <i class="fa-solid fa-rotate-right me-1"></i> Atualizar
        </a>
    </div>
</div>

<div class="content-body">
    <!-- ══ 4 STAT CARDS NO TOPO (BENTO GRID SALESOPS) ═════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Registros Hoje -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Registros Hoje</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($kpiTotalHoje, 0, ',', '.') ?></h3>
                        <small class="text-muted">Ações registradas hoje</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Vendas Auditadas Hoje -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Vendas Auditadas</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($kpiVendasHoje, 0, ',', '.') ?></h3>
                        <small class="text-success fw-semibold">PDV e Venda Rápida hoje</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Logins Autenticados Hoje -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Logins Autenticados</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($kpiLoginsHoje, 0, ',', '.') ?></h3>
                        <small class="text-info fw-semibold">Sessões iniciadas hoje</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--info">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Ajustes & Ações Críticas -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Ações Críticas / Estoque</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= number_format($kpiAjustesHoje, 0, ',', '.') ?></h3>
                        <small class="text-warning fw-semibold">Falhas, Exclusões e Estoque</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--warning">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ CARD DE FILTROS AVANÇADOS ═══════════════════════════════════════════ -->
    <div class="so-card mb-4">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <h5 class="so-card-title text-dark m-0">
                <i class="fa-solid fa-filter text-primary me-2"></i>Filtros de Auditoria
            </h5>
            <?php if ($busca || $filtroAcao || $filtroUser || $dataInicio || $dataFim || $filtroCritico || $filtroRapido || $ordem !== 'recente'): ?>
                <a href="<?= BASE_URL ?>/relatorios/logs.php" class="btn btn-sm btn-secondary text-white">
                    <i class="fa-solid fa-rotate-left me-1"></i> Limpar Filtros
                </a>
            <?php endif; ?>
        </div>
        <div class="so-card-body">
            <form method="GET" action="<?= BASE_URL ?>/relatorios/logs.php" class="row g-3 align-items-end">
                <!-- Busca Textual Inteligente -->
                <div class="col-12 col-md-3">
                    <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_busca">Busca Inteligente</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="filtro_busca" name="busca" class="form-control" placeholder="Ação, Descrição, IP, Usuário..." value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <!-- Filtro de Ação com Opção de Ações Críticas -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_acao">Tipo de Ação</label>
                    <select id="filtro_acao" name="acao" class="form-select form-select-sm">
                        <option value="">Todas as Ações</option>
                        <option value="__CRITICAS__" <?= ($filtroAcao === '__CRITICAS__' || $filtroCritico) ? 'selected' : '' ?>>⚠️ Ações Críticas (Falhas/Exclusões)</option>
                        <?php foreach ($acoesFiltro as $ac): ?>
                            <option value="<?= htmlspecialchars($ac, ENT_QUOTES, 'UTF-8') ?>" <?= ($filtroAcao === $ac && !$filtroCritico) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ac, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ordenação com Severidade Implícita -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_ordem">Ordenação</label>
                    <select id="filtro_ordem" name="ordem" class="form-select form-select-sm">
                        <option value="recente" <?= $ordem === 'recente' ? 'selected' : '' ?>>Mais Recentes (Padrão)</option>
                        <option value="severidade" <?= $ordem === 'severidade' ? 'selected' : '' ?>>Severidade Crítica Primeiro</option>
                    </select>
                </div>

                <!-- Filtro de Usuário -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_usuario">Operador / Usuário</label>
                    <select id="filtro_usuario" name="usuario_id" class="form-select form-select-sm">
                        <option value="">Todos os Operadores</option>
                        <?php foreach ($usuariosFiltro as $u): ?>
                            <option value="<?= (int)$u['id'] ?>" <?= $filtroUser === (int)$u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['perfil'], ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data Inicial -->
                <div class="col-12 col-sm-6 col-md-1">
                    <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_data_ini">Data Início</label>
                    <input type="date" id="filtro_data_ini" name="data_inicio" class="form-control form-control-sm tabular-nums" value="<?= htmlspecialchars($dataInicio, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Data Final -->
                <div class="col-12 col-sm-6 col-md-1">
                    <label class="form-label text-xs fw-semibold text-muted mb-1" for="filtro_data_fim">Data Fim</label>
                    <input type="date" id="filtro_data_fim" name="data_fim" class="form-control form-control-sm tabular-nums" value="<?= htmlspecialchars($dataFim, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Botão Filtrar -->
                <div class="col-12 col-md-1 d-grid">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold text-white shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>

            <!-- Acessos Rápidos e Filtros de Severidade Integrados -->
            <div class="mt-3 pt-2 border-top d-flex gap-2 align-items-center flex-wrap">
                <span class="text-xs text-muted fw-bold text-uppercase d-flex align-items-center">
                    <i class="fa-solid fa-bolt text-warning me-1"></i> Filtros Rápidos:
                </span>
                <a href="<?= BASE_URL ?>/relatorios/logs.php" class="badge rounded-pill <?= (empty($filtroRapido) && empty($filtroAcao) && !$filtroCritico && $ordem !== 'severidade') ? 'bg-primary text-white' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-3">
                    Todos os Logs
                </a>
                <a href="<?= BASE_URL ?>/relatorios/logs.php?critico=1" class="badge rounded-pill <?= ($filtroCritico || $filtroRapido === 'criticas') ? 'bg-danger text-white' : 'bg-light text-danger border' ?> text-decoration-none py-2 px-3">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Ações Críticas
                </a>
                <a href="<?= BASE_URL ?>/relatorios/logs.php?quick=vendas" class="badge rounded-pill <?= $filtroRapido === 'vendas' ? 'bg-success text-white' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-3">
                    <i class="fa-solid fa-cash-register me-1"></i> Vendas
                </a>
                <a href="<?= BASE_URL ?>/relatorios/logs.php?quick=logins" class="badge rounded-pill <?= $filtroRapido === 'logins' ? 'bg-primary text-white' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-3">
                    <i class="fa-solid fa-shield-halved me-1"></i> Logins
                </a>
                <a href="<?= BASE_URL ?>/relatorios/logs.php?quick=estoque" class="badge rounded-pill <?= ($filtroRapido === 'estoque' || $filtroAcao === 'AJUSTE_ESTOQUE') ? 'bg-warning text-dark' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-3">
                    <i class="fa-solid fa-boxes-packing me-1"></i> Estoque
                </a>
                <a href="<?= BASE_URL ?>/relatorios/logs.php?acao=FALHA_LOGIN" class="badge rounded-pill <?= $filtroAcao === 'FALHA_LOGIN' ? 'bg-danger text-white' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-3">
                    <i class="fa-solid fa-shield-virus me-1"></i> Falhas de Login
                </a>
                <a href="<?= BASE_URL ?>/relatorios/logs.php?acao=CONFIGURACAO_ALTERADA" class="badge rounded-pill <?= $filtroAcao === 'CONFIGURACAO_ALTERADA' ? 'bg-dark text-white' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-3">
                    <i class="fa-solid fa-sliders me-1"></i> Configurações
                </a>
                <a href="<?= BASE_URL ?>/relatorios/logs.php?ordem=severidade" class="badge rounded-pill <?= $ordem === 'severidade' ? 'bg-dark text-white' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-3 ms-auto">
                    <i class="fa-solid fa-arrow-down-wide-short me-1"></i> Ordenar por Severidade
                </a>
            </div>
        </div>
    </div>

    <!-- ══ TABELA DE REGISTROS DE AUDITORIA ════════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="so-card-title text-dark m-0">
                    <i class="fa-solid fa-list-ul text-primary me-2"></i>Trilha de Auditoria
                </h5>
                <small class="text-muted">Exibindo <span class="tabular-nums"><?= count($logs) ?></span> de <span class="tabular-nums"><?= $totalRegistros == 1 ? '1 evento registrado' : number_format($totalRegistros, 0, ',', '.') . ' eventos registrados' ?></span></small>
            </div>
            <div class="text-muted text-xs">
                Página <strong class="text-dark tabular-nums"><?= $pagina ?></strong> de <strong class="text-dark tabular-nums"><?= $totalPaginas ?></strong>
            </div>
        </div>
        <div class="so-card-body p-0">
            <?php if (empty($logs)): ?>
                <div class="p-4">
                    <?= render_empty_state('Nenhum log encontrado', 'Nenhum registro de auditoria corresponde aos critérios de busca selecionados.', BASE_URL . '/relatorios/logs.php') ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light text-uppercase text-xs fw-bold text-muted border-bottom">
                            <tr>
                                <th style="width: 70px;" class="text-center">#ID</th>
                                <th style="width: 150px;">Data & Hora</th>
                                <th style="width: 170px;">Operador</th>
                                <th style="width: 160px;">Ação</th>
                                <th style="width: 120px;">Tabela</th>
                                <th>Descrição da Operação</th>
                                <th style="width: 120px;" class="text-center">IP Origem</th>
                                <th style="width: 100px;" class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): 
                                $badgeInfo = $getBadgeAcao($log['acao']);
                                $dt = !empty($log['data_log']) ? date('d/m/Y H:i:s', strtotime($log['data_log'])) : '-';
                                $logJson = htmlspecialchars(json_encode([
                                    'id'          => (int)$log['id'],
                                    'data_hora'   => $dt,
                                    'username'    => $log['username'],
                                    'perfil'      => $log['perfil'],
                                    'acao'        => $log['acao'],
                                    'acao_label'  => $badgeInfo['label'],
                                    'badge_class' => $badgeInfo['class'],
                                    'badge_icon'  => $badgeInfo['icon'],
                                    'tabela'      => $log['tabela_afetada'] ?? 'N/A',
                                    'descricao'   => $log['descricao'] ?? '',
                                    'ip'          => $log['ip_usuario'] ?? '127.0.0.1',
                                    'avatar'      => strtoupper(substr($log['username'], 0, 2))
                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr>
                                    <!-- ID do Log -->
                                    <td class="text-center text-muted fw-bold tabular-nums">
                                        #<?= (int)$log['id'] ?>
                                    </td>

                                    <!-- Data e Hora -->
                                    <td class="text-nowrap tabular-nums text-dark">
                                        <?= $dt ?>
                                    </td>

                                    <!-- Operador / Usuário -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle bg-light border text-primary">
                                                <?= strtoupper(substr($log['username'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block text-truncate" style="max-width: 120px;"><?= htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <small class="text-muted text-xxs text-uppercase fw-semibold d-block"><?= htmlspecialchars($log['perfil'], ENT_QUOTES, 'UTF-8') ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Ação Auditada com Badge -->
                                    <td>
                                        <span class="badge <?= $badgeInfo['class'] ?> d-inline-flex align-items-center gap-1 px-2 py-1 font-monospace text-xs shadow-xs">
                                            <i class="<?= $badgeInfo['icon'] ?>"></i>
                                            <?= htmlspecialchars($log['acao'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>

                                    <!-- Tabela Afetada -->
                                    <td>
                                        <?php if (!empty($log['tabela_afetada'])): ?>
                                            <span class="font-monospace text-dark text-xs"><?= htmlspecialchars($log['tabela_afetada'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php else: ?>
                                            <span class="text-muted text-xs">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Descrição do Log -->
                                    <td class="text-break">
                                        <span class="text-dark fw-normal text-break"><?= htmlspecialchars($log['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>

                                    <!-- Endereço IP do Usuário -->
                                    <td class="text-center">
                                        <span class="font-monospace text-muted text-xs tabular-nums"><?= htmlspecialchars($log['ip_usuario'] ?? '127.0.0.1', ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>

                                    <!-- Botão Ação: Detalhes -->
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary text-white shadow-xs px-2 py-1" 
                                                data-log='<?= $logJson ?>' 
                                                onclick="abrirDetalhesLog(this)" 
                                                title="Inspecionar detalhes profundos deste log"
                                                aria-label="Ver detalhes do log #<?= (int)$log['id'] ?>">
                                            <i class="fa-solid fa-eye me-1"></i> Detalhes
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ══ PAGINAÇÃO FUNCIONAL ═════════════════════════════════════════ -->
                <?php if ($totalPaginas > 1): ?>
                    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="text-muted text-xs">
                            Mostrando <strong class="tabular-nums"><?= $offset + 1 ?></strong> até <strong class="tabular-nums"><?= min($offset + $itensPorPag, $totalRegistros) ?></strong> de <strong class="tabular-nums"><?= $totalRegistros == 1 ? '1 registro' : number_format($totalRegistros, 0, ',', '.') . ' registros' ?></strong>
                        </div>
                        <nav aria-label="Paginação de logs">
                            <ul class="pagination pagination-sm m-0 gap-1">
                                <!-- Botão Primeira Página -->
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $buildPaginationUrl(1) ?>" title="Primeira Página">
                                        <i class="fa-solid fa-angles-left"></i>
                                    </a>
                                </li>

                                <!-- Botão Anterior -->
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $buildPaginationUrl($pagina - 1) ?>" title="Página Anterior">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </a>
                                </li>

                                <!-- Janela de Páginas Numeradas -->
                                <?php
                                $janelaInicio = max(1, $pagina - 2);
                                $janelaFim    = min($totalPaginas, $pagina + 2);
                                for ($i = $janelaInicio; $i <= $janelaFim; $i++):
                                ?>
                                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                        <a class="page-link tabular-nums" href="<?= $buildPaginationUrl($i) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Botão Próxima -->
                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $buildPaginationUrl($pagina + 1) ?>" title="Próxima Página">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </li>

                                <!-- Botão Última Página -->
                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $buildPaginationUrl($totalPaginas) ?>" title="Última Página">
                                        <i class="fa-solid fa-angles-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══ MODAL BOOTSTRAP DE INSPEÇÃO PROFUNDA DE LOGS (#modalDetalhesLog) ═══ -->
<div class="modal fade" id="modalDetalhesLog" tabindex="-1" aria-labelledby="modalDetalhesLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-sm" style="border: 1px solid #cbd5e1; border-radius: 8px;">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header bg-white border-bottom py-3 align-items-center" style="border-color: #cbd5e1 !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle bg-primary text-white">
                        <i class="fa-solid fa-shield-halved fs-xs"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0 text-dark" id="modalDetalhesLogLabel">
                            Inspeção de Log de Auditoria <span id="modalLogId" class="badge bg-secondary text-white font-monospace text-xs ms-1">#0</span>
                        </h5>
                        <small class="text-muted fs-xs">Registro imutável da trilha de eventos do ERP MrStock</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <!-- Corpo do Modal -->
            <div class="modal-body p-4 bg-light">
                <!-- Grid de Metadados -->
                <div class="row g-3 mb-3">
                    <!-- Data & Hora -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="bg-white p-3 rounded border shadow-xs h-100">
                            <span class="text-muted text-uppercase fw-bold text-xxs d-block mb-1">
                                <i class="fa-regular fa-clock me-1 text-primary"></i>Data & Hora
                            </span>
                            <div class="fw-bold text-dark font-monospace" id="modalLogDataHora">-</div>
                        </div>
                    </div>

                    <!-- Operador / Sessão -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="bg-white p-3 rounded border shadow-xs h-100">
                            <span class="text-muted text-uppercase fw-bold text-xxs d-block mb-1">
                                <i class="fa-solid fa-user me-1 text-primary"></i>Operador / Sessão
                            </span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div id="modalLogAvatar" class="avatar-circle bg-light border text-primary">
                                    --
                                </div>
                                <div>
                                    <div id="modalLogUsername" class="fw-bold text-dark text-truncate" style="max-width: 140px;">-</div>
                                    <span id="modalLogPerfil" class="badge bg-light text-secondary border text-xxs text-uppercase">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- IP Origem -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="bg-white p-3 rounded border shadow-xs h-100">
                            <span class="text-muted text-uppercase fw-bold text-xxs d-block mb-1">
                                <i class="fa-solid fa-network-wired me-1 text-primary"></i>Endereço IP
                            </span>
                            <div class="mt-1">
                                <div id="modalLogIp" class="font-monospace text-dark text-xs bg-light px-2 py-1 rounded border text-break tabular-nums" style="word-break: break-all;">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ação Executada -->
                    <div class="col-12 col-sm-6 col-md-6">
                        <div class="bg-white p-3 rounded border shadow-xs h-100">
                            <span class="text-muted text-uppercase fw-bold text-xxs d-block mb-1">
                                <i class="fa-solid fa-tag me-1 text-primary"></i>Ação Executada
                            </span>
                            <div id="modalLogAcaoBadge" class="mt-1">
                                <span class="badge bg-secondary text-white font-monospace text-xs px-2 py-1">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela Afetada -->
                    <div class="col-12 col-sm-6 col-md-6">
                        <div class="bg-white p-3 rounded border shadow-xs h-100">
                            <span class="text-muted text-uppercase fw-bold text-xxs d-block mb-1">
                                <i class="fa-solid fa-database me-1 text-primary"></i>Tabela Afetada
                            </span>
                            <div id="modalLogTabela" class="mt-1">
                                <span class="badge bg-light text-dark border font-monospace text-xs px-2 py-1">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descrição Completa e Payload -->
                <div class="bg-white p-3 rounded border shadow-xs">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted text-uppercase fw-bold text-xxs">
                            <i class="fa-solid fa-align-left me-1 text-primary"></i>Descrição Completa & Payload
                        </span>
                        <button type="button" id="btnCopiarDescricao" class="btn btn-sm btn-secondary text-white py-0 px-2 shadow-xs" style="font-size: 0.75rem;" onclick="copiarDescricaoLog(this)" title="Copiar descrição do log">
                            <i class="fa-solid fa-copy me-1"></i> Copiar
                        </button>
                    </div>
                    <div id="modalLogDescricao" class="p-3 bg-light rounded border font-monospace text-dark text-break" style="font-size: 0.85rem; max-height: 220px; overflow-y: auto; white-space: pre-wrap; line-height: 1.5;">
                        -
                    </div>
                </div>
            </div>

            <!-- Rodapé do Modal -->
            <div class="modal-footer bg-white px-4 py-3 d-flex justify-content-between">
                <span class="text-muted text-xs">
                    <i class="fa-solid fa-lock text-success me-1"></i> Trilha gravada no banco de dados MrStock
                </span>
                <button type="button" class="btn btn-secondary text-white fw-semibold shadow-xs" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ JAVASCRIPT VANILLA DE INSPEÇÃO PROFUNDA ═══════════════════════════════ -->
<script>
function abrirDetalhesLog(elementOrData) {
    let data = {};
    if (elementOrData instanceof HTMLElement) {
        try {
            data = JSON.parse(elementOrData.getAttribute('data-log') || '{}');
        } catch (e) {
            console.error("Erro ao analisar dados do log:", e);
        }
    } else if (typeof elementOrData === 'string') {
        try {
            data = JSON.parse(elementOrData);
        } catch (e) {
            console.error("Erro ao converter string em objeto de log:", e);
        }
    } else if (typeof elementOrData === 'object' && elementOrData !== null) {
        data = elementOrData;
    }

    document.getElementById('modalLogId').textContent = '#' + (data.id || '0');
    document.getElementById('modalLogDataHora').textContent = data.data_hora || '-';
    document.getElementById('modalLogAvatar').textContent = data.avatar || '--';
    document.getElementById('modalLogUsername').textContent = data.username || 'Sistema';
    document.getElementById('modalLogPerfil').textContent = data.perfil || 'sistema';
    document.getElementById('modalLogIp').textContent = data.ip || '127.0.0.1';

    // Tabela Afetada
    const modalLogTabela = document.getElementById('modalLogTabela');
    if (data.tabela && data.tabela !== 'N/A') {
        modalLogTabela.innerHTML = '<span class="badge bg-light text-dark border font-monospace text-xs px-2 py-1"><i class="fa-solid fa-table me-1 text-muted"></i>' + escapeHtml(data.tabela) + '</span>';
    } else {
        modalLogTabela.innerHTML = '<span class="text-muted text-xs">—</span>';
    }

    // Badge Ação
    const badgeClass = data.badge_class || 'bg-secondary text-white';
    const badgeIcon = data.badge_icon || 'fa-solid fa-shield';
    const acaoText = data.acao || '-';
    document.getElementById('modalLogAcaoBadge').innerHTML = 
        '<span class="badge ' + badgeClass + ' d-inline-flex align-items-center gap-1 px-2 py-1 font-monospace text-xs shadow-xs">' +
        '<i class="' + badgeIcon + '"></i> ' + escapeHtml(acaoText) +
        '</span>';

    // Descrição
    document.getElementById('modalLogDescricao').textContent = data.descricao || 'Nenhuma descrição detalhada registrada.';

    // Instanciar e exibir modal Bootstrap 5
    const modalEl = document.getElementById('modalDetalhesLog');
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
    }
}

function copiarDescricaoLog(btn) {
    const texto = document.getElementById('modalLogDescricao').textContent;
    const button = btn || document.getElementById('btnCopiarDescricao');
    const originalHtml = button ? button.innerHTML : '';

    const notifySuccess = () => {
        if (button) {
            button.innerHTML = '<i class="fa-solid fa-check me-1"></i> Copiado!';
            button.classList.remove('btn-secondary');
            button.classList.add('btn-success');
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-secondary');
            }, 2000);
        }
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(texto).then(notifySuccess).catch(() => {
            fallbackCopy(texto, notifySuccess);
        });
    } else {
        fallbackCopy(texto, notifySuccess);
    }
}

function fallbackCopy(text, cb) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        if (cb) cb();
    } catch (err) {}
    document.body.removeChild(ta);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
