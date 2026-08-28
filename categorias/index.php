<?php
$pageTitle  = 'MrStock ERP - Gestão de Categorias';
$activePage = 'categorias';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Apenas Admin
$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

$stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM produtos p WHERE p.categoria_id = c.id) as qtd_produtos FROM categorias c ORDER BY c.nome ASC");
$categorias = $stmt->fetchAll();
$totalCategorias = count($categorias);

$editCategoria = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmtEdit->execute([$_GET['edit']]);
    $editCategoria = $stmtEdit->fetch();
}

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-tags text-primary me-2"></i>Gestão de Categorias</h2>
        <p class="text-muted m-0">Cadastre e organize as categorias do catálogo de produtos.</p>
    </div>
    <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCategoria" onclick="clearForm()">
        <i class="fas fa-plus-circle me-1"></i> Nova Categoria
    </button>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <strong>Sucesso!</strong> Registro de categoria salvo no banco de dados. 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['msg'] == 'erro'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <strong>Erro!</strong> Não foi possível salvar a categoria. 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ BARRA DE LIVE SEARCH ═════════════════════════════════════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-body p-3">
            <div class="so-search-box w-100" style="max-width:100%;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="liveSearchCategorias" class="form-control" placeholder="Filtrar categorias ao vivo por nome ou descrição..." onkeyup="filtrarCategorias(this)">
            </div>
        </div>
    </div>

    <!-- ══ TABELA MODULAR DE CATEGORIAS ═════════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-folder-tree text-primary"></i> Categorias Cadastradas</h5>
            <span class="so-badge so-badge-primary"><?= $totalCategorias ?> categorias</span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaCategorias">
                    <thead>
                        <tr>
                            <th width="8%">ID</th>
                            <th width="35%">Nome da Categoria</th>
                            <th width="35%">Descrição</th>
                            <th width="16%" class="text-center">Mix Produtos</th>
                            <th width="6%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalCategorias > 0): ?>
                            <?php foreach ($categorias as $c): ?>
                            <tr class="linha-categoria">
                                <td class="fw-bold text-muted font-monospace">#<?= str_pad((string)$c['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($c['nome']) ?></strong>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($c['descricao'] ?: 'Sem descrição informada') ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?= (int)$c['qtd_produtos'] ?> itens</span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ações">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="font-size:0.85rem;">
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/categorias/index.php?edit=<?= $c['id'] ?>">
                                                    <i class="fas fa-edit text-primary me-2"></i> Editar Categoria
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/produtos/index.php?categoria_id=<?= $c['id'] ?>">
                                                    <i class="fas fa-boxes-stacked text-info me-2"></i> Ver Produtos Vinculados
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="<?= BASE_URL ?>/categorias/functions.php?tipo=categoria" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria? Os produtos vinculados não serão apagados, apenas perderão o vínculo.')" class="m-0">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="acao" value="deletar">
                                                    <input type="hidden" name="id"   value="<?= $c['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger py-1">
                                                        <i class="fas fa-trash-alt me-2"></i> Excluir Categoria
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-tags fs-1 d-block mb-3 opacity-50"></i>
                                    Nenhuma categoria cadastrada ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Categoria -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--mr-radius);">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalCategoriaLabel"><i class="fas fa-tag text-primary me-2"></i> <?= $editCategoria ? 'Editar Categoria' : 'Nova Categoria' ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="window.location='<?= BASE_URL ?>/categorias/index.php'"></button>
            </div>
            <form action="<?= BASE_URL ?>/categorias/functions.php?tipo=categoria" method="POST">
                <?= csrf_input() ?>
                <div class="modal-body bg-light p-4">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id"   id="cat_id" value="<?= $editCategoria ? $editCategoria['id'] : '' ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome da Categoria <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome" id="cat_nome" value="<?= $editCategoria ? htmlspecialchars($editCategoria['nome']) : '' ?>" required placeholder="Ex: Papéis & Cadernos">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descrição (Opcional)</label>
                        <textarea class="form-control" name="descricao" id="cat_descricao" rows="3" placeholder="Breve resumo dos tipos de produtos inclusos nesta categoria..."><?= $editCategoria ? htmlspecialchars($editCategoria['descricao'] ?? '') : '' ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" onclick="window.location='<?= BASE_URL ?>/categorias/index.php'">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm"><i class="fas fa-save me-1"></i> <?= $editCategoria ? 'Atualizar' : 'Cadastrar' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filtrarCategorias(input) {
    const termo = input.value.toLowerCase().trim();
    const linhas = document.querySelectorAll('#tabelaCategorias tbody .linha-categoria');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}

function clearForm() {
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_nome').value = '';
    document.getElementById('cat_descricao').value = '';
    document.getElementById('modalCategoriaLabel').innerHTML = '<i class="fas fa-tag text-primary me-2"></i> Nova Categoria';
}

<?php if ($editCategoria): ?>
window.onload = () => new bootstrap.Modal(document.getElementById('modalCategoria')).show();
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
