<?php
/**
 * Exportador de Dump SQL Oficial em UTF-8 Puro (Sem BOM) para MrStock ERP
 */

$host = '127.0.0.1';
$db   = 'mrstock_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    // Se o MySQL estiver desligado, tenta iniciar o serviço localmente
    exec('start /B C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone');
    sleep(3);
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

$output = "-- ============================================================================\n";
$output .= "-- MrStock ERP — Database Dump Oficial (UTF-8 sem BOM)\n";
$output .= "-- Gerado automaticamente com suporte a Plug-and-Play e integridade referencial\n";
$output .= "-- Host: 127.0.0.1    Database: mrstock_db\n";
$output .= "-- ============================================================================\n\n";

$output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
$output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
$output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
$output .= "/*!40101 SET NAMES utf8mb4 */;\n";
$output .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n";
$output .= "/*!40103 SET TIME_ZONE='+00:00' */;\n";
$output .= "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n";
$output .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n";
$output .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
$output .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n\n";

// Inserção da criação da base e seleção
$output .= "CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mrstock_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;\n";
$output .= "USE `mrstock_db`;\n\n";

// Ordem correta das tabelas respeitando dependências FK
$tables = [
    'usuarios',
    'categorias',
    'fornecedores',
    'clientes',
    'produtos',
    'lotes',
    'movimentacoes',
    'compras',
    'itens_compra',
    'vendas',
    'vendas_itens',
    'cupons_fiscais',
    'logs'
];

foreach ($tables as $table) {
    // Verifica se tabela existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    if (!$stmt->fetch()) continue;

    $output .= "--\n-- Estrutura da tabela `{$table}`\n--\n\n";
    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $output .= "/*!40101 SET @saved_cs_client     = @@character_set_client */;\n";
    $output .= "/*!40101 SET character_set_client = utf8mb4 */;\n";

    $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
    $createSql = $createTableStmt[1];
    $output .= $createSql . ";\n";
    $output .= "/*!40101 SET character_set_client = @saved_cs_client */;\n\n";

    // Dados da tabela
    $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $output .= "--\n-- Despejando dados para a tabela `{$table}`\n--\n\n";
        $output .= "LOCK TABLES `{$table}` WRITE;\n";
        $output .= "/*!40000 ALTER TABLE `{$table}` DISABLE KEYS */;\n";

        $cols = array_keys($rows[0]);
        $colNames = implode('`, `', $cols);

        $valuesArr = [];
        foreach ($rows as $row) {
            $escapedValues = [];
            foreach ($row as $val) {
                if (is_null($val)) {
                    $escapedValues[] = 'NULL';
                } else {
                    $escapedValues[] = $pdo->quote($val);
                }
            }
            $valuesArr[] = "(" . implode(", ", $escapedValues) . ")";
        }

        $output .= "INSERT INTO `{$table}` (`{$colNames}`) VALUES \n" . implode(",\n", $valuesArr) . ";\n";
        $output .= "/*!40000 ALTER TABLE `{$table}` ENABLE KEYS */;\n";
        $output .= "UNLOCK TABLES;\n\n";
    }
}

$output .= "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n";
$output .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n";
$output .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n";
$output .= "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n";
$output .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
$output .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
$output .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
$output .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n\n";
$output .= "-- Dump concluído em " . date('Y-m-d H:i:s') . "\n";

$targetFile = 'C:/xampp/htdocs/MrStockBackup/database/mrstock_db.sql';
file_put_contents($targetFile, $output);

echo "✅ Dump gerado com sucesso em UTF-8 puro (sem BOM)!\n";
echo "Tamanho final: " . strlen($output) . " bytes\n";
echo "Arquivo: " . $targetFile . "\n";
