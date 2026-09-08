<?php
/**
 * MrStock ERP - Política de Privacidade & Proteção de Dados (LGPD)
 * Em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018)
 * e o Marco Civil da Internet (Lei nº 12.965/2014, Art. 15).
 * 
 * Arquitetura Híbrida: Suporta renderização integrada ao ERP (usuário logado)
 * e visualização institucional limpa (usuário visitante/deslogado).
 */
require_once __DIR__ . '/config.php';

$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $pageTitle  = 'Política de Privacidade & LGPD';
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
    <meta name="description" content="Política de Privacidade e Proteção de Dados do MrStock ERP - Papelaria Real. Em conformidade com a LGPD.">
    <title>MrStock ERP - Política de Privacidade &amp; LGPD</title>
    
    <!-- Metadados OpenGraph & Tema Institucional -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="MrStock ERP - Política de Privacidade &amp; LGPD">
    <meta property="og:description" content="Política de Privacidade e Proteção de Dados do MrStock ERP para a Papelaria Real.">
    <meta property="og:url" content="https://mrstock.com.br/privacidade.php">
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
                <h1 class="h2 fw-bold mb-2 text-white">Política de Privacidade &amp; Proteção de Dados</h1>
                <p class="mb-2 text-white-50" style="font-size: 0.95rem;">Diretrizes de conformidade, segurança da informação e transparência da Papelaria Real Ltda.</p>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background: rgba(106, 228, 155, 0.2); color: #6ae49b; border: 1px solid rgba(106, 228, 155, 0.3);">LGPD Compliance</span>
                    <span class="badge" style="background: rgba(255, 255, 255, 0.15); color: #ffffff;">Marco Civil da Internet</span>
                    <span class="badge" style="background: rgba(255, 255, 255, 0.15); color: #ffffff;">Versão 2.2.0</span>
                </div>
            </div>
            <div class="text-md-end text-start">
                <small class="text-white-50 d-block">Última atualização:</small>
                <strong class="text-white">07 de Setembro de 2026</strong>
            </div>
        </div>
    </div>

    <!-- Conteúdo Jurídico Estruturado -->
    <div class="card border shadow-sm rounded-3 p-4 mb-4" style="background: #ffffff; border-color: #cbd5e1 !important;">
        
        <!-- 1. Controlador e Operador -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-building text-success me-2" style="color: #284936 !important;"></i>1. Qualificação das Partes: Controlador e Operador
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Para fins do disposto na Lei Geral de Proteção de Dados Pessoais (LGPD – Lei Federal nº 13.709/2018), as operações de tratamento de dados pessoais realizadas através do <strong>MrStock ERP</strong> operam sob a seguinte estrutura de governança:
            </p>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <h3 class="h6 fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-store me-2 text-success" style="color: #284936 !important;"></i>Controlador do Tratamento:</h3>
                        <p class="mb-0 small" style="color: #475569;">
                            <strong>Papelaria Real Ltda</strong><br>
                            CNPJ: 12.345.678/0001-90<br>
                            Sorocaba/SP – Brasil<br>
                            Canal Institucional: <code>contato@mrstock.com.br</code>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <h3 class="h6 fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-code me-2 text-success" style="color: #284936 !important;"></i>Operador &amp; Desenvolvedor:</h3>
                        <p class="mb-0 small" style="color: #475569;">
                            <strong>Equipe Mr. Coding</strong><br>
                            Desenvolvimento e Engenharia de Software<br>
                            ETEC Fernando Prestes – Centro Paula Souza<br>
                            Orientação Técnica: Prof. Vinicius
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Dados Coletados -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-database text-success me-2" style="color: #284936 !important;"></i>2. Dados Pessoais Coletados e Finalidades do Tratamento
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O MrStock ERP processa exclusivamente dados pessoais estritamente necessários à operação comercial da Papelaria Real, divididos nas seguintes categorias:
            </p>
            <div class="table-responsive mt-3">
                <table class="table table-bordered align-middle" style="border-color: #cbd5e1;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="color: #1e293b; width: 25%;">Categoria de Titular</th>
                            <th style="color: #1e293b; width: 35%;">Dados Pessoais Coletados</th>
                            <th style="color: #1e293b; width: 40%;">Finalidade Específica</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.875rem; color: #334155;">
                        <tr>
                            <td><strong>Clientes / Consumidores</strong></td>
                            <td>Nome completo, CPF (Cadastro de Pessoa Física), telefone/WhatsApp, e-mail e endereço para faturamento/entrega.</td>
                            <td>Identificação comercial, faturamento de vendas no PDV, emissão de NFC-e, controle de contas a receber e pós-venda.</td>
                        </tr>
                        <tr>
                            <td><strong>Operadores / Colaboradores</strong></td>
                            <td>Nome de usuário (username), credencial criptografada (hash BCrypt Cost 12) e perfil de acesso (RBAC).</td>
                            <td>Autenticação biométrica/credencial, segregação de privilégios de acesso e prevenção de acessos indevidos.</td>
                        </tr>
                        <tr>
                            <td><strong>Registros de Conexão (Logs Forenses)</strong></td>
                            <td>Endereço IP de origem, data, horário (UTC-3), recurso acessado e identificador de ação no sistema.</td>
                            <td>Cumprimento mandatório do <strong>Artigo 15 da Lei nº 12.965/2014 (Marco Civil da Internet)</strong> e auditoria interna contra fraudes.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 3. Bases Legais -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-scale-balanced text-success me-2" style="color: #284936 !important;"></i>3. Bases Legais do Tratamento (Art. 7º da LGPD)
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Todas as operações de tratamento de dados realizadas pelo MrStock ERP possuem respaldo jurídico inequívoco nas seguintes hipóteses legais do Artigo 7º da Lei Federal nº 13.709/2018:
            </p>
            <ul style="color: #334155; line-height: 1.8;">
                <li><strong>Execução de Contrato ou Procedimentos Preliminares (Art. 7º, V):</strong> Necessário para a formalização da compra e venda de mercadorias no balcão e no PDV, movimentação de itens e entrega física de produtos.</li>
                <li><strong>Cumprimento de Obrigação Legal ou Regulatória (Art. 7º, II):</strong> Guarda de documentos fiscais e dados contábeis pelo prazo mínimo de 5 (cinco) anos, em cumprimento aos Artigos 173 e 174 do Código Tributário Nacional (CTN) e regulamentações da SEFAZ/SP.</li>
                <li><strong>Cumprimento do Marco Civil da Internet (Art. 7º, II c/c Lei 12.965/2014, Art. 15):</strong> Armazenamento sob sigilo dos registros de acesso a aplicações de internet pelo prazo regulamentar de 6 (seis) meses.</li>
                <li><strong>Legítimo Interesse do Controlador (Art. 7º, IX):</strong> Auditoria interna, aperfeiçoamento da estabilidade do software, integridade contábil e prevenção ativa contra fraudes comerciais.</li>
            </ul>
        </section>

        <!-- 4. Direitos dos Titulares -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-user-shield text-success me-2" style="color: #284936 !important;"></i>4. Direitos dos Titulares de Dados (Art. 18 da LGPD)
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O titular dos dados pessoais tem direito a obter da Papelaria Real Ltda, a qualquer momento e mediante requisição formal, em relação aos dados por ela tratados:
            </p>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="p-2 border rounded" style="border-color: #cbd5e1 !important; background: #f8fafc; font-size: 0.85rem;">
                        <i class="fas fa-check-circle text-success me-2" style="color: #284936 !important;"></i>Confirmação da existência de tratamento e acesso aos dados;
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2 border rounded" style="border-color: #cbd5e1 !important; background: #f8fafc; font-size: 0.85rem;">
                        <i class="fas fa-check-circle text-success me-2" style="color: #284936 !important;"></i>Correção de dados incompletos, inexatos ou desatualizados;
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2 border rounded" style="border-color: #cbd5e1 !important; background: #f8fafc; font-size: 0.85rem;">
                        <i class="fas fa-check-circle text-success me-2" style="color: #284936 !important;"></i>Anonimização, bloqueio ou eliminação de dados excessivos;
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2 border rounded" style="border-color: #cbd5e1 !important; background: #f8fafc; font-size: 0.85rem;">
                        <i class="fas fa-check-circle text-success me-2" style="color: #284936 !important;"></i>Revogação de consentimento e eliminação conforme disposições legais.
                    </div>
                </div>
            </div>
            <div class="alert alert-light border p-3 rounded-3" style="border-color: #cbd5e1 !important; background: #f8fafc;">
                <h3 class="h6 fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-envelope text-success me-2" style="color: #284936 !important;"></i>Canal Oficial de Atendimento ao Titular / DPO:</h3>
                <p class="mb-0 small" style="color: #475569;">
                    Para exercer quaisquer de seus direitos previstos no Art. 18 da LGPD, o titular poderá encaminhar solicitação para o e-mail: <strong><code>contato@mrstock.com.br</code></strong>, com o assunto <em>"Requisição LGPD - Titular de Dados"</em>. As solicitações serão atendidas no prazo regulamentar de até 15 (quinze) dias.
                </p>
            </div>
        </section>

        <!-- 5. Segurança Técnica -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-shield-halved text-success me-2" style="color: #284936 !important;"></i>5. Camadas de Segurança Técnica e Defesa Cibernética
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                O MrStock ERP adota medidas técnicas, organizacionais e administrativas de segurança aptas a proteger os dados pessoais contra acessos não autorizados e eventos acidentais ou ilícitos:
            </p>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 h-100" style="border-color: #cbd5e1 !important; background: #ffffff;">
                        <div class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-lock text-success me-2" style="color: #284936 !important;"></i>Criptografia TLS 1.3</div>
                        <p class="small mb-0" style="color: #475569;">Comunicação web 100% criptografada sob HTTPS com certificados SSL/TLS vigentes e HSTS ativo.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 h-100" style="border-color: #cbd5e1 !important; background: #ffffff;">
                        <div class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-key text-success me-2" style="color: #284936 !important;"></i>Hash BCrypt Cost 12</div>
                        <p class="small mb-0" style="color: #475569;">Credenciais de operadores salvas em hashes unidirecionais BCrypt com fator de custo elevado.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 h-100" style="border-color: #cbd5e1 !important; background: #ffffff;">
                        <div class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-shield-virus text-success me-2" style="color: #284936 !important;"></i>PDO &amp; Anti-CSRF</div>
                        <p class="small mb-0" style="color: #475569;">Prepared Statements PDO contra SQL Injection e verificação de tokens CSRF e Session Fixation.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Política de Cookies (#cookies) -->
        <section id="cookies" class="mb-4 pt-2">
            <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3" style="color: #1e293b;">
                <i class="fas fa-cookie-bite text-success me-2" style="color: #284936 !important;"></i>6. Política de Cookies &amp; Rastreamento Técnico
            </h2>
            <p style="color: #334155; line-height: 1.7;">
                Cookies são pequenos arquivos gravados no dispositivo do usuário para permitir o funcionamento adequado da aplicação. O MrStock ERP utiliza <strong>exclusivamente cookies técnicos estritamente necessários</strong> para garantir a segurança, integridade de sessão e ergonomia visual:
            </p>
            <div class="table-responsive mt-3">
                <table class="table table-bordered align-middle" style="border-color: #cbd5e1;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="color: #1e293b;">Identificador</th>
                            <th style="color: #1e293b;">Tipo</th>
                            <th style="color: #1e293b;">Validade</th>
                            <th style="color: #1e293b;">Finalidade Técnica</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.875rem; color: #334155;">
                        <tr>
                            <td><code>PHPSESSID</code></td>
                            <td>Cookie de Sessão HTTP</td>
                            <td>Ao fechar navegador</td>
                            <td>Identificador criptográfico de sessão de operador autenticado (`HttpOnly`, `SameSite=Lax`).</td>
                        </tr>
                        <tr>
                            <td><code>mrstock_sidebar_state</code></td>
                            <td>Armazenamento Local (LocalStorage)</td>
                            <td>Persistente</td>
                            <td>Memorização da preferência de layout do menu lateral (expandido vs. retrátil) para eliminar FOUC.</td>
                        </tr>
                        <tr>
                            <td><code>mrstock_cookie_consent</code></td>
                            <td>Armazenamento Local (LocalStorage)</td>
                            <td>Persistente</td>
                            <td>Registro da confirmação de ciência e conformidade com os cookies técnicos essenciais sob a LGPD.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-light border p-3 mt-3 rounded-3" style="border-color: #cbd5e1 !important; background: #f8fafc;">
                <p class="small mb-0" style="color: #475569;">
                    <i class="fas fa-info-circle text-success me-1" style="color: #284936 !important;"></i> <strong>Transparência Absoluta:</strong> O MrStock ERP <strong>não utiliza cookies de rastreamento de terceiros, pixels de redes sociais ou ferramentas de publicidade comportamental</strong>. O usuário pode desativar o armazenamento de cookies diretamente nas configurações de seu navegador web, ciente de que o acesso às áreas autenticadas do ERP poderá ficar comprometido.
                </p>
            </div>
        </section>

    </div>

    <!-- Navegação de Retorno -->
    <div class="d-flex justify-content-between align-items-center mt-3 mb-5">
        <a href="<?= $isLoggedIn ? BASE_URL . '/dashboard.php' : BASE_URL . '/login.php' ?>" class="btn btn-success" style="background-color: #284936; border-color: #284936; color: #ffffff; padding: 10px 24px; font-weight: 600; border-radius: 8px;">
            <i class="fas fa-arrow-left me-2"></i><?= $isLoggedIn ? 'Retornar ao Dashboard' : 'Voltar ao Login' ?>
        </a>
        <a href="<?= BASE_URL ?>/termos.php" class="text-decoration-none fw-semibold" style="color: #284936;">
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
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span>MrStock ERP © 2026 Papelaria Real Ltda • Sistema de Gestão Comercial</span>
            </div>
            <div class="d-flex gap-3">
                <a href="<?= BASE_URL ?>/termos.php" class="text-decoration-none" style="color: #cbd5e1;">Termos de Uso</a>
                <span class="text-muted">•</span>
                <a href="<?= BASE_URL ?>/privacidade.php" class="text-decoration-none text-white fw-semibold">Privacidade &amp; LGPD</a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="tel:+5511987654321" class="text-decoration-none" style="color: #cbd5e1;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#cbd5e1'" aria-label="Ligar para Papelaria Real no número (11) 98765-4321"><i class="fas fa-phone-alt me-1" style="font-size: 0.75rem;"></i>(11) 98765-4321</a>
                <span class="text-muted">•</span>
                <a href="mailto:contato@mrstock.com.br" class="text-decoration-none" style="color: #cbd5e1;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#cbd5e1'" aria-label="Enviar e-mail para contato@mrstock.com.br"><i class="fas fa-envelope me-1" style="font-size: 0.75rem;"></i>contato@mrstock.com.br</a>
            </div>
        </div>
    </footer>
    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/inc/cookie_banner.php'; ?>
</body>
</html>
<?php } ?>
