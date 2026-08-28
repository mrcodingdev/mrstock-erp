<?php
/**
 * MrStock ERP — Central de Ajuda, Base de Conhecimento & FAQ Operacional
 * Versão 2.0 (SalesOps v0 + Benchmark Corporativo de Suporte)
 */
$pageTitle  = 'Ajuda & Suporte';
$activePage = 'ajuda';

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-circle-question text-primary me-2"></i>Central de Ajuda & Base de Conhecimento</h2>
        <p class="text-muted m-0">Consulte manuais operacionais, guia tátil de atalhos do caixa e respostas para dúvidas frequentes.</p>
    </div>
</div>

<div class="content-body">

    <!-- ══ BARRA DE BUSCA EM TEMPO REAL NO FAQ & MANUAIS (SEARCH-FIRST) ════════ -->
    <div class="so-card mb-4 border overflow-hidden">
        <div class="p-4 text-center" style="background: linear-gradient(135deg, rgba(40, 73, 54, 0.08) 0%, rgba(106, 228, 155, 0.12) 100%);">
            <span class="badge bg-primary text-white px-3 py-1 rounded-pill mb-2 fw-semibold">Base de Conhecimento 2.0</span>
            <h3 class="fw-bold text-dark mb-2">Como podemos te ajudar hoje?</h3>
            <p class="text-muted mb-3" style="font-size: 0.95rem;">Digite uma palavra-chave para filtrar instantaneamente atalhos, manuais e soluções operacionais.</p>
            <div class="so-search-box mx-auto position-relative" style="max-width: 580px;">
                <i class="fas fa-search search-icon fs-6 text-primary"></i>
                <input type="text" id="ajudaSearchInput" class="form-control form-control-lg shadow-sm bg-white" 
                       placeholder="Ex: atalhos, troco, desconto, nfc-e, etiqueta, estoque, cancelamento, dre..." 
                       oninput="filtrarAjuda(this.value)" autocomplete="off">
            </div>
            <div class="mt-2 text-muted small" id="searchFeedbackLabel">
                Consulte mais de 20 tópicos operacionais da Papelaria Real
            </div>
        </div>
    </div>

    <!-- ══ CARDS DE ACESSO RÁPIDO (BENTO BOX GRID 4 COLUNAS) ══════════════════ -->
    <div class="row g-3 mb-4" id="bentoCardsGrid">
        <!-- 1. Atalhos do PDV -->
        <div class="col-md-6 col-xl-3">
            <div class="so-card so-bento-card--primary h-100 p-3 border">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                         style="width: 46px; height: 46px; background: #284936; font-size: 1.25rem;">
                        <i class="fas fa-keyboard"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark m-0">Atalhos do PDV</h6>
                        <small class="text-muted">Guia F1 a F9 e ESC</small>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted">
                    <code>F2</code> Leitor | <code>F4</code> Pagar | <code>F9</code> Cancelar
                </div>
            </div>
        </div>

        <!-- 2. Manual de Estoque & Etiquetas -->
        <div class="col-md-6 col-xl-3">
            <div class="so-card so-bento-card--info h-100 p-3 border">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                         style="width: 46px; height: 46px; background: #0284c7; font-size: 1.25rem;">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark m-0">Estoque & Etiquetas</h6>
                        <small class="text-muted">Famílias e Barcode 128</small>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted">
                    10 Famílias Funcionais & Impressão Térmica
                </div>
            </div>
        </div>

        <!-- 3. Inteligência Fiscal & DRE -->
        <div class="col-md-6 col-xl-3">
            <div class="so-card so-bento-card--success h-100 p-3 border">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                         style="width: 46px; height: 46px; background: #16a34a; font-size: 1.25rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark m-0">Finanças & Curva ABC</h6>
                        <small class="text-muted">DRE Gerencial e Lucro Real</small>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted">
                    Auditoria de CMV e Margem Bruta
                </div>
            </div>
        </div>

        <!-- 4. Suporte Mr. Coding -->
        <div class="col-md-6 col-xl-3">
            <div class="so-card so-bento-card--warning h-100 p-3 border">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                         style="width: 46px; height: 46px; background: #d97706; font-size: 1.25rem;">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark m-0">Suporte Técnico</h6>
                        <small class="text-muted">Equipe Mr. Coding ETEC</small>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted">
                    Atendimento via WhatsApp e Suporte Direto
                </div>
            </div>
        </div>
    </div>

    <!-- ══ SEÇÃO 1: CHEAT SHEET TÁTIL DE ATALHOS DO CAIXA (PDV) ═══════════════ -->
    <div class="so-card mb-4 border ajuda-section" data-keywords="atalho teclado f1 f2 f4 f8 f9 esc pdv caixa tecla operacao comando">
        <div class="so-card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="so-card-title text-white m-0"><i class="fas fa-keyboard text-success me-2"></i>Guia Tátil de Atalhos de Teclado (Frente de Caixa)</h5>
            <span class="badge bg-success text-white">Alta Velocidade</span>
        </div>
        <div class="so-card-body p-4">
            <p class="text-muted small mb-3">O PDV do MrStock foi arquitetado para permitir operação 100% via teclado, sem necessidade de mouse:</p>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th width="15%" class="text-center">Tecla de Atalho</th>
                            <th width="25%">Ação Executada</th>
                            <th width="60%">Comportamento Operacional</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <tr>
                            <td class="text-center"><kbd class="bg-dark text-white px-2 py-1">F1</kbd></td>
                            <td><strong class="text-dark">Manual do Caixa</strong></td>
                            <td>Abre o modal flutuante com a listagem resumida de todos os comandos do sistema.</td>
                        </tr>
                        <tr>
                            <td class="text-center"><kbd class="bg-primary text-white px-2 py-1">F2</kbd></td>
                            <td><strong class="text-dark">Focar Leitor de Código de Barras</strong></td>
                            <td>Coloca o cursor no campo de bipagem automática ou busca rápida por nome de produto.</td>
                        </tr>
                        <tr>
                            <td class="text-center"><kbd class="bg-success text-white px-2 py-1">F4</kbd></td>
                            <td><strong class="text-dark">Pagar / Emitir NFC-e</strong></td>
                            <td>Abre o modal de finalização com cálculo de troco, seleção de pagamento e emissão fiscal.</td>
                        </tr>
                        <tr>
                            <td class="text-center"><kbd class="bg-warning text-dark px-2 py-1">F8</kbd></td>
                            <td><strong class="text-dark">Alternar Forma de Pagamento</strong></td>
                            <td>Alterna ciclicamente entre Dinheiro, PIX, Cartão de Crédito e Cartão de Débito.</td>
                        </tr>
                        <tr>
                            <td class="text-center"><kbd class="bg-danger text-white px-2 py-1">F9</kbd></td>
                            <td><strong class="text-dark">Cancelar Venda Atual</strong></td>
                            <td>Limpa o carrinho de compras atual e restabelece o caixa para a próxima venda.</td>
                        </tr>
                        <tr>
                            <td class="text-center"><kbd class="bg-secondary text-white px-2 py-1">ESC</kbd></td>
                            <td><strong class="text-dark">Fechar Modais e Janelas</strong></td>
                            <td>Fecha qualquer modal aberto (pagamento, atalhos, alerta de estoque) e devolve o foco ao leitor.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ══ SEÇÃO 2: MANUAIS OPERACIONAIS PASSO A PASSO (ACCORDION) ════════════ -->
    <div class="so-card mb-4 border">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <h5 class="so-card-title"><i class="fas fa-book-open text-primary me-2"></i>Manuais Operacionais Passo a Passo</h5>
            <span class="badge bg-primary text-white" id="modulosCountBadge">5 Módulos</span>
        </div>
        <div class="so-card-body p-0">
            <div class="accordion accordion-flush" id="accordionManuais">

                <!-- ── MÓDULO 1: FRENTE DE CAIXA (PDV) ────────────────────────── -->
                <div class="accordion-item ajuda-item" data-keywords="pdv caixa venda cupom nfce troco desconto leitor bipar cartao dinheiro pix">
                    <h2 class="accordion-header" id="headingM1">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseM1" aria-expanded="false">
                            <i class="fas fa-cash-register text-success me-2"></i> Módulo 1: Operando a Frente de Caixa (PDV)
                        </button>
                    </h2>
                    <div id="collapseM1" class="accordion-collapse collapse" data-bs-parent="#accordionManuais">
                        <div class="accordion-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-barcode text-primary me-1"></i> 1. Registro de Produtos</h6>
                                        <p class="text-muted small m-0">
                                            Utilize o leitor óptico com foco no campo <kbd class="bg-primary text-white">F2</kbd>. O item é adicionado instantaneamente ao cupom. Você também pode clicar nos cards da <strong>Grade de Catálogo Rápido</strong> ou filtrar pelas <strong>Famílias de Produtos</strong>.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-calculator text-primary me-1"></i> 2. Ajuste de Quantidade & Desconto</h6>
                                        <p class="text-muted small m-0">
                                            Ajuste a quantidade diretamente na coluna do cupom através dos botões táteis <code>[-]</code> e <code>[+]</code>. Para aplicar desconto em reais, digite o valor no campo <strong>DESCONTO (R$)</strong>. O totalizador recalculará automaticamente.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-user-tag text-primary me-1"></i> 3. Identificação do Cliente & CPF na Nota</h6>
                                        <p class="text-muted small m-0">
                                            Opcionalmente, selecione um cliente cadastrado ou digite o CPF/CNPJ do consumidor no campo de identificação fiscal para vinculação ao cupom DANFE.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-receipt text-primary me-1"></i> 4. Pagamento, Troco & Emissão</h6>
                                        <p class="text-muted small m-0">
                                            Pressione <kbd class="bg-success text-white">F4</kbd> para abrir o modal de finalização. Ao selecionar <em>Dinheiro</em>, use os botões rápidos de cédulas (R$ 10, 20, 50, 100) para cálculo imediato de troco. Confirme para emitir o cupom térmico com QR Code.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── MÓDULO 2: ESTOQUE, PRODUTOS & ETIQUETAS ────────────────── -->
                <div class="accordion-item ajuda-item" data-keywords="estoque produtos cadastro categoria familias lucro custo etiqueta barcode avaria doacao">
                    <h2 class="accordion-header" id="headingM2">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseM2" aria-expanded="false">
                            <i class="fas fa-boxes-stacked text-primary me-2"></i> Módulo 2: Gestão do Catálogo, Estoque & Etiquetas
                        </button>
                    </h2>
                    <div id="collapseM2" class="accordion-collapse collapse" data-bs-parent="#accordionManuais">
                        <div class="accordion-body p-4">
                            <ul class="list-group list-group-flush border rounded mb-3 small">
                                <li class="list-group-item p-3">
                                    <strong class="text-dark d-block mb-1">Classificação por Famílias Específicas:</strong>
                                    No MrStock ERP, os produtos são categorizados por famílias reais da papelaria (ex: <em>Cadernos & Blocos, Canetas & Marcadores, Papéis & Folhas</em>), eliminando termos genéricos e garantindo relatórios precisos.
                                </li>
                                <li class="list-group-item p-3">
                                    <strong class="text-dark d-block mb-1">Cálculo de Formação de Preço e Margem de Lucro:</strong>
                                    Ao cadastrar um produto, o sistema calcula em tempo real a margem bruta estimada a partir do preço de compra e preço de venda. O perfil Operador de Caixa nunca visualiza os custos.
                                </li>
                                <li class="list-group-item p-3">
                                    <strong class="text-dark d-block mb-1">Impressão de Etiquetas de Código de Barras:</strong>
                                    No menu <em>Estoque & Produtos</em>, utilize o botão de etiqueta térmica para gerar etiquetas padronizadas no padrão <strong>Code 128 Vetorial</strong>, prontas para gôndolas e prateleiras.
                                </li>
                                <li class="list-group-item p-3">
                                    <strong class="text-dark d-block mb-1">Movimentações de Ajuste Manual:</strong>
                                    Para registrar quebras, avarias, doações ou acertos de inventário, utilize a tela <em>Movimentações</em> selecionando o tipo correspondente. Toda alteração fica auditada no histórico.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ── MÓDULO 3: COMPRAS & FORNECEDORES ────────────────────────── -->
                <div class="accordion-item ajuda-item" data-keywords="compras fornecedor ordem de compra entrada mercadoria estorno cancelamento nota">
                    <h2 class="accordion-header" id="headingM3">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseM3" aria-expanded="false">
                            <i class="fas fa-truck-ramp-box text-warning me-2"></i> Módulo 3: Compras & Entrada de Mercadorias
                        </button>
                    </h2>
                    <div id="collapseM3" class="accordion-collapse collapse" data-bs-parent="#accordionManuais">
                        <div class="accordion-body p-4">
                            <p class="text-muted small mb-3">O fluxo de compras garante o reabastecimento seguro com atualização automática dos saldos físicos:</p>
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <span class="badge bg-primary text-white mb-2">Passo 1</span>
                                        <h6 class="fw-bold text-dark">Lançamento da Compra</h6>
                                        <p class="text-muted small m-0">Acesse <em>Compras > Nova Compra</em>, selecione o fornecedor cadastrado e adicione os itens e quantidades recebidas.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <span class="badge bg-success text-white mb-2">Passo 2</span>
                                        <h6 class="fw-bold text-dark">Entrada no Estoque</h6>
                                        <p class="text-muted small m-0">Ao salvar a compra, o MrStock injeta imediatamente as quantidades no estoque e registra a movimentação de entrada.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <span class="badge bg-danger text-white mb-2">Passo 3</span>
                                        <h6 class="fw-bold text-dark">Estorno em Cancelamento</h6>
                                        <p class="text-muted small m-0">Caso uma compra seja cancelada, o sistema efetua o estorno automático do estoque prevenindo furos contábeis.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── MÓDULO 4: FINANÇAS, DRE & CURVA ABC ─────────────────────── -->
                <div class="accordion-item ajuda-item" data-keywords="financas relatorios dre curva abc cmv lucro faturamento receita analise bi">
                    <h2 class="accordion-header" id="headingM4">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseM4" aria-expanded="false">
                            <i class="fas fa-chart-pie text-info me-2"></i> Módulo 4: Relatórios, DRE Gerencial & Curva ABC
                        </button>
                    </h2>
                    <div id="collapseM4" class="accordion-collapse collapse" data-bs-parent="#accordionManuais">
                        <div class="accordion-body p-4">
                            <div class="alert alert-light border shadow-sm mb-3">
                                <h6 class="fw-bold text-dark mb-1"><i class="fas fa-square-root-variable text-primary me-1"></i> Entendendo a Curva ABC (Princípio de Pareto)</h6>
                                <p class="text-muted small m-0">
                                    No menu <em>Relatórios > Análise & Curva ABC</em>, os produtos são divididos em:
                                    <br>• <strong>Classe A (80% da receita):</strong> Os carros-chefe que nunca podem faltar em estoque (ex: Cadernos e Sulfite A4).
                                    <br>• <strong>Classe B (15% da receita):</strong> Produtos de giro intermediário com reposição regular.
                                    <br>• <strong>Classe C (5% da receita):</strong> Produtos de cauda longa com baixo impacto no faturamento.
                                </p>
                            </div>
                            <div class="alert alert-light border shadow-sm m-0">
                                <h6 class="fw-bold text-dark mb-1"><i class="fas fa-receipt text-success me-1"></i> DRE Gerencial & Cálculo de CMV</h6>
                                <p class="text-muted small m-0">
                                    O DRE consolida a <strong>Receita Bruta Total</strong>, subtrai os descontos concedidos e o <strong>CMV (Custo da Mercadoria Vendida)</strong>, entregando o <strong>Lucro Bruto Real</strong> com precisão contábil.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── MÓDULO 5: CONTINGÊNCIA & RESOLUÇÃO DE PROBLEMAS ────────── -->
                <div class="accordion-item ajuda-item" data-keywords="contingencia problema erro leitor internet impressora travada bobina reimprimir backup">
                    <h2 class="accordion-header" id="headingM5">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseM5" aria-expanded="false">
                            <i class="fas fa-shield-virus text-danger me-2"></i> Módulo 5: Resolução de Problemas & Contingência Operacional
                        </button>
                    </h2>
                    <div id="collapseM5" class="accordion-collapse collapse" data-bs-parent="#accordionManuais">
                        <div class="accordion-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-danger mb-2"><i class="fas fa-wifi text-danger me-1"></i> O que fazer se a internet cair?</h6>
                                        <p class="text-muted small m-0">
                                            O MrStock ERP roda em arquitetura híbrida (XAMPP local). Se a conexão com a nuvem oscilar, o PDV local continua operando perfeitamente sem interrupção nas vendas da loja.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-danger mb-2"><i class="fas fa-barcode text-danger me-1"></i> E se o leitor de código de barras falhar?</h6>
                                        <p class="text-muted small m-0">
                                            Pressione <kbd class="bg-primary text-white">F2</kbd> e digite parte do nome do produto (ex: "Caderno") ou clique diretamente no card do produto na grade visual à direita.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-danger mb-2"><i class="fas fa-print text-danger me-1"></i> Como reimprimir um cupom fiscal?</h6>
                                        <p class="text-muted small m-0">
                                            Acesse o menu <em>Histórico de Vendas</em> ou <em>Consulta Fiscal NFC-e</em>, localize a venda pelo número ou data e clique no botão de impressão térmica.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <h6 class="fw-bold text-danger mb-2"><i class="fas fa-database text-danger me-1"></i> Como fazer backup preventivo?</h6>
                                        <p class="text-muted small m-0">
                                            Acesse <em>Configurações > Backup & Diagnóstico</em> e clique no botão <strong>Baixar Backup SQL</strong> para salvar uma cópia instantânea de todas as tabelas.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══ SEÇÃO 3: CONTATO & SUPORTE DIRETO (MR. CODING) ═════════════════════ -->
    <div class="so-card border">
        <div class="so-card-header bg-primary text-white">
            <h5 class="so-card-title text-white m-0"><i class="fas fa-headset me-2"></i>Equipe Técnica & Suporte Acadêmico (Mr. Coding)</h5>
        </div>
        <div class="so-card-body p-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8 col-12">
                    <h6 class="fw-bold text-dark mb-2">Projeto de Conclusão de Curso (TCC) — ETEC Fernando Prestes (Sorocaba/SP)</h6>
                    <p class="text-muted small mb-3">
                        O MrStock ERP foi projetado e desenvolvido pelo time de engenharia de software <strong>Mr. Coding</strong>:
                    </p>
                    <div class="row g-2 small">
                        <div class="col-md-6 col-12">
                            <span class="fw-bold text-dark">• Douglas:</span> <span class="text-muted">Direção Técnica e Arquitetura</span>
                        </div>
                        <div class="col-md-6 col-12">
                            <span class="fw-bold text-dark">• Nikolas:</span> <span class="text-muted">Banco de Dados e Modelagem DER</span>
                        </div>
                        <div class="col-md-6 col-12">
                            <span class="fw-bold text-dark">• Cesar:</span> <span class="text-muted">Requisitos de Negócio e Cliente</span>
                        </div>
                        <div class="col-md-6 col-12">
                            <span class="fw-bold text-dark">• Enzo:</span> <span class="text-muted">Documentação e Processos</span>
                        </div>
                        <div class="col-md-6 col-12">
                            <span class="fw-bold text-dark">• Eduardo Sugahara:</span> <span class="text-muted">Demonstração e Navegação do Sistema</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 text-lg-end">
                    <div class="border rounded p-3 bg-light text-center">
                        <span class="text-muted small d-block mb-1">Canal de Contato Oficial</span>
                        <strong class="text-dark d-block mb-2"><i class="fab fa-whatsapp text-success me-1"></i> Suporte WhatsApp</strong>
                        <a href="https://wa.me/5515991234567?text=Ola%20equipe%20MrStock%2C%20preciso%20de%20suporte%20no%20ERP" 
                           target="_blank" class="btn btn-success fw-bold px-4 shadow-sm w-100">
                            <i class="fab fa-whatsapp me-1"></i> Falar com o Suporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ══ SCRIPT DE FILTRAGEM DINÂMICA SEARCH-FIRST ═════════════════════════════ -->
<script>
function filtrarAjuda(termo) {
    termo = termo.toLowerCase().trim();
    const itens = document.querySelectorAll('.ajuda-item, .ajuda-section');
    const feedbackLabel = document.getElementById('searchFeedbackLabel');
    let encontrados = 0;

    itens.forEach(el => {
        const keywords = (el.getAttribute('data-keywords') || '') + ' ' + el.innerText.toLowerCase();
        if (!termo || keywords.includes(termo)) {
            el.style.display = '';
            encontrados++;
            // Se for accordion e houver busca, abre automaticamente o colapso
            if (termo && el.classList.contains('ajuda-item')) {
                const collapseEl = el.querySelector('.accordion-collapse');
                if (collapseEl && !collapseEl.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                    bsCollapse.show();
                }
            }
        } else {
            el.style.display = 'none';
        }
    });

    if (feedbackLabel) {
        if (termo) {
            feedbackLabel.innerHTML = `Exibindo <strong>${encontrados}</strong> módulos e seções encontrados para "<em>${termo}</em>".`;
        } else {
            feedbackLabel.textContent = 'Consulte mais de 20 tópicos operacionais da Papelaria Real';
        }
    }
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>