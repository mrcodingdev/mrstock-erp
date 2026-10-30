<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

// In-memory PDO double: executes the real controller without opening MySQL.
final class CarrinhoDb extends PDO
{
    public array $state;
    public array $writes = [];
    public array $locks = [];
    public array $logs = [];
    public bool $committed = false;
    public int $rollbacks = 0;
    private bool $transaction = false;
    private array $snapshot = [];

    public function __construct(public array $fixture)
    {
        $this->state = [
            'products' => [
                1 => ['id' => 1, 'nome' => 'Produto ficticio A', 'preco_venda' => 10.0, 'preco_compra' => 4.0, 'quantidade' => $fixture['stock'] ?? 10, 'status' => $fixture['status'] ?? 'ativo'],
                2 => ['id' => 2, 'nome' => 'Produto ficticio B', 'preco_venda' => 20.0, 'preco_compra' => 8.0, 'quantidade' => 10, 'status' => 'ativo'],
            ],
            'lots' => $fixture['lots'] ?? [], 'sales' => [], 'items' => [], 'movements' => [], 'receipts' => [],
        ];
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new CarrinhoStatement($this, preg_replace('/\s+/', ' ', trim($query)));
    }

    public function beginTransaction(): bool
    {
        $this->snapshot = $this->state;
        return $this->transaction = true;
    }

    public function inTransaction(): bool { return $this->transaction; }

    public function commit(): bool
    {
        $this->committed = true;
        $this->transaction = false;
        return true;
    }

    public function rollBack(): bool
    {
        $this->state = $this->snapshot;
        $this->transaction = false;
        $this->rollbacks++;
        return true;
    }

    public function lastInsertId(?string $name = null): string|false { return '1'; }
}

final class CarrinhoStatement extends PDOStatement
{
    private mixed $value = null;

    public function __construct(private CarrinhoDb $db, private string $sql) {}

    public function execute(?array $params = null): bool
    {
        $params ??= [];
        $sql = $this->sql;
        $db = $this->db;
        if (str_starts_with($sql, 'SELECT')) {
            if (str_contains($sql, 'FROM produtos WHERE')) {
                $db->locks[] = (int)$params[0];
                $this->value = $db->state['products'][(int)$params[0]] ?? false;
            } elseif (str_contains($sql, 'FROM lotes')) {
                $lots = array_values(array_filter($db->state['lots'], static fn(array $lot): bool => $lot['produto_id'] === (int)$params[0]));
                if (!str_contains($sql, 'COUNT(*)')) {
                    $lots = array_values(array_filter($lots, static fn(array $lot): bool => $lot['quantidade'] > 0 && $lot['data_validade'] >= '2026-10-30'));
                }
                usort($lots, static fn(array $a, array $b): int => [$a['data_validade'], $a['id']] <=> [$b['data_validade'], $b['id']]);
                $this->value = str_contains($sql, 'COUNT(*)') ? count($lots)
                    : (str_contains($sql, 'SUM(quantidade)') ? array_sum(array_column($lots, 'quantidade')) : $lots);
            } else {
                throw new RuntimeException('Unsupported fixture SELECT');
            }
            return true;
        }

        $db->writes[] = ['sql' => $sql, 'params' => $params];
        if (str_starts_with($sql, 'INSERT INTO vendas ')) {
            $db->state['sales'][] = ['total' => $params[count($params) - 2], 'payment' => $params[count($params) - 1]];
        } elseif (str_starts_with($sql, 'INSERT INTO vendas_itens ')) {
            if ($db->fixture['fail_item'] ?? false) {
                throw new RuntimeException('simulated_item_failure');
            }
            $db->state['items'][] = ['product' => $params[1], 'quantity' => $params[2], 'price' => $params[3]];
        } elseif (str_starts_with($sql, 'UPDATE produtos ')) {
            $db->state['products'][(int)$params[1]]['quantidade'] -= $params[0];
        } elseif (str_starts_with($sql, 'UPDATE lotes ')) {
            foreach ($db->state['lots'] as &$lot) {
                if ($lot['id'] === (int)$params[1]) { $lot['quantidade'] = $params[0]; }
            }
            unset($lot);
        } elseif (str_starts_with($sql, 'INSERT INTO movimentacoes ')) {
            $db->state['movements'][] = $params;
        } elseif (str_starts_with($sql, 'INSERT INTO cupons_fiscais ')) {
            $db->state['receipts'][] = $params;
        } else {
            throw new RuntimeException('Unsupported fixture write');
        }
        return true;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed { return $this->value; }
    public function fetchColumn(int $column = 0): mixed { return $this->value; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->value; }
}

function controllerWorker(array $case): never
{
    $root = dirname(__DIR__);
    $temp = sys_get_temp_dir() . '/mrstock-carrinho-' . bin2hex(random_bytes(8));
    mkdir($temp . '/inc', 0700, true);
    mkdir($temp . '/vendas', 0700);
    copy($root . '/vendas/functions.php', $temp . '/vendas/functions.php');
    copy($root . '/inc/auth.php', $temp . '/inc/auth.php');
    file_put_contents($temp . '/inc/database.php', '<?php $pdo = $GLOBALS["cartDb"]; define("BASE_URL", "/MrStock");');
    file_put_contents($temp . '/inc/functions.php', <<<'PHP'
<?php
function csrf_verify(): void {}
function get_app_config(PDO $pdo, string $key, string $default): string { return (string)($pdo->fixture['config'][$key] ?? $default); }
function registrar_log(PDO $pdo, string $action, string $message, string $table): void { $pdo->logs[] = $action; }
PHP);
    ini_set('session.save_path', $temp);
    ini_set('error_log', $temp . '/errors.log');
    session_start(['use_cookies' => false, 'cache_limiter' => '']);
    $_SESSION = ['user_id' => 1, 'user_perfil' => 'caixa'];
    $_GET = ['tipo' => 'venda'];
    $_POST = array_merge(['acao' => 'venda_completa', 'cart_data' => json_encode($case['cart'] ?? [], JSON_THROW_ON_ERROR)], $case['post'] ?? []);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $db = new CarrinhoDb($case);
    $GLOBALS['cartDb'] = $db;
    ob_start();
    register_shutdown_function(static function () use ($db, $temp): void {
        $result = ['state' => $db->state, 'committed' => $db->committed, 'rollbacks' => $db->rollbacks,
            'writes' => $db->writes, 'locks' => $db->locks, 'logs' => $db->logs, 'html' => ob_get_clean(),
            'error_log' => is_file($temp . '/errors.log') ? file_get_contents($temp . '/errors.log') : ''];
        $sessionFile = $temp . '/sess_' . session_id();
        session_write_close();
        foreach (['vendas/functions.php', 'inc/auth.php', 'inc/database.php', 'inc/functions.php', 'errors.log'] as $path) {
            if (is_file($temp . '/' . $path)) { unlink($temp . '/' . $path); }
        }
        if (is_file($sessionFile)) { unlink($sessionFile); }
        rmdir($temp . '/inc');
        rmdir($temp . '/vendas');
        rmdir($temp);
        echo json_encode($result, JSON_THROW_ON_ERROR);
    });
    set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    require $temp . '/vendas/functions.php';
    exit;
}

if (($argv[1] ?? '') === '--worker') {
    controllerWorker(json_decode(base64_decode($argv[2]), true, flags: JSON_THROW_ON_ERROR));
}

function sale(array $case): array
{
    $pipes = [];
    $process = proc_open([PHP_BINARY, __FILE__, '--worker', base64_encode(json_encode($case, JSON_THROW_ON_ERROR))],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) { throw new RuntimeException('Unable to start worker'); }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0 || $stderr !== '') { throw new RuntimeException('Worker failed: ' . $stderr); }
    return json_decode($stdout, true, flags: JSON_THROW_ON_ERROR);
}

function check(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}

function rejected(array $result): void
{
    check(!$result['committed'] && $result['rollbacks'] === 1, 'Invalid sale was committed or not rolled back');
    check($result['writes'] === [], 'Invalid cart reached writes');
    check($result['error_log'] === '', 'Unexpected processing exception');
}

$duplicates = [['id' => 1, 'quantidade' => 6], ['id' => '1', 'quantidade' => 6]];
$lots = [
    ['id' => 3, 'produto_id' => 1, 'quantidade' => 50, 'data_validade' => '2020-01-01'],
    ['id' => 2, 'produto_id' => 1, 'quantidade' => 7, 'data_validade' => '2027-02-01'],
    ['id' => 1, 'produto_id' => 1, 'quantidade' => 3, 'data_validade' => '2027-01-01'],
];
$tests = [
    'duplicados_excedem_estoque' => static function () use ($duplicates): void {
        $r = sale(['cart' => $duplicates]);
        rejected($r);
        check($r['state']['products'][1]['quantidade'] === 10, 'Stock changed on rejection');
    },
    'duplicados_excedem_lotes_validos' => static function () use ($duplicates, $lots): void {
        $r = sale(['cart' => $duplicates, 'stock' => 20, 'lots' => $lots]);
        rejected($r);
        check(in_array('BLOQUEIO_SANITARIO', $r['logs'], true), 'Valid-lot shortage not detected');
        check($r['state']['lots'] === $lots, 'Lots changed on rejection');
    },
    'soma_nao_pode_estourar_inteiro' => static function (): void {
        rejected(sale(['stock' => PHP_INT_MAX, 'cart' => [['id' => 1, 'quantidade' => PHP_INT_MAX], ['id' => 1, 'quantidade' => 1]]]));
    },
    'duplicados_no_limite' => static function (): void {
        $r = sale(['cart' => [['id' => 1, 'quantidade' => 4], ['id' => 1, 'quantidade' => 6]]]);
        check($r['committed'] && $r['state']['products'][1]['quantidade'] === 0, 'Exact stock balance failed');
        check(count($r['state']['items']) === 2 && count($r['state']['movements']) === 2, 'Sale lines changed');
    },
    'preco_oficial_e_desconto_preservados' => static function (): void {
        $r = sale(['cart' => [['id' => 1, 'quantidade' => 2, 'preco' => 0.01], ['id' => 1, 'quantidade' => 3, 'preco' => 0.01]], 'post' => ['desconto' => '2']]);
        check($r['committed'] && (float)$r['state']['sales'][0]['total'] === 48.0, 'Official total or discount changed');
        check($r['state']['products'][1]['quantidade'] === 5, 'Wrong quantity debited');
    },
    'consumo_fefo_preservado' => static function () use ($lots): void {
        $r = sale(['lots' => $lots, 'cart' => [['id' => 1, 'quantidade' => 2], ['id' => 1, 'quantidade' => 3]]]);
        $balances = array_column($r['state']['lots'], 'quantidade', 'id');
        check($r['committed'] && $balances === [3 => 50, 2 => 5, 1 => 0], 'Valid lots/FEFO changed');
    },
    'ordem_de_locks_preservada' => static function (): void {
        $r = sale(['cart' => [['id' => 2, 'quantidade' => 1], ['id' => 1, 'quantidade' => 2], ['id' => 2, 'quantidade' => 2]]]);
        check($r['committed'] && $r['locks'] === [1, 2, 2], 'Product lock order changed');
        check($r['state']['products'][1]['quantidade'] === 8 && $r['state']['products'][2]['quantidade'] === 7, 'Cross-product quantities mixed');
    },
    'quantidade_invalida' => static fn() => rejected(sale(['cart' => [['id' => 1, 'quantidade' => 2], ['id' => 1, 'quantidade' => -1]]])),
    'produto_inexistente' => static fn() => rejected(sale(['cart' => [['id' => 99, 'quantidade' => 1]]])),
    'produto_inativo' => static fn() => rejected(sale(['status' => 'inativo', 'cart' => [['id' => 1, 'quantidade' => 1]]])),
    'carrinho_vazio' => static fn() => rejected(sale(['cart' => []])),
    'desconto_excessivo' => static fn() => rejected(sale(['cart' => [['id' => 1, 'quantidade' => 1]], 'post' => ['desconto' => 9]])),
    'margem_bloqueada' => static fn() => rejected(sale(['cart' => [['id' => 1, 'quantidade' => 1]], 'post' => ['desconto' => 7], 'config' => ['pdv_desconto_maximo' => '100', 'pdv_trava_margem' => 'bloquear']])),
    'rollback_em_falha_de_gravacao_simulada' => static function (): void {
        $r = sale(['fail_item' => true, 'cart' => [['id' => 1, 'quantidade' => 1]]]);
        check(!$r['committed'] && $r['rollbacks'] === 1 && $r['state']['sales'] === [] && $r['state']['products'][1]['quantidade'] === 10, 'Rollback did not restore state');
        check(str_contains($r['error_log'], 'simulated_item_failure'), 'Expected failure was not processed');
    },
    'venda_rapida_preservada' => static function (): void {
        $r = sale(['post' => ['acao' => 'venda_rapida', 'produto_id' => 1, 'quantidade' => 2]]);
        check($r['committed'] && $r['state']['products'][1]['quantidade'] === 8 && (float)$r['state']['sales'][0]['total'] === 20.0, 'Quick sale changed');
    },
];
$failures = 0;
foreach ($tests as $name => $test) {
    try { $test(); echo "PASS $name\n"; }
    catch (Throwable $error) { $failures++; echo "FAIL $name: {$error->getMessage()}\n"; }
}
echo sprintf("%d cases; %d passed; %d failed; PDO double, no MySQL\n", count($tests), count($tests) - $failures, $failures);
exit($failures === 0 ? 0 : 1);
