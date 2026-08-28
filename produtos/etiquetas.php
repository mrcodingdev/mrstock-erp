<?php
$pageTitle  = 'MrStock ERP - Impressão de Etiquetas de Código de Barras';
$activePage = 'etiquetas';

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/barcode_helper.php';

// RBAC: Apenas admin pode acessar
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

// Buscar categorias para filtro
$stmtCat = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Parâmetros de filtro
$catFiltro = (int)($_GET['categoria_id'] ?? 0);
$sql = "SELECT p.id, p.nome, p.preco_venda, p.codigo_de_barra, p.quantidade, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.status = 'ativo'";
$params = [];

if ($catFiltro > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $catFiltro;
}
$sql .= " ORDER BY p.nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se o usuário selecionou produtos específicos para impressão via POST/GET
$etiquetasParaGerar = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['imprimir_etiquetas'])) {
    $itensSelecionados = $_POST['produtos_sel'] ?? [];
    $qtds = $_POST['qtd_etiquetas'] ?? [];

    foreach ($itensSelecionados as $prodId) {
        $prodId = (int)$prodId;
        $qtdCopias = max(1, min(50, (int)($qtds[$prodId] ?? 1)));
        
        // Encontra o produto correspondente
        foreach ($produtos as $p) {
            if ((int)$p['id'] === $prodId) {
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
                break;
            }
        }
    }
}

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-body">
    <!-- Cabeçalho do Módulo (Ocultado na impressão) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="fas fa-barcode text-primary me-2"></i> Gerador & Impressão de Etiquetas</h4>
            <small class="text-muted">Geração de etiquetas térmicas e folhas A4 com código de barras vetorial SVG puro.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/produtos/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar aos Produtos
            </a>
            <?php if (!empty($etiquetasParaGerar)): ?>
            <button type="button" class="btn btn-success fw-bold shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir Folha (<?= count($etiquetasParaGerar) ?> etiquetas)
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Painel de Seleção de Produtos para Etiquetas (no-print) -->
    <div class="so-card no-print">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-filter text-primary"></i> 1. Selecionar Produtos e Quantidades</h5>
            <form method="GET" class="d-flex gap-2 align-items-center">
                <select name="categoria_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="0">Todas as Categorias</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $catFiltro === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="so-card-body">
            <form method="POST" action="<?= BASE_URL ?>/produtos/etiquetas.php<?= $catFiltro ? '?categoria_id='.$catFiltro : '' ?>" id="formEtiquetas">
                <?= csrf_input() ?>
                <input type="hidden" name="imprimir_etiquetas" value="1">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkTodos" onchange="toggleSelectAll(this)">
                        <label class="form-check-label fw-bold text-secondary" for="checkTodos">
                            Selecionar Todos os Produtos Listados
                        </label>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">Definir Qtd para todos:</span>
                        <input type="number" id="qtdGlobal" class="form-control form-control-sm text-center" style="width:70px;" value="1" min="1" max="50">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="aplicarQtdGlobal()">Aplicar</button>
                    </div>
                </div>

                <div class="table-responsive border rounded" style="max-height: 320px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 so-table">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">Sel.</th>
                                <th width="45%">Produto</th>
                                <th width="20%">Cód. Barras</th>
                                <th width="15%">Preço</th>
                                <th width="15%" class="text-center">Qtd. Etiquetas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $p): 
                                $codBarras = !empty($p['codigo_de_barra']) ? $p['codigo_de_barra'] : str_pad((string)$p['id'], 8, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="produtos_sel[]" value="<?= $p['id'] ?>" class="form-check-input item-check">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['nome']) ?></strong>
                                    <small class="text-muted d-block"><?= htmlspecialchars($p['categoria_nome'] ?? 'Geral') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($codBarras) ?></span>
                                </td>
                                <td class="fw-bold text-success">
                                    R$ <?= number_format((float)$p['preco_venda'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <input type="number" name="qtd_etiquetas[<?= $p['id'] ?>]" class="form-control form-control-sm text-center mx-auto qtd-input" value="1" min="1" max="50" style="width: 75px;">
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
        </div>
    </div>

    <!-- ══ ÁREA DE IMPRESSÃO DAS ETIQUETAS (VISÍVEL EM TELA E IMPRESSORA) ══════ -->
    <?php if (!empty($etiquetasParaGerar)): ?>
    <div class="so-card">
        <div class="so-card-header no-print">
            <h5 class="so-card-title"><i class="fas fa-eye text-success"></i> 2. Pré-visualização da Grade de Etiquetas (<?= count($etiquetasParaGerar) ?> unidades)</h5>
            <button type="button" class="btn btn-success btn-sm fw-bold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir Agora
            </button>
        </div>
        <div class="so-card-body p-2">
            <div class="label-sheet-grid">
                <?php foreach ($etiquetasParaGerar as $etq): ?>
                <div class="label-item-card">
                    <div class="d-flex justify-content-between align-items-center w-100 mb-1" style="font-size:0.65rem;">
                        <span class="fw-bold text-uppercase" style="color:var(--mr-bg-primary);">MrStock ERP</span>
                        <span class="text-muted"><?= htmlspecialchars($etq['categoria']) ?></span>
                    </div>
                    <div class="label-prod-name" title="<?= htmlspecialchars($etq['nome']) ?>">
                        <?= htmlspecialchars($etq['nome']) ?>
                    </div>
                    <div class="my-1">
                        <?= gerarBarcodeSVG($etq['codigo_de_barra'], 170, 42, true) ?>
                    </div>
                    <div class="label-prod-price">
                        R$ <?= number_format($etq['preco'], 2, ',', '.') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
    <div class="alert alert-warning no-print">
        <i class="fas fa-exclamation-circle me-2"></i> Nenhum produto foi selecionado para gerar etiquetas.
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
    const val = parseInt(document.getElementById('qtdGlobal').value) || 1;
    document.querySelectorAll('.qtd-input').forEach(inp => {
        inp.value = val;
    });
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
