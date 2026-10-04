-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: dating_site
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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_settings`
--

DROP TABLE IF EXISTS `admin_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(100) DEFAULT 'LoveConnect',
  `logo_url` varchar(255) DEFAULT 'assets/uploads/default-logo.png',
  `contact_email` varchar(100) DEFAULT 'support@loveconnect.com',
  `help_line` varchar(20) DEFAULT '+1-800-LOVE',
  `footer_text` text DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `about_us` text DEFAULT NULL,
  `privacy_policy` text DEFAULT NULL,
  `terms_of_service` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_settings`
--

LOCK TABLES `admin_settings` WRITE;
/*!40000 ALTER TABLE `admin_settings` DISABLE KEYS */;
INSERT INTO `admin_settings` VALUES (1,'PolyLink','assets/uploads/1777544671_87e14523f0972936614247e9664482cd.jpg','nyamongopolycap212@gmail.com','0746926210','© 2024 LoveConnect. All rights reserved.','https://wa.me/message/LXV633SP3UFUJ1','https://wa.me/message/LXV633SP3UFUJ1','https://wa.me/message/LXV633SP3UFUJ1','','','','2026-04-30 10:25:01');
/*!40000 ALTER TABLE `admin_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocked_users`
--

DROP TABLE IF EXISTS `blocked_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_users` (
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `blocked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`blocker_id`,`blocked_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocked_users`
--

LOCK TABLES `blocked_users` WRITE;
/*!40000 ALTER TABLE `blocked_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bookmark` (`user_id`,`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks`
--

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_participants`
--

DROP TABLE IF EXISTS `chat_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_participants` (
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `joined_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`room_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_participants`
--

LOCK TABLES `chat_participants` WRITE;
/*!40000 ALTER TABLE `chat_participants` DISABLE KEYS */;
INSERT INTO `chat_participants` VALUES (1,4,0,'2026-04-26 18:47:12'),(1,5,0,'2026-04-26 18:47:12'),(2,1,0,'2026-04-26 18:59:41'),(2,2,0,'2026-04-26 18:59:41'),(2,3,0,'2026-04-26 18:59:41'),(2,4,0,'2026-04-26 18:59:41'),(2,5,0,'2026-04-26 18:59:41'),(3,2,0,'2026-04-27 12:51:04'),(3,5,0,'2026-04-27 12:51:04'),(4,1,0,'2026-04-30 10:27:28'),(4,3,0,'2026-04-30 10:27:28'),(5,2,0,'2026-05-01 13:51:49'),(5,4,0,'2026-05-01 13:51:49'),(6,3,0,'2026-05-01 14:22:15'),(6,5,0,'2026-05-01 14:22:15'),(7,2,0,'2026-05-01 14:50:35'),(7,3,0,'2026-05-01 14:50:35'),(8,3,0,'2026-05-01 14:51:10'),(8,4,0,'2026-05-01 14:51:10'),(9,4,0,'2026-05-02 10:22:47'),(9,6,0,'2026-05-02 10:22:47'),(10,4,0,'2026-06-16 06:30:13'),(10,7,0,'2026-06-16 06:30:13'),(11,5,0,'2026-06-24 08:33:50'),(11,8,0,'2026-06-24 08:33:50'),(12,4,0,'2026-06-24 08:34:56'),(12,8,0,'2026-06-24 08:34:56'),(13,9,0,'2026-09-18 11:17:27'),(13,11,0,'2026-09-18 11:17:27');
/*!40000 ALTER TABLE `chat_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_rooms`
--

DROP TABLE IF EXISTS `chat_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `type` enum('direct','group') DEFAULT 'direct',
  `created_by` int(11) DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_rooms`
--

LOCK TABLES `chat_rooms` WRITE;
/*!40000 ALTER TABLE `chat_rooms` DISABLE KEYS */;
INSERT INTO `chat_rooms` VALUES (1,NULL,'direct',5,'Yes','2026-06-24 08:18:26','2026-04-26 18:47:12'),(2,'Polg','group',5,'Okay','2026-06-24 08:20:09','2026-04-26 18:59:41'),(3,NULL,'direct',2,'Hey','2026-06-24 08:16:49','2026-04-27 12:51:04'),(4,NULL,'direct',1,NULL,NULL,'2026-04-30 10:27:28'),(5,NULL,'direct',4,NULL,NULL,'2026-05-01 13:51:49'),(6,NULL,'direct',3,NULL,NULL,'2026-05-01 14:22:15'),(7,NULL,'direct',3,'...','2026-05-06 08:14:59','2026-05-01 14:50:35'),(8,NULL,'direct',3,'@emma_d','2026-05-03 11:27:19','2026-05-01 14:51:10'),(9,NULL,'direct',6,'Uko aje','2026-05-03 11:26:33','2026-05-02 10:22:47'),(10,NULL,'direct',7,'What\'s up','2026-06-24 08:11:48','2026-06-16 06:30:13'),(11,NULL,'direct',8,'Hello','2026-06-24 08:34:10','2026-06-24 08:33:49'),(12,NULL,'direct',8,'Hi','2026-06-24 08:35:05','2026-06-24 08:34:56'),(13,NULL,'direct',11,NULL,NULL,'2026-09-18 11:17:27');
/*!40000 ALTER TABLE `chat_rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comment_likes`
--

DROP TABLE IF EXISTS `comment_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment_likes` (
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment_likes`
--

LOCK TABLES `comment_likes` WRITE;
/*!40000 ALTER TABLE `comment_likes` DISABLE KEYS */;
INSERT INTO `comment_likes` VALUES (4,7,'2026-04-30 10:03:50'),(4,8,'2026-04-30 10:03:51'),(4,10,'2026-04-30 10:03:52'),(4,13,'2026-04-30 10:03:54'),(4,26,'2026-05-01 14:15:56'),(5,7,'2026-04-27 11:42:52'),(5,8,'2026-04-27 11:42:51'),(5,10,'2026-04-27 11:43:29'),(5,13,'2026-04-27 11:42:02'),(5,17,'2026-04-27 11:52:24'),(5,21,'2026-04-27 12:41:41'),(5,77,'2026-05-23 16:07:17'),(5,81,'2026-05-23 16:07:44'),(7,84,'2026-06-16 06:32:52');
/*!40000 ALTER TABLE `comment_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_post` (`post_id`),
  KEY `idx_parent` (`parent_comment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,5,NULL,'Love you so much 😘😘','2026-04-26 19:08:01'),(2,1,5,NULL,'Want you ufance that way','2026-04-26 19:08:09'),(3,2,4,NULL,'Love you so much 😘😘','2026-04-26 20:50:07'),(4,6,5,NULL,'Enjoy','2026-04-27 06:48:35'),(5,3,5,NULL,'Hey','2026-04-27 07:06:56'),(6,3,5,NULL,'Hsnns','2026-04-27 07:07:02'),(7,11,4,NULL,'Hello','2026-04-27 11:19:14'),(8,11,4,NULL,'G','2026-04-27 11:27:51'),(9,6,4,NULL,'Great site','2026-04-27 11:28:17'),(10,11,4,NULL,'Uhh','2026-04-27 11:30:08'),(11,2,4,NULL,'http://localhost:8000/dsite/pages/index.php','2026-04-27 11:31:39'),(12,6,4,NULL,'Yes','2026-04-27 11:33:19'),(13,11,5,NULL,'Yes','2026-04-27 11:33:59'),(14,11,5,10,'Wahh','2026-04-27 11:42:20'),(15,11,5,8,'Serious','2026-04-27 11:42:36'),(16,11,5,NULL,'<!-- Actions --> <div class=\"x-post-actions\" onclick=\"event.stopPropagation();\">     <!-- Comment -->     <button class=\"x-action-group\" onclick=\"toggleComments(<?php echo $post[\'id\']; ?>)\">         <span class=\"x-action-icon comment-icon\"><i class=\"far fa-comment\"></i></span>         <span class=\"x-action-count\"><?php echo $post[\'comments_count\']; ?></span>     </button>          <!-- Repost/Share -->     <button class=\"x-action-group share-btn <?php echo $post[\'shares_count\']>0?\'reposted\':\'\'; ?>\" onclick=\"handlePostShare(this, <?php echo $post[\'id\']; ?>)\">         <span class=\"x-action-icon repost-icon\"><i class=\"fas fa-retweet\"></i></span>         <span class=\"x-action-count\"><?php echo $post[\'shares_count\']; ?></span>     </button>          <!-- Like -->     <button class=\"x-action-group like-btn <?php echo $post[\'user_liked\']?\'liked\':\'\'; ?>\" onclick=\"handlePostLike(this, <?php echo $post[\'id\']; ?>)\">         <span class=\"x-action-icon like-icon\"><i class=\"<?php echo $post[\'user_liked\']?\'fas\':\'far\'; ?> fa-heart\"></i></span>         <span class=\"x-action-count\"><?php echo $post[\'likes_count\']; ?></span>     </button>          <!-- Share -->     <button class=\"x-action-group\" onclick=\"copyLink()\">         <span class=\"x-action-icon share-icon\"><i class=\"fas fa-upload\"></i></span>     </button>          <?php if($post[\'user_id\']==$_SESSION[\'user_id\']): ?>     <button class=\"x-action-group\" onclick=\"deletePost(<?php echo $post[\'id\']; ?>)\" style=\"margin-left:auto;\">         <span class=\"x-action-icon\"><i class=\"fas fa-trash-alt\"></i></span>     </button>     <?php endif; ?> </div>','2026-04-27 11:49:41'),(17,9,5,NULL,'Hey','2026-04-27 11:52:22'),(18,9,5,17,'How 🙂‍↔️','2026-04-27 11:52:37'),(19,9,5,17,'Seriously','2026-04-27 11:52:45'),(20,7,5,NULL,'54','2026-04-27 12:03:42'),(21,10,5,NULL,'Yes','2026-04-27 12:41:39'),(22,12,4,NULL,'What\'s this','2026-04-30 10:12:04'),(23,13,4,NULL,'Hello','2026-05-01 11:28:50'),(24,13,4,23,'http://localhost:8001/pages/index.php','2026-05-01 11:28:58'),(25,13,4,NULL,'Geywh','2026-05-01 11:29:46'),(26,14,4,NULL,'@emma_d','2026-05-01 14:15:44'),(27,14,4,26,'Jfjd','2026-05-01 14:16:16'),(28,12,2,NULL,'Hey','2026-05-02 13:32:38'),(29,8,2,NULL,'Hey','2026-05-02 13:33:04'),(30,13,2,NULL,'Ghj','2026-05-02 13:50:38'),(31,13,2,NULL,'Fhh','2026-05-02 13:50:42'),(32,12,2,NULL,'Bjk','2026-05-02 13:51:39'),(33,12,2,NULL,'Vhj','2026-05-02 13:51:43'),(34,13,2,NULL,'How','2026-05-02 13:59:34'),(35,13,2,NULL,'Hji','2026-05-02 14:03:02'),(36,8,2,29,'How','2026-05-02 20:15:55'),(37,8,2,36,'Seriously','2026-05-02 20:16:03'),(38,8,2,NULL,'Hello','2026-05-02 20:16:16'),(39,8,2,38,'Hsbs','2026-05-02 20:16:26'),(40,2,2,NULL,'@emma_d','2026-05-02 20:20:24'),(41,2,2,NULL,'Hey','2026-05-02 20:20:46'),(42,2,2,NULL,'#emma_d','2026-05-02 20:21:06'),(43,15,2,NULL,'@emmaf','2026-05-02 20:26:05'),(44,15,2,NULL,'#emmaf','2026-05-02 20:26:16'),(45,8,2,NULL,'Hey','2026-05-02 20:34:24'),(46,8,2,45,'@emma_d','2026-05-02 20:35:04'),(47,8,2,NULL,'#freeemmad','2026-05-02 20:35:35'),(48,16,2,NULL,'How','2026-05-02 20:37:30'),(49,16,2,48,'Ask @emmad','2026-05-02 20:37:45'),(50,16,2,NULL,'#emmad','2026-05-02 20:38:02'),(51,15,2,NULL,'#emad','2026-05-02 20:45:46'),(52,15,2,NULL,'@emma_d','2026-05-02 20:46:03'),(53,15,2,NULL,'@emma_d','2026-05-02 20:46:05'),(54,15,2,NULL,'@emma_d','2026-05-02 20:46:07'),(55,15,2,NULL,'@emma_dhs','2026-05-02 20:46:11'),(56,15,2,NULL,'@emma_d','2026-05-02 20:46:34'),(57,15,2,NULL,'@emma_d','2026-05-02 20:46:49'),(58,15,2,NULL,'@emma_d','2026-05-02 20:46:51'),(59,15,2,NULL,'@emma_','2026-05-02 20:46:55'),(60,15,2,NULL,'@','2026-05-02 20:47:07'),(61,15,2,NULL,'@sarah','2026-05-02 20:47:19'),(62,16,2,NULL,'@emma_d','2026-05-02 20:47:43'),(63,16,2,NULL,'@emma_d','2026-05-02 20:47:45'),(64,2,2,41,'@&_&','2026-05-02 20:58:08'),(65,2,2,NULL,'#-_-','2026-05-02 20:58:15'),(66,2,2,NULL,'#2567tdy','2026-05-02 20:58:24'),(67,15,2,NULL,'@emm','2026-05-02 21:28:26'),(68,24,4,NULL,'True @Sarah Johnson','2026-05-02 21:51:00'),(69,24,4,68,'Bhn','2026-05-02 21:56:25'),(70,24,4,NULL,'@sarah_j','2026-05-02 22:13:02'),(71,16,4,NULL,'@Sarah Johnson','2026-05-03 04:59:50'),(72,24,4,NULL,'@Site Admin','2026-05-03 05:00:36'),(73,24,4,NULL,'@Mike Wilson','2026-05-03 05:04:06'),(74,24,4,NULL,'@Site Admin','2026-05-03 05:09:16'),(75,24,4,NULL,'#emma','2026-05-03 05:11:55'),(76,25,4,NULL,'@John Kamau','2026-05-03 05:13:54'),(77,30,4,NULL,'Hey','2026-05-06 08:16:44'),(78,27,4,NULL,'How','2026-05-21 09:29:22'),(79,27,4,78,'Heya','2026-05-21 09:29:27'),(80,30,5,77,'Hello','2026-05-23 16:07:24'),(81,29,5,NULL,'How','2026-05-23 16:07:39'),(82,13,5,25,'Seriously','2026-05-23 16:09:29'),(83,30,7,NULL,'Hey','2026-06-16 06:28:53'),(84,9,7,NULL,'Hey','2026-06-16 06:32:49'),(85,9,7,84,'What','2026-06-16 06:32:57'),(86,20,11,NULL,'hello','2026-09-18 11:12:45'),(87,20,11,86,'yes','2026-09-18 11:12:53'),(88,33,11,NULL,'@poly12','2026-09-18 11:20:18'),(89,33,11,NULL,'#uhdfuv','2026-09-18 11:20:28');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `follows`
--

DROP TABLE IF EXISTS `follows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `follows` (
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`follower_id`,`following_id`),
  KEY `idx_follower` (`follower_id`),
  KEY `idx_following` (`following_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `follows`
--

LOCK TABLES `follows` WRITE;
/*!40000 ALTER TABLE `follows` DISABLE KEYS */;
INSERT INTO `follows` VALUES (1,7,'2026-06-24 09:10:45'),(2,3,'2026-04-27 10:59:44'),(2,4,'2026-04-27 10:54:35'),(2,5,'2026-04-27 12:45:10'),(2,6,'2026-05-03 11:34:25'),(3,2,'2026-05-01 14:48:26'),(3,5,'2026-05-01 14:48:25'),(4,2,'2026-05-01 14:17:19'),(4,3,'2026-05-03 11:19:51'),(4,5,'2026-05-01 15:22:15'),(4,6,'2026-05-03 11:19:58'),(4,7,'2026-06-24 08:11:05'),(5,2,'2026-04-26 20:10:18'),(5,3,'2026-04-26 20:10:16'),(5,4,'2026-04-26 20:10:14'),(5,6,'2026-05-23 16:02:51'),(7,2,'2026-06-16 06:33:17'),(7,4,'2026-06-16 06:37:26'),(9,4,'2026-07-03 18:46:34'),(9,7,'2026-07-03 18:47:23'),(9,8,'2026-07-03 18:47:18');
/*!40000 ALTER TABLE `follows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `likes` (
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`post_id`),
  KEY `idx_post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (2,2,'2026-04-27 12:44:43'),(2,5,'2026-04-27 12:44:42'),(2,6,'2026-04-27 12:44:40'),(2,7,'2026-04-27 12:44:38'),(2,8,'2026-04-27 12:44:37'),(2,9,'2026-05-01 19:51:53'),(2,10,'2026-05-01 19:51:48'),(2,11,'2026-05-01 19:51:45'),(2,12,'2026-05-01 19:51:41'),(4,2,'2026-04-26 20:46:03'),(4,5,'2026-04-30 10:12:19'),(4,6,'2026-04-30 10:12:17'),(4,7,'2026-04-30 10:12:15'),(4,8,'2026-04-30 10:12:15'),(4,9,'2026-04-30 10:12:14'),(4,11,'2026-04-30 10:08:38'),(4,12,'2026-04-30 10:11:49'),(4,13,'2026-04-30 10:13:35'),(4,14,'2026-05-01 14:15:19'),(4,15,'2026-05-02 21:31:37'),(4,16,'2026-05-02 21:31:37'),(4,17,'2026-06-24 08:25:15'),(4,18,'2026-06-24 08:25:15'),(4,20,'2026-06-24 08:25:13'),(4,21,'2026-06-24 08:25:12'),(4,22,'2026-06-24 08:25:12'),(4,23,'2026-06-24 08:25:11'),(4,24,'2026-06-24 08:25:10'),(4,25,'2026-06-24 08:25:08'),(4,26,'2026-06-24 08:25:07'),(4,27,'2026-06-24 08:25:07'),(4,28,'2026-06-24 08:24:56'),(4,29,'2026-06-24 08:25:05'),(4,30,'2026-06-24 08:25:05'),(4,31,'2026-06-24 08:22:02'),(5,1,'2026-04-26 19:07:48'),(5,2,'2026-04-27 12:32:56'),(5,5,'2026-04-27 12:06:53'),(5,6,'2026-04-27 12:18:56'),(5,9,'2026-04-27 11:52:15'),(5,11,'2026-04-27 11:52:06'),(5,16,'2026-05-23 16:08:16'),(5,17,'2026-05-23 16:08:17'),(5,18,'2026-05-23 16:08:17'),(5,20,'2026-05-23 16:08:18'),(5,21,'2026-05-23 16:08:09'),(5,22,'2026-05-23 16:08:19'),(5,23,'2026-05-23 16:08:20'),(5,25,'2026-05-23 16:08:55'),(5,26,'2026-05-23 16:08:54'),(5,28,'2026-05-23 16:07:56'),(5,29,'2026-05-23 16:07:57'),(5,30,'2026-05-23 16:07:27'),(6,2,'2026-05-02 10:22:25'),(6,5,'2026-05-02 10:22:26'),(6,6,'2026-05-02 10:22:28'),(6,8,'2026-05-02 10:22:30'),(6,9,'2026-05-02 10:22:31'),(7,9,'2026-06-16 06:32:43'),(7,29,'2026-06-16 06:29:08'),(7,30,'2026-06-16 06:35:59'),(11,21,'2026-09-18 11:12:35'),(11,22,'2026-09-18 11:12:33'),(11,23,'2026-09-18 11:12:31'),(11,32,'2026-09-18 11:17:00');
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_streams`
--

DROP TABLE IF EXISTS `live_streams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `live_streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `stream_key` varchar(64) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `viewers_count` int(11) DEFAULT 0,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stream_key` (`stream_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_streams`
--

LOCK TABLES `live_streams` WRITE;
/*!40000 ALTER TABLE `live_streams` DISABLE KEYS */;
INSERT INTO `live_streams` VALUES (1,4,'0ce77f1c1ce11681581103972deb52d91f1754b8011a13b07044a3f1a8a33551','Studio','Chjj',NULL,0,0,'2026-04-26 20:46:56','2026-04-26 20:47:36','2026-04-26 20:46:56'),(2,4,'2c6b28dbf0e012b5c7afc29afb25c992f20d6752c242184f4059aa86d17dfe1e','Studio','Uryjj',NULL,1,78,'2026-05-01 19:42:25',NULL,'2026-05-01 19:42:25'),(3,2,'b7cb1221c65a5183227b2475d5cb169b5d78757cbbdcca48da14fca0a68b2bbb','Advice for me please 🙏🙏🥺','Just advise',NULL,0,0,'2026-05-03 11:37:45','2026-05-03 11:38:32','2026-05-03 11:37:45'),(4,11,'f6d1745681ea2daa91b5c9b72662de2b5b0d20608ccf286f4aacebb26bc0a1a6','yfv','guiyvjy',NULL,0,0,'2026-09-18 11:23:39','2026-09-18 11:27:25','2026-09-18 11:23:39');
/*!40000 ALTER TABLE `live_streams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_deletions`
--

DROP TABLE IF EXISTS `message_deletions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_deletions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_deletion` (`message_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_deletions`
--

LOCK TABLES `message_deletions` WRITE;
/*!40000 ALTER TABLE `message_deletions` DISABLE KEYS */;
INSERT INTO `message_deletions` VALUES (1,10,5,'2026-04-27 06:05:07'),(2,12,5,'2026-04-27 06:06:18'),(3,33,5,'2026-04-27 09:48:52');
/*!40000 ALTER TABLE `message_deletions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_reactions`
--

DROP TABLE IF EXISTS `message_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `emoji` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reaction` (`message_id`,`user_id`,`emoji`),
  KEY `idx_message` (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_reactions`
--

LOCK TABLES `message_reactions` WRITE;
/*!40000 ALTER TABLE `message_reactions` DISABLE KEYS */;
INSERT INTO `message_reactions` VALUES (4,13,5,'❤️','2026-04-27 09:51:11'),(6,13,4,'❤️','2026-04-27 09:52:41');
/*!40000 ALTER TABLE `message_reactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `message_type` enum('text','image','video','file','audio') DEFAULT 'text',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `reply_to` int(11) DEFAULT NULL,
  `deleted_for_everyone` tinyint(1) DEFAULT 0,
  `forwarded` tinyint(1) DEFAULT 0,
  `forwarded_from` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_room` (`room_id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1,5,'Hey',NULL,'text',1,'2026-04-26 18:47:19',NULL,0,0,NULL),(2,2,5,'Ry',NULL,'text',1,'2026-04-26 19:00:12',NULL,0,0,NULL),(3,2,5,'Hello',NULL,'text',1,'2026-04-26 19:52:38',NULL,0,0,NULL),(4,2,5,'DAVID -EMIS.docx','assets/uploads/1777234499_8c250f7e0dc1135797a63ed0083583e6.docx','file',1,'2026-04-26 20:14:59',NULL,0,0,NULL),(5,2,5,'Hello',NULL,'text',1,'2026-04-26 20:18:17',NULL,0,0,NULL),(6,2,1,'Hello',NULL,'text',1,'2026-04-26 20:33:52',NULL,0,0,NULL),(7,2,1,'Hello',NULL,'text',1,'2026-04-26 20:40:07',NULL,0,0,NULL),(8,2,5,'Yes',NULL,'text',1,'2026-04-26 20:41:45',NULL,0,0,NULL),(9,2,4,'Yes',NULL,'text',1,'2026-04-26 20:44:44',NULL,0,0,NULL),(10,1,4,'DAVID -EMIS.docx','assets/uploads/1777236642_a0ba8f8fe4b95458aab16255373496ab.docx','file',1,'2026-04-26 20:50:42',NULL,0,0,NULL),(11,1,4,'😍😍',NULL,'text',1,'2026-04-26 20:50:59',NULL,0,0,NULL),(12,1,4,'17772366687752639014624435998112.jpg','assets/uploads/1777236684_537d8140198ae1051b7fee7afb46aff6.jpg','image',1,'2026-04-26 20:51:25',NULL,0,0,NULL),(13,2,4,'cr.id',NULL,'text',1,'2026-04-26 21:09:44',NULL,0,0,NULL),(14,1,5,'Bhb',NULL,'text',1,'2026-04-26 21:21:22',NULL,0,0,NULL),(15,1,5,'voice_note.webm','assets/uploads/1777238499_7e0de8014105c4667246360d3a144fcc.webm','video',1,'2026-04-26 21:21:39',NULL,0,0,NULL),(16,1,5,'Hello',NULL,'text',1,'2026-04-26 21:58:34',NULL,0,0,NULL),(17,1,5,'voice_note.webm','assets/uploads/1777240778_b19047cc19786767924a6ec943f773a9.webm','video',1,'2026-04-26 21:59:38',NULL,0,0,NULL),(18,1,5,'Nyhh',NULL,'text',1,'2026-04-26 22:02:32',NULL,0,0,NULL),(19,1,5,'voice_note.webm','assets/uploads/1777242045_cf7e7115799c840312d8498fa3c73260.webm','video',1,'2026-04-26 22:20:45',NULL,0,0,NULL),(20,1,5,'voice_note.webm','assets/uploads/1777264770_6585930b0ecdcb2a221fc79ff46819ba.webm','video',1,'2026-04-27 04:39:31',NULL,0,0,NULL),(21,1,5,'Hey',NULL,'text',1,'2026-04-27 05:10:13',NULL,0,0,NULL),(22,1,5,'Screenshot 2025-12-15 123940.png','assets/uploads/1777266672_f7e7e3ba392ed8425cbdf0030260d694.png','image',1,'2026-04-27 05:11:12',NULL,0,0,NULL),(23,1,5,'voice_note.webm','assets/uploads/1777267649_018411acc860bdbe26b4d338afc9c415.webm','video',1,'2026-04-27 05:27:29',NULL,0,0,NULL),(24,1,5,'This message was deleted',NULL,'text',1,'2026-04-27 05:34:08',NULL,1,0,NULL),(25,1,5,'Hey',NULL,'text',1,'2026-04-27 05:40:10',NULL,0,0,NULL),(26,1,5,'How',NULL,'text',1,'2026-04-27 06:07:22',NULL,0,0,NULL),(27,1,5,'Yes',NULL,'text',1,'2026-04-27 06:24:00',NULL,0,0,NULL),(28,1,5,'Hello',NULL,'text',1,'2026-04-27 06:24:08',NULL,0,0,NULL),(29,1,4,'Hey',NULL,'text',1,'2026-04-27 09:28:34',NULL,0,0,NULL),(30,2,4,'Hsjjd',NULL,'text',1,'2026-04-27 09:29:30',NULL,0,0,NULL),(31,2,4,'DAVID -EMIS.docx','assets/uploads/1777282183_223f2e6e1b21882b36e8472758dff3bd.docx','file',1,'2026-04-27 09:29:43',NULL,0,0,NULL),(32,2,4,'images.jpg','assets/uploads/1777282206_0586ef81e01da73cd51559cfed385db4.jpg','image',1,'2026-04-27 09:30:06',NULL,0,0,NULL),(33,2,4,'voice_note.webm','assets/uploads/1777282226_64b6f725596bf77b9e3d2d1497417317.webm','video',1,'2026-04-27 09:30:26',NULL,0,0,NULL),(34,2,4,'Hey',NULL,'text',1,'2026-04-27 09:31:05',NULL,0,0,NULL),(35,1,4,'💯',NULL,'text',1,'2026-04-27 09:31:13',NULL,0,0,NULL),(36,1,5,'Y',NULL,'text',1,'2026-04-27 09:35:15',NULL,0,0,NULL),(37,2,5,'Yes',NULL,'text',1,'2026-04-27 09:49:19',NULL,0,0,NULL),(38,2,5,'Ye',NULL,'text',1,'2026-04-27 09:50:37',NULL,0,0,NULL),(39,2,4,'Hey',NULL,'text',1,'2026-04-27 09:53:28',NULL,0,0,NULL),(40,2,2,'This message was deleted',NULL,'text',1,'2026-04-27 12:43:19',NULL,1,1,'sarah_j'),(41,3,2,'Hello',NULL,'text',1,'2026-04-27 12:51:09',NULL,0,0,NULL),(42,2,2,'Why delete',NULL,'text',1,'2026-04-27 12:51:42',NULL,0,0,NULL),(43,2,4,'Hey',NULL,'text',1,'2026-04-30 10:04:47',NULL,0,0,NULL),(44,4,1,'Hello',NULL,'text',1,'2026-04-30 10:27:40',NULL,0,0,NULL),(45,1,4,'Hey',NULL,'text',1,'2026-04-30 12:03:42',NULL,0,0,NULL),(46,2,4,'..',NULL,'text',1,'2026-04-30 12:04:04',NULL,0,0,NULL),(47,2,4,'voice_note.webm','assets/uploads/1777636445_555c511a93b6dfe70c16790b501dd1b1.webm','video',1,'2026-05-01 11:54:05',NULL,0,0,NULL),(48,5,4,'Hello',NULL,'text',1,'2026-05-01 15:21:49',NULL,0,0,NULL),(49,5,2,'Hello',NULL,'text',1,'2026-05-01 15:25:25',NULL,0,0,NULL),(50,2,2,'Hey',NULL,'text',1,'2026-05-01 15:37:10',NULL,0,0,NULL),(51,5,2,'Hey',NULL,'text',1,'2026-05-01 15:49:54',NULL,0,0,NULL),(52,7,2,'Hello',NULL,'text',0,'2026-05-01 15:50:10',NULL,0,0,NULL),(53,2,2,'Hey',NULL,'text',1,'2026-05-01 15:50:19',NULL,0,0,NULL),(54,7,2,'Just got back',NULL,'text',0,'2026-05-01 15:51:39',NULL,0,0,NULL),(55,1,4,'Kslsl',NULL,'text',1,'2026-05-01 16:31:49',NULL,0,0,NULL),(56,8,4,'👋',NULL,'text',0,'2026-05-01 16:46:43',NULL,0,0,NULL),(57,2,4,'Screenshot.png','assets/uploads/1777657299_932db965b5003989ddb39e12b065a8a2.png','image',1,'2026-05-01 17:41:39',NULL,0,0,NULL),(58,9,6,'Hello',NULL,'text',1,'2026-05-02 10:22:56',NULL,0,0,NULL),(59,9,4,'Yes',NULL,'text',0,'2026-05-02 11:25:04',NULL,0,0,NULL),(60,9,4,'Uko aje',NULL,'text',0,'2026-05-03 11:26:20',NULL,0,0,NULL),(61,9,4,'Uko aje',NULL,'text',0,'2026-05-03 11:26:33',NULL,0,0,NULL),(62,8,4,'Hi',NULL,'text',0,'2026-05-03 11:26:48',NULL,0,0,NULL),(63,8,4,'@emma_d',NULL,'text',0,'2026-05-03 11:27:19',NULL,0,0,NULL),(64,2,4,'@sarah_j',NULL,'text',1,'2026-05-03 11:27:36',NULL,0,0,NULL),(65,2,2,'Josh\'s',NULL,'text',1,'2026-05-06 08:14:38',64,0,0,NULL),(66,7,2,'...',NULL,'text',0,'2026-05-06 08:14:59',NULL,0,0,NULL),(67,3,5,'Hello',NULL,'text',1,'2026-05-23 16:10:50',NULL,0,0,NULL),(68,3,5,'How are you',NULL,'text',1,'2026-05-23 16:11:03',NULL,0,0,NULL),(69,3,5,'Wonderful',NULL,'text',1,'2026-05-23 16:13:41',NULL,0,0,NULL),(70,2,5,'Sure',NULL,'text',1,'2026-05-23 16:14:09',NULL,0,0,NULL),(71,10,7,'Hello',NULL,'text',1,'2026-06-16 06:30:18',NULL,0,0,NULL),(72,10,4,'Hi',NULL,'text',0,'2026-06-24 08:11:24',71,0,0,NULL),(73,10,4,'What\'s up',NULL,'text',0,'2026-06-24 08:11:48',71,0,0,NULL),(74,1,5,'Hey',NULL,'text',1,'2026-06-24 08:15:02',45,0,0,NULL),(75,1,5,'What are you doing',NULL,'text',1,'2026-06-24 08:15:15',NULL,0,0,NULL),(76,1,5,'Seriously',NULL,'text',1,'2026-06-24 08:15:24',75,0,0,NULL),(77,3,5,'Hey',NULL,'text',1,'2026-06-24 08:16:49',41,0,0,NULL),(78,1,4,'Yes',NULL,'text',0,'2026-06-24 08:17:54',76,0,0,NULL),(79,1,4,'Yes',NULL,'text',0,'2026-06-24 08:18:26',75,0,0,NULL),(80,2,4,'Okay',NULL,'text',0,'2026-06-24 08:20:09',70,0,0,NULL),(81,11,8,'Hello',NULL,'text',0,'2026-06-24 08:34:10',NULL,0,0,NULL),(82,12,8,'Hi',NULL,'text',0,'2026-06-24 08:35:05',NULL,0,0,NULL),(83,13,11,'HELLO',NULL,'text',0,'2026-09-18 11:17:32',NULL,0,0,NULL);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `type` enum('like','comment','reply','follow','share','message','system') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=309 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (2,1,5,'message',2,'sent you a message',1,'2026-04-26 19:00:12'),(3,2,5,'message',2,'sent you a message',1,'2026-04-26 19:00:12'),(4,3,5,'message',2,'sent you a message',1,'2026-04-26 19:00:12'),(6,1,5,'message',2,'sent you a message',1,'2026-04-26 19:52:38'),(7,2,5,'message',2,'sent you a message',1,'2026-04-26 19:52:38'),(8,3,5,'message',2,'sent you a message',1,'2026-04-26 19:52:38'),(11,3,5,'follow',5,'started following you',1,'2026-04-26 20:10:16'),(12,2,5,'follow',5,'started following you',1,'2026-04-26 20:10:18'),(13,1,5,'message',2,'sent you a file',1,'2026-04-26 20:14:59'),(14,2,5,'message',2,'sent you a file',1,'2026-04-26 20:14:59'),(15,3,5,'message',2,'sent you a file',1,'2026-04-26 20:14:59'),(17,1,5,'message',2,'sent you a message',1,'2026-04-26 20:18:17'),(18,2,5,'message',2,'sent you a message',1,'2026-04-26 20:18:17'),(19,3,5,'message',2,'sent you a message',1,'2026-04-26 20:18:17'),(21,2,1,'message',2,'sent you a message',1,'2026-04-26 20:33:52'),(22,3,1,'message',2,'sent you a message',1,'2026-04-26 20:33:52'),(25,2,1,'message',2,'sent you a message',1,'2026-04-26 20:40:07'),(26,3,1,'message',2,'sent you a message',1,'2026-04-26 20:40:07'),(29,1,5,'message',2,'sent you a message',1,'2026-04-26 20:41:45'),(30,2,5,'message',2,'sent you a message',1,'2026-04-26 20:41:45'),(31,3,5,'message',2,'sent you a message',1,'2026-04-26 20:41:45'),(33,1,4,'message',2,'sent you a message',1,'2026-04-26 20:44:44'),(34,2,4,'message',2,'sent you a message',1,'2026-04-26 20:44:44'),(35,3,4,'message',2,'sent you a message',1,'2026-04-26 20:44:44'),(40,1,4,'message',2,'sent you a message',1,'2026-04-26 21:09:44'),(41,2,4,'message',2,'sent you a message',1,'2026-04-26 21:09:44'),(42,3,4,'message',2,'sent you a message',1,'2026-04-26 21:09:44'),(60,5,4,'message',1,'sent you a message',1,'2026-04-27 09:28:34'),(61,1,4,'message',2,'sent you a message',1,'2026-04-27 09:29:30'),(62,2,4,'message',2,'sent you a message',1,'2026-04-27 09:29:30'),(63,3,4,'message',2,'sent you a message',1,'2026-04-27 09:29:30'),(64,5,4,'message',2,'sent you a message',1,'2026-04-27 09:29:30'),(65,1,4,'message',2,'sent you a file',1,'2026-04-27 09:29:43'),(66,2,4,'message',2,'sent you a file',1,'2026-04-27 09:29:43'),(67,3,4,'message',2,'sent you a file',1,'2026-04-27 09:29:43'),(68,5,4,'message',2,'sent you a file',1,'2026-04-27 09:29:43'),(69,1,4,'message',2,'sent you an image',1,'2026-04-27 09:30:06'),(70,2,4,'message',2,'sent you an image',1,'2026-04-27 09:30:06'),(71,3,4,'message',2,'sent you an image',1,'2026-04-27 09:30:06'),(72,5,4,'message',2,'sent you an image',1,'2026-04-27 09:30:06'),(73,1,4,'message',2,'sent you a message',1,'2026-04-27 09:30:26'),(74,2,4,'message',2,'sent you a message',1,'2026-04-27 09:30:26'),(75,3,4,'message',2,'sent you a message',1,'2026-04-27 09:30:26'),(76,5,4,'message',2,'sent you a message',1,'2026-04-27 09:30:26'),(77,1,4,'message',2,'sent you a message',1,'2026-04-27 09:31:05'),(78,2,4,'message',2,'sent you a message',1,'2026-04-27 09:31:05'),(79,3,4,'message',2,'sent you a message',1,'2026-04-27 09:31:05'),(80,5,4,'message',2,'sent you a message',1,'2026-04-27 09:31:05'),(81,5,4,'message',1,'sent you a message',1,'2026-04-27 09:31:13'),(83,1,5,'message',2,'sent you a message',1,'2026-04-27 09:49:19'),(84,2,5,'message',2,'sent you a message',1,'2026-04-27 09:49:19'),(85,3,5,'message',2,'sent you a message',1,'2026-04-27 09:49:19'),(87,1,5,'message',2,'sent you a message',1,'2026-04-27 09:50:37'),(88,2,5,'message',2,'sent you a message',1,'2026-04-27 09:50:37'),(89,3,5,'message',2,'sent you a message',1,'2026-04-27 09:50:37'),(91,1,4,'message',2,'sent you a message',1,'2026-04-27 09:53:28'),(92,2,4,'message',2,'sent you a message',1,'2026-04-27 09:53:28'),(93,3,4,'message',2,'sent you a message',1,'2026-04-27 09:53:28'),(94,5,4,'message',2,'sent you a message',1,'2026-04-27 09:53:28'),(96,3,2,'follow',2,'started following you',1,'2026-04-27 10:54:48'),(97,3,2,'follow',2,'started following you',1,'2026-04-27 10:59:44'),(98,2,4,'comment',11,'commented on your post',1,'2026-04-27 11:19:14'),(99,2,4,'comment',11,'commented on your post',1,'2026-04-27 11:27:51'),(100,5,4,'comment',6,'commented on your post',1,'2026-04-27 11:28:17'),(101,2,4,'comment',11,'commented on your post',1,'2026-04-27 11:30:08'),(102,5,4,'comment',6,'commented on your post',1,'2026-04-27 11:33:19'),(103,2,5,'comment',11,'commented on your post',1,'2026-04-27 11:33:59'),(104,2,5,'reply',11,'commented on your post',1,'2026-04-27 11:42:20'),(105,2,5,'reply',11,'commented on your post',1,'2026-04-27 11:42:36'),(106,2,5,'comment',11,'commented on your post',1,'2026-04-27 11:49:41'),(107,2,5,'like',11,'liked your post',1,'2026-04-27 11:52:06'),(108,2,5,'share',11,'shared your post',1,'2026-04-27 11:59:51'),(109,2,5,'share',10,'shared your post',1,'2026-04-27 11:59:56'),(111,2,5,'comment',10,'commented on your post',1,'2026-04-27 12:41:39'),(112,5,2,'like',8,'liked your post',1,'2026-04-27 12:44:37'),(113,5,2,'like',7,'liked your post',1,'2026-04-27 12:44:38'),(114,5,2,'like',6,'liked your post',1,'2026-04-27 12:44:40'),(115,5,2,'like',5,'liked your post',1,'2026-04-27 12:44:42'),(117,5,2,'follow',2,'started following you',1,'2026-04-27 12:45:10'),(118,5,2,'message',3,'sent you a message',1,'2026-04-27 12:51:09'),(119,1,2,'message',2,'sent you a message',1,'2026-04-27 12:51:42'),(120,3,2,'message',2,'sent you a message',1,'2026-04-27 12:51:42'),(122,5,2,'message',2,'sent you a message',1,'2026-04-27 12:51:42'),(123,2,4,'share',11,'shared your post',1,'2026-04-30 10:03:39'),(124,2,4,'like',11,'liked your post',1,'2026-04-30 10:03:44'),(125,1,4,'message',2,'sent you a message',1,'2026-04-30 10:04:47'),(126,2,4,'message',2,'sent you a message',1,'2026-04-30 10:04:47'),(127,3,4,'message',2,'sent you a message',1,'2026-04-30 10:04:47'),(128,5,4,'message',2,'sent you a message',1,'2026-04-30 10:04:47'),(129,2,4,'share',10,'shared your post',1,'2026-04-30 10:08:31'),(130,2,4,'like',11,'liked your post',1,'2026-04-30 10:08:38'),(131,5,4,'share',7,'shared your post',1,'2026-04-30 10:08:49'),(132,5,4,'share',8,'shared your post',1,'2026-04-30 10:08:50'),(133,5,4,'share',9,'shared your post',1,'2026-04-30 10:12:14'),(134,5,4,'like',9,'liked your post',1,'2026-04-30 10:12:14'),(135,5,4,'like',8,'liked your post',1,'2026-04-30 10:12:15'),(136,5,4,'like',7,'liked your post',1,'2026-04-30 10:12:15'),(137,5,4,'like',6,'liked your post',1,'2026-04-30 10:12:17'),(138,5,4,'like',5,'liked your post',1,'2026-04-30 10:12:19'),(139,3,1,'message',4,'sent you a message',1,'2026-04-30 10:27:40'),(140,5,4,'message',1,'sent you a message',1,'2026-04-30 12:03:42'),(141,1,4,'message',2,'sent you a message',1,'2026-04-30 12:04:04'),(142,2,4,'message',2,'sent you a message',1,'2026-04-30 12:04:04'),(143,3,4,'message',2,'sent you a message',1,'2026-04-30 12:04:04'),(144,5,4,'message',2,'sent you a message',1,'2026-04-30 12:04:04'),(145,1,4,'message',2,'sent you a message',1,'2026-05-01 11:54:05'),(146,2,4,'message',2,'sent you a message',1,'2026-05-01 11:54:05'),(147,3,4,'message',2,'sent you a message',1,'2026-05-01 11:54:05'),(148,5,4,'message',2,'sent you a message',1,'2026-05-01 11:54:05'),(149,2,4,'follow',4,'started following you',1,'2026-05-01 14:17:14'),(150,2,4,'follow',4,'started following you',1,'2026-05-01 14:17:19'),(151,5,3,'follow',3,'started following you',1,'2026-05-01 14:48:25'),(152,2,3,'follow',3,'started following you',1,'2026-05-01 14:48:26'),(153,2,4,'message',5,'sent you a message',1,'2026-05-01 15:21:49'),(154,5,4,'follow',4,'started following you',1,'2026-05-01 15:22:11'),(155,5,4,'follow',4,'started following you',1,'2026-05-01 15:22:15'),(157,1,2,'message',2,'sent you a message',1,'2026-05-01 15:37:10'),(158,3,2,'message',2,'sent you a message',0,'2026-05-01 15:37:10'),(160,5,2,'message',2,'sent you a message',1,'2026-05-01 15:37:10'),(162,3,2,'message',7,'sent you a message',0,'2026-05-01 15:50:10'),(163,1,2,'message',2,'sent you a message',1,'2026-05-01 15:50:19'),(164,3,2,'message',2,'sent you a message',0,'2026-05-01 15:50:19'),(166,5,2,'message',2,'sent you a message',1,'2026-05-01 15:50:19'),(167,3,2,'message',7,'sent you a message',0,'2026-05-01 15:51:39'),(168,5,4,'message',1,'sent you a message',1,'2026-05-01 16:31:49'),(169,3,4,'message',8,'👋',0,'2026-05-01 16:46:43'),(170,1,4,'message',2,'Screenshot.png',1,'2026-05-01 17:41:39'),(171,2,4,'message',2,'Screenshot.png',1,'2026-05-01 17:41:39'),(172,3,4,'message',2,'Screenshot.png',0,'2026-05-01 17:41:39'),(173,5,4,'message',2,'Screenshot.png',1,'2026-05-01 17:41:39'),(175,5,2,'like',9,'liked your post',1,'2026-05-01 19:51:53'),(177,5,6,'like',5,'liked your post',1,'2026-05-02 10:22:26'),(178,5,6,'like',6,'liked your post',1,'2026-05-02 10:22:28'),(179,5,6,'like',8,'liked your post',1,'2026-05-02 10:22:30'),(180,5,6,'like',9,'liked your post',1,'2026-05-02 10:22:31'),(182,6,4,'message',9,'Yes',1,'2026-05-02 11:25:04'),(184,5,2,'share',8,'shared your post',1,'2026-05-02 13:32:57'),(185,5,2,'comment',8,'Hey',1,'2026-05-02 13:33:04'),(192,5,2,'reply',8,'How',1,'2026-05-02 20:15:55'),(193,5,2,'reply',8,'Seriously',1,'2026-05-02 20:16:03'),(194,5,2,'comment',8,'Hello',1,'2026-05-02 20:16:16'),(195,5,2,'reply',8,'Hsbs',1,'2026-05-02 20:16:26'),(199,5,2,'comment',8,'Hey',1,'2026-05-02 20:34:25'),(200,5,2,'reply',8,'@emma_d',1,'2026-05-02 20:35:04'),(201,5,2,'comment',8,'#freeemmad',1,'2026-05-02 20:35:35'),(205,2,4,'share',15,'shared your post',1,'2026-05-02 21:31:35'),(206,2,4,'like',15,'liked your post',1,'2026-05-02 21:31:37'),(207,2,4,'like',16,'liked your post',1,'2026-05-02 21:31:37'),(208,2,4,'comment',16,'commented on your post',1,'2026-05-03 04:59:50'),(209,3,4,'follow',4,'started following you',0,'2026-05-03 11:19:51'),(210,6,4,'follow',4,'started following you',0,'2026-05-03 11:19:59'),(211,6,4,'message',9,'Uko aje',0,'2026-05-03 11:26:20'),(212,6,4,'message',9,'Uko aje',0,'2026-05-03 11:26:33'),(213,3,4,'message',8,'Hi',0,'2026-05-03 11:26:48'),(214,3,4,'message',8,'@emma_d',0,'2026-05-03 11:27:19'),(215,1,4,'message',2,'@sarah_j',1,'2026-05-03 11:27:36'),(216,2,4,'message',2,'@sarah_j',1,'2026-05-03 11:27:36'),(217,3,4,'message',2,'@sarah_j',0,'2026-05-03 11:27:36'),(218,5,4,'message',2,'@sarah_j',1,'2026-05-03 11:27:36'),(219,6,2,'follow',2,'started following you',0,'2026-05-03 11:34:25'),(220,1,2,'message',2,'Josh\'s',1,'2026-05-06 08:14:38'),(221,3,2,'message',2,'Josh\'s',0,'2026-05-06 08:14:38'),(223,5,2,'message',2,'Josh\'s',1,'2026-05-06 08:14:38'),(224,3,2,'message',7,'...',0,'2026-05-06 08:14:59'),(225,2,4,'comment',30,'commented on your post',1,'2026-05-06 08:16:44'),(226,6,5,'follow',5,'started following you',0,'2026-05-23 16:02:51'),(227,2,5,'reply',30,'commented on your post',1,'2026-05-23 16:07:24'),(228,2,5,'share',30,'shared your post',1,'2026-05-23 16:07:26'),(229,2,5,'like',30,'liked your post',1,'2026-05-23 16:07:27'),(235,2,5,'like',21,'liked your post',1,'2026-05-23 16:08:09'),(236,2,5,'like',16,'liked your post',1,'2026-05-23 16:08:16'),(237,2,5,'like',17,'liked your post',1,'2026-05-23 16:08:17'),(238,2,5,'like',18,'liked your post',1,'2026-05-23 16:08:17'),(239,2,5,'like',20,'liked your post',1,'2026-05-23 16:08:18'),(240,2,5,'like',22,'liked your post',1,'2026-05-23 16:08:19'),(241,2,5,'like',23,'liked your post',1,'2026-05-23 16:08:20'),(246,2,5,'share',22,'shared your post',1,'2026-05-23 16:09:08'),(248,2,5,'message',3,'Hello',1,'2026-05-23 16:10:50'),(249,2,5,'message',3,'How are you',1,'2026-05-23 16:11:03'),(250,2,5,'message',3,'Wonderful',1,'2026-05-23 16:13:41'),(251,1,5,'message',2,'Sure',1,'2026-05-23 16:14:09'),(252,2,5,'message',2,'Sure',1,'2026-05-23 16:14:09'),(253,3,5,'message',2,'Sure',0,'2026-05-23 16:14:09'),(255,2,7,'comment',30,'commented on your post',1,'2026-06-16 06:28:53'),(256,2,7,'like',30,'liked your post',1,'2026-06-16 06:29:06'),(261,5,7,'share',9,'shared your post',1,'2026-06-16 06:32:41'),(262,5,7,'like',9,'liked your post',1,'2026-06-16 06:32:43'),(263,5,7,'comment',9,'commented on your post',1,'2026-06-16 06:32:49'),(264,5,7,'reply',9,'commented on your post',1,'2026-06-16 06:32:57'),(265,2,7,'follow',7,'started following you',1,'2026-06-16 06:33:17'),(266,2,7,'share',30,'shared your post',1,'2026-06-16 06:35:53'),(267,2,7,'like',30,'liked your post',1,'2026-06-16 06:35:59'),(270,7,4,'follow',4,'started following you',0,'2026-06-24 08:11:05'),(271,7,4,'message',10,'Hi',0,'2026-06-24 08:11:24'),(272,7,4,'message',10,'What\'s up',0,'2026-06-24 08:11:48'),(276,2,5,'message',3,'Hey',1,'2026-06-24 08:16:49'),(277,5,4,'message',1,'Yes',0,'2026-06-24 08:17:54'),(278,5,4,'message',1,'Yes',0,'2026-06-24 08:18:26'),(279,1,4,'message',2,'Okay',1,'2026-06-24 08:20:09'),(280,2,4,'message',2,'Okay',1,'2026-06-24 08:20:09'),(281,3,4,'message',2,'Okay',0,'2026-06-24 08:20:09'),(282,5,4,'message',2,'Okay',0,'2026-06-24 08:20:09'),(283,2,4,'like',30,'liked your post',1,'2026-06-24 08:25:05'),(284,2,4,'like',23,'liked your post',1,'2026-06-24 08:25:11'),(285,2,4,'like',22,'liked your post',1,'2026-06-24 08:25:12'),(286,2,4,'like',21,'liked your post',1,'2026-06-24 08:25:12'),(287,2,4,'like',20,'liked your post',1,'2026-06-24 08:25:13'),(288,2,4,'like',18,'liked your post',1,'2026-06-24 08:25:15'),(289,2,4,'like',17,'liked your post',1,'2026-06-24 08:25:15'),(290,2,4,'share',23,'shared your post',1,'2026-06-24 08:25:18'),(291,2,4,'share',21,'shared your post',1,'2026-06-24 08:25:20'),(292,2,4,'share',20,'shared your post',1,'2026-06-24 08:25:21'),(293,2,4,'share',18,'shared your post',1,'2026-06-24 08:25:21'),(294,2,4,'share',17,'shared your post',1,'2026-06-24 08:25:22'),(295,2,4,'share',16,'shared your post',1,'2026-06-24 08:25:23'),(296,5,8,'message',11,'Hello',0,'2026-06-24 08:34:10'),(297,4,8,'message',12,'Hi',0,'2026-06-24 08:35:05'),(298,7,1,'follow',1,'started following you',0,'2026-06-24 09:10:45'),(299,7,9,'follow',9,'started following you',0,'2026-07-03 18:46:24'),(300,4,9,'follow',9,'started following you',0,'2026-07-03 18:46:34'),(301,8,9,'follow',9,'started following you',0,'2026-07-03 18:47:18'),(302,7,9,'follow',9,'started following you',0,'2026-07-03 18:47:23'),(303,2,11,'like',23,'liked your post',0,'2026-09-18 11:12:31'),(304,2,11,'like',22,'liked your post',0,'2026-09-18 11:12:33'),(305,2,11,'like',21,'liked your post',0,'2026-09-18 11:12:35'),(306,2,11,'comment',20,'commented on your post',0,'2026-09-18 11:12:45'),(307,2,11,'reply',20,'commented on your post',0,'2026-09-18 11:12:53'),(308,9,11,'message',13,'sent you a message',0,'2026-09-18 11:17:32');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `media_urls` text DEFAULT NULL,
  `media_type` enum('text','image','video') DEFAULT 'text',
  `privacy` enum('public','followers','private') DEFAULT 'public',
  `likes_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `shares_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (2,4,'Xhvkk','[\"assets/uploads/1777236359_006aec4cc6d0b16508921f970ecef2c8.jpg\"]','image','public',4,8,2,'2026-04-26 20:45:59','2026-05-02 20:58:24'),(5,5,'','[\"assets/uploads/1777238662_99af03ca1497c8be7f8a95a146e1cedf.jpg\"]','image','public',4,0,1,'2026-04-26 21:24:22','2026-05-02 10:22:26'),(6,5,'','[\"assets/uploads/1777238710_4b5b3ba6f0157f5ae854aec00fc39463.png\"]','image','public',4,3,1,'2026-04-26 21:25:10','2026-05-02 10:22:28'),(7,5,'I don&#039;t know what&#039;s going on',NULL,'text','public',2,1,1,'2026-04-27 10:03:55','2026-04-30 10:12:15'),(8,5,'What&#039;s up',NULL,'text','public',3,8,2,'2026-04-27 10:04:08','2026-05-02 20:35:35'),(9,5,'Help','[\"assets\\/uploads\\/1777284296_7b380e911db1e2fe46025291d2f20d50.jpg\"]','image','public',5,5,2,'2026-04-27 10:04:56','2026-06-16 06:32:57'),(10,2,'http://localhost:8000/dsite/pages/index.php\\r\\nJust got back to you and I was thinking of going to a pub with my friends and family in London this morning and I think it was a good idea to do it on the phone',NULL,'text','public',1,1,3,'2026-04-27 10:56:37','2026-05-02 13:32:49'),(11,2,'http://localhost:8000/dsite/pages/index.phprnJust got back to you and I was thinking of going to a pub with my friends and family in London this morning and I think it was a good idea to do it on the phone','[\"assets\\/uploads\\/1777287467_04a287801615d696105920daab931166.jpg\"]','image','public',3,7,3,'2026-04-27 10:57:47','2026-05-01 19:51:45'),(12,4,'group.dataset.sender','[\"assets\\/uploads\\/1777543901_eabafaf8056c6167c79ecd3672fe33cf.jpg\"]','image','public',2,4,1,'2026-04-30 10:11:41','2026-05-02 13:51:43'),(13,4,'php -S localhost:8000','[\"assets\\/uploads\\/1777544010_8959ab3d98dfea6a744a7961361824dd.jpg\"]','image','public',1,8,1,'2026-04-30 10:13:30','2026-05-23 16:09:29'),(15,2,'@emma_d',NULL,'text','public',1,14,1,'2026-05-02 20:23:04','2026-05-02 21:31:37'),(16,2,'#emma_d',NULL,'text','public',2,6,1,'2026-05-02 20:23:18','2026-06-24 08:25:23'),(17,2,'#emmad',NULL,'text','public',2,0,1,'2026-05-02 20:38:31','2026-06-24 08:25:22'),(18,2,'#freepolycap',NULL,'text','public',2,0,1,'2026-05-02 20:56:41','2026-06-24 08:25:21'),(20,2,'#039',NULL,'text','public',2,2,1,'2026-05-02 21:01:05','2026-09-18 11:12:53'),(21,2,'@emm',NULL,'text','public',3,0,1,'2026-05-02 21:28:53','2026-09-18 11:12:35'),(22,2,'@sarah_j welcome to Kenya',NULL,'text','public',3,0,1,'2026-05-02 21:29:40','2026-09-18 11:12:33'),(23,2,'@emma_d jsjsj',NULL,'text','public',3,0,1,'2026-05-02 21:30:18','2026-09-18 11:12:31'),(24,4,'@emmb jjhj #tfjjjjjj546','[\"assets\\/uploads\\/1777758621_d854e187f1d02d59eb35053182528d8b.png\"]','image','public',1,7,1,'2026-05-02 21:50:21','2026-06-24 08:25:10'),(25,4,'@poly #poly',NULL,'text','public',2,1,1,'2026-05-03 05:12:47','2026-06-24 08:25:08'),(26,4,'My first social media pages @emma_d #devs #webs','[\"assets\\/uploads\\/1777807422_6bcc667398fba7f4628e27e7ca704124.png\"]','image','public',2,0,0,'2026-05-03 11:23:42','2026-06-24 08:25:07'),(27,4,'mariadb-dump -u root dating_site > backup.sqltermux-setup-storagecd storage/shared/Documents/\"Shop Easy\" $ cloudflared tunnel --url http://localhost:8000php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\" $ cloudflared tunnel --url http://localhost:8000php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"php -S localhost:8000php -S localhost:8000cd storage/shared/Documents/\"Shop Easy\"','[\"assets\\/uploads\\/1777807471_3ffe319eb24e995ef5275d560c0220ee.png\"]','image','public',1,2,0,'2026-05-03 11:24:31','2026-06-24 08:25:07'),(28,4,'@sarah_j',NULL,'text','public',2,0,2,'2026-05-03 11:31:20','2026-06-24 08:24:56'),(29,4,'@sarah_j',NULL,'text','public',3,1,2,'2026-05-03 11:31:26','2026-06-24 08:25:05'),(30,2,'#labourday',NULL,'text','public',3,3,2,'2026-05-06 08:15:57','2026-06-24 08:25:05'),(31,4,'Hey Everyone',NULL,'text','public',1,0,1,'2026-06-24 08:21:42','2026-06-24 08:22:02'),(32,11,'ON BIG MACHINE','[\"assets/uploads/1789719414_d15e9da1051808de558180056eea44ee.png\"]','image','public',1,0,1,'2026-09-18 11:16:54','2026-09-18 11:17:06'),(33,11,'@POLYN',NULL,'text','public',0,2,0,'2026-09-18 11:19:19','2026-09-18 11:20:28');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_id` int(11) NOT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `reason` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shares`
--

DROP TABLE IF EXISTS `shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shared_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_share` (`post_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shares`
--

LOCK TABLES `shares` WRITE;
/*!40000 ALTER TABLE `shares` DISABLE KEYS */;
INSERT INTO `shares` VALUES (1,2,5,'2026-04-27 08:54:35'),(2,11,5,'2026-04-27 11:59:51'),(3,10,5,'2026-04-27 11:59:56'),(4,5,5,'2026-04-27 12:06:51'),(5,6,5,'2026-04-27 12:18:55'),(6,11,2,'2026-04-27 12:44:18'),(7,11,4,'2026-04-30 10:03:39'),(8,2,4,'2026-04-30 10:04:10'),(9,10,4,'2026-04-30 10:08:31'),(10,7,4,'2026-04-30 10:08:49'),(11,8,4,'2026-04-30 10:08:50'),(12,12,4,'2026-04-30 10:11:48'),(13,9,4,'2026-04-30 10:12:14'),(14,13,4,'2026-04-30 10:13:35'),(15,14,4,'2026-05-01 14:15:16'),(16,10,2,'2026-05-02 13:32:49'),(17,8,2,'2026-05-02 13:32:57'),(18,15,4,'2026-05-02 21:31:35'),(19,30,5,'2026-05-23 16:07:26'),(20,29,5,'2026-05-23 16:07:58'),(21,28,5,'2026-05-23 16:08:03'),(22,25,5,'2026-05-23 16:08:57'),(23,24,5,'2026-05-23 16:09:00'),(24,22,5,'2026-05-23 16:09:08'),(25,29,7,'2026-06-16 06:29:09'),(26,28,7,'2026-06-16 06:29:12'),(27,9,7,'2026-06-16 06:32:41'),(28,30,7,'2026-06-16 06:35:53'),(29,31,4,'2026-06-24 08:21:53'),(30,23,4,'2026-06-24 08:25:18'),(31,21,4,'2026-06-24 08:25:20'),(32,20,4,'2026-06-24 08:25:20'),(33,18,4,'2026-06-24 08:25:21'),(34,17,4,'2026-06-24 08:25:22'),(35,16,4,'2026-06-24 08:25:23'),(36,32,11,'2026-09-18 11:17:06');
/*!40000 ALTER TABLE `shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `starred_messages`
--

DROP TABLE IF EXISTS `starred_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `starred_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_star` (`message_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `starred_messages`
--

LOCK TABLES `starred_messages` WRITE;
/*!40000 ALTER TABLE `starred_messages` DISABLE KEYS */;
INSERT INTO `starred_messages` VALUES (1,20,5,'2026-04-27 06:06:45'),(2,34,4,'2026-04-27 09:53:11');
/*!40000 ALTER TABLE `starred_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stories`
--

DROP TABLE IF EXISTS `stories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `media_type` enum('image','video') DEFAULT 'image',
  `caption` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stories`
--

LOCK TABLES `stories` WRITE;
/*!40000 ALTER TABLE `stories` DISABLE KEYS */;
INSERT INTO `stories` VALUES (1,4,'assets/uploads/1777758785_f86bca556cf3bc62c3f15a7864894e07.png','image','In our prime','2026-05-02 21:53:05','2026-05-03 21:53:05'),(2,4,'assets/uploads/1777760062_d8d360b65677156269b1d7360f2b1b67.png','image','','2026-05-02 22:14:22','2026-05-03 22:14:22'),(3,4,'assets/uploads/1777784309_26480ff0fcb475ed495d096f535d7dcd.png','image','','2026-05-03 04:58:29','2026-05-04 04:58:29'),(4,4,'assets/uploads/1777784334_a81a9b15a2150f91bc41e3590a5f8472.png','image','','2026-05-03 04:58:54','2026-05-04 04:58:54'),(5,4,'assets/uploads/1778055424_8d69dc8715dff3ccecf53187de384eeb.png','image','','2026-05-06 08:17:04','2026-05-07 08:17:04'),(6,9,'assets/uploads/1783104478_eed414504427a890c9b17a063004b169.png','image','','2026-07-03 18:47:58','2026-07-04 18:47:58'),(7,9,'assets/uploads/1783104494_7c73c7b4063533681bdb61224846a28b.png','image','In our prime','2026-07-03 18:48:14','2026-07-04 18:48:14');
/*!40000 ALTER TABLE `stories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stream_chats`
--

DROP TABLE IF EXISTS `stream_chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stream_chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stream` (`stream_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stream_chats`
--

LOCK TABLES `stream_chats` WRITE;
/*!40000 ALTER TABLE `stream_chats` DISABLE KEYS */;
INSERT INTO `stream_chats` VALUES (1,2,2,'❤️','2026-05-01 19:52:54'),(2,2,2,'😂','2026-05-01 19:52:55'),(3,2,2,'🎉','2026-05-01 19:52:58'),(4,2,2,'❤️','2026-05-01 19:54:02'),(5,2,2,'😂','2026-05-01 19:54:04'),(6,2,2,'😂','2026-05-01 19:54:05'),(7,2,2,'❤️','2026-05-01 19:54:05'),(8,2,2,'😂','2026-05-01 19:54:07'),(9,2,2,'😂','2026-05-01 19:54:08'),(10,2,2,'😂','2026-05-01 19:54:08'),(11,2,2,'😂','2026-05-01 19:54:09'),(12,2,2,'😂','2026-05-01 19:54:09'),(13,2,2,'😂','2026-05-01 19:54:09'),(14,2,2,'😂','2026-05-01 19:54:09'),(15,2,2,'😂','2026-05-01 19:54:09'),(16,2,2,'😂','2026-05-01 19:54:10'),(17,2,2,'😂','2026-05-01 19:54:10'),(18,2,2,'😂','2026-05-01 19:54:10'),(19,2,2,'😂','2026-05-01 19:54:10'),(20,2,2,'😂','2026-05-01 19:54:10'),(21,2,2,'😂','2026-05-01 19:54:11'),(22,2,2,'👏','2026-05-01 19:54:12'),(23,2,2,'👏','2026-05-01 19:54:12'),(24,2,2,'👏','2026-05-01 19:54:12'),(25,2,2,'👏','2026-05-01 19:54:13'),(26,2,2,'👏','2026-05-01 19:54:13'),(27,2,2,'👏','2026-05-01 19:54:13'),(28,2,2,'👏','2026-05-01 19:54:13'),(29,2,2,'👏','2026-05-01 19:54:14'),(30,2,2,'👏','2026-05-01 19:54:14'),(31,2,2,'🔥','2026-05-01 19:54:15'),(32,2,2,'🔥','2026-05-01 19:54:15'),(33,2,2,'🔥','2026-05-01 19:54:16'),(34,2,2,'🔥','2026-05-01 19:54:16'),(35,2,2,'🔥','2026-05-01 19:54:16'),(36,2,2,'🎉','2026-05-01 19:54:18'),(37,2,2,'🎉','2026-05-01 19:54:18'),(38,2,2,'🎉','2026-05-01 19:54:18'),(39,2,2,'🎉','2026-05-01 19:54:18'),(40,2,2,'🎉','2026-05-01 19:54:18'),(41,2,2,'🎉','2026-05-01 19:54:19'),(42,2,2,'Bdj','2026-05-03 11:36:07'),(43,2,2,'🔥','2026-05-03 11:36:22'),(44,2,2,'🔥','2026-05-03 11:36:23'),(45,2,2,'🔥','2026-05-03 11:36:23'),(46,2,2,'🔥','2026-05-03 11:36:24'),(47,2,2,'🔥','2026-05-03 11:36:24'),(48,2,2,'🔥','2026-05-03 11:36:24'),(49,2,2,'👏','2026-05-03 11:36:26'),(50,2,2,'😂','2026-05-03 11:36:26'),(51,2,2,'🎉','2026-05-03 11:36:48'),(52,2,2,'🎉','2026-05-03 11:36:50'),(53,2,2,'🔥','2026-05-03 11:36:51'),(54,2,2,'👏','2026-05-03 11:36:52'),(55,2,2,'Joo9','2026-05-03 11:36:58'),(56,2,2,'Hji','2026-05-03 11:37:04');
/*!40000 ALTER TABLE `stream_chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `typing_indicators`
--

DROP TABLE IF EXISTS `typing_indicators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `typing_indicators` (
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `is_typing` tinyint(1) DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`,`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `typing_indicators`
--

LOCK TABLES `typing_indicators` WRITE;
/*!40000 ALTER TABLE `typing_indicators` DISABLE KEYS */;
INSERT INTO `typing_indicators` VALUES (2,2,1,'2026-04-27 12:51:42'),(2,3,0,'2026-04-27 12:51:11'),(4,1,0,'2026-04-30 12:03:43'),(4,2,0,'2026-04-30 12:04:03'),(5,1,0,'2026-04-27 09:35:16'),(5,2,0,'2026-04-27 09:50:38');
/*!40000 ALTER TABLE `typing_indicators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `profile_visibility` enum('public','friends','private') DEFAULT 'public',
  `show_online_status` tinyint(1) DEFAULT 1,
  `allow_messages_from` enum('everyone','friends','none') DEFAULT 'everyone',
  `language` varchar(10) DEFAULT 'en',
  `theme` enum('light','dark') DEFAULT 'light',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_settings`
--

LOCK TABLES `user_settings` WRITE;
/*!40000 ALTER TABLE `user_settings` DISABLE KEYS */;
INSERT INTO `user_settings` VALUES (1,1,1,1,'public',1,'everyone','en','light','2026-04-26 21:37:25'),(2,2,1,1,'public',1,'everyone','en','light','2026-04-26 21:37:25'),(3,3,1,1,'public',1,'everyone','en','light','2026-04-26 21:37:25'),(4,4,1,1,'public',1,'everyone','en','light','2026-04-26 21:37:25'),(8,5,1,1,'public',1,'everyone','en','light','2026-04-26 18:46:30'),(9,6,1,1,'public',1,'everyone','en','light','2026-05-02 10:21:56'),(10,7,1,1,'public',1,'everyone','en','light','2026-06-16 06:28:33'),(11,8,1,1,'public',1,'everyone','en','light','2026-06-24 08:32:01'),(12,9,1,1,'public',1,'everyone','en','light','2026-07-03 18:44:17'),(13,13,1,1,'public',1,'everyone','en','light','2026-09-18 10:56:10'),(14,10,1,1,'public',1,'everyone','en','light','2026-09-18 11:10:13'),(15,11,1,1,'public',1,'everyone','en','light','2026-09-18 11:12:00');
/*!40000 ALTER TABLE `user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'assets/uploads/default-avatar.png',
  `cover_pic` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','non-binary','prefer-not-to-say','other') DEFAULT 'prefer-not-to-say',
  `interested_in` enum('male','female','both','all') DEFAULT 'both',
  `birth_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `education` varchar(100) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `role` enum('user','admin','moderator') DEFAULT 'user',
  `account_status` enum('active','suspended','deactivated') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_online` (`is_online`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@loveconnect.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Site Admin',NULL,'assets/uploads/default-avatar.png',NULL,'prefer-not-to-say','both',NULL,NULL,NULL,NULL,NULL,0,'2026-06-24 09:11:02',1,NULL,'admin','active','2026-04-26 21:37:17',NULL,NULL),(2,'sarah_j','sarah@example.com','$2y$12$i7G99aNS6bDFqHst5cTZyuv.zmUDAEnabB7MQw36jV88mUmEgCX8G','Sarah Johnson','Love traveling and photography ✈️📸','assets/uploads/1777287494_2e3831aedd996bb4c703d76965280fcd.png',NULL,'female','male',NULL,'New York, USA','','','',1,'2026-06-24 08:25:35',1,NULL,'user','active','2026-04-26 21:37:17',NULL,NULL),(3,'mike_w','mike@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Mike Wilson','Fitness enthusiast and food lover 🏋️‍♂️🍕','assets/uploads/1777645169_57bd316251a0a00610f6a6002202132a.png',NULL,'male','female',NULL,'Los Angeles, USA','','','',0,'2026-05-01 14:59:14',1,NULL,'user','active','2026-04-26 21:37:17',NULL,NULL),(4,'emma_d','emma@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Emma Davis','Artist and dreamer 🎨✨\r\nhttp://localhost:8000/pages/posts.php?success=1','assets/uploads/1777721063_57df1dda2910e27cbe60847c2d9fe246.jpg','assets/uploads/1782289440_55cc8ca1447335d9344d13f1b8317255.jpg','female','both',NULL,'London, UK','Teacher','Degree in Education Arts','Music, Technology, Sports',0,'2026-06-24 08:25:30',1,NULL,'user','active','2026-04-26 21:37:17',NULL,NULL),(5,'Poly','nyamongopolycap212@gmail.com','$2y$12$jIYdufeMvuzq9F/kRBeZt.JnwQUcXpg0hNJd.YdMmplMA0dWVhNG.','Polycap Nyamongo','','assets/uploads/1777230698_dafa4a74d32ec7e54e301610159d5fe2.jpg','assets/uploads/1777230707_c573f6ce7fe99702f3e76cfb2f884854.jpg','male','female','1990-04-26','','','','',0,'2026-06-24 08:17:17',0,'89de8e2a54674ac3dee42351db4c95d3996df2c53f99e224110cd25ed83a0fee','user','active','2026-04-26 18:46:30','6647a3808b22dc88ab1f9afd4bd5df3815a678300555b28df532483b55931fff','2026-07-03 19:42:34'),(6,'@johnk','john@gmail.com','$2y$12$MqmtpVkoh98Z/OE9mxW0HuOEmaQfa6MOVCBANj.dgNz02iAemuYDG','John Kamau','','assets/uploads/1777721362_ec19f8c112ba1aaf3a673e2b2d8d8536.jpg',NULL,'male','all','1960-05-02','','','','',0,'2026-05-02 11:29:30',0,'c9151cbe5e569219a13b92aaab2945a57c818802abf799d279a6e3dd022409df','user','active','2026-05-02 10:21:56',NULL,NULL),(7,'Jkal','nyammongopolycap212@gmail.com','$2y$12$3AeH3rwiKxK6lE54b7/J4etKjWIVRuZPIYiXOa7ytZXkE.npRIyQa','Polycap Nyamongo',NULL,'assets/uploads/default-avatar.png',NULL,'male','all','1997-06-16',NULL,NULL,NULL,NULL,1,'2026-06-16 07:17:57',0,'e1ff7a9125f38da1dcd54f7e2faf6059f02c3d5848aba16ba59cd950dff8347e','user','active','2026-06-16 06:28:33',NULL,NULL),(8,'Polyn','pollynmature@gmail.com','$2y$12$SRNt9i3qfn.sPoGotAm.9.6yyYcCFr.WXNWxNwAO1Hnko4kCLuNRK','Polyn',NULL,'assets/uploads/default-avatar.png',NULL,'non-binary','all','1970-06-24',NULL,NULL,NULL,NULL,1,'2026-06-24 08:32:15',0,'666581bf034cb84a1c891b12dd11fea97f90356b3927e530f5083daafaa02aba','user','active','2026-06-24 08:32:01',NULL,NULL),(9,'Pollyn','pyamongopolycap212@gmail.com','$2y$12$4orc/32iuESw0cy2QL7DOu6icSxlmEKoZTYpvIajxRqt6O2oPonh2','Nyamongo Polycap','','assets/uploads/1783104368_a7920129b5657281b9a5fe1e74e742eb.png','assets/uploads/1783104368_a7a9509fc049cfe3980f27903222b89c.png','male','all','2007-07-03','','','','',1,'2026-07-03 18:53:53',0,'2f35d04b31e1ebb51f9ef8daf6be9d8fa97bd62464ab7d38f761b5b53b082173','user','active','2026-07-03 18:44:17',NULL,NULL),(10,'gujkfv','nyamongopoltiugycap212@gmail.com','$2y$10$wSOhkDu0WPe06br.zeVAXuldwA34BOBXSwpVGajgWw9YsGRII0jK6','Nyamongo Polycap',NULL,'assets/uploads/default-avatar.png',NULL,'non-binary','female','2002-05-07',NULL,NULL,NULL,NULL,0,NULL,0,'f305f20b528eb96ec5ebcfc63992461361a2422f0f4f03bb575101e349aa0d5e','user','active','2026-09-18 11:10:13',NULL,NULL),(11,'poly12','nyamongopolycap2@gmail.com','$2y$10$.vL8x4GCBvOjXL8ijy22ceR2EMOD8lKA/FNVyVmMlcxC//OTxymcG','Nyamongo Polycap','','assets/uploads/1789719384_bd1ebc30faabf508d74352a58a230725.png','assets/uploads/1789719384_de1d69dfd20ea42a15a70452b7e338df.png','female','male','2006-06-08','','','','',1,'2026-09-18 11:12:11',0,'5e14f5b80d953831acacf5dff81fab285fef3fb1222a9ea4295bdf8be7b8d23c','user','active','2026-09-18 11:12:00',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-18 12:09:10
