-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: easeestate
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `areca_inventory`
--

DROP TABLE IF EXISTS `areca_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areca_inventory` (
  `lot_number` int NOT NULL,
  `date_received` date NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`lot_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `areca_inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `areca_inventory_history`
--

DROP TABLE IF EXISTS `areca_inventory_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areca_inventory_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lot_number` int NOT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_received` date NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lot_number_date_added` (`lot_number`,`date_added`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `areca_inventory_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `worker_id` int NOT NULL,
  `lead_id` int NOT NULL,
  `job_id` int NOT NULL,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `job_role` text NOT NULL,
  `present` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `unique_attendance` (`worker_id`,`date`,`job_id`,`lead_id`),
  KEY `lead_id` (`lead_id`),
  KEY `job_id` (`job_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`lead_id`) REFERENCES `labour_lead` (`lead_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=605 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coffee_godown`
--

DROP TABLE IF EXISTS `coffee_godown`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coffee_godown` (
  `lot_id` int NOT NULL AUTO_INCREMENT,
  `lot_number` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `date_received` date NOT NULL,
  `coffee_type` enum('Parchment','Cherry') NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `moisture_level` varchar(255) NOT NULL,
  `sell_date` date DEFAULT NULL,
  PRIMARY KEY (`lot_id`),
  UNIQUE KEY `lot_number` (`lot_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `coffee_godown_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coffee_inventory_history`
--

DROP TABLE IF EXISTS `coffee_inventory_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coffee_inventory_history` (
  `lot_number` int NOT NULL,
  `date_received` date NOT NULL,
  `coffee_type` varchar(50) NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `moisture_level` int NOT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lot_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coffee_inventory_record`
--

DROP TABLE IF EXISTS `coffee_inventory_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coffee_inventory_record` (
  `record_id` int NOT NULL AUTO_INCREMENT,
  `lot_number` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `date_received` date NOT NULL,
  `coffee_type` enum('Parchment','Cherry') NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `moisture_level` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `lot_number` (`lot_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `coffee_inventory_record_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coffee_lots`
--

DROP TABLE IF EXISTS `coffee_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coffee_lots` (
  `lot_number` varchar(50) NOT NULL,
  `date_received` date NOT NULL,
  `coffee_type` varchar(255) NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `moisture_level` decimal(5,2) NOT NULL,
  PRIMARY KEY (`lot_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crops_plucked`
--

DROP TABLE IF EXISTS `crops_plucked`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crops_plucked` (
  `crops_plucked_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `lead_id` int NOT NULL,
  `job_id` int NOT NULL,
  `plucked_date` date NOT NULL,
  `ripe_kg` decimal(10,2) DEFAULT NULL,
  `unripe_kg` decimal(10,2) DEFAULT NULL,
  `total_kg` decimal(10,2) DEFAULT NULL,
  `kone_count` int DEFAULT NULL,
  `per_kg_rate` decimal(10,2) DEFAULT NULL,
  `daily_wage` decimal(10,2) DEFAULT NULL,
  `salary_calculation_type` enum('per_kg','daily_wage') NOT NULL DEFAULT 'per_kg',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`crops_plucked_id`),
  KEY `user_id` (`user_id`),
  KEY `worker_id` (`worker_id`),
  KEY `lead_id` (`lead_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `crops_plucked_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crops_plucked_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  CONSTRAINT `crops_plucked_ibfk_3` FOREIGN KEY (`lead_id`) REFERENCES `labour_lead` (`lead_id`) ON DELETE RESTRICT,
  CONSTRAINT `crops_plucked_ibfk_4` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=324 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fertilizer_inventory`
--

DROP TABLE IF EXISTS `fertilizer_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fertilizer_inventory` (
  `fertilizer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_quantity` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fertilizer_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fertilizer_purchase_history`
--

DROP TABLE IF EXISTS `fertilizer_purchase_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fertilizer_purchase_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_date` date NOT NULL,
  `fertilizer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_purchased` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_lot_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fertilizer_name` (`fertilizer_name`),
  CONSTRAINT `fertilizer_purchase_history_ibfk_1` FOREIGN KEY (`fertilizer_name`) REFERENCES `fertilizer_inventory` (`fertilizer_name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fertilizer_usage_history`
--

DROP TABLE IF EXISTS `fertilizer_usage_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fertilizer_usage_history` (
  `usage_id` int NOT NULL AUTO_INCREMENT,
  `date_used` date NOT NULL,
  `fertilizer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lead_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  KEY `fertilizer_name` (`fertilizer_name`),
  KEY `lead_id` (`lead_id`),
  CONSTRAINT `fertilizer_usage_history_ibfk_1` FOREIGN KEY (`fertilizer_name`) REFERENCES `fertilizer_inventory` (`fertilizer_name`) ON DELETE CASCADE,
  CONSTRAINT `fertilizer_usage_history_ibfk_2` FOREIGN KEY (`lead_id`) REFERENCES `labour_lead` (`lead_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `job_name` varchar(255) NOT NULL,
  `daily_wage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_kg_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `overtime_hourly_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labour_lead`
--

DROP TABLE IF EXISTS `labour_lead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `labour_lead` (
  `lead_id` int NOT NULL AUTO_INCREMENT,
  `lead_name` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lead_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `labour_lead_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `overtime`
--

DROP TABLE IF EXISTS `overtime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `overtime` (
  `overtime_id` int NOT NULL AUTO_INCREMENT,
  `worker_id` int NOT NULL,
  `lead_id` int NOT NULL,
  `job_id` int NOT NULL,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `overtime_hours` decimal(4,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`overtime_id`),
  UNIQUE KEY `unique_overtime` (`worker_id`,`date`,`job_id`,`lead_id`),
  KEY `lead_id` (`lead_id`),
  KEY `job_id` (`job_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `overtime_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  CONSTRAINT `overtime_ibfk_2` FOREIGN KEY (`lead_id`) REFERENCES `labour_lead` (`lead_id`) ON DELETE CASCADE,
  CONSTRAINT `overtime_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `overtime_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pepper_inventory`
--

DROP TABLE IF EXISTS `pepper_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pepper_inventory` (
  `lot_number` int NOT NULL,
  `date_received` date NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `moisture_level` decimal(5,2) NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`lot_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pepper_inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pepper_inventory_history`
--

DROP TABLE IF EXISTS `pepper_inventory_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pepper_inventory_history` (
  `lot_number` int NOT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_received` date NOT NULL,
  `total_bags` int NOT NULL,
  `total_weight_kg` decimal(10,2) NOT NULL,
  `moisture_level` decimal(5,2) NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`lot_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pepper_inventory_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tool_assignments`
--

DROP TABLE IF EXISTS `tool_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tool_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `tool_id` int NOT NULL,
  `assigned_worker_id` int NOT NULL,
  `quantity_assigned` int NOT NULL DEFAULT '1',
  `assignment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `return_date` timestamp NULL DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'assigned',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `tool_id` (`tool_id`),
  KEY `assigned_worker_id` (`assigned_worker_id`),
  CONSTRAINT `tool_assignments_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`),
  CONSTRAINT `tool_assignments_ibfk_2` FOREIGN KEY (`assigned_worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tool_returns`
--

DROP TABLE IF EXISTS `tool_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tool_returns` (
  `return_id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `quantity_returned` int NOT NULL,
  `return_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `return_notes` text,
  `user_id` int NOT NULL,
  PRIMARY KEY (`return_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tool_returns_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `tool_assignments` (`assignment_id`),
  CONSTRAINT `tool_returns_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tools`
--

DROP TABLE IF EXISTS `tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tools` (
  `tool_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `tool_name` varchar(255) NOT NULL,
  `tool_quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tool_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tools_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workers`
--

DROP TABLE IF EXISTS `workers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workers` (
  `worker_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `lead_id` int NOT NULL,
  `worker_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_id`),
  KEY `user_id` (`user_id`),
  KEY `lead_id` (`lead_id`),
  CONSTRAINT `workers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workers_ibfk_2` FOREIGN KEY (`lead_id`) REFERENCES `labour_lead` (`lead_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-27  9:12:01
