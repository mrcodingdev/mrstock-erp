<?php
$pageTitle  = 'MrStock ERP - Nova Compra';
$activePage = 'compras';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? $_SESSION['perfil'] ?? '';
if ($userPerfil !== 'admin') {
    $_SESSION['flash_error'] = "Acesso restrito a administradores.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

// Fetch Fornecedores
$stmtF = $pdo->query("SELECT id, nome FROM fornecedores WHERE status = 'ativo' ORDER BY nome ASC");
$fornecedores = $stmtF->fetchAll(PDO::FETCH_ASSOC);

// Fetch Produtos (passando para JSON para o Javascript usar os preços com escape estrito)
$stmtP = $pdo->query("SELECT id, nome, preco_compra FROM produtos WHERE status = 'ativo' ORDER BY nome ASC");
$produtos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
$produtosJson = json_encode($produtos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

require_once __DIR__ . '/../inc/header.php';
?>

    <div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-cart-plus text-primary me-2"></i>Registrar Nova Compra</h2>
            <p class="text-muted m-0">Abasteça o estoque lançando a nota fiscal do fornecedor.</p>
        </div>
        <a href="<?= BASE_URL ?>/compras/index.php" class="btn btn-secondary fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <div class="content-body">
        <form action="<?= BASE_URL ?>/compras/functions.php?tipo=compra" method="POST" id="formCompra" onsubmit="return prepararEnvio();">
            <?= csrf_input() ?>
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="itens_json" id="itens_json" value="">
            <input type="hidden" name="valor_total" id="input_valor_total" value="0">

            <div class="row">
                <!-- COLUNA ESQUERDA: Dados da Nota -->
                <div class="col-lg-4 col-12 mb-4 mb-lg-0">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-info-circle me-1"></i> 1. Dados da Nota</div>
                        <div class="card-body bg-light">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fornecedor <span class="text-danger">*</span></label>
                                <select class="form-select" name="fornecedor_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($fornecedores as $f): ?>
                                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Número da Nota (Opcional)</label>
                                <input type="text" class="form-control" name="numero_nota" placeholder="Ex: NFE 001234">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status do Pagamento</label>
                                <select class="form-select" name="status">
                                    <option value="PENDENTE">Pendente (Contas a Pagar)</option>
                                    <option value="PAGA">Já Pago</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Pagamento</label>
                                <input type="text" class="form-control" name="tipo_pagamento" placeholder="Ex: Boleto, PIX, Prazo 30 dias">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0 border-primary">
                        <div class="card-body text-center bg-primary text-white" style="border-radius: 6px;">
                            <h5 class="mb-1">Valor Total da Compra</h5>
                            <h2 class="fw-bold mb-0" id="display_total">R$ 0,00</h2>
                        </div>
                    </div>
                </div>

                <!-- COLUNA DIREITA: Itens da Compra -->
                <div class="col-lg-8 col-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-boxes me-1"></i> 2. Adicionar Produtos</div>
                        <div class="card-body bg-light">
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-12 col-md-5">
                                    <label class="form-label fw-bold">Produto</label>
                                    <select class="form-select" id="add_produto" onchange="atualizarPrecoSugerido()">
                                        <option value="">Selecione o produto...</option>
                                        <?php foreach ($produtos as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label fw-bold">Qtd.</label>
                                    <input type="number" step="1" min="1" class="form-control" id="add_qtd" value="1">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label fw-bold">Custo Unit. (R$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="add_preco" value="0.00">
                                </div>
                                <div class="col-12 col-md-2">
                                    <button type="button" class="btn btn-success w-100 fw-bold" onclick="adicionarItem()"><i class="fas fa-plus"></i> Add</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white fw-bold border-bottom"><i class="fas fa-list me-1"></i> 3. Lista de Itens (O estoque subirá imediatamente)</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped so-table mb-0 text-center align-middle" id="tabela_itens">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-start ps-3">Produto</th>
                                            <th width="15%">Qtd</th>
                                            <th width="20%">Custo Unit.</th>
                                            <th width="20%">Subtotal</th>
                                            <th width="10%">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="linha_vazia"><td colspan="5" class="text-muted py-4">Nenhum item adicionado à compra ainda.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-end py-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm px-5" id="btnFinalizar" disabled>
                                <i class="fas fa-check-circle me-1"></i> Finalizar Registro de Compra
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

<script>
const produtosDB = <?= $produtosJson ?>;
let itensCarrinho = [];

function atualizarPrecoSugerido() {
    const pId = document.getElementById('add_produto').value;
    const produto = produtosDB.find(p => p.id == pId);
    if (produto) {
        document.getElementById('add_preco').value = parseFloat(produto.preco_compra).toFixed(2);
    } else {
        document.getElementById('add_preco').value = '0.00';
    }
}

function adicionarItem() {
    const selectProd = document.getElementById('add_produto');
    const pId = selectProd.value;
    const pNome = selectProd.options[selectProd.selectedIndex].text;
    const qtd = parseFloat(document.getElementById('add_qtd').value);
    const preco = parseFloat(document.getElementById('add_preco').value);

    if (!pId || isNaN(qtd) || qtd <= 0 || isNaN(preco) || preco < 0) {
        alert("Preencha corretamente o produto, quantidade e preço.");
        return;
    }

    // Verifica se já existe na lista e apenas soma
    const existente = itensCarrinho.find(i => i.produto_id == pId);
    if (existente) {
        existente.quantidade += qtd;
        existente.preco_unitario = preco; // atualiza pro mais recente
        existente.subtotal = existente.quantidade * existente.preco_unitario;
    } else {
        itensCarrinho.push({
            produto_id: pId,
            nome: pNome,
            quantidade: qtd,
            preco_unitario: preco,
            subtotal: qtd * preco
        });
    }

    // Limpa campos
    selectProd.value = '';
    document.getElementById('add_qtd').value = '1';
    document.getElementById('add_preco').value = '0.00';

    renderizarTabela();
}

function removerItem(index) {
    itensCarrinho.splice(index, 1);
    renderizarTabela();
}

function renderizarTabela() {
    const tbody = document.querySelector('#tabela_itens tbody');
    tbody.innerHTML = '';
    
    let totalCompra = 0;

    if (itensCarrinho.length === 0) {
        tbody.innerHTML = '<tr id="linha_vazia"><td colspan="5" class="text-muted py-4">Nenhum item adicionado à compra ainda.</td></tr>';
        document.getElementById('btnFinalizar').disabled = true;
    } else {
        document.getElementById('btnFinalizar').disabled = false;
        itensCarrinho.forEach((item, index) => {
            totalCompra += item.subtotal;
            tbody.innerHTML += `
                <tr>
                    <td class="text-start ps-3 fw-bold">${item.nome}</td>
                    <td>${item.quantidade}</td>
                    <td>R$ ${item.preco_unitario.toFixed(2).replace('.', ',')}</td>
                    <td class="text-primary fw-bold">R$ ${item.subtotal.toFixed(2).replace('.', ',')}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger py-0" onclick="removerItem(${index})"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;
        });
    }

    // Atualiza Display
    document.getElementById('display_total').innerText = 'R$ ' + totalCompra.toFixed(2).replace('.', ',');
    document.getElementById('input_valor_total').value = totalCompra.toFixed(2);
}

function prepararEnvio() {
    if (itensCarrinho.length === 0) {
        alert("Você precisa adicionar pelo menos um produto à compra!");
        return false;
    }
    // Grava o JSON no input hidden para o PHP ler
    document.getElementById('itens_json').value = JSON.stringify(itensCarrinho);
    return true;
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
