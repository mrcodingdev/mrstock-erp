<?php
/**
 * MrStock ERP - Política de Privacidade
 * Diretrizes de transparência e tratamento de dados da Papelaria Real.
 * 
 * Arquitetura Híbrida: Suporta renderização integrada ao ERP (usuário logado)
 * e visualização institucional limpa (usuário visitante/deslogado).
 */
require_once __DIR__ . '/config.php';

$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $pageTitle  = 'Política de Privacidade';
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
    <meta name="description" content="Política de Privacidade do MrStock ERP - Papelaria Real.">
    <title>Política de Privacidade - MrStock ERP</title>
    
    <!-- Metadados OpenGraph & Tema Institucional -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Política de Privacidade - MrStock ERP">
    <meta property="og:description" content="Diretrizes de transparência e tratamento de dados da Papelaria Real.">
    <meta property="og:url" content="https://mrstock.com.br/privacidade.php">
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
                <h1 class="h2 fw-bold mb-1 text-white">Política de Privacidade</h1>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">Diretrizes de transparência e tratamento de dados da Papelaria Real.</p>
            </div>
            <div class="text-md-end text-start">
                <small class="text-white-50 d-block">Última atualização</small>
                <strong class="text-white">24 de Outubro de 2026</strong>
            </div>
        </div>
    </div>

    <!-- Conteúdo da Política de Privacidade (7 Seções Claras e Objetivas) -->
    <div class="card border shadow-sm rounded-3 p-4 mb-4" style="background: #ffffff; border-color: #cbd5e1 !important;">
        
        <!-- 1. Informações que Coletamos -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                1. Informações que Coletamos
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Para o regular funcionamento das operações comerciais e administrativas da <strong>Papelaria Real Ltda - ME</strong>, o MrStock ERP realiza a coleta e o processamento dos seguintes dados cadastrais e operacionais:
            </p>
            <ul style="color: #334155; line-height: 1.8; margin-bottom: 0;">
                <li><strong>Dados de Clientes:</strong> Nome completo ou razão social, <strong>CPF/CNPJ</strong> (quando informado para faturamento), telefone/<strong>WhatsApp</strong> para contato e <strong>endereço para entrega</strong> de mercadorias.</li>
                <li><strong>Registros de Vendas:</strong> <strong>Histórico de itens adquiridos, quantidades e valores</strong> faturados, data e hora da transação e modalidades de pagamento empregadas.</li>
                <li><strong>Dados de Operadores e Colaboradores:</strong> Nome de usuário, credenciais com <strong>hash criptográfico</strong>, nível de permissão (<strong>RBAC</strong>) e <strong>logs técnicos de auditoria</strong> com carimbo temporal e endereço IP.</li>
            </ul>
        </section>

        <!-- 2. Finalidade do Tratamento -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                2. Finalidade do Tratamento
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Os dados tratados pela Papelaria Real possuem finalidades operacionais legítimas e bem definidas:
            </p>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                        <h3 class="h6 fw-bold mb-2" style="color: #1e293b;"><i class="fas fa-cash-register me-2 text-success" style="color: #14532d !important;"></i>Operação do PDV &amp; Balcão:</h3>
                        <p class="mb-0 small" style="color: #475569; line-height: 1.6;">
                            Identificação do comprador no registro de pedidos, emissão de <strong>comprovantes de venda térmicos</strong> e conferência de <strong>trocas de produtos</strong> escolares e de escritório.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                        <h3 class="h6 fw-bold mb-2" style="color: #1e293b;"><i class="fab fa-whatsapp me-2 text-success"></i>Comunicação e Entrega:</h3>
                        <p class="mb-0 small" style="color: #475569; line-height: 1.6;">
                            Aviso de chegada de <strong>listas de material escolar</strong>, <strong>orçamentos solicitados</strong> e confirmação de <strong>rotas de entrega</strong> de pedidos corporativos via WhatsApp ou telefone.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Armazenamento e Segurança da Informação -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                3. Armazenamento e Segurança da Informação
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Adotamos controles rígidos de engenharia de software para resguardar a integridade, o sigilo e a disponibilidade de todas as bases de dados:
            </p>
            <ul style="color: #334155; line-height: 1.8; margin-bottom: 0;">
                <li><strong>Criptografia em Trânsito:</strong> Conexões protegidas sob protocolo <strong>HTTPS</strong> com certificados <strong>SSL/TLS 1.3</strong> ativos em ambiente de produção na Hostinger.</li>
                <li><strong>Proteção de Credenciais:</strong> Senhas armazenadas com <strong>hash criptográfico unidirecional seguro (Bcrypt)</strong>, sendo inacessíveis inclusive aos administradores.</li>
                <li><strong>Prevenção contra Intrusões:</strong> Utilização estrita de <strong>consultas preparadas PDO (Prepared Statements)</strong> para neutralizar tentativas de injeção de SQL (<strong>SQL Injection</strong>), além de tokens de sessão <strong>anti-CSRF</strong> e renovação de <strong>Session ID</strong> contra fixação de sessão.</li>
            </ul>
        </section>

        <!-- 4. Compartilhamento de Dados -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                4. Compartilhamento de Dados
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                A <strong>Papelaria Real Ltda</strong> adota <strong>política de compartilhamento zero</strong>:
            </p>
            <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                <p class="mb-0 small" style="color: #334155; line-height: 1.6;">
                    <strong>Não comercializamos, alugamos, cedemos ou repassamos informações cadastrais</strong> de clientes ou colaboradores a terceiros, parceiros de publicidade ou ferramentas de marketing externo. As informações ficam <strong>restritas aos sistemas de gestão interna da loja física</strong> para processamento de transações comerciais.
                </p>
            </div>
        </section>

        <!-- 5. Direitos do Titular de Dados -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                5. Direitos do Titular de Dados
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Em observância à transparência e aos direitos do consumidor, qualquer cliente cadastrado pode requisitar à Papelaria Real, mediante solicitação formal:
            </p>
            <ul style="color: #334155; line-height: 1.8; margin-bottom: 0;">
                <li><strong>Confirmação da existência</strong> de registros cadastrais vinculados ao seu CPF;</li>
                <li><strong>Correção ou atualização imediata</strong> de dados cadastrais incompletos, inexatos ou antigos;</li>
                <li><strong>Exclusão de dados pessoais</strong> da base operacional, ressalvada a guarda obrigatória de registros contábeis e fiscais exigida pela legislação tributária aplicável.</li>
            </ul>
        </section>

        <!-- 6. Cookies e Tecnologias de Navegação -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                6. Cookies e Tecnologias de Navegação
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O MrStock ERP utiliza exclusivamente recursos técnicos fundamentais para o funcionamento da aplicação web:
            </p>
            <div class="table-responsive mt-2">
                <table class="table table-bordered align-middle" style="border-color: #cbd5e1;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="color: #1e293b; width: 30%;">Recurso Técnico</th>
                            <th style="color: #1e293b; width: 25%;">Tipo</th>
                            <th style="color: #1e293b; width: 45%;">Finalidade</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.875rem; color: #334155;">
                        <tr>
                            <td><strong><code>PHPSESSID</code></strong></td>
                            <td><strong>Cookie de Sessão HTTP</strong></td>
                            <td>Manter a autenticação segura do operador durante o expediente de trabalho (<strong><code>HttpOnly</code></strong> e <strong><code>SameSite=Lax</code></strong>).</td>
                        </tr>
                        <tr>
                            <td><strong><code>mrstock_sidebar_state</code></strong></td>
                            <td><strong>Armazenamento Local (LocalStorage)</strong></td>
                            <td>Memorizar a preferência de visualização do menu lateral (expandido ou recolhido) sem oscilação visual.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="small mb-0 text-muted">
                O sistema <strong>não utiliza cookies de rastreamento de anúncios, pixels espiões ou scripts analíticos invasivos de terceiros</strong>.
            </p>
        </section>

        <!-- 7. Canal de Atendimento e Dúvidas -->
        <section class="mb-4">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                7. Canal de Atendimento e Dúvidas
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Para esclarecimentos adicionais, exercício de direitos de titular ou solicitações sobre o tratamento de dados pessoais, entre em contato diretamente com a equipe responsável:
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
        <a href="<?= BASE_URL ?>/termos.php" class="text-decoration-none fw-semibold" style="color: #14532d;">
            Consultar Termos de Uso <i class="fas fa-arrow-right ms-1"></i>
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
                    <a href="<?= BASE_URL ?>/termos.php" class="text-decoration-none fw-semibold" style="color: #cbd5e1;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#cbd5e1'">Termos de Uso</a><span class="mx-2 text-white-50">•</span><a href="<?= BASE_URL ?>/privacidade.php" class="text-decoration-none fw-semibold text-white">Política de Privacidade</a>
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
