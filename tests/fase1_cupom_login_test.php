<?php
declare(strict_types=1);

// Fixtures PHP CLI e SQLite em memória. Apenas o dialeto de data é adaptado.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require_once dirname(__DIR__) . '/inc/rate_limiter.php';

final class Fase1FixtureDb extends PDO
{
    public int $leituras = 0;

    public function __construct()
    {
        parent::__construct('sqlite::memory:');
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->leituras++;
        return parent::prepare($this->adaptarQuery($query), $options);
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $this->leituras++;
        return parent::query($this->adaptarQuery($query), $fetchMode, ...$fetchModeArgs);
    }

    public function exec(string $statement): int|false
    {
        // A tabela é criada com o schema de fixture antes de chamar o rate limiter.
        if (str_starts_with($statement, 'CREATE TABLE IF NOT EXISTS login_tentativas')) {
            return 0;
        }
        return parent::exec($statement);
    }

    private function adaptarQuery(string $query): string
    {
        return str_replace(
            ['CURRENT_DATE + INTERVAL 1 DAY', 'NOW() - INTERVAL ? MINUTE', 'NOW()'],
            ["DATE(CURRENT_DATE, '+1 day')", "DATETIME('now', '-' || ? || ' minutes')", 'CURRENT_TIMESTAMP'],
            $query
        );
    }
}

function conferir(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

function casoCupomWorker(string $perfil, string $rota, int $vendaId, string $documento): never
{
    $_SESSION = ['user_id' => 2, 'user_perfil' => $perfil];
    $_GET = ['venda_id' => $vendaId];

    $pdo = new Fase1FixtureDb();
    $pdo->exec("CREATE TABLE vendas (id INTEGER, cliente_id INTEGER, data_venda TEXT, total REAL, forma_pagamento TEXT);
        CREATE TABLE clientes (id INTEGER, nome TEXT, cpf_cnpj TEXT);
        CREATE TABLE vendas_itens (id INTEGER, venda_id INTEGER, produto_id INTEGER, quantidade INTEGER, preco_unitario REAL);
        CREATE TABLE produtos (id INTEGER, nome TEXT, codigo_de_barra TEXT);
        CREATE TABLE configuracoes (chave TEXT, valor TEXT);
        CREATE TABLE cupons_fiscais (id INTEGER, venda_id INTEGER, chave_acesso TEXT, data_emissao TEXT);
        INSERT INTO vendas VALUES (1, 1, CURRENT_DATE || ' 10:30:00', 12.5, 'Pix');
        INSERT INTO vendas VALUES (2, 1, DATETIME(CURRENT_DATE, '-1 second'), 12.5, 'Pix');
        INSERT INTO vendas VALUES (3, 1, DATETIME(CURRENT_DATE, '+1 day'), 12.5, 'Pix');");
    $pdo->prepare('INSERT INTO clientes VALUES (1, ?, ?)')->execute(['Consumidor fictício', $documento]);
    $pdo->leituras = 0;
    $GLOBALS['fase1FixtureDb'] = $pdo;

    $pasta = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mrstock-fase1-cupom-' . bin2hex(random_bytes(8));
    mkdir($pasta . '/inc', 0700, true);
    mkdir($pasta . '/vendas', 0700);
    $arquivos = ['inc/auth.php', 'inc/barcode_helper.php', 'inc/functions.php', 'vendas/cupom.php', 'vendas/nfce.php'];
    $raiz = dirname(__DIR__);
    foreach ($arquivos as $arquivo) {
        copy($raiz . '/' . $arquivo, $pasta . '/' . $arquivo);
    }
    file_put_contents($pasta . '/inc/database.php', '<?php define("BASE_URL", "/MrStock"); $pdo = $GLOBALS["fase1FixtureDb"];');
    $arquivos[] = 'inc/database.php';

    ob_start();
    register_shutdown_function(static function () use ($pasta, $arquivos, $pdo): void {
        $html = ob_get_clean();
        foreach ($arquivos as $arquivo) {
            unlink($pasta . '/' . $arquivo);
        }
        rmdir($pasta . '/inc');
        rmdir($pasta . '/vendas');
        rmdir($pasta);
        echo json_encode([
            'status' => http_response_code(),
            'html' => $html,
            'leituras' => $pdo->leituras,
            'flash' => $_SESSION['flash_error'] ?? null,
        ], JSON_THROW_ON_ERROR);
    });
    require $pasta . '/vendas/' . $rota . '.php';
    exit;
}

if (($argv[1] ?? '') === '--cupom-worker') {
    casoCupomWorker($argv[2], $argv[3], (int)$argv[4], $argv[5]);
}

function renderizarCaso(string $perfil, string $rota, int $vendaId, string $documento): array
{
    $comando = [PHP_BINARY, '-d', 'session.save_path=' . sys_get_temp_dir(), __FILE__, '--cupom-worker',
        $perfil, $rota, (string)$vendaId, $documento];
    $processo = proc_open($comando, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    conferir(is_resource($processo), 'Não foi possível iniciar a fixture de cupom');
    $saida = stream_get_contents($pipes[1]);
    $erros = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($processo);
    conferir($status === 0 && $erros === '', 'Erro na fixture de cupom: ' . $erros);
    return json_decode($saida, true, flags: JSON_THROW_ON_ERROR);
}

$casos = [
    'Caixa recebe 404 para vendas fora do dia e IDs sem registro' => static function (): void {
        foreach ([2, 3, 99] as $id) {
            $resposta = renderizarCaso('caixa', 'cupom', $id, '111.222.333-44');
            conferir($resposta['status'] === 404, 'Cupom indisponível deve responder 404');
            conferir($resposta['leituras'] === 1, 'Só a consulta protegida pode ser executada');
            conferir(!str_contains($resposta['html'], 'Consumidor fictício'), 'Recusa deve usar resposta genérica');
        }
    },
    'Caixa vê cupom de hoje com documentos protegidos sem distinção de operador' => static function (): void {
        foreach ([['111.222.333-44', '111.***.***-44'], ['11.222.333/4444-55', '11.***.***/****-55'], ['FICTICIOSEMFORMATO', '***']] as [$documento, $mascara]) {
            $resposta = renderizarCaso('caixa', 'cupom', 1, $documento);
            conferir(str_contains($resposta['html'], 'Consumidor fictício'), 'Cupom de hoje deve abrir sem condição de proprietário nesta fase');
            conferir(str_contains($resposta['html'], 'CPF/CNPJ:</strong> ' . $mascara), 'Máscara esperada ausente');
            conferir(!str_contains($resposta['html'], $documento), 'Documento completo encontrado na resposta do caixa');
        }
    },
    'Admin mantém reimpressão histórica' => static function (): void {
        $resposta = renderizarCaso('admin', 'cupom', 2, '111.222.333-44');
        conferir(str_contains($resposta['html'], 'Consumidor fictício'), 'Admin deve abrir cupom anterior');
    },
    'Listagem fiscal recusa caixa antes de consultar' => static function (): void {
        $resposta = renderizarCaso('caixa', 'nfce', 1, '111.222.333-44');
        conferir($resposta['leituras'] === 0 && $resposta['flash'] !== null, 'Listagem deve exigir administrador antes das consultas');
    },
    'IP direto é usado quando o proxy não está cadastrado' => static function (): void {
        $_ENV['MRSTOCK_TRUSTED_PROXY_IPS'] = '';
        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.0.2.20';
        $_SERVER['HTTP_CLIENT_IP'] = '192.0.2.30';
        conferir(get_client_ip() === '192.0.2.10', 'Sem proxy cadastrado, considerar somente o peer direto');
    },
    'Fronteira do proxy reconhece o primeiro endereço após os proxies confiáveis' => static function (): void {
        $_ENV['MRSTOCK_TRUSTED_PROXY_IPS'] = '2001:db8:0:0:0:0:0:1, 192.0.2.3';
        $_SERVER['REMOTE_ADDR'] = '2001:db8::1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.0.2.11, 192.0.2.12, 192.0.2.3';
        conferir(get_client_ip() === '192.0.2.12', 'A cadeia deve parar na fronteira de confiança');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'inválido, 192.0.2.3';
        conferir(get_client_ip() === '2001:db8::1', 'Cadeia inválida deve usar o peer direto');
    },
    'Rate limit separa os contadores por IP e par IP+usuário' => static function (): void {
        $pdo = new Fase1FixtureDb();
        $pdo->exec('CREATE TABLE login_tentativas (ip_usuario TEXT, username TEXT COLLATE NOCASE, data_tentativa TEXT)');
        $stmt = $pdo->prepare('INSERT INTO login_tentativas VALUES (?, ?, CURRENT_TIMESTAMP)');
        for ($i = 0; $i < 5; $i++) {
            $stmt->execute(['192.0.2.50', 'OperadorA']);
        }
        $stmt->execute(['192.0.2.50', 'OperadorB']);
        $stmt->execute(['192.0.2.51', 'OperadorB']);
        conferir(check_rate_limit($pdo, '192.0.2.50')['bloqueado'], 'O limite agregado atual deve continuar em 5');
        conferir(check_rate_limit($pdo, '192.0.2.50', 10, 15, 'operadora')['bloqueado'], 'O par deve bloquear em 5 mesmo com agregado maior');
        conferir(!check_rate_limit($pdo, '192.0.2.50', 10, 15, 'operadorb')['bloqueado'], 'Usuário distinto abaixo do limite com agregado maior');
        limpar_tentativas_ip($pdo, '192.0.2.50', 'operadorb');
        conferir((int)$pdo->query('SELECT COUNT(*) FROM login_tentativas')->fetchColumn() === 6, 'Sucesso deve limpar apenas o par autenticado');
    },
    'Falha do rate limiter conserva contingência e registra o erro' => static function (): void {
        $log = tempnam(sys_get_temp_dir(), 'mrstock-rate-');
        conferir($log !== false, 'Não foi possível preparar a saída de log da fixture');
        $anterior = ini_set('error_log', $log);
        try {
            $pdo = new Fase1FixtureDb();
            $status = check_rate_limit($pdo, '192.0.2.50', username: 'OperadorA');
            conferir($status['bloqueado'] === false, 'Falha de consulta deve manter a contingência');
            conferir(str_contains(file_get_contents($log), 'Falha na consulta ao rate limiter'), 'A contingência precisa registrar a falha');
        } finally {
            ini_set('error_log', (string)$anterior);
            unlink($log);
        }
    },
];

$falhas = 0;
foreach ($casos as $nome => $caso) {
    try {
        $caso();
        echo "PASS {$nome}\n";
    } catch (Throwable $e) {
        $falhas++;
        fwrite(STDERR, "FAIL {$nome}: {$e->getMessage()}\n");
    }
}
printf("%d casos; %d falhas; fixtures locais sem MySQL.\n", count($casos), $falhas);
exit($falhas === 0 ? 0 : 1);
