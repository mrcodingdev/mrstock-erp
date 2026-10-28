<?php
/**
 * MrStock ERP - Termos de Uso
 * Condições gerais de operação da plataforma MrStock ERP na Papelaria Real.
 * 
 * Arquitetura Híbrida: Suporta renderização integrada ao ERP (usuário logado)
 * e visualização institucional limpa (usuário visitante/deslogado).
 */
require_once __DIR__ . '/config.php';

$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $pageTitle  = 'Termos de Uso';
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
    <meta name="description" content="Termos de Uso do MrStock ERP - Papelaria Real.">
    <title>Termos de Uso - MrStock ERP</title>
    
    <!-- Metadados OpenGraph & Tema Institucional -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Termos de Uso - MrStock ERP">
    <meta property="og:description" content="Condições gerais de operação da plataforma MrStock ERP na Papelaria Real.">
    <meta property="og:url" content="https://mrstock.com.br/termos.php">
    <meta property="og:image" content="https://mrstock.com.br/assets/img/mrstock_og.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="MrStock ERP">
    <meta name="theme-color" content="#1a4231">

    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/inter.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.min.css?v=2.2.7">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" type="image/x-icon">

    <style>
        :root {
            --brand-primary: #1a4231;
            --brand-secondary: #14532d;
            --brand-accent: #39e07b;
            --bg-color: #f8fafc;
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
            color: #cbd5e1;
            padding: 1.5rem 0;
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
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-success" style="background-color: #14532d; border-color: #14532d; color: #ffffff; font-weight: 600;" aria-label="Voltar para a página de login">
                <i class="fas fa-arrow-left me-2"></i>Voltar ao Login
            </a>
        </div>
    </header>
<?php endif; ?>

<div class="<?= $isLoggedIn ? 'content-body' : 'container py-5 flex-grow-1' ?>" style="max-width: 1050px; margin: 0 auto; animation: mrStockSlideInLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;">
    
    <!-- Cabeçalho Institucional do Documento -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-4" style="background: #1a4231; color: #ffffff;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="h2 fw-bold mb-1 text-white">Termos de Uso</h1>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">Condições gerais de operação da plataforma MrStock ERP na Papelaria Real.</p>
            </div>
            <div class="text-md-end text-start">
                <small class="text-white-50 d-block">Última atualização</small>
                <strong class="text-white">24 de Outubro de 2026</strong>
            </div>
        </div>
    </div>

    <!-- Conteúdo dos Termos (7 Seções Claras e Objetivas) -->
    <div class="card border shadow-sm rounded-3 p-4 mb-4" style="background: #ffffff; border-color: #cbd5e1 !important;">
        
        <!-- 1. Visão Geral e Aceite dos Termos -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                1. Visão Geral e Aceite dos Termos
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O presente documento regula os termos e condições gerais para acesso e utilização da plataforma <strong>MrStock ERP</strong>, desenvolvida para atender às demandas de <strong>gestão de estoque</strong>, <strong>controle financeiro</strong> e <strong>operação de frente de caixa</strong> da <strong>Papelaria Real Ltda - ME</strong> (CNPJ: 50.334.808/0001-38), sediada na Rua XV de Novembro, 250 - Centro, Sorocaba/SP.
            </p>
            <p style="color: #334155; line-height: 1.7; margin-bottom: 0;">
                O acesso às dependências lógicas do sistema é estritamente corporativo e concedido a colaboradores <strong>previamente cadastrados e autorizados</strong>. A autenticação na plataforma implica em <strong>concordância plena e irrestrita</strong> com todas as cláusulas e diretrizes aqui expostas.
            </p>
        </section>

        <!-- 2. Perfis de Acesso e Contas de Usuário -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                2. Perfis de Acesso e Contas de Usuário (RBAC)
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O MrStock ERP adota arquitetura de <strong>Controle de Acesso Baseado em Papéis (RBAC)</strong> (<em>Role-Based Access Control</em>), assegurando o princípio do menor privilégio e delimitando as funções operacionais em dois perfis fundamentais:
            </p>
            <div class="row g-3 mt-1 mb-3">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100 d-flex flex-column" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                        <h3 class="h6 fw-bold mb-2" style="color: #1e293b;"><i class="fas fa-user-shield me-2 text-danger"></i>Perfil Administrador:</h3>
                        <p class="mb-0 small" style="color: #475569; line-height: 1.6;">
                            <strong>Acesso irrestrito</strong> a todos os módulos do ERP, incluindo <strong>parametrização de configurações</strong> da loja, <strong>controle de margens e custos</strong>, <strong>relatórios financeiros</strong> (DRE Gerencial, Curva ABC), <strong>auditoria de movimentações</strong> e gestão de contas de operadores.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100 d-flex flex-column" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                        <h3 class="h6 fw-bold mb-2" style="color: #1e293b;"><i class="fas fa-cash-register me-2 text-success" style="color: #14532d !important;"></i>Perfil Operador de Caixa:</h3>
                        <p class="mb-0 small" style="color: #475569; line-height: 1.6;">
                            <strong>Acesso delimitado à frente de caixa (PDV)</strong>, <strong>abertura e fechamento de turno</strong>, <strong>registro ágil de itens</strong>, consulta de catálogo por preço final e emissão de comprovantes de balcão. O operador de caixa <strong>não possui visualização de preços de custo, margens de lucro ou relatórios contábeis</strong>.
                        </p>
                    </div>
                </div>
            </div>
            <p class="small mb-0" style="color: #64748b; line-height: 1.6;">
                <strong>Dever de Confidencialidade:</strong> As credenciais de acesso são <strong>individuais e intransferíveis</strong>. O <strong>compartilhamento de senhas é proibido</strong>, e todas as ações no banco de dados contam com registro de <strong>autoria, data, hora e IP</strong> do operador logado.
            </p>
        </section>

        <!-- 3. Operação e Recursos do Sistema -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                3. Operação e Recursos do Sistema
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O MrStock ERP integra as etapas essenciais da rotina varejista da Papelaria Real, contemplando:
            </p>
            <ul style="color: #334155; line-height: 1.8; margin-bottom: 0;">
                <li><strong>Frente de Caixa (PDV):</strong> Lançamento veloz com foco no <strong>teclado</strong>, consulta instantânea por <strong>leitor de código de barras</strong> ou descrição de produto, controle de <strong>formas de pagamento</strong> (Dinheiro, Cartão de Crédito, Cartão de Débito e Pix) e <strong>cálculo automático de troco</strong>.</li>
                <li><strong>Estoque Físico com Validade PEPS/FIFO:</strong> Rastreabilidade rigorosa de lotes com datas de validade para <strong>materiais sensíveis</strong> (como tintas, colas, corretores e marcadores), emitindo <strong>alertas preventivos para produtos com vencimento em até 30 dias</strong> a fim de evitar desperdícios e perdas de estoque.</li>
                <li><strong>Ordens de Compra e Fornecedores:</strong> Controle de reposição inteligente a partir de parâmetros de <strong>estoque mínimo</strong>, permitindo emissão estruturada de pedidos a fornecedores cadastrados.</li>
                <li><strong>Relatórios e Inteligência Comercial:</strong> Geração de resumos de faturamento, histórico de vendas detalhado e análise de <strong>giro de mercadorias</strong>.</li>
            </ul>
        </section>

        <!-- 4. Emissão de Comprovantes de Venda -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                4. Emissão de Comprovantes de Venda
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O sistema disponibiliza rotinas para impressão de cupons e comprovantes de venda no padrão térmico de <strong>80mm</strong>, contendo detalhamento de itens, quantidades, valores unitários, totais e identificação comercial da Papelaria Real.
            </p>
            <p style="color: #334155; line-height: 1.7; margin-bottom: 0;">
                No <strong>âmbito acadêmico de validação técnica junto à ETEC Fernando Prestes</strong>, o módulo de <strong>simulação de cupom auxiliar (NFC-e)</strong> opera com <strong>geração didática de chave de 44 dígitos e QR Code de conferência</strong>, <strong>não havendo transmissão de lotes fiscais em ambiente de produção da Secretaria da Fazenda (SEFAZ/SP)</strong>.
            </p>
        </section>

        <!-- 5. Disponibilidade e Contingência Operacional -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                5. Disponibilidade e Contingência Operacional
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O ambiente oficial de produção opera sob o domínio <strong><code>mrstock.com.br</code></strong> com hospedagem na infraestrutura em nuvem da <strong>Hostinger</strong>, contando com conexão segura via <strong>HTTPS</strong> e <strong>certificados SSL ativos</strong>.
            </p>
            <p style="color: #334155; line-height: 1.7; margin-bottom: 0;">
                Para assegurar resiliência nas atividades de atendimento físico na loja, a Papelaria Real mantém instância de <strong>contingência operacional local</strong> configurada em ambiente <strong>XAMPP (Apache e MySQL)</strong>, permitindo <strong>continuidade imediata da emissão de vendas de balcão</strong> mesmo em caso de instabilidades na conexão externa de internet.
            </p>
        </section>

        <!-- 6. Propriedade Intelectual e Direitos Autorais -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                6. Propriedade Intelectual e Direitos Autorais
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Todo o código-fonte, esquemas de banco de dados, fluxos de automação, documentações técnicas, identidade visual e elementos do design system do <strong>MrStock ERP</strong> constituem criação e propriedade exclusiva da <strong>Papelaria Real Ltda</strong> e da equipe <strong>Mr. Coding (Douglas, Nikolas, Cesar, Enzo e Sugahara)</strong>, respaldados pela legislação de direitos autorais e propriedade de software (Leis Federais <strong>nº 9.609/1998</strong> e <strong>nº 9.610/1998</strong>).
            </p>
            <p style="color: #334155; line-height: 1.7; margin-bottom: 0;">
                É <strong>estritamente vedada qualquer tentativa de cópia, engenharia reversa, redistribuição, exploração comercial desautorizada ou modificação</strong> sem prévio consentimento formal dos titulares.
            </p>
        </section>

        <!-- 7. Canais de Suporte e Atendimento -->
        <section class="mb-4">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                7. Canais de Suporte e Atendimento
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Para suporte técnico, dúvidas de utilização ou encaminhamento de solicitações relativas ao sistema, estão disponíveis os canais oficiais de comunicação:
            </p>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                        <div class="fw-bold mb-1" style="color: #1e293b;"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Oficial:</div>
                        <p class="mb-0" style="color: #334155;">
                            <a href="https://wa.me/5515996441796?text=Ola%20equipe%20MrStock%2C%20preciso%20de%20suporte%20no%20ERP" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold" style="color: #14532d;">
                                <strong>(15) 99644-1796</strong>
                            </a>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                        <div class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-envelope text-primary me-2"></i>E-mail Institucional:</div>
                        <p class="mb-0" style="color: #334155;">
                            <a href="mailto:contato@mrstock.com.br" class="text-decoration-none fw-bold" style="color: #14532d;">
                                <strong>contato@mrstock.com.br</strong>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <strong>Endereço Comercial:</strong> Rua XV de Novembro, 250 - Centro, Sorocaba/SP • CEP: 18010-082
            </div>
        </section>

    </div>

    <!-- Navegação de Retorno -->
    <div class="d-flex justify-content-between align-items-center mt-3 mb-5">
        <a href="<?= $isLoggedIn ? BASE_URL . '/dashboard.php' : BASE_URL . '/login.php' ?>" class="btn btn-success" style="background-color: #14532d; border-color: #14532d; color: #ffffff; padding: 10px 24px; font-weight: 600; border-radius: 8px;">
            <i class="fas fa-arrow-left me-2"></i><?= $isLoggedIn ? 'Retornar ao Dashboard' : 'Voltar ao Login' ?>
        </a>
        <a href="<?= BASE_URL ?>/privacidade.php" class="text-decoration-none fw-semibold" style="color: #14532d;">
            Consultar Política de Privacidade <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>

</div>

<?php
if ($isLoggedIn) {
    require_once __DIR__ . '/inc/footer.php';
} else {
?>
    <footer class="public-footer">
        <div class="container">
            <div class="row g-2 align-items-center">
                <div class="col-md-4 col-12 text-md-start text-center">
                    <span>MrStock ERP © 2026 Papelaria Real</span>
                </div>
                <div class="col-md-4 col-12 text-center">
                    <a href="<?= BASE_URL ?>/termos.php" class="text-decoration-none fw-semibold text-white">Termos de Uso</a><span class="mx-2 text-white-50">•</span><a href="<?= BASE_URL ?>/privacidade.php" class="text-decoration-none fw-semibold" style="color: #cbd5e1;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#cbd5e1'">Política de Privacidade</a>
                </div>
                <div class="col-md-4 col-12 text-md-end text-center">
                    <a href="tel:+5515996441796" class="text-decoration-none" style="color: #cbd5e1;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#cbd5e1'"><i class="fas fa-phone-alt me-1" style="font-size: 0.7rem;"></i>(15) 99644-1796</a><span class="mx-2 text-white-50">•</span><a href="mailto:contato@mrstock.com.br" class="text-decoration-none" style="color: #cbd5e1;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#cbd5e1'"><i class="fas fa-envelope me-1" style="font-size: 0.7rem;"></i>contato@mrstock.com.br</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/inc/cookie_banner.php'; ?>
</body>
</html>
<?php } ?>
