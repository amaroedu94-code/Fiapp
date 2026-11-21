CREATE DATABASE  IF NOT EXISTS `fiapp` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `fiapp`;
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: fiapp
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alimentos`
--

DROP TABLE IF EXISTS `alimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `unidade_medida` varchar(30) NOT NULL,
  `marca_preferencia` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `alimentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alimentos`
--

LOCK TABLES `alimentos` WRITE;
/*!40000 ALTER TABLE `alimentos` DISABLE KEYS */;
INSERT INTO `alimentos` VALUES (1,1,'Leite','Laticínios','L','Marca A'),(2,1,'Queijo','Laticínios','kg','Marca B'),(3,2,'Frango','Carnes','kg','Marca C'),(4,2,'Alface','Vegetais','un','Horta X'),(5,3,'Maçã','Frutas','kg','Sítio Y'),(6,3,'Iogurte','Laticínios','un','Marca D'),(7,4,'Tomate','Vegetais','kg','Horta X'),(8,6,'Pão Integral','Padaria','un','Padaria Z'),(9,9,'Morango','Frutas','g',''),(10,9,'Alface','Verduras','unidade',''),(11,9,'Abacaxi','Frutas','Kg','TURMA DA MONICA'),(12,9,'uva passa','fruta','12',''),(14,9,'Tomate','Frutas','kg',''),(15,9,'Frango','Carnes','kg','Sadia'),(16,9,'Iogurte','Laticínios','unidade',''),(17,9,'Leite','Laticínios','litro','Tirol'),(18,9,'Maçã','Frutas','kg',''),(19,9,'Queijo','Laticínios','kg','Tirolez');
/*!40000 ALTER TABLE `alimentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogo_alimentos`
--

DROP TABLE IF EXISTS `catalogo_alimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogo_alimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogo_alimentos`
--

LOCK TABLES `catalogo_alimentos` WRITE;
/*!40000 ALTER TABLE `catalogo_alimentos` DISABLE KEYS */;
INSERT INTO `catalogo_alimentos` VALUES (10,'Abacaxi'),(4,'Alface'),(3,'Frango'),(6,'Iogurte'),(1,'Leite'),(5,'Maçã'),(9,'Morango'),(8,'Pão Integral'),(2,'Queijo'),(7,'Tomate'),(11,'uva passa');
/*!40000 ALTER TABLE `catalogo_alimentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estoque`
--

DROP TABLE IF EXISTS `estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque` (
  `id_item_estoque` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `alimento_id` int(11) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `data_validade` date NOT NULL,
  `data_cadastro` date NOT NULL,
  `observacoes` varchar(200) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_item_estoque`),
  KEY `usuario_id` (`usuario_id`),
  KEY `alimento_id` (`alimento_id`),
  CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `estoque_ibfk_2` FOREIGN KEY (`alimento_id`) REFERENCES `alimentos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque`
--

LOCK TABLES `estoque` WRITE;
/*!40000 ALTER TABLE `estoque` DISABLE KEYS */;
INSERT INTO `estoque` VALUES (1,1,1,2.00,'2026-11-03','2026-10-27','Carga inicial',NULL),(2,1,2,0.50,'2026-11-11','2026-10-27','Carga inicial',NULL),(3,2,3,1.50,'2026-10-30','2026-10-27','Carga inicial',NULL),(4,2,4,3.00,'2026-10-29','2026-10-27','Carga inicial',NULL),(5,3,5,1.00,'2026-11-06','2026-10-27','Carga inicial',NULL),(6,3,6,6.00,'2026-11-01','2026-10-27','Carga inicial',NULL),(7,4,7,2.00,'2026-10-31','2026-10-27','Carga inicial',NULL),(8,6,8,4.00,'2026-11-02','2026-10-27','Carga inicial',NULL),(23,9,10,2.00,'2026-10-09','0000-00-00','',''),(24,9,11,1.00,'2026-11-10','0000-00-00','',''),(25,9,9,10.00,'2026-11-16','0000-00-00','',''),(26,9,14,1.00,'2026-12-30','0000-00-00','',''),(27,9,15,2.00,'2026-11-16','0000-00-00','Congelar se não for usar logo','Korin'),(28,9,16,5.00,'2026-11-12','0000-00-00','',''),(29,9,17,2.00,'2026-11-25','0000-00-00','','Piracanjuba'),(30,9,18,5.00,'2026-11-20','0000-00-00','',''),(31,9,19,0.50,'2026-11-30','0000-00-00','Mussarela fatiada','Tirolez');
/*!40000 ALTER TABLE `estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estoque_receita`
--

DROP TABLE IF EXISTS `estoque_receita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_receita` (
  `estoque_id` int(11) NOT NULL,
  `receita_id` int(11) NOT NULL,
  PRIMARY KEY (`estoque_id`,`receita_id`),
  KEY `receita_id` (`receita_id`),
  CONSTRAINT `estoque_receita_ibfk_1` FOREIGN KEY (`estoque_id`) REFERENCES `estoque` (`id_item_estoque`),
  CONSTRAINT `estoque_receita_ibfk_2` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id_receita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque_receita`
--

LOCK TABLES `estoque_receita` WRITE;
/*!40000 ALTER TABLE `estoque_receita` DISABLE KEYS */;
INSERT INTO `estoque_receita` VALUES (1,1),(2,5),(3,2),(4,1),(5,3),(6,6),(7,4),(8,8);
/*!40000 ALTER TABLE `estoque_receita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredientes`
--

DROP TABLE IF EXISTS `ingredientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredientes` (
  `id_ingrediente_receita` int(11) NOT NULL AUTO_INCREMENT,
  `receita_id` int(11) NOT NULL,
  `alimento_id` int(11) NOT NULL,
  `quantidade_necessaria` decimal(10,2) NOT NULL,
  `unidade_medida` varchar(20) NOT NULL,
  `substitutos_possiveis` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_ingrediente_receita`),
  KEY `receita_id` (`receita_id`),
  KEY `fk_ingredientes_catalogo` (`alimento_id`),
  CONSTRAINT `fk_ingredientes_catalogo` FOREIGN KEY (`alimento_id`) REFERENCES `catalogo_alimentos` (`id`),
  CONSTRAINT `ingredientes_ibfk_1` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id_receita`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredientes`
--

LOCK TABLES `ingredientes` WRITE;
/*!40000 ALTER TABLE `ingredientes` DISABLE KEYS */;
INSERT INTO `ingredientes` VALUES (1,1,4,1.00,'un','Rúcula'),(2,2,3,0.50,'kg','Peito de peru'),(3,3,5,0.30,'kg','Banana'),(4,3,1,0.20,'L','Leite vegetal'),(5,4,7,1.00,'kg','Tomate pelado'),(6,5,2,0.10,'kg','Muçarela'),(7,6,6,1.00,'un','Iogurte natural'),(8,1,7,2.00,'un','Pepino'),(9,6,5,0.20,'kg','Banana'),(10,7,7,1.00,'kg','Abóbora'),(11,8,3,0.30,'kg','Peito de peru');
/*!40000 ALTER TABLE `ingredientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receitas`
--

DROP TABLE IF EXISTS `receitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `receitas` (
  `id_receita` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `modo_preparo` varchar(1000) NOT NULL,
  `tempo_preparo` int(11) DEFAULT NULL,
  `nivel_dificuldade` varchar(20) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `porcoes` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_receita`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receitas`
--

LOCK TABLES `receitas` WRITE;
/*!40000 ALTER TABLE `receitas` DISABLE KEYS */;
INSERT INTO `receitas` VALUES (1,'Salada Mista','Lave bem a alface e os tomates. Corte os tomates em pedaços e rasgue as folhas de alface. Coloque tudo em uma tigela, tempere com sal, azeite e limão a gosto. Misture delicadamente e sirva.',10,'Fácil','Saladas',2),(2,'Frango Grelhado','Tempere o frango com sal, alho e outros temperos de sua preferência. Aqueça uma frigideira com um pouco de óleo ou azeite. Coloque o frango e grelhe dos dois lados até ficar bem cozido. Retire do fogo e sirva.',25,'Fácil','Carnes',2),(3,'Vitamina de Maçã','Lave, descasque e corte a maçã em pedaços. Coloque no liquidificador junto com o leite e bata até obter uma mistura cremosa. Se preferir, adicione gelo e bata novamente. Sirva em seguida.',5,'Fácil','Bebidas',1),(4,'Molho de Tomate','Lave os tomates e corte em pedaços pequenos. Coloque em uma panela com um fio de azeite e temperos a gosto. Cozinhe em fogo baixo, mexendo de vez em quando, até os tomates ficarem macios. Amasse ou bata para obter um molho mais uniforme.',30,'Médio','Molhos',4),(5,'Omelete de Queijo','Quebre os ovos em uma tigela e bata com um garfo. Adicione sal e misture. Aqueça uma frigideira untada, despeje os ovos e espalhe o queijo por cima. Cozinhe em fogo baixo até firmar e dobre a omelete ao meio. Sirva ainda quente.',10,'Fácil','Café',1),(6,'Iogurte com Frutas','Coloque o iogurte em uma tigela. Lave e corte as frutas em pedaços pequenos. Acrescente as frutas ao iogurte e misture delicadamente. Se desejar, adicione aveia ou mel para complementar. Sirva gelado.',5,'Fácil','Sobremesas',2),(7,'Sopa de Legumes','Lave e corte os legumes em pedaços pequenos. Coloque-os em uma panela com água e temperos a gosto. Cozinhe em fogo médio até ficarem macios. Se desejar uma sopa cremosa, bata parte dos legumes no liquidificador e misture novamente na panela.',35,'Médio','Sopas',4),(8,'Panqueca de Frango','Cozinhe o frango e desfie em pedaços pequenos. Prepare a massa misturando ovos, leite, farinha e uma pitada de sal até ficar homogênea. Despeje pequenas porções em uma frigideira untada e doure dos dois lados. Recheie com o frango desfiado, enrole e sirva.',40,'Médio','Pratos',3);
/*!40000 ALTER TABLE `receitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` date NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Maria Silva','maria@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000001','2026-10-27',1),(2,'João Souza','joao@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000002','2026-10-27',1),(3,'Ana Lima','ana@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000003','2026-10-27',1),(4,'Pedro Costa','pedro@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000004','2026-10-27',1),(5,'Carla Dias','carla@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000005','2026-10-27',1),(6,'Lucas Rocha','lucas@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000006','2026-10-27',1),(7,'Fernanda Melo','fernanda@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000007','2026-10-27',1),(8,'Rafael Nunes','rafael@fiapp.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','41990000008','2026-10-27',1),(9,'Fiapp','fiapp10@gmail.com','$2y$10$v2lvSBGoukFGqAzcSc1ED.oZkjNZcUAkrZ5HoNhUjHE3osv4s7Dqq',NULL,'0000-00-00',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vencimentos`
--

DROP TABLE IF EXISTS `vencimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vencimentos` (
  `id_aviso` int(11) NOT NULL AUTO_INCREMENT,
  `estoque_id` int(11) NOT NULL,
  `data_alerta` datetime NOT NULL,
  `status` enum('Pendente','Visualizado','Resolvido') NOT NULL DEFAULT 'Pendente',
  `descricao` varchar(200) DEFAULT NULL,
  `data_resolucao` datetime DEFAULT NULL,
  PRIMARY KEY (`id_aviso`),
  KEY `estoque_id` (`estoque_id`),
  CONSTRAINT `vencimentos_ibfk_1` FOREIGN KEY (`estoque_id`) REFERENCES `estoque` (`id_item_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vencimentos`
--

LOCK TABLES `vencimentos` WRITE;
/*!40000 ALTER TABLE `vencimentos` DISABLE KEYS */;
INSERT INTO `vencimentos` VALUES (1,4,'2026-10-27 20:31:38','Pendente','Alface vence em 2 dias',NULL),(2,3,'2026-10-27 20:31:38','Pendente','Frango vence em 3 dias',NULL),(3,7,'2026-10-27 20:31:38','Visualizado','Tomate vence em 4 dias',NULL),(4,6,'2026-10-27 20:31:38','Pendente','Iogurte vence em 5 dias',NULL),(5,1,'2026-10-27 20:31:38','Resolvido','Leite consumido','2026-10-27 20:31:38'),(6,5,'2026-10-27 20:31:38','Pendente','Maçã vence em 10 dias',NULL),(7,2,'2026-10-27 20:31:38','Pendente','Queijo vence em 15 dias',NULL),(8,8,'2026-10-27 20:31:38','Visualizado','Pão vence em 6 dias',NULL);
/*!40000 ALTER TABLE `vencimentos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-20 13:15:46
