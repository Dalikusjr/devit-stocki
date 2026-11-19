-- DevIt | Stocki : base de données (structure et données) à jour
-- Migrations 001 à 003 déjà appliquées. Les accès projets sont chiffrés :
-- il faut la même APP_KEY (fichier .env) que celle de l'installation d'origine.
-- Import : mysql -u <utilisateur> -p db_devitstocki < database/db_devitstocki.sql

-- MySQL dump 10.13  Distrib 8.4.10, for Linux (x86_64)
--
-- Host: localhost    Database: db_devitstocki
-- ------------------------------------------------------
-- Server version	8.4.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `idclient` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `adresse` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idclient`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'Jean Dupont','jean.dupont@email.com','0102030405','12 rue de Paris, Paris'),(2,'Marie Durand','marie.durand@email.com','0607080910','45 Avenue Lyon, Lyon'),(3,'Ahmed Mansour','ahmed.m@email.com','0708091122','Boulevard Sousse, Tunisie'),(4,'Sophie Lefebvre','sophie.l@email.com','0504030201','8 impasse Lille, Lille'),(14,'dali bettaieb','dali.snoop@gmail.com','24846066','rue22'),(101,'Sondes Belhadj','sondes.belhadj@mail.tn','037020487','108 Avenue de Carthage, Kairouan'),(102,'Achraf Ben Slama','achraf.ben.slama@mail.tn','093230543','130 Rue de la République, Bizerte'),(104,'Selim Bahri','selim.bahri@mail.tn','099123680','102 Rue Hedi Chaker, Ariana');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historic`
--

DROP TABLE IF EXISTS `historic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historic` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `parent_type` enum('maintain','project','stocki','stocki_premium') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'project',
  `montant` float NOT NULL DEFAULT '0',
  `id_client` int NOT NULL,
  `payed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pk_hist_client` (`id_client`),
  KEY `idx_payment_lookup` (`parent_id`,`parent_type`) USING BTREE,
  CONSTRAINT `pk_hist_client` FOREIGN KEY (`id_client`) REFERENCES `clients` (`idclient`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historic`
--

LOCK TABLES `historic` WRITE;
/*!40000 ALTER TABLE `historic` DISABLE KEYS */;
INSERT INTO `historic` VALUES (30,7,'maintain',200,2,'2026-11-16 21:15:51'),(31,3,'maintain',500,14,'2026-11-16 21:16:00');
/*!40000 ALTER TABLE `historic` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintains`
--

DROP TABLE IF EXISTS `maintains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintains` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_proj` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_maintain_project` (`id_proj`),
  CONSTRAINT `fk_maintain_project` FOREIGN KEY (`id_proj`) REFERENCES `projects` (`idproj`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintains`
--

LOCK TABLES `maintains` WRITE;
/*!40000 ALTER TABLE `maintains` DISABLE KEYS */;
INSERT INTO `maintains` VALUES (7,2),(3,13),(6,321);
/*!40000 ALTER TABLE `maintains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `idpay` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `parent_type` enum('maintain','project','stocki','stocki_premium') NOT NULL DEFAULT 'project',
  `echeance` double NOT NULL,
  `commentaire` varchar(100) NOT NULL,
  `dateech` date NOT NULL,
  `status` enum('paid','unpaid') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `datepay` date DEFAULT NULL,
  PRIMARY KEY (`idpay`),
  KEY `idx_parent_lookup` (`parent_id`,`parent_type`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (2,2,'project',1250,'Première tranche','2026-03-01','paid','2026-04-01'),(3,2,'project',1250,'Solde final','2026-03-25','paid','2026-04-14'),(8,13,'project',5000,'avance','2026-04-02','paid','2026-04-02'),(10,1,'project',1500,'Acompte initial','2026-07-03','paid','2026-07-31'),(12,2,'project',1200,'Frais de mise en service','2025-01-15','unpaid',NULL),(13,2,'project',130,'Échéance trimestrielle','2025-05-01','paid','2026-05-01'),(14,13,'project',850,'Licence Angular annuelle','2025-03-01','paid','2025-03-01'),(15,13,'project',450,'Support technique','2025-06-01','unpaid',NULL),(16,126,'project',5000,'Audit de phase 1','2025-02-20','paid','2025-02-20'),(18,320,'project',1250.75,'Configuration Système de paie','2025-01-05','paid','2025-01-05'),(19,320,'project',1250.75,'Maintenance Janvier','2025-02-05','unpaid',NULL),(22,322,'project',15000,'Installation ERP industriel','2025-01-20','paid','2025-01-20'),(23,322,'project',7500,'Formation utilisateurs','2025-03-20','unpaid',NULL),(24,323,'project',3200,'Setup CRM Premium','2025-02-15','paid','2025-02-15'),(25,323,'project',900,'Abonnement mensuel CRM','2025-03-15','unpaid',NULL),(27,325,'project',1500,'Module Contrôle Qualité','2025-04-05','unpaid',NULL),(28,326,'project',6000,'Portail Investisseurs','2025-02-10','paid','2025-02-10'),(47,321,'project',454,'dfh','2026-07-15','paid','2026-07-15'),(48,1,'project',2500,'gh','2026-07-24','unpaid',NULL),(49,126,'project',2000,'dhndfhn','2026-07-18','paid','2026-07-18'),(50,126,'project',7555.25,'fegnhhg,','2026-07-30','paid','2026-07-30'),(65,7,'maintain',200,'projet 3','2026-11-01','paid','2026-11-01'),(66,3,'maintain',500,'projet 4','2026-11-01','paid','2026-11-01'),(67,6,'maintain',750,'projet 5','2026-11-01','unpaid',NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `idproj` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(40) NOT NULL,
  `datedebut` date NOT NULL,
  `daterenouv` date NOT NULL,
  `couttot` double NOT NULL,
  `acces` text NOT NULL,
  `idclient` int NOT NULL,
  `status` enum('active','completed','archived') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`idproj`),
  KEY `fk_projets_clients` (`idclient`),
  CONSTRAINT `fk_projets_clients` FOREIGN KEY (`idclient`) REFERENCES `clients` (`idclient`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=460 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Boutique Bio en ligne 2','2026-01-15','2027-01-15',4000,'enc:v1:HNzgJGnbAC4EImq/E4pBqcoLqP79E8fXQ+sVuK0+/SB3TqHuEoTSUkVDoKqxoQFELeopBr/zpMQ9OIXlGfAgTIswst2vqNcHGjT9jEf7tWX1eF6lCuWhwgNKyk1Umrk=',1,'active'),(2,'App Fitness Lyon','2026-03-01','2027-03-01',5000,'enc:v1:47bQMYE5OFcqtPfmmRe3/bSLvG9ck1O634G7Ety4WlZp5W6l+NnInRhtAkpAB8+ukA3kZKJly+qeOJ9acVLFnTaRpHBQrL8pxZE/cEVk7Yle7VuYrXRj0nFRr+ByJpU5',2,'active'),(13,'angular','2026-04-08','2026-04-16',6300,'enc:v1:nTzNAst5AGYoNCCaEwrSy0xuUVm8TOUeBDHziW5A6EKlx9GOjQ==',14,'active'),(126,'Refonte site web','2023-01-02','2025-01-02',14555.28,'enc:v1:44swTcPtOv6yuGLQfmFWqyiACv78zEln9ko9OOJ667u5IVvM8xw5Kz68AD6SmQVA0N4nIQjro+IqPVVQeVY++KHf8LGFkApuVf8MOUGF4N56Ztgfr4BrwIn7qHg=',1,'archived'),(320,'Système de paie','2024-02-18','2025-02-18',21927.3,'enc:v1:kQo58E/416o6bfu+aLdsvD6Ji9T+rEAfBoEiS7CGy8r++JQ0idN0eYiPIQ/N+AtK1LXuMVp0L8TWZxN0VivSad67IuS3KuZRX1o0GR5F9xWiQIJcH2FTIA==',14,'active'),(321,'Application mobile RH','2023-08-05','2024-08-05',6907.09,'enc:v1:Kv5xogh81+w3Gq+zKfYo56CmCWpmqVt3ymukG2m7MCjFL1bUb9vcorb9VW1qjB8VppFYe8SS/X/nuNfy7b4KuIziUQ6PwC8xyq2UGo64',2,'active'),(322,'ERP industriel','2021-02-27','2024-02-27',49471.59,'enc:v1:5KwNKY3iNJ5ThyW7tJygynQ24tU2ai23MubxFZ78BH1sJTlZcpUz526Z3SwIDJ83RLCATlXO1J3W8q611J5AiRYibBsQDHHySW19RHT3tA==',3,'active'),(323,'CRM client premium','2022-08-28','2025-08-28',42074.38,'enc:v1:0Q+xm3WxFwxsYDOGcvcPDtOgd/R/QUYfODwDCVYTPXnjgeEzYWF8DkyJ3Txqf/7POprELGvJfS+J7SgsyizUgSGjrDyNb7Xhlnvh+Wr1nxFo',4,'active'),(325,'Module contrôle qualité','2023-10-01','2024-10-01',34386.42,'enc:v1:vJp80oDDTqLIP2ghStCyx6Yr8K+2xU5+gVrk68V5kCaKOIZ7L0Z8rqy2OisIGpxJbI61/UxhPHVRsYMQYP62RfvI/bRo1GYpzpfl57vuD+PDQSZDLoy7Obe08I5blFI=',101,'active'),(326,'Portail investisseurs','2023-11-04','2025-05-04',6000,'enc:v1:3HRKECdNTCnJ79lDiSj5CLKaxnLtyTkqb0bMfuNieM4jhjz5rYYNN9pkjc6DmtrK2e/jgDUpbA2JZpvrud7jpHq5A4hS3q+gu+h+Wb8kP5z6jIldPz+5ip8=',102,'completed'),(328,'Système dispatch techniciens','2024-06-04','2024-12-04',14873.22,'enc:v1:71/rH2LCv5rRXG51j+/BFuYWX9FqsYc03zAg6R/L+BiNhZwp3bhIqdedT7LrkpCe1eAefeGoWTxNHm75ALHU79Chnsih9qn+Fq9/zUnV0jCA/LspzXx5RZf7Zf+K0XM=',104,'archived');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stocki`
--

DROP TABLE IF EXISTS `stocki`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stocki` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `payment_Type` enum('mensuel','trimestriel','annuel') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'mensuel',
  `startDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stocki_client` (`id_client`),
  CONSTRAINT `fk_stocki_client` FOREIGN KEY (`id_client`) REFERENCES `clients` (`idclient`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stocki`
--

LOCK TABLES `stocki` WRITE;
/*!40000 ALTER TABLE `stocki` DISABLE KEYS */;
/*!40000 ALTER TABLE `stocki` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stocki_premium`
--

DROP TABLE IF EXISTS `stocki_premium`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stocki_premium` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `montant` float NOT NULL,
  `datedebut` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stockip_client` (`id_client`),
  CONSTRAINT `fk_stockip_client` FOREIGN KEY (`id_client`) REFERENCES `clients` (`idclient`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stocki_premium`
--

LOCK TABLES `stocki_premium` WRITE;
/*!40000 ALTER TABLE `stocki_premium` DISABLE KEYS */;
/*!40000 ALTER TABLE `stocki_premium` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `iduser` int NOT NULL AUTO_INCREMENT,
  `email` varchar(40) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nom` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `role` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `tel` varchar(20) NOT NULL,
  PRIMARY KEY (`iduser`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'dali.snoop@gmail.com','$2y$12$GcIIX.2cLg7hiEC8tXRvB.xIsVWcetBdAefXkv/CwDLf.GlYbkm6a','Bettaieb','Mohamed ali','Manager',1,'24846066'),(7,'test@test.com','$2y$12$sC9IbpW1bvbaGd5iRmHRJO4ML8mHZJ4VUpppapopXeGFEt/GEG0Ne','test','test','Directeur IT',1,'testffff');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'db_devitstocki'
--

--
-- Dumping routines for database 'db_devitstocki'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
