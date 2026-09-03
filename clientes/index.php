<?php
$pageTitle  = 'Clientes';
$activePage = 'clientes';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Consulta completa de clientes com agregações de compras e faturamento
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM vendas v WHERE v.cliente_id = c.id) AS qtd_compras, 
           (SELECT COALESCE(SUM(total), 0) FROM vendas v WHERE v.cliente_id = c.id) AS total_compras 
    FROM clientes c 
    ORDER BY c.nome ASC
");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalClientes = count($clientes);

// KPIs para Bento Grid Stat Cards
$clientesAtivos = count(array_filter($clientes, fn($c) => ($c['status'] ?? '') === 'ativo'));
$faturamentoTotalClientes = (float)array_sum(array_column($clientes, 'total_compras'));

$editCliente = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmtEdit->execute([$_GET['edit']]);
    $editCliente = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-users text-primary me-2"></i>Gestão de Clientes</h2>
        <p class="text-muted m-0">Cadastre clientes, visualize histórico de compras e inicie contatos rapidamente.</p>
    </div>
    <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="clearForm()">
        <i class="fas fa-user-plus me-1"></i> Adicionar Cliente
    </button>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border border-success mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>Sucesso!</strong> Registro do cliente salvo com sucesso. 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['msg'] == 'inativado'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border border-warning mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i> <strong>Atenção!</strong> O cliente possui vendas vinculadas e foi alterado para <strong>Inativo</strong>. 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <?php if ($_GET['erro'] === 'nome_invalido'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i> <strong>Nome Inválido:</strong> O nome do cliente deve conter apenas letras e espaços (sem números).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'email_invalido'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border border-danger mb-3" role="alert">
            <i class="fas fa-circle-xmark me-2"></i> <strong>E-mail Inválido:</strong> Informe um endereço de e-mail válido com @ e extensão de domínio (ex: cliente@dominio.com).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ STAT CARDS (BENTO GRID SALESOPS) ══════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total de Clientes -->
        <div class="col-12 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Total de Clientes</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $totalClientes ?></h3>
                        <small class="text-muted"><?= $totalClientes === 1 ? '1 cliente cadastrado' : "$totalClientes clientes cadastrados" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Clientes Ativos -->
        <div class="col-12 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Clientes Ativos</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums"><?= $clientesAtivos ?></h3>
                        <small class="text-muted"><?= $clientesAtivos === 1 ? '1 cliente ativo' : "$clientesAtivos clientes ativos" ?></small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Faturamento Total dos Clientes -->
        <div class="col-12 col-md-4">
            <div class="so-card p-3 mb-0 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-bold text-xs d-block mb-1">Faturamento Total</span>
                        <h3 class="fw-bold text-dark m-0 tabular-nums">R$ <?= number_format($faturamentoTotalClientes, 2, ',', '.') ?></h3>
                        <small class="text-muted">Total em compras acumuladas</small>
                    </div>
                    <div class="kpi-icon-box kpi-icon-box--warning">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ BARRA DE LIVE SEARCH ═════════════════════════════════════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-body p-3">
            <label for="liveSearchClientes" class="form-label fw-bold text-dark text-xs mb-1">Buscar Clientes</label>
            <div class="so-search-box w-100" style="max-width:100%;">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <input type="text" 
                       id="liveSearchClientes" 
                       class="form-control" 
                       placeholder="Filtrar clientes ao vivo por nome, CPF/CNPJ, e-mail ou telefone..." 
                       onkeyup="filtrarClientes(this)"
                       aria-label="Filtrar clientes ao vivo por nome, CPF/CNPJ, e-mail ou telefone">
            </div>
        </div>
    </div>

    <!-- ══ TABELA MODULAR DE CLIENTES ═══════════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-address-book text-primary me-2"></i> Base de Clientes</h5>
            <span class="so-badge so-badge-primary tabular-nums">
                <?= $totalClientes === 1 ? '1 cliente cadastrado' : "$totalClientes clientes cadastrados" ?>
            </span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaClientes">
                    <thead>
                        <tr>
                            <th scope="col" width="8%">ID</th>
                            <th scope="col" width="32%">Nome &amp; Documentos</th>
                            <th scope="col" width="22%">Contato / WhatsApp</th>
                            <th scope="col" width="12%" class="text-center">Compras</th>
                            <th scope="col" width="14%" class="text-end pe-4">Total Gasto</th>
                            <th scope="col" width="8%" class="text-center">Status</th>
                            <th scope="col" width="4%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalClientes > 0): ?>
                            <?php foreach ($clientes as $c):
                                $zapNum = preg_replace('/[^0-9]/', '', $c['telefone'] ?? '');
                                $hasZap = !empty($zapNum) && strlen($zapNum) >= 10;
                                $badgeStatus = ($c['status'] ?? '') === 'ativo' 
                                    ? '<span class="so-badge so-badge-success">Ativo</span>' 
                                    : '<span class="so-badge so-badge-danger">Inativo</span>';
                            ?>
                            <tr class="linha-cliente">
                                <td class="fw-bold text-muted font-monospace tabular-nums">#<?= str_pad((string)$c['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($c['nome']) ?></strong>
                                    <small class="text-muted">
                                        <i class="far fa-id-card me-1"></i><span class="tabular-nums"><?= htmlspecialchars(formatar_cpf_cnpj($c['cpf_cnpj'])) ?></span>
                                        &nbsp;|&nbsp;<i class="far fa-envelope me-1"></i><?= htmlspecialchars($c['email'] ?: 'Sem e-mail') ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-dark tabular-nums"><i class="fas fa-phone text-muted me-1 small"></i> <?= htmlspecialchars(formatar_telefone($c['telefone'])) ?></span>
                                        <?php if ($hasZap): ?>
                                            <a href="https://wa.me/55<?= $zapNum ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp" title="Conversar no WhatsApp" aria-label="Conversar no WhatsApp com <?= htmlspecialchars($c['nome']) ?>">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 tabular-nums fw-semibold">
                                        <?= (int)$c['qtd_compras'] === 1 ? '1 compra' : (int)$c['qtd_compras'] . ' compras' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4 fw-bold text-dark tabular-nums">
                                    R$ <?= number_format((float)$c['total_compras'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center"><?= $badgeStatus ?></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="so-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ações para <?= htmlspecialchars($c['nome']) ?>" aria-label="Ações para <?= htmlspecialchars($c['nome']) ?>">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border py-2" style="font-size: 0.85rem; border-color: #cbd5e1;">
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/clientes/index.php?edit=<?= $c['id'] ?>">
                                                    <i class="fas fa-edit text-primary me-2"></i> Editar Cliente
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1" href="<?= BASE_URL ?>/vendas/historico.php?cliente_id=<?= $c['id'] ?>">
                                                    <i class="fas fa-receipt text-info me-2"></i> Ver Histórico
                                                </a>
                                            </li>
                                            <?php if ($hasZap): ?>
                                            <li>
                                                <a class="dropdown-item py-1 text-success" href="https://wa.me/55<?= $zapNum ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fab fa-whatsapp text-success me-2"></i> Abrir WhatsApp
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if (isset($_SESSION['user_perfil']) && $_SESSION['user_perfil'] === 'admin'): ?>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form method="POST" action="functions.php?tipo=cliente" onsubmit="return confirm('Deseja realmente excluir ou inativar este cliente?');">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                        <input type="hidden" name="acao" value="deletar">
                                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                                        <button type="submit" class="dropdown-item text-danger py-1">
                                                            <i class="fas fa-trash-alt me-2"></i> Excluir / Inativar
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fs-1 d-block mb-3 opacity-50"></i>
                                    Ainda não há clientes cadastrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border shadow-lg" style="border-radius: var(--mr-radius); border-color: #cbd5e1;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalClienteLabel">
                    <i class="fas fa-user-circle text-primary me-2"></i> <?= $editCliente ? 'Editar Cliente' : 'Cadastrar Cliente' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" onclick="window.location='<?= BASE_URL ?>/clientes/index.php'"></button>
            </div>
            <form action="<?= BASE_URL ?>/clientes/functions.php?tipo=cliente" method="POST" id="formCliente" onsubmit="return validarFormCliente()">
                <?= csrf_input() ?>
                <div class="modal-body bg-light p-4">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id"   id="cli_id" value="<?= $editCliente ? $editCliente['id'] : '' ?>">
                    
                    <div class="card border mb-3" style="border-color: #cbd5e1 !important;">
                        <div class="card-body">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-user me-1"></i> Dados Pessoais</h6>
                            <div class="mb-3">
                                <label for="cli_nome" class="form-label fw-bold">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nome" id="cli_nome" placeholder="Digite apenas o nome (sem números)" value="<?= $editCliente ? htmlspecialchars($editCliente['nome']) : '' ?>" oninput="filtrarApenasLetras(this)" required pattern="^[A-Za-zÀ-ÖØ-öø-ÿ\s]{3,}$" title="O nome deve conter pelo menos 3 caracteres e apenas letras e espaços.">
                                <div class="form-text text-muted" style="font-size:0.75rem;">Aceita exclusivamente letras e espaços (números são bloqueados).</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cli_telefone" class="form-label fw-bold">Telefone / WhatsApp</label>
                                    <input type="text" class="form-control tabular-nums" name="telefone" id="cli_telefone" placeholder="(11) 99999-9999" maxlength="15" oninput="mascaraTelefone(this)" value="<?= $editCliente ? htmlspecialchars(formatar_telefone($editCliente['telefone'])) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cli_status" class="form-label fw-bold">Status</label>
                                    <select class="form-select" name="status" id="cli_status">
                                        <option value="ativo"   <?= ($editCliente && ($editCliente['status'] ?? '') === 'ativo')   ? 'selected' : '' ?>>Ativo</option>
                                        <option value="inativo" <?= ($editCliente && ($editCliente['status'] ?? '') === 'inativo') ? 'selected' : '' ?>>Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cli_email" class="form-label fw-bold">E-mail</label>
                                    <input type="email" class="form-control tabular-nums" name="email" id="cli_email" placeholder="cliente@email.com" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" title="Digite um formato de e-mail válido com domínio (ex: usuario@dominio.com)" value="<?= $editCliente ? htmlspecialchars($editCliente['email']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cli_cpf_cnpj" class="form-label fw-bold">CPF / CNPJ</label>
                                    <input type="text" class="form-control tabular-nums" name="cpf_cnpj" id="cli_cpf_cnpj" placeholder="000.000.000-00 ou 00.000.000/0000-00" maxlength="18" oninput="mascaraCpfCnpj(this)" value="<?= $editCliente ? htmlspecialchars(formatar_cpf_cnpj($editCliente['cpf_cnpj'])) : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border" style="border-color: #cbd5e1 !important;">
                        <div class="card-body">
                            <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-map-marker-alt me-1"></i> Endereço de Entrega / Faturamento</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="cli_cep" class="form-label fw-bold">CEP <small class="text-muted fw-normal">(Auto-busca)</small> <span id="cli_cep_feedback" class="ms-1"></span></label>
                                    <input type="text" class="form-control tabular-nums" name="cep" id="cli_cep" placeholder="00000-000" maxlength="9" oninput="mascaraCep(this)" onblur="buscarViaCep(this)" onchange="buscarViaCep(this)" value="<?= $editCliente ? htmlspecialchars(formatar_cep($editCliente['cep'] ?? '')) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cli_endereco" class="form-label fw-bold">Logradouro</label>
                                    <input type="text" class="form-control" name="endereco" id="cli_endereco" placeholder="Rua, Avenida..." value="<?= $editCliente ? htmlspecialchars($editCliente['endereco'] ?? '') : '' ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="cli_numero" class="form-label fw-bold">Nº</label>
                                    <input type="text" class="form-control tabular-nums" name="numero" id="cli_numero" maxlength="10" placeholder="123" value="<?= $editCliente ? htmlspecialchars($editCliente['numero'] ?? '') : '' ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="cli_bairro" class="form-label fw-bold">Bairro</label>
                                    <input type="text" class="form-control" name="bairro" id="cli_bairro" placeholder="Bairro" value="<?= $editCliente ? htmlspecialchars($editCliente['bairro'] ?? '') : '' ?>">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="cli_cidade" class="form-label fw-bold">Cidade</label>
                                    <input type="text" class="form-control" name="cidade" id="cli_cidade" placeholder="Cidade" value="<?= $editCliente ? htmlspecialchars($editCliente['cidade'] ?? '') : '' ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="cli_estado" class="form-label fw-bold">UF</label>
                                    <select class="form-select" name="estado" id="cli_estado">
                                        <?php
                                        $ufs = ['SP','AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SE','TO'];
                                        $ufSel = $editCliente ? strtoupper($editCliente['estado'] ?? 'SP') : 'SP';
                                        foreach ($ufs as $uf): ?>
                                            <option value="<?= $uf ?>" <?= ($ufSel === $uf) ? 'selected' : '' ?>><?= $uf ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-white p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" onclick="window.location='<?= BASE_URL ?>/clientes/index.php'">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm"><i class="fas fa-save me-1"></i> <?= $editCliente ? 'Salvar Alterações' : 'Cadastrar Cliente' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ══ VALIDAÇÃO DE NOME (APENAS LETRAS E ESPAÇOS) ══════════════════════════════
function filtrarApenasLetras(input) {
    input.value = input.value.replace(/[^a-zA-ZÀ-ÖØ-öø-ÿ\s]/g, '');
}

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

let viaCepTimeout = null;

function mascaraCep(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 8) v = v.substring(0, 8);
    if (v.length > 5) {
        input.value = v.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
    } else {
        input.value = v;
    }

    if (v.length === 8) {
        clearTimeout(viaCepTimeout);
        viaCepTimeout = setTimeout(() => buscarViaCep(input), 120);
    }
}

function buscarViaCep(input) {
    const rawCep = input.value.replace(/\D/g, '');
    const feedback = document.getElementById('cli_cep_feedback');
    if (rawCep.length !== 8) {
        if (feedback) feedback.innerHTML = '';
        return;
    }

    if (feedback) {
        feedback.innerHTML = '<span class="text-primary fw-semibold" style="font-size:0.75rem;"><i class="fas fa-spinner fa-spin me-1"></i> Localizando...</span>';
    }

    // 1. Consulta via proxy interno local (Resolve bloqueios de CORS, SSL e Adblockers)
    const urlLocal = '<?= BASE_URL ?>/inc/viacep.php?cep=' + encodeURIComponent(rawCep);

    fetch(urlLocal)
        .then(res => {
            if (!res.ok) throw new Error('Falha no proxy local');
            return res.json();
        })
        .then(data => {
            if (data && !data.erro) {
                aplicarEnderecoCliente(data.logradouro, data.bairro, data.localidade, data.uf);
            } else {
                // Fallback secundário: consulta direta na nuvem caso o proxy retorne erro
                return fetch(`https://viacep.com.br/ws/${rawCep}/json/`)
                    .then(r => r.json())
                    .then(d => {
                        if (d && !d.erro) {
                            aplicarEnderecoCliente(d.logradouro, d.bairro, d.localidade, d.uf);
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
                        aplicarEnderecoCliente(d.logradouro, d.bairro, d.localidade, d.uf);
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

function aplicarEnderecoCliente(logradouro, bairro, cidade, uf) {
    const endEl = document.getElementById('cli_endereco');
    const baiEl = document.getElementById('cli_bairro');
    const cidEl = document.getElementById('cli_cidade');
    const estEl = document.getElementById('cli_estado');
    const numEl = document.getElementById('cli_numero');
    const feedback = document.getElementById('cli_cep_feedback');

    if (endEl) { endEl.value = logradouro || ''; destacarCampo(endEl); }
    if (baiEl) { baiEl.value = bairro || ''; destacarCampo(baiEl); }
    if (cidEl) { cidEl.value = cidade || ''; destacarCampo(cidEl); }
    if (estEl && uf) { estEl.value = uf.toUpperCase(); destacarCampo(estEl); }

    if (feedback) {
        feedback.innerHTML = '<span class="text-success fw-bold" style="font-size:0.75rem;"><i class="fas fa-check-circle me-1"></i> Localizado!</span>';
    }

    if (numEl && !numEl.value) {
        numEl.focus();
    }
}

function destacarCampo(el) {
    el.style.transition = 'background-color 0.3s ease';
    el.style.backgroundColor = '#f0fdf4';
    setTimeout(() => {
        el.style.backgroundColor = '';
    }, 1800);
}

function validarFormCliente() {
    const nome = document.getElementById('cli_nome').value.trim();
    if (/[0-9]/.test(nome)) {
        alert('O nome do cliente não pode conter números.');
        document.getElementById('cli_nome').focus();
        return false;
    }
    const email = document.getElementById('cli_email').value.trim();
    if (email && !/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(email)) {
        alert('Por favor, informe um e-mail válido no formato usuario@dominio.com');
        document.getElementById('cli_email').focus();
        return false;
    }
    return true;
}

function filtrarClientes(input) {
    const termo = input.value.toLowerCase().trim();
    const linhas = document.querySelectorAll('#tabelaClientes tbody .linha-cliente');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}

function clearForm() {
    ['cli_id','cli_nome','cli_telefone','cli_email','cli_cpf_cnpj','cli_cep','cli_endereco','cli_numero','cli_bairro','cli_cidade'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const feedback = document.getElementById('cli_cep_feedback');
    if (feedback) feedback.innerHTML = '';
    document.getElementById('cli_estado').value = 'SP';
    document.getElementById('cli_status').value = 'ativo';
    document.getElementById('modalClienteLabel').innerHTML = '<i class="fas fa-user-circle text-primary me-2"></i> Cadastrar Cliente';
}

<?php if ($editCliente): ?>
window.onload = () => new bootstrap.Modal(document.getElementById('modalCliente')).show();
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

