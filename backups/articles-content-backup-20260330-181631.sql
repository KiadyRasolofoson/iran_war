-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: iran_war
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `author_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_articles_slug` (`slug`),
  KEY `idx_articles_status_published_at` (`status`,`published_at`),
  KEY `idx_articles_category_status` (`category_id`,`status`),
  KEY `idx_articles_author` (`author_id`),
  FULLTEXT KEY `ft_articles_search` (`title`,`excerpt`,`content`),
  CONSTRAINT `fk_articles_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_articles_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (1,1,1,'Chronologie courte du conflit Iran Irak','chronologie-courte-conflit-iran-irak','Repere rapide des principales phases politiques et militaires du conflit.','<h2>Contexte initial</h2><p>Le conflit evolue par cycles avec des periodes offensives puis defensives.</p><blockquote>La duree du conflit transforme les objectifs initiaux en logique dattrition.</blockquote><ul><li>Phase 1: escalation frontaliere</li><li>Phase 2: guerre de position</li><li>Phase 3: pression diplomatique</li></ul>',NULL,'Carte simplifiee de la chronologie du conflit','Chronologie du conflit Iran Irak','Resume chronologique et points de bascule de la guerre Iran Irak.','published','2024-05-10 09:00:00','2026-03-30 13:28:10','2026-03-30 13:28:10'),(2,2,1,'Routes energetiques et enjeux regionaux','routes-energetiques-enjeux-regionaux','Lecture geopolitique des routes maritimes et de la securite energetique.','<h2>Axes strategiques</h2><p>Les couloirs maritimes deviennent des points de tension permanents.</p><blockquote>Controler les flux energetiques influence directement le rapport de force diplomatique.</blockquote><ul><li>Detroits sensibles</li><li>Assurance et cout du transport</li><li>Effets sur les partenaires regionaux</li></ul>',NULL,'Navire cargo sur route energetique regionale','Routes energetiques et geopolitique','Analyse des routes energetiques et des impacts regionaux pendant la guerre.','published','2024-07-22 14:30:00','2026-03-30 13:28:10','2026-03-30 13:28:10'),(3,3,1,'Doctrine operationnelle et adaptation tactique','doctrine-operationnelle-adaptation-tactique','Comment les forces adaptent doctrine, logistique et rythme des operations.','<h2>Evolution tactique</h2><p>Les commandements ajustent progressivement leurs modes daction face a la duree du conflit.</p><blockquote>Ladaptation logistique est aussi decisive que la manoeuvre sur le terrain.</blockquote><ul><li>Rotation des unites</li><li>Priorite au ravitaillement</li><li>Integration du renseignement</li></ul>',NULL,'Unite en deplacement avec soutien logistique','Doctrine militaire et adaptation tactique','Panorama des ajustements tactiques et operationnels observes dans le conflit.','published','2024-10-03 08:15:00','2026-03-30 13:28:10','2026-03-30 13:28:10');
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-30 15:16:31
