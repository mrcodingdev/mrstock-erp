<?php
/**
 * MrStock ERP - Gestão de Estoque & Produtos com Sistema Avançado de Filtros,
 * Chips Rápidos de Status, Paginação Parametrizada e Design System SalesOps
 */
$pageTitle  = 'MrStock ERP - Estoque & Produtos';
$activePage = 'produtos';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// ── 1. Ingestão de Parâmetros de Filtro via GET ─────────────────────────────
$statusFiltro     = trim($_GET['status'] ?? '');
$buscaFiltro      = trim($_GET['busca'] ?? '');
$categoriaFiltro  = filter_var($_GET['categoria_id'] ?? '', FILTER_VALIDATE_INT);
$fornecedorFiltro = filter_var($_GET['fornecedor_id'] ?? '', FILTER_VALIDATE_INT);
$precoMinRaw      = trim($_GET['preco_min'] ?? '');
$precoMaxRaw      = trim($_GET['preco_max'] ?? '');
$ordemFiltro      = trim($_GET['ordem'] ?? 'recentes');
$limitFiltro      = filter_var($_GET['itens_por_pagina'] ?? 10, FILTER_VALIDATE_INT);

// Sanitização de Preços
$precoMin = ($precoMinRaw !== '' && is_numeric(str_replace(',', '.', $precoMinRaw))) ? (float)str_replace(',', '.', $precoMinRaw) : null;
$precoMax = ($precoMaxRaw !== '' && is_numeric(str_replace(',', '.', $precoMaxRaw))) ? (float)str_replace(',', '.', $precoMaxRaw) : null;

// Validação de Itens por Página
$validLimits = [10, 25, 50, 100];
$limit = in_array($limitFiltro, $validLimits, true) ? $limitFiltro : 10;
$page  = max(1, (int)($_GET['pagina'] ?? 1));

// ── 2. Quick Summary Counts (Totais Rápidos em Query Única Otimizada) ─────────
$stmtQuick = $pdo->query("
    SELECT
        COUNT(*) AS todos,
        SUM(CASE WHEN status = 'ativo' AND quantidade > 0 THEN 1 ELSE 0 END) AS em_estoque,
        SUM(CASE WHEN status = 'ativo' AND quantidade <= estoque_minimo AND quantidade > 0 THEN 1 ELSE 0 END) AS baixo_estoque,
        SUM(CASE WHEN status = 'ativo' AND quantidade = 0 THEN 1 ELSE 0 END) AS sem_estoque,
        SUM(CASE WHEN status = 'ativo' AND validade IS NOT NULL AND validade >= CURDATE() AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS vencendo_30,
        SUM(CASE WHEN status = 'ativo' AND validade IS NOT NULL AND validade < CURDATE() THEN 1 ELSE 0 END) AS vencido,
        SUM(CASE WHEN status = 'inativo' THEN 1 ELSE 0 END) AS inativo
    FROM produtos
");
$quickCountsRow = $stmtQuick ? ($stmtQuick->fetch(PDO::FETCH_ASSOC) ?: []) : [];
$quickCounts = [
    'todos'         => (int)($quickCountsRow['todos'] ?? 0),
    'em_estoque'    => (int)($quickCountsRow['em_estoque'] ?? 0),
    'baixo_estoque' => (int)($quickCountsRow['baixo_estoque'] ?? 0),
    'sem_estoque'   => (int)($quickCountsRow['sem_estoque'] ?? 0),
    'vencendo_30'   => (int)($quickCountsRow['vencendo_30'] ?? 0),
    'vencido'       => (int)($quickCountsRow['vencido'] ?? 0),
    'inativo'       => (int)($quickCountsRow['inativo'] ?? 0),
];

// ── 3. Construção Dinâmica da Query SQL com PDO ──────────────────────────────
$where = ["1=1"];
$params = [];

// Filtro de Status
if ($statusFiltro === 'em_estoque' || $statusFiltro === 'ativo') {
    $where[] = "p.status = 'ativo' AND p.quantidade > 0";
} elseif ($statusFiltro === 'baixo_estoque') {
    $where[] = "p.status = 'ativo' AND p.quantidade <= p.estoque_minimo AND p.quantidade > 0";
} elseif ($statusFiltro === 'sem_estoque') {
    $where[] = "p.status = 'ativo' AND p.quantidade = 0";
} elseif ($statusFiltro === 'vencendo_30') {
    $where[] = "p.status = 'ativo' AND p.validade IS NOT NULL AND p.validade >= CURDATE() AND p.validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
} elseif ($statusFiltro === 'vencido') {
    $where[] = "p.status = 'ativo' AND p.validade IS NOT NULL AND p.validade < CURDATE()";
} elseif ($statusFiltro === 'inativo') {
    $where[] = "p.status = 'inativo'";
} elseif ($statusFiltro === 'todos') {
    // Exibe todos os registros (ativos e inativos)
} else {
    // Padrão de segurança: apenas produtos ativos
    $where[] = "p.status = 'ativo'";
}

// Filtro de Categoria
if ($categoriaFiltro) {
    $where[] = "(p.categoria_id = :cat_id OR p.categoria = (SELECT nome FROM categorias WHERE id = :cat_id_sub))";
    $params[':cat_id']     = $categoriaFiltro;
    $params[':cat_id_sub'] = $categoriaFiltro;
}

// Filtro de Fornecedor
if ($fornecedorFiltro) {
    $where[] = "p.fornecedor_id = :forn_id";
    $params[':forn_id'] = $fornecedorFiltro;
}

// Filtro de Faixa de Preço
if ($precoMin !== null) {
    $where[] = "p.preco_venda >= :preco_min";
    $params[':preco_min'] = $precoMin;
}
if ($precoMax !== null) {
    $where[] = "p.preco_venda <= :preco_max";
    $params[':preco_max'] = $precoMax;
}

// Filtro de Busca Textual
if (!empty($buscaFiltro)) {
    $where[] = "(p.nome LIKE :busca OR p.categoria LIKE :busca OR f.nome LIKE :busca OR p.codigo_de_barra LIKE :busca OR CAST(p.id AS CHAR) LIKE :busca)";
    $params[':busca'] = "%{$buscaFiltro}%";
}

$whereSql = implode(' AND ', $where);

// Mapeamento Seguro de Ordenação
$orderMap = [
    'recentes'       => 'p.id DESC',
    'antigos'        => 'p.id ASC',
    'nome_az'        => 'p.nome ASC',
    'nome_za'        => 'p.nome DESC',
    'menor_estoque'  => 'p.quantidade ASC, p.id DESC',
    'maior_estoque'  => 'p.quantidade DESC, p.id DESC',
    'menor_preco'    => 'p.preco_venda ASC',
    'maior_preco'    => 'p.preco_venda DESC',
    'validade_prox'  => 'CASE WHEN p.validade IS NULL THEN 1 ELSE 0 END, p.validade ASC, p.id DESC'
];
$orderBy = $orderMap[$ordemFiltro] ?? 'p.id DESC';

// ── 4. Paginação e Contagem Total ───────────────────────────────────────────
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM produtos p LEFT JOIN fornecedores f ON p.fornecedor_id = f.id WHERE $whereSql");
$stmtCount->execute($params);
$totalRows  = (int)$stmtCount->fetchColumn();
$totalPages = $limit > 0 ? (int)ceil($totalRows / $limit) : 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Query Principal de Produtos
$stmt = $pdo->prepare("
    SELECT p.*, f.nome as fornecedor_nome, c.nome as categoria_nome_rel
    FROM produtos p 
    LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE $whereSql 
    ORDER BY $orderBy 
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produtos = $stmt->fetchAll();

// ── 5. Listas Auxiliares para Modais e Selects ──────────────────────────────
$stmtForn = $pdo->query("SELECT id, nome FROM fornecedores WHERE status = 'ativo' ORDER BY nome ASC");
$fornecedoresLista = $stmtForn ? $stmtForn->fetchAll() : [];

$stmtCat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
$categoriasLista = $stmtCat ? $stmtCat->fetchAll() : [];

// Edição de Produto via Modal
$editProduto = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmtEdit->execute([$_GET['edit']]);
    $editProduto = $stmtEdit->fetch();
}

// Verificação de Filtros Ativos
$hasActiveFilters = !empty($statusFiltro) || !empty($buscaFiltro) || !empty($categoriaFiltro) || !empty($fornecedorFiltro) || $precoMin !== null || $precoMax !== null || ($ordemFiltro !== 'recentes' && !empty($ordemFiltro)) || ($limit !== 10);

// Parâmetros Base para Paginação e Chips (sem parâmetro 'pagina')
$queryParamsBase = $_GET;
unset($queryParamsBase['pagina']);

// Função auxiliar para gerar URLs dos Chips preservando outros filtros
$makeChipUrl = function(?string $targetStatus) use ($queryParamsBase): string {
    $params = $queryParamsBase;
    if ($targetStatus === null || $targetStatus === '') {
        unset($params['status']);
    } else {
        $params['status'] = $targetStatus;
    }
    return 'index.php?' . http_build_query($params);
};

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-boxes-stacked text-primary me-2"></i>Estoque & Produtos</h2>
        <p class="text-muted m-0">Cadastre itens, defina o estoque mínimo, preços e gere etiquetas de código de barras.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/produtos/etiquetas.php" class="btn btn-secondary fw-semibold">
            <i class="fas fa-barcode me-1"></i> Imprimir Etiquetas
        </a>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalProduto" onclick="clearForm()">
            <i class="fas fa-plus-circle me-1"></i> Adicionar Produto
        </button>
    </div>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border" role="alert">
        <i class="fas fa-check-circle me-2"></i> <strong>Sucesso!</strong> Registro de produto salvo no banco de dados.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'inativado'): ?>
    <div class="alert alert-warning alert-dismissible fade show shadow-sm border" role="alert">
        <i class="fas fa-info-circle me-2"></i> <strong>Produto Inativado!</strong> Como este produto já possui histórico de vendas ou compras, ele foi inativado para manter a integridade fiscal.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'reativado'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border" role="alert">
        <i class="fas fa-check-circle me-2"></i> <strong>Produto Reativado!</strong> O produto voltou a ficar ativo para vendas no PDV e consultas.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    <?php elseif (isset($_GET['erro']) && $_GET['erro'] == 'barcode_duplicado'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Erro de Cadastro:</strong> Este código de barras já está cadastrado em outro produto.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    <?php endif; ?>

    <!-- ══ 1. BARRA DE CHIPS RÁPIDOS DE STATUS (QUICK SUMMARY FILTER CHIPS) ════ -->
    <div class="category-chips-nav-wrapper mb-3">
        <div class="category-chips-bar" role="toolbar" aria-label="Filtros rápidos de status do estoque">
            <?php
            $chipsConfig = [
                ['key' => 'todos',         'label' => 'Todos',          'icon' => 'fas fa-layer-group',          'count' => $quickCounts['todos']],
                ['key' => 'em_estoque',    'label' => 'Em Estoque',     'icon' => 'fas fa-circle-check',         'count' => $quickCounts['em_estoque']],
                ['key' => 'baixo_estoque', 'label' => 'Estoque Baixo',  'icon' => 'fas fa-triangle-exclamation', 'count' => $quickCounts['baixo_estoque']],
                ['key' => 'sem_estoque',   'label' => 'Sem Estoque',    'icon' => 'fas fa-circle-xmark',         'count' => $quickCounts['sem_estoque']],
                ['key' => 'vencendo_30',   'label' => 'Vencendo (<30d)','icon' => 'fas fa-clock',                'count' => $quickCounts['vencendo_30']],
                ['key' => 'vencido',       'label' => 'Vencidos',       'icon' => 'fas fa-calendar-xmark',       'count' => $quickCounts['vencido']],
                ['key' => 'inativo',       'label' => 'Inativos',       'icon' => 'fas fa-ban',                  'count' => $quickCounts['inativo']],
            ];

            foreach ($chipsConfig as $chip):
                $isActive = ($statusFiltro === $chip['key']) || ($chip['key'] === 'todos' && $statusFiltro === 'todos') || ($chip['key'] === 'em_estoque' && $statusFiltro === 'ativo');
            ?>
                <a href="<?= $makeChipUrl($chip['key'] === 'todos' ? 'todos' : $chip['key']) ?>"
                   class="category-chip <?= $isActive ? 'active' : '' ?>"
                   aria-current="<?= $isActive ? 'true' : 'false' ?>"
                   title="Filtrar por: <?= htmlspecialchars($chip['label']) ?>">
                    <i class="<?= $chip['icon'] ?>"></i>
                    <span><?= htmlspecialchars($chip['label']) ?></span>
                    <span class="badge <?= $isActive ? 'bg-white text-dark' : 'bg-secondary text-white' ?> rounded-pill tabular-nums ms-1" style="font-size:0.7rem; padding: 2px 6px;">
                        <?= number_format($chip['count'], 0, ',', '.') ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ══ 2. CARD DE FILTROS AVANÇADOS (GRID BENTO 2 LINHAS) ═══════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="so-card-title m-0 text-sm">
                <i class="fas fa-filter text-primary"></i> Filtros de Pesquisa &amp; Catálogo
            </h6>
            <?php if ($hasActiveFilters): ?>
                <span class="badge bg-light text-primary border font-monospace text-xs">
                    <i class="fas fa-circle-dot me-1 text-success"></i>Filtros Ativos
                </span>
            <?php endif; ?>
        </div>
        <div class="so-card-body p-3">
            <form method="GET" action="<?= BASE_URL ?>/produtos/index.php" id="formFiltrosProdutos" class="row g-3">
                <!-- LINHA 1: Busca Textual + Categoria + Fornecedor + Status -->
                <div class="col-12 col-lg-4">
                    <label for="liveSearchProdutos" class="form-label fw-bold text-muted small mb-1">
                        <i class="fas fa-search me-1"></i> Busca Textual
                    </label>
                    <div class="position-relative">
                        <input type="text" 
                               name="busca" 
                               id="liveSearchProdutos" 
                               class="form-control form-control-sm ps-4" 
                               placeholder="Buscar por nome, código, fornecedor, SKU..." 
                               value="<?= htmlspecialchars($buscaFiltro) ?>" 
                               onkeyup="filtrarAoVivo(this)" 
                               aria-label="Buscar produtos por nome, código de barras, SKU ou fornecedor">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small pointer-events-none"></i>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label for="filtro_categoria_id" class="form-label fw-bold text-muted small mb-1">
                        <i class="fas fa-tag me-1"></i> Categoria
                    </label>
                    <select name="categoria_id" id="filtro_categoria_id" class="form-select form-select-sm" aria-label="Filtrar por Categoria">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categoriasLista as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($categoriaFiltro === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label for="filtro_fornecedor_id" class="form-label fw-bold text-muted small mb-1">
                        <i class="fas fa-truck me-1"></i> Fornecedor
                    </label>
                    <select name="fornecedor_id" id="filtro_fornecedor_id" class="form-select form-select-sm" aria-label="Filtrar por Fornecedor">
                        <option value="">Todos os Fornecedores</option>
                        <?php foreach ($fornecedoresLista as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($fornecedorFiltro === (int)$f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="filtro_status" class="form-label fw-bold text-muted small mb-1">
                        <i class="fas fa-sliders me-1"></i> Status / Estoque
                    </label>
                    <select name="status" id="filtro_status" class="form-select form-select-sm" aria-label="Filtrar por Status do Estoque e Validade">
                        <option value="" <?= ($statusFiltro === '') ? 'selected' : '' ?>>Ativos (Padrão)</option>
                        <option value="em_estoque" <?= ($statusFiltro === 'em_estoque' || $statusFiltro === 'ativo') ? 'selected' : '' ?>>Em Estoque (&gt;0)</option>
                        <option value="baixo_estoque" <?= ($statusFiltro === 'baixo_estoque') ? 'selected' : '' ?>>Estoque Baixo</option>
                        <option value="sem_estoque" <?= ($statusFiltro === 'sem_estoque') ? 'selected' : '' ?>>Sem Estoque (0)</option>
                        <option value="vencendo_30" <?= ($statusFiltro === 'vencendo_30') ? 'selected' : '' ?>>Vencendo (&lt;30d)</option>
                        <option value="vencido" <?= ($statusFiltro === 'vencido') ? 'selected' : '' ?>>Vencidos</option>
                        <option value="inativo" <?= ($statusFiltro === 'inativo') ? 'selected' : '' ?>>Inativos</option>
                        <option value="todos" <?= ($statusFiltro === 'todos') ? 'selected' : '' ?>>Todos os Registros</option>
                    </select>
                </div>

                <!-- LINHA 2: Faixa de Preço + Ordenação + Itens por Página + Botões -->
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-bold text-muted small mb-1">
                        <i class="fas fa-dollar-sign me-1"></i> Faixa de Preço de Venda (R$)
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted">De R$</span>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               name="preco_min" 
                               id="filtro_preco_min" 
                               class="form-control tabular-nums" 
                               placeholder="0,00" 
                               value="<?= $precoMin !== null ? htmlspecialchars((string)$precoMin) : '' ?>" 
                               aria-label="Preço mínimo em Reais">
                        <span class="input-group-text bg-light text-muted">Até R$</span>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               name="preco_max" 
                               id="filtro_preco_max" 
                               class="form-control tabular-nums" 
                               placeholder="Sem limite" 
                               value="<?= $precoMax !== null ? htmlspecialchars((string)$precoMax) : '' ?>" 
                               aria-label="Preço máximo em Reais">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label for="filtro_ordem" class="form-label fw-bold text-muted small mb-1">
                        <i class="fas fa-arrow-down-a-z me-1"></i> Ordenação
                    </label>
                    <select name="ordem" id="filtro_ordem" class="form-select form-select-sm" aria-label="Ordenar listagem de produtos">
                        <option value="recentes" <?= ($ordemFiltro === 'recentes') ? 'selected' : '' ?>>Mais Recentes (ID Desc)</option>
                        <option value="antigos" <?= ($ordemFiltro === 'antigos') ? 'selected' : '' ?>>Mais Antigos (ID Asc)</option>
                        <option value="nome_az" <?= ($ordemFiltro === 'nome_az') ? 'selected' : '' ?>>Nome (A - Z)</option>
                        <option value="nome_za" <?= ($ordemFiltro === 'nome_za') ? 'selected' : '' ?>>Nome (Z - A)</option>
                        <option value="menor_estoque" <?= ($ordemFiltro === 'menor_estoque') ? 'selected' : '' ?>>Menor Estoque</option>
                        <option value="maior_estoque" <?= ($ordemFiltro === 'maior_estoque') ? 'selected' : '' ?>>Maior Estoque</option>
                        <option value="menor_preco" <?= ($ordemFiltro === 'menor_preco') ? 'selected' : '' ?>>Menor Preço</option>
                        <option value="maior_preco" <?= ($ordemFiltro === 'maior_preco') ? 'selected' : '' ?>>Maior Preço</option>
                        <option value="validade_prox" <?= ($ordemFiltro === 'validade_prox') ? 'selected' : '' ?>>Validade Mais Próxima</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="filtro_itens_por_pagina" class="form-label fw-bold text-muted small mb-1">
                        <i class="fas fa-list-ol me-1"></i> Itens / Pág.
                    </label>
                    <select name="itens_por_pagina" id="filtro_itens_por_pagina" class="form-select form-select-sm tabular-nums" aria-label="Quantidade de itens por página">
                        <option value="10" <?= ($limit === 10) ? 'selected' : '' ?>>10 por pág.</option>
                        <option value="25" <?= ($limit === 25) ? 'selected' : '' ?>>25 por pág.</option>
                        <option value="50" <?= ($limit === 50) ? 'selected' : '' ?>>50 por pág.</option>
                        <option value="100" <?= ($limit === 100) ? 'selected' : '' ?>>100 por pág.</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary fw-bold w-100 shadow-sm" title="Aplicar Filtros">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <?php if ($hasActiveFilters): ?>
                    <a href="<?= BASE_URL ?>/produtos/index.php" class="btn btn-secondary px-3 shadow-sm" title="Limpar Filtros" aria-label="Limpar Filtros">
                        <i class="fas fa-undo"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ TABELA MODULAR DE PRODUTOS ══════════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <h5 class="so-card-title mb-0"><i class="fas fa-list text-primary me-2"></i>Catálogo Cadastrado</h5>
            <span class="so-badge so-badge-primary tabular-nums"><?= $totalRows === 1 ? '1 item' : $totalRows . ' itens' ?></span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaProdutos">
                    <thead>
                        <tr>
                            <th width="8%">Cód.</th>
                            <th width="28%">Produto / Categoria</th>
                            <th width="18%">Fornecedor</th>
                            <th width="14%" class="text-center">Estoque</th>
                            <th width="14%">Validade</th>
                            <th width="12%">Preço Venda</th>
                            <th width="6%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produtos) > 0): ?>
                            <?php foreach ($produtos as $p):
                                if ($p['status'] === 'inativo') {
                                    $badgeEstoque = '<span class="so-badge so-badge-danger">Inativo</span>';
                                } elseif ((int)$p['quantidade'] <= 0) {
                                    $badgeEstoque = '<span class="so-badge so-badge-danger">Sem Estoque</span>';
                                } elseif ((int)$p['quantidade'] <= (int)$p['estoque_minimo']) {
                                    $badgeEstoque = '<span class="so-badge so-badge-warning">Estoque Baixo</span>';
                                } else {
                                    $badgeEstoque = '<span class="so-badge so-badge-success">Em Estoque</span>';
                                }

                                $validadeHtml = '<span class="text-muted small"><i class="fas fa-minus"></i> N/A</span>';
                                if (!empty($p['validade'])) {
                                    $dv   = new DateTime($p['validade']);
                                    $hoje = new DateTime(date('Y-m-d'));
                                    $dias = (int)$hoje->diff($dv)->format('%r%a');
                                    if ($dias < 0) {
                                        $validadeHtml = '<span class="so-badge so-badge-danger"><i class="fas fa-times-circle me-1"></i> Vencido</span><br><small class="text-muted tabular-nums">'.date('d/m/Y',strtotime($p['validade'])).'</small>';
                                    } elseif ($dias <= 15) {
                                        $validadeHtml = '<span class="so-badge so-badge-danger tabular-nums">Faltam '.$dias.'d</span><br><small class="text-muted tabular-nums">'.date('d/m/Y',strtotime($p['validade'])).'</small>';
                                    } elseif ($dias <= 30) {
                                        $validadeHtml = '<span class="so-badge so-badge-warning tabular-nums">Faltam '.$dias.'d</span><br><small class="text-muted tabular-nums">'.date('d/m/Y',strtotime($p['validade'])).'</small>';
                                    } else {
                                        $validadeHtml = '<span class="text-secondary fw-semibold small tabular-nums"><i class="fas fa-calendar-check text-success me-1"></i>'.date('d/m/Y',strtotime($p['validade'])).'</span>';
                                    }
                                }
                                $catNomeExib = !empty($p['categoria_nome_rel']) ? $p['categoria_nome_rel'] : ($p['categoria'] ?: 'Geral');
                            ?>
                            <tr class="linha-produto">
                                <td class="fw-bold text-muted font-monospace tabular-nums">#<?= str_pad((string)$p['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($p['nome']) ?></strong>
                                    <small class="text-muted text-uppercase" style="font-size:11px;">
                                        <i class="fas fa-tag me-1 text-secondary"></i><?= htmlspecialchars($catNomeExib) ?>
                                        <?php if (!empty($p['codigo_de_barra'])): ?>
                                            &nbsp;|&nbsp;<i class="fas fa-barcode text-muted"></i> <span class="tabular-nums font-monospace"><?= htmlspecialchars($p['codigo_de_barra']) ?></span>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['fornecedor_nome'] ?? 'Sem Vínculo') ?>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark d-block tabular-nums"><?= (int)$p['quantidade'] ?> un</span>
                                    <?= $badgeEstoque ?>
                                </td>
                                <td><?= $validadeHtml ?></td>
                                <td class="fw-bold text-dark tabular-nums">
                                    R$ <?= number_format((float)$p['preco_venda'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <!-- Menu de Ações em Linha (3 Pontos) -->
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ações para <?= htmlspecialchars($p['nome']) ?>" title="Ações">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="font-size: 0.85rem;">
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/produtos/index.php?edit=<?= $p['id'] ?>">
                                                    <i class="fas fa-edit text-primary me-2"></i> Editar Produto
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/produtos/movimentacoes.php?busca=<?= urlencode($p['nome']) ?>">
                                                    <i class="fas fa-arrow-right-arrow-left text-info me-2"></i> Ver Movimentações
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/produtos/etiquetas.php">
                                                    <i class="fas fa-barcode text-success me-2"></i> Gerar Etiquetas
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <?php if (($p['status'] ?? 'ativo') === 'inativo'): ?>
                                                <form action="<?= BASE_URL ?>/produtos/functions.php?tipo=produto" method="POST" class="m-0">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="acao" value="reativar">
                                                    <input type="hidden" name="id"   value="<?= $p['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-success py-1">
                                                        <i class="fas fa-check-circle me-2"></i> Reativar Produto
                                                    </button>
                                                </form>
                                                <?php else: ?>
                                                <form action="<?= BASE_URL ?>/produtos/functions.php?tipo=produto" method="POST" onsubmit="return confirm('Deseja realmente inativar/excluir este produto?')" class="m-0">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="acao" value="deletar">
                                                    <input type="hidden" name="id"   value="<?= $p['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger py-1">
                                                        <i class="fas fa-trash-alt me-2"></i> Excluir / Inativar
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="p-0 border-0">
                                    <?= render_empty_state('Nenhum produto localizado', 'Não encontramos nenhum produto ativo para os filtros selecionados.', BASE_URL . '/produtos/index.php') ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ PAGINAÇÃO MODERNA INSTITUCIONAL ═════════════════════════ -->
        <?php
        $firstItem = $totalRows > 0 ? ($offset + 1) : 0;
        $lastItem  = min($offset + $limit, $totalRows);
        ?>
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <span class="text-muted small">
                    Exibindo <strong class="tabular-nums"><?= $firstItem ?></strong> a <strong class="tabular-nums"><?= $lastItem ?></strong> de <strong class="tabular-nums"><?= number_format($totalRows, 0, ',', '.') ?></strong> <?= $totalRows === 1 ? 'produto' : 'produtos' ?>
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação da listagem de produtos">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link tabular-nums" href="index.php?<?= http_build_query(array_merge($queryParamsBase, ['pagina' => $page - 1])) ?>" aria-label="Anterior">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        
                        <?php
                        $range = 2;
                        $startPage = max(1, $page - $range);
                        $endPage = min($totalPages, $page + $range);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link tabular-nums" href="index.php?' . http_build_query(array_merge($queryParamsBase, ['pagina' => 1])) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link tabular-nums" href="index.php?<?= http_build_query(array_merge($queryParamsBase, ['pagina' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link tabular-nums" href="index.php?' . http_build_query(array_merge($queryParamsBase, ['pagina' => $totalPages])) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link tabular-nums" href="index.php?<?= http_build_query(array_merge($queryParamsBase, ['pagina' => $page + 1])) ?>" aria-label="Próximo">
                                Próximo <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Produto -->
<div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border shadow-sm" style="border-radius: var(--mr-radius);">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalProdutoLabel"><i class="fas fa-box-open me-2 text-warning"></i> <?= $editProduto ? 'Editar Produto' : 'Cadastrar Produto' ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar" onclick="window.location='<?= BASE_URL ?>/produtos/index.php'"></button>
            </div>
            <form action="<?= BASE_URL ?>/produtos/functions.php?tipo=produto" method="POST">
                <?= csrf_input() ?>
                <div class="modal-body bg-light p-4">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id"   id="prod_id" value="<?= $editProduto ? $editProduto['id'] : '' ?>">
                    
                    <div class="card border shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Informações Básicas</h6>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="prod_nome" class="form-label fw-bold text-dark">Nome do Produto <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nome" id="prod_nome" value="<?= $editProduto ? htmlspecialchars($editProduto['nome']) : '' ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="prod_codigo_de_barra" class="form-label fw-bold text-dark">Código de Barras</label>
                                    <input type="text" class="form-control font-monospace tabular-nums" name="codigo_de_barra" id="prod_codigo_de_barra" placeholder="EAN-13 / CODE128" value="<?= $editProduto ? htmlspecialchars($editProduto['codigo_de_barra'] ?? '') : '' ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="prod_categoria_id" class="form-label fw-bold text-dark">Categoria</label>
                                    <select class="form-select" name="categoria_id" id="prod_categoria_id">
                                        <option value="">-- Selecione a Categoria --</option>
                                        <?php foreach ($categoriasLista as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= ($editProduto && $editProduto['categoria_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="categoria" id="prod_categoria_texto" value="<?= $editProduto ? htmlspecialchars($editProduto['categoria']) : 'Geral' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="prod_fornecedor_id" class="form-label fw-bold text-dark">Fornecedor Responsável</label>
                                    <select class="form-select" name="fornecedor_id" id="prod_fornecedor_id">
                                        <option value="">Nenhum / Sem Vínculo</option>
                                        <?php foreach ($fornecedoresLista as $forn): ?>
                                            <option value="<?= $forn['id'] ?>" <?= ($editProduto && $editProduto['fornecedor_id'] == $forn['id']) ? 'selected' : '' ?>><?= htmlspecialchars($forn['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="prod_validade" class="form-label fw-bold text-dark">Data de Validade</label>
                                    <input type="date" class="form-control tabular-nums" name="validade" id="prod_validade" min="2020-01-01" max="2099-12-31" value="<?= $editProduto ? $editProduto['validade'] : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-tags text-primary me-2"></i>Preços e Controle de Estoque</h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="prod_preco_compra" class="form-label fw-bold text-dark">Custo (R$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control tabular-nums" name="preco_compra" id="prod_preco_compra" value="<?= $editProduto ? $editProduto['preco_compra'] : '0.00' ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="prod_preco_venda" class="form-label fw-bold text-dark">Venda (R$) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control tabular-nums" name="preco_venda" id="prod_preco_venda" value="<?= $editProduto ? $editProduto['preco_venda'] : '0.00' ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="prod_quantidade" class="form-label fw-bold text-dark">Estoque Atual</label>
                                    <input type="number" step="1" min="0" class="form-control tabular-nums" name="quantidade" id="prod_quantidade" value="<?= $editProduto ? $editProduto['quantidade'] : '0' ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="prod_minimo" class="form-label fw-bold text-dark">Estoque Mínimo <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="0" class="form-control tabular-nums" name="estoque_minimo" id="prod_minimo" value="<?= $editProduto ? $editProduto['estoque_minimo'] : '0' ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" onclick="window.location='<?= BASE_URL ?>/produtos/index.php'">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm"><i class="fas fa-check-circle me-1"></i> <?= $editProduto ? 'Atualizar' : 'Cadastrar' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filtrarAoVivo(input) {
    const termo = input.value.toLowerCase().trim();
    const linhas = document.querySelectorAll('#tabelaProdutos tbody .linha-produto');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}

function clearForm() {
    ['prod_id','prod_nome','prod_validade','prod_codigo_de_barra'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('prod_categoria_id').value = '';
    document.getElementById('prod_categoria_texto').value = 'Geral';
    document.getElementById('prod_fornecedor_id').value = '';
    document.getElementById('prod_preco_compra').value  = '0.00';
    document.getElementById('prod_preco_venda').value   = '0.00';
    document.getElementById('prod_quantidade').value    = '0';
    document.getElementById('prod_minimo').value        = '5';
    document.getElementById('modalProdutoLabel').innerHTML = '<i class="fas fa-box-open me-2 text-warning"></i> Cadastrar Produto';
}

<?php if ($editProduto): ?>
window.onload = () => new bootstrap.Modal(document.getElementById('modalProduto')).show();
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
