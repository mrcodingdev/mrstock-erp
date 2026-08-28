-- ============================================================================
-- MrStock ERP — Database Dump para Nuvem / ProFreeHost (mrstock.unaux.com)
-- Arquivo: mrstock_db_unaux_production.sql (Encoding UTF-8 sem BOM)
-- Pronto para importação via phpMyAdmin em bases compartilhadas (sem CREATE DATABASE/USE)
-- ============================================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ----------------------------------------------------------------------------
-- 1. Tabela: `usuarios`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `perfil` enum('admin','caixa') DEFAULT 'caixa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `usuarios` WRITE;
INSERT INTO `usuarios` (`id`, `username`, `password`, `perfil`) VALUES 
(1, 'admin', '$2y$10$JW4itU7/mn9qgScmmboxSuSBkE.fXcV.aIRafv9fXA6TmyBzlJXpG', 'admin'),
(2, 'caixa', '$2y$10$JW4itU7/mn9qgScmmboxSuSBkE.fXcV.aIRafv9fXA6TmyBzlJXpG', 'caixa');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 2. Tabela: `categorias`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `categorias` WRITE;
INSERT INTO `categorias` (`id`, `nome`, `descricao`) VALUES 
(2, 'Papelaria', 'Cadernos, blocos, papéis e envelopes'),
(3, 'Escrita & Escritório', 'Canetas, lápis, marca-textos e corretores'),
(4, 'Escolar', 'Borrachas, apontadores, colas e tesouras'),
(5, 'Artes & Pintura', 'Tintas, pincéis, lápis de cor e guache'),
(6, 'Organização', 'Pastas, arquivos, grampeadores e organizadores'),
(7, 'Desenho & Técnico', 'Réguas, esquadros, compassos e pranchetas');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 3. Tabela: `fornecedores`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `fornecedores`;
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

LOCK TABLES `fornecedores` WRITE;
INSERT INTO `fornecedores` (`id`, `nome`, `cnpj`, `telefone`, `email`, `status`, `contato`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`) VALUES 
(1, 'Tilibra S.A', '44.990.901/0001-43', '(14) 3235-4000', 'vendas@tilibra.com.br', 'ativo', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Bic Brasil', '04.148.243/0001-16', '(11) 2118-8000', 'comercial@bic.com', 'ativo', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Acrilex', '50.334.808/0001-38', '(11) 4344-8800', 'contato@acrilex.com', 'ativo', 'a', '', '', '', '', '', '');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 4. Tabela: `clientes`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `clientes`;
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

LOCK TABLES `clientes` WRITE;
INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`, `status`, `data_cadastro`, `cpf_cnpj`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`) VALUES 
(1, 'João Silva', 'joao@email.com', '(11) 98765-4321', 'ativo', '2026-06-03 21:36:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Maria Oliveira', 'maria@email.com', '(11) 91234-5678', 'ativo', '2026-06-03 21:36:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Carlos Souza', 'carlos@email.com', '(11) 99999-8888', 'ativo', '2026-06-03 21:36:30', '111.111.111-11', '', '', '', '', '', '11111-11');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 5. Tabela: `produtos`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `produtos`;
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

LOCK TABLES `produtos` WRITE;
INSERT INTO `produtos` (`id`, `nome`, `categoria`, `quantidade`, `estoque_minimo`, `validade`, `preco_venda`, `preco_compra`, `fornecedor_id`, `status`, `codigo_de_barra`, `categoria_id`) VALUES 
(1, 'Caderno Espiral 10 Matérias', 'Papelaria', 121, 20, NULL, 25.90, 10.50, 1, 'ativo', NULL, NULL),
(2, 'Caneta Esferográfica Azul (Caixa)', 'Escritório', 0, 10, '2028-05-20', 45.00, 20.00, 2, 'ativo', NULL, NULL),
(3, 'Tinta Guache Kit 6 Cores', 'Artes', 181, 15, '2018-12-01', 12.50, 5.20, 3, 'ativo', NULL, NULL),
(4, 'Papel Sulfite A4 (Resma)', 'Papelaria', 82, 50, NULL, 29.90, 18.00, NULL, 'ativo', NULL, NULL),
(5, 'Caderno Universitário 10 Matérias Spiral', 'Papelaria', 45, 10, NULL, 24.90, 11.50, 1, 'ativo', '7891027101015', 2),
(6, 'Caneta Esferográfica Azul 0.7mm Caixa c/ 50', 'Escrita & Escritório', 18, 5, '2028-12-31', 49.90, 28.00, 2, 'ativo', '7891027101022', 3),
(7, 'Resma Papel Sulfite A4 75g Chamex 500fls', 'Papelaria', 60, 20, NULL, 32.00, 19.50, 1, 'ativo', '7891027101039', 2),
(8, 'Lápis Grafite HB Nº 2 Faber-Castell Caixa c/ 12', 'Escrita & Escritório', 35, 10, NULL, 14.50, 7.20, 2, 'ativo', '7891027101046', 3),
(9, 'Borracha Branca com Cinta Plástica Mercur', 'Escolar', 80, 25, NULL, 2.50, 0.90, 3, 'ativo', '7891027101053', 4),
(10, 'Apontador com Depósito Faber-Castell', 'Escolar', 23, 10, NULL, 5.90, 2.40, 2, 'ativo', '7891027101060', 4),
(11, 'Marca-Texto Amarelo Fluorescente', 'Escrita & Escritório', 50, 15, '2028-06-30', 4.90, 2.10, 2, 'ativo', '7891027101077', 3),
(12, 'Cola Bastão 40g Pritt', 'Escolar', 30, 10, '2027-10-31', 11.90, 5.80, 3, 'ativo', '7891027101084', 4),
(13, 'Tesoura Escolar Sem Ponta Mundial', 'Escolar', 40, 10, NULL, 7.90, 3.50, 3, 'ativo', '7891027101091', 4),
(14, 'Régua Cristal 30cm Waleu', 'Desenho & Técnico', 65, 15, NULL, 3.00, 1.10, 3, 'ativo', '7891027101107', 7),
(15, 'Tinta Guache 6 Cores 15ml Acrilex', 'Artes & Pintura', 28, 10, '2027-08-30', 8.90, 4.20, 3, 'ativo', '7891027101114', 5),
(16, 'Caixa de Lápis de Cor 24 Cores Ecolápis', 'Artes & Pintura', 16, 8, NULL, 29.90, 16.00, 3, 'ativo', '7891027101121', 5),
(17, 'Pasta Suspensa Kraft Dello', 'Organização', 55, 15, NULL, 4.50, 1.80, 1, 'ativo', '7891027101138', 6),
(18, 'Grampeador de Mesa Médio 26/6', 'Organização', 12, 5, NULL, 26.50, 14.00, 2, 'ativo', '7891027101145', 6),
(19, 'Bloco de Notas Adesivas Post-it 76x76mm', 'Papelaria', 39, 15, NULL, 9.90, 4.50, 1, 'ativo', '7891027101152', 2);
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 6. Tabela: `lotes`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `lotes`;
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

-- ----------------------------------------------------------------------------
-- 7. Tabela: `movimentacoes`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `movimentacoes`;
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `movimentacoes` WRITE;
INSERT INTO `movimentacoes` (`id`, `produto_id`, `tipo`, `quantidade`, `data_movimento`, `observacao`) VALUES 
(1, 1, 'entrada_compra', 120, '2026-06-03 21:36:30', 'Estoque inicial - Compra NFe 1001'),
(2, 2, 'entrada_compra', 10, '2026-06-03 21:36:30', 'Compra NFe 1002'),
(3, 3, 'entrada_compra', 20, '2026-06-03 21:36:30', 'Compra NFe 1003'),
(4, 4, 'entrada_compra', 80, '2026-06-03 21:36:30', 'Compra NFe 1004'),
(5, 2, 'saida_venda', 5, '2026-06-03 21:36:30', 'Vendas na loja'),
(6, 3, 'saida_venda', 2, '2026-06-03 21:36:30', 'Venda online'),
(7, 3, 'entrada_compra', 163, '2026-06-03 21:39:25', 'Ajuste Manual via Cadastro'),
(8, 1, 'entrada_compra', 1, '2026-06-06 13:56:17', 'Entrada de Compra #1 - Nota: S/N'),
(9, 1, 'saida_venda', 1, '2026-06-06 13:57:21', 'Venda PDV #3'),
(10, 1, 'entrada_compra', 1, '2026-06-06 13:58:06', 'Entrada de Compra #2 - Nota: S/N'),
(11, 4, 'saida_venda', 1, '2026-08-14 20:33:07', 'Venda PDV #4'),
(12, 4, 'entrada_compra', 0, '2026-08-14 20:33:47', 'Ajuste Manual via Cadastro'),
(13, 4, 'entrada_compra', 0, '2026-08-14 20:33:52', 'Ajuste Manual via Cadastro'),
(14, 4, 'entrada_compra', 1, '2026-08-14 20:34:09', 'Ajuste Manual via Cadastro'),
(15, 4, 'entrada_compra', 1, '2026-08-14 20:34:19', 'Ajuste Manual via Cadastro'),
(16, 4, 'entrada_compra', 0, '2026-08-14 20:34:24', 'Ajuste Manual via Cadastro'),
(17, 4, 'entrada_compra', 1, '2026-08-14 20:54:09', 'Ajuste Manual via Cadastro'),
(18, 2, 'saida_venda', 5, '2026-08-14 23:16:24', 'Venda PDV #5'),
(19, 5, 'saida_venda', 2, '2026-08-15 04:57:55', 'Venda PDV #6'),
(20, 6, 'saida_venda', 1, '2026-08-15 04:57:55', 'Venda PDV #6'),
(21, 7, 'saida_venda', 3, '2026-08-14 04:57:55', 'Venda PDV #7'),
(22, 11, 'saida_venda', 4, '2026-08-14 04:57:55', 'Venda PDV #7'),
(23, 16, 'saida_venda', 1, '2026-08-13 04:57:55', 'Venda PDV #8'),
(24, 15, 'saida_venda', 2, '2026-08-13 04:57:55', 'Venda PDV #8'),
(25, 19, 'saida_venda', 1, '2026-08-13 04:57:55', 'Venda PDV #8'),
(26, 18, 'saida_venda', 1, '2026-08-12 04:57:55', 'Venda PDV #9'),
(27, 17, 'saida_venda', 5, '2026-08-12 04:57:55', 'Venda PDV #9'),
(28, 5, 'saida_venda', 3, '2026-08-11 04:57:55', 'Venda PDV #10'),
(29, 8, 'saida_venda', 2, '2026-08-11 04:57:55', 'Venda PDV #10'),
(30, 7, 'saida_venda', 2, '2026-08-10 04:57:55', 'Venda PDV #11'),
(31, 12, 'saida_venda', 3, '2026-08-10 04:57:55', 'Venda PDV #11'),
(32, 13, 'saida_venda', 2, '2026-08-09 04:57:55', 'Venda PDV #12'),
(33, 14, 'saida_venda', 4, '2026-08-09 04:57:55', 'Venda PDV #12'),
(34, 9, 'saida_venda', 5, '2026-08-09 04:57:55', 'Venda PDV #12'),
(35, 16, 'saida_venda', 2, '2026-07-31 04:57:55', 'Venda PDV #13'),
(36, 5, 'saida_venda', 2, '2026-07-31 04:57:55', 'Venda PDV #13'),
(37, 7, 'saida_venda', 5, '2026-07-24 04:57:55', 'Venda PDV #14'),
(38, 6, 'saida_venda', 2, '2026-07-01 04:57:55', 'Venda PDV #15'),
(39, 18, 'saida_venda', 2, '2026-07-01 04:57:55', 'Venda PDV #15'),
(40, 19, 'saida_venda', 1, '2026-08-15 18:26:41', 'Venda PDV #16'),
(41, 10, 'saida_venda', 1, '2026-08-15 18:26:41', 'Venda PDV #16'),
(42, 10, 'saida_venda', 1, '2026-08-15 19:53:26', 'Venda PDV #17'),
(43, 16, 'saida_venda', 6, '2026-08-15 19:53:52', 'Venda PDV #18');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 8. Tabela: `compras`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `compras`;
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

LOCK TABLES `compras` WRITE;
INSERT INTO `compras` (`id`, `fornecedor_id`, `usuario_id`, `numero_nota`, `valor_total`, `tipo_pagamento`, `status`, `data_compra`) VALUES 
(1, 3, 1, '', 10.50, 'pix', 'PAGA', '2026-06-06 13:56:17'),
(2, 1, 1, '', 10.50, 'pix', 'PAGA', '2026-06-06 13:58:06');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 9. Tabela: `itens_compra`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `itens_compra`;
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

LOCK TABLES `itens_compra` WRITE;
INSERT INTO `itens_compra` (`id`, `compra_id`, `produto_id`, `quantidade`, `preco_unitario`, `subtotal`) VALUES 
(1, 1, 1, 1.000, 10.50, 10.50),
(2, 2, 1, 1.000, 10.50, 10.50);
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 10. Tabela: `vendas`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `vendas`;
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `vendas` WRITE;
INSERT INTO `vendas` (`id`, `cliente_id`, `data_venda`, `total`, `forma_pagamento`) VALUES 
(1, 1, '2026-06-03 21:36:30', 135.00, 'PIX'),
(2, 2, '2026-06-03 21:36:30', 25.00, 'Cartão de Crédito'),
(3, NULL, '2026-06-06 13:57:21', 25.90, 'DINHEIRO'),
(4, 3, '2026-08-14 20:33:07', 29.90, 'DINHEIRO'),
(5, NULL, '2026-08-14 23:16:24', 225.00, 'PIX'),
(6, NULL, '2026-08-15 04:57:55', 99.70, 'PIX'),
(7, NULL, '2026-08-14 04:57:55', 115.60, 'CARTÃO DE CRÉDITO'),
(8, NULL, '2026-08-13 04:57:55', 57.60, 'DINHEIRO'),
(9, NULL, '2026-08-12 04:57:55', 49.00, 'PIX'),
(10, NULL, '2026-08-11 04:57:55', 103.70, 'CARTÃO DE DÉBITO'),
(11, NULL, '2026-08-10 04:57:55', 99.70, 'PIX'),
(12, NULL, '2026-08-09 04:57:55', 40.30, 'DINHEIRO'),
(13, NULL, '2026-07-31 04:57:55', 109.60, 'CARTÃO DE CRÉDITO'),
(14, NULL, '2026-07-24 04:57:55', 160.00, 'PIX'),
(15, NULL, '2026-07-01 04:57:55', 152.80, 'DINHEIRO'),
(16, NULL, '2026-08-15 18:26:41', 15.80, 'PIX'),
(17, NULL, '2026-08-15 19:53:26', 5.90, 'DINHEIRO'),
(18, NULL, '2026-08-15 19:53:52', 179.40, 'DINHEIRO');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 11. Tabela: `vendas_itens`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `vendas_itens`;
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `vendas_itens` WRITE;
INSERT INTO `vendas_itens` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES 
(1, 1, 2, 3, 45.00),
(2, 2, 3, 2, 12.50),
(3, 3, 1, 1, 25.90),
(4, 4, 4, 1, 29.90),
(5, 5, 2, 5, 45.00),
(6, 6, 5, 2, 24.90),
(7, 6, 6, 1, 49.90),
(8, 7, 7, 3, 32.00),
(9, 7, 11, 4, 4.90),
(10, 8, 16, 1, 29.90),
(11, 8, 15, 2, 8.90),
(12, 8, 19, 1, 9.90),
(13, 9, 18, 1, 26.50),
(14, 9, 17, 5, 4.50),
(15, 10, 5, 3, 24.90),
(16, 10, 8, 2, 14.50),
(17, 11, 7, 2, 32.00),
(18, 11, 12, 3, 11.90),
(19, 12, 13, 2, 7.90),
(20, 12, 14, 4, 3.00),
(21, 12, 9, 5, 2.50),
(22, 13, 16, 2, 29.90),
(23, 13, 5, 2, 24.90),
(24, 14, 7, 5, 32.00),
(25, 15, 6, 2, 49.90),
(26, 15, 18, 2, 26.50),
(27, 16, 19, 1, 9.90),
(28, 16, 10, 1, 5.90),
(29, 17, 10, 1, 5.90),
(30, 18, 16, 6, 29.90);
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 12. Tabela: `cupons_fiscais`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `cupons_fiscais`;
CREATE TABLE `cupons_fiscais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `chave_acesso` varchar(44) NOT NULL,
  `data_emissao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave_acesso` (`chave_acesso`),
  KEY `venda_id` (`venda_id`),
  CONSTRAINT `cupons_fiscais_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `cupons_fiscais` WRITE;
INSERT INTO `cupons_fiscais` (`id`, `venda_id`, `chave_acesso`, `data_emissao`) VALUES 
(1, 1, '35260304148243000116650010000000011000000015', '2026-06-03 21:36:30'),
(2, 2, '35260304148243000116650010000000021000000021', '2026-06-03 21:36:30'),
(3, 3, '35260300000000000000650010000000031000000009', '2026-06-06 13:57:21'),
(4, 4, '35260300000000000000650010000000041000000008', '2026-08-14 20:33:07'),
(5, 5, '35260300000000000000650010000000051000000006', '2026-08-14 23:16:24'),
(6, 6, '35260000000000000000000000000000000000000006', '2026-08-15 04:57:55'),
(7, 7, '35260000000000000000000000000000000000000007', '2026-08-14 04:57:55'),
(8, 8, '35260000000000000000000000000000000000000008', '2026-08-13 04:57:55'),
(9, 9, '35260000000000000000000000000000000000000009', '2026-08-12 04:57:55'),
(10, 10, '35260000000000000000000000000000000000000010', '2026-08-11 04:57:55'),
(11, 11, '35260000000000000000000000000000000000000011', '2026-08-10 04:57:55'),
(12, 12, '35260000000000000000000000000000000000000012', '2026-08-09 04:57:55'),
(13, 13, '35260000000000000000000000000000000000000013', '2026-07-31 04:57:55'),
(14, 14, '35260000000000000000000000000000000000000014', '2026-07-24 04:57:55'),
(15, 15, '35260000000000000000000000000000000000000015', '2026-07-01 04:57:55'),
(16, 16, '35260300000000000000650010000000161000000008', '2026-08-15 18:26:41'),
(17, 17, '35260300000000000000650010000000171000000008', '2026-08-15 19:53:26'),
(18, 18, '35260300000000000000650010000000181000000009', '2026-08-15 19:53:52');
UNLOCK TABLES;

-- ----------------------------------------------------------------------------
-- 13. Tabela: `logs`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `logs`;
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
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
