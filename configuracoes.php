<?php
/**
 * MrStock ERP — Central de Configurações & Preferências do Sistema
 * Versão 2.0 (SalesOps v0 + Benchmark Corporativo de ERP)
 */
$pageTitle  = 'Configurações';
$activePage = 'configuracoes';

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$userId   = (int)($_SESSION['user_id'] ?? 1);
$userRole = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? 'caixa';
$userName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuário';
$isAdmin  = is_admin();

// ── ROTINA DE BACKUP SQL EM 1-CLIQUE (EXCLUSIVO ADMIN) ────────────────────────
if (isset($_GET['acao']) && $_GET['acao'] === 'exportar_backup_sql') {
    if (!$isAdmin) {
        header('Location: ' . BASE_URL . '/configuracoes.php?tab=sistema&erro=acesso_negado');
        exit;
    }

    $filename = 'mrstock_backup_' . date('Ymd_His') . '.sql';
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "-- ============================================================================\n";
    echo "-- MrStock ERP — Backup Oficial do Banco de Dados\n";
    echo "-- Data de Geração: " . date('d/m/Y H:i:s') . "\n";
    echo "-- Gerado por: " . $userName . " (" . $userRole . ")\n";
    echo "-- ============================================================================\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\nSET NAMES utf8mb4;\n\n";

    $tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tabelas as $tab) {
        // Estrutura da Tabela
        $stmtCreate = $pdo->query("SHOW CREATE TABLE `{$tab}`");
        $rowCreate = $stmtCreate->fetch(PDO::FETCH_NUM);
        echo "DROP TABLE IF EXISTS `{$tab}`;\n";
        echo $rowCreate[1] . ";\n\n";

        // Dados da Tabela
        $stmtData = $pdo->query("SELECT * FROM `{$tab}`");
        $rowsData = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rowsData)) {
            echo "INSERT INTO `{$tab}` VALUES\n";
            $valRows = [];
            foreach ($rowsData as $r) {
                $escapedVals = [];
                foreach ($r as $val) {
                    if ($val === null) {
                        $escapedVals[] = 'NULL';
                    } else {
                        $escapedVals[] = $pdo->quote($val);
                    }
                }
                $valRows[] = "(" . implode(', ', $escapedVals) . ")";
            }
            echo implode(",\n", $valRows) . ";\n\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit;
}

// Processamento de Formulários
$msgFeedback  = '';
$tipoFeedback = 'success';
$activeTab    = $_GET['tab'] ?? 'perfil';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verify();
    $acao = $_POST['acao'] ?? '';

    // ── 1. Salvar Perfil ──────────────────────────────────────────────────
    if ($acao === 'salvar_perfil') {
        $activeTab = 'perfil';
        $novoNome  = trim($_POST['nome_exibicao'] ?? '');
        if (!empty($novoNome)) {
            $_SESSION['user_name'] = $novoNome;
            $msgFeedback = 'Nome de exibição atualizado para a sua sessão!';
            $tipoFeedback = 'success';
        }
    }

    // ── 2. Salvar Segurança / Troca de Senha ──────────────────────────────
    elseif ($acao === 'salvar_seguranca') {
        $activeTab = 'seguranca';
        $senhaAtual    = $_POST['senha_atual'] ?? '';
        $novaSenha     = $_POST['nova_senha'] ?? '';
        $confirmaSenha = $_POST['confirma_senha'] ?? '';

        if (empty($senhaAtual)) {
            $msgFeedback = 'Informe a sua senha atual para autorizar a alteração.';
            $tipoFeedback = 'danger';
        } elseif (empty($novaSenha) || strlen($novaSenha) < 4) {
            $msgFeedback = 'A nova senha deve possuir no mínimo 4 caracteres.';
            $tipoFeedback = 'danger';
        } elseif ($novaSenha !== $confirmaSenha) {
            $msgFeedback = 'A confirmação de senha não confere com a nova senha digitada.';
            $tipoFeedback = 'danger';
        } else {
            $stmtUser = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmtUser->execute([$userId]);
            $hashBanco = $stmtUser->fetchColumn();

            $senhaAtualCorreta = false;
            if ($hashBanco) {
                if (password_verify($senhaAtual, $hashBanco) || $hashBanco === $senhaAtual) {
                    $senhaAtualCorreta = true;
                }
            }

            if (!$senhaAtualCorreta) {
                $msgFeedback = 'A senha atual informada está incorreta.';
                $tipoFeedback = 'danger';
            } else {
                $novoHash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmtUpd = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmtUpd->execute([$novoHash, $userId]);

                $msgFeedback = 'Sua senha foi alterada com sucesso! A nova chave já está em vigor.';
                $tipoFeedback = 'success';
            }
        }
    }

    // ── 3. Salvar Dados da Loja e Parâmetros Fiscais (Admin) ──────────────
    elseif ($acao === 'salvar_loja') {
        $activeTab = 'loja';
        if (!$isAdmin) {
            $msgFeedback = 'Apenas administradores possuem permissão para alterar os dados da empresa.';
            $tipoFeedback = 'danger';
        } else {
            $empNome      = trim($_POST['empresa_nome'] ?? '');
            $empRazao     = trim($_POST['empresa_razao'] ?? '');
            $empCnpj      = trim($_POST['empresa_cnpj'] ?? '');
            $empIe        = trim($_POST['empresa_ie'] ?? '');
            $empTelefone  = trim($_POST['empresa_telefone'] ?? '');
            $empWhatsapp  = trim($_POST['empresa_whatsapp'] ?? '');
            $empCep       = trim($_POST['empresa_cep'] ?? '');
            $empEndereco  = trim($_POST['empresa_endereco'] ?? '');
            $empCidade    = trim($_POST['empresa_cidade'] ?? '');
            $empRegime    = trim($_POST['empresa_regime'] ?? 'Simples Nacional (ME)');

            set_app_config($pdo, 'empresa_nome', $empNome);
            set_app_config($pdo, 'empresa_razao', $empRazao);
            set_app_config($pdo, 'empresa_cnpj', $empCnpj);
            set_app_config($pdo, 'empresa_ie', $empIe);
            set_app_config($pdo, 'empresa_telefone', $empTelefone);
            set_app_config($pdo, 'empresa_whatsapp', $empWhatsapp);
            set_app_config($pdo, 'empresa_cep', $empCep);
            set_app_config($pdo, 'empresa_endereco', $empEndereco);
            set_app_config($pdo, 'empresa_cidade', $empCidade);
            set_app_config($pdo, 'empresa_regime', $empRegime);

            $msgFeedback = 'Dados cadastrais e parâmetros da loja atualizados com sucesso!';
            $tipoFeedback = 'success';
        }
    }

    // ── 4. Salvar Parâmetros do PDV & Automação Comercial ─────────────────
    elseif ($acao === 'salvar_pdv') {
        $activeTab = 'pdv';
        if (!$isAdmin) {
            $msgFeedback = 'Apenas administradores podem configurar parâmetros de automação comercial.';
            $tipoFeedback = 'danger';
        } else {
            $pdvImp         = trim($_POST['pdv_impressora'] ?? '80mm');
            $pdvSom         = isset($_POST['som_pdv']) ? '1' : '0';
            $pdvDescMax     = (float)($_POST['pdv_desconto_maximo'] ?? 15.0);
            $pdvTravaMargem = trim($_POST['pdv_trava_margem'] ?? 'aviso');

            set_app_config($pdo, 'pdv_impressora', $pdvImp);
            set_app_config($pdo, 'som_pdv', $pdvSom);
            set_app_config($pdo, 'pdv_desconto_maximo', (string)$pdvDescMax);
            set_app_config($pdo, 'pdv_trava_margem', $pdvTravaMargem);

            $msgFeedback = 'Parâmetros operacionais do PDV salvos com sucesso!';
            $tipoFeedback = 'success';
        }
    }

    // ── 5. Salvar Gestão de Estoque & Suprimentos ─────────────────────────
    elseif ($acao === 'salvar_estoque') {
        $activeTab = 'estoque';
        if (!$isAdmin) {
            $msgFeedback = 'Apenas administradores podem configurar parâmetros de estoque.';
            $tipoFeedback = 'danger';
        } else {
            $estMinGlobal   = (int)($_POST['estoque_minimo_global'] ?? 5);
            $alertaVencDias = (int)($_POST['alerta_vencimento_dias'] ?? 30);
            $travaNegativo  = trim($_POST['estoque_trava_negativo'] ?? 'bloquear');

            set_app_config($pdo, 'estoque_minimo_global', (string)$estMinGlobal);
            set_app_config($pdo, 'alerta_vencimento_dias', (string)$alertaVencDias);
            set_app_config($pdo, 'estoque_trava_negativo', $travaNegativo);

            $msgFeedback = 'Diretrizes de estoque e suprimentos atualizadas com sucesso!';
            $tipoFeedback = 'success';
        }
    }

    // ── 6. Salvar Aparência & Interface ───────────────────────────────────
    elseif ($acao === 'salvar_aparencia') {
        $activeTab = 'aparencia';
        $densidadeTabela = trim($_POST['densidade_tabela'] ?? 'padrao');
        $tamanhoFonte    = trim($_POST['tamanho_fonte'] ?? 'normal');
        $linhasZebradas  = isset($_POST['linhas_zebradas']) ? '1' : '0';

        set_app_config($pdo, 'densidade_tabela', $densidadeTabela);
        set_app_config($pdo, 'tamanho_fonte', $tamanhoFonte);
        set_app_config($pdo, 'linhas_zebradas', $linhasZebradas);

        $_SESSION['cfg_densidade_tabela'] = $densidadeTabela;
        $_SESSION['cfg_tamanho_fonte']    = $tamanhoFonte;
        $_SESSION['cfg_linhas_zebradas']  = $linhasZebradas;

        $msgFeedback = 'Preferências visuais da interface salvas com sucesso!';
        $tipoFeedback = 'success';
    }

    // ── 7. Limpar Cache da Aplicação ──────────────────────────────────────
    elseif ($acao === 'limpar_cache') {
        $activeTab = 'sistema';
        unset($_SESSION['cfg_densidade_tabela'], $_SESSION['cfg_tamanho_fonte'], $_SESSION['cfg_linhas_zebradas']);
        $msgFeedback = 'Cache de configurações e sessões temporárias limpo com êxito!';
        $tipoFeedback = 'success';
    }
}

// Carrega dados da sessão e loja
$stmtU = $pdo->prepare("SELECT id, username, perfil FROM usuarios WHERE id = ?");
$stmtU->execute([$userId]);
$dadosUsuario = $stmtU->fetch() ?: [
    'id' => $userId,
    'username' => $userName,
    'perfil' => $userRole
];

// Carregamento de Configurações
$cfgNomeLoja        = get_app_config($pdo, 'empresa_nome', 'Papelaria Real');
$cfgRazaoLoja       = get_app_config($pdo, 'empresa_razao', 'Papelaria Real Sorocaba Ltda - ME');
$cfgCnpjLoja        = get_app_config($pdo, 'empresa_cnpj', '50.334.808/0001-38');
$cfgIeLoja          = get_app_config($pdo, 'empresa_ie', '688.123.456.789');
$cfgTelLoja         = get_app_config($pdo, 'empresa_telefone', '(15) 3232-0000');
$cfgZapLoja         = get_app_config($pdo, 'empresa_whatsapp', '(15) 99123-4567');
$cfgCepLoja         = get_app_config($pdo, 'empresa_cep', '18010-082');
$cfgEndLoja         = get_app_config($pdo, 'empresa_endereco', 'Rua XV de Novembro, 250 - Centro');
$cfgCidadeLoja      = get_app_config($pdo, 'empresa_cidade', 'Sorocaba/SP');
$cfgRegimeLoja      = get_app_config($pdo, 'empresa_regime', 'Simples Nacional (ME)');

$cfgPdvImp          = get_app_config($pdo, 'pdv_impressora', '80mm');
$cfgSomPdv          = get_app_config($pdo, 'som_pdv', '1') === '1';
$cfgPdvDescMax      = get_app_config($pdo, 'pdv_desconto_maximo', '15.0');
$cfgPdvTravaMargem  = get_app_config($pdo, 'pdv_trava_margem', 'aviso');

$cfgEstMin          = get_app_config($pdo, 'estoque_minimo_global', '5');
$cfgAlertaVencDias  = get_app_config($pdo, 'alerta_vencimento_dias', '30');
$cfgTravaNegativo   = get_app_config($pdo, 'estoque_trava_negativo', 'bloquear');

$cfgDensidade       = get_app_config($pdo, 'densidade_tabela', 'padrao');
$cfgFonte           = get_app_config($pdo, 'tamanho_fonte', 'normal');
$cfgZebrada         = get_app_config($pdo, 'linhas_zebradas', '1') === '1';

// Diagnósticos de Ambiente
$phpVersion = phpversion();
$mysqlVersion = $pdo->query('select version()')->fetchColumn();
$curlAtivo = function_exists('curl_version');
$gdAtivo = function_exists('gd_info');

require_once __DIR__ . '/inc/header.php';
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-gear text-primary me-2"></i>Configurações do Sistema</h2>
        <p class="text-muted m-0">Gerencie preferências da sua conta, parâmetros fiscais da loja, regras de PDV e diagnósticos.</p>
    </div>
</div>

<div class="content-body">
    <?php if (!empty($msgFeedback)): ?>
        <div class="alert alert-<?= $tipoFeedback ?> alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
            <i class="fas fa-<?= $tipoFeedback === 'success' ? 'check-circle' : 'triangle-exclamation' ?> me-2"></i>
            <?= htmlspecialchars($msgFeedback) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ══ BARRA DE ABAS FULL-WIDTH (ESTILO SEGMENTED TABS) ══════════════════ -->
    <div class="so-card p-0 mb-4 overflow-hidden border">
        <div class="settings-tabs-wrapper">
            <ul class="nav settings-nav-tabs" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'perfil' ? 'active' : '' ?>" 
                            id="tab-perfil-btn" data-bs-toggle="pill" data-bs-target="#tab-perfil" type="button" role="tab">
                        <i class="fas fa-user-circle me-1"></i> Perfil
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'seguranca' ? 'active' : '' ?>" 
                            id="tab-seguranca-btn" data-bs-toggle="pill" data-bs-target="#tab-seguranca" type="button" role="tab">
                        <i class="fas fa-shield-halved me-1"></i> Segurança
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'loja' ? 'active' : '' ?>" 
                            id="tab-loja-btn" data-bs-toggle="pill" data-bs-target="#tab-loja" type="button" role="tab">
                        <i class="fas fa-store me-1"></i> Dados da Loja
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'pdv' ? 'active' : '' ?>" 
                            id="tab-pdv-btn" data-bs-toggle="pill" data-bs-target="#tab-pdv" type="button" role="tab">
                        <i class="fas fa-cash-register me-1"></i> PDV & Caixa
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'estoque' ? 'active' : '' ?>" 
                            id="tab-estoque-btn" data-bs-toggle="pill" data-bs-target="#tab-estoque" type="button" role="tab">
                        <i class="fas fa-boxes-stacked me-1"></i> Estoque & Alertas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'sistema' ? 'active' : '' ?>" 
                            id="tab-sistema-btn" data-bs-toggle="pill" data-bs-target="#tab-sistema" type="button" role="tab">
                        <i class="fas fa-database me-1"></i> Backup & Diagnóstico
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'aparencia' ? 'active' : '' ?>" 
                            id="tab-aparencia-btn" data-bs-toggle="pill" data-bs-target="#tab-aparencia" type="button" role="tab">
                        <i class="fas fa-palette me-1"></i> Aparência
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- ══ CONTEÚDO DAS ABAS ═════════════════════════════════════════════════ -->
    <div class="tab-content" id="configTabsContent">

        <!-- ── ABA 1: PERFIL DO OPERADOR ───────────────────────────────────── -->
        <div class="tab-pane fade <?= $activeTab === 'perfil' ? 'show active' : '' ?>" id="tab-perfil" role="tabpanel">
            <div class="so-card">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-id-card text-primary me-2"></i>Perfil do Operador Atual</h5>
                </div>
                <div class="so-card-body p-4">
                    <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom flex-wrap">
                        <div class="so-profile-avatar-lg">
                            <?= strtoupper(substr($dadosUsuario['username'], 0, 2)) ?>
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark m-0"><?= htmlspecialchars($dadosUsuario['username']) ?></h4>
                            <span class="badge <?= $dadosUsuario['perfil'] === 'admin' ? 'bg-danger text-white' : 'bg-primary text-white' ?> px-3 py-1 rounded-pill mt-1">
                                <?= $dadosUsuario['perfil'] === 'admin' ? 'Administrador Geral' : 'Operador de Caixa' ?>
                            </span>
                            <p class="text-muted small m-0 mt-1">Sessão ativa autenticada no MrStock ERP.</p>
                        </div>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/configuracoes.php?tab=perfil">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao" value="salvar_perfil">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-secondary small">Nome de Usuário (Login)</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($dadosUsuario['username']) ?>" readonly disabled>
                                <small class="text-muted">O identificador de login é gerenciado pelo Administrador.</small>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-secondary small">Nível de Permissão (RBAC)</label>
                                <input type="text" class="form-control bg-light" value="<?= $dadosUsuario['perfil'] === 'admin' ? 'Acesso Total (Admin)' : 'Restrito (PDV/Caixa)' ?>" readonly disabled>
                                <small class="text-muted">Operadores de caixa não visualizam margem de lucro ou custos.</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary fw-bold shadow-sm" onclick="location.href='<?= BASE_URL ?>/ajuda.php'">
                                <i class="fas fa-circle-question me-1"></i> Ver Guia de Uso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── ABA 2: SEGURANÇA & SENHA ────────────────────────────────────── -->
        <div class="tab-pane fade <?= $activeTab === 'seguranca' ? 'show active' : '' ?>" id="tab-seguranca" role="tabpanel">
            <div class="so-card">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-shield-halved text-primary me-2"></i>Segurança & Troca de Senha</h5>
                </div>
                <div class="so-card-body p-4">
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <i class="fas fa-lock me-2"></i> As senhas no MrStock ERP utilizam criptografia <strong>BCrypt com Salt Dinâmico</strong>, garantindo máxima proteção contra ataques de dicionário ou vazamentos.
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/configuracoes.php?tab=seguranca" style="max-width: 600px;">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao" value="salvar_seguranca">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Senha Atual</label>
                            <input type="password" name="senha_atual" class="form-control" placeholder="Digite sua senha atual" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-secondary small">Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control" placeholder="Mínimo 4 caracteres" required>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-secondary small">Confirmar Nova Senha</label>
                                <input type="password" name="confirma_senha" class="form-control" placeholder="Repita a nova senha" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="fas fa-key me-1"></i> Atualizar Senha
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── ABA 3: DADOS DA LOJA & FISCAL (ADMIN) ───────────────────────── -->
        <div class="tab-pane fade <?= $activeTab === 'loja' ? 'show active' : '' ?>" id="tab-loja" role="tabpanel">
            <div class="so-card">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-store text-primary me-2"></i>Dados Cadastrais & Parâmetros Fiscais da Empresa</h5>
                </div>
                <div class="so-card-body p-4">
                    <?php if (!$isAdmin): ?>
                        <div class="alert alert-warning border-0 shadow-sm">
                            <i class="fas fa-lock me-2"></i> Visualização restrita. Apenas administradores podem editar os dados fiscais da loja.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/configuracoes.php?tab=loja">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao" value="salvar_loja">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-secondary small">Nome Fantasia da Loja</label>
                                <input type="text" name="empresa_nome" class="form-control" value="<?= htmlspecialchars($cfgNomeLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?> required>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-secondary small">Razão Social Oficial</label>
                                <input type="text" name="empresa_razao" class="form-control" value="<?= htmlspecialchars($cfgRazaoLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?> required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-secondary small">CNPJ</label>
                                <input type="text" name="empresa_cnpj" class="form-control" value="<?= htmlspecialchars($cfgCnpjLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?> required>
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-secondary small">Inscrição Estadual (IE)</label>
                                <input type="text" name="empresa_ie" class="form-control" value="<?= htmlspecialchars($cfgIeLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-secondary small">Regime Tributário</label>
                                <input type="text" name="empresa_regime" class="form-control" value="<?= htmlspecialchars($cfgRegimeLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-secondary small">Telefone Fixo</label>
                                <input type="text" name="empresa_telefone" class="form-control" value="<?= htmlspecialchars($cfgTelLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-secondary small">WhatsApp Comercial</label>
                                <input type="text" name="empresa_whatsapp" class="form-control" value="<?= htmlspecialchars($cfgZapLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-secondary small">CEP</label>
                                <input type="text" name="empresa_cep" class="form-control" value="<?= htmlspecialchars($cfgCepLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-8 col-12">
                                <label class="form-label fw-bold text-secondary small">Endereço Completo</label>
                                <input type="text" name="empresa_endereco" class="form-control" value="<?= htmlspecialchars($cfgEndLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?> required>
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-secondary small">Município / UF</label>
                                <input type="text" name="empresa_cidade" class="form-control" value="<?= htmlspecialchars($cfgCidadeLoja) ?>" <?= !$isAdmin ? 'disabled' : '' ?> required>
                            </div>
                        </div>

                        <?php if ($isAdmin): ?>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Salvar Dados da Loja
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── ABA 4: PDV & AUTOMAÇÃO COMERCIAL ────────────────────────────── -->
        <div class="tab-pane fade <?= $activeTab === 'pdv' ? 'show active' : '' ?>" id="tab-pdv" role="tabpanel">
            <div class="so-card">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-cash-register text-primary me-2"></i>Parâmetros da Frente de Caixa & PDV</h5>
                </div>
                <div class="so-card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/configuracoes.php?tab=pdv">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao" value="salvar_pdv">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-print text-primary me-2"></i>Padrão de Impressora Térmica</h6>
                                    <p class="text-muted small mb-3">Define o formato padrão para emissão do cupom fiscal DANFE NFC-e.</p>
                                    <select name="pdv_impressora" class="form-select" <?= !$isAdmin ? 'disabled' : '' ?>>
                                        <option value="80mm" <?= $cfgPdvImp === '80mm' ? 'selected' : '' ?>>Bobina Térmica 80mm (Padrão Varejo)</option>
                                        <option value="58mm" <?= $cfgPdvImp === '58mm' ? 'selected' : '' ?>>Bobina Térmica 58mm (Compacta)</option>
                                        <option value="A4"   <?= $cfgPdvImp === 'A4'   ? 'selected' : '' ?>>Folha Completa A4 (Laser / Jato)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-percent text-success me-2"></i>Limite de Desconto no Caixa</h6>
                                    <p class="text-muted small mb-3">Desconto máximo permitido sem requerer autorização superior.</p>
                                    <div class="input-group">
                                        <input type="number" step="0.5" min="0" max="100" name="pdv_desconto_maximo" class="form-control fw-bold" value="<?= htmlspecialchars($cfgPdvDescMax) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
                                        <span class="input-group-text bg-white fw-bold">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-triangle-exclamation text-danger me-2"></i>Trava de Venda com Prejuízo</h6>
                                    <p class="text-muted small mb-3">Ação quando o preço com desconto for menor que o custo de compra.</p>
                                    <select name="pdv_trava_margem" class="form-select" <?= !$isAdmin ? 'disabled' : '' ?>>
                                        <option value="aviso"    <?= $cfgPdvTravaMargem === 'aviso' ? 'selected' : '' ?>>Exibir Aviso Sonoro e Visual (Permitir Venda)</option>
                                        <option value="bloquear" <?= $cfgPdvTravaMargem === 'bloquear' ? 'selected' : '' ?>>Bloquear Finalização da Venda</option>
                                        <option value="nenhum"   <?= $cfgPdvTravaMargem === 'nenhum' ? 'selected' : '' ?>>Sem Restrições</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-volume-high text-info me-2"></i>Efeitos Sonoros do PDV</h6>
                                    <p class="text-muted small mb-3">Emite bipe sintético ao ler código de barras, adicionar item ou troco.</p>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="som_pdv" id="som_pdv" <?= $cfgSomPdv ? 'checked' : '' ?> <?= !$isAdmin ? 'disabled' : '' ?>>
                                        <label class="form-check-label fw-bold text-secondary" for="som_pdv">Ativar Efeitos Sonoros (Web Audio API)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($isAdmin): ?>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Salvar Parâmetros do PDV
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── ABA 5: GESTÃO DE ESTOQUE & SUPRIMENTOS ───────────────────────── -->
        <div class="tab-pane fade <?= $activeTab === 'estoque' ? 'show active' : '' ?>" id="tab-estoque" role="tabpanel">
            <div class="so-card">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-boxes-stacked text-primary me-2"></i>Diretrizes de Estoque & Controle de Lotes</h5>
                </div>
                <div class="so-card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/configuracoes.php?tab=estoque">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao" value="salvar_estoque">

                        <div class="row g-4 mb-4">
                            <div class="col-md-4 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-cubes text-primary me-2"></i>Estoque Mínimo Padrão</h6>
                                    <p class="text-muted small mb-3">Quantidade mínima de segurança sugerida para novos cadastros.</p>
                                    <div class="input-group">
                                        <input type="number" min="1" name="estoque_minimo_global" class="form-control fw-bold" value="<?= htmlspecialchars($cfgEstMin) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
                                        <span class="input-group-text bg-white">un</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-calendar-xmark text-warning me-2"></i>Alerta de Vencimento</h6>
                                    <p class="text-muted small mb-3">Notificar no Dashboard produtos vencendo nos próximos dias.</p>
                                    <select name="alerta_vencimento_dias" class="form-select" <?= !$isAdmin ? 'disabled' : '' ?>>
                                        <option value="15" <?= (int)$cfgAlertaVencDias === 15 ? 'selected' : '' ?>>Próximos 15 dias</option>
                                        <option value="30" <?= (int)$cfgAlertaVencDias === 30 ? 'selected' : '' ?>>Próximos 30 dias (Recomendado)</option>
                                        <option value="60" <?= (int)$cfgAlertaVencDias === 60 ? 'selected' : '' ?>>Próximos 60 dias</option>
                                        <option value="90" <?= (int)$cfgAlertaVencDias === 90 ? 'selected' : '' ?>>Próximos 90 dias</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-ban text-danger me-2"></i>Saldo Negativo</h6>
                                    <p class="text-muted small mb-3">Comportamento ao tentar vender produto com saldo zerado.</p>
                                    <select name="estoque_trava_negativo" class="form-select" <?= !$isAdmin ? 'disabled' : '' ?>>
                                        <option value="bloquear" <?= $cfgTravaNegativo === 'bloquear' ? 'selected' : '' ?>>Bloquear Venda (Estrito)</option>
                                        <option value="permitir" <?= $cfgTravaNegativo === 'permitir' ? 'selected' : '' ?>>Permitir com Registro de Ajuste</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <?php if ($isAdmin): ?>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Salvar Diretrizes de Estoque
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── ABA 6: BACKUP & DIAGNÓSTICO DO SISTEMA (ADMIN) ──────────────── -->
        <div class="tab-pane fade <?= $activeTab === 'sistema' ? 'show active' : '' ?>" id="tab-sistema" role="tabpanel">
            <div class="so-card mb-4">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-database text-primary me-2"></i>Backup do Banco de Dados & Contingência</h5>
                </div>
                <div class="so-card-body p-4">
                    <?php if (!$isAdmin): ?>
                        <div class="alert alert-warning border-0 shadow-sm">
                            <i class="fas fa-lock me-2"></i> O download de backups e diagnósticos avançados é exclusivo para Administradores.
                        </div>
                    <?php else: ?>
                        <div class="row g-4 align-items-center">
                            <div class="col-md-8 col-12">
                                <h6 class="fw-bold text-dark mb-1">Exportar Dump SQL Completo do MrStock ERP</h6>
                                <p class="text-muted small mb-0">
                                    Gera um arquivo SQL íntegro em formato <code>UTF-8</code> contendo a estrutura de todas as 13 tabelas e todos os registros de produtos, vendas, compras e clientes para restauração rápida.
                                </p>
                            </div>
                            <div class="col-md-4 col-12 text-md-end">
                                <a href="<?= BASE_URL ?>/configuracoes.php?acao=exportar_backup_sql" class="btn btn-success fw-bold px-4 py-2 shadow-sm">
                                    <i class="fas fa-download me-2"></i> Baixar Backup SQL
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Diagnóstico de Infraestrutura e PHP -->
            <div class="so-card">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-server text-primary me-2"></i>Diagnóstico de Infraestrutura do Servidor</h5>
                </div>
                <div class="so-card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <span class="text-muted small d-block">Versão do PHP</span>
                                <strong class="text-dark fs-6"><?= $phpVersion ?></strong>
                                <span class="badge bg-success text-white mt-1">Compatível</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <span class="text-muted small d-block">Servidor MySQL</span>
                                <strong class="text-dark fs-6"><?= substr($mysqlVersion, 0, 12) ?></strong>
                                <span class="badge bg-success text-white mt-1">Conectado</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <span class="text-muted small d-block">Extensão cURL (ViaCEP)</span>
                                <strong class="text-dark fs-6"><?= $curlAtivo ? 'Ativa' : 'Inativa' ?></strong>
                                <span class="badge <?= $curlAtivo ? 'bg-success' : 'bg-danger' ?> text-white mt-1"><?= $curlAtivo ? 'Operacional' : 'Offline' ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <span class="text-muted small d-block">Biblioteca GD</span>
                                <strong class="text-dark fs-6"><?= $gdAtivo ? 'Ativa' : 'Inativa' ?></strong>
                                <span class="badge <?= $gdAtivo ? 'bg-success' : 'bg-secondary' ?> text-white mt-1"><?= $gdAtivo ? 'Disponível' : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/configuracoes.php?tab=sistema">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao" value="limpar_cache">
                        <button type="submit" class="btn btn-secondary fw-bold shadow-sm">
                            <i class="fas fa-broom me-1"></i> Limpar Cache de Sessão
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── ABA 7: APARÊNCIA & ERGONOMIA ────────────────────────────────── -->
        <div class="tab-pane fade <?= $activeTab === 'aparencia' ? 'show active' : '' ?>" id="tab-aparencia" role="tabpanel">
            <div class="so-card">
                <div class="so-card-header">
                    <h5 class="so-card-title"><i class="fas fa-palette text-primary me-2"></i>Preferências de Interface & Acessibilidade</h5>
                </div>
                <div class="so-card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/configuracoes.php?tab=aparencia">
                        <?= csrf_input() ?>
                        <input type="hidden" name="acao" value="salvar_aparencia">

                        <div class="row g-4 mb-4">
                            <div class="col-md-4 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-table text-primary me-2"></i>Densidade das Tabelas</h6>
                                    <p class="text-muted small mb-3">Ajusta o espaçamento entre as linhas das listagens.</p>
                                    <select name="densidade_tabela" class="form-select">
                                        <option value="padrao"   <?= $cfgDensidade === 'padrao' ? 'selected' : '' ?>>Padrão Confortável</option>
                                        <option value="compacto" <?= $cfgDensidade === 'compacto' ? 'selected' : '' ?>>Alta Densidade (Compacto)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-text-height text-info me-2"></i>Tamanho da Tipografia</h6>
                                    <p class="text-muted small mb-3">Facilita a leitura em monitores de PDV de diferentes resoluções.</p>
                                    <select name="tamanho_fonte" class="form-select">
                                        <option value="normal"  <?= $cfgFonte === 'normal' ? 'selected' : '' ?>>Normal (Inter 14px)</option>
                                        <option value="grande"  <?= $cfgFonte === 'grande' ? 'selected' : '' ?>>Conforto Visual (Inter 15.5px)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 col-12">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-bars-staggered text-secondary me-2"></i>Linhas Zebradas</h6>
                                    <p class="text-muted small mb-3">Alterna o fundo das linhas para melhorar o escaneamento ocular.</p>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="linhas_zebradas" id="linhas_zebradas" <?= $cfgZebrada ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-secondary" for="linhas_zebradas">Ativar Fundo Alternado</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Salvar Preferências Visuais
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>