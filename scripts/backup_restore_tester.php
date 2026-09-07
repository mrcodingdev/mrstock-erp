<?php
/**
 * MrStock ERP - Validador de Restauração de Backups (CLI)
 * Executa importação isolada em ambiente sandbox temporário para testar
 * integridade física, lógica, referencial e credenciais administrativas.
 *
 * Uso: php scripts/backup_restore_tester.php
 */

declare(strict_types=1);

// Garante execução exclusiva em interface CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Acesso negado. Este script deve ser executado exclusivamente via linha de comando (CLI).\n");
}

// 1. Carrega configurações do projeto com fallbacks defensivos
$configPath = dirname(__DIR__) . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: 'localhost');
$user = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'root');
$pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
$port = defined('DB_PORT') ? (int)DB_PORT : 3306;

$testDb = 'mrstock_restore_test';

// Paleta ANSI para terminal
$cGreen  = "\033[1;32m";
$cRed    = "\033[1;31m";
$cYellow = "\033[1;33m";
$cCyan   = "\033[1;36m";
$cBold   = "\033[1m";
$cReset  = "\033[0m";

echo "\n" . str_repeat('=', 70) . "\n";
echo "{$cCyan}{$cBold}  MrStock ERP - Validador Automatizado de Restauração de Backup{$cReset}\n";
echo "  Ambiente de Validação em Sandbox Isolado\n";
echo str_repeat('=', 70) . "\n\n";

// ====================================================================
// ETAPA 1: LOCALIZAÇÃO DO DUMP SQL MAIS RECENTE
// ====================================================================
$searchDirs = [
    dirname(__DIR__) . '/database',
    dirname(__DIR__) . '/03_Backups_e_Releases/database_dumps',
    'G:/Meu Drive/TCC_MrStock/03_Backups_e_Releases/database_dumps',
    'G:/Meu Drive/TCC_MrStock/database'
];

$sqlFiles = [];
foreach ($searchDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*.sql');
        if ($files) {
            foreach ($files as $f) {
                if (is_file($f) && filesize($f) > 100) {
                    $sqlFiles[$f] = filemtime($f);
                }
            }
        }
    }
}

if (empty($sqlFiles)) {
    echo "{$cRed}[ FALHA ] Nenhum arquivo de dump SQL encontrado nos diretórios de backup.{$cReset}\n";
    exit(1);
}

// Ordena pelo arquivo mais recente
arsort($sqlFiles);
$selectedDump = array_key_first($sqlFiles);
$dumpSizeKb   = number_format(filesize($selectedDump) / 1024, 2, ',', '.');
$dumpDate     = date('d/m/Y H:i:s', $sqlFiles[$selectedDump]);

echo "{$cCyan}[INFO]{$cReset} Dump SQL selecionado:\n";
echo "  - Arquivo:   {$cBold}{$selectedDump}{$cReset}\n";
echo "  - Tamanho:   {$dumpSizeKb} KB\n";
echo "  - Alteração: {$dumpDate}\n\n";

// ====================================================================
// ETAPA 2: CONEXÃO COM O MYSQL E CRIAÇÃO DO BANCO SANDBOX
// ====================================================================
try {
    $pdoServer = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo "{$cRed}[ FALHA ] Conexão com o servidor MySQL falhou: {$e->getMessage()}{$cReset}\n";
    exit(1);
}

echo "{$cCyan}[INFO]{$cReset} Criando banco de dados sandbox '{$testDb}'...\n";
try {
    $pdoServer->exec("DROP DATABASE IF EXISTS `{$testDb}`;");
    $pdoServer->exec("CREATE DATABASE `{$testDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
} catch (PDOException $e) {
    echo "{$cRed}[ FALHA ] Falha ao criar banco sandbox '{$testDb}': {$e->getMessage()}{$cReset}\n";
    exit(1);
}

// Conecta especificamente ao banco de teste sandbox
try {
    $pdoTest = new PDO("mysql:host={$host};port={$port};dbname={$testDb};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS   => true,
    ]);
} catch (PDOException $e) {
    echo "{$cRed}[ FALHA ] Falha ao conectar ao banco sandbox: {$e->getMessage()}{$cReset}\n";
    exit(1);
}

// ====================================================================
// ETAPA 3: IMPORTAÇÃO DO DUMP COM TRATAMENTO DEFENSIVO DE ESCOPO
// ====================================================================
echo "{$cCyan}[INFO]{$cReset} Importando dados do dump SQL para '{$testDb}'...\n";

$sqlContent = file_get_contents($selectedDump);
if ($sqlContent === false) {
    echo "{$cRed}[ FALHA ] Não foi possível ler o conteúdo do arquivo de dump.{$cReset}\n";
    exit(1);
}

// Tratamento de integridade:
// Remove declarações de 'CREATE DATABASE' e redireciona 'USE' para o banco sandbox
$sqlClean = preg_replace('/CREATE\s+DATABASE\s+[^;]+;/i', '', $sqlContent);
$sqlClean = preg_replace('/USE\s+`?[a-zA-Z0-9_-]+`?;/i', "USE `{$testDb}`;", $sqlClean);

// Assegura desativação de checagem de FKs durante importação
$importPayload = "SET FOREIGN_KEY_CHECKS = 0;\n"
               . "USE `{$testDb}`;\n"
               . $sqlClean . "\n"
               . "SET FOREIGN_KEY_CHECKS = 1;\n";

try {
    $pdoTest->exec($importPayload);
    // Limpa quaisquer resultados pendentes da execução multi-statement
    while ($pdoTest->query("SELECT 1")->nextRowset()) {}
    echo "{$cGreen}[ OK ]{$cReset} Estrutura e dados importados com sucesso.\n\n";
} catch (Throwable $e) {
    echo "{$cRed}[ FALHA ] Erro durante a importação SQL: {$e->getMessage()}{$cReset}\n";
    $pdoServer->exec("DROP DATABASE IF EXISTS `{$testDb}`;");
    exit(1);
}

// ====================================================================
// BATERIA DE 5 TESTES DE INTEGRIDADE E RESTAURAÇÃO
// ====================================================================
$testesPassados = 0;
$totalTestes    = 5;

echo "{$cBold}Iniciando Bateria de Testes de Conformidade:{$cReset}\n";
echo str_repeat('-', 70) . "\n";

// --- TESTE 1: Existência das 12 tabelas vitais ---
$vitalTables = [
    'categorias',
    'clientes',
    'compras',
    'cupons_fiscais',
    'fornecedores',
    'lotes',
    'movimentacoes',
    'produtos',
    'usuarios',
    'vendas',
    'vendas_itens',
    'configuracoes'
];

$stmt = $pdoTest->query("
    SELECT TABLE_NAME 
    FROM information_schema.tables 
    WHERE table_schema = '{$testDb}' AND table_type = 'BASE TABLE'
");
$tablesFound = $stmt->fetchAll(PDO::FETCH_COLUMN);

$missingTables = array_diff($vitalTables, $tablesFound);
if (empty($missingTables)) {
    echo "{$cGreen}[ PASS ]{$cReset} Teste 1: Existência das 12 tabelas vitais confirmada (" . count($vitalTables) . "/12 presentes).\n";
    $testesPassados++;
} else {
    echo "{$cRed}[ FAIL ]{$cReset} Teste 1: Tabelas vitais ausentes: " . implode(', ', $missingTables) . "\n";
}

// --- TESTE 2: Contagem de registros (verificação de base povoada) ---
$totalRegistros = 0;
$detalhesContagem = [];

foreach ($vitalTables as $table) {
    if (in_array($table, $tablesFound, true)) {
        $count = (int)$pdoTest->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        $totalRegistros += $count;
        $detalhesContagem[$table] = $count;
    }
}

if ($totalRegistros > 0 && ($detalhesContagem['usuarios'] ?? 0) > 0 && ($detalhesContagem['produtos'] ?? 0) > 0) {
    echo "{$cGreen}[ PASS ]{$cReset} Teste 2: Base com dados reais comprovados ({$totalRegistros} registros totais computados).\n";
    $testesPassados++;
} else {
    echo "{$cRed}[ FAIL ]{$cReset} Teste 2: Base de dados vazia ou sem registros essenciais (Total: {$totalRegistros}).\n";
}

// --- TESTE 3: Integridade referencial de chaves estrangeiras ---
$fkStmt = $pdoTest->query("
    SELECT 
        TABLE_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = '{$testDb}' 
      AND REFERENCED_TABLE_NAME IS NOT NULL
");
$foreignKeys = $fkStmt->fetchAll();

$fkOrphans = 0;
$fkOrphanDetails = [];

foreach ($foreignKeys as $fk) {
    $tabelaOrigem  = $fk['TABLE_NAME'];
    $colunaOrigem  = $fk['COLUMN_NAME'];
    $tabelaDestino = $fk['REFERENCED_TABLE_NAME'];
    $colunaDestino = $fk['REFERENCED_COLUMN_NAME'];

    $checkSql = "
        SELECT COUNT(*) 
        FROM `{$tabelaOrigem}` o
        WHERE o.`{$colunaOrigem}` IS NOT NULL
          AND NOT EXISTS (
              SELECT 1 FROM `{$tabelaDestino}` d 
              WHERE d.`{$colunaDestino}` = o.`{$colunaOrigem}`
          )
    ";
    try {
        $orphans = (int)$pdoTest->query($checkSql)->fetchColumn();
        if ($orphans > 0) {
            $fkOrphans += $orphans;
            $fkOrphanDetails[] = "{$tabelaOrigem}.{$colunaOrigem} -> {$tabelaDestino}.{$colunaDestino} ({$orphans} órfãos)";
        }
    } catch (Throwable $e) {
        // Se a query falhar, considera inconsistência
        $fkOrphans++;
        $fkOrphanDetails[] = "Erro ao checar {$tabelaOrigem}: " . $e->getMessage();
    }
}

if ($fkOrphans === 0) {
    echo "{$cGreen}[ PASS ]{$cReset} Teste 3: Integridade referencial de Foreign Keys 100% íntegra (" . count($foreignKeys) . " restrições avaliadas).\n";
    $testesPassados++;
} else {
    echo "{$cRed}[ FAIL ]{$cReset} Teste 3: Detectados {$fkOrphans} registros órfãos violando integridade: " . implode('; ', $fkOrphanDetails) . "\n";
}

// --- TESTE 4: Existência de pelo menos 1 usuário Administrador com senha BCrypt ---
$stmtAdmin = $pdoTest->query("
    SELECT id, username, password, perfil 
    FROM usuarios 
    WHERE perfil = 'admin'
");
$admins = $stmtAdmin->fetchAll();

$adminValido = false;
$adminNome   = '';

foreach ($admins as $admin) {
    $hash = $admin['password'];
    // Valida se o hash é padrão BCrypt ($2y$, $2a$, $2b$ de 60 caracteres)
    $info = password_get_info($hash);
    $isBcrypt = ($info['algo'] === PASSWORD_BCRYPT) 
             || (preg_match('/^\$2[ayb]\$\d{2}\$[A-Za-z0-9\.\/]{53}$/', $hash) === 1);

    if ($isBcrypt) {
        $adminValido = true;
        $adminNome = $admin['username'];
        break;
    }
}

if ($adminValido) {
    echo "{$cGreen}[ PASS ]{$cReset} Teste 4: Administrador ativo com hash BCrypt identificado (Usuário: '{$adminNome}').\n";
    $testesPassados++;
} else {
    echo "{$cRed}[ FAIL ]{$cReset} Teste 4: Nenhum usuário administrador com hash seguro BCrypt encontrado.\n";
}

// --- TESTE 5: Remoção limpa do banco temporário ---
unset($pdoTest); // Fecha conexão com o banco sandbox para permitir drop
try {
    $pdoServer->exec("DROP DATABASE IF EXISTS `{$testDb}`;");
    
    // Verifica se o banco realmente foi removido
    $stmtCheck = $pdoServer->query("
        SELECT COUNT(*) 
        FROM information_schema.schemata 
        WHERE schema_name = '{$testDb}'
    ");
    $dbStillExists = (int)$stmtCheck->fetchColumn();

    if ($dbStillExists === 0) {
        echo "{$cGreen}[ PASS ]{$cReset} Teste 5: Remoção limpa do banco temporário '{$testDb}' confirmada.\n";
        $testesPassados++;
    } else {
        echo "{$cRed}[ FAIL ]{$cReset} Teste 5: Banco temporário ainda presente após comando DROP.\n";
    }
} catch (Throwable $e) {
    echo "{$cRed}[ FAIL ]{$cReset} Teste 5: Erro ao remover banco temporário: {$e->getMessage()}\n";
}

echo str_repeat('-', 70) . "\n";

// ====================================================================
// RELATÓRIO FINAL
// ====================================================================
if ($testesPassados === $totalTestes) {
    echo "\n{$cGreen}{$cBold}[ 🟢 PASS ] Teste de Restauração 100% Validado{$cReset}\n";
    echo "Todos os 5 testes de integridade foram aprovados com êxito.\n";
    echo "O backup é totalmente apto para restauração segura em desastre.\n\n";
    exit(0);
} else {
    echo "\n{$cRed}{$cBold}[ ❌ FAIL ] Falha na validação de restauração ({$testesPassados}/{$totalTestes} testes aprovados){$cReset}\n";
    echo "Verifique os erros acima antes de homologar os dumps de backup.\n\n";
    exit(1);
}
