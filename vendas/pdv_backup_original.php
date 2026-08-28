<?php
$pageTitle  = 'MrStock ERP - PDV (Frente de Caixa)';
$activePage = 'pdv';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

$stmt = $pdo->query("SELECT p.id, p.nome, p.preco_venda as preco, p.quantidade, p.codigo_de_barra, p.categoria_id, p.categoria FROM produtos p WHERE p.quantidade > 0 AND p.status = 'ativo' ORDER BY p.nome");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtCat = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$stmtCli = $pdo->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome");
$clientes = $stmtCli->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../inc/header.php';
?>

    <!-- Toast Container Flutuante para Feedback Visual -->
    <div id="toastContainer"></div>

    <!-- ══ ALERTAS DE ESTOQUE (vindos do venda_action) ══════════════════════ -->
    <?php if (isset($_GET['erro'])): ?>
        <?php if ($_GET['erro'] === 'estoque'): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 shadow border-0" role="alert" id="alertaEstoque">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3 text-danger"></i>
                <div>
                    <h6 class="mb-1 fw-bold"><i class="fas fa-ban text-danger me-1"></i> Estoque Insuficiente — Venda Bloqueada!</h6>
                    <span>Produto: <strong><?= htmlspecialchars($_GET['produto'] ?? '') ?></strong></span><br>
                    <small>Disponível no estoque: <strong class="text-danger"><?= (int)($_GET['disponivel'] ?? 0) ?></strong> unidade(s) &nbsp;|&nbsp;
                    Solicitado: <strong><?= (int)($_GET['solicitado'] ?? 1) ?></strong> unidade(s)</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'carrinho_vazio'): ?>
        <div class="alert alert-warning alert-dismissible fade show mx-3 mt-3 shadow-sm border-0" role="alert">
            <i class="fas fa-cart-arrow-down me-2"></i> <strong>Carrinho vazio.</strong> Adicione produtos antes de finalizar.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'cupom_invalido'): ?>
        <div class="alert alert-warning alert-dismissible fade show mx-3 mt-3 shadow-sm border-0" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i> <strong>Aviso:</strong> Identificador de venda não especificado para emissão do cupom.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['erro'] === 'venda_nao_encontrada'): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 shadow-sm border-0" role="alert">
            <i class="fas fa-circle-xmark me-2"></i> <strong>Erro:</strong> Venda não localizada no banco de dados.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="content-body" style="background:#fff;margin:15px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
        <div class="d-flex justify-content-between align-items-center p-2 px-3 border-bottom" style="background:#f8fafc;border-radius:8px 8px 0 0;">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark"><i class="fas fa-shopping-basket text-primary me-1"></i> Frente de Caixa PDV</span>
            </div>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold shadow-none" data-bs-toggle="modal" data-bs-target="#modalGuiaAtalhos">
                    <i class="fas fa-keyboard me-1 text-primary"></i> Atalhos do Caixa (F1)
                </button>
            </div>
        </div>
        <div class="p-4">
            <div class="row">
                  <!-- Esquerda: Adicionar Produtos e Finalizar -->
                <div class="col-lg-5 border-end-lg pe-lg-4 pb-4 pb-lg-0 border-bottom border-bottom-lg-0 mb-4 mb-lg-0">
                    <h5 class="text-primary fw-bold mb-3"><i class="fas fa-barcode"></i> Informar Produto</h5>

                    <!-- Leitor Rápido de Código de Barras / Busca -->
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-bold">Leitor / Busca Rápida <span class="badge bg-secondary ms-1">F2</span></label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" id="barcode_input" class="form-control border-0 bg-light" placeholder="Bipe o código ou digite e [Enter]..." autocomplete="off">
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">Bipe o código de barras ou tecle Enter para inserção instantânea.</small>
                    </div>

                    <!-- 1. Filtro Pré-Selecionador por Família / Categoria -->
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-bold">1. Filtrar por Família de Produtos</label>
                        <select id="categoria_filtro_pdv" class="form-select border-0 bg-light shadow-sm" onchange="filtrarProdutosPorCategoria(this.value)">
                            <option value="">-- Todas as Famílias (Ver Todos) --</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. Catálogo de Produtos em Estoque -->
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-bold">2. Selecionar Produto <span class="text-danger">*</span></label>
                        <select id="produto_select" class="form-select form-select-lg shadow-sm border-0 bg-light">
                            <option value="">-- Selecione na Lista --</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"
                                        data-categoria-id="<?= $p['categoria_id'] ?? '' ?>"
                                        data-categoria-nome="<?= htmlspecialchars($p['categoria'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-nome="<?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-preco="<?= number_format((float)$p['preco'], 2, '.', '') ?>"
                                        data-max="<?= (int)$p['quantidade'] ?>"
                                        data-barcode="<?= htmlspecialchars($p['codigo_de_barra'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?> — R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?> (Estq: <?= (int)$p['quantidade'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                            <label class="form-label text-secondary fw-bold">Quantidade</label>
                            <input type="number" id="qtd_input" class="form-control form-control-lg shadow-sm border-0 bg-light" value="1" min="1">
                        </div>
                        <div class="col-md-6 col-12 d-flex align-items-end">
                            <button type="button" class="btn btn-success btn-lg w-100 shadow-sm fw-bold" onclick="adicionarAoCarrinho()">
                                <i class="fas fa-plus me-2"></i> Adicionar
                            </button>
                        </div>
                    </div>

                    <hr class="my-3">

                    <h5 class="text-primary fw-bold mb-3"><i class="fas fa-money-check-alt"></i> Finalização</h5>
                    <form action="<?= BASE_URL ?>/vendas/functions.php?tipo=venda" method="POST" id="formVenda">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao"       value="venda_completa">
                        <input type="hidden" name="cart_data"  id="cart_data" value="[]">
                        <div class="p-3 bg-light border rounded shadow-sm text-center mb-3">
                            <h6 class="text-muted fw-bold mb-1">TOTAL DA COMPRA</h6>
                            <h1 class="text-success mb-0 fw-bold">R$ <span id="display_total">0,00</span></h1>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-5">
                                <button type="button" class="btn btn-danger w-100 py-3 fw-bold shadow-sm" onclick="limparCarrinho()">
                                    <i class="fas fa-times me-1"></i> Cancelar <small>(F9)</small>
                                </button>
                            </div>
                            <div class="col-md-7">
                                <button type="button" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" onclick="abrirModalPagamento()">
                                    <i class="fas fa-cash-register me-2"></i> Pagar / NFC-e <small>(F4)</small>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Direita: Carrinho e Itens -->
                <div class="col-lg-7 ps-lg-4 pb-2 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-primary fw-bold m-0"><i class="fas fa-list-ol"></i> Itens no Cupom</h5>
                            <span class="badge bg-secondary rounded-pill fs-6" id="total_itens_badge">0 itens</span>
                        </div>
                        <div class="table-responsive border rounded shadow-sm" style="max-height:430px;min-height:280px;overflow-y:auto;">
                            <table class="table table-hover mb-0 so-table align-middle">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th width="40%">Produto</th>
                                        <th width="15%" class="text-center">Qtd</th>
                                        <th width="20%">Vlr. Unit</th>
                                        <th width="20%" class="text-end pe-4">Subtotal</th>
                                        <th width="5%" class="text-center text-danger"><i class="fas fa-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="tabela_carrinho">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-5">
                                            <strong>O carrinho está vazio.</strong><br>Bipe o código ou selecione os itens ao lado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ MODAL GUIA DE ATALHOS & MANUAL DO CAIXA (F1) ═══════════════════════ -->
    <div class="modal fade" id="modalGuiaAtalhos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--mr-radius);">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-keyboard text-primary me-2"></i> Atalhos de Teclado & Guia do Caixa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Opere o PDV em alta velocidade utilizando as teclas de atalho do teclado:</p>
                    <div class="list-group list-group-flush border rounded">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F1</strong> — Abrir este Guia de Atalhos</span>
                            <kbd class="bg-dark text-white px-2 py-1">F1</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F2</strong> — Focar campo do Leitor / Bipagem</span>
                            <kbd class="bg-primary text-white px-2 py-1">F2</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F4</strong> — Finalizar Venda & Emissão de NFC-e</span>
                            <kbd class="bg-success text-white px-2 py-1">F4</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F8</strong> — Alternar Forma de Pagamento</span>
                            <kbd class="bg-warning text-dark px-2 py-1">F8</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">F9</strong> — Cancelar / Limpar Carrinho Atual</span>
                            <kbd class="bg-danger text-white px-2 py-1">F9</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span><strong class="text-dark">ESC</strong> — Fechar Janelas e Modais Abertos</span>
                            <kbd class="bg-secondary text-white px-2 py-1">ESC</kbd>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Fechar (ESC)</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ MODAL DE FINALIZAÇÃO, CÉDULAS RÁPIDAS E TROCO DINÂMICO ══════════════ -->
    <div class="modal fade" id="modalFinalizarVenda" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--mr-radius);">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-cash-register me-2"></i> Finalizar Venda — Frente de Caixa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Resumo da Venda -->
                    <div class="text-center p-3 mb-3 bg-light rounded border">
                        <span class="text-muted fw-bold d-block mb-1" style="font-size:0.85rem;">TOTAL A RECEBER</span>
                        <h2 class="text-success fw-bold mb-0">R$ <span id="modal_total_display">0,00</span></h2>
                        <small class="text-muted" id="modal_cliente_display">Consumidor Final</small>
                    </div>

                    <!-- Seleção de Forma de Pagamento no Modal -->
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-bold">Forma de Pagamento</label>
                        <select id="modal_forma_pagamento" class="form-select form-select-lg border-0 bg-light shadow-sm" onchange="aoMudarFormaPagamentoModal(this.value)">
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
                            <button type="button" class="btn-cedula flex-fill" onclick="definirCedula(10)">R$ 10</button>
                            <button type="button" class="btn-cedula flex-fill" onclick="definirCedula(20)">R$ 20</button>
                            <button type="button" class="btn-cedula flex-fill" onclick="definirCedula(50)">R$ 50</button>
                            <button type="button" class="btn-cedula flex-fill" onclick="definirCedula(100)">R$ 100</button>
                            <button type="button" class="btn-cedula flex-fill" onclick="definirCedula(200)">R$ 200</button>
                            <button type="button" class="btn-cedula flex-fill active-cedula" onclick="definirValorExato()">Exato</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">Valor Recebido (R$)</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-light border-0 fw-bold">R$</span>
                                <input type="number" step="0.01" min="0" id="modal_valor_recebido" class="form-control border-0 bg-light fw-bold text-dark fs-4" placeholder="0,00" oninput="calcularTroco()">
                            </div>
                        </div>

                        <!-- Painel Dinâmico de Troco -->
                        <div id="trocoBox" class="troco-box troco-valido text-center">
                            <span class="text-muted fw-bold d-block mb-1" style="font-size:0.8rem;" id="trocoTitulo">TROCO A DEVOLVER</span>
                            <h3 class="fw-bold mb-0 text-success" id="trocoValorDisplay">R$ 0,00</h3>
                            <small class="text-danger fw-bold d-none" id="trocoAlertaInsuficiente"><i class="fas fa-exclamation-circle me-1"></i> Valor recebido é menor que o total!</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Voltar (ESC)</button>
                    <button type="button" class="btn btn-success px-4 py-2 fw-bold shadow-sm" id="btnConfirmarVendaModal" onclick="confirmarVendaFinal()">
                        <i class="fas fa-check-circle me-2"></i> Confirmar e Emitir NFC-e
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Alerta de Estoque (Frontend) -->
    <div class="modal fade" id="modalEstoque" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--mr-radius);">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Estoque Insuficiente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-boxes fa-3x text-danger mb-3"></i>
                    <h5 id="modalEstoqueMsg" class="fw-bold text-dark"></h5>
                    <p id="modalEstoqueDetalhe" class="text-muted mb-0"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Entendido (ESC)</button>
                </div>
            </div>
        </div>
    </div>

<script>
// ══ DADOS DO CATÁLOGO CLIENT-SIDE PARA BUSCA RÁPIDA ══════════════════════════
const catalogoProdutos = <?= json_encode($produtos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let carrinho = [];
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
    try {
        const ctx = getAudioContext();
        if (!ctx) return;

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.connect(gain);
        gain.connect(ctx.destination);

        const now = ctx.currentTime;

        if (type === 'success') {
            // Bipe suave de leitor de código de barras (880Hz / A5, senoidal, 75ms)
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now);
            gain.gain.setValueAtTime(0.18, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.075);
            osc.start(now);
            osc.stop(now + 0.075);
        } else if (type === 'error') {
            // Som de erro / alerta (280Hz, onda dente de serra, 160ms)
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(280, now);
            gain.gain.setValueAtTime(0.22, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.16);
            osc.start(now);
            osc.stop(now + 0.16);
        } else if (type === 'cash') {
            // Som harmônico de finalização de venda (suave arpeggio)
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(587.33, now); // D5
            osc.frequency.setValueAtTime(880, now + 0.06); // A5
            gain.gain.setValueAtTime(0.2, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.18);
            osc.start(now);
            osc.stop(now + 0.18);
        }
    } catch (e) {
        console.warn('Web Audio indisponível:', e);
    }
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
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
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
    }, 2800);
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

// ══ ADIÇÃO E MANIPULAÇÃO DO CARRINHO ═════════════════════════════════════════
function processarAdicao(id, nome, preco, qtdMax, qtd) {
    if (isNaN(qtd) || qtd <= 0) {
        mostrarAlertaEstoque('Quantidade inválida', 'Informe uma quantidade maior que zero.');
        return false;
    }

    const jaNoCarrinho = (carrinho.find(i => i.id === id)?.quantidade) || 0;
    const totalSolicit = jaNoCarrinho + qtd;

    if (totalSolicit > qtdMax) {
        mostrarAlertaEstoque(
            'Estoque Insuficiente!',
            `Produto: ${nome}\nDisponível: ${qtdMax} | Já no carrinho: ${jaNoCarrinho}\nSolicitado: ${qtd} (Total: ${totalSolicit})`
        );
        return false;
    }

    const item = carrinho.find(i => i.id === id);
    if (item) {
        item.quantidade += qtd;
    } else {
        carrinho.push({id, nome, preco, quantidade: qtd});
    }

    playBeep('success');
    showToast(`+${qtd}x ${nome}`, 'success', 'Item Inserido');
    atualizarTabela();
    return true;
}

function adicionarAoCarrinho() {
    const select = document.getElementById('produto_select');
    const qtdInput = document.getElementById('qtd_input');

    if (!select.value) {
        mostrarAlertaEstoque('Nenhum produto selecionado', 'Selecione um produto da lista ou bipe o código.');
        return;
    }

    const opt    = select.options[select.selectedIndex];
    const id     = parseInt(select.value);
    const nome   = opt.getAttribute('data-nome');
    const preco  = parseFloat(opt.getAttribute('data-preco'));
    const qtdMax = parseInt(opt.getAttribute('data-max'));
    const qtd    = parseInt(qtdInput.value) || 1;

    if (processarAdicao(id, nome, preco, qtdMax, qtd)) {
        select.value   = '';
        qtdInput.value = '1';
        document.getElementById('barcode_input').focus();
    }
}

// Busca rápida por leitor de código de barras ou ID
document.getElementById('barcode_input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const term = this.value.trim();
        if (!term) return;

        // Procura por barcode exato, id ou nome parcial
        const prod = catalogoProdutos.find(p => 
            (p.codigo_de_barra && p.codigo_de_barra.trim() === term) ||
            String(p.id) === term ||
            p.nome.toLowerCase().includes(term.toLowerCase())
        );

        if (prod) {
            const qtdInput = document.getElementById('qtd_input');
            const qtd = parseInt(qtdInput.value) || 1;
            if (processarAdicao(parseInt(prod.id), prod.nome, parseFloat(prod.preco), parseInt(prod.quantidade), qtd)) {
                this.value = '';
                qtdInput.value = '1';
                this.focus();
            }
        } else {
            playBeep('error');
            showToast(`Nenhum produto com o termo "${term}"`, 'danger', 'Não Encontrado');
            this.select();
        }
    }
});

function removerItem(id) {
    const item = carrinho.find(i => i.id === id);
    carrinho = carrinho.filter(i => i.id !== id);
    if (item) {
        showToast(`Removido: ${item.nome}`, 'danger', 'Item Cancelado');
    }
    atualizarTabela();
}

function limparCarrinho() {
    if (carrinho.length > 0) {
        if (confirm('Deseja realmente cancelar todos os itens do carrinho? (F9)')) {
            carrinho = [];
            try { localStorage.removeItem('mrstock_pdv_cart'); } catch(e) {}
            atualizarTabela();
            playBeep('error');
            showToast('Carrinho esvaziado com sucesso.', 'danger', 'Carrinho Limpo');
            document.getElementById('barcode_input').focus();
        }
    } else {
        showToast('O carrinho já está vazio.', 'info', 'PDV MrStock');
    }
}

function atualizarTabela() {
    const tbody  = document.getElementById('tabela_carrinho');
    const total  = document.getElementById('display_total');
    const input  = document.getElementById('cart_data');
    const badge  = document.getElementById('total_itens_badge');

    tbody.innerHTML = '';
    let totalVenda = 0, totalQtd = 0;

    if (carrinho.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-5"><strong>O carrinho está vazio.</strong><br>Bipe o código ou selecione os itens ao lado.</td></tr>';
        total.innerText = '0,00';
        input.value = '[]';
        badge.innerText = '0 itens';
        totalVendaAtual = 0.0;
        try { localStorage.removeItem('mrstock_pdv_cart'); } catch(e) {}
        return;
    }

    carrinho.forEach(item => {
        const sub = item.quantidade * item.preco;
        totalVenda += sub;
        totalQtd   += item.quantidade;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${item.nome}</strong><br><small class="text-muted">Cód: ${String(item.id).padStart(4,'0')}</small></td>
            <td class="text-center"><span class="badge bg-info text-dark" style="font-size:13px;">x${item.quantidade}</span></td>
            <td>R$ ${formatarMoeda(item.preco)}</td>
            <td class="text-end pe-4 text-success fw-bold">R$ ${formatarMoeda(sub)}</td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger shadow-sm" onclick="removerItem(${item.id})"><i class="fas fa-trash"></i></button></td>`;
        tbody.appendChild(tr);
    });

    totalVendaAtual = totalVenda;
    total.innerText = formatarMoeda(totalVenda);
    input.value     = JSON.stringify(carrinho);
    badge.innerText = totalQtd + ' itens';
    try {
        localStorage.setItem('mrstock_pdv_cart', JSON.stringify(carrinho));
    } catch(e) {}
}

// ══ MODAL DE PAGAMENTO, CÉDULAS E TROCO DINÂMICO ═════════════════════════════
function abrirModalPagamento() {
    if (carrinho.length === 0) {
        mostrarAlertaEstoque('Carrinho Vazio', 'Adicione pelo menos um produto antes de finalizar a venda (F4).');
        return;
    }

    // Sincroniza campos do form principal para o modal
    const formaPgtoPrincipal = document.getElementById('forma_pagamento_select').value;
    const clienteSelect = document.getElementById('cliente_select');
    const nomeCliente = clienteSelect.options[clienteSelect.selectedIndex]?.text || 'Consumidor Final';

    document.getElementById('modal_total_display').innerText = formatarMoeda(totalVendaAtual);
    document.getElementById('modal_cliente_display').innerText = 'Cliente: ' + nomeCliente;
    document.getElementById('modal_forma_pagamento').value = formaPgtoPrincipal;

    aoMudarFormaPagamentoModal(formaPgtoPrincipal);

    if (!modalFinalizarInstancia) {
        modalFinalizarInstancia = new bootstrap.Modal(document.getElementById('modalFinalizarVenda'));
    }
    modalFinalizarInstancia.show();

    setTimeout(() => {
        const inputRecebido = document.getElementById('modal_valor_recebido');
        if (formaPgtoPrincipal === 'DINHEIRO' && inputRecebido) {
            inputRecebido.focus();
            inputRecebido.select();
        }
    }, 300);
}

function sincronizarFormaPagamento(valor) {
    document.getElementById('modal_forma_pagamento').value = valor;
}

function aoMudarFormaPagamentoModal(forma) {
    document.getElementById('forma_pagamento_select').value = forma;
    const secaoDinheiro = document.getElementById('secaoDinheiroTroco');
    const btnConfirmar = document.getElementById('btnConfirmarVendaModal');
    const input = document.getElementById('modal_valor_recebido');

    if (forma === 'DINHEIRO') {
        secaoDinheiro.classList.remove('d-none');
        document.querySelectorAll('.btn-cedula').forEach(b => b.classList.remove('active-cedula'));
        input.value = '';
        calcularTroco();
    } else {
        secaoDinheiro.classList.add('d-none');
        btnConfirmar.disabled = false;
    }
}

function definirCedula(valor) {
    document.querySelectorAll('.btn-cedula').forEach(b => b.classList.remove('active-cedula'));
    const input = document.getElementById('modal_valor_recebido');
    input.value = parseFloat(valor).toFixed(2);
    calcularTroco();
}

function definirValorExato() {
    document.querySelectorAll('.btn-cedula').forEach(b => b.classList.remove('active-cedula'));
    const btnExato = document.querySelector('.btn-cedula[onclick="definirValorExato()"]');
    if (btnExato) btnExato.classList.add('active-cedula');
    const input = document.getElementById('modal_valor_recebido');
    input.value = totalVendaAtual.toFixed(2);
    calcularTroco();
}

function calcularTroco() {
    const input = document.getElementById('modal_valor_recebido');
    const forma = document.getElementById('modal_forma_pagamento').value;
    const btnConfirmar = document.getElementById('btnConfirmarVendaModal');
    const trocoBox = document.getElementById('trocoBox');
    const trocoDisplay = document.getElementById('trocoValorDisplay');
    const trocoTitulo = document.getElementById('trocoTitulo');
    const alertaInsuficiente = document.getElementById('trocoAlertaInsuficiente');

    if (forma !== 'DINHEIRO') {
        btnConfirmar.disabled = false;
        return;
    }

    const valorRaw = input.value.trim();
    if (valorRaw === '') {
        trocoBox.className = 'troco-box text-center bg-light border';
        trocoTitulo.innerText = 'DIGITE O VALOR OU SELECIONE A CÉDULA';
        trocoDisplay.className = 'fw-bold mb-0 text-muted';
        trocoDisplay.innerText = 'R$ 0,00';
        alertaInsuficiente.classList.add('d-none');
        btnConfirmar.disabled = true;
        return;
    }

    const valorRecebido = parseFloat(valorRaw) || 0;
    const troco = valorRecebido - totalVendaAtual;

    if (valorRecebido >= totalVendaAtual) {
        trocoBox.className = 'troco-box troco-valido text-center';
        trocoTitulo.innerText = 'TROCO A DEVOLVER';
        trocoDisplay.className = 'fw-bold mb-0 text-success';
        trocoDisplay.innerText = 'R$ ' + formatarMoeda(troco);
        alertaInsuficiente.classList.add('d-none');
        btnConfirmar.disabled = false;
    } else {
        trocoBox.className = 'troco-box troco-insuficiente text-center';
        trocoTitulo.innerText = 'VALOR INSUFICIENTE';
        trocoDisplay.className = 'fw-bold mb-0 text-danger';
        trocoDisplay.innerText = 'Faltam R$ ' + formatarMoeda(Math.abs(troco));
        alertaInsuficiente.classList.remove('d-none');
        btnConfirmar.disabled = true;
    }
}

function confirmarVendaFinal() {
    if (carrinho.length === 0) {
        mostrarAlertaEstoque('Carrinho Vazio', 'Adicione pelo menos um produto antes de emitir a NFC-e.');
        return;
    }

    const forma = document.getElementById('modal_forma_pagamento').value;
    if (forma === 'DINHEIRO') {
        const valorRecebido = parseFloat(document.getElementById('modal_valor_recebido').value) || 0;
        if (valorRecebido < totalVendaAtual) {
            playBeep('error');
            showToast('O valor recebido não pode ser menor que o total da venda.', 'danger', 'Valor Insuficiente');
            return;
        }
    }

    playBeep('cash');
    showToast('Processando pagamento e emitindo NFC-e...', 'success', 'Venda Confirmada');
    
    try { localStorage.removeItem('mrstock_pdv_cart'); } catch(e) {}

    // Submete o formulário principal
    setTimeout(() => {
        document.getElementById('formVenda').submit();
    }, 200);
}

// ══ FILTRAGEM DE PRODUTOS POR CATEGORIA (EM CASCATA) ═════════════════════════
function filtrarProdutosPorCategoria(categoriaId) {
    const selectProduto = document.getElementById('produto_select');
    if (!selectProduto) return;
    
    const options = selectProduto.querySelectorAll('option');
    let totalVisiveis = 0;

    options.forEach((opt, index) => {
        if (index === 0) { // Opção placeholder
            opt.selected = true;
            return;
        }
        const optCatId = opt.getAttribute('data-categoria-id');
        if (!categoriaId || optCatId === String(categoriaId)) {
            opt.style.display = '';
            totalVisiveis++;
        } else {
            opt.style.display = 'none';
        }
    });

    options[0].textContent = categoriaId 
        ? `-- Selecione o Produto (${totalVisiveis} disponíveis) --` 
        : '-- Selecione na Lista (Todos os Produtos) --';
}

// ══ LISTENER GLOBAL DE ATALHOS DE TECLADO (F1, F2, F4, F8, F9, ESC) ═══════════
window.addEventListener('keydown', function(e) {
    // F1: Abrir Guia de Atalhos & Manual do Caixa
    if (e.key === 'F1') {
        e.preventDefault();
        const modalGuiaEl = document.getElementById('modalGuiaAtalhos');
        if (modalGuiaEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalGuiaEl);
            modal.show();
        }
    }

    // F2: Focar busca / leitor de código de barras
    else if (e.key === 'F2') {
        e.preventDefault();
        const inputBarcode = document.getElementById('barcode_input');
        if (inputBarcode) {
            inputBarcode.focus();
            inputBarcode.select();
            showToast('Modo de busca rápida ativado', 'info', 'Atalho F2');
        }
    }

    // F4: Finalizar / Pagar venda
    else if (e.key === 'F4') {
        e.preventDefault();
        abrirModalPagamento();
    }

    // F8: Alternar forma de pagamento rapidamente
    else if (e.key === 'F8') {
        e.preventDefault();
        const formas = ['DINHEIRO', 'PIX', 'CARTÃO DE CRÉDITO', 'CARTÃO DE DÉBITO'];
        const selectPgto = document.getElementById('forma_pagamento_select');
        let idx = formas.indexOf(selectPgto.value);
        idx = (idx + 1) % formas.length;
        const novaForma = formas[idx];

        selectPgto.value = novaForma;
        sincronizarFormaPagamento(novaForma);
        aoMudarFormaPagamentoModal(novaForma);

        playBeep('success');
        showToast(`Pagamento alternado para: ${novaForma}`, 'info', 'Atalho F8');
    }

    // F9: Cancelar / Limpar carrinho
    else if (e.key === 'F9') {
        e.preventDefault();
        limparCarrinho();
    }

    // ESC: Fechar modais abertos
    else if (e.key === 'Escape') {
        const modalPagarEl = document.getElementById('modalFinalizarVenda');
        const modalEstoqueEl = document.getElementById('modalEstoque');
        const modalGuiaEl = document.getElementById('modalGuiaAtalhos');

        if (modalPagarEl && modalPagarEl.classList.contains('show')) {
            const modal = bootstrap.Modal.getInstance(modalPagarEl);
            if (modal) modal.hide();
        }
        if (modalEstoqueEl && modalEstoqueEl.classList.contains('show')) {
            const modal = bootstrap.Modal.getInstance(modalEstoqueEl);
            if (modal) modal.hide();
        }
        if (modalGuiaEl && modalGuiaEl.classList.contains('show')) {
            const modal = bootstrap.Modal.getInstance(modalGuiaEl);
            if (modal) modal.hide();
        }
    }
});

// Foco automático inicial no campo de bipe/código e restauração de carrinho salvo
document.addEventListener('DOMContentLoaded', function() {
    const inputBarcode = document.getElementById('barcode_input');
    if (inputBarcode) {
        inputBarcode.focus();
    }
    try {
        const cartSalvo = localStorage.getItem('mrstock_pdv_cart');
        if (cartSalvo) {
            const parsed = JSON.parse(cartSalvo);
            if (Array.isArray(parsed) && parsed.length > 0) {
                carrinho = parsed;
                atualizarTabela();
                showToast('Carrinho anterior restaurado.', 'info', 'Restauração Automática');
            }
        }
    } catch(e) {}
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
