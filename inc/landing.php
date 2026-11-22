<?php

function servir_landing(string $arquivo): never
{
    $caminho = ROOT_PATH . '/landing/' . $arquivo;
    header('Cache-Control: private, no-store');
    header('X-Robots-Tag: noindex');

    if (!is_readable($caminho)) {
        http_response_code(503);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('A apresentação do MrStock está temporariamente indisponível.');
    }

    header('Content-Type: text/html; charset=UTF-8');
    readfile($caminho);
    exit;
}

function destino_sessao_landing(): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user_id'])) {
        return null;
    }

    $destinos = ['admin' => '/dashboard.php', 'caixa' => '/vendas/pdv.php'];
    $perfil = $_SESSION['user_perfil'] ?? null;
    $idSessao = $_SESSION['user_id'];
    $id = is_int($idSessao) || is_string($idSessao)
        ? filter_var($idSessao, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
        : false;
    if ($id === false || !is_string($perfil) || !isset($destinos[$perfil])) {
        invalidar_sessao_landing();
        return null;
    }

    try {
        require __DIR__ . '/database.php';
        $conta = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = :id');
        $conta->bindValue(':id', $id, PDO::PARAM_INT);
        $conta->execute();
        $perfilAtual = $conta->fetchColumn();
    } catch (PDOException) {
        error_log('Não foi possível validar a sessão na entrada pública do MrStock.');
        return null;
    }

    if ($perfilAtual !== $perfil) {
        invalidar_sessao_landing();
        return null;
    }

    return $destinos[$perfil];
}

function invalidar_sessao_landing(): void
{
    $_SESSION = [];
    session_destroy();

    if (ini_get('session.use_cookies')) {
        $opcoes = session_get_cookie_params();
        unset($opcoes['lifetime']);
        $opcoes['expires'] = time() - 42000;
        setcookie(session_name(), '', $opcoes);
    }
}
