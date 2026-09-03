<?php
$pageTitle  = 'Fornecedores';
$activePage = 'fornecedores';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Proteção extra: Apenas Admin
require_admin();

$stmt = $pdo->query("
    SELECT f.*, 
           (SELECT COUNT(*) FROM produtos p WHERE p.fornecedor_id = f.id AND p.status = 'ativo') as qtd_produtos, 
           (SELECT COALESCE(SUM(quantidade), 0) FROM produtos p WHERE p.fornecedor_id = f.id AND p.status = 'ativo') as total_itens 
    FROM fornecedores f 
    ORDER BY f.nome ASC
");
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalFornecedores = count($fornecedores);

// KPIs para Bento Grid Stat Cards
$fornecedoresAtivos = count(array_filter($fornecedores, fn($f) => ($f['status'] ?? '') === 'ativo'));
$totalProdutosVinculados = (int)array_sum(array_column($fornecedores, 'qtd_produtos'));

$editFornecedor = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
    $stmtEdit->execute([$_GET['edit']]);
    $editFornecedor = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-truck text-primary me-2"></i>Gestão de Fornecedores</h2>
        <p class="text-muted m-0">Administre parceiros comerciais, contatos e catálogo de produtos vinculados.</p>
    </div>
    <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFornecedor" onclick="clearForm()">
        <i class="fas fa-plus-circle me-1"></i> Adicionar Fornecedor
    </button>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border border-success mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>Sucesso!</strong> Registro do fornecedor salvo no banco de dados. 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] == 'inativado'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border border-warning mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i> <strong>Atenção!</strong> Fornecedor possui produtos cadastrados e foi alterado para <strong>Inativo</strong>. 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <?php if ($_GET['erro'] === 'nome_obrigatorio'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i> <strong>Razão Social Obrigatória:</strong> Informe o nome do fornecedor ou razão social.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'email_invalido'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i> <strong>E-mail Inválido:</strong> Informe um endereço de e-mail válido (ex: contato@fornecedor.com.br).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ STAT CARDS (BENTO GRID SALESOPS) ══════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total de Fornecedores -->
        <div class="col-12 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Total de Fornecedores</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $totalFornecedores ?></h3>
                        <small class="text-muted"><?= $totalFornecedores === 1 ? '1 parceiro cadastrado' : "$totalFornecedores parceiros cadastrados" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fas fa-truck"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Fornecedores Ativos -->
        <div class="col-12 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Fornecedores Ativos</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $fornecedoresAtivos ?></h3>
                        <small class="text-muted">Parceiros homologados</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Mix de Produtos Vinculados -->
        <div class="col-12 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Mix de Produtos Vinculados</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $totalProdutosVinculados ?></h3>
                        <small class="text-muted">Itens catalogados</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--warning">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ BARRA DE LIVE SEARCH ═════════════════════════════════════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-body p-3">
            <label for="liveSearchFornecedores" class="form-label fw-bold text-dark text-xs mb-1">Buscar Fornecedores</label>
            <div class="so-search-box w-100" style="max-width:100%;">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <input type="text" 
                       id="liveSearchFornecedores" 
                       class="form-control" 
                       placeholder="Filtrar fornecedores ao vivo por razão social, CNPJ, e-mail ou contato..." 
                       onkeyup="filtrarFornecedores(this)"
                       aria-label="Filtrar fornecedores ao vivo por razão social, CNPJ, e-mail ou contato">
            </div>
        </div>
    </div>

    <!-- ══ TABELA MODULAR DE FORNECEDORES ═══════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-building text-primary me-2"></i>Fornecedores Cadastrados</h5>
            <span class="so-badge so-badge-primary tabular-nums">
                <?= $totalFornecedores === 1 ? '1 fornecedor cadastrado' : "$totalFornecedores fornecedores cadastrados" ?>
            </span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaFornecedores">
                    <thead>
                        <tr>
                            <th scope="col" width="8%">ID</th>
                            <th scope="col" width="30%">Razão Social &amp; CNPJ</th>
                            <th scope="col" width="24%">Contato / WhatsApp</th>
                            <th scope="col" width="14%" class="text-center">Mix Produtos</th>
                            <th scope="col" width="12%" class="text-center">Estoque Total</th>
                            <th scope="col" width="8%" class="text-center">Status</th>
                            <th scope="col" width="4%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalFornecedores > 0): ?>
                            <?php foreach ($fornecedores as $f):
                                $badgeStatus = ($f['status'] ?? '') === 'ativo' 
                                    ? '<span class="so-badge so-badge-success">Ativo</span>' 
                                    : '<span class="so-badge so-badge-danger">Inativo</span>';
                                $zapLimpo = preg_replace('/[^0-9]/', '', $f['telefone'] ?? '');
                                $qtdProds = (int)($f['qtd_produtos'] ?? 0);
                                $totalItens = (int)($f['total_itens'] ?? 0);
                            ?>
                            <tr class="linha-fornecedor">
                                <td class="fw-bold text-muted font-monospace tabular-nums">#<?= str_pad((string)$f['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($f['nome']) ?></strong>
                                    <small class="text-muted">
                                        <i class="far fa-id-badge me-1"></i>CNPJ: <span class="tabular-nums"><?= htmlspecialchars(formatar_cpf_cnpj($f['cnpj'])) ?></span>
                                        &nbsp;|&nbsp;<i class="far fa-envelope me-1"></i><?= htmlspecialchars($f['email'] ?: 'Sem e-mail') ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-dark tabular-nums"><i class="fas fa-phone text-muted me-1 small"></i> <?= htmlspecialchars(formatar_telefone($f['telefone'])) ?></span>
                                        <?php if (!empty($zapLimpo) && strlen($zapLimpo) >= 10): ?>
                                            <a href="https://wa.me/55<?= $zapLimpo ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp" title="Conversar no WhatsApp" aria-label="Conversar no WhatsApp com <?= htmlspecialchars($f['nome']) ?>">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($f['contato'])): ?>
                                        <div class="small text-muted mt-1"><i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($f['contato']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border tabular-nums fw-semibold">
                                        <?= $qtdProds === 1 ? '1 produto' : "$qtdProds produtos" ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark tabular-nums">
                                        <?= $totalItens === 1 ? '1 unidade' : "$totalItens unidades" ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $badgeStatus ?></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ações para <?= htmlspecialchars($f['nome']) ?>" aria-label="Ações para <?= htmlspecialchars($f['nome']) ?>">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border py-2" style="font-size:0.85rem; border-color:#cbd5e1;">
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/fornecedores/index.php?edit=<?= $f['id'] ?>">
                                                    <i class="fas fa-edit text-primary me-2"></i> Editar Fornecedor
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/produtos/index.php?fornecedor_id=<?= $f['id'] ?>">
                                                    <i class="fas fa-boxes-stacked text-info me-2"></i> Ver Produtos no Estoque
                                                </a>
                                            </li>
                                            <?php if (!empty($zapLimpo)): ?>
                                            <li>
                                                <a class="dropdown-item py-1 text-success" href="https://wa.me/55<?= $zapLimpo ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fab fa-whatsapp text-success me-2"></i> Abrir WhatsApp
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="<?= BASE_URL ?>/fornecedores/functions.php?tipo=fornecedor" method="POST" onsubmit="return confirm('Deseja realmente inativar/excluir este fornecedor?')" class="m-0">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="acao" value="deletar">
                                                    <input type="hidden" name="id"   value="<?= $f['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger py-1">
                                                        <i class="fas fa-trash-alt me-2"></i> Excluir / Inativar
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
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-truck-ramp-box fs-1 d-block mb-3 opacity-50"></i>
                                    Ainda não há fornecedores cadastrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fornecedor -->
<div class="modal fade" id="modalFornecedor" tabindex="-1" aria-labelledby="modalFornecedorLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border shadow-lg" style="border-radius: var(--mr-radius); border-color: #cbd5e1 !important;">
            <div class="modal-header bg-white border-bottom py-3" style="border-color: #cbd5e1 !important;">
                <h5 class="modal-title text-dark fw-bold" id="modalFornecedorLabel">
                    <i class="fas fa-truck text-primary me-2"></i> <?= $editFornecedor ? 'Editar Fornecedor' : 'Cadastrar Fornecedor' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" onclick="window.location='<?= BASE_URL ?>/fornecedores/index.php'"></button>
            </div>
            <form action="<?= BASE_URL ?>/fornecedores/functions.php?tipo=fornecedor" method="POST" id="formFornecedor" onsubmit="return validarFormFornecedor()">
                <?= csrf_input() ?>
                <div class="modal-body bg-light p-4">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id"   id="forn_id" value="<?= $editFornecedor ? $editFornecedor['id'] : '' ?>">
                    
                    <div class="card border shadow-sm mb-3" style="border-color: #cbd5e1 !important;">
                        <div class="card-body">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-building me-1"></i> Dados Cadastrais</h6>
                            <div class="mb-3">
                                <label for="forn_nome" class="form-label fw-bold">Razão Social / Nome Fantasia <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-none" name="nome" id="forn_nome" value="<?= $editFornecedor ? htmlspecialchars($editFornecedor['nome']) : '' ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="forn_cnpj" class="form-label fw-bold">CNPJ</label>
                                    <input type="text" class="form-control tabular-nums shadow-none" name="cnpj" id="forn_cnpj" placeholder="00.000.000/0001-00" maxlength="18" oninput="mascaraCpfCnpj(this)" value="<?= $editFornecedor ? htmlspecialchars(formatar_cpf_cnpj($editFornecedor['cnpj'])) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="forn_status" class="form-label fw-bold">Status</label>
                                    <select class="form-select shadow-none" name="status" id="forn_status">
                                        <option value="ativo"   <?= ($editFornecedor && $editFornecedor['status']=='ativo')   ? 'selected' : '' ?>>Ativo (Fornecendo)</option>
                                        <option value="inativo" <?= ($editFornecedor && $editFornecedor['status']=='inativo') ? 'selected' : '' ?>>Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="forn_telefone" class="form-label fw-bold">Telefone / WhatsApp</label>
                                    <input type="text" class="form-control tabular-nums shadow-none" name="telefone" id="forn_telefone" placeholder="(00) 00000-0000" maxlength="15" oninput="mascaraTelefone(this)" value="<?= $editFornecedor ? htmlspecialchars(formatar_telefone($editFornecedor['telefone'])) : '' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="forn_email" class="form-label fw-bold">E-mail</label>
                                    <input type="email" class="form-control shadow-none" name="email" id="forn_email" placeholder="vendas@fornecedor.com" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" title="Digite um formato de e-mail válido com domínio (ex: contato@fornecedor.com.br)" value="<?= $editFornecedor ? htmlspecialchars($editFornecedor['email']) : '' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="forn_contato" class="form-label fw-bold">Pessoa de Contato</label>
                                    <input type="text" class="form-control shadow-none" name="contato" id="forn_contato" placeholder="Ex: Representante Comercial" value="<?= $editFornecedor ? htmlspecialchars($editFornecedor['contato'] ?? '') : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-sm" style="border-color: #cbd5e1 !important;">
                        <div class="card-body">
                            <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-map-location-dot me-1"></i> Localização &amp; Faturamento</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="forn_cep" class="form-label fw-bold">CEP <small class="text-muted fw-normal">(Auto-busca)</small> <span id="forn_cep_feedback" class="ms-1"></span></label>
                                    <input type="text" class="form-control tabular-nums shadow-none" name="cep" id="forn_cep" placeholder="00000-000" maxlength="9" oninput="mascaraCepFornecedor(this)" onblur="buscarViaCepFornecedor(this)" onchange="buscarViaCepFornecedor(this)" value="<?= $editFornecedor ? htmlspecialchars(formatar_cep($editFornecedor['cep'] ?? '')) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="forn_endereco" class="form-label fw-bold">Logradouro</label>
                                    <input type="text" class="form-control shadow-none" name="endereco" id="forn_endereco" placeholder="Rua, Avenida..." value="<?= $editFornecedor ? htmlspecialchars($editFornecedor['endereco'] ?? '') : '' ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="forn_numero" class="form-label fw-bold">Nº</label>
                                    <input type="text" class="form-control tabular-nums shadow-none" name="numero" id="forn_numero" maxlength="10" placeholder="123" value="<?= $editFornecedor ? htmlspecialchars($editFornecedor['numero'] ?? '') : '' ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="forn_bairro" class="form-label fw-bold">Bairro</label>
                                    <input type="text" class="form-control shadow-none" name="bairro" id="forn_bairro" placeholder="Bairro" value="<?= $editFornecedor ? htmlspecialchars($editFornecedor['bairro'] ?? '') : '' ?>">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="forn_cidade" class="form-label fw-bold">Cidade</label>
                                    <input type="text" class="form-control shadow-none" name="cidade" id="forn_cidade" placeholder="Cidade" value="<?= $editFornecedor ? htmlspecialchars($editFornecedor['cidade'] ?? '') : '' ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="forn_estado" class="form-label fw-bold">UF</label>
                                    <select class="form-select shadow-none" name="estado" id="forn_estado">
                                        <?php
                                        $ufs = ['SP','AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SE','TO'];
                                        $ufSel = $editFornecedor ? strtoupper($editFornecedor['estado'] ?? 'SP') : 'SP';
                                        foreach ($ufs as $uf): ?>
                                            <option value="<?= $uf ?>" <?= ($ufSel === $uf) ? 'selected' : '' ?>><?= $uf ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-white p-3" style="border-color: #cbd5e1 !important;">
                    <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal" onclick="window.location='<?= BASE_URL ?>/fornecedores/index.php'">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm"><i class="fas fa-save me-1"></i> <?= $editFornecedor ? 'Salvar Alterações' : 'Cadastrar Fornecedor' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ══ MÁSCARAS DE ENTRADA DINÂMICAS (VANILLA JS) ══════════════════════════════
function mascaraTelefone(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 11) v = v.substring(0, 11);
    if (v.length > 10) {
        input.value = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    } else if (v.length > 5) {
        input.value = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
    } else if (v.length > 2) {
        input.value = v.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
    } else {
        input.value = v;
    }
}

function mascaraCpfCnpj(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 14) v = v.substring(0, 14);
    if (v.length <= 11) {
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    }
    input.value = v;
}

let viaCepFornecedorTimeout = null;

function mascaraCepFornecedor(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 8) v = v.substring(0, 8);
    if (v.length > 5) {
        input.value = v.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
    } else {
        input.value = v;
    }

    if (v.length === 8) {
        clearTimeout(viaCepFornecedorTimeout);
        viaCepFornecedorTimeout = setTimeout(() => buscarViaCepFornecedor(input), 120);
    }
}

function buscarViaCepFornecedor(input) {
    const rawCep = input.value.replace(/\D/g, '');
    const feedback = document.getElementById('forn_cep_feedback');
    if (rawCep.length !== 8) {
        if (feedback) feedback.innerHTML = '';
        return;
    }

    if (feedback) {
        feedback.innerHTML = '<span class="text-primary fw-semibold" style="font-size:0.75rem;"><i class="fas fa-spinner fa-spin me-1"></i> Localizando...</span>';
    }

    const urlLocal = '<?= BASE_URL ?>/inc/viacep.php?cep=' + encodeURIComponent(rawCep);

    fetch(urlLocal)
        .then(res => {
            if (!res.ok) throw new Error('Falha no proxy local');
            return res.json();
        })
        .then(data => {
            if (data && !data.erro) {
                aplicarEnderecoFornecedor(data.logradouro, data.bairro, data.localidade, data.uf);
            } else {
                return fetch(`https://viacep.com.br/ws/${rawCep}/json/`)
                    .then(r => r.json())
                    .then(d => {
                        if (d && !d.erro) {
                            aplicarEnderecoFornecedor(d.logradouro, d.bairro, d.localidade, d.uf);
                        } else {
                            if (feedback) {
                                feedback.innerHTML = '<span class="text-danger fw-bold" style="font-size:0.75rem;"><i class="fas fa-circle-xmark me-1"></i> CEP não encontrado</span>';
                            }
                        }
                    });
            }
        })
        .catch(err => {
            console.warn('Tentando fallback direto ViaCEP:', err);
            fetch(`https://viacep.com.br/ws/${rawCep}/json/`)
                .then(r => r.json())
                .then(d => {
                    if (d && !d.erro) {
                        aplicarEnderecoFornecedor(d.logradouro, d.bairro, d.localidade, d.uf);
                    } else {
                        if (feedback) {
                            feedback.innerHTML = '<span class="text-danger fw-bold" style="font-size:0.75rem;"><i class="fas fa-circle-xmark me-1"></i> CEP não encontrado</span>';
                        }
                    }
                })
                .catch(e => {
                    if (feedback) {
                        feedback.innerHTML = '<span class="text-muted fw-semibold" style="font-size:0.75rem;"><i class="fas fa-pencil me-1"></i> Preencha o endereço</span>';
                    }
                });
        });
}

function aplicarEnderecoFornecedor(logradouro, bairro, cidade, uf) {
    const endEl = document.getElementById('forn_endereco');
    const baiEl = document.getElementById('forn_bairro');
    const cidEl = document.getElementById('forn_cidade');
    const estEl = document.getElementById('forn_estado');
    const numEl = document.getElementById('forn_numero');
    const feedback = document.getElementById('forn_cep_feedback');

    if (endEl) { endEl.value = logradouro || ''; destacarCampoFornecedor(endEl); }
    if (baiEl) { baiEl.value = bairro || ''; destacarCampoFornecedor(baiEl); }
    if (cidEl) { cidEl.value = cidade || ''; destacarCampoFornecedor(cidEl); }
    if (estEl && uf) { estEl.value = uf.toUpperCase(); destacarCampoFornecedor(estEl); }

    if (feedback) {
        feedback.innerHTML = '<span class="text-success fw-bold" style="font-size:0.75rem;"><i class="fas fa-check-circle me-1"></i> Localizado!</span>';
    }

    if (numEl && !numEl.value) {
        numEl.focus();
    }
}

function destacarCampoFornecedor(el) {
    el.style.transition = 'background-color 0.3s ease';
    el.style.backgroundColor = '#f0fdf4';
    setTimeout(() => {
        el.style.backgroundColor = '';
    }, 1800);
}

function validarFormFornecedor() {
    const nome = document.getElementById('forn_nome').value.trim();
    if (!nome) {
        alert('Por favor, informe a Razão Social ou Nome Fantasia.');
        document.getElementById('forn_nome').focus();
        return false;
    }
    const email = document.getElementById('forn_email').value.trim();
    if (email && !/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(email)) {
        alert('Por favor, informe um e-mail válido no formato contato@fornecedor.com.br');
        document.getElementById('forn_email').focus();
        return false;
    }
    return true;
}

function filtrarFornecedores(input) {
    const termo = input.value.toLowerCase().trim();
    const linhas = document.querySelectorAll('#tabelaFornecedores tbody .linha-fornecedor');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}

function clearForm() {
    ['forn_id','forn_nome','forn_cnpj','forn_telefone','forn_email','forn_contato','forn_cep','forn_endereco','forn_numero','forn_bairro','forn_cidade'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const feedback = document.getElementById('forn_cep_feedback');
    if (feedback) feedback.innerHTML = '';
    document.getElementById('forn_estado').value = 'SP';
    document.getElementById('forn_status').value = 'ativo';
    document.getElementById('modalFornecedorLabel').innerHTML = '<i class="fas fa-truck text-primary me-2"></i> Cadastrar Fornecedor';
}

<?php if ($editFornecedor): ?>
window.onload = () => new bootstrap.Modal(document.getElementById('modalFornecedor')).show();
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

