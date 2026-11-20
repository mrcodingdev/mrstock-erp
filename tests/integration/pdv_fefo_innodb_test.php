<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit(2);
}

try {
    runFefoStage($argv);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(2);
}

function runFefoStage(array $args): void
{
    $stage = $args[1] ?? '';
    $port = filter_var($args[2] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1024, 'max_range' => 65535]]);
    $schema = $args[3] ?? '';
    $password = getenv('PDV_FEFO_TEST_PASSWORD');
    $expectedDatadir = realpath(__DIR__ . '/data');
    if (!in_array($stage, ['seed', 'sale', 'assert'], true)
        || $port === false || !in_array($schema, ['fefo_base', 'fefo_validade', 'fefo_empate'], true)
        || $password === false || $expectedDatadir === false
    ) {
        throw new RuntimeException('Execute o piloto com run_pdv_fefo.py.');
    }

    $pdo = new PDO("mysql:host=127.0.0.1;port=$port;dbname=$schema;charset=utf8mb4", 'root', $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $identity = $pdo->query('SELECT @@datadir AS directory, @@port AS port, DATABASE() AS db')->fetch();
    if (realpath($identity['directory']) !== $expectedDatadir || (int)$identity['port'] !== $port
        || $identity['db'] !== $schema
    ) {
        throw new RuntimeException('A identidade do banco diverge da instância descartável.');
    }

    if ($stage === 'seed') {
        seedFefoFixture($pdo);
        return;
    }

    if ($stage === 'sale') {
        session_save_path(__DIR__);
        session_start(['use_cookies' => false, 'cache_limiter' => '']);
        $_SESSION = ['user_id' => 1, 'user_perfil' => 'caixa', 'csrf_token' => bin2hex(random_bytes(32))];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET = ['tipo' => 'venda'];
        $_POST = [
            'acao' => 'venda_completa',
            'csrf_token' => $_SESSION['csrf_token'],
            'forma_pagamento' => 'DINHEIRO',
            'cart_data' => json_encode([['id' => 1, 'quantidade' => 3]], JSON_THROW_ON_ERROR),
        ];
        define('BASE_URL', '/MrStock');
        // O controlador chama exit após concluir o POST. A asserção roda em outro processo.
        require __DIR__ . '/app/vendas/functions.php';
        return;
    }

    assertFefoResult($pdo);
}

function seedFefoFixture(PDO $pdo): void
{
    $engines = $pdo->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')
        ->fetchAll(PDO::FETCH_COLUMN);
    if (count($engines) !== 15 || array_unique($engines) !== ['InnoDB']) {
        throw new RuntimeException('As 15 tabelas da fixture precisam usar InnoDB.');
    }
    $pdo->exec("INSERT INTO usuarios (id, username, password, perfil) VALUES (1, 'caixa_fixture', 'sem-login', 'caixa')");
    $pdo->exec("INSERT INTO produtos (id, nome, quantidade, preco_venda, preco_compra) VALUES (1, 'Produto fictício', 11, 10, 5)");
    $pdo->exec(<<<'SQL'
        INSERT INTO lotes (id, produto_id, numero_lote, data_validade, quantidade)
        VALUES
            (10, 1, 'L10', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 5),
            (90, 1, 'L90', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2),
            (91, 1, 'L91', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 4)
        SQL);
}

function assertFefoResult(PDO $pdo): void
{
    $sales = $pdo->query('SELECT id, total, forma_pagamento FROM vendas')->fetchAll();
    $items = $pdo->query('SELECT venda_id, produto_id, quantidade FROM vendas_itens')->fetchAll();
    $movements = $pdo->query('SELECT produto_id, tipo, quantidade FROM movimentacoes')->fetchAll();
    $stock = (int)$pdo->query('SELECT quantidade FROM produtos WHERE id = 1')->fetchColumn();
    if (count($sales) !== 1 || (float)$sales[0]['total'] !== 30.0 || $sales[0]['forma_pagamento'] !== 'DINHEIRO'
        || $items !== [['venda_id' => $sales[0]['id'], 'produto_id' => 1, 'quantidade' => 3]]
        || $movements !== [['produto_id' => 1, 'tipo' => 'saida_venda', 'quantidade' => 3]]
        || $stock !== 8
    ) {
        throw new RuntimeException('Divergência na venda, item, Kardex ou saldo mestre. O mutante precisa atingir a asserção de lotes.');
    }

    $expected = [10 => 5, 90 => 0, 91 => 3];
    $actual = $pdo->query('SELECT id, quantidade FROM lotes WHERE produto_id = 1 ORDER BY id')
        ->fetchAll(PDO::FETCH_KEY_PAIR);
    $matches = $actual === $expected;
    echo json_encode([
        'rule' => 'saldo_por_lote',
        'expected' => $expected,
        'actual' => $actual,
        'master_stock' => $stock,
        'sale_total' => (float)$sales[0]['total'],
        'item_quantity' => $items[0]['quantidade'],
        'kardex_quantity' => $movements[0]['quantidade'],
    ], JSON_THROW_ON_ERROR), PHP_EOL;
    exit($matches ? 0 : 1);
}
