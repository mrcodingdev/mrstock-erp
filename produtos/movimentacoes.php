<?php
$pageTitle  = 'MrStock ERP - Movimentações de Estoque';
$activePage = 'movimentacoes';
require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/auth.php';

// Configuração da Paginação
$limit = 15;
$page = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Contagem total de movimentações
$totalRows = (int)$pdo->query("SELECT COUNT(*) FROM movimentacoes")->fetchColumn();
$totalPages = ceil($totalRows / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// Busca Histórico de Movimentações paginada
$stmt = $pdo->prepare("SELECT m.*, p.nome as produto_nome, p.codigo_de_barra FROM movimentacoes m JOIN produtos p ON m.produto_id = p.id ORDER BY m.data_movimento DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movimentacoes = $stmt->fetchAll();

$stmtProd = $pdo->query("SELECT id, nome, quantidade FROM produtos WHERE status = 'ativo' ORDER BY nome ASC");
$produtosLista = $stmtProd->fetchAll();

require_once __DIR__ . '/../inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-arrow-right-arrow-left text-primary me-2"></i>Movimentações de Estoque</h2>
        <p class="text-muted m-0">Rastreabilidade completa de Entradas, Saídas, Ajustes, Devoluções e Perdas.</p>
    </div>
    <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMovimentacao">
        <i class="fas fa-plus-circle me-1"></i> Nova Movimentação
    </button>
</div>

<div class="content-body">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'sucesso'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <strong>Sucesso!</strong> Movimentação registrada e saldo de estoque atualizado. 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'erro_dados'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <strong>Erro!</strong> Dados inválidos. Verifique os campos informados. 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'erro_saldo_insuficiente'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Saldo Insuficiente!</strong> A quantidade solicitada para saída é maior que o saldo disponível em estoque (Disponível: <strong><?= (int)($_GET['disponivel'] ?? 0) ?> un</strong>).
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($_GET['msg'] === 'erro_banco'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <strong>Erro no Banco de Dados!</strong> Não foi possível processar a movimentação. Tente novamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ══ BARRA DE LIVE SEARCH ═════════════════════════════════════════════ -->
    <div class="so-card mb-3">
        <div class="so-card-body p-3">
            <div class="so-search-box w-100" style="max-width:100%;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="liveSearchMovimentacoes" class="form-control" placeholder="Filtrar movimentações ao vivo por produto, tipo, observação ou data..." onkeyup="filtrarMovimentacoes(this)">
            </div>
        </div>
    </div>

    <!-- ══ TABELA MODULAR DE MOVIMENTAÇÕES ══════════════════════════════════ -->
    <div class="so-card">
        <div class="so-card-header">
            <h5 class="so-card-title"><i class="fas fa-clock-rotate-left text-primary"></i> Registro Cronológico de Movimentações</h5>
            <span class="so-badge so-badge-primary"><?= $totalRows ?> registros</span>
        </div>
        <div class="so-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 so-table align-middle" id="tabelaMovimentacoes">
                    <thead>
                        <tr>
                            <th width="16%">Data / Hora</th>
                            <th width="18%">Tipo de Movimento</th>
                            <th width="32%">Produto</th>
                            <th width="10%" class="text-center">Qtd</th>
                            <th width="24%">Observação / Justificativa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($movimentacoes) > 0): ?>
                            <?php foreach ($movimentacoes as $m):
                                $tipoMap = [
                                    'entrada_compra'       => ['so-badge-success', 'fa-arrow-down',   'Entrada (Compra)'],
                                    'saida_venda'          => ['so-badge-primary', 'fa-arrow-up',     'Saída (Venda)'],
                                    'devolucao_cliente'    => ['so-badge-info',    'fa-undo',         'Devolução Cliente'],
                                    'devolucao_fornecedor' => ['so-badge-warning', 'fa-reply',        'Devolução Fornec.'],
                                    'perda'                => ['so-badge-danger',  'fa-times-circle', 'Perda / Avaria'],
                                ];
                                [$badgeCls, $icone, $label] = $tipoMap[$m['tipo']] ?? ['so-badge-primary','fa-question','Desconhecido'];
                                $isEntrada = in_array($m['tipo'], ['entrada_compra','devolucao_cliente']);
                            ?>
                            <tr class="linha-movimentacao">
                                <td>
                                    <span class="text-muted small">
                                        <i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($m['data_movimento'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="so-badge <?= htmlspecialchars($badgeCls) ?>">
                                        <i class="fas <?= htmlspecialchars($icone) ?>"></i> <?= htmlspecialchars($label) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($m['produto_nome']) ?></strong>
                                    <?php if (!empty($m['codigo_de_barra'])): ?>
                                        <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i><?= htmlspecialchars($m['codigo_de_barra']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isEntrada): ?>
                                        <span class="text-success fw-bold">+ <?= (int)$m['quantidade'] ?></span>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold">- <?= (int)$m['quantidade'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($m['observacao'] ?: '--') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fs-1 d-block mb-3 opacity-50"></i>
                                    Nenhum histórico de movimentações localizado.
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
                    Exibindo <strong><?= $firstItem ?></strong> a <strong><?= $lastItem ?></strong> de <strong><?= $totalRows ?></strong> registros
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação da listagem">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <?php
                                $queryParams = $_GET;
                                $queryParams['pagina'] = $page - 1;
                            ?>
                            <a class="page-link" href="movimentacoes.php?<?= http_build_query($queryParams) ?>" aria-label="Anterior">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        
                        <?php
                        $range = 2;
                        $startPage = max(1, $page - $range);
                        $endPage = min($totalPages, $page + $range);
                        
                        if ($startPage > 1) {
                            $queryParams = $_GET;
                            $queryParams['pagina'] = 1;
                            echo '<li class="page-item"><a class="page-link" href="movimentacoes.php?' . http_build_query($queryParams) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                            $queryParams = $_GET;
                            $queryParams['pagina'] = $i;
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="movimentacoes.php?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            $queryParams = $_GET;
                            $queryParams['pagina'] = $totalPages;
                            echo '<li class="page-item"><a class="page-link" href="movimentacoes.php?' . http_build_query($queryParams) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                           <?php
                               $queryParams = $_GET;
                               $queryParams['pagina'] = $page + 1;
                           ?>
                            <a class="page-link" href="movimentacoes.php?<?= http_build_query($queryParams) ?>" aria-label="Próximo">
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

<!-- Modal Nova Movimentação -->
<div class="modal fade" id="modalMovimentacao" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--mr-radius);">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-arrow-right-arrow-left text-primary me-2"></i> Registrar Movimentação</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/produtos/functions.php?tipo=movimentacao" method="POST">
                <?= csrf_input() ?>
                <div class="modal-body bg-light p-4">
                    <input type="hidden" name="acao" value="registrar">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Movimento <span class="text-danger">*</span></label>
                        <select class="form-select bg-white" name="tipo" required>
                            <option value="" disabled selected>Escolha o tipo...</option>
                            <optgroup label="Entradas (Soma no Estoque)">
                                <option value="entrada_compra">Entrada de Compra (Fornecedor)</option>
                                <option value="devolucao_cliente">Devolução de Cliente</option>
                            </optgroup>
                            <optgroup label="Saídas (Subtrai do Estoque)">
                                <option value="saida_venda">Saída Avulsa (Ajuste)</option>
                                <option value="devolucao_fornecedor">Devolução para Fornecedor</option>
                                <option value="perda">Perda / Avaria</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Produto <span class="text-danger">*</span></label>
                        <select class="form-select bg-white" name="produto_id" required>
                            <option value="" disabled selected>Selecione um produto...</option>
                            <?php foreach ($produtosLista as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?> (Estoque: <?= $p['quantidade'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantidade <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantidade" min="1" required placeholder="Ex: 5">
                        <small class="text-muted">Informe números positivos. O sistema soma ou subtrai conforme a regra do Tipo.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Observação / Motivo / NFe</label>
                        <textarea class="form-control" name="observacao" rows="2" placeholder="Ex: Ajuste de contagem física de estoque..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm"><i class="fas fa-check-circle me-1"></i> Confirmar Movimentação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filtrarMovimentacoes(input) {
    const termo = input.value.toLowerCase().trim();
    const linhas = document.querySelectorAll('#tabelaMovimentacoes tbody .linha-movimentacao');
    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
