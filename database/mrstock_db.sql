-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mrstock_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `mrstock_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mrstock_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `mrstock_db`;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Cadernos & Blocos','Cadernos universitários, brochuras, blocos de anotações e post-it'),(2,'Canetas & Marcadores','Canetas esferográficas, ponta fina, marca-texto e corretores'),(3,'Lápis & Apontadores','Lápis grafite, lápis de cor e apontadores com depósito'),(4,'Borrachas & Correção','Borrachas escolares, ponteiras e fitas corretivas'),(5,'Colas & Fitas Adesivas','Cola bastão, cola líquida, fita crepe e fita transparente'),(6,'Papéis & Folhas','Resmas sulfite A4, cartolinas, papel cartão e color set'),(7,'Pastas & Organização','Pastas aba elástico, pastas suspensas e organizadores'),(8,'Corte & Medição','Tesouras escolares, estiletes, réguas e esquadros'),(9,'Tintas & Pintura','Tintas guache, aquarela, tintas para tecido e pincéis'),(10,'Grampeadores & Fixação','Grampeadores de mesa, caixas de grampos e clips');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `cpf_cnpj` varchar(18) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'João Silva','joao@email.com','(11) 98765-4321','ativo','2026-06-03 21:36:30',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'Maria Oliveira','maria@email.com','(11) 91234-5678','ativo','2026-06-03 21:36:30',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'Carlos Souza','carlos@email.com','(11) 99999-8888','ativo','2026-06-03 21:36:30','111.111.111-11','','','','','','11111-11');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fornecedor_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `numero_nota` varchar(50) DEFAULT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `tipo_pagamento` varchar(50) DEFAULT NULL,
  `status` enum('PENDENTE','PAGA','CANCELADA') DEFAULT 'PENDENTE',
  `data_compra` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fornecedor_id` (`fornecedor_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (1,3,1,'',10.50,'pix','PAGA','2026-06-06 13:56:17'),(2,1,1,'',10.50,'pix','PAGA','2026-06-06 13:58:06');
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracoes` (
  `chave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracoes`
--

LOCK TABLES `configuracoes` WRITE;
/*!40000 ALTER TABLE `configuracoes` DISABLE KEYS */;
INSERT INTO `configuracoes` VALUES ('alerta_estoque','1','2026-08-22 02:03:57'),('alerta_vencimento','1','2026-08-22 02:03:57'),('empresa_cnpj','50.334.808/0001-38','2026-08-22 02:03:57'),('empresa_endereco','Rua XV de Novembro, 250 - Centro, Sorocaba/SP','2026-08-22 02:03:57'),('empresa_nome','Papelaria Real (Sueli & Osnir)','2026-08-22 02:03:57'),('empresa_razao','Papelaria Real Ltda - ME','2026-08-22 02:03:57'),('empresa_telefone','(15) 3232-0000','2026-08-22 02:03:57'),('empresa_whatsapp','(15) 99123-4567','2026-08-22 02:03:57'),('estoque_minimo_global','5','2026-08-22 02:03:57'),('pdv_impressora','80mm','2026-08-22 02:03:57'),('som_pdv','1','2026-08-22 02:03:57');
/*!40000 ALTER TABLE `configuracoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cupons_fiscais`
--

DROP TABLE IF EXISTS `cupons_fiscais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cupons_fiscais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `chave_acesso` varchar(44) NOT NULL,
  `data_emissao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave_acesso` (`chave_acesso`),
  KEY `venda_id` (`venda_id`),
  CONSTRAINT `cupons_fiscais_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cupons_fiscais`
--

LOCK TABLES `cupons_fiscais` WRITE;
/*!40000 ALTER TABLE `cupons_fiscais` DISABLE KEYS */;
INSERT INTO `cupons_fiscais` VALUES (1,1,'35260304148243000116650010000000011000000015','2026-06-03 21:36:30'),(2,2,'35260304148243000116650010000000021000000021','2026-06-03 21:36:30'),(3,3,'35260300000000000000650010000000031000000009','2026-06-06 13:57:21'),(4,4,'35260300000000000000650010000000041000000008','2026-08-14 20:33:07'),(5,5,'35260300000000000000650010000000051000000006','2026-08-14 23:16:24'),(6,6,'35260000000000000000000000000000000000000006','2026-08-15 04:57:55'),(7,7,'35260000000000000000000000000000000000000007','2026-08-14 04:57:55'),(8,8,'35260000000000000000000000000000000000000008','2026-08-13 04:57:55'),(9,9,'35260000000000000000000000000000000000000009','2026-08-12 04:57:55'),(10,10,'35260000000000000000000000000000000000000010','2026-08-11 04:57:55'),(11,11,'35260000000000000000000000000000000000000011','2026-08-10 04:57:55'),(12,12,'35260000000000000000000000000000000000000012','2026-08-09 04:57:55'),(13,13,'35260000000000000000000000000000000000000013','2026-07-31 04:57:55'),(14,14,'35260000000000000000000000000000000000000014','2026-07-24 04:57:55'),(15,15,'35260000000000000000000000000000000000000015','2026-07-01 04:57:55'),(16,16,'35260300000000000000650010000000161000000008','2026-08-15 18:26:41'),(17,17,'35260300000000000000650010000000171000000008','2026-08-15 19:53:26'),(18,18,'35260300000000000000650010000000181000000009','2026-08-15 19:53:52'),(19,19,'35260300000000000000650010000000191000000001','2026-08-21 20:43:47');
/*!40000 ALTER TABLE `cupons_fiscais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fornecedores`
--

DROP TABLE IF EXISTS `fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `contato` varchar(100) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedores`
--

LOCK TABLES `fornecedores` WRITE;
/*!40000 ALTER TABLE `fornecedores` DISABLE KEYS */;
INSERT INTO `fornecedores` VALUES (1,'Tilibra S.A','44.990.901/0001-43','(14) 3235-4000','vendas@tilibra.com.br','ativo',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'Bic Brasil','04.148.243/0001-16','(11) 2118-8000','comercial@bic.com','ativo',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'Acrilex','50.334.808/0001-38','(11) 4344-8800','contato@acrilex.com','ativo','a','','','','','','');
/*!40000 ALTER TABLE `fornecedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itens_compra`
--

DROP TABLE IF EXISTS `itens_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itens_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compra_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_id` (`compra_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `itens_compra_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itens_compra_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itens_compra`
--

LOCK TABLES `itens_compra` WRITE;
/*!40000 ALTER TABLE `itens_compra` DISABLE KEYS */;
INSERT INTO `itens_compra` VALUES (1,1,1,1.000,10.50,10.50),(2,2,1,1.000,10.50,10.50);
/*!40000 ALTER TABLE `itens_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tabela_afetada` varchar(100) DEFAULT NULL,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `data_log` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_logs_data_log` (`data_log`),
  KEY `idx_logs_acao` (`acao`),
  KEY `idx_logs_tabela` (`tabela_afetada`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes`
--

DROP TABLE IF EXISTS `lotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `numero_lote` varchar(50) NOT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `data_validade` date NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0,
  `preco_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fornecedor_id` int(11) DEFAULT NULL,
  `data_entrada` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  KEY `fornecedor_id` (`fornecedor_id`),
  CONSTRAINT `lotes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lotes_ibfk_2` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes`
--

LOCK TABLES `lotes` WRITE;
/*!40000 ALTER TABLE `lotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `lotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimentacoes`
--

DROP TABLE IF EXISTS `movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada_compra','saida_venda','devolucao_cliente','devolucao_fornecedor','perda') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_movimento` datetime DEFAULT current_timestamp(),
  `observacao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  KEY `idx_movimentacoes_data` (`data_movimento`),
  CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimentacoes`
--

LOCK TABLES `movimentacoes` WRITE;
/*!40000 ALTER TABLE `movimentacoes` DISABLE KEYS */;
INSERT INTO `movimentacoes` VALUES (1,1,'entrada_compra',120,'2026-06-03 21:36:30','Estoque inicial - Compra NFe 1001'),(2,2,'entrada_compra',10,'2026-06-03 21:36:30','Compra NFe 1002'),(3,3,'entrada_compra',20,'2026-06-03 21:36:30','Compra NFe 1003'),(4,4,'entrada_compra',80,'2026-06-03 21:36:30','Compra NFe 1004'),(5,2,'saida_venda',5,'2026-06-03 21:36:30','Vendas na loja'),(6,3,'saida_venda',2,'2026-06-03 21:36:30','Venda online'),(7,3,'entrada_compra',163,'2026-06-03 21:39:25','Ajuste Manual via Cadastro'),(8,1,'entrada_compra',1,'2026-06-06 13:56:17','Entrada de Compra #1 - Nota: S/N'),(9,1,'saida_venda',1,'2026-06-06 13:57:21','Venda PDV #3'),(10,1,'entrada_compra',1,'2026-06-06 13:58:06','Entrada de Compra #2 - Nota: S/N'),(11,4,'saida_venda',1,'2026-08-14 20:33:07','Venda PDV #4'),(12,4,'entrada_compra',0,'2026-08-14 20:33:47','Ajuste Manual via Cadastro'),(13,4,'entrada_compra',0,'2026-08-14 20:33:52','Ajuste Manual via Cadastro'),(14,4,'entrada_compra',1,'2026-08-14 20:34:09','Ajuste Manual via Cadastro'),(15,4,'entrada_compra',1,'2026-08-14 20:34:19','Ajuste Manual via Cadastro'),(16,4,'entrada_compra',0,'2026-08-14 20:34:24','Ajuste Manual via Cadastro'),(17,4,'entrada_compra',1,'2026-08-14 20:54:09','Ajuste Manual via Cadastro'),(18,2,'saida_venda',5,'2026-08-14 23:16:24','Venda PDV #5'),(19,5,'saida_venda',2,'2026-08-15 04:57:55','Venda PDV #6'),(20,6,'saida_venda',1,'2026-08-15 04:57:55','Venda PDV #6'),(21,7,'saida_venda',3,'2026-08-14 04:57:55','Venda PDV #7'),(22,11,'saida_venda',4,'2026-08-14 04:57:55','Venda PDV #7'),(23,16,'saida_venda',1,'2026-08-13 04:57:55','Venda PDV #8'),(24,15,'saida_venda',2,'2026-08-13 04:57:55','Venda PDV #8'),(25,19,'saida_venda',1,'2026-08-13 04:57:55','Venda PDV #8'),(26,18,'saida_venda',1,'2026-08-12 04:57:55','Venda PDV #9'),(27,17,'saida_venda',5,'2026-08-12 04:57:55','Venda PDV #9'),(28,5,'saida_venda',3,'2026-08-11 04:57:55','Venda PDV #10'),(29,8,'saida_venda',2,'2026-08-11 04:57:55','Venda PDV #10'),(30,7,'saida_venda',2,'2026-08-10 04:57:55','Venda PDV #11'),(31,12,'saida_venda',3,'2026-08-10 04:57:55','Venda PDV #11'),(32,13,'saida_venda',2,'2026-08-09 04:57:55','Venda PDV #12'),(33,14,'saida_venda',4,'2026-08-09 04:57:55','Venda PDV #12'),(34,9,'saida_venda',5,'2026-08-09 04:57:55','Venda PDV #12'),(35,16,'saida_venda',2,'2026-07-31 04:57:55','Venda PDV #13'),(36,5,'saida_venda',2,'2026-07-31 04:57:55','Venda PDV #13'),(37,7,'saida_venda',5,'2026-07-24 04:57:55','Venda PDV #14'),(38,6,'saida_venda',2,'2026-07-01 04:57:55','Venda PDV #15'),(39,18,'saida_venda',2,'2026-07-01 04:57:55','Venda PDV #15'),(40,19,'saida_venda',1,'2026-08-15 18:26:41','Venda PDV #16'),(41,10,'saida_venda',1,'2026-08-15 18:26:41','Venda PDV #16'),(42,10,'saida_venda',1,'2026-08-15 19:53:26','Venda PDV #17'),(43,16,'saida_venda',6,'2026-08-15 19:53:52','Venda PDV #18'),(44,19,'saida_venda',1,'2026-08-21 20:43:47','Venda PDV #19');
/*!40000 ALTER TABLE `movimentacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produtos`
--

DROP TABLE IF EXISTS `produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 0,
  `estoque_minimo` int(11) DEFAULT 5,
  `validade` date DEFAULT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `preco_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fornecedor_id` int(11) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `codigo_de_barra` varchar(50) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fornecedor_id` (`fornecedor_id`),
  KEY `fk_produtos_categoria` (`categoria_id`),
  CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produtos`
--

LOCK TABLES `produtos` WRITE;
/*!40000 ALTER TABLE `produtos` DISABLE KEYS */;
INSERT INTO `produtos` VALUES (1,'Caderno Espiral 10 Matérias','Cadernos & Blocos',121,20,NULL,25.90,10.50,1,'ativo',NULL,1),(2,'Caneta Esferográfica Azul (Caixa)','Canetas & Marcadores',0,10,'2028-05-20',45.00,20.00,2,'ativo',NULL,2),(3,'Tinta Guache Kit 6 Cores','Tintas & Pintura',181,15,'2018-12-01',12.50,5.20,3,'ativo',NULL,9),(4,'Papel Sulfite A4 (Resma)','Papéis & Folhas',82,50,NULL,29.90,18.00,NULL,'ativo',NULL,6),(5,'Caderno Universitário 10 Matérias Spiral','Cadernos & Blocos',45,10,NULL,24.90,11.50,1,'ativo','7891027101015',1),(6,'Caneta Esferográfica Azul 0.7mm Caixa c/ 50','Canetas & Marcadores',18,5,'2028-12-31',49.90,28.00,2,'ativo','7891027101022',2),(7,'Resma Papel Sulfite A4 75g Chamex 500fls','Papéis & Folhas',60,20,NULL,32.00,19.50,1,'ativo','7891027101039',6),(8,'Lápis Grafite HB Nº 2 Faber-Castell Caixa c/ 12','Lápis & Apontadores',35,10,NULL,14.50,7.20,2,'ativo','7891027101046',3),(9,'Borracha Branca com Cinta Plástica Mercur','Borrachas & Correção',80,25,NULL,2.50,0.90,3,'ativo','7891027101053',4),(10,'Apontador com Depósito Faber-Castell','Lápis & Apontadores',23,10,NULL,5.90,2.40,2,'ativo','7891027101060',3),(11,'Marca-Texto Amarelo Fluorescente','Canetas & Marcadores',50,15,'2028-06-30',4.90,2.10,2,'ativo','7891027101077',2),(12,'Cola Bastão 40g Pritt','Colas & Fitas Adesivas',30,10,'2027-10-31',11.90,5.80,3,'ativo','7891027101084',5),(13,'Tesoura Escolar Sem Ponta Mundial','Corte & Medição',40,10,NULL,7.90,3.50,3,'ativo','7891027101091',8),(14,'Régua Cristal 30cm Waleu','Corte & Medição',65,15,NULL,3.00,1.10,3,'ativo','7891027101107',8),(15,'Tinta Guache 6 Cores 15ml Acrilex','Tintas & Pintura',28,10,'2027-08-30',8.90,4.20,3,'ativo','7891027101114',9),(16,'Caixa de Lápis de Cor 24 Cores Ecolápis','Lápis & Apontadores',16,8,NULL,29.90,16.00,3,'ativo','7891027101121',3),(17,'Pasta Suspensa Kraft Dello','Pastas & Organização',55,15,NULL,4.50,1.80,1,'ativo','7891027101138',7),(18,'Grampeador de Mesa Médio 26/6','Grampeadores & Fixação',12,5,NULL,26.50,14.00,2,'ativo','7891027101145',10),(19,'Bloco de Notas Adesivas Post-it 76x76mm','Cadernos & Blocos',38,15,NULL,9.90,4.50,1,'ativo','7891027101152',1);
/*!40000 ALTER TABLE `produtos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `perfil` enum('admin','caixa') DEFAULT 'caixa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin','$2y$12$5vO68WF9baFnaOSRkXJORetFEd0z.0JauQq183dSA2CerFPTbA5pC','admin'),(2,'caixa','$2y$10$JW4itU7/mn9qgScmmboxSuSBkE.fXcV.aIRafv9fXA6TmyBzlJXpG','caixa');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendas`
--

DROP TABLE IF EXISTS `vendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) DEFAULT NULL,
  `data_venda` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `forma_pagamento` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vendas_cliente` (`cliente_id`),
  KEY `idx_vendas_data` (`data_venda`),
  CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendas`
--

LOCK TABLES `vendas` WRITE;
/*!40000 ALTER TABLE `vendas` DISABLE KEYS */;
INSERT INTO `vendas` VALUES (1,1,'2026-06-03 21:36:30',135.00,'PIX'),(2,2,'2026-06-03 21:36:30',25.00,'Cartão de Crédito'),(3,NULL,'2026-06-06 13:57:21',25.90,'DINHEIRO'),(4,3,'2026-08-14 20:33:07',29.90,'DINHEIRO'),(5,NULL,'2026-08-14 23:16:24',225.00,'PIX'),(6,NULL,'2026-08-15 04:57:55',99.70,'PIX'),(7,NULL,'2026-08-14 04:57:55',115.60,'CARTÃO DE CRÉDITO'),(8,NULL,'2026-08-13 04:57:55',57.60,'DINHEIRO'),(9,NULL,'2026-08-12 04:57:55',49.00,'PIX'),(10,NULL,'2026-08-11 04:57:55',103.70,'CARTÃO DE DÉBITO'),(11,NULL,'2026-08-10 04:57:55',99.70,'PIX'),(12,NULL,'2026-08-09 04:57:55',40.30,'DINHEIRO'),(13,NULL,'2026-07-31 04:57:55',109.60,'CARTÃO DE CRÉDITO'),(14,NULL,'2026-07-24 04:57:55',160.00,'PIX'),(15,NULL,'2026-07-01 04:57:55',152.80,'DINHEIRO'),(16,NULL,'2026-08-15 18:26:41',15.80,'PIX'),(17,NULL,'2026-08-15 19:53:26',5.90,'DINHEIRO'),(18,NULL,'2026-08-15 19:53:52',179.40,'DINHEIRO'),(19,1,'2026-08-21 20:43:47',9.90,'DINHEIRO');
/*!40000 ALTER TABLE `vendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendas_itens`
--

DROP TABLE IF EXISTS `vendas_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendas_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vendas_itens_venda` (`venda_id`),
  KEY `idx_vendas_itens_produto` (`produto_id`),
  CONSTRAINT `vendas_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendas_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendas_itens`
--

LOCK TABLES `vendas_itens` WRITE;
/*!40000 ALTER TABLE `vendas_itens` DISABLE KEYS */;
INSERT INTO `vendas_itens` VALUES (1,1,2,3,45.00),(2,2,3,2,12.50),(3,3,1,1,25.90),(4,4,4,1,29.90),(5,5,2,5,45.00),(6,6,5,2,24.90),(7,6,6,1,49.90),(8,7,7,3,32.00),(9,7,11,4,4.90),(10,8,16,1,29.90),(11,8,15,2,8.90),(12,8,19,1,9.90),(13,9,18,1,26.50),(14,9,17,5,4.50),(15,10,5,3,24.90),(16,10,8,2,14.50),(17,11,7,2,32.00),(18,11,12,3,11.90),(19,12,13,2,7.90),(20,12,14,4,3.00),(21,12,9,5,2.50),(22,13,16,2,29.90),(23,13,5,2,24.90),(24,14,7,5,32.00),(25,15,6,2,49.90),(26,15,18,2,26.50),(27,16,19,1,9.90),(28,16,10,1,5.90),(29,17,10,1,5.90),(30,18,16,6,29.90),(31,19,19,1,9.90);
/*!40000 ALTER TABLE `vendas_itens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-21 23:04:42
