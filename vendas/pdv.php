<?php
$pageTitle  = 'MrStock ERP - PDV (Frente de Caixa)';
$activePage = 'pdv';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Ingestão das configurações operacionais do PDV
$cfgSomPdv         = get_app_config($pdo, 'som_pdv', '1') === '1';
$cfgPdvDescMax     = (float)get_app_config($pdo, 'pdv_desconto_maximo', '15.0');
$cfgPdvTravaMargem = get_app_config($pdo, 'pdv_trava_margem', 'aviso');
$cfgPdvImpressora  = get_app_config($pdo, 'pdv_impressora', '80mm');

// Consulta de produtos ativos com saldo em estoque
$stmt = $pdo->query("SELECT p.id, p.nome, p.preco_venda as preco, p.preco_compra, p.quantidade, p.codigo_de_barra, p.categoria_id, c.nome as categoria 
                     FROM produtos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.quantidade > 0 AND p.status = 'ativo' 
                     ORDER BY p.nome ASC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta de categorias de produtos
$stmtCat = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Consulta de clientes ativos
$stmtCli = $pdo->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome ASC");
$clientes = $stmtCli->fetchAll(PDO::FETCH_ASSOC);

$userPerfil = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? 'admin';
$userName   = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Operador';

require_once __DIR__ . '/../inc/header.php';
?>

    <!-- Toast Container Flutuante para Feedback Visual -->
    <div id="toastContainer"></div>

    <!-- ══ ALERTAS DE ESTOQUE E STATUS ═══════════════════════════════════════ -->
    <?php if (isset($_GET['erro'])): ?>
        <?php if ($_GET['erro'] === 'estoque'): 
            $disp  = (int)($_GET['disponivel'] ?? 0);
            $solic = (int)($_GET['solicitado'] ?? 1);
        ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 shadow-sm border border-danger-subtle" role="alert" id="alertaEstoque">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3 text-danger"></i>
                <div>
                    <h6 class="mb-1 fw-bold"><i class="fas fa-ban text-danger me-1"></i> Estoque Insuficiente — Venda Bloqueada!</h6>
                    <span>Produto: <strong><?= htmlspecialchars($_GET['produto'] ?? '') ?></strong></span><br>
                    <small>Disponível no estoque: <strong class="text-danger tabular-nums"><?= $disp ?></strong> <?= $disp === 1 ? 'unidade' : 'unidades' ?> &nbsp;|&nbsp;
                    Solicitado: <strong class="tabular-nums"><?= $solic ?></strong> <?= $solic === 1 ? 'unidade' : 'unidades' ?></small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'carrinho_vazio'): ?>
        <div class="alert alert-warning alert-dismissible fade show mx-3 mt-3 shadow-sm border border-warning-subtle" role="alert">
            <i class="fas fa-cart-arrow-down me-2"></i> <strong>Carrinho vazio.</strong> Adicione produtos antes de finalizar.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'cupom_invalido'): ?>
        <div class="alert alert-warning alert-dismissible fade show mx-3 mt-3 shadow-sm border border-warning-subtle" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i> <strong>Aviso:</strong> Identificador de venda não especificado para emissão do cupom.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'venda_nao_encontrada'): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 shadow-sm border border-danger-subtle" role="alert">
            <i class="fas fa-circle-xmark me-2"></i> <strong>Erro:</strong> Venda não localizada no banco de dados.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="content-body pdv-master-container">
        
        <!-- ══ BARRA SUPERIOR OPERACIONAL DO PDV ═════════════════════════════ -->
        <div class="d-flex justify-content-between align-items-center p-3 px-4 border-bottom bg-light">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-cash-register text-success fs-5"></i>
                <span class="fw-bold text-dark fs-6">Frente de Caixa</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-secondary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGuiaAtalhos" aria-label="Abrir guia de atalhos (F1)">
                    <i class="fas fa-keyboard me-1 text-white"></i> Atalhos (F1)
                </button>
                <button type="button" class="btn btn-danger btn-sm fw-semibold shadow-sm" onclick="limparCarrinho()" title="Cancelar Venda (F9)" aria-label="Cancelar venda atual (F9)">
                    <i class="fas fa-times me-1"></i> Cancelar (F9)
                </button>
            </div>
        </div>

        <div class="p-3 p-md-4">
            <div class="row g-4 align-items-start">
                
                <!-- ══════════════════════════════════════════════════════════════
                     COLUNA ESQUERDA: CUPOM DIGITAL & TOTALIZADOR FINANCEIRO
                     ══════════════════════════════════════════════════════════════ -->
                <div class="col-lg-5 col-12">
                    <div class="so-card mb-0 d-flex flex-column border shadow-sm">
                        <div class="so-card-header py-3 bg-dark text-white d-flex justify-content-between align-items-center border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-receipt text-success"></i>
                                <span class="fw-bold">Cupom Fiscal Digital</span>
                            </div>
                            <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold tabular-nums" id="badge_itens_count" style="font-size:0.8rem;">0 itens</span>
                        </div>

                        <!-- Lista de Itens do Cupom com Altura Estável e Padronizada -->
                        <div class="so-card-body p-0 pdv-cart-scroll" id="container_cupom_scroll">
                            <table class="table table-hover mb-0 so-table align-middle" id="tabela_cupom">
                                <thead class="table-light sticky-top" style="font-size:0.75rem;">
                                    <tr>
                                        <th width="8%">#</th>
                                        <th width="42%">Produto</th>
                                        <th width="24%" class="text-center">Qtd</th>
                                        <th width="20%" class="text-end pe-2">Subtotal</th>
                                        <th width="6%" class="text-center text-danger"><i class="fas fa-trash-alt"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="tabela_carrinho">
                                    <tr id="empty_cart_row">
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-cart-shopping fa-3x text-muted opacity-50 mb-3 d-block"></i>
                                            <strong class="text-dark d-block">O carrinho está vazio</strong>
                                            <span class="small">Bipe o código de barras ou selecione os produtos ao lado.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totalizador e Painel Financeiro -->
                        <div class="p-3 bg-light border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-bold">SUBTOTAL:</span>
                                <span class="fw-bold text-dark tabular-nums" id="display_subtotal">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label for="desconto_input" class="text-muted small fw-bold mb-0">DESCONTO (R$) <kbd class="bg-light text-secondary border px-1 py-0 text-2xs fw-bold ms-1" title="Atalho F7">F7</kbd>:</label>
                                <div class="w-desconto-input">
                                    <input type="number" step="0.01" min="0" id="desconto_input" class="form-control form-control-sm text-end fw-bold shadow-none tabular-nums" value="0.00" oninput="recalcularTotal()" aria-label="Valor de desconto em reais">
                                </div>
                            </div>

                            <!-- Display Grande de Total -->
                            <div class="pdv-total-display mb-3 text-center">
                                <div class="text-uppercase fw-bold text-light opacity-75 mb-1" style="font-size:0.75rem;letter-spacing:0.08em;">Total a Pagar</div>
                                <div class="pdv-total-amount tabular-nums" id="display_total">R$ 0,00</div>
                            </div>

                            <!-- Formulário Oculto de Submissão -->
                            <form action="<?= BASE_URL ?>/vendas/functions.php?tipo=venda" method="POST" id="formVenda">
                                <?= csrf_input() ?>
                                <input type="hidden" name="acao"            value="venda_completa">
                                <input type="hidden" name="cart_data"       id="cart_data" value="[]">
                                <input type="hidden" name="subtotal"        id="input_subtotal" value="0.00">
                                <input type="hidden" name="total"           id="input_total" value="0.00">
                                <input type="hidden" name="desconto"        id="input_desconto_real" value="0.00">
                                <input type="hidden" name="cliente_id"      id="input_cliente_id" value="">
                                <input type="hidden" name="cpf_cliente"     id="input_cpf_cliente" value="">
                                <input type="hidden" name="forma_pagamento" id="input_forma_pagamento" value="DINHEIRO">

                                <div class="d-grid">
                                    <button type="button" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm" id="btnPagarModal" onclick="abrirModalPagamento()" aria-label="Pagar e emitir NFC-e (F4)">
                                        <i class="fas fa-cash-register me-2"></i> Pagar / Emitir NFC-e (F4)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════════
                     COLUNA DIREITA: HUB DE COMANDOS & CATÁLOGO VISUAL
                     ══════════════════════════════════════════════════════════════ -->
                <div class="col-lg-7 col-12">
                    
                    <!-- 1. Leitor Rápido de Código de Barras / Busca Textual -->
                    <div class="so-card p-3 mb-3 border shadow-sm">
                        <label for="barcode_input" class="form-label text-secondary fw-bold d-flex justify-content-between align-items-center mb-1">
                            <span><i class="fas fa-barcode text-primary me-1"></i> Leitor de Código de Barras / Busca Rápida</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F2</kbd>
                        </label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-light border"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" id="barcode_input" class="form-control form-control-lg border bg-white fw-bold" placeholder="Bipe o código ou digite o nome e [Enter]..." autocomplete="off" autofocus aria-label="Código de barras ou busca de produto">
                        </div>
                        <small class="text-muted mt-1 d-block" style="font-size:0.75rem;">
                            Bipagem automática: o item é inserido instantaneamente no cupom digital.
                        </small>
                    </div>

                    <!-- 2. Chips Interativos de Famílias de Produtos com Setas de Navegação -->
                    <div class="mb-3" id="categoria_filtro_pdv">
                        <label class="form-label text-secondary fw-bold small mb-2 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-tags text-primary me-1"></i> Famílias de Produtos</span>
                            <span class="text-muted" style="font-size:0.7rem;">Use as setas ou a rodinha do mouse</span>
                        </label>
                        <div class="category-chips-nav-wrapper">
                            <button type="button" class="btn-chips-scroll btn-chips-prev" id="btnChipsPrev" onclick="scrollChipsBar(-200)" title="Rolar famílias para esquerda" aria-label="Rolar famílias para esquerda">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="category-chips-bar" id="categoryChipsBar">
                                <button type="button" class="category-chip active" data-categoria-id="" data-cat-id="" onclick="filtrarProdutosPorCategoria('')">
                                    <i class="fas fa-border-all"></i> Todas
                                </button>
                                <?php foreach ($categorias as $cat): ?>
                                    <button type="button" class="category-chip" data-categoria-id="<?= $cat['id'] ?>" data-cat-id="<?= $cat['id'] ?>" onclick="filtrarProdutosPorCategoria('<?= $cat['id'] ?>')">
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn-chips-scroll btn-chips-next" id="btnChipsNext" onclick="scrollChipsBar(200)" title="Rolar famílias para direita" aria-label="Rolar famílias para direita">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 3. Grade Rápida de Produtos (Quick Product Deck) -->
                    <div class="so-card p-3 mb-3 border shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small"><i class="fas fa-boxes-stacked text-primary me-1"></i> Catálogo Rápido (Clique para Adicionar)</span>
                            <span class="text-muted small tabular-nums" id="catalogo_count_label"><?= count($produtos) === 1 ? '1 item ativo' : count($produtos) . ' itens ativos' ?></span>
                        </div>
                        <div class="quick-product-grid" id="quickProductGrid">
                            <!-- Preenchido dinamicamente via JS -->
                        </div>
                    </div>

                    <!-- 4. Dados do Cliente & Opções da Venda -->
                    <div class="so-card p-3 mb-0 border shadow-sm">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-user-check text-primary me-1"></i> Dados do Cliente & Identificação Fiscal</h6>
                        <div class="row g-2">
                            <div class="col-md-6 col-12">
                                <label for="cliente_select" class="form-label text-muted small fw-bold mb-1">Cliente Vinculado (Opcional)</label>
                                <select id="cliente_select" class="form-select form-select-sm border bg-white shadow-sm" onchange="atualizarClienteSelecionado(this)">
                                    <option value="">Consumidor Final (Sem Vínculo)</option>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?= (int)$cli['id'] ?>"><?= htmlspecialchars($cli['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label for="cpf_cliente" class="form-label text-muted small fw-bold mb-1">CPF/CNPJ na Nota (Opcional)</label>
                                <input type="text" id="cpf_cliente" class="form-control form-control-sm border bg-white shadow-sm tabular-nums" placeholder="000.000.000-00 ou 00.000.000/0000-00" maxlength="18" oninput="mascaraCpfCnpj(this)">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- ══ MODAL GUIA DE ATALHOS & MANUAL DO CAIXA (F1) ═══════════════════════ -->
    <div class="modal fade" id="modalGuiaAtalhos" tabindex="-1" aria-labelledby="modalGuiaAtalhosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border shadow-sm" style="border-radius: var(--mr-radius);">
                <div class="modal-header bg-dark text-white border-bottom py-3">
                    <h5 class="modal-title fw-bold" id="modalGuiaAtalhosLabel"><i class="fas fa-keyboard text-primary me-2"></i> Atalhos de Teclado do Caixa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Opere o PDV em alta velocidade utilizando os atalhos de teclado:</p>
                    <div class="list-group list-group-flush border rounded">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F1</strong> — Abrir este Guia de Atalhos</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F1</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F2</strong> — Focar campo do Leitor / Busca</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F2</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F4</strong> — Abrir Pagamento & Emissão de NFC-e</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F4</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F7</strong> — Focar e Aplicar Desconto (R$)</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F7</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F8</strong> — Alternar Forma de Pagamento</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F8</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F9</strong> — Cancelar / Limpar Cupom Atual</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F9</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">ESC</strong> — Fechar Janelas e Modais</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">ESC</kbd>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Fechar (ESC)</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ MODAL DE FINALIZAÇÃO, CÉDULAS RÁPIDAS E TROCO DINÂMICO (F4) ═════════ -->
    <div class="modal fade" id="modalFinalizarVenda" tabindex="-1" aria-labelledby="modalFinalizarVendaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border shadow-sm" style="border-radius: var(--mr-radius);">
                <div class="modal-header bg-primary text-white border-bottom py-3">
                    <h5 class="modal-title fw-bold" id="modalFinalizarVendaLabel"><i class="fas fa-cash-register me-2"></i> Finalizar Venda — Frente de Caixa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Resumo da Venda: Total em Preto Corporativo -->
                    <div class="text-center p-3 mb-3 bg-light rounded border shadow-sm">
                        <span class="text-muted fw-bold d-block mb-1" style="font-size:0.85rem;">TOTAL A RECEBER</span>
                        <h2 class="text-dark fw-bold mb-0 tabular-nums">R$ <span id="modal_total_display">0,00</span></h2>
                        <small class="text-muted" id="modal_cliente_display">Consumidor Final</small>
                    </div>

                    <!-- Seleção de Forma de Pagamento no Modal -->
                    <div class="mb-3">
                        <label for="modal_forma_pagamento" class="form-label text-secondary fw-bold d-flex justify-content-between align-items-center mb-1">
                            <span>Forma de Pagamento</span>
                            <kbd class="bg-light text-secondary border px-2 py-0 text-2xs fw-bold">F8</kbd>
                        </label>
                        <select id="modal_forma_pagamento" class="form-select form-select-lg border bg-white shadow-sm" onchange="aoMudarFormaPagamentoModal(this.value)">
                            <option value="DINHEIRO">Dinheiro Espécie</option>
                            <option value="PIX">PIX Automático</option>
                            <option value="CARTÃO DE CRÉDITO">Cartão de Crédito</option>
                            <option value="CARTÃO DE DÉBITO">Cartão de Débito</option>
                        </select>
                    </div>

                    <!-- Seção Específica para Pagamento em Dinheiro (Cédulas Rápidas e Troco) -->
                    <div id="secaoDinheiroTroco">
                        <label class="form-label text-secondary fw-bold mb-2">Cédulas Rápidas</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn-cedula flex-fill tabular-nums" onclick="definirCedula(10)" aria-label="Inserir cédula de 10 reais">R$ 10</button>
                            <button type="button" class="btn-cedula flex-fill tabular-nums" onclick="definirCedula(20)" aria-label="Inserir cédula de 20 reais">R$ 20</button>
                            <button type="button" class="btn-cedula flex-fill tabular-nums" onclick="definirCedula(50)" aria-label="Inserir cédula de 50 reais">R$ 50</button>
                            <button type="button" class="btn-cedula flex-fill tabular-nums" onclick="definirCedula(100)" aria-label="Inserir cédula de 100 reais">R$ 100</button>
                            <button type="button" class="btn-cedula flex-fill tabular-nums" onclick="definirCedula(200)" aria-label="Inserir cédula de 200 reais">R$ 200</button>
                            <button type="button" class="btn-cedula flex-fill fw-bold" onclick="definirValorExato()" aria-label="Inserir valor exato">Exato</button>
                        </div>

                        <div class="mb-3">
                            <label for="modal_valor_recebido" class="form-label text-secondary fw-bold">Valor Recebido (R$)</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-light border fw-bold">R$</span>
                                <input type="number" step="0.01" min="0" id="modal_valor_recebido" class="form-control border bg-white fw-bold text-dark fs-4 tabular-nums" placeholder="0,00" oninput="calcularTroco()" aria-label="Valor recebido em dinheiro">
                            </div>
                        </div>

                        <!-- Painel Dinâmico de Troco -->
                        <div id="trocoBox" class="troco-box troco-valido text-center">
                            <span class="text-muted fw-bold d-block mb-1" style="font-size:0.8rem;" id="trocoTitulo">TROCO A DEVOLVER</span>
                            <h3 class="fw-bold mb-0 text-success tabular-nums" id="trocoValorDisplay">R$ 0,00</h3>
                            <small class="text-danger fw-bold d-none" id="trocoAlertaInsuficiente"><i class="fas fa-exclamation-circle me-1"></i> Valor recebido é menor que o total!</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Voltar (ESC)</button>
                    <button type="button" class="btn btn-success px-4 py-2 fw-bold shadow-sm" id="btnConfirmarVendaModal" onclick="confirmarVendaFinal()">
                        <i class="fas fa-check-circle me-2"></i> Confirmar e Emitir NFC-e
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Alerta de Estoque (Frontend) -->
    <div class="modal fade" id="modalEstoque" tabindex="-1" aria-labelledby="modalEstoqueLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border shadow-sm" style="border-radius: var(--mr-radius);">
                <div class="modal-header bg-danger text-white border-bottom py-3">
                    <h5 class="modal-title fw-bold" id="modalEstoqueLabel"><i class="fas fa-exclamation-triangle me-2"></i> Estoque Insuficiente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-boxes-stacked fa-3x text-danger mb-3"></i>
                    <h5 id="modalEstoqueMsg" class="fw-bold text-dark"></h5>
                    <p id="modalEstoqueDetalhe" class="text-muted mb-0"></p>
                </div>
                <div class="modal-footer border-top justify-content-center bg-light">
                    <button type="button" class="btn btn-danger px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Entendido (ESC)</button>
                </div>
            </div>
        </div>
    </div>

<script>
// ══ CONFIGURAÇÕES OPERACIONAIS DINÂMICAS (INJETADAS DO BACKEND) ═══════════════
const MRSTOCK_CONFIG = {
    somPdv:         <?= json_encode($cfgSomPdv) ?>,
    pdvDescMax:     <?= json_encode($cfgPdvDescMax) ?>,
    pdvTravaMargem: <?= json_encode($cfgPdvTravaMargem) ?>,
    pdvImpressora:  <?= json_encode($cfgPdvImpressora) ?>
};
// ══ CATÁLOGO CLIENT-SIDE E ESTADO DO PDV ═════════════════════════════════════
const catalogoProdutos = <?= json_encode($produtos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let carrinho = [];
let categoriaAtivaFiltro = '';
let modalFinalizarInstancia = null;
let modalEstoqueInstancia = null;
let totalVendaAtual = 0.0;

// ══ SINTETIZADOR DE ÁUDIO NATIVO (WEB AUDIO API - 100% OFFLINE) ══════════════
let audioCtx = null;
function getAudioContext() {
    if (!audioCtx) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) {
            audioCtx = new AudioContextClass();
        }
    }
    if (audioCtx && audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
    return audioCtx;
}

function playBeep(type = 'success') {
    if (!MRSTOCK_CONFIG.somPdv) return;
    try {
        const ctx = getAudioContext();
        if (!ctx) return;

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        const now = ctx.currentTime;

        if (type === 'success') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now);
            gain.gain.setValueAtTime(0.18, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.075);
            osc.start(now);
            osc.stop(now + 0.075);
        } else if (type === 'error') {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(280, now);
            gain.gain.setValueAtTime(0.22, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.16);
            osc.start(now);
            osc.stop(now + 0.16);
        } else if (type === 'cash') {
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(587.33, now);
            osc.frequency.setValueAtTime(880, now + 0.06);
            gain.gain.setValueAtTime(0.2, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.18);
            osc.start(now);
            osc.stop(now + 0.18);
        }
    } catch (e) {}
}

// ══ TOAST NOTIFICATIONS DINÂMICAS ═════════════════════════════════════════════
function showToast(message, type = 'success', title = 'PDV MrStock') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toastEl = document.createElement('div');
    const bgClass = (type === 'success') ? 'bg-success text-white' : ((type === 'danger') ? 'bg-danger text-white' : 'bg-dark text-white');
    const icon = (type === 'success') ? 'fa-check-circle' : ((type === 'danger') ? 'fa-exclamation-triangle' : 'fa-info-circle');

    toastEl.className = `toast align-items-center ${bgClass} border-0 shadow-lg mb-2 show`;
    toastEl.setAttribute('role', 'alert');
    toastEl.style.minWidth = '270px';
    toastEl.style.transition = 'opacity 0.25s ease, transform 0.25s ease';

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center py-2 px-3">
                <i class="fas ${icon} me-2 fs-5"></i>
                <div>
                    <div class="fw-bold" style="font-size:0.85rem;">${title}</div>
                    <div style="font-size:0.8rem;">${message}</div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;

    container.appendChild(toastEl);
    setTimeout(() => {
        toastEl.style.opacity = '0';
        toastEl.style.transform = 'translateY(-8px)';
        setTimeout(() => toastEl.remove(), 280);
    }, 2500);
}

function formatarMoeda(v) {
    return v.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function mostrarAlertaEstoque(msg, detalhe) {
    playBeep('error');
    document.getElementById('modalEstoqueMsg').textContent    = msg;
    document.getElementById('modalEstoqueDetalhe').textContent = detalhe;
    
    if (!modalEstoqueInstancia) {
        modalEstoqueInstancia = new bootstrap.Modal(document.getElementById('modalEstoque'));
    }
    modalEstoqueInstancia.show();
    showToast(msg, 'danger', 'Alerta de Estoque');
}

// ══ RENDERIZAÇÃO DA GRADE RÁPIDA DE PRODUTOS ══════════════════════════════════
function renderizarGradeRapida(filtroCat = '', buscaTexto = '') {
    const grid = document.getElementById('quickProductGrid');
    if (!grid) return;

    let filtrados = catalogoProdutos;

    if (filtroCat) {
        filtrados = filtrados.filter(p => String(p.categoria_id) === String(filtroCat));
    }

    if (buscaTexto) {
        const termo = buscaTexto.toLowerCase().trim();
        filtrados = filtrados.filter(p => 
            p.nome.toLowerCase().includes(termo) || 
            (p.codigo_de_barra && p.codigo_de_barra.toLowerCase().includes(termo))
        );
    }

    const countLabel = document.getElementById('catalogo_count_label');
    if (countLabel) {
        countLabel.textContent = filtrados.length === 1 ? '1 item encontrado' : `${filtrados.length} itens encontrados`;
    }

    if (filtrados.length === 0) {
        grid.innerHTML = `<div class="p-4 text-center text-muted w-100"><i class="fas fa-search me-1"></i> Nenhum produto encontrado nesta família.</div>`;
        return;
    }

    let html = '';
    filtrados.forEach(p => {
        const precoFmt = formatarMoeda(parseFloat(p.preco));
        const jaNoCarrinho = carrinho.find(i => i.id === parseInt(p.id))?.quantidade || 0;
        const qtdRestante = parseInt(p.quantidade) - jaNoCarrinho;

        // Regra #6: Remover badge cinza de estoque normal, usando text-muted small com tabular-nums (badge vermelha apenas para estoque crítico <= 3)
        const stockHtml = qtdRestante <= 3
            ? `<span class="badge bg-danger text-white tabular-nums">Estq: ${qtdRestante}</span>`
            : `<span class="text-muted small tabular-nums">Estq: ${qtdRestante}</span>`;

        html += `
            <div class="quick-prod-card" onclick="adicionarItemDireto(${p.id})">
                <div class="quick-prod-card__name" title="${p.nome}">${p.nome}</div>
                <div class="quick-prod-card__footer">
                    <span class="quick-prod-card__price tabular-nums">R$ ${precoFmt}</span>
                    <span class="quick-prod-card__stock">${stockHtml}</span>
                </div>
            </div>
        `;
    });

    grid.innerHTML = html;
}

function filtrarProdutosPorCategoria(catId) {
    categoriaAtivaFiltro = catId;
    document.querySelectorAll('.category-chip').forEach(btn => {
        const btnCat = btn.getAttribute('data-cat-id') || '';
        if ((!catId && !btnCat) || btnCat === String(catId)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    renderizarGradeRapida(catId, document.getElementById('barcode_input').value);
}

// ══ ADIÇÃO E MANIPULAÇÃO DO CARRINHO ═════════════════════════════════════════
function processarAdicao(id, nome, preco, qtdMax, qtd, precoCompra = 0) {
    if (isNaN(qtd) || qtd <= 0) {
        mostrarAlertaEstoque('Quantidade inválida', 'Informe uma quantidade maior que zero.');
        return false;
    }

    const itemExistente = carrinho.find(i => i.id === id);
    const jaNoCarrinho = itemExistente ? itemExistente.quantidade : 0;
    const totalSolicit = jaNoCarrinho + qtd;

    if (totalSolicit > qtdMax) {
        const dispTxt     = qtdMax === 1 ? '1 unidade' : `${qtdMax} unidades`;
        const carrinhoTxt = jaNoCarrinho === 1 ? '1 unidade' : `${jaNoCarrinho} unidades`;
        const solicTxt    = qtd === 1 ? '1 unidade' : `${qtd} unidades`;
        const totalTxt    = totalSolicit === 1 ? '1 unidade' : `${totalSolicit} unidades`;

        mostrarAlertaEstoque(
            'Estoque Insuficiente!',
            `Produto: ${nome}\nDisponível: ${dispTxt} | Já no carrinho: ${carrinhoTxt}\nSolicitado: ${solicTxt} (Total: ${totalTxt})`
        );
        return false;
    }

    if (itemExistente) {
        itemExistente.quantidade += qtd;
        itemExistente.subtotal = itemExistente.quantidade * itemExistente.preco;
    } else {
        carrinho.push({
            id: id,
            nome: nome,
            preco: preco,
            preco_compra: precoCompra,
            quantidade: qtd,
            subtotal: qtd * preco,
            max: qtdMax
        });
    }

    playBeep('success');
    const toastQtdTxt = qtd === 1 ? '1 item' : `${qtd} itens`;
    showToast(`${toastQtdTxt} (${nome}) adicionado ao cupom.`, 'success');
    renderizarCarrinho();
    renderizarGradeRapida(categoriaAtivaFiltro, document.getElementById('barcode_input').value);
    return true;
}

function adicionarItemDireto(id) {
    const p = catalogoProdutos.find(item => parseInt(item.id) === parseInt(id));
    if (!p) return;
    processarAdicao(parseInt(p.id), p.nome, parseFloat(p.preco), parseInt(p.quantidade), 1, parseFloat(p.preco_compra || 0));
}

function alterarQuantidadeItem(id, delta) {
    const item = carrinho.find(i => i.id === id);
    if (!item) return;

    const novaQtd = item.quantidade + delta;
    if (novaQtd <= 0) {
        removerItem(id);
        return;
    }

    if (novaQtd > item.max) {
        const dispTxt = item.max === 1 ? '1 unidade' : `${item.max} unidades`;
        mostrarAlertaEstoque('Estoque Insuficiente!', `Disponível no estoque: ${dispTxt}.`);
        return;
    }

    item.quantidade = novaQtd;
    item.subtotal = item.quantidade * item.preco;
    playBeep('success');
    renderizarCarrinho();
    renderizarGradeRapida(categoriaAtivaFiltro, document.getElementById('barcode_input').value);
}

function removerItem(id) {
    carrinho = carrinho.filter(i => i.id !== id);
    playBeep('error');
    renderizarCarrinho();
    renderizarGradeRapida(categoriaAtivaFiltro, document.getElementById('barcode_input').value);
}

function limparCarrinho() {
    if (carrinho.length === 0) return;
    if (confirm('Deseja realmente cancelar e limpar todos os itens do cupom?')) {
        carrinho = [];
        document.getElementById('desconto_input').value = '0.00';
        renderizarCarrinho();
        renderizarGradeRapida(categoriaAtivaFiltro, document.getElementById('barcode_input').value);
        showToast('Carrinho limpo com sucesso.', 'info', 'PDV Limpo');
    }
}

// ══ RENDERIZAÇÃO DO CUPOM FISCAL DIGITAL ══════════════════════════════════════
function renderizarCarrinho() {
    const tbody = document.getElementById('tabela_carrinho');
    const badgeItens = document.getElementById('badge_itens_count');
    
    if (!tbody) return;

    if (carrinho.length === 0) {
        tbody.innerHTML = `
            <tr id="empty_cart_row">
                <td colspan="5" class="text-center text-muted py-5">
                    <i class="fas fa-cart-shopping fa-3x text-muted opacity-50 mb-3 d-block"></i>
                    <strong class="text-dark d-block">O carrinho está vazio</strong>
                    <span class="small">Bipe o código de barras ou selecione os produtos ao lado.</span>
                </td>
            </tr>
        `;
        if (badgeItens) badgeItens.textContent = '0 itens';
        recalcularTotal();
        return;
    }

    let totalQtd = 0;
    let html = '';

    carrinho.forEach((item, index) => {
        totalQtd += item.quantidade;
        const seq = String(index + 1).padStart(2, '0');
        
        html += `
            <tr class="align-middle">
                <td class="text-muted fw-bold font-monospace tabular-nums" style="font-size:0.75rem;">#${seq}</td>
                <td>
                    <strong class="text-dark d-block" style="font-size:0.85rem;line-height:1.2;">${item.nome}</strong>
                    <small class="text-muted tabular-nums" style="font-size:0.75rem;">R$ ${formatarMoeda(item.preco)} un.</small>
                </td>
                <td class="text-center">
                    <div class="pdv-stepper">
                        <button type="button" class="pdv-stepper-btn" onclick="alterarQuantidadeItem(${item.id}, -1)" title="Diminuir" aria-label="Diminuir quantidade de ${item.nome}">
                            <i class="fas fa-minus" style="font-size:0.65rem;"></i>
                        </button>
                        <span class="pdv-stepper-val tabular-nums">${item.quantidade}</span>
                        <button type="button" class="pdv-stepper-btn" onclick="alterarQuantidadeItem(${item.id}, 1)" title="Aumentar" aria-label="Aumentar quantidade de ${item.nome}">
                            <i class="fas fa-plus" style="font-size:0.65rem;"></i>
                        </button>
                    </div>
                </td>
                <td class="text-end fw-bold text-dark pe-2 tabular-nums" style="font-size:0.9rem;">
                    R$ ${formatarMoeda(item.subtotal)}
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger p-0 border-0 shadow-none" onclick="removerItem(${item.id})" title="Remover item" aria-label="Remover ${item.nome} do cupom">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    if (badgeItens) badgeItens.textContent = `${totalQtd === 1 ? '1 item' : totalQtd + ' itens'}`;
    
    // Auto-scroll suave para o final da lista
    const scrollCont = document.getElementById('container_cupom_scroll');
    if (scrollCont) {
        scrollCont.scrollTop = scrollCont.scrollHeight;
    }

    recalcularTotal();
}

function recalcularTotal() {
    let subtotal = 0.0;
    let custoTotal = 0.0;
    carrinho.forEach(item => { 
        subtotal += item.subtotal; 
        custoTotal += (parseFloat(item.preco_compra || 0) * item.quantidade);
    });

    let descInput = parseFloat(document.getElementById('desconto_input')?.value) || 0.0;
    if (descInput < 0) descInput = 0;
    if (descInput > subtotal) descInput = subtotal;

    // Validação de Desconto Máximo Configurado
    const maxDescPercent = parseFloat(MRSTOCK_CONFIG.pdvDescMax) || 0;
    if (maxDescPercent > 0 && subtotal > 0) {
        const maxDescValor = (subtotal * maxDescPercent) / 100.0;
        if (descInput > maxDescValor) {
            descInput = maxDescValor;
            const inputDesc = document.getElementById('desconto_input');
            if (inputDesc) inputDesc.value = descInput.toFixed(2);
            showToast(`Desconto limitado a ${maxDescPercent}% (R$ ${formatarMoeda(maxDescValor)}).`, 'danger', 'Desconto Limitado');
            playBeep('error');
        }
    }

    totalVendaAtual = Math.max(0, subtotal - descInput);

    // Validação de Margem de Lucro / Custo de Compra
    const btnPagar = document.getElementById('btnPagarModal');
    if (carrinho.length > 0 && custoTotal > 0 && totalVendaAtual < custoTotal) {
        if (MRSTOCK_CONFIG.pdvTravaMargem === 'bloquear') {
            if (btnPagar) {
                btnPagar.disabled = true;
                btnPagar.title = 'Venda bloqueada: Total abaixo do custo de compra.';
            }
        } else {
            if (btnPagar) {
                btnPagar.disabled = false;
                btnPagar.title = '';
            }
        }
    } else {
        if (btnPagar) {
            btnPagar.disabled = false;
            btnPagar.title = '';
        }
    }

    document.getElementById('display_subtotal').textContent = `R$ ${formatarMoeda(subtotal)}`;
    document.getElementById('display_total').textContent    = `R$ ${formatarMoeda(totalVendaAtual)}`;
    
    document.getElementById('input_subtotal').value      = subtotal.toFixed(2);
    document.getElementById('input_desconto_real').value = descInput.toFixed(2);
    document.getElementById('input_total').value         = totalVendaAtual.toFixed(2);
    document.getElementById('cart_data').value           = JSON.stringify(carrinho);
}

// ══ BIPAGEM E BUSCA POR CÓDIGO DE BARRAS / ENTER ═════════════════════════════
document.getElementById('barcode_input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim();
        if (!val) return;

        // 1. Busca exata por código de barras
        let produto = catalogoProdutos.find(p => p.codigo_de_barra && p.codigo_de_barra.trim() === val);

        // 2. Fallback: busca por ID ou nome
        if (!produto) {
            produto = catalogoProdutos.find(p => String(p.id) === val || p.nome.toLowerCase().includes(val.toLowerCase()));
        }

        if (produto) {
            processarAdicao(parseInt(produto.id), produto.nome, parseFloat(produto.preco), parseInt(produto.quantidade), 1);
            this.value = '';
            this.focus();
        } else {
            playBeep('error');
            mostrarAlertaEstoque('Produto não localizado!', `Nenhum produto cadastrado com o código ou termo "${val}".`);
            this.select();
        }
    }
});

document.getElementById('barcode_input').addEventListener('input', function() {
    renderizarGradeRapida(categoriaAtivaFiltro, this.value);
});

// ══ MODAL DE PAGAMENTO, TROCO E FINALIZAÇÃO ══════════════════════════════════
function abrirModalPagamento() {
    if (carrinho.length === 0) {
        playBeep('error');
        showToast('Adicione ao menos um produto antes de finalizar a venda.', 'danger', 'Carrinho Vazio');
        document.getElementById('barcode_input').focus();
        return;
    }

    let custoTotal = 0.0;
    carrinho.forEach(item => { custoTotal += (parseFloat(item.preco_compra || 0) * item.quantidade); });

    if (custoTotal > 0 && totalVendaAtual < custoTotal) {
        if (MRSTOCK_CONFIG.pdvTravaMargem === 'bloquear') {
            playBeep('error');
            mostrarAlertaEstoque('Margem Negativa Bloqueada!', `O valor total da venda (R$ ${formatarMoeda(totalVendaAtual)}) é inferior ao custo dos produtos (R$ ${formatarMoeda(custoTotal)}). Reduza o desconto para prosseguir.`);
            return;
        } else if (MRSTOCK_CONFIG.pdvTravaMargem === 'aviso') {
            showToast(`Atenção: Total abaixo do custo dos produtos (R$ ${formatarMoeda(custoTotal)}). Venda autorizada sob margem negativa.`, 'danger', 'Aviso de Margem');
        }
    }

    recalcularTotal();
    document.getElementById('modal_total_display').textContent = formatarMoeda(totalVendaAtual);

    const cliSelect = document.getElementById('cliente_select');
    const cliNome = cliSelect.options[cliSelect.selectedIndex].text;
    document.getElementById('modal_cliente_display').textContent = cliNome;

    // Sincroniza campos ocultos
    document.getElementById('input_cliente_id').value  = cliSelect.value;
    document.getElementById('input_cpf_cliente').value = document.getElementById('cpf_cliente').value;

    const formaPagto = document.getElementById('modal_forma_pagamento').value;
    aoMudarFormaPagamentoModal(formaPagto);

    if (!modalFinalizarInstancia) {
        modalFinalizarInstancia = new bootstrap.Modal(document.getElementById('modalFinalizarVenda'));
    }
    modalFinalizarInstancia.show();

    setTimeout(() => {
        if (formaPagto === 'DINHEIRO') {
            definirValorExato();
            const valInput = document.getElementById('modal_valor_recebido');
            if (valInput) {
                valInput.focus();
                valInput.select();
            }
        }
    }, 400);
}

function aoMudarFormaPagamentoModal(forma) {
    document.getElementById('input_forma_pagamento').value = forma;
    const secaoDinheiro = document.getElementById('secaoDinheiroTroco');

    if (forma === 'DINHEIRO') {
        secaoDinheiro.style.display = 'block';
        definirValorExato();
    } else {
        secaoDinheiro.style.display = 'none';
    }
}

function definirCedula(valor) {
    document.querySelectorAll('.btn-cedula').forEach(b => b.classList.remove('active-cedula'));
    document.getElementById('modal_valor_recebido').value = valor.toFixed(2);
    calcularTroco();
}

function definirValorExato() {
    document.querySelectorAll('.btn-cedula').forEach(b => b.classList.remove('active-cedula'));
    const btnExato = document.querySelector('.btn-cedula:last-child');
    if (btnExato) btnExato.classList.add('active-cedula');

    document.getElementById('modal_valor_recebido').value = totalVendaAtual.toFixed(2);
    calcularTroco();
}

function calcularTroco() {
    const valRecebido = parseFloat(document.getElementById('modal_valor_recebido').value) || 0.0;
    const troco = valRecebido - totalVendaAtual;
    const trocoDisplay = document.getElementById('trocoValorDisplay');
    const trocoBox = document.getElementById('trocoBox');
    const alertaInsuf = document.getElementById('trocoAlertaInsuficiente');
    const btnConfirmar = document.getElementById('btnConfirmarVendaModal');

    if (valRecebido < totalVendaAtual) {
        trocoBox.className = 'troco-box troco-insuficiente text-center';
        trocoDisplay.className = 'fw-bold mb-0 text-danger tabular-nums';
        trocoDisplay.textContent = `Faltam R$ ${formatarMoeda(Math.abs(troco))}`;
        alertaInsuf.classList.remove('d-none');
        btnConfirmar.disabled = true;
    } else {
        trocoBox.className = 'troco-box troco-valido text-center';
        trocoDisplay.className = 'fw-bold mb-0 text-success tabular-nums';
        trocoDisplay.textContent = `R$ ${formatarMoeda(troco)}`;
        alertaInsuf.classList.add('d-none');
        btnConfirmar.disabled = false;
    }
}

function confirmarVendaFinal() {
    playBeep('cash');
    const btn = document.getElementById('btnConfirmarVendaModal');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Emitindo NFC-e...';
    document.getElementById('formVenda').submit();
}

function atualizarClienteSelecionado(select) {
    document.getElementById('input_cliente_id').value = select.value;
}

// ══ ATALHOS GLOBAIS DE TECLADO (F1, F2, F4, F8, F9, ESC) ═════════════════════
window.addEventListener('keydown', function(e) {
    // F1: Modal de Ajuda / Atalhos
    if (e.key === 'F1') {
        e.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('modalGuiaAtalhos'));
        modal.show();
    }
    // F2: Focar Leitor de Código de Barras
    else if (e.key === 'F2') {
        e.preventDefault();
        const input = document.getElementById('barcode_input');
        if (input) {
            input.focus();
            input.select();
        }
    }
    // F4: Finalizar / Pagar
    else if (e.key === 'F4') {
        e.preventDefault();
        abrirModalPagamento();
    }
    // F7: Focar e Selecionar Campo de Desconto (previne Caret Browsing nativo)
    else if (e.key === 'F7') {
        e.preventDefault();
        const d = document.getElementById('desconto_input');
        if (d) {
            d.focus();
            d.select();
        }
    }
    // F8: Alternar Forma de Pagamento
    else if (e.key === 'F8') {
        e.preventDefault();
        const select = document.getElementById('modal_forma_pagamento');
        if (select) {
            const opcoes = ['DINHEIRO', 'PIX', 'CARTÃO DE CRÉDITO', 'CARTÃO DE DÉBITO'];
            let idx = opcoes.indexOf(select.value);
            idx = (idx + 1) % opcoes.length;
            select.value = opcoes[idx];
            aoMudarFormaPagamentoModal(opcoes[idx]);
        }
    }
    // F9: Cancelar / Limpar Carrinho
    else if (e.key === 'F9') {
        e.preventDefault();
        limparCarrinho();
    }
});

// Máscara dinâmica de CPF/CNPJ
function mascaraCpfCnpj(i) {
    let v = i.value.replace(/\D/g, '');
    if (v.length <= 11) {
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
    }
    i.value = v;
}

// Rolagem Suave das Famílias de Produtos
function scrollChipsBar(distance) {
    const bar = document.getElementById('categoryChipsBar');
    if (bar) {
        bar.scrollBy({ left: distance, behavior: 'smooth' });
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    renderizarGradeRapida();
    renderizarCarrinho();
    const barcodeInput = document.getElementById('barcode_input');
    if (barcodeInput) barcodeInput.focus();

    // Suporte ao scroll horizontal pela rodinha do mouse na barra de famílias
    const chipsBar = document.getElementById('categoryChipsBar');
    if (chipsBar) {
        chipsBar.addEventListener('wheel', function(e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                chipsBar.scrollLeft += (e.deltaY * 0.9);
            }
        }, { passive: false });
    }
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>