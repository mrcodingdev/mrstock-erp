<?php
/**
 * MrStock ERP - Termos de Uso & Regulamento Operacional
 * Regras de utilização, responsabilidades de perfis (RBAC), simulação acadêmica de NFC-e
 * e propriedade intelectual.
 * 
 * Arquitetura Híbrida: Suporta renderização integrada ao ERP (usuário logado)
 * e visualização institucional limpa (usuário visitante/deslogado).
 */
require_once __DIR__ . '/config.php';

$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $pageTitle  = 'Termos de Uso & Regulamento do Sistema';
    $activePage = '';
    require_once __DIR__ . '/inc/header.php';
}
?>
<?php if (!$isLoggedIn): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Termos de Uso e Regulamento Operacional do MrStock ERP - Papelaria Real.">
    <title>MrStock ERP - Termos de Uso &amp; Regulamento do Sistema</title>
    
    <!-- Metadados OpenGraph & Tema Institucional -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="MrStock ERP - Termos de Uso &amp; Regulamento do Sistema">
    <meta property="og:description" content="Regulamento operacional e termos de utilização do MrStock ERP para a Papelaria Real.">
    <meta property="og:url" content="https://mrstock.com.br/termos.php">
    <meta property="og:image" content="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="MrStock ERP">
    <meta name="theme-color" content="#284936">

    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/inter.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" type="image/x-icon">

    <style>
        :root {
            --brand-primary: #1a4231;
            --brand-secondary: #284936;
            --brand-accent: #6ae49b;
            --bg-color: #f1f5f9;
            --text-dark: #1e293b;
            --text-muted: #475569;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .public-topbar {
            background: #222d31;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem 1.5rem;
        }
        .public-footer {
            background: #222d31;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: #94a3b8;
            padding: 1.5rem;
            margin-top: auto;
            font-size: 0.8125rem;
        }
    </style>
</head>
<body>
    <header class="public-topbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="<?= BASE_URL ?>/login.php" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" alt="MrStock ERP" width="28" height="28" style="object-fit: contain;">
                <span class="text-white fw-bold fs-5">MrStock <small class="text-white-50 fs-6">ERP</small></span>
            </a>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-success" style="background-color: #284936; border-color: #284936; color: #ffffff; font-weight: 600;" aria-label="Voltar para a página de login">
                <i class="fas fa-arrow-left me-2"></i>Voltar ao Login
            </a>
        </div>
    </header>
<?php endif; ?>

<div class="<?= $isLoggedIn ? 'content-body' : 'container py-5 flex-grow-1' ?>" style="max-width: 1050px; margin: 0 auto; animation: mrStockSlideInLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;">
    
    <!-- Cabeçalho Institucional do Documento -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-4" style="background: linear-gradient(135deg, #1a4231, #284936); color: #ffffff;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge" style="background: rgba(106, 228, 155, 0.2); color: #6ae49b; border: 1px solid rgba(106, 228, 155, 0.3);">Regulamento Operacional</span>
                    <span class="badge" style="background: rgba(255, 255, 255, 0.15); color: #ffffff;">RBAC Enterprise</span>
                    <span class="badge" style="background: rgba(255, 255, 255, 0.15); color: #ffffff;">Versão 2.2.0</span>
                </div>
                <h1 class="h2 fw-bold mb-1 text-white">Termos de Uso &amp; Regulamento do Sistema</h1>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">Contrato operacional de utilização da plataforma MrStock ERP na Papelaria Real.</p>
            </div>
            <div class="text-md-end text-start">
                <small class="text-white-50 d-block">Última atualização:</small>
                <strong class="text-white">07 de Setembro de 2026</strong>
            </div>
        </div>
    </div>

    <!-- Conteúdo dos Termos -->
    <div class="card border shadow-sm rounded-3 p-4 mb-4" style="background: #ffffff; border-color: #cbd5e1 !important;">
        
        <!-- 1. Objeto do Sistema -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-boxes-stacked text-success me-2" style="color: #284936 !important;"></i>1. Objeto da Plataforma e Escopo Operacional
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O <strong>MrStock ERP</strong> é um software de gestão empresarial integrado (ERP) desenvolvido especificamente para a gestão comercial, faturamento de PDV ágil e controle rigoroso de estoque físico baseado no método PEPS/FIFO (Primeiro que Entra, Primeiro que Sai) com rastreabilidade de lotes e datas de validade para a <strong>Papelaria Real Ltda</strong>.
            </p>
            <p style="color: #334155; line-height: 1.7;">
                O acesso e a utilização dos módulos operacionais (PDV, Estoque, Ordens de Compra, Fornecedores, Clientes e Inteligência Gerencial) ficam condicionados à estrita aceitação e observância destes Termos de Uso.
            </p>
        </section>

        <!-- 2. Perfis de Acesso e Responsabilidades -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-users-gear text-success me-2" style="color: #284936 !important;"></i>2. Perfis de Acesso e Responsabilidades (RBAC)
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                A segurança do sistema é estruturada sob Controle de Acesso Baseado em Papéis (RBAC - <em>Role-Based Access Control</em>). As permissões operacionais são delimitadas em duas categorias principais:
            </p>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <h3 class="h6 fw-bold mb-2" style="color: #1e293b;"><i class="fas fa-user-shield me-2 text-danger"></i>Perfil Administrador:</h3>
                        <p class="mb-0 small" style="color: #475569; line-height: 1.6;">
                            Acesso irrestrito a todos os módulos, parametrizações tributárias, controle de margens financeiras, centro de análise gerencial (DRE, Curva ABC), auditoria forense de logs e gestão de contas de operadores.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <h3 class="h6 fw-bold mb-2" style="color: #1e293b;"><i class="fas fa-cash-register me-2 text-success" style="color: #284936 !important;"></i>Perfil Operador de Caixa:</h3>
                        <p class="mb-0 small" style="color: #475569; line-height: 1.6;">
                            Acesso delimitado à frente de caixa (PDV), registro de itens via leitor óptico, conferência de preços, emissão de comprovantes de venda e movimentações de vendas sob seu turno de operação.
                        </p>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning border mt-3 p-3 rounded-3" style="background: #fffbeb; border-color: #fef3c7 !important; color: #92400e;">
                <i class="fas fa-triangle-exclamation me-2"></i><strong>Dever de Sigilo e Confidencialidade:</strong> É expressamente proibido o compartilhamento de credenciais de acesso individuais entre colaboradores. Toda e qualquer ação realizada na plataforma é registrada com carimbo de data/hora e IP na base de dados, respondendo o operador titular pelas operações realizadas sob sua autenticação.
            </div>
        </section>

        <!-- 3. Simulação Acadêmica da NFC-e -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-receipt text-success me-2" style="color: #284936 !important;"></i>3. Cláusula de Simulação Didático-Acadêmica da NFC-e
            </h2>
            <div class="p-4" style="border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc;">
                <p class="mb-2" style="color: #1e293b; font-weight: 600;">
                    Declaração de Validação Técnica e Limite Tributário:
                </p>
                <p class="mb-0" style="color: #334155; line-height: 1.7;">
                    Fica expressamente cientificado aos usuários, auditores e partes interessadas que o módulo de <strong>Nota Fiscal de Consumidor Eletrônica (NFC-e)</strong> implementado no MrStock ERP possui <strong>finalidade estritamente didática e de validação acadêmica</strong> no âmbito do Trabalho de Conclusão de Curso (TCC) da ETEC Fernando Prestes. Os cupons fiscais emitidos geram uma chave de acesso estruturada de 44 dígitos com validação por Módulo 11, DANFE térmico de 80mm e QR Code homologado pelo orientador Prof. Vinicius. <strong>Não há transmissão real de dados tributários para os webservices da Secretaria da Fazenda (SEFAZ/SP)</strong> em ambiente de produção fiscal de arrecadação.
                </p>
            </div>
        </section>

        <!-- 4. Propriedade Intelectual -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-copyright text-success me-2" style="color: #284936 !important;"></i>4. Propriedade Intelectual e Direitos Autorais
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O código-fonte integral, modelos de dados relacionais, rotinas de automação, documentação técnica, design system de interfaces e marcas associadas ao <strong>MrStock ERP</strong> constituem propriedade intelectual exclusiva dos membros da <strong>Equipe Mr. Coding</strong> (Douglas, Nikolas, Cesar, Enzo, Sugahara) e da <strong>Papelaria Real Ltda</strong>, protegidos pela Lei de Software (Lei Federal nº 9.609/1998) e Lei de Direitos Autorais (Lei Federal nº 9.610/1998).
            </p>
            <p style="color: #334155; line-height: 1.7;">
                É terminantemente vedada a reprodução não autorizada, engenharia reversa, redistribuição, descompilação ou comercialização parcial ou total da solução sem expressa autorização escrita dos detentores da titularidade.
            </p>
        </section>

        <!-- 5. Disponibilidade e Contingência -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-server text-success me-2" style="color: #284936 !important;"></i>5. Disponibilidade, Manutenção e Contingência Operacional
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                A plataforma opera em ambiente de nuvem de alta disponibilidade sob o domínio <code>mrstock.com.br</code> com hospedagem na infraestrutura Hostinger.
            </p>
            <p style="color: #334155; line-height: 1.7;">
                Para assegurar que a Papelaria Real nunca interrompa suas vendas físicas no balcão por eventual oscilação na conexão com a internet, o sistema conta com <strong>Procedimento de Contingência Operacional Local</strong> homologado via XAMPP (Apache/MariaDB local), assegurando continuidade imediata dos processos de frente de caixa.
            </p>
        </section>

        <!-- 6. Legislação e Foro -->
        <section class="mb-4">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-gavel text-success me-2" style="color: #284936 !important;"></i>6. Legislação Aplicável e Foro de Eleição
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Estes Termos de Uso são regidos e interpretados de acordo com a legislação da República Federativa do Brasil, em especial o Código Civil Brasileiro, o Marco Civil da Internet e a Lei Geral de Proteção de Dados Pessoais.
            </p>
            <p style="color: #334155; line-height: 1.7;">
                Fica eleito o <strong>Foro da Comarca de Sorocaba, Estado de São Paulo</strong>, para dirimir eventuais litígios ou controvérsias oriundas da utilização do MrStock ERP, com expressa renúncia a qualquer outro, por mais privilegiado que seja ou venha a ser.
            </p>
        </section>

    </div>

    <!-- Navegação de Retorno -->
    <div class="d-flex justify-content-between align-items-center mt-3 mb-5">
        <a href="<?= $isLoggedIn ? BASE_URL . '/dashboard.php' : BASE_URL . '/login.php' ?>" class="btn btn-success" style="background-color: #284936; border-color: #284936; color: #ffffff; padding: 10px 24px; font-weight: 600; border-radius: 8px;">
            <i class="fas fa-arrow-left me-2"></i><?= $isLoggedIn ? 'Retornar ao Dashboard' : 'Voltar ao Login' ?>
        </a>
        <a href="<?= BASE_URL ?>/privacidade.php" class="text-decoration-none fw-semibold" style="color: #284936;">
            Consultar Política de Privacidade &amp; LGPD <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>

</div>

<?php
if ($isLoggedIn) {
    require_once __DIR__ . '/inc/footer.php';
} else {
?>
    <footer class="public-footer">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span>MrStock ERP © 2026 Papelaria Real Ltda • Sistema de Gestão Comercial</span>
            </div>
            <div class="d-flex gap-3">
                <a href="<?= BASE_URL ?>/termos.php" class="text-decoration-none text-white fw-semibold">Termos de Uso</a>
                <span class="text-muted">•</span>
                <a href="<?= BASE_URL ?>/privacidade.php" class="text-decoration-none" style="color: #cbd5e1;">Privacidade &amp; LGPD</a>
            </div>
            <div>
                <span>(11) 98765-4321 • contato@mrstock.com.br</span>
            </div>
        </div>
    </footer>
    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/inc/cookie_banner.php'; ?>
</body>
</html>
<?php } ?>
