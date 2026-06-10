-- MySQL dump 10.13  Distrib 8.0.33, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: restobenin
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avis` (
  `id_avis` int(11) NOT NULL AUTO_INCREMENT,
  `id_plat` int(11) NOT NULL,
  `nom_client` varchar(100) DEFAULT NULL,
  `telephone_client` varchar(20) DEFAULT NULL,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_avis`),
  KEY `fk_avis_plat` (`id_plat`),
  CONSTRAINT `fk_avis_plat` FOREIGN KEY (`id_plat`) REFERENCES `plats` (`id_plat`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
INSERT INTO `avis` VALUES (38,34,'Mariette',NULL,5,'Très bon service, rapide et efficace.','2026-06-08 22:58:18'),(39,34,'Anicet',NULL,5,'Le ragoût d\'igname est tout simplement un délice !','2026-06-08 22:58:48');
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;

--
-- Table structure for table `categories_plats`
--

DROP TABLE IF EXISTS `categories_plats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories_plats` (
  `id_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ordre_affichage` int(11) DEFAULT 0,
  PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories_plats`
--

/*!40000 ALTER TABLE `categories_plats` DISABLE KEYS */;
INSERT INTO `categories_plats` VALUES (1,'Plats traditionnels','Les spécialités culinaires typiques du Bénin préparées selon les recettes locales.',1),(2,'Grillades','Viandes et poissons grillés accompagnés de garnitures variées.',2),(3,'Fast Food','Burgers, pizzas, sandwichs et autres repas rapides.',3),(4,'Boissons','Jus naturels, sodas, cocktails et boissons rafraîchissantes.',4),(5,'Desserts','Gâteaux, glaces, fruits et autres douceurs pour terminer le repas.',5),(6,'Petit-déjeuner','Repas matinaux comprenant café, thé, pain, omelettes et viennoiseries.',6),(7,'Végétarien','Plats préparés sans viande ni poisson pour les amateurs de cuisine végétale.',7);
/*!40000 ALTER TABLE `categories_plats` ENABLE KEYS */;

--
-- Table structure for table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commandes` (
  `id_commande` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('sur_place','emporter','livraison') NOT NULL,
  `id_table` int(11) DEFAULT NULL,
  `nom_client` varchar(100) NOT NULL,
  `telephone_client` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `adresse_livraison` text DEFAULT NULL,
  `statut` enum('en_attente','en_cuisine','prete','livree','annulee') DEFAULT 'en_attente',
  `montant_total` decimal(10,2) DEFAULT 0.00,
  `date_heure` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_commande`),
  KEY `fk_commande_table` (`id_table`),
  CONSTRAINT `fk_commande_table` FOREIGN KEY (`id_table`) REFERENCES `tables_restaurant` (`id_table`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commandes`
--

/*!40000 ALTER TABLE `commandes` DISABLE KEYS */;
INSERT INTO `commandes` VALUES (1,'sur_place',NULL,'Arnaud Koffi','+22997001111','arnaud@gmail.com',NULL,'livree',6000.00,'2026-06-02 19:38:36'),(2,'sur_place',NULL,'Marie Adjovi','+22997001112','marie@gmail.com',NULL,'prete',6500.00,'2026-06-02 19:38:36'),(3,'emporter',NULL,'David Toko','+22997001113','david@gmail.com',NULL,'livree',4500.00,'2026-06-02 19:38:36'),(4,'livraison',NULL,'Sonia Mensah','+22997001114','sonia@gmail.com','Fidjrossè, Cotonou','prete',8500.00,'2026-06-02 19:38:36'),(5,'sur_place',NULL,'Jean Houngbo','+22997001115','jean@gmail.com',NULL,'livree',3000.00,'2026-06-02 19:38:36'),(6,'emporter',NULL,'Clarisse Dossa','+22997001116','clarisse@gmail.com',NULL,'prete',5500.00,'2026-06-02 19:38:36'),(7,'livraison',NULL,'Paul Ahouanvoe','+22997001117','paul@gmail.com','Akpakpa, Cotonou','livree',7000.00,'2026-06-02 19:38:36'),(8,'sur_place',NULL,'Michel Boko','+22997001118','michel@gmail.com',NULL,'annulee',5000.00,'2026-06-02 19:38:36'),(9,'sur_place',NULL,'Arnaud Koffi','+22997001111','arnaud@gmail.com',NULL,'livree',6000.00,'2026-06-02 19:38:46'),(10,'sur_place',NULL,'Marie Adjovi','+22997001112','marie@gmail.com',NULL,'prete',6500.00,'2026-06-02 19:38:46'),(11,'emporter',NULL,'David Toko','+22997001113','david@gmail.com',NULL,'livree',4500.00,'2026-06-02 19:38:46'),(12,'livraison',NULL,'Sonia Mensah','+22997001114','sonia@gmail.com','Fidjrossè, Cotonou','prete',8500.00,'2026-06-02 19:38:46'),(13,'sur_place',NULL,'Jean Houngbo','+22997001115','jean@gmail.com',NULL,'livree',3000.00,'2026-06-02 19:38:46'),(14,'emporter',NULL,'Clarisse Dossa','+22997001116','clarisse@gmail.com',NULL,'prete',5500.00,'2026-06-02 19:38:46'),(15,'livraison',NULL,'Paul Ahouanvoe','+22997001117','paul@gmail.com','Akpakpa, Cotonou','livree',7000.00,'2026-06-02 19:38:46'),(16,'sur_place',NULL,'Michel Boko','+22997001118','michel@gmail.com',NULL,'annulee',5000.00,'2026-06-02 19:38:46'),(17,'sur_place',NULL,'Arnaud Koffi','+22997001111','arnaud@gmail.com',NULL,'prete',6000.00,'2026-06-02 19:46:16'),(18,'sur_place',NULL,'Marie Adjovi','+22997001112','marie@gmail.com',NULL,'prete',6500.00,'2026-06-02 19:46:16'),(19,'emporter',NULL,'David Toko','+22997001113','david@gmail.com',NULL,'livree',4500.00,'2026-06-02 19:46:16'),(20,'livraison',NULL,'Sonia Mensah','+22997001114','sonia@gmail.com','Fidjrossè, Cotonou','prete',8500.00,'2026-06-02 19:46:16'),(21,'sur_place',NULL,'Jean Houngbo','+22997001115','jean@gmail.com',NULL,'livree',3000.00,'2026-06-02 19:46:16'),(22,'emporter',NULL,'Clarisse Dossa','+22997001116','clarisse@gmail.com',NULL,'prete',5500.00,'2026-06-02 19:46:16'),(23,'livraison',NULL,'Paul Ahouanvoe','+22997001117','paul@gmail.com','Akpakpa, Cotonou','livree',7000.00,'2026-06-02 19:46:16'),(24,'sur_place',NULL,'Michel Boko','+22997001118','michel@gmail.com',NULL,'annulee',5000.00,'2026-06-02 19:46:16'),(25,'sur_place',NULL,'Arnaud Koffi','+22997001111','arnaud@gmail.com',NULL,'livree',6000.00,'2026-06-05 14:10:17'),(26,'sur_place',NULL,'Marie Adjovi','+22997001112','marie@gmail.com',NULL,'prete',6500.00,'2026-06-05 14:10:17'),(27,'emporter',NULL,'David Toko','+22997001113','david@gmail.com',NULL,'livree',4500.00,'2026-06-05 14:10:17'),(28,'livraison',NULL,'Sonia Mensah','+22997001114','sonia@gmail.com','Fidjrossè, Cotonou','prete',8500.00,'2026-06-05 14:10:17'),(29,'sur_place',NULL,'Jean Houngbo','+22997001115','jean@gmail.com',NULL,'livree',3000.00,'2026-06-05 14:10:17'),(30,'emporter',NULL,'Clarisse Dossa','+22997001116','clarisse@gmail.com',NULL,'prete',5500.00,'2026-06-05 14:10:17'),(31,'livraison',NULL,'Paul Ahouanvoe','+22997001117','paul@gmail.com','Akpakpa, Cotonou','livree',7000.00,'2026-06-05 14:10:17'),(32,'sur_place',NULL,'Michel Boko','+22997001118','michel@gmail.com',NULL,'prete',5000.00,'2026-06-05 14:10:17'),(33,'sur_place',NULL,'Arnaud Koffi','+22997001111','arnaud@gmail.com',NULL,'livree',6000.00,'2026-06-05 14:10:19'),(34,'sur_place',NULL,'Marie Adjovi','+22997001112','marie@gmail.com',NULL,'prete',6500.00,'2026-06-05 14:10:19'),(35,'emporter',NULL,'David Toko','+22997001113','david@gmail.com',NULL,'livree',4500.00,'2026-06-05 14:10:19'),(36,'livraison',NULL,'Sonia Mensah','+22997001114','sonia@gmail.com','Fidjrossè, Cotonou','prete',8500.00,'2026-06-05 14:10:19'),(37,'sur_place',NULL,'Jean Houngbo','+22997001115','jean@gmail.com',NULL,'livree',3000.00,'2026-06-05 14:10:19'),(38,'emporter',NULL,'Clarisse Dossa','+22997001116','clarisse@gmail.com',NULL,'prete',5500.00,'2026-06-05 14:10:19'),(39,'livraison',NULL,'Paul Ahouanvoe','+22997001117','paul@gmail.com','Akpakpa, Cotonou','livree',7000.00,'2026-06-05 14:10:19'),(40,'sur_place',NULL,'Michel Boko','+22997001118','michel@gmail.com',NULL,'annulee',5000.00,'2026-06-05 14:10:19'),(41,'sur_place',NULL,'Arnaud Koffi','+22997001111','arnaud@gmail.com',NULL,'prete',6000.00,'2026-06-05 14:10:20'),(42,'sur_place',NULL,'Marie Adjovi','+22997001112','marie@gmail.com',NULL,'prete',6500.00,'2026-06-05 14:10:20'),(43,'emporter',NULL,'David Toko','+22997001113','david@gmail.com',NULL,'livree',4500.00,'2026-06-05 14:10:20'),(44,'livraison',NULL,'Sonia Mensah','+22997001114','sonia@gmail.com','Fidjrossè, Cotonou','prete',8500.00,'2026-06-05 14:10:20'),(45,'sur_place',NULL,'Jean Houngbo','+22997001115','jean@gmail.com',NULL,'livree',3000.00,'2026-06-05 14:10:20'),(46,'emporter',NULL,'Clarisse Dossa','+22997001116','clarisse@gmail.com',NULL,'prete',5500.00,'2026-06-05 14:10:20'),(47,'livraison',NULL,'Paul Ahouanvoe','+22997001117','paul@gmail.com','Akpakpa, Cotonou','livree',7000.00,'2026-06-05 14:10:20'),(48,'sur_place',NULL,'Michel Boko','+22997001118','michel@gmail.com',NULL,'en_cuisine',5000.00,'2026-06-05 14:10:20'),(52,'sur_place',5,'Farid DOSSA','14725836',NULL,NULL,'prete',5000.00,'2026-06-08 22:12:33'),(53,'sur_place',5,'Farid DOSSA','14725836',NULL,NULL,'prete',3000.00,'2026-06-08 22:16:00'),(54,'emporter',NULL,'Farid DOSSA','14725836',NULL,NULL,'prete',1500.00,'2026-06-08 22:27:31'),(55,'emporter',NULL,'Farid DOSSA','14725836','dossafarid1@gmail.com',NULL,'livree',1000.00,'2026-06-08 22:32:08'),(56,'emporter',NULL,'Farid DOSSA','14725836','dossafarid1@gmail.com',NULL,'prete',1500.00,'2026-06-08 22:41:18'),(57,'sur_place',1,'Farid DOSSA','14725836','dossafarid1@gmail.com',NULL,'en_attente',5000.00,'2026-06-08 22:52:30'),(58,'emporter',NULL,'Farid DOSSA','14725836','dossafarid1@gmail.com',NULL,'en_cuisine',5000.00,'2026-06-09 14:33:29'),(59,'sur_place',NULL,'Arnaud Koffi','+22997001111',NULL,'Cotonou','livree',8500.00,'2026-02-05 11:30:00'),(60,'sur_place',NULL,'Marie Dossou','+22996002222',NULL,'Abomey-Calavi','livree',12000.00,'2026-02-12 18:15:00'),(61,'sur_place',NULL,'Jean Houngbedji','+22995003333',NULL,'Porto-Novo','livree',6500.00,'2026-02-24 12:45:00'),(62,'sur_place',NULL,'Sonia Adjovi','+22994004444',NULL,'Cotonou','livree',14000.00,'2026-04-03 19:10:00'),(63,'sur_place',NULL,'Paul Hounkpatin','+22993005555',NULL,'Calavi','livree',9000.00,'2026-04-11 17:30:00'),(64,'sur_place',NULL,'Alice Kiki','+22992006666',NULL,'Akpakpa','livree',11000.00,'2026-04-22 13:20:00'),(65,'sur_place',NULL,'David Agossou','+22991007777',NULL,'Godomey','livree',7500.00,'2026-05-06 11:00:00'),(68,'sur_place',NULL,'Arnaud Koffi','+22997001111',NULL,'Cotonou','livree',8500.00,'2026-02-05 11:30:00'),(69,'sur_place',NULL,'Marie Dossou','+22996002222',NULL,'Abomey-Calavi','livree',12000.00,'2026-02-12 18:15:00'),(70,'sur_place',NULL,'Jean Houngbedji','+22995003333',NULL,'Porto-Novo','livree',6500.00,'2026-02-24 12:45:00'),(71,'sur_place',NULL,'Sonia Adjovi','+22994004444',NULL,'Cotonou','livree',14000.00,'2026-04-03 19:10:00'),(72,'sur_place',NULL,'Paul Hounkpatin','+22993005555',NULL,'Calavi','livree',9000.00,'2026-04-11 17:30:00'),(73,'sur_place',NULL,'Alice Kiki','+22992006666',NULL,'Akpakpa','livree',11000.00,'2026-04-22 13:20:00'),(77,'sur_place',NULL,'Arnaud Koffi','+22997001111',NULL,'Cotonou','livree',8500.00,'2026-02-05 11:30:00'),(78,'sur_place',NULL,'Marie Dossou','+22996002222',NULL,'Abomey-Calavi','livree',12000.00,'2026-02-12 18:15:00'),(79,'sur_place',NULL,'Jean Houngbedji','+22995003333',NULL,'Porto-Novo','livree',6500.00,'2026-02-24 12:45:00'),(80,'sur_place',NULL,'Sonia Adjovi','+22994004444',NULL,'Cotonou','livree',14000.00,'2026-04-03 19:10:00'),(81,'sur_place',NULL,'Paul Hounkpatin','+22993005555',NULL,'Calavi','livree',9000.00,'2026-04-11 17:30:00'),(82,'sur_place',NULL,'Alice Kiki','+22992006666',NULL,'Akpakpa','livree',11000.00,'2026-04-22 13:20:00'),(84,'sur_place',NULL,'Nadia Alao','+22990008888',NULL,'Fidjrossè','livree',16000.00,'2026-05-15 20:00:00'),(85,'sur_place',NULL,'Franck Ahouansou','+22998009999',NULL,'Porto-Novo','livree',13500.00,'2026-05-28 18:40:00'),(86,'sur_place',NULL,'Arnaud Koffi','+22997001111',NULL,'Cotonou','livree',8500.00,'2026-02-05 11:30:00'),(87,'sur_place',NULL,'Marie Dossou','+22996002222',NULL,'Abomey-Calavi','livree',12000.00,'2026-02-12 18:15:00'),(88,'sur_place',NULL,'Jean Houngbedji','+22995003333',NULL,'Porto-Novo','livree',6500.00,'2026-02-24 12:45:00'),(89,'sur_place',NULL,'Sonia Adjovi','+22994004444',NULL,'Cotonou','livree',14000.00,'2026-04-03 19:10:00'),(90,'sur_place',NULL,'Paul Hounkpatin','+22993005555',NULL,'Calavi','livree',9000.00,'2026-04-11 17:30:00'),(91,'sur_place',NULL,'Alice Kiki','+22992006666',NULL,'Akpakpa','livree',11000.00,'2026-04-22 13:20:00'),(92,'sur_place',NULL,'David Agossou','+22991007777',NULL,'Godomey','livree',7500.00,'2026-05-06 11:00:00'),(93,'sur_place',NULL,'Nadia Alao','+22990008888',NULL,'Fidjrossè','livree',16000.00,'2026-05-15 20:00:00'),(94,'sur_place',NULL,'Franck Ahouansou','+22998009999',NULL,'Porto-Novo','livree',13500.00,'2026-05-28 18:40:00'),(95,'sur_place',NULL,'Roger Ahounou','+22997112233',NULL,'Cotonou','livree',9500.00,'2026-01-10 12:15:00'),(96,'sur_place',NULL,'Clarisse Dossa','+22997223344',NULL,'Abomey-Calavi','livree',12000.00,'2026-01-25 18:45:00'),(97,'sur_place',NULL,'Kevin Kpodar','+22997334455',NULL,'Godomey','livree',8000.00,'2026-03-08 11:20:00'),(98,'sur_place',NULL,'Mireille Hountondji','+22997445566',NULL,'Porto-Novo','livree',14500.00,'2026-03-21 19:10:00'),(99,'sur_place',NULL,'Roger Ahounou','+22997112233',NULL,'Cotonou','livree',9500.00,'2026-01-10 12:15:00'),(100,'sur_place',NULL,'Clarisse Dossa','+22997223344',NULL,'Abomey-Calavi','livree',12000.00,'2026-01-25 18:45:00'),(101,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',1500.00,'2026-06-10 08:28:12'),(102,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',1500.00,'2026-06-10 08:28:31'),(103,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',1500.00,'2026-06-10 08:28:37'),(104,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',1500.00,'2026-06-10 08:28:45'),(105,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',1500.00,'2026-06-10 08:28:51'),(106,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',1500.00,'2026-06-10 08:31:13'),(107,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',1500.00,'2026-06-10 08:34:09'),(108,'sur_place',2,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',2500.00,'2026-06-10 08:36:37'),(109,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'en_attente',5000.00,'2026-06-10 08:45:21'),(110,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'prete',1000.00,'2026-06-10 09:11:06'),(111,'livraison',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com','Cotonou,Saint Michelle','en_attente',1500.00,'2026-06-10 09:15:50'),(112,'emporter',NULL,'Farid DOSSA','0154764272','dossafarid1@gmail.com',NULL,'prete',6000.00,'2026-06-10 09:24:53');
/*!40000 ALTER TABLE `commandes` ENABLE KEYS */;

--
-- Table structure for table `employes`
--

DROP TABLE IF EXISTS `employes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employes` (
  `id_employe` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `role` enum('serveur','cuisinier','manager','admin') NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('actif','inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_employe`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employes`
--

/*!40000 ALTER TABLE `employes` DISABLE KEYS */;
INSERT INTO `employes` VALUES (1,'Dossa','Farid','farid@restobenin.bj','123456','+22997000001','admin','2026-06-02 19:38:06','actif'),(2,'Adjovi','Marie','marie@restobenin.bj','123456','+22997000002','manager','2026-06-02 19:38:06','inactif'),(3,'Hounkpatin','Jean','jean@restobenin.bj','123456','+22997000003','cuisinier','2026-06-02 19:38:06','inactif'),(4,'Kouassi','Paul','paul@restobenin.bj','123456','+22997000004','cuisinier','2026-06-02 19:38:06','actif'),(5,'Ahouanvoe','Sonia','sonia@restobenin.bj','123456','+22997000005','serveur','2026-06-02 19:38:06','actif'),(6,'Boko','Arnaud','arnaud@restobenin.bj','123456','+22997000006','serveur','2026-06-02 19:38:06','actif'),(7,'Mensah','Clarisse','clarisse@restobenin.bj','123456','+22997000007','serveur','2026-06-02 19:38:06','actif'),(8,'Tossou','Michel','michel@restobenin.bj','123456','+22997000008','manager','2026-06-02 19:38:06','inactif'),(9,'Houessou','Esther','esther@restobenin.bj','123456','+22997000009','cuisinier','2026-06-02 19:38:06','actif'),(10,'Kiki','David','david@restobenin.bj','123456','+22997000010','serveur','2026-06-02 19:38:06','inactif'),(11,'DOSSA','Farid','dossafarid1@gmail.com','$2y$10$XKWlcl7qWEEV88M9PrHhbuvpKrLaL/FfJJmwXCJ2oxRNSvufHnMSm','1478523','manager','2026-06-03 14:30:42','actif');
/*!40000 ALTER TABLE `employes` ENABLE KEYS */;

--
-- Table structure for table `lignes_commande`
--

DROP TABLE IF EXISTS `lignes_commande`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lignes_commande` (
  `id_ligne` int(11) NOT NULL AUTO_INCREMENT,
  `id_commande` int(11) NOT NULL,
  `id_plat` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `commentaire_client` text DEFAULT NULL,
  PRIMARY KEY (`id_ligne`),
  KEY `fk_ligne_commande` (`id_commande`),
  KEY `fk_ligne_plat` (`id_plat`),
  CONSTRAINT `fk_ligne_commande` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id_commande`) ON DELETE CASCADE,
  CONSTRAINT `fk_ligne_plat` FOREIGN KEY (`id_plat`) REFERENCES `plats` (`id_plat`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lignes_commande`
--

/*!40000 ALTER TABLE `lignes_commande` DISABLE KEYS */;
INSERT INTO `lignes_commande` VALUES (12,52,38,1,5000.00,'Note : \"sans piment\"'),(13,53,37,1,1500.00,NULL),(14,53,34,1,1500.00,NULL),(15,54,34,1,1500.00,NULL),(16,55,35,1,1000.00,NULL),(17,56,34,1,1500.00,NULL),(18,57,39,1,5000.00,NULL),(19,58,39,1,5000.00,NULL),(20,101,34,1,1500.00,NULL),(21,102,34,1,1500.00,NULL),(22,103,34,1,1500.00,NULL),(23,104,34,1,1500.00,NULL),(24,105,34,1,1500.00,NULL),(25,106,37,1,1500.00,NULL),(26,107,37,1,1500.00,NULL),(27,108,36,1,2500.00,NULL),(28,109,39,1,5000.00,NULL),(29,110,35,1,1000.00,NULL),(30,111,37,1,1500.00,NULL),(31,112,39,1,5000.00,NULL),(32,112,35,1,1000.00,NULL);
/*!40000 ALTER TABLE `lignes_commande` ENABLE KEYS */;

--
-- Table structure for table `plats`
--

DROP TABLE IF EXISTS `plats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plats` (
  `id_plat` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `temps_preparation` int(11) DEFAULT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  PRIMARY KEY (`id_plat`),
  KEY `fk_plat_categorie` (`id_categorie`),
  CONSTRAINT `fk_plat_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categories_plats` (`id_categorie`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plats`
--

/*!40000 ALTER TABLE `plats` DISABLE KEYS */;
INSERT INTO `plats` VALUES (34,'Riz','Riz rouge',1500.00,'plat_1780910758.jpg',1,1200,'Fast Food',3),(35,'Jus de Bissap','Boisson fraîche à base d’hibiscus',1000.00,'plat_1780924464.jpg',1,5,'Boissons',4),(36,'Igname Pilée','Igname pilée avec sauce arachide.',2500.00,'plat_1780924628.jpg',1,13,'Plats traditionnels',1),(37,'Akassa Poisson','Akassa servie avec poisson frit et sauce piment.',1500.00,'plat_1780925748.jpg',1,10,'Plats traditionnels',1),(38,'Pizza Poulet','Pizza garnie de poulet et fromage.',5000.00,'plat_1780925900.jpg',1,14,'Fast Food',3),(39,'Burger Maison','Burger au bœuf avec frites.',5000.00,'plat_1780925990.jpg',1,14,'Fast Food',3);
/*!40000 ALTER TABLE `plats` ENABLE KEYS */;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id_reservation` int(11) NOT NULL AUTO_INCREMENT,
  `nom_client` varchar(100) NOT NULL,
  `telephone_client` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `id_table` int(11) NOT NULL,
  `date_heure` datetime NOT NULL,
  `nb_couverts` int(11) NOT NULL,
  `statut` enum('en_attente','confirmee','annulee') DEFAULT 'en_attente',
  `commentaire` text DEFAULT NULL,
  PRIMARY KEY (`id_reservation`),
  KEY `fk_reservation_table` (`id_table`),
  CONSTRAINT `fk_reservation_table` FOREIGN KEY (`id_table`) REFERENCES `tables_restaurant` (`id_table`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (6,'Arnaud Koffi','+22997001111','arnaud@gmail.com',3,'2026-06-05 12:30:00',2,'annulee','Table près de la fenêtre si possible'),(7,'Marie Adjovi','+22997001112','marie@gmail.com',4,'2026-06-05 19:00:00',4,'en_attente','Anniversaire en famille'),(8,'David Toko','+22997001113','david@gmail.com',6,'2026-06-06 13:00:00',5,'confirmee','Déjeuner professionnel'),(9,'Sonia Mensah','+22997001114','sonia@gmail.com',8,'2026-06-06 20:00:00',8,'confirmee','Réunion entre amis'),(10,'Jean Houngbo','+22997001115','jean@gmail.com',2,'2026-06-07 12:00:00',2,'en_attente','Empêchement de dernière minute'),(11,'Farid DOSSA','68452570','dossafarid1@gmail.com',5,'2026-06-19 16:00:00',4,'confirmee',''),(12,'Farid DOSSA','68452570','dossafarid1@gmail.com',1,'2026-05-07 20:00:00',1,'confirmee',''),(13,'Farid DOSSA','68452570','dossafarid1@gmail.com',11,'2026-06-24 22:00:00',1,'confirmee','');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;

--
-- Table structure for table `tables_restaurant`
--

DROP TABLE IF EXISTS `tables_restaurant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables_restaurant` (
  `id_table` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `capacite` int(11) NOT NULL,
  `statut` enum('libre','occupee','reservee') DEFAULT 'libre',
  PRIMARY KEY (`id_table`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables_restaurant`
--

/*!40000 ALTER TABLE `tables_restaurant` DISABLE KEYS */;
INSERT INTO `tables_restaurant` VALUES (1,1,2,'reservee'),(2,2,2,'occupee'),(3,3,4,'libre'),(4,4,4,'reservee'),(5,5,4,'reservee'),(6,6,6,'occupee'),(7,7,6,'libre'),(8,8,8,'reservee'),(9,9,8,'libre'),(10,10,10,'libre'),(11,11,2,'reservee'),(12,12,4,'occupee'),(13,13,6,'libre'),(14,14,8,'reservee'),(15,15,12,'libre');
/*!40000 ALTER TABLE `tables_restaurant` ENABLE KEYS */;

--
-- Dumping routines for database 'restobenin'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-10 10:27:50
