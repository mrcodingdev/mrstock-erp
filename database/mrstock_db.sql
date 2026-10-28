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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'João da Silva Ramos','joao.ramos@gmail.com','(15) 99124-8832','ativo','2026-08-01 10:00:00','219.482.908-14','Rua São Bento','145','Centro','Sorocaba','SP','18010-030'),(2,'Maria Eduarda Oliveira','maria.eduarda@outlook.com','(15) 99781-4421','ativo','2026-08-01 10:00:00','341.872.108-56','Avenida Antônio Carlos Comitre','820','Parque Campolim','Sorocaba','SP','18047-620'),(3,'Carlos Alberto de Souza','carlos.asouza@terra.com.br','(15) 98112-3390','ativo','2026-08-01 10:00:00','118.942.308-72','Rua Hermelino Matarazzo','302','Vila Carvalho','Sorocaba','SP','18060-000'),(4,'Patrícia Helena Mendonça','patricia.mendonca@gmail.com','(15) 99654-1287','ativo','2026-08-01 10:00:00','405.123.788-90','Rua Nogueira Martins','512','Centro','Sorocaba','SP','18010-230'),(5,'Lucas Henrique Pereira','lucas.pereira@gmail.com','(15) 99234-5678','ativo','2026-08-01 10:00:00','289.654.328-01','Rua Cel. Nogueira Padilha','780','Vila Hortência','Sorocaba','SP','18020-000'),(6,'Beatriz Fernandes Santos','bia.fernandes@uol.com.br','(15) 98845-6712','ativo','2026-08-01 10:00:00','378.912.458-33','Avenida General Carneiro','1240','Vila Lucy','Sorocaba','SP','18043-000'),(7,'Rodrigo Moreira Lima','rodrigo.mlima@gmail.com','(15) 99156-7890','ativo','2026-08-01 10:00:00','156.789.238-44','Avenida Elias Maluf','450','Wanel Ville','Sorocaba','SP','18055-500'),(8,'Fernanda Cristina Gomes','fernanda.gomes@hotmail.com','(15) 99712-3456','ativo','2026-08-01 10:00:00','421.365.898-77','Rua Aparecida','920','Santa Rosália','Sorocaba','SP','18095-000'),(9,'Marcelo Antonio Barbosa','marcelo.barbosa@gmail.com','(15) 98123-4567','ativo','2026-08-01 10:00:00','234.567.898-88','Avenida Pereira da Silva','630','Jardim Santa Rosália','Sorocaba','SP','18090-000'),(10,'Camila Regina de Toledo','camila.toledo@yahoo.com.br','(15) 99678-9012','ativo','2026-08-01 10:00:00','312.456.788-99','Rua da Penha','340','Centro','Sorocaba','SP','18010-001'),(11,'Rafael Guimarães Castro','rafael.castro@gmail.com','(15) 99201-2345','ativo','2026-08-01 10:00:00','198.765.438-12','Rua Álvaro Soares','210','Centro','Sorocaba','SP','18010-190'),(12,'Juliana Silveira Prado','juliana.prado@gmail.com','(15) 99765-4321','ativo','2026-08-01 10:00:00','456.123.988-65','Avenida Barão de Tatuí','550','Jardim Vergueiro','Sorocaba','SP','18030-000');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (1,1,1,'NF-e 14201',3420.00,'boleto','PAGA','2026-09-10 14:20:00'),(2,2,1,'NF-e 8841',1890.50,'pix','PAGA','2026-09-18 10:15:00'),(3,3,1,'NF-e 33092',2650.00,'boleto','PAGA','2026-09-25 11:30:00'),(4,4,1,'NF-e 55104',1980.00,'pix','PAGA','2026-10-05 16:40:00'),(5,5,1,'NF-e 99210',4120.00,'boleto','PAGA','2026-10-14 09:50:00'),(6,1,1,'NF-e 14890',2150.00,'pix','PAGA','2026-10-22 15:10:00');
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
INSERT INTO `configuracoes` VALUES ('alerta_estoque','1','2026-08-22 02:03:57'),('alerta_vencimento','1','2026-08-22 02:03:57'),('alerta_vencimento_dias','30','2026-09-01 23:21:04'),('densidade_tabela','padrao','2026-09-01 23:30:28'),('empresa_cep','18010-082','2026-09-01 23:21:04'),('empresa_cidade','Sorocaba/SP','2026-09-01 23:21:04'),('empresa_cnpj','50.334.808/0001-38','2026-08-22 02:03:57'),('empresa_endereco','Rua XV de Novembro, 250 - Centro, Sorocaba/SP','2026-08-22 02:03:57'),('empresa_ie','688.123.456.789','2026-09-01 23:21:04'),('empresa_nome','Papelaria Real (Sueli & Osnir)','2026-08-22 02:03:57'),('empresa_razao','Papelaria Real Ltda - ME','2026-08-22 02:03:57'),('empresa_regime','Simples Nacional (ME)','2026-09-01 23:21:04'),('empresa_telefone','(15) 3232-0000','2026-08-22 02:03:57'),('empresa_whatsapp','(15) 99123-4567','2026-08-22 02:03:57'),('estoque_minimo_global','5','2026-09-01 23:21:04'),('estoque_trava_negativo','bloquear','2026-09-01 21:44:10'),('linhas_zebradas','1','2026-08-28 01:15:04'),('pdv_desconto_maximo','10','2026-09-01 23:55:48'),('pdv_impressora','80mm','2026-09-01 23:21:04'),('pdv_trava_margem','aviso','2026-09-01 23:55:21'),('som_pdv','1','2026-09-01 23:46:14'),('tamanho_fonte','normal','2026-09-01 23:30:52');
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cupons_fiscais`
--

LOCK TABLES `cupons_fiscais` WRITE;
/*!40000 ALTER TABLE `cupons_fiscais` DISABLE KEYS */;
INSERT INTO `cupons_fiscais` VALUES (1,1,'35261004148243000116650010000000018000341151','2026-09-28 10:52:15'),(2,2,'35261004148243000116650010000000028278585199','2026-09-29 16:20:36'),(3,3,'35261004148243000116650010000000034886609651','2026-09-30 14:52:10'),(4,4,'35261004148243000116650010000000043708641719','2026-10-01 16:34:26'),(5,5,'35261004148243000116650010000000055074541928','2026-10-02 14:52:50'),(6,6,'35261004148243000116650010000000069247725026','2026-10-03 14:15:20'),(7,7,'35261004148243000116650010000000077478682337','2026-10-03 16:22:27'),(8,8,'35261004148243000116650010000000086674160194','2026-10-04 11:20:10'),(9,9,'35261004148243000116650010000000099887947339','2026-10-05 10:42:24'),(10,10,'35261004148243000116650010000000101872036613','2026-10-05 16:45:00'),(11,11,'35261004148243000116650010000000114484881713','2026-10-06 11:55:28'),(12,12,'35261004148243000116650010000000124476009829','2026-10-06 14:20:39'),(13,13,'35261004148243000116650010000000136404644086','2026-10-07 13:26:15'),(14,14,'35261004148243000116650010000000143780597828','2026-10-08 10:10:42'),(15,15,'35261004148243000116650010000000153387783101','2026-10-09 12:28:49'),(16,16,'35261004148243000116650010000000169621210121','2026-10-09 15:19:34'),(17,17,'35261004148243000116650010000000179429641841','2026-10-10 13:41:54'),(18,18,'35261004148243000116650010000000186986656136','2026-10-11 09:17:46'),(19,19,'35261004148243000116650010000000195606027600','2026-10-12 09:10:37'),(20,20,'35261004148243000116650010000000206825250832','2026-10-12 18:41:27'),(21,21,'35261004148243000116650010000000213096709072','2026-10-13 10:10:16'),(22,22,'35261004148243000116650010000000228181035628','2026-10-14 09:28:34'),(23,23,'35261004148243000116650010000000233803307747','2026-10-15 09:34:38'),(24,24,'35261004148243000116650010000000245992475930','2026-10-15 16:39:36'),(25,25,'35261004148243000116650010000000257205594921','2026-10-16 10:54:39'),(26,26,'35261004148243000116650010000000269408414498','2026-10-16 14:46:13'),(27,27,'35261004148243000116650010000000279797302553','2026-10-16 16:30:11'),(28,28,'35261004148243000116650010000000288065188992','2026-10-17 10:49:30'),(29,29,'35261004148243000116650010000000298489748634','2026-10-17 14:24:48'),(30,30,'35261004148243000116650010000000309263190125','2026-10-17 16:21:40'),(31,31,'35261004148243000116650010000000319474323712','2026-10-18 10:28:35'),(32,32,'35261004148243000116650010000000323051199798','2026-10-18 14:39:40'),(33,33,'35261004148243000116650010000000336455369059','2026-10-18 16:31:40'),(34,34,'35261004148243000116650010000000343072646718','2026-10-19 10:46:20'),(35,35,'35261004148243000116650010000000352738220532','2026-10-19 14:42:19'),(36,36,'35261004148243000116650010000000366983962568','2026-10-19 16:23:32'),(37,37,'35261004148243000116650010000000373893457244','2026-10-20 10:14:28'),(38,38,'35261004148243000116650010000000385295508411','2026-10-20 14:39:44'),(39,39,'35261004148243000116650010000000399598642339','2026-10-20 16:19:32'),(40,40,'35261004148243000116650010000000406237011929','2026-10-20 17:30:12'),(41,41,'35261004148243000116650010000000417191966923','2026-10-21 09:40:19'),(42,42,'35261004148243000116650010000000424439882275','2026-10-21 11:44:14'),(43,43,'35261004148243000116650010000000435617769647','2026-10-21 15:36:40'),(44,44,'35261004148243000116650010000000441402559719','2026-10-22 09:37:38'),(45,45,'35261004148243000116650010000000452932629827','2026-10-22 11:47:12'),(46,46,'35261004148243000116650010000000464801267687','2026-10-22 15:33:38'),(47,47,'35261004148243000116650010000000471095285839','2026-10-23 09:50:45'),(48,48,'35261004148243000116650010000000487607594398','2026-10-23 11:55:21'),(49,49,'35261004148243000116650010000000495902961513','2026-10-23 15:19:16'),(50,50,'35261004148243000116650010000000507820356557','2026-10-24 09:43:29'),(51,51,'35261004148243000116650010000000519618748321','2026-10-24 11:42:43'),(52,52,'35261004148243000116650010000000524171305231','2026-10-24 15:18:37'),(53,53,'35261004148243000116650010000000537999385903','2026-10-25 09:45:26'),(54,54,'35261004148243000116650010000000547085450019','2026-10-25 11:21:48'),(55,55,'35261004148243000116650010000000552570361015','2026-10-25 15:34:38'),(56,56,'35261004148243000116650010000000566219521512','2026-10-26 09:12:44'),(57,57,'35261004148243000116650010000000579812577873','2026-10-26 11:44:18'),(58,58,'35261004148243000116650010000000587124833238','2026-10-26 15:38:49'),(59,59,'35261004148243000116650010000000597206925961','2026-10-27 09:14:22'),(60,60,'35261004148243000116650010000000607813126693','2026-10-27 10:45:10'),(61,61,'35261004148243000116650010000000616818576429','2026-10-27 12:30:45'),(62,62,'35261004148243000116650010000000627611238487','2026-10-27 14:15:33'),(63,63,'35261004148243000116650010000000633855314884','2026-10-27 16:50:18'),(64,64,'35261004148243000116650010000000646693680575','2026-10-27 17:35:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedores`
--

LOCK TABLES `fornecedores` WRITE;
/*!40000 ALTER TABLE `fornecedores` DISABLE KEYS */;
INSERT INTO `fornecedores` VALUES (1,'Tilibra S/A Produtos de Papelaria','44.990.901/0001-43','(14) 3235-4000','vendas@tilibra.com.br','ativo','Roberto Almeida','Rodovia Marechal Rondon, Km 354','S/N','Distrito Industrial','Bauru','SP','17034-290'),(2,'BIC da Amazônia S/A','04.148.243/0001-16','(11) 2118-8000','comercial@bic.com','ativo','Amanda Castro','Avenida das Nações Unidas','12901','Brooklin Paulista','São Paulo','SP','04578-000'),(3,'Faber-Castell Brasil Ltda','61.064.838/0001-40','(16) 2106-1000','vendas@fabercastell.com.br','ativo','Fernando Silveira','Rua Cel. José Augusto de Oliveira Salles','1876','Vila Izabel','São Carlos','SP','13570-820'),(4,'Acrilex Tintas Especiais S/A','50.334.808/0001-38','(11) 4344-8800','contato@acrilex.com.br','ativo','Marcos Vinicius','Estrada das Lágrimas','130','Rudge Ramos','São Bernardo do Campo','SP','09642-000'),(5,'International Paper / Chamex Papéis','60.543.907/0001-20','(19) 3874-7000','atendimento@chamex.com.br','ativo','Carlos Alberto Duarte','Rodovia SP 340, Km 171','S/N','Zona Rural','Mogi Guaçu','SP','13840-970');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itens_compra`
--

LOCK TABLES `itens_compra` WRITE;
/*!40000 ALTER TABLE `itens_compra` DISABLE KEYS */;
INSERT INTO `itens_compra` VALUES (1,1,1,50.000,18.50,925.00),(2,1,2,40.000,4.80,192.00),(3,1,4,30.000,8.20,246.00),(4,2,6,20.000,32.00,640.00),(5,2,7,20.000,32.00,640.00),(6,2,10,30.000,4.20,126.00),(7,3,11,40.000,8.50,340.00),(8,3,12,30.000,17.50,525.00),(9,3,14,50.000,3.10,155.00),(10,4,21,40.000,6.50,260.00),(11,4,22,60.000,2.20,132.00),(12,4,41,50.000,4.50,225.00),(13,5,26,80.000,19.50,1560.00),(14,5,27,40.000,18.00,720.00),(15,6,31,60.000,2.10,126.00),(16,6,46,20.000,14.50,290.00);
/*!40000 ALTER TABLE `itens_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_tentativas`
--

DROP TABLE IF EXISTS `login_tentativas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_tentativas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_usuario` varchar(45) NOT NULL,
  `username` varchar(100) NOT NULL,
  `data_tentativa` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip_data` (`ip_usuario`,`data_tentativa`),
  KEY `idx_user_data` (`username`,`data_tentativa`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_tentativas`
--

LOCK TABLES `login_tentativas` WRITE;
/*!40000 ALTER TABLE `login_tentativas` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_tentativas` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,2,'VENDA_REALIZADA','Venda PDV #1 finalizada no valor de R$ 111.50 via PIX','vendas','127.0.0.1','2026-09-28 10:52:15'),(2,2,'VENDA_REALIZADA','Venda PDV #2 finalizada no valor de R$ 43.20 via DINHEIRO','vendas','127.0.0.1','2026-09-29 16:20:36'),(3,1,'VENDA_REALIZADA','Venda PDV #3 finalizada no valor de R$ 103.50 via DINHEIRO','vendas','127.0.0.1','2026-09-30 14:52:10'),(4,2,'VENDA_REALIZADA','Venda PDV #4 finalizada no valor de R$ 28.80 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-01 16:34:26'),(5,2,'VENDA_REALIZADA','Venda PDV #5 finalizada no valor de R$ 230.50 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-02 14:52:50'),(6,1,'VENDA_REALIZADA','Venda PDV #6 finalizada no valor de R$ 121.60 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-03 14:15:20'),(7,1,'VENDA_REALIZADA','Venda PDV #7 finalizada no valor de R$ 13.80 via PIX','vendas','127.0.0.1','2026-10-03 16:22:27'),(8,2,'VENDA_REALIZADA','Venda PDV #8 finalizada no valor de R$ 112.60 via DINHEIRO','vendas','127.0.0.1','2026-10-04 11:20:10'),(9,1,'VENDA_REALIZADA','Venda PDV #9 finalizada no valor de R$ 146.40 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-05 10:42:24'),(10,1,'VENDA_REALIZADA','Venda PDV #10 finalizada no valor de R$ 231.30 via DINHEIRO','vendas','127.0.0.1','2026-10-05 16:45:00'),(11,1,'VENDA_REALIZADA','Venda PDV #11 finalizada no valor de R$ 52.60 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-06 11:55:28'),(12,2,'VENDA_REALIZADA','Venda PDV #12 finalizada no valor de R$ 126.80 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-06 14:20:39'),(13,1,'VENDA_REALIZADA','Venda PDV #13 finalizada no valor de R$ 7.80 via DINHEIRO','vendas','127.0.0.1','2026-10-07 13:26:15'),(14,1,'VENDA_REALIZADA','Venda PDV #14 finalizada no valor de R$ 25.80 via PIX','vendas','127.0.0.1','2026-10-08 10:10:42'),(15,1,'VENDA_REALIZADA','Venda PDV #15 finalizada no valor de R$ 30.00 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-09 12:28:49'),(16,2,'VENDA_REALIZADA','Venda PDV #16 finalizada no valor de R$ 20.70 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-09 15:19:34'),(17,1,'VENDA_REALIZADA','Venda PDV #17 finalizada no valor de R$ 48.40 via DINHEIRO','vendas','127.0.0.1','2026-10-10 13:41:54'),(18,2,'VENDA_REALIZADA','Venda PDV #18 finalizada no valor de R$ 143.50 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-11 09:17:46'),(19,2,'VENDA_REALIZADA','Venda PDV #19 finalizada no valor de R$ 53.70 via PIX','vendas','127.0.0.1','2026-10-12 09:10:37'),(20,2,'VENDA_REALIZADA','Venda PDV #20 finalizada no valor de R$ 138.20 via PIX','vendas','127.0.0.1','2026-10-12 18:41:27'),(21,2,'VENDA_REALIZADA','Venda PDV #21 finalizada no valor de R$ 68.40 via PIX','vendas','127.0.0.1','2026-10-13 10:10:16'),(22,1,'VENDA_REALIZADA','Venda PDV #22 finalizada no valor de R$ 107.60 via PIX','vendas','127.0.0.1','2026-10-14 09:28:34'),(23,1,'VENDA_REALIZADA','Venda PDV #23 finalizada no valor de R$ 182.30 via PIX','vendas','127.0.0.1','2026-10-15 09:34:38'),(24,2,'VENDA_REALIZADA','Venda PDV #24 finalizada no valor de R$ 163.70 via DINHEIRO','vendas','127.0.0.1','2026-10-15 16:39:36'),(25,2,'VENDA_REALIZADA','Venda PDV #25 finalizada no valor de R$ 69.70 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-16 10:54:39'),(26,2,'VENDA_REALIZADA','Venda PDV #26 finalizada no valor de R$ 25.00 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-16 14:46:13'),(27,2,'VENDA_REALIZADA','Venda PDV #27 finalizada no valor de R$ 107.30 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-16 16:30:11'),(28,2,'VENDA_REALIZADA','Venda PDV #28 finalizada no valor de R$ 106.00 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-17 10:49:30'),(29,2,'VENDA_REALIZADA','Venda PDV #29 finalizada no valor de R$ 11.00 via PIX','vendas','127.0.0.1','2026-10-17 14:24:48'),(30,2,'VENDA_REALIZADA','Venda PDV #30 finalizada no valor de R$ 133.60 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-17 16:21:40'),(31,2,'VENDA_REALIZADA','Venda PDV #31 finalizada no valor de R$ 78.50 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-18 10:28:35'),(32,2,'VENDA_REALIZADA','Venda PDV #32 finalizada no valor de R$ 45.80 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-18 14:39:40'),(33,2,'VENDA_REALIZADA','Venda PDV #33 finalizada no valor de R$ 187.60 via PIX','vendas','127.0.0.1','2026-10-18 16:31:40'),(34,2,'VENDA_REALIZADA','Venda PDV #34 finalizada no valor de R$ 21.90 via DINHEIRO','vendas','127.0.0.1','2026-10-19 10:46:20'),(35,2,'VENDA_REALIZADA','Venda PDV #35 finalizada no valor de R$ 141.00 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-19 14:42:19'),(36,1,'VENDA_REALIZADA','Venda PDV #36 finalizada no valor de R$ 107.50 via DINHEIRO','vendas','127.0.0.1','2026-10-19 16:23:32'),(37,2,'VENDA_REALIZADA','Venda PDV #37 finalizada no valor de R$ 74.50 via DINHEIRO','vendas','127.0.0.1','2026-10-20 10:14:28'),(38,2,'VENDA_REALIZADA','Venda PDV #38 finalizada no valor de R$ 231.50 via PIX','vendas','127.0.0.1','2026-10-20 14:39:44'),(39,1,'VENDA_REALIZADA','Venda PDV #39 finalizada no valor de R$ 81.20 via PIX','vendas','127.0.0.1','2026-10-20 16:19:32'),(40,2,'VENDA_REALIZADA','Venda PDV #40 finalizada no valor de R$ 204.50 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-20 17:30:12'),(41,2,'VENDA_REALIZADA','Venda PDV #41 finalizada no valor de R$ 84.40 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-21 09:40:19'),(42,1,'VENDA_REALIZADA','Venda PDV #42 finalizada no valor de R$ 29.70 via DINHEIRO','vendas','127.0.0.1','2026-10-21 11:44:14'),(43,1,'VENDA_REALIZADA','Venda PDV #43 finalizada no valor de R$ 15.70 via DINHEIRO','vendas','127.0.0.1','2026-10-21 15:36:40'),(44,2,'VENDA_REALIZADA','Venda PDV #44 finalizada no valor de R$ 76.10 via DINHEIRO','vendas','127.0.0.1','2026-10-22 09:37:38'),(45,2,'VENDA_REALIZADA','Venda PDV #45 finalizada no valor de R$ 85.90 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-22 11:47:12'),(46,2,'VENDA_REALIZADA','Venda PDV #46 finalizada no valor de R$ 184.40 via DINHEIRO','vendas','127.0.0.1','2026-10-22 15:33:38'),(47,1,'VENDA_REALIZADA','Venda PDV #47 finalizada no valor de R$ 142.50 via DINHEIRO','vendas','127.0.0.1','2026-10-23 09:50:45'),(48,1,'VENDA_REALIZADA','Venda PDV #48 finalizada no valor de R$ 194.30 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-23 11:55:21'),(49,2,'VENDA_REALIZADA','Venda PDV #49 finalizada no valor de R$ 48.00 via PIX','vendas','127.0.0.1','2026-10-23 15:19:16'),(50,2,'VENDA_REALIZADA','Venda PDV #50 finalizada no valor de R$ 74.90 via DINHEIRO','vendas','127.0.0.1','2026-10-24 09:43:29'),(51,2,'VENDA_REALIZADA','Venda PDV #51 finalizada no valor de R$ 11.80 via DINHEIRO','vendas','127.0.0.1','2026-10-24 11:42:43'),(52,2,'VENDA_REALIZADA','Venda PDV #52 finalizada no valor de R$ 172.60 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-24 15:18:37'),(53,2,'VENDA_REALIZADA','Venda PDV #53 finalizada no valor de R$ 76.70 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-25 09:45:26'),(54,2,'VENDA_REALIZADA','Venda PDV #54 finalizada no valor de R$ 71.70 via PIX','vendas','127.0.0.1','2026-10-25 11:21:48'),(55,2,'VENDA_REALIZADA','Venda PDV #55 finalizada no valor de R$ 122.40 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-25 15:34:38'),(56,2,'VENDA_REALIZADA','Venda PDV #56 finalizada no valor de R$ 16.90 via CARTÃO DE DÉBITO','vendas','127.0.0.1','2026-10-26 09:12:44'),(57,1,'VENDA_REALIZADA','Venda PDV #57 finalizada no valor de R$ 54.90 via PIX','vendas','127.0.0.1','2026-10-26 11:44:18'),(58,2,'VENDA_REALIZADA','Venda PDV #58 finalizada no valor de R$ 166.40 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-26 15:38:49'),(59,1,'VENDA_REALIZADA','Venda PDV #59 finalizada no valor de R$ 39.80 via PIX','vendas','127.0.0.1','2026-10-27 09:14:22'),(60,2,'VENDA_REALIZADA','Venda PDV #60 finalizada no valor de R$ 56.60 via PIX','vendas','127.0.0.1','2026-10-27 10:45:10'),(61,2,'VENDA_REALIZADA','Venda PDV #61 finalizada no valor de R$ 57.70 via CARTÃO DE CRÉDITO','vendas','127.0.0.1','2026-10-27 12:30:45'),(62,1,'VENDA_REALIZADA','Venda PDV #62 finalizada no valor de R$ 174.60 via PIX','vendas','127.0.0.1','2026-10-27 14:15:33'),(63,2,'VENDA_REALIZADA','Venda PDV #63 finalizada no valor de R$ 74.40 via PIX','vendas','127.0.0.1','2026-10-27 16:50:18'),(64,2,'VENDA_REALIZADA','Venda PDV #64 finalizada no valor de R$ 221.60 via PIX','vendas','127.0.0.1','2026-10-27 17:35:50'),(65,1,'LOGIN','Autenticação bem-sucedida do Administrador (admin)','usuarios','127.0.0.1','2026-10-27 08:00:00'),(66,2,'LOGIN','Operador de Frente de Caixa logado com sucesso (caixa)','usuarios','127.0.0.1','2026-10-27 08:05:00'),(67,1,'LOTE_CRIADO','Lote #LT-2026-FC08A cadastrado com 25 un. para o produto #8','lotes','127.0.0.1','2026-10-26 14:10:00'),(68,1,'LOTE_CRIADO','Lote #LT-2026-BC10A cadastrado com 20 un. para o produto #10','lotes','127.0.0.1','2026-10-26 14:15:00'),(69,1,'COMPRA_REGISTRADA','Compra #6 registrada para Tilibra S/A no valor de R$ 2.150,00','compras','127.0.0.1','2026-10-22 15:10:00'),(70,1,'RELATORIO_GERADO','Exportação de Relatório Financeiro Consolidado em formato PDF','relatorios','127.0.0.1','2026-10-27 11:00:00');
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
  KEY `idx_lotes_validade` (`data_validade`),
  KEY `idx_lotes_quantidade` (`quantidade`),
  KEY `idx_lotes_fifo` (`produto_id`,`quantidade`,`data_validade`),
  CONSTRAINT `lotes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lotes_ibfk_2` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes`
--

LOCK TABLES `lotes` WRITE;
/*!40000 ALTER TABLE `lotes` DISABLE KEYS */;
INSERT INTO `lotes` VALUES (1,8,'LT-2026-FC08A','2025-11-14','2026-11-14',25,2.30,3,'2026-09-01 08:00:00'),(2,10,'LT-2026-BC10A','2025-11-18','2026-11-18',20,4.20,2,'2026-09-01 08:00:00'),(3,17,'LT-2026-TX17A','2025-11-16','2026-11-16',15,5.90,2,'2026-09-01 08:00:00'),(4,21,'LT-2026-PR21A','2025-11-12','2026-11-12',20,6.50,4,'2026-09-01 08:00:00'),(5,41,'LT-2026-AX41A','2025-11-13','2026-11-13',30,4.50,4,'2026-09-01 08:00:00'),(6,8,'LT-2027-FC08B','2026-08-30','2027-08-30',50,2.30,3,'2026-09-01 08:00:00'),(7,10,'LT-2027-BC10B','2026-09-15','2027-11-15',40,4.20,2,'2026-09-01 08:00:00'),(8,17,'LT-2027-TX17B','2026-09-20','2027-09-20',40,5.90,2,'2026-09-01 08:00:00'),(9,21,'LT-2027-PR21B','2026-08-31','2027-10-31',40,6.50,4,'2026-09-01 08:00:00'),(10,41,'LT-2027-AX41B','2026-06-30','2027-06-30',60,4.50,4,'2026-09-01 08:00:00'),(11,6,'LT-2027-BC06','2026-01-10','2027-12-31',40,32.00,2,'2026-09-01 08:00:00'),(12,7,'LT-2027-BC07','2026-01-10','2027-12-31',35,32.00,2,'2026-09-01 08:00:00'),(13,9,'LT-2027-FC09','2026-04-15','2027-10-15',50,9.80,3,'2026-09-01 08:00:00'),(14,18,'LT-2027-BC18','2026-03-30','2027-09-30',65,2.40,2,'2026-09-01 08:00:00'),(15,20,'LT-2027-FC20','2026-01-20','2027-07-20',45,4.80,3,'2026-09-01 08:00:00'),(16,22,'LT-2027-AX22','2026-02-15','2027-08-15',95,2.20,4,'2026-09-01 08:00:00'),(17,23,'LT-2027-AX23','2026-05-30','2027-11-30',80,2.80,4,'2026-09-01 08:00:00'),(18,42,'LT-2027-AX42','2026-06-10','2027-12-10',65,8.50,4,'2026-09-01 08:00:00'),(19,43,'LT-2028-AX43','2026-08-28','2028-02-28',85,3.20,4,'2026-09-01 08:00:00'),(20,45,'LT-2028-PT45','2026-04-30','2028-04-30',35,16.50,4,'2026-09-01 08:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimentacoes`
--

LOCK TABLES `movimentacoes` WRITE;
/*!40000 ALTER TABLE `movimentacoes` DISABLE KEYS */;
INSERT INTO `movimentacoes` VALUES (1,1,'entrada_compra',80,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(2,2,'entrada_compra',95,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(3,3,'entrada_compra',105,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(4,4,'entrada_compra',60,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(5,5,'entrada_compra',45,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(6,6,'entrada_compra',55,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(7,7,'entrada_compra',50,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(8,8,'entrada_compra',90,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(9,9,'entrada_compra',65,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(10,10,'entrada_compra',75,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(11,11,'entrada_compra',85,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(12,12,'entrada_compra',70,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(13,13,'entrada_compra',63,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(14,14,'entrada_compra',100,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(15,15,'entrada_compra',55,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(16,16,'entrada_compra',125,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(17,17,'entrada_compra',70,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(18,18,'entrada_compra',80,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(19,19,'entrada_compra',45,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(20,20,'entrada_compra',60,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(21,21,'entrada_compra',75,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(22,22,'entrada_compra',110,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(23,23,'entrada_compra',95,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(24,24,'entrada_compra',85,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(25,25,'entrada_compra',80,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(26,26,'entrada_compra',155,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(27,27,'entrada_compra',65,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(28,28,'entrada_compra',60,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(29,29,'entrada_compra',50,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(30,30,'entrada_compra',55,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(31,31,'entrada_compra',135,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(32,32,'entrada_compra',40,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(33,33,'entrada_compra',53,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(34,34,'entrada_compra',45,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(35,35,'entrada_compra',37,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(36,36,'entrada_compra',100,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(37,37,'entrada_compra',65,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(38,38,'entrada_compra',145,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(39,39,'entrada_compra',60,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(40,40,'entrada_compra',35,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(41,41,'entrada_compra',105,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(42,42,'entrada_compra',80,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(43,43,'entrada_compra',100,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(44,44,'entrada_compra',70,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(45,45,'entrada_compra',50,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(46,46,'entrada_compra',50,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(47,47,'entrada_compra',115,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(48,48,'entrada_compra',125,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(49,49,'entrada_compra',43,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(50,50,'entrada_compra',75,'2026-09-01 08:30:00','Carga Inicial de Estoque Papelaria Real'),(51,11,'saida_venda',2,'2026-09-28 10:52:15','Venda PDV #1'),(52,37,'saida_venda',1,'2026-09-28 10:52:15','Venda PDV #1'),(53,45,'saida_venda',2,'2026-09-28 10:52:15','Venda PDV #1'),(54,28,'saida_venda',1,'2026-09-29 16:20:36','Venda PDV #2'),(55,36,'saida_venda',3,'2026-09-29 16:20:36','Venda PDV #2'),(56,16,'saida_venda',2,'2026-09-30 14:52:10','Venda PDV #3'),(57,49,'saida_venda',3,'2026-09-30 14:52:10','Venda PDV #3'),(58,8,'saida_venda',1,'2026-10-01 16:34:26','Venda PDV #4'),(59,38,'saida_venda',2,'2026-10-01 16:34:26','Venda PDV #4'),(60,42,'saida_venda',1,'2026-10-01 16:34:26','Venda PDV #4'),(61,6,'saida_venda',3,'2026-10-02 14:52:50','Venda PDV #5'),(62,17,'saida_venda',1,'2026-10-02 14:52:50','Venda PDV #5'),(63,35,'saida_venda',1,'2026-10-02 14:52:50','Venda PDV #5'),(64,9,'saida_venda',1,'2026-10-03 14:15:20','Venda PDV #6'),(65,33,'saida_venda',3,'2026-10-03 14:15:20','Venda PDV #6'),(66,44,'saida_venda',2,'2026-10-03 14:15:20','Venda PDV #6'),(67,14,'saida_venda',2,'2026-10-03 16:22:27','Venda PDV #7'),(68,4,'saida_venda',1,'2026-10-04 11:20:10','Venda PDV #8'),(69,45,'saida_venda',3,'2026-10-04 11:20:10','Venda PDV #8'),(70,6,'saida_venda',1,'2026-10-05 10:42:24','Venda PDV #9'),(71,27,'saida_venda',3,'2026-10-05 10:42:24','Venda PDV #9'),(72,11,'saida_venda',3,'2026-10-05 16:45:00','Venda PDV #10'),(73,15,'saida_venda',1,'2026-10-05 16:45:00','Venda PDV #10'),(74,35,'saida_venda',3,'2026-10-05 16:45:00','Venda PDV #10'),(75,9,'saida_venda',1,'2026-10-06 11:55:28','Venda PDV #11'),(76,10,'saida_venda',3,'2026-10-06 11:55:28','Venda PDV #11'),(77,38,'saida_venda',2,'2026-10-06 11:55:28','Venda PDV #11'),(78,40,'saida_venda',2,'2026-10-06 14:20:39','Venda PDV #12'),(79,47,'saida_venda',3,'2026-10-06 14:20:39','Venda PDV #12'),(80,16,'saida_venda',2,'2026-10-07 13:26:15','Venda PDV #13'),(81,17,'saida_venda',2,'2026-10-08 10:10:42','Venda PDV #14'),(82,44,'saida_venda',2,'2026-10-09 12:28:49','Venda PDV #15'),(83,14,'saida_venda',1,'2026-10-09 15:19:34','Venda PDV #16'),(84,16,'saida_venda',2,'2026-10-09 15:19:34','Venda PDV #16'),(85,38,'saida_venda',2,'2026-10-09 15:19:34','Venda PDV #16'),(86,14,'saida_venda',1,'2026-10-10 13:41:54','Venda PDV #17'),(87,23,'saida_venda',2,'2026-10-10 13:41:54','Venda PDV #17'),(88,25,'saida_venda',3,'2026-10-10 13:41:54','Venda PDV #17'),(89,6,'saida_venda',1,'2026-10-11 09:17:46','Venda PDV #18'),(90,17,'saida_venda',3,'2026-10-11 09:17:46','Venda PDV #18'),(91,40,'saida_venda',1,'2026-10-11 09:17:46','Venda PDV #18'),(92,16,'saida_venda',3,'2026-10-12 09:10:37','Venda PDV #19'),(93,34,'saida_venda',2,'2026-10-12 09:10:37','Venda PDV #19'),(94,13,'saida_venda',3,'2026-10-12 18:41:27','Venda PDV #20'),(95,17,'saida_venda',2,'2026-10-12 18:41:27','Venda PDV #20'),(96,26,'saida_venda',1,'2026-10-12 18:41:27','Venda PDV #20'),(97,22,'saida_venda',3,'2026-10-13 10:10:16','Venda PDV #21'),(98,42,'saida_venda',3,'2026-10-13 10:10:16','Venda PDV #21'),(99,16,'saida_venda',2,'2026-10-14 09:28:34','Venda PDV #22'),(100,40,'saida_venda',2,'2026-10-14 09:28:34','Venda PDV #22'),(101,1,'saida_venda',3,'2026-10-15 09:34:38','Venda PDV #23'),(102,14,'saida_venda',2,'2026-10-15 09:34:38','Venda PDV #23'),(103,45,'saida_venda',2,'2026-10-15 09:34:38','Venda PDV #23'),(104,13,'saida_venda',2,'2026-10-15 16:39:36','Venda PDV #24'),(105,35,'saida_venda',1,'2026-10-15 16:39:36','Venda PDV #24'),(106,46,'saida_venda',2,'2026-10-15 16:39:36','Venda PDV #24'),(107,30,'saida_venda',2,'2026-10-16 10:54:39','Venda PDV #25'),(108,45,'saida_venda',1,'2026-10-16 10:54:39','Venda PDV #25'),(109,3,'saida_venda',2,'2026-10-16 14:46:13','Venda PDV #26'),(110,3,'saida_venda',3,'2026-10-16 16:30:11','Venda PDV #27'),(111,34,'saida_venda',2,'2026-10-16 16:30:11','Venda PDV #27'),(112,37,'saida_venda',2,'2026-10-16 16:30:11','Venda PDV #27'),(113,5,'saida_venda',2,'2026-10-17 10:49:30','Venda PDV #28'),(114,38,'saida_venda',1,'2026-10-17 10:49:30','Venda PDV #28'),(115,47,'saida_venda',3,'2026-10-17 10:49:30','Venda PDV #28'),(116,50,'saida_venda',2,'2026-10-17 14:24:48','Venda PDV #29'),(117,4,'saida_venda',2,'2026-10-17 16:21:40','Venda PDV #30'),(118,40,'saida_venda',2,'2026-10-17 16:21:40','Venda PDV #30'),(119,3,'saida_venda',1,'2026-10-18 10:28:35','Venda PDV #31'),(120,28,'saida_venda',3,'2026-10-18 10:28:35','Venda PDV #31'),(121,50,'saida_venda',3,'2026-10-18 10:28:35','Venda PDV #31'),(122,19,'saida_venda',1,'2026-10-18 14:39:40','Venda PDV #32'),(123,23,'saida_venda',1,'2026-10-18 14:39:40','Venda PDV #32'),(124,35,'saida_venda',3,'2026-10-18 16:31:40','Venda PDV #33'),(125,46,'saida_venda',1,'2026-10-18 16:31:40','Venda PDV #33'),(126,17,'saida_venda',1,'2026-10-19 10:46:20','Venda PDV #34'),(127,47,'saida_venda',1,'2026-10-19 10:46:20','Venda PDV #34'),(128,17,'saida_venda',2,'2026-10-19 14:42:19','Venda PDV #35'),(129,24,'saida_venda',3,'2026-10-19 14:42:19','Venda PDV #35'),(130,27,'saida_venda',3,'2026-10-19 14:42:19','Venda PDV #35'),(131,48,'saida_venda',2,'2026-10-19 16:23:32','Venda PDV #36'),(132,49,'saida_venda',3,'2026-10-19 16:23:32','Venda PDV #36'),(133,25,'saida_venda',3,'2026-10-20 10:14:28','Venda PDV #37'),(134,36,'saida_venda',2,'2026-10-20 10:14:28','Venda PDV #37'),(135,47,'saida_venda',3,'2026-10-20 10:14:28','Venda PDV #37'),(136,6,'saida_venda',3,'2026-10-20 14:39:44','Venda PDV #38'),(137,21,'saida_venda',1,'2026-10-20 14:39:44','Venda PDV #38'),(138,35,'saida_venda',1,'2026-10-20 14:39:44','Venda PDV #38'),(139,1,'saida_venda',1,'2026-10-20 16:19:32','Venda PDV #39'),(140,10,'saida_venda',2,'2026-10-20 16:19:32','Venda PDV #39'),(141,41,'saida_venda',3,'2026-10-20 16:19:32','Venda PDV #39'),(142,1,'saida_venda',3,'2026-10-20 17:30:12','Venda PDV #40'),(143,40,'saida_venda',2,'2026-10-20 17:30:12','Venda PDV #40'),(144,20,'saida_venda',2,'2026-10-21 09:40:19','Venda PDV #41'),(145,24,'saida_venda',1,'2026-10-21 09:40:19','Venda PDV #41'),(146,30,'saida_venda',3,'2026-10-21 09:40:19','Venda PDV #41'),(147,20,'saida_venda',3,'2026-10-21 11:44:14','Venda PDV #42'),(148,31,'saida_venda',2,'2026-10-21 15:36:40','Venda PDV #43'),(149,48,'saida_venda',1,'2026-10-21 15:36:40','Venda PDV #43'),(150,13,'saida_venda',1,'2026-10-22 09:37:38','Venda PDV #44'),(151,14,'saida_venda',3,'2026-10-22 09:37:38','Venda PDV #44'),(152,46,'saida_venda',1,'2026-10-22 09:37:38','Venda PDV #44'),(153,5,'saida_venda',2,'2026-10-22 11:47:12','Venda PDV #45'),(154,25,'saida_venda',1,'2026-10-22 11:47:12','Venda PDV #45'),(155,5,'saida_venda',2,'2026-10-22 15:33:38','Venda PDV #46'),(156,11,'saida_venda',1,'2026-10-22 15:33:38','Venda PDV #46'),(157,27,'saida_venda',3,'2026-10-22 15:33:38','Venda PDV #46'),(158,1,'saida_venda',3,'2026-10-23 09:50:45','Venda PDV #47'),(159,30,'saida_venda',2,'2026-10-23 09:50:45','Venda PDV #47'),(160,8,'saida_venda',3,'2026-10-23 11:55:21','Venda PDV #48'),(161,19,'saida_venda',2,'2026-10-23 11:55:21','Venda PDV #48'),(162,40,'saida_venda',2,'2026-10-23 11:55:21','Venda PDV #48'),(163,29,'saida_venda',2,'2026-10-23 15:19:16','Venda PDV #49'),(164,27,'saida_venda',2,'2026-10-24 09:43:29','Venda PDV #50'),(165,37,'saida_venda',1,'2026-10-24 09:43:29','Venda PDV #50'),(166,31,'saida_venda',1,'2026-10-24 11:42:43','Venda PDV #51'),(167,43,'saida_venda',1,'2026-10-24 11:42:43','Venda PDV #51'),(168,35,'saida_venda',3,'2026-10-24 15:18:37','Venda PDV #52'),(169,37,'saida_venda',1,'2026-10-24 15:18:37','Venda PDV #52'),(170,17,'saida_venda',1,'2026-10-25 09:45:26','Venda PDV #53'),(171,49,'saida_venda',2,'2026-10-25 09:45:26','Venda PDV #53'),(172,23,'saida_venda',3,'2026-10-25 11:21:48','Venda PDV #54'),(173,38,'saida_venda',3,'2026-10-25 11:21:48','Venda PDV #54'),(174,44,'saida_venda',3,'2026-10-25 11:21:48','Venda PDV #54'),(175,1,'saida_venda',3,'2026-10-25 15:34:38','Venda PDV #55'),(176,23,'saida_venda',3,'2026-10-25 15:34:38','Venda PDV #55'),(177,11,'saida_venda',1,'2026-10-26 09:12:44','Venda PDV #56'),(178,6,'saida_venda',1,'2026-10-26 11:44:18','Venda PDV #57'),(179,9,'saida_venda',2,'2026-10-26 15:38:49','Venda PDV #58'),(180,14,'saida_venda',1,'2026-10-26 15:38:49','Venda PDV #58'),(181,19,'saida_venda',3,'2026-10-26 15:38:49','Venda PDV #58'),(182,9,'saida_venda',2,'2026-10-27 09:14:22','Venda PDV #59'),(183,14,'saida_venda',1,'2026-10-27 10:45:10','Venda PDV #60'),(184,15,'saida_venda',1,'2026-10-27 10:45:10','Venda PDV #60'),(185,21,'saida_venda',2,'2026-10-27 10:45:10','Venda PDV #60'),(186,2,'saida_venda',2,'2026-10-27 12:30:45','Venda PDV #61'),(187,9,'saida_venda',1,'2026-10-27 12:30:45','Venda PDV #61'),(188,47,'saida_venda',2,'2026-10-27 12:30:45','Venda PDV #61'),(189,31,'saida_venda',1,'2026-10-27 14:15:33','Venda PDV #62'),(190,32,'saida_venda',2,'2026-10-27 14:15:33','Venda PDV #62'),(191,42,'saida_venda',3,'2026-10-27 14:15:33','Venda PDV #62'),(192,17,'saida_venda',2,'2026-10-27 16:50:18','Venda PDV #63'),(193,20,'saida_venda',3,'2026-10-27 16:50:18','Venda PDV #63'),(194,30,'saida_venda',1,'2026-10-27 16:50:18','Venda PDV #63'),(195,6,'saida_venda',2,'2026-10-27 17:35:50','Venda PDV #64'),(196,29,'saida_venda',2,'2026-10-27 17:35:50','Venda PDV #64'),(197,45,'saida_venda',2,'2026-10-27 17:35:50','Venda PDV #64');
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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produtos`
--

LOCK TABLES `produtos` WRITE;
/*!40000 ALTER TABLE `produtos` DISABLE KEYS */;
INSERT INTO `produtos` VALUES (1,'Caderno Universitário Capa Dura 10 Matérias Zip 200fls Tilibra','Cadernos & Blocos',65,15,NULL,34.90,18.50,1,'ativo','7891027101015',1),(2,'Caderno Brochura Caligrafia Tilibra 40fls','Cadernos & Blocos',80,10,NULL,9.90,4.80,1,'ativo','7891027101022',1),(3,'Bloco de Notas Adesivas Post-it 76x76mm Amarelo 100fls 3M','Cadernos & Blocos',90,15,NULL,12.50,5.50,1,'ativo','7891027101039',1),(4,'Caderno de Desenho Cartografia Espiral Tilibra 80fls','Cadernos & Blocos',45,10,NULL,16.90,8.20,1,'ativo','7891027101046',1),(5,'Bloco Flip Chart 50 Folhas 75g Tilibra','Cadernos & Blocos',30,5,NULL,38.00,19.00,1,'ativo','7891027101053',1),(6,'Caneta Esferográfica BIC Cristal Azul 1.0mm (Caixa c/ 50)','Canetas & Marcadores',40,10,'2027-12-31',54.90,32.00,2,'ativo','7891027102012',2),(7,'Caneta Esferográfica BIC Cristal Preta 1.0mm (Caixa c/ 50)','Canetas & Marcadores',35,10,'2027-12-31',54.90,32.00,2,'ativo','7891027102029',2),(8,'Marca-Texto Amarelo Fluorescente Faber-Castell Grifpen','Canetas & Marcadores',75,15,'2026-11-14',4.90,2.30,3,'ativo','7891027102036',2),(9,'Caneta Hidrográfica 12 Cores Faber-Castell Bicolor','Canetas & Marcadores',50,10,'2027-10-15',19.90,9.80,3,'ativo','7891027102043',2),(10,'Marcador para Quadro Branco BIC Marking Azul','Canetas & Marcadores',60,10,'2026-11-18',8.90,4.20,2,'ativo','7891027102050',2),(11,'Lápis Grafite HB Nº 2 Faber-Castell Max (Caixa c/ 12)','Lápis & Apontadores',70,15,NULL,16.90,8.50,3,'ativo','7891027103019',3),(12,'Caixa de Lápis de Cor 24 Cores Faber-Castell Ecolápis','Lápis & Apontadores',55,10,NULL,32.90,17.50,3,'ativo','7891027103026',3),(13,'Caixa de Lápis de Cor 12 Cores Bicolor 24 Cores Faber-Castell','Lápis & Apontadores',48,10,NULL,26.50,14.00,3,'ativo','7891027103033',3),(14,'Apontador com Depósito Faber-Castell Grip','Lápis & Apontadores',85,15,NULL,6.90,3.10,3,'ativo','7891027103040',3),(15,'Lapiseira Técnica Poly Matic 0.7mm Faber-Castell','Lápis & Apontadores',40,8,NULL,21.90,11.20,3,'ativo','7891027103057',3),(16,'Borracha Branca com Cinta Plástica Faber-Castell Dust Free','Borrachas & Correção',110,20,NULL,3.90,1.80,3,'ativo','7891027104016',4),(17,'Fita Corretiva 5mm x 6m BIC Tipp-Ex Easy Refill','Borrachas & Correção',55,10,'2026-11-16',12.90,5.90,2,'ativo','7891027104023',4),(18,'Corretivo Líquido à Base de Água 18ml BIC ECO','Borrachas & Correção',65,10,'2027-09-30',5.50,2.40,2,'ativo','7891027104030',4),(19,'Borracha Escolar Mercur Record 40 (Caixa c/ 40)','Borrachas & Correção',30,5,NULL,39.90,22.00,3,'ativo','7891027104047',4),(20,'Caneta Corretiva 8ml Faber-Castell','Borrachas & Correção',45,8,'2027-07-20',9.90,4.80,3,'ativo','7891027104054',4),(21,'Cola Bastão 40g Pritt Henkel','Colas & Fitas Adesivas',60,15,'2026-11-12',13.90,6.50,4,'ativo','7891027105013',5),(22,'Cola Branca Escolar Líquida 90g Acrilex Tenaz','Colas & Fitas Adesivas',95,20,'2027-08-15',4.90,2.20,4,'ativo','7891027105020',5),(23,'Cola Bastão 20g Acrilex Paper Glue','Colas & Fitas Adesivas',80,15,'2027-11-30',5.90,2.80,4,'ativo','7891027105037',5),(24,'Fita Adesiva Crepe 18mm x 50m Adelbras','Colas & Fitas Adesivas',70,15,NULL,7.90,3.90,1,'ativo','7891027105044',5),(25,'Fita Transparente Larga 45mm x 45m Scotch 3M','Colas & Fitas Adesivas',65,10,NULL,9.90,4.80,1,'ativo','7891027105051',5),(26,'Resma Papel Sulfite A4 75g Chamex Office 500fls','Papéis & Folhas',140,30,NULL,32.90,19.50,5,'ativo','7891027106010',6),(27,'Resma Papel Sulfite Carta 75g Chamex 500fls','Papéis & Folhas',50,15,NULL,30.50,18.00,5,'ativo','7891027106027',6),(28,'Pacote Papel Color Set A4 120g Cores Sortidas 50fls','Papéis & Folhas',45,10,NULL,16.50,8.50,5,'ativo','7891027106034',6),(29,'Cartolina Branca Escolar 150g 50x66cm (Pacote c/ 20)','Papéis & Folhas',35,10,NULL,24.00,12.00,5,'ativo','7891027106041',6),(30,'Papel Fotográfico Glossy A4 180g Masterprint 20fls','Papéis & Folhas',40,8,NULL,18.90,9.00,5,'ativo','7891027106058',6),(31,'Pasta Aba Elástico Cristal Ofício Dello Line','Pastas & Organização',120,25,NULL,4.90,2.10,1,'ativo','7891027107017',7),(32,'Pasta Suspensa Kraft Completa Dello (Caixa c/ 25)','Pastas & Organização',25,5,NULL,58.00,31.00,1,'ativo','7891027107024',7),(33,'Pasta Catálogo A4 c/ 50 Envelopes Médios ACP','Pastas & Organização',38,8,NULL,23.90,12.50,1,'ativo','7891027107031',7),(34,'Organizador de Mesa com Divisórias Waleu Fumê','Pastas & Organização',30,5,NULL,21.00,10.50,1,'ativo','7891027107048',7),(35,'Fichário Universitário 4 Argolas Tilibra Preto','Pastas & Organização',22,5,NULL,52.90,28.00,1,'ativo','7891027107055',7),(36,'Tesoura Escolar Sem Ponta 13cm Mundial Ponto Vermelho','Corte & Medição',85,15,NULL,8.90,4.20,1,'ativo','7891027108014',8),(37,'Estilete Profissional Largo 18mm com Trava Olfa','Corte & Medição',50,10,NULL,13.90,6.80,1,'ativo','7891027108021',8),(38,'Régua Cristal Transparente 30cm Waleu','Corte & Medição',130,25,NULL,3.00,1.20,3,'ativo','7891027108038',8),(39,'Kit Geometria Escolar 4 Peças Waleu','Corte & Medição',45,10,NULL,13.50,6.50,3,'ativo','7891027108045',8),(40,'Base de Corte A3 Dupla Face 45x30cm Tilibra','Corte & Medição',20,5,NULL,49.90,26.00,1,'ativo','7891027108052',8),(41,'Tinta Guache 6 Cores 15ml Acrilex Tempera Guache','Tintas & Pintura',90,20,'2026-11-13',9.50,4.50,4,'ativo','7891027109011',9),(42,'Tinta Guache 12 Cores 15ml Acrilex com Pincel','Tintas & Pintura',65,15,'2027-12-10',17.90,8.50,4,'ativo','7891027109028',9),(43,'Tinta para Tecido Acrilex 37ml Cores Variadas','Tintas & Pintura',85,15,'2028-02-28',6.90,3.20,4,'ativo','7891027109035',9),(44,'Kit Pincéis Escolares Chatos Nº 2, 4, 8 e 12 Tigre','Tintas & Pintura',55,10,NULL,15.00,7.20,4,'ativo','7891027109042',9),(45,'Aquarela em Pastilha 12 Cores Pentel Arts','Tintas & Pintura',35,8,'2028-04-30',31.90,16.50,4,'ativo','7891027109059',9),(46,'Grampeador de Mesa Médio Metal 26/6 Tilibra G101','Grampeadores & Fixação',35,8,NULL,28.90,14.50,1,'ativo','7891027110017',10),(47,'Caixa de Grampos Galvanizados 26/6 c/ 5000 Tilibra','Grampeadores & Fixação',100,20,NULL,9.00,4.50,1,'ativo','7891027110024',10),(48,'Caixa de Clips Metálicos Nº 2/0 c/ 100un Bacchi','Grampeadores & Fixação',110,20,NULL,5.90,2.80,3,'ativo','7891027110031',10),(49,'Perfurador de Papel 2 Furos Metal 20 Folhas Tilibra','Grampeadores & Fixação',28,5,NULL,31.90,16.00,1,'ativo','7891027110048',10),(50,'Extrator de Grampos Espátula Metal Tilibra','Grampeadores & Fixação',60,10,NULL,5.50,2.50,1,'ativo','7891027110055',10);
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
INSERT INTO `usuarios` VALUES (1,'admin','$2y$10$d.CdY5rXeDU6Dcp0pA7ayO5iFrF7FOSKfG3r/O6VsI1CFw3NXxyKC','admin'),(2,'caixa','$2y$10$Uef6Bt1qX/4oMAGPmCxRrOG0p.htajl752doSz2KTtH4tYAgWDDM.','caixa');
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendas`
--

LOCK TABLES `vendas` WRITE;
/*!40000 ALTER TABLE `vendas` DISABLE KEYS */;
INSERT INTO `vendas` VALUES (1,7,'2026-09-28 10:52:15',111.50,'PIX'),(2,NULL,'2026-09-29 16:20:36',43.20,'DINHEIRO'),(3,NULL,'2026-09-30 14:52:10',103.50,'DINHEIRO'),(4,NULL,'2026-10-01 16:34:26',28.80,'CARTÃO DE CRÉDITO'),(5,11,'2026-10-02 14:52:50',230.50,'CARTÃO DE CRÉDITO'),(6,NULL,'2026-10-03 14:15:20',121.60,'CARTÃO DE CRÉDITO'),(7,11,'2026-10-03 16:22:27',13.80,'PIX'),(8,2,'2026-10-04 11:20:10',112.60,'DINHEIRO'),(9,6,'2026-10-05 10:42:24',146.40,'CARTÃO DE CRÉDITO'),(10,5,'2026-10-05 16:45:00',231.30,'DINHEIRO'),(11,NULL,'2026-10-06 11:55:28',52.60,'CARTÃO DE DÉBITO'),(12,2,'2026-10-06 14:20:39',126.80,'CARTÃO DE DÉBITO'),(13,NULL,'2026-10-07 13:26:15',7.80,'DINHEIRO'),(14,12,'2026-10-08 10:10:42',25.80,'PIX'),(15,1,'2026-10-09 12:28:49',30.00,'CARTÃO DE CRÉDITO'),(16,NULL,'2026-10-09 15:19:34',20.70,'CARTÃO DE CRÉDITO'),(17,7,'2026-10-10 13:41:54',48.40,'DINHEIRO'),(18,NULL,'2026-10-11 09:17:46',143.50,'CARTÃO DE CRÉDITO'),(19,NULL,'2026-10-12 09:10:37',53.70,'PIX'),(20,12,'2026-10-12 18:41:27',138.20,'PIX'),(21,NULL,'2026-10-13 10:10:16',68.40,'PIX'),(22,2,'2026-10-14 09:28:34',107.60,'PIX'),(23,NULL,'2026-10-15 09:34:38',182.30,'PIX'),(24,6,'2026-10-15 16:39:36',163.70,'DINHEIRO'),(25,1,'2026-10-16 10:54:39',69.70,'CARTÃO DE DÉBITO'),(26,2,'2026-10-16 14:46:13',25.00,'CARTÃO DE DÉBITO'),(27,11,'2026-10-16 16:30:11',107.30,'CARTÃO DE CRÉDITO'),(28,2,'2026-10-17 10:49:30',106.00,'CARTÃO DE CRÉDITO'),(29,4,'2026-10-17 14:24:48',11.00,'PIX'),(30,11,'2026-10-17 16:21:40',133.60,'CARTÃO DE DÉBITO'),(31,NULL,'2026-10-18 10:28:35',78.50,'CARTÃO DE CRÉDITO'),(32,NULL,'2026-10-18 14:39:40',45.80,'CARTÃO DE DÉBITO'),(33,4,'2026-10-18 16:31:40',187.60,'PIX'),(34,NULL,'2026-10-19 10:46:20',21.90,'DINHEIRO'),(35,2,'2026-10-19 14:42:19',141.00,'CARTÃO DE CRÉDITO'),(36,NULL,'2026-10-19 16:23:32',107.50,'DINHEIRO'),(37,NULL,'2026-10-20 10:14:28',74.50,'DINHEIRO'),(38,2,'2026-10-20 14:39:44',231.50,'PIX'),(39,5,'2026-10-20 16:19:32',81.20,'PIX'),(40,NULL,'2026-10-20 17:30:12',204.50,'CARTÃO DE DÉBITO'),(41,12,'2026-10-21 09:40:19',84.40,'CARTÃO DE CRÉDITO'),(42,12,'2026-10-21 11:44:14',29.70,'DINHEIRO'),(43,2,'2026-10-21 15:36:40',15.70,'DINHEIRO'),(44,NULL,'2026-10-22 09:37:38',76.10,'DINHEIRO'),(45,NULL,'2026-10-22 11:47:12',85.90,'CARTÃO DE DÉBITO'),(46,NULL,'2026-10-22 15:33:38',184.40,'DINHEIRO'),(47,8,'2026-10-23 09:50:45',142.50,'DINHEIRO'),(48,NULL,'2026-10-23 11:55:21',194.30,'CARTÃO DE CRÉDITO'),(49,3,'2026-10-23 15:19:16',48.00,'PIX'),(50,NULL,'2026-10-24 09:43:29',74.90,'DINHEIRO'),(51,8,'2026-10-24 11:42:43',11.80,'DINHEIRO'),(52,5,'2026-10-24 15:18:37',172.60,'CARTÃO DE CRÉDITO'),(53,NULL,'2026-10-25 09:45:26',76.70,'CARTÃO DE DÉBITO'),(54,NULL,'2026-10-25 11:21:48',71.70,'PIX'),(55,NULL,'2026-10-25 15:34:38',122.40,'CARTÃO DE DÉBITO'),(56,NULL,'2026-10-26 09:12:44',16.90,'CARTÃO DE DÉBITO'),(57,NULL,'2026-10-26 11:44:18',54.90,'PIX'),(58,NULL,'2026-10-26 15:38:49',166.40,'CARTÃO DE CRÉDITO'),(59,11,'2026-10-27 09:14:22',39.80,'PIX'),(60,6,'2026-10-27 10:45:10',56.60,'PIX'),(61,3,'2026-10-27 12:30:45',57.70,'CARTÃO DE CRÉDITO'),(62,NULL,'2026-10-27 14:15:33',174.60,'PIX'),(63,3,'2026-10-27 16:50:18',74.40,'PIX'),(64,5,'2026-10-27 17:35:50',221.60,'PIX');
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
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendas_itens`
--

LOCK TABLES `vendas_itens` WRITE;
/*!40000 ALTER TABLE `vendas_itens` DISABLE KEYS */;
INSERT INTO `vendas_itens` VALUES (1,1,11,2,16.90),(2,1,37,1,13.90),(3,1,45,2,31.90),(4,2,28,1,16.50),(5,2,36,3,8.90),(6,3,16,2,3.90),(7,3,49,3,31.90),(8,4,8,1,4.90),(9,4,38,2,3.00),(10,4,42,1,17.90),(11,5,6,3,54.90),(12,5,17,1,12.90),(13,5,35,1,52.90),(14,6,9,1,19.90),(15,6,33,3,23.90),(16,6,44,2,15.00),(17,7,14,2,6.90),(18,8,4,1,16.90),(19,8,45,3,31.90),(20,9,6,1,54.90),(21,9,27,3,30.50),(22,10,11,3,16.90),(23,10,15,1,21.90),(24,10,35,3,52.90),(25,11,9,1,19.90),(26,11,10,3,8.90),(27,11,38,2,3.00),(28,12,40,2,49.90),(29,12,47,3,9.00),(30,13,16,2,3.90),(31,14,17,2,12.90),(32,15,44,2,15.00),(33,16,14,1,6.90),(34,16,16,2,3.90),(35,16,38,2,3.00),(36,17,14,1,6.90),(37,17,23,2,5.90),(38,17,25,3,9.90),(39,18,6,1,54.90),(40,18,17,3,12.90),(41,18,40,1,49.90),(42,19,16,3,3.90),(43,19,34,2,21.00),(44,20,13,3,26.50),(45,20,17,2,12.90),(46,20,26,1,32.90),(47,21,22,3,4.90),(48,21,42,3,17.90),(49,22,16,2,3.90),(50,22,40,2,49.90),(51,23,1,3,34.90),(52,23,14,2,6.90),(53,23,45,2,31.90),(54,24,13,2,26.50),(55,24,35,1,52.90),(56,24,46,2,28.90),(57,25,30,2,18.90),(58,25,45,1,31.90),(59,26,3,2,12.50),(60,27,3,3,12.50),(61,27,34,2,21.00),(62,27,37,2,13.90),(63,28,5,2,38.00),(64,28,38,1,3.00),(65,28,47,3,9.00),(66,29,50,2,5.50),(67,30,4,2,16.90),(68,30,40,2,49.90),(69,31,3,1,12.50),(70,31,28,3,16.50),(71,31,50,3,5.50),(72,32,19,1,39.90),(73,32,23,1,5.90),(74,33,35,3,52.90),(75,33,46,1,28.90),(76,34,17,1,12.90),(77,34,47,1,9.00),(78,35,17,2,12.90),(79,35,24,3,7.90),(80,35,27,3,30.50),(81,36,48,2,5.90),(82,36,49,3,31.90),(83,37,25,3,9.90),(84,37,36,2,8.90),(85,37,47,3,9.00),(86,38,6,3,54.90),(87,38,21,1,13.90),(88,38,35,1,52.90),(89,39,1,1,34.90),(90,39,10,2,8.90),(91,39,41,3,9.50),(92,40,1,3,34.90),(93,40,40,2,49.90),(94,41,20,2,9.90),(95,41,24,1,7.90),(96,41,30,3,18.90),(97,42,20,3,9.90),(98,43,31,2,4.90),(99,43,48,1,5.90),(100,44,13,1,26.50),(101,44,14,3,6.90),(102,44,46,1,28.90),(103,45,5,2,38.00),(104,45,25,1,9.90),(105,46,5,2,38.00),(106,46,11,1,16.90),(107,46,27,3,30.50),(108,47,1,3,34.90),(109,47,30,2,18.90),(110,48,8,3,4.90),(111,48,19,2,39.90),(112,48,40,2,49.90),(113,49,29,2,24.00),(114,50,27,2,30.50),(115,50,37,1,13.90),(116,51,31,1,4.90),(117,51,43,1,6.90),(118,52,35,3,52.90),(119,52,37,1,13.90),(120,53,17,1,12.90),(121,53,49,2,31.90),(122,54,23,3,5.90),(123,54,38,3,3.00),(124,54,44,3,15.00),(125,55,1,3,34.90),(126,55,23,3,5.90),(127,56,11,1,16.90),(128,57,6,1,54.90),(129,58,9,2,19.90),(130,58,14,1,6.90),(131,58,19,3,39.90),(132,59,9,2,19.90),(133,60,14,1,6.90),(134,60,15,1,21.90),(135,60,21,2,13.90),(136,61,2,2,9.90),(137,61,9,1,19.90),(138,61,47,2,9.00),(139,62,31,1,4.90),(140,62,32,2,58.00),(141,62,42,3,17.90),(142,63,17,2,12.90),(143,63,20,3,9.90),(144,63,30,1,18.90),(145,64,6,2,54.90),(146,64,29,2,24.00),(147,64,45,2,31.90);
/*!40000 ALTER TABLE `vendas_itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'mrstock_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-27 23:44:55
