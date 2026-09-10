<?php
/**
 * MrStock ERP - Página Institucional 404 (Não Encontrada)
 * Resposta de status HTTP 404 defensiva com design system institucional.
 * Híbrida: Exibe layout integrado para usuários autenticados e layout limpo para visitantes públicos.
 */
http_response_code(404);
require_once __DIR__ . '/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$homeUrl    = $isLoggedIn ? BASE_URL . '/dashboard.php' : BASE_URL . '/login.php';
$btnLabel   = $isLoggedIn ? 'Voltar para o Início' : 'Ir para o Login';

if ($isLoggedIn) {
    $pageTitle  = '404 - Página Não Encontrada';
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
    <meta name="description" content="Página Não Encontrada - MrStock ERP.">
    <title>MrStock ERP - 404 Página Não Encontrada</title>
    
    <!-- Metadados OpenGraph & Tema Institucional -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="MrStock ERP - 404 Página Não Encontrada">
    <meta property="og:description" content="O recurso solicitado não existe ou foi movido no MrStock ERP.">
    <meta property="og:url" content="https://mrstock.com.br/404.php">
    <meta property="og:image" content="https://mrstock.com.br/assets/img/mrstock_og.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
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
            --bg-color: #e8f3ee;
            --text-dark: #1e293b;
            --text-muted: #475569;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            margin: 0;
            padding: 20px;
        }
        .bg-shapes {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        .shape {
            position: absolute;
            background: linear-gradient(135deg, rgba(44,110,83,0.12), rgba(26,66,49,0.03));
            border-radius: 50%;
            animation: float 25s infinite ease-in-out alternate;
        }
        .shape-1 { width: 900px; height: 900px; top: -300px; left: -250px; }
        .shape-2 { width: 800px; height: 800px; bottom: -250px; right: -200px; }
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50px, 50px) rotate(30deg); }
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
<?php endif; ?>

<div class="<?= $isLoggedIn ? 'content-body' : 'container position-relative' ?>" style="z-index: 1; max-width: 680px; margin: 0 auto; animation: mrStockSlideInLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;">
    
    <div class="card border shadow-lg rounded-3 text-center p-5" style="background: #ffffff; border-color: #cbd5e1 !important;">
        <!-- Logotipo Institucional -->
        <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 12px; background: linear-gradient(135deg, #1a4231, #284936); box-shadow: 0 8px 24px rgba(40, 73, 54, 0.3);">
                <img src="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" alt="MrStock ERP" width="44" height="44" style="object-fit: contain;">
            </div>
        </div>

        <!-- Badge de Código de Status -->
        <div class="mb-3">
            <span class="badge px-3 py-2 fs-6 fw-bold" style="background: rgba(40, 73, 54, 0.12); color: #284936; border: 1px solid rgba(40, 73, 54, 0.25);">
                Código de Erro HTTP 404
            </span>
        </div>

        <!-- Tipografia 404 -->
        <h1 class="display-3 fw-bold mb-2" style="color: #1a4231; letter-spacing: -2px;">404</h1>
        <h2 class="h4 fw-bold mb-3" style="color: #1e293b;">Página Não Encontrada</h2>
        
        <p class="mb-4 text-secondary" style="font-size: 1rem; color: #475569 !important; line-height: 1.6; max-width: 480px; margin-left: auto; margin-right: auto;">
            O endereço solicitado não existe, foi alterado ou está temporariamente indisponível no servidor do <strong>MrStock ERP</strong>.
        </p>

        <!-- Botão Sólido Institucional -->
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= $homeUrl ?>" class="btn btn-success" style="background-color: #284936; border-color: #284936; color: #ffffff; padding: 12px 28px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 12px rgba(40, 73, 54, 0.25);" aria-label="Voltar para a página principal">
                <i class="fas fa-home me-2"></i><?= $btnLabel ?>
            </a>
            <a href="<?= BASE_URL ?>/privacidade.php" class="btn btn-secondary" style="background-color: #475569; border-color: #475569; color: #ffffff; padding: 12px 20px; font-weight: 600; border-radius: 8px;" aria-label="Acessar política de privacidade">
                <i class="fas fa-shield-halved me-2"></i>Privacidade &amp; LGPD
            </a>
        </div>

        <div class="mt-4 pt-3 border-top" style="border-color: #e2e8f0 !important;">
            <small style="color: #475569; font-size: 0.8125rem;">
                MrStock ERP • Papelaria Real Ltda • ETEC Fernando Prestes
            </small>
        </div>
    </div>

</div>

<?php
if ($isLoggedIn) {
    require_once __DIR__ . '/inc/footer.php';
} else {
?>
    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/inc/cookie_banner.php'; ?>
</body>
</html>
<?php } ?>
