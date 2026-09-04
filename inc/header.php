<?php
/**
 * MrStock ERP - Header Reutilizável (SalesOps v0 Design System)
 *
 * Variáveis esperadas (definidas antes do include):
 *   $pageTitle  string  - Ex: "MrStock ERP - Dashboard"
 *   $activePage string  - Ex: "dashboard" | "produtos" | "clientes" | ...
 */

// Prepara dados do usuário (disponíveis via $_SESSION após auth.php)
$_uName  = htmlspecialchars($_SESSION['user_name']   ?? 'Usuário', ENT_QUOTES, 'UTF-8');
$_uPerf  = htmlspecialchars($_SESSION['user_perfil'] ?? 'gestor', ENT_QUOTES, 'UTF-8');
$_uLabel = match($_uPerf) {
    'admin' => 'Administrador',
    'caixa' => 'Operador de Caixa',
    default => 'Gestor',
};
$_uInitials = $_uPerf === 'admin' ? 'AD' : ($_uPerf === 'caixa' ? 'CX' : strtoupper(substr($_uName, 0, 2)));
$_ap = $activePage ?? '';

// Estrutura de Menus Agrupados com RBAC
$_menuGroups = [
    [
        'type'     => 'link',
        'id'       => 'dashboard',
        'title'    => 'Dashboard',
        'href'     => BASE_URL . '/dashboard.php',
        'icon'     => 'fa-solid fa-gauge-high',
        'active'   => ($_ap === 'dashboard'),
        'rbac'     => null, // Acesso livre (Admin e Caixa)
    ],
    [
        'type'     => 'group',
        'id'       => 'menuVendas',
        'title'    => 'Operação de Vendas',
        'icon'     => 'fa-solid fa-cash-register',
        'active'   => in_array($_ap, ['pdv', 'historico', 'fiscal']),
        'rbac'     => null,
        'items'    => [
            ['id' => 'pdv',       'href' => BASE_URL . '/vendas/pdv.php',       'icon' => 'fa-solid fa-barcode',         'label' => 'PDV (Frente de Caixa)', 'rbac' => null],
            ['id' => 'historico', 'href' => BASE_URL . '/vendas/historico.php', 'icon' => 'fa-solid fa-clock-rotate-left', 'label' => 'Histórico de Vendas',  'rbac' => 'admin'],
            ['id' => 'fiscal',    'href' => BASE_URL . '/vendas/nfce.php',      'icon' => 'fa-solid fa-receipt',           'label' => 'Consulta Fiscal NFC-e', 'rbac' => null],
        ]
    ],
    [
        'type'     => 'group',
        'id'       => 'menuEstoque',
        'title'    => 'Catálogo & Estoque',
        'icon'     => 'fa-solid fa-boxes-stacked',
        'active'   => in_array($_ap, ['produtos', 'categorias', 'movimentacoes', 'etiquetas', 'lotes']),
        'rbac'     => null,
        'items'    => [
            ['id' => 'produtos',      'href' => BASE_URL . '/produtos/index.php',         'icon' => 'fa-solid fa-box-open',           'label' => 'Estoque & Produtos',         'rbac' => null],
            ['id' => 'lotes',         'href' => BASE_URL . '/lotes/index.php',            'icon' => 'fa-solid fa-calendar-days',       'label' => 'Lotes & Validades',         'rbac' => 'admin'],
            ['id' => 'etiquetas',     'href' => BASE_URL . '/produtos/etiquetas.php',     'icon' => 'fa-solid fa-barcode',            'label' => 'Gerador de Etiquetas',      'rbac' => 'admin'],
            ['id' => 'categorias',    'href' => BASE_URL . '/categorias/index.php',       'icon' => 'fa-solid fa-tags',               'label' => 'Categorias',                'rbac' => 'admin'],
            ['id' => 'movimentacoes', 'href' => BASE_URL . '/produtos/movimentacoes.php', 'icon' => 'fa-solid fa-arrow-right-arrow-left', 'label' => 'Movimentações de Estoque', 'rbac' => null],
        ]
    ],
    [
        'type'     => 'group',
        'id'       => 'menuCompras',
        'title'    => 'Compras & Contatos',
        'icon'     => 'fa-solid fa-truck-ramp-box',
        'active'   => in_array($_ap, ['compras', 'fornecedores', 'clientes']),
        'rbac'     => null,
        'items'    => [
            ['id' => 'compras',      'href' => BASE_URL . '/compras/index.php',      'icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Ordens de Compra', 'rbac' => 'admin'],
            ['id' => 'fornecedores', 'href' => BASE_URL . '/fornecedores/index.php', 'icon' => 'fa-solid fa-truck',               'label' => 'Fornecedores',     'rbac' => 'admin'],
            ['id' => 'clientes',     'href' => BASE_URL . '/clientes/index.php',     'icon' => 'fa-solid fa-users',               'label' => 'Clientes',         'rbac' => null],
        ]
    ],
    [
        'type'     => 'group',
        'id'       => 'menuRelatorios',
        'title'    => 'Inteligência & Relatórios',
        'icon'     => 'fa-solid fa-chart-pie',
        'active'   => in_array($_ap, ['analise', 'relatorios', 'logs']),
        'rbac'     => 'admin', // Grupo inteiro exclusivo Admin
        'items'    => [
            ['id' => 'analise',    'href' => BASE_URL . '/relatorios/analise.php', 'icon' => 'fa-solid fa-chart-line',     'label' => 'Centro de Análise',     'rbac' => 'admin'],
            ['id' => 'relatorios', 'href' => BASE_URL . '/relatorios/index.php',   'icon' => 'fa-solid fa-print',          'label' => 'Central de Relatórios', 'rbac' => 'admin'],
            ['id' => 'logs',       'href' => BASE_URL . '/relatorios/logs.php',    'icon' => 'fa-solid fa-clipboard-list', 'label' => 'Auditoria & Logs',       'rbac' => 'admin'],
        ]
    ],
];
// Preferências visuais da interface (carregadas da tabela configuracoes com cache em session)
if (!isset($_SESSION['cfg_densidade_tabela']) && isset($pdo)) {
    try {
        $stmtCfg = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('densidade_tabela', 'tamanho_fonte', 'linhas_zebradas')");
        $cfgs = $stmtCfg->fetchAll(PDO::FETCH_KEY_PAIR);
        $_SESSION['cfg_densidade_tabela'] = $cfgs['densidade_tabela'] ?? 'padrao';
        $_SESSION['cfg_tamanho_fonte']    = $cfgs['tamanho_fonte'] ?? 'normal';
        $_SESSION['cfg_linhas_zebradas']  = $cfgs['linhas_zebradas'] ?? '0';
    } catch(Exception $e) {}
}

$_bodyClasses = [];
if (($_SESSION['cfg_densidade_tabela'] ?? '') === 'compacto') {
    $_bodyClasses[] = 'table-density-compact';
}
if (($_SESSION['cfg_tamanho_fonte'] ?? '') === 'grande') {
    $_bodyClasses[] = 'font-size-comfort';
}
if (($_SESSION['cfg_linhas_zebradas'] ?? '') === '1') {
    $_bodyClasses[] = 'table-striped-on';
}
$_bodyClassStr = implode(' ', $_bodyClasses);

// Normalização Universal de Título (Browser Tab e Topbar)
$_rawTitle = $pageTitle ?? 'Dashboard';
$_pageTitleClean = trim(str_ireplace(['MrStock ERP - ', 'MrStock ERP — ', 'MrStock ERP'], '', $_rawTitle));
if (empty($_pageTitleClean)) {
    $_pageTitleClean = 'Dashboard';
}
$_browserTitle = 'MrStock ERP - ' . $_pageTitleClean;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MrStock ERP - Sistema Integrado de Gestão Comercial, Controle de Estoque com Validades e PDV Ágil para Papelaria Real.">
    <title><?= htmlspecialchars($_browserTitle, ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Script Anti-FOUC para Restauração Instantânea do Estado da Sidebar -->
    <script>
    (function() {
        try {
            if (localStorage.getItem('mrstock_sidebar_state') === 'collapsed') {
                document.documentElement.classList.add('sidebar-collapsed-preload');
            }
        } catch (e) {}
    })();
    </script>

    <link href="<?= BASE_URL ?>/css/bootstrap.min.css?v=2.2.0" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css?v=2.2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/inter.css?v=2.2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.min.css?v=2.2.2">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" type="image/x-icon">
    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= $_bodyClassStr ?>">

    <!-- ===== OVERLAY PARA MOBILE ===== -->
    <div class="mobile-overlay" onclick="toggleSidebar()"></div>

    <!-- ===== SIDEBAR SALESOPS (v0) ===== -->
    <aside class="so-sidebar" id="soSidebar">
        <!-- Marca / Brand com Logo Oficial do MrStock -->
        <div class="so-brand">
            <a href="<?= BASE_URL ?>/dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="so-brand__logo">
                    <img src="<?= BASE_URL ?>/assets/img/logo-mrstock.svg" alt="MrStock Logo" width="28" height="28" style="width: 28px; height: 28px; object-fit: contain; display: block;">
                </div>
                <span class="so-brand__name text-white fw-bold">MrStock <small class="fw-light opacity-75" style="font-size: 0.8rem;">ERP</small></span>
            </a>
        </div>

        <!-- Navegação Principal -->
        <nav class="so-nav">
            <ul class="so-nav__list">
                <?php foreach ($_menuGroups as $group): ?>
                    <?php
                    // Filtro de Grupo por RBAC
                    if (!empty($group['rbac']) && $group['rbac'] === 'admin' && $_uPerf !== 'admin') {
                        continue;
                    }

                    if ($group['type'] === 'link'):
                    ?>
                        <!-- Item Direto (Sem Submenu) -->
                        <li class="so-nav__item">
                            <a href="<?= $group['href'] ?>" 
                               class="so-link <?= $group['active'] ? 'is-active' : '' ?>"
                               title="<?= $group['title'] ?>"
                               data-tooltip="<?= $group['title'] ?>">
                                <i class="<?= $group['icon'] ?>"></i>
                                <span class="so-label"><?= $group['title'] ?></span>
                            </a>
                        </li>
                    <?php 
                    elseif ($group['type'] === 'group'): 
                        // Filtra itens visíveis do grupo
                        $visibleItems = [];
                        foreach ($group['items'] as $it) {
                            if (!empty($it['rbac']) && $it['rbac'] === 'admin' && $_uPerf !== 'admin') {
                                continue;
                            }
                            $visibleItems[] = $it;
                        }

                        // Se não há itens visíveis para o perfil, não renderiza o grupo
                        if (empty($visibleItems)) continue;
                    ?>
                        <!-- Grupo Acordeão (SalesOps v0 Accordion) -->
                        <li class="so-nav__item <?= $group['active'] ? 'is-open' : '' ?>" id="<?= $group['id'] ?>">
                            <button type="button" 
                                    class="so-link <?= $group['active'] ? 'is-active' : '' ?>" 
                                    data-accordion-toggle
                                    title="<?= $group['title'] ?>"
                                    data-tooltip="<?= $group['title'] ?>"
                                    aria-expanded="<?= $group['active'] ? 'true' : 'false' ?>">
                                <i class="<?= $group['icon'] ?>"></i>
                                <span class="so-label"><?= $group['title'] ?></span>
                                <i class="fa-solid fa-chevron-down so-caret"></i>
                            </button>
                            <ul class="so-submenu">
                                <?php foreach ($visibleItems as $item): ?>
                                <li>
                                    <a href="<?= $item['href'] ?>" 
                                       class="so-link <?= $_ap === $item['id'] ? 'is-active' : '' ?>"
                                       title="<?= $item['label'] ?>"
                                       data-tooltip="<?= $item['label'] ?>">
                                        <i class="<?= $item['icon'] ?>"></i>
                                        <span class="so-label"><?= $item['label'] ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Botão Collapse no Rodapé -->
        <div class="so-collapse-wrap">
            <button type="button" 
                    class="so-collapse-btn" 
                    id="soCollapseBtn" 
                    onclick="toggleSidebarCollapse()" 
                    aria-label="Recolher ou expandir menu lateral"
                    title="Recolher / Expandir Menu Lateral">
                <i class="fa-solid fa-chevron-left so-collapse-icon"></i>
                <span class="so-label">Recolher Menu</span>
            </button>
        </div>
    </aside>

    <!-- ===== MAIN PANEL ===== -->
    <div class="main-panel">
        <!-- TOPBAR SUPERIOR (SALESOPS v0) -->
        <header class="so-header">
            <div class="d-flex align-items-center gap-3">
                <button class="d-md-none border-0 bg-transparent text-secondary p-0 fs-5" onclick="toggleSidebar()" aria-label="Alternar menu lateral" title="Abrir Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($_pageTitleClean, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <!-- USER PROFILE POPOVER DROPDOWN -->
            <div class="so-profile" id="soProfile">
                <button class="so-avatar-btn" type="button" id="soAvatarBtn" onclick="toggleProfileDropdown()" aria-label="Menu de perfil do operador">
                    <div class="so-avatar <?= $_uPerf === 'admin' ? 'so-avatar--admin' : 'so-avatar--caixa' ?>">
                        <?= $_uInitials ?>
                    </div>
                    <div class="d-none d-md-flex flex-column text-start" style="line-height:1.2;">
                        <span class="fw-semibold text-dark" style="font-size:0.875rem;"><?= htmlspecialchars($_uName, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-muted" style="font-size:0.6875rem;"><?= htmlspecialchars($_uLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-muted ms-1" style="font-size:0.65rem;"></i>
                </button>

                <div class="so-dropdown" id="soDropdown">
                    <div class="px-2 py-1 mb-1">
                        <div class="fw-bold text-white"><?= htmlspecialchars($_uName, ENT_QUOTES, 'UTF-8') ?></div>
                        <span class="so-role-badge">
                            <i class="fa-solid fa-shield-halved me-1"></i> <?= htmlspecialchars($_uLabel, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <hr class="so-dropdown__divider">
                    <a href="<?= BASE_URL ?>/configuracoes.php" class="so-dropdown__link">
                        <i class="fa-solid fa-gear text-muted" style="width:18px;"></i> Configurações
                    </a>
                    <a href="<?= BASE_URL ?>/ajuda.php" class="so-dropdown__link">
                        <i class="fa-solid fa-circle-question text-muted" style="width:18px;"></i> Ajuda & FAQ
                    </a>
                    <hr class="so-dropdown__divider">
                    <a href="<?= BASE_URL ?>/logout.php" class="so-dropdown__link is-danger">
                        <i class="fa-solid fa-arrow-right-from-bracket" style="width:18px;"></i> Encerrar Sessão
                    </a>
                </div>
            </div>
        </header>

        <!-- MENSAGENS FLASH GLOBAIS (RBAC E NOTIFICAÇÕES) -->
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 shadow-sm border-0" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3 shadow-sm border-0" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <main class="main-content flex-grow-1" role="main" id="mainContent">
