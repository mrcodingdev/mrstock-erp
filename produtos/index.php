<?php
/**
 * MrStock ERP - Gestão de Estoque & Produtos com Filtros Diretos em 1 Linha,
 * Paginação Parametrizada e Design System SalesOps
 */
$pageTitle  = 'MrStock ERP - Estoque & Produtos';
$activePage = 'produtos';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// ── 1. Ingestão de Parâmetros de Filtro via GET ─────────────────────────────
$statusFiltro     = trim($_GET['status'] ?? '');
$buscaFiltro      = trim($_GET['busca'] ?? '');
$categoriaFiltro  = filter_var($_GET['categoria_id'] ?? '', FILTER_VALIDATE_INT);
if ($categoriaFiltro !== false && $categoriaFiltro <= 0) $categoriaFiltro = null;

$fornecedorFiltro = filter_var($_GET['fornecedor_id'] ?? '', FILTER_VALIDATE_INT);
if ($fornecedorFiltro !== false && $fornecedorFiltro <= 0) $fornecedorFiltro = null;

$limit = 10;
$page  = max(1, (int)($_GET['pagina'] ?? 1));

// ── 2. Construção Dinâmica da Query SQL com PDO ──────────────────────────────
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

// Filtro de Busca Textual
if (!empty($buscaFiltro)) {
    $where[] = "(p.nome LIKE :busca OR p.categoria LIKE :busca OR f.nome LIKE :busca OR p.codigo_de_barra LIKE :busca OR CAST(p.id AS CHAR) LIKE :busca)";
    $params[':busca'] = "%{$buscaFiltro}%";
}

$whereSql = implode(' AND ', $where);

// ── 3. Paginação e Contagem Total ───────────────────────────────────────────
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
    ORDER BY p.id DESC 
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produtos = $stmt->fetchAll();

// ── 4. Listas Auxiliares para Modais e Selects ──────────────────────────────
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
$hasActiveFilters = !empty($statusFiltro) || !empty($buscaFiltro) || !empty($categoriaFiltro) || !empty($fornecedorFiltro);

// Parâmetros Base para Paginação (sem parâmetro 'pagina')
$queryParamsBase = $_GET;
unset($queryParamsBase['pagina']);

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

    <!-- ══ BARRA DE FILTROS UNIFICADA EM 1 LINHA ═══════════════════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-body p-3">
            <form method="GET" action="<?= BASE_URL ?>/produtos/index.php" id="formFiltrosProdutos" class="row g-2 align-items-center">
                <!-- 1. Busca Textual -->
                <div class="col-12 col-lg-4">
                    <div class="so-search-box w-100" style="max-width:100%;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="busca" 
                               id="liveSearchProdutos" 
                               class="form-control shadow-none" 
                               placeholder="Buscar por nome, código, fornecedor, SKU..." 
                               value="<?= htmlspecialchars($buscaFiltro) ?>" 
                               onkeyup="filtrarAoVivo(this)" 
                               aria-label="Buscar produtos por nome, código de barras, SKU ou fornecedor">
                    </div>
                </div>

                <!-- 2. Categoria -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <select name="categoria_id" id="filtro_categoria_id" class="form-select shadow-none" onchange="this.form.submit()" aria-label="Filtrar por Categoria">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categoriasLista as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($categoriaFiltro === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 3. Fornecedor -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <select name="fornecedor_id" id="filtro_fornecedor_id" class="form-select shadow-none" onchange="this.form.submit()" aria-label="Filtrar por Fornecedor">
                        <option value="">Todos os Fornecedores</option>
                        <?php foreach ($fornecedoresLista as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($fornecedorFiltro === (int)$f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 4. Status do Estoque -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <select name="status" id="filtro_status" class="form-select shadow-none" onchange="this.form.submit()" aria-label="Filtrar por Status do Estoque e Validade">
                        <option value="" <?= ($statusFiltro === '') ? 'selected' : '' ?>>Status: Ativos</option>
                        <option value="em_estoque" <?= ($statusFiltro === 'em_estoque' || $statusFiltro === 'ativo') ? 'selected' : '' ?>>Em Estoque (&gt;0)</option>
                        <option value="baixo_estoque" <?= ($statusFiltro === 'baixo_estoque') ? 'selected' : '' ?>>Estoque Baixo</option>
                        <option value="sem_estoque" <?= ($statusFiltro === 'sem_estoque') ? 'selected' : '' ?>>Sem Estoque (0)</option>
                        <option value="vencendo_30" <?= ($statusFiltro === 'vencendo_30') ? 'selected' : '' ?>>Vencendo (&lt;30d)</option>
                        <option value="vencido" <?= ($statusFiltro === 'vencido') ? 'selected' : '' ?>>Vencidos</option>
                        <option value="inativo" <?= ($statusFiltro === 'inativo') ? 'selected' : '' ?>>Inativos</option>
                        <option value="todos" <?= ($statusFiltro === 'todos') ? 'selected' : '' ?>>Todos os Registros</option>
                    </select>
                </div>

                <!-- 5. Botões de Ação -->
                <div class="col-12 col-sm-6 col-lg-2 d-flex gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-primary fw-bold w-100 shadow-sm" title="Filtrar">
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
            <span class="so-badge so-badge-primary tabular-nums"><?= $totalRows === 1 ? '1 produto' : number_format($totalRows, 0, ',', '.') . ' produtos' ?></span>
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
