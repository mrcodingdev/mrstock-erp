<?php
$pageTitle  = 'Gerador de Etiquetas';
$activePage = 'etiquetas';

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/barcode_helper.php';

// RBAC: Apenas Administrador pode acessar o gerador de etiquetas
require_admin();

// ── 1. Buscar Categorias para Filtro ─────────────────────────────────────────
$stmtCat = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
$categorias = $stmtCat ? $stmtCat->fetchAll(PDO::FETCH_ASSOC) : [];

// ── 2. Ingestão de Filtros via GET ───────────────────────────────────────────
$buscaFiltro = trim($_GET['busca'] ?? '');
$catFiltro   = filter_var($_GET['categoria_id'] ?? '', FILTER_VALIDATE_INT);
if ($catFiltro !== false && $catFiltro <= 0) {
    $catFiltro = null;
}

$where = ["p.status = 'ativo'"];
$params = [];

if (!empty($catFiltro)) {
    $where[] = "(p.categoria_id = :cat_id OR p.categoria = (SELECT nome FROM categorias WHERE id = :cat_id_sub))";
    $params[':cat_id']     = $catFiltro;
    $params[':cat_id_sub'] = $catFiltro;
}

if (!empty($buscaFiltro)) {
    $where[] = "(p.nome LIKE :busca OR p.codigo_de_barra LIKE :busca OR CAST(p.id AS CHAR) LIKE :busca)";
    $params[':busca'] = "%{$buscaFiltro}%";
}

$whereSql = implode(' AND ', $where);

// ── 3. Query Principal de Produtos ───────────────────────────────────────────
$sql = "SELECT p.id, p.nome, p.preco_venda, p.codigo_de_barra, p.quantidade, c.nome AS categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE {$whereSql} 
        ORDER BY p.nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── 4. Processamento da Geração de Etiquetas (POST) ──────────────────────────
$etiquetasParaGerar = [];
$isPost = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['imprimir_etiquetas']));

if ($isPost) {
    csrf_verify();
    $itensSelecionados = $_POST['produtos_sel'] ?? [];
    $qtds = $_POST['qtd_etiquetas'] ?? [];

    if (!empty($itensSelecionados)) {
        $cleanIds = array_filter(array_map('intval', (array)$itensSelecionados));
        if (!empty($cleanIds)) {
            $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
            $stmtSel = $pdo->prepare("SELECT p.id, p.nome, p.preco_venda, p.codigo_de_barra, c.nome AS categoria_nome 
                                      FROM produtos p 
                                      LEFT JOIN categorias c ON p.categoria_id = c.id 
                                      WHERE p.id IN ($placeholders)
                                      ORDER BY p.nome ASC");
            $stmtSel->execute($cleanIds);
            $prodsMap = [];
            foreach ($stmtSel->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $prodsMap[(int)$row['id']] = $row;
            }

            foreach ($itensSelecionados as $prodId) {
                $prodId = (int)$prodId;
                if (isset($prodsMap[$prodId])) {
                    $p = $prodsMap[$prodId];
                    $qtdCopias = max(1, min(50, (int)($qtds[$prodId] ?? 1)));
                    $barcodeFinal = !empty($p['codigo_de_barra']) ? $p['codigo_de_barra'] : str_pad((string)$p['id'], 8, '0', STR_PAD_LEFT);
                    for ($k = 0; $k < $qtdCopias; $k++) {
                        $etiquetasParaGerar[] = [
                            'id'              => $p['id'],
                            'nome'            => $p['nome'],
                            'preco'           => (float)$p['preco_venda'],
                            'codigo_de_barra' => $barcodeFinal,
                            'categoria'       => $p['categoria_nome'] ?? 'Geral'
                        ];
                    }
                }
            }
        }
    }
}

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-body">
    <!-- Cabeçalho do Módulo (Ocultado na impressão) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="fas fa-barcode text-primary me-2"></i> Gerador & Impressão de Etiquetas</h4>
            <small class="text-muted">Geração de etiquetas térmicas e folhas A4 com código de barras vetorial SVG puro.</small>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="<?= BASE_URL ?>/produtos/index.php" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Voltar aos Produtos
            </a>
            <?php if (!empty($etiquetasParaGerar)): ?>
            <button type="button" class="btn btn-success fw-bold shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir Folha (<?= count($etiquetasParaGerar) ?> <?= count($etiquetasParaGerar) === 1 ? 'etiqueta' : 'etiquetas' ?>)
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Painel de Filtros Acessível (no-print) -->
    <div class="so-card mb-4 no-print">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-filter text-primary me-1"></i> Filtros de Busca</h5>
        </div>
        <div class="so-card-body">
            <form method="GET" action="<?= BASE_URL ?>/produtos/etiquetas.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="buscaEtq" class="form-label small fw-semibold text-secondary mb-1">Buscar por Nome, Cód. Barras ou ID</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" id="buscaEtq" name="busca" class="form-control border-start-0" placeholder="Digite para filtrar produtos..." value="<?= htmlspecialchars($buscaFiltro) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="categoriaIdEtq" class="form-label small fw-semibold text-secondary mb-1">Categoria de Produto</label>
                    <select id="categoriaIdEtq" name="categoria_id" class="form-select">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($catFiltro === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill shadow-sm">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <?php if (!empty($buscaFiltro) || !empty($catFiltro)): ?>
                    <a href="<?= BASE_URL ?>/produtos/etiquetas.php" class="btn btn-secondary shadow-sm" title="Limpar Filtros">
                        <i class="fas fa-undo me-1"></i> Limpar
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Painel de Seleção de Produtos para Etiquetas (no-print) -->
    <div class="so-card mb-4 no-print">
        <div class="so-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="so-card-title m-0">
                <i class="fas fa-list-check text-primary me-1"></i> 1. Selecionar Produtos e Quantidades
            </h5>
            <span class="badge bg-light text-secondary border fw-semibold tabular-nums">
                <?= count($produtos) === 1 ? '1 produto listado' : count($produtos) . ' produtos listados' ?>
            </span>
        </div>
        <div class="so-card-body">
            <?php if (empty($produtos)): ?>
                <?= render_empty_state('Nenhum produto encontrado', 'Não encontramos produtos ativos com os filtros selecionados.', BASE_URL . '/produtos/etiquetas.php') ?>
            <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/produtos/etiquetas.php<?= (!empty($catFiltro) || !empty($buscaFiltro)) ? '?' . http_build_query(array_filter(['categoria_id' => $catFiltro, 'busca' => $buscaFiltro])) : '' ?>" id="formEtiquetas">
                <?= csrf_input() ?>
                <input type="hidden" name="imprimir_etiquetas" value="1">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkTodos" onchange="toggleSelectAll(this)">
                        <label class="form-check-label fw-semibold text-secondary" for="checkTodos">
                            Selecionar Todos os Produtos Listados
                        </label>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="qtdGlobal" class="small text-muted mb-0">Definir Qtd para todos:</label>
                        <input type="number" id="qtdGlobal" class="form-control form-control-sm text-center tabular-nums" style="width:70px;" value="1" min="1" max="50">
                        <button type="button" class="btn btn-secondary btn-sm shadow-sm" onclick="aplicarQtdGlobal()">
                            <i class="fas fa-check me-1"></i> Aplicar
                        </button>
                    </div>
                </div>

                <div class="table-responsive border rounded" style="max-height: 360px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 so-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th scope="col" style="width: 50px;" class="text-center">Sel.</th>
                                <th scope="col">Produto</th>
                                <th scope="col" style="width: 200px;">Cód. Barras</th>
                                <th scope="col" style="width: 140px;" class="text-end">Preço Unit.</th>
                                <th scope="col" style="width: 130px;" class="text-center">Qtd. Etiquetas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $p): 
                                $codBarras = !empty($p['codigo_de_barra']) ? $p['codigo_de_barra'] : str_pad((string)$p['id'], 8, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="produtos_sel[]" value="<?= $p['id'] ?>" class="form-check-input item-check" aria-label="Selecionar produto <?= htmlspecialchars($p['nome']) ?>">
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($p['nome']) ?></div>
                                    <small class="text-muted"><i class="fas fa-tag me-1 text-2xs"></i><?= htmlspecialchars($p['categoria_nome'] ?? 'Geral') ?></small>
                                </td>
                                <td>
                                    <code class="text-dark bg-light px-2 py-1 rounded border font-monospace tabular-nums"><?= htmlspecialchars($codBarras) ?></code>
                                </td>
                                <td class="text-end fw-bold text-dark tabular-nums">
                                    R$ <?= number_format((float)$p['preco_venda'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <input type="number" name="qtd_etiquetas[<?= $p['id'] ?>]" class="form-control form-control-sm text-center mx-auto qtd-input tabular-nums" value="1" min="1" max="50" style="width: 75px;" aria-label="Quantidade de etiquetas para <?= htmlspecialchars($p['nome']) ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="fas fa-tags me-2"></i> Gerar Visualização de Etiquetas
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ ÁREA DE IMPRESSÃO DAS ETIQUETAS (VISÍVEL EM TELA E IMPRESSORA) ══════ -->
    <?php if (!empty($etiquetasParaGerar)): ?>
    <div class="so-card">
        <div class="so-card-header no-print d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="so-card-title m-0">
                <i class="fas fa-eye text-success me-1"></i> 2. Pré-visualização da Grade de Etiquetas (<?= count($etiquetasParaGerar) ?> <?= count($etiquetasParaGerar) === 1 ? 'unidade' : 'unidades' ?>)
            </h5>
            <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir Agora
            </button>
        </div>
        <div class="so-card-body p-3">
            <div class="label-sheet-grid">
                <?php foreach ($etiquetasParaGerar as $etq): ?>
                <div class="label-item-card">
                    <div class="d-flex justify-content-between align-items-center w-100 mb-1" style="font-size:0.6875rem;">
                        <span class="fw-bold text-uppercase" style="color:var(--mr-bg-primary); letter-spacing: 0.5px;">MrStock ERP</span>
                        <span class="text-muted text-truncate" style="max-width: 110px;"><?= htmlspecialchars($etq['categoria']) ?></span>
                    </div>
                    <div class="label-prod-name" title="<?= htmlspecialchars($etq['nome']) ?>">
                        <?= htmlspecialchars($etq['nome']) ?>
                    </div>
                    <div class="my-1 d-flex justify-content-center w-100">
                        <?= gerarBarcodeSVG($etq['codigo_de_barra'], 170, 42, true) ?>
                    </div>
                    <div class="label-prod-price tabular-nums">
                        R$ <?= number_format($etq['preco'], 2, ',', '.') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php elseif ($isPost): ?>
    <div class="alert alert-warning no-print d-flex align-items-center gap-2 shadow-sm" role="alert">
        <i class="fas fa-triangle-exclamation fs-5 text-warning"></i>
        <div>
            <strong>Atenção:</strong> Nenhum produto foi selecionado para gerar etiquetas. Por favor, marque ao menos um produto na tabela acima antes de clicar em <em>Gerar Visualização</em>.
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleSelectAll(master) {
    document.querySelectorAll('.item-check').forEach(chk => {
        chk.checked = master.checked;
    });
}

function aplicarQtdGlobal() {
    const val = Math.max(1, Math.min(50, parseInt(document.getElementById('qtdGlobal').value, 10) || 1));
    document.getElementById('qtdGlobal').value = val;
    document.querySelectorAll('.qtd-input').forEach(inp => {
        inp.value = val;
    });
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
