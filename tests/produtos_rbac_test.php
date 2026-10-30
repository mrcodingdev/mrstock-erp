<?php
declare(strict_types=1);

// Execute via CLI. Fixtures isolate rendering from MySQL and real credentials.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

final class ProdutosFixtureDb
{
    public array $queries = [];
    public array $product = [
        'id' => 37, 'nome' => 'Caneta de teste RBAC', 'categoria' => 'Teste RBAC',
        'categoria_id' => 1, 'categoria_nome_rel' => 'Teste RBAC',
        'fornecedor_id' => 1, 'fornecedor_nome' => 'Fornecedor ficticio',
        'codigo_de_barra' => '7890000000037', 'quantidade' => 12,
        'estoque_minimo' => 3, 'preco_venda' => '19.90',
        'preco_compra' => '8457.13', 'validade' => null, 'status' => 'ativo',
    ];

    public function __construct(public bool $empty = false) {}

    public function prepare(string $sql): ProdutosFixtureStatement
    {
        $this->queries[] = preg_replace('/\s+/', ' ', trim($sql));
        return new ProdutosFixtureStatement($this, $sql);
    }

    public function query(string $sql): ProdutosFixtureStatement
    {
        return $this->prepare($sql);
    }
}

final class ProdutosFixtureStatement
{
    private array $params = [];

    public function __construct(private ProdutosFixtureDb $db, private string $sql) {}

    public function bindValue(string $name, mixed $value, int $type = 0): void
    {
        $this->params[$name] = $value;
    }

    public function execute(?array $params = null): bool
    {
        $this->params = $params ?? $this->params;
        if (!preg_match('/^\s*SELECT\b/i', $this->sql)) {
            throw new RuntimeException('Fixture refuses writes');
        }
        return true;
    }

    public function fetchColumn(): int
    {
        return $this->db->empty ? 0 : 11;
    }

    public function fetch(): array|false
    {
        return (int)($this->params[0] ?? 0) === 37 ? $this->db->product : false;
    }

    public function fetchAll(): array
    {
        if (preg_match('/FROM produtos p\b/i', $this->sql)) {
            return $this->db->empty ? [] : [$this->db->product];
        }
        return [['id' => 1, 'nome' => 'Teste RBAC']];
    }
}

function renderWorker(array $case): never
{
    $root = dirname(__DIR__);
    $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mrstock-produtos-rbac-' . bin2hex(random_bytes(8));
    mkdir($temp . '/inc', 0700, true);
    mkdir($temp . '/produtos', 0700);
    foreach (['produtos/index.php', 'produtos/functions.php', 'inc/auth.php'] as $path) {
        copy($root . '/' . $path, $temp . '/' . $path);
    }
    file_put_contents($temp . '/inc/database.php', '<?php $pdo = $GLOBALS["fixtureDb"]; define("BASE_URL", "/MrStock");');
    file_put_contents($temp . '/inc/functions.php', '<?php');
    file_put_contents($temp . '/inc/header.php', <<<'PHP'
<?php
function csrf_input(): string { return '<input type="hidden" name="csrf_token" value="fixture">'; }
function render_empty_state(string $title, string $message, string $url): string { return htmlspecialchars($title); }
PHP);
    file_put_contents($temp . '/inc/footer.php', '<?php');

    ini_set('session.save_path', $temp);
    session_start(['use_cookies' => false, 'cache_limiter' => '']);
    $_SESSION = $case['session'];
    $_GET = $case['get'] ?? [];
    $_POST = $case['post'] ?? [];
    $_SERVER['REQUEST_METHOD'] = $case['method'] ?? 'GET';
    $db = new ProdutosFixtureDb($case['empty'] ?? false);
    $GLOBALS['fixtureDb'] = $db;
    $result = ['completed' => false, 'error' => null];
    ob_start();
    register_shutdown_function(static function () use (&$result, $db, $temp): void {
        $result['html'] = ob_get_clean();
        $result['queries'] = $db->queries;
        $result['flash_error'] = $_SESSION['flash_error'] ?? null;
        $sessionFile = $temp . '/sess_' . session_id();
        session_write_close();
        // Only the files created by this worker are removed; no recursive delete.
        foreach (['produtos/index.php', 'produtos/functions.php', 'inc/auth.php',
                  'inc/database.php', 'inc/functions.php', 'inc/header.php', 'inc/footer.php'] as $path) {
            unlink($temp . '/' . $path);
        }
        if (is_file($sessionFile)) {
            unlink($sessionFile);
        }
        rmdir($temp . '/inc');
        rmdir($temp . '/produtos');
        rmdir($temp);
        echo json_encode($result, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    });
    set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    try {
        require $temp . '/' . ($case['target'] ?? 'produtos/index.php');
        $result['completed'] = true;
    } catch (Throwable $error) {
        $result['error'] = $error->getMessage();
    }
    exit;
}

if (($argv[1] ?? '') === '--worker') {
    renderWorker(json_decode(base64_decode($argv[2]), true, flags: JSON_THROW_ON_ERROR));
}

function renderCase(array $case): array
{
    $command = [PHP_BINARY, __FILE__, '--worker', base64_encode(json_encode($case, JSON_THROW_ON_ERROR))];
    $pipes = [];
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start PHP worker');
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0 || $stderr !== '') {
        throw new RuntimeException('PHP worker failed: ' . $stderr);
    }
    $result = json_decode($stdout, true, flags: JSON_THROW_ON_ERROR);
    if ($result['error'] !== null) {
        throw new RuntimeException($result['error']);
    }
    return $result;
}

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expectCashierReadOnly(array $result): void
{
    expect($result['completed'], 'Catalog did not render');
    foreach (['modalProduto', 'preco_compra', '8457.13', 'Custo (R$)',
              '/produtos/functions.php', 'Editar Produto', 'Adicionar Produto'] as $forbidden) {
        expect(!str_contains($result['html'], $forbidden), 'Administrative content in response: ' . $forbidden);
    }
    expect(str_contains($result['html'], 'Caneta de teste RBAC'), 'Product missing');
    expect(str_contains($result['html'], 'Teste RBAC'), 'Category missing');
    expect(str_contains($result['html'], '12 un'), 'Stock missing');
    expect(str_contains($result['html'], '19,90'), 'Sale price missing');
    $editQueries = array_filter($result['queries'], static fn(string $sql): bool => (bool)preg_match('/FROM produtos WHERE id\s*=\s*\?/i', $sql));
    expect($editQueries === [], 'Cashier executed edit query');
    foreach ($result['queries'] as $sql) {
        if (preg_match('/FROM produtos p\b/i', $sql) && !str_contains($sql, 'COUNT(*)')) {
            expect(!str_contains($sql, 'p.*') && !str_contains($sql, 'preco_compra'), 'Catalog requested administrative fields');
        }
    }
}

$cashier = ['user_id' => 1, 'user_perfil' => 'caixa'];
$admin = ['user_id' => 2, 'user_perfil' => 'admin'];
$cases = [
    'caixa_catalogo' => static fn() => expectCashierReadOnly(renderCase(['session' => $cashier])),
    'caixa_edit_direto' => static fn() => expectCashierReadOnly(renderCase(['session' => $cashier, 'get' => ['edit' => '37']])),
    'caixa_filtros_paginacao' => static function () use ($cashier): void {
        $result = renderCase(['session' => $cashier, 'get' => ['pagina' => '2', 'categoria_id' => '1', 'busca' => 'Caneta']]);
        expectCashierReadOnly($result);
        expect(str_contains($result['html'], 'pagina=1'), 'Pagination missing');
        expect(str_contains($result['html'], 'categoria_id=1'), 'Filter lost in pagination');
    },
    'caixa_lista_vazia' => static function () use ($cashier): void {
        $html = renderCase(['session' => $cashier, 'empty' => true])['html'];
        expect(str_contains($html, 'Nenhum produto localizado'), 'Empty state missing');
        expect(str_contains($html, 'colspan="6"'), 'Empty state has wrong column count');
        expect(!str_contains($html, 'modalProduto'), 'Administrative modal in empty catalog');
    },
    'perfil_sem_privilegio' => static fn() => expectCashierReadOnly(renderCase(['session' => ['user_id' => 3]])),
    'admin_cadastro' => static function () use ($admin): void {
        $html = renderCase(['session' => $admin])['html'];
        foreach (['id="modalProduto"', 'Adicionar Produto', 'Editar Produto', 'name="preco_compra"', 'name="csrf_token"', 'function clearForm()'] as $required) {
            expect(str_contains($html, $required), 'Admin content missing: ' . $required);
        }
    },
    'admin_edicao' => static function () use ($admin): void {
        $result = renderCase(['session' => $admin, 'get' => ['edit' => '37']]);
        expect(str_contains($result['html'], 'value="8457.13"'), 'Admin cost was not populated');
        expect(str_contains($result['html'], 'value="37"'), 'Admin product ID missing');
        expect(str_contains($result['html'], 'window.onload'), 'Edit modal no longer opens');
    },
    'admin_edicao_inexistente' => static function () use ($admin): void {
        $html = renderCase(['session' => $admin, 'get' => ['edit' => '999']])['html'];
        expect(str_contains($html, 'id="modalProduto"'), 'Admin create modal missing');
        expect(!str_contains($html, 'window.onload'), 'Unknown product opened edit modal');
    },
    'visitante_bloqueado' => static function (): void {
        $result = renderCase(['session' => [], 'get' => ['edit' => '37']]);
        expect(!$result['completed'] && $result['queries'] === [] && $result['html'] === '', 'Unauthenticated request reached catalog');
    },
    'caixa_post_bloqueado' => static function () use ($cashier): void {
        $result = renderCase(['session' => $cashier, 'target' => 'produtos/functions.php',
            'method' => 'POST', 'get' => ['tipo' => 'produto'],
            'post' => ['acao' => 'salvar', 'id' => '37', 'preco_compra' => '1']]);
        expect(!$result['completed'] && $result['queries'] === [] && $result['flash_error'] !== null, 'Cashier reached product write path');
    },
];

$failures = 0;
foreach ($cases as $name => $test) {
    try {
        $test();
        echo "PASS $name\n";
    } catch (Throwable $error) {
        $failures++;
        echo "FAIL $name: {$error->getMessage()}\n";
    }
}
echo sprintf("%d cases; %d passed; %d failed; isolated fixtures, no MySQL\n", count($cases), count($cases) - $failures, $failures);
exit($failures === 0 ? 0 : 1);
