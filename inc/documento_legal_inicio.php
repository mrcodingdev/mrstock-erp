<?php
$isLoggedIn = isset($_SESSION['user_id']);
$activePage = '';
$cssLegal = BASE_URL . '/css/documentos-legais.css?v=1';
$documentosLegais = [
    'privacidade' => 'Política de Privacidade',
    'termos' => 'Termos de Uso',
];
$indiceLegal = static function () use ($documentoLegal, $secoesLegais): void {
    ?>
    <ol class="index">
        <?php foreach ($secoesLegais as $i => $secao): ?>
            <?php $ancora = $documentoLegal === 'privacidade' && $i === 5 ? 'cookies' : $documentoLegal . '-' . ($i + 1); ?>
            <li><a href="#<?= $ancora ?>"><span class="index-no"><?= sprintf('%02d', $i + 1) ?></span><?= htmlspecialchars($secao, ENT_QUOTES, 'UTF-8') ?></a></li>
        <?php endforeach; ?>
    </ol>
    <?php
};

if ($isLoggedIn) {
    require __DIR__ . '/header.php';
} else {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - MrStock ERP</title>
    <meta name="description" content="<?= htmlspecialchars($metaDescricaoLegal) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?> - MrStock ERP">
    <meta property="og:description" content="<?= htmlspecialchars($descricaoLegal) ?>">
    <meta property="og:url" content="https://mrstock.com.br/<?= $documentoLegal ?>.php">
    <meta property="og:image" content="https://mrstock.com.br/assets/img/mrstock_og.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="MrStock ERP">
    <meta name="theme-color" content="#fbfaf4">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css">
    <link rel="stylesheet" href="<?= $cssLegal ?>">
</head>
<body class="legal-public">
<?php } ?>
<?php if ($isLoggedIn): ?>
    <link rel="stylesheet" href="<?= $cssLegal ?>">
<?php endif; ?>
<div class="legal-page">
    <?php if (!$isLoggedIn): ?>
        <a class="skip" href="#conteudo-legal">Ir para o conteúdo</a>
        <header class="site-header">
            <div class="wrap header-row">
                <a class="brand" href="<?= BASE_URL ?>/" aria-label="MrStock ERP, início"><img src="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" alt="" width="27" height="27"><span>MrStock <small>ERP</small></span></a>
                <a class="back" href="<?= BASE_URL ?>/login.php"><span aria-hidden="true">←</span> Voltar ao login</a>
            </div>
        </header>
    <?php endif; ?>
    <<?= $isLoggedIn ? 'article' : 'main' ?> id="conteudo-legal" tabindex="-1">
        <header class="hero">
            <div class="wrap hero-inner">
                <p class="kicker">Documentos do MrStock</p>
                <h1 class="title"><?= htmlspecialchars($pageTitle) ?></h1>
                <p class="intro"><?= htmlspecialchars($descricaoLegal) ?></p>
                <p class="stamp"><span>Última atualização</span><time datetime="2026-11-25">25 de novembro de 2026</time></p>
                <nav class="document-nav" aria-label="Escolher documento">
                    <?php foreach ($documentosLegais as $slug => $titulo): ?>
                        <a href="<?= BASE_URL ?>/<?= $slug ?>.php"<?= $slug === $documentoLegal ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($titulo) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </header>
        <div class="wrap reader">
            <nav class="sidebar" aria-label="Nesta página"><p class="index-title">Nesta página</p><?php $indiceLegal(); ?></nav>
            <div class="sections">
                <details class="mobile-index"><summary>Nesta página</summary><nav aria-label="Seções deste documento"><?php $indiceLegal(); ?></nav></details>
