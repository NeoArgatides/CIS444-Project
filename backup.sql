-- MySQL dump 10.13  Distrib 9.3.0, for macos15 (arm64)
--
-- Host: localhost    Database: team3
-- ------------------------------------------------------
-- Server version	9.3.0

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
-- Table structure for table `AdminActions`
--

DROP TABLE IF EXISTS `AdminActions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AdminActions` (
  `ActionID` int NOT NULL AUTO_INCREMENT,
  `AdminID` int NOT NULL,
  `TargetUserID` int NOT NULL,
  `ActionType` enum('Edit','Delete','Ban') NOT NULL,
  `TargetPostID` int DEFAULT NULL,
  `TargetReplyID` int DEFAULT NULL,
  `Notes` text,
  `Timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ActionID`),
  KEY `AdminID` (`AdminID`),
  KEY `TargetUserID` (`TargetUserID`),
  KEY `TargetPostID` (`TargetPostID`),
  KEY `TargetReplyID` (`TargetReplyID`),
  CONSTRAINT `adminactions_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `adminactions_ibfk_2` FOREIGN KEY (`TargetUserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `adminactions_ibfk_3` FOREIGN KEY (`TargetPostID`) REFERENCES `Posts` (`PostID`) ON DELETE SET NULL,
  CONSTRAINT `adminactions_ibfk_4` FOREIGN KEY (`TargetReplyID`) REFERENCES `Replies` (`ReplyID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AdminActions`
--

LOCK TABLES `AdminActions` WRITE;
/*!40000 ALTER TABLE `AdminActions` DISABLE KEYS */;
/*!40000 ALTER TABLE `AdminActions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Likes`
--

DROP TABLE IF EXISTS `Likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Likes` (
  `LikeID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `PostID` int NOT NULL,
  `Timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LikeID`),
  UNIQUE KEY `UserID` (`UserID`,`PostID`),
  KEY `PostID` (`PostID`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`PostID`) REFERENCES `Posts` (`PostID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Likes`
--

LOCK TABLES `Likes` WRITE;
/*!40000 ALTER TABLE `Likes` DISABLE KEYS */;
INSERT INTO `Likes` VALUES (1,2,1,'2025-05-07 13:31:39'),(2,3,1,'2025-05-07 13:31:39'),(3,4,1,'2025-05-07 13:31:39'),(4,5,2,'2025-05-07 13:31:39'),(5,6,2,'2025-05-07 13:31:39'),(6,1,2,'2025-05-07 13:31:39'),(7,7,3,'2025-05-07 13:31:39'),(8,8,3,'2025-05-07 13:31:39'),(9,9,4,'2025-05-07 13:31:39'),(10,10,4,'2025-05-07 13:31:39'),(11,2,4,'2025-05-07 13:31:39'),(12,3,5,'2025-05-07 13:31:39'),(13,4,5,'2025-05-07 13:31:39'),(14,5,5,'2025-05-07 13:31:39'),(15,6,6,'2025-05-07 13:31:39'),(16,7,6,'2025-05-07 13:31:39'),(17,8,6,'2025-05-07 13:31:39'),(18,1,7,'2025-05-07 13:31:39'),(19,2,7,'2025-05-07 13:31:39'),(20,3,8,'2025-05-07 13:31:39'),(21,4,8,'2025-05-07 13:31:39'),(22,5,9,'2025-05-07 13:31:39'),(23,6,9,'2025-05-07 13:31:39'),(24,7,10,'2025-05-07 13:31:39'),(25,8,10,'2025-05-07 13:31:39'),(26,9,11,'2025-05-07 13:31:39'),(27,10,11,'2025-05-07 13:31:39'),(28,1,12,'2025-05-07 13:31:39'),(29,2,12,'2025-05-07 13:31:39'),(30,3,13,'2025-05-07 13:31:39'),(31,4,13,'2025-05-07 13:31:39'),(32,5,14,'2025-05-07 13:31:39'),(33,6,14,'2025-05-07 13:31:39'),(34,7,15,'2025-05-07 13:31:39'),(35,8,15,'2025-05-07 13:31:39'),(36,9,15,'2025-05-07 13:31:39'),(37,10,15,'2025-05-07 13:31:39'),(38,5,1,'2025-05-07 13:31:39'),(39,7,2,'2025-05-07 13:31:39'),(40,6,5,'2025-05-07 13:31:39'),(48,42,2,'2025-05-07 14:38:08'),(51,42,4,'2025-05-07 14:49:24'),(54,42,6,'2025-05-07 14:54:10');
/*!40000 ALTER TABLE `Likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Posts`
--

DROP TABLE IF EXISTS `Posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Posts` (
  `PostID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `Timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Tags` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`PostID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Posts`
--

LOCK TABLES `Posts` WRITE;
/*!40000 ALTER TABLE `Posts` DISABLE KEYS */;
INSERT INTO `Posts` VALUES (1,1,'How to hash passwords in PHP?','I want to securely hash user passwords in PHP. What’s the best approach?','2025-05-07 13:30:12','php,security,password'),(2,2,'What does SQL injection look like?','Can someone show an example of SQL injection and how to prevent it?','2025-05-07 13:30:12','sql,security,backend'),(3,3,'Difference between echo and print in PHP?','Is there a real performance or use case difference?','2025-05-07 13:30:12','php,beginners,syntax'),(4,4,'How to style a sticky header?','My sticky header overlaps content. Tips?','2025-05-07 13:30:12','css,html,frontend'),(5,5,'Why use prepared statements?','I keep hearing about SQL injection. How do prepared statements help?','2025-05-07 13:30:12','php,mysql,security'),(6,6,'Dark mode in CSS?','Is there a way to implement a toggleable dark mode with CSS only?','2025-05-07 13:30:12','css,frontend,design'),(7,7,'What is the use of JOIN in SQL?','Explain INNER, LEFT and RIGHT JOINs with examples.','2025-05-07 13:30:12','sql,databases,joins'),(8,8,'Center a div?','How do I perfectly center a div both vertically and horizontally?','2025-05-07 13:30:12','css,layout,tricks'),(9,9,'Should I use enums in MySQL?','What are the pros/cons of using ENUM in SQL?','2025-05-07 13:30:12','mysql,database,types'),(10,10,'Async in JavaScript?','When should I use async/await vs promises?','2025-05-07 13:30:12','javascript,async,promises'),(11,3,'Login system best practices?','How to make a secure and scalable login system?','2025-05-07 13:30:12','php,login,security'),(12,5,'Differences between GET and POST?','Are there practical use cases for both in forms?','2025-05-07 13:30:12','http,forms,methods'),(13,6,'Best way to store tags?','Should I use comma-separated tags or a relational table?','2025-05-07 13:30:12','mysql,design,tags'),(14,2,'Bootstrap vs Tailwind?','Which one is better for fast frontend development?','2025-05-07 13:30:12','css,bootstrap,tailwind'),(15,1,'How to debug PHP errors?','Any tools or approaches to speed up debugging PHP?','2025-05-07 13:30:12','php,debugging,errors'),(29,42,'my best question','aeaezazea\\r\\nzeaz\\r\\ne\\r\\naze\\r\\naze\\r\\n\r\na\r\nze\r\naz\r\nea\r\ne\r\nae','2025-05-07 14:42:15','security,mysql,sql,frontend,design'),(30,42,'Test question','azeazeazes','2025-05-07 14:53:41','security,mysql,sql,frontend'),(31,42,'zaeaze','azeaeaze','2025-05-07 14:59:33','aze,security,mysql');
/*!40000 ALTER TABLE `Posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Replies`
--

DROP TABLE IF EXISTS `Replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Replies` (
  `ReplyID` int NOT NULL AUTO_INCREMENT,
  `PostID` int NOT NULL,
  `UserID` int NOT NULL,
  `Content` text NOT NULL,
  `Timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ReplyID`),
  KEY `PostID` (`PostID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `Posts` (`PostID`) ON DELETE CASCADE,
  CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Replies`
--

LOCK TABLES `Replies` WRITE;
/*!40000 ALTER TABLE `Replies` DISABLE KEYS */;
INSERT INTO `Replies` VALUES (1,1,2,'Use password_hash and password_verify.','2025-05-07 13:30:52'),(2,1,3,'Never use MD5 or SHA1 for passwords.','2025-05-07 13:30:52'),(3,2,4,'SQL injection usually looks like 1=1 tricks.','2025-05-07 13:30:52'),(4,2,5,'Use prepared statements to avoid it.','2025-05-07 13:30:52'),(5,3,1,'They are almost the same, echo is slightly faster.','2025-05-07 13:30:52'),(6,3,7,'print returns a value, echo doesn’t.','2025-05-07 13:30:52'),(7,4,8,'Use z-index and padding below the header.','2025-05-07 13:30:52'),(8,4,9,'Try using position: sticky with a top value.','2025-05-07 13:30:52'),(9,5,6,'Prepared statements sanitize input.','2025-05-07 13:30:52'),(10,5,2,'They make SQL safer.','2025-05-07 13:30:52'),(11,6,10,'You can use prefers-color-scheme media query.','2025-05-07 13:30:52'),(12,6,1,'Add a toggle class via JS.','2025-05-07 13:30:52'),(13,7,5,'INNER JOIN gives matched rows only.','2025-05-07 13:30:52'),(14,7,3,'LEFT JOIN gives all rows from left table.','2025-05-07 13:30:52'),(15,8,4,'Flexbox is easiest for centering.','2025-05-07 13:30:52'),(16,8,9,'Grid works too.','2025-05-07 13:30:52'),(17,9,6,'Enums are fine but hard to change later.','2025-05-07 13:30:52'),(18,9,7,'Use a separate lookup table for flexibility.','2025-05-07 13:30:52'),(19,10,8,'Async/await is cleaner than .then().','2025-05-07 13:30:52'),(20,10,1,'Promises give more control sometimes.','2025-05-07 13:30:52'),(21,11,2,'Use a secure session handler.','2025-05-07 13:30:52'),(22,11,4,'Validate all inputs.','2025-05-07 13:30:52'),(23,12,3,'GET is for fetching, POST for changing data.','2025-05-07 13:30:52'),(24,12,10,'POST is more secure for sensitive data.','2025-05-07 13:30:52'),(25,13,5,'Use a separate Tag table if you need search/filter.','2025-05-07 13:30:52'),(26,13,6,'Comma-separated tags are quick but limit queries.','2025-05-07 13:30:52'),(27,14,8,'Tailwind offers more flexibility.','2025-05-07 13:30:52'),(28,14,9,'Bootstrap is faster for prototypes.','2025-05-07 13:30:52'),(29,15,1,'Use Xdebug or Laravel Telescope.','2025-05-07 13:30:52'),(30,15,7,'var_dump + die() still works!','2025-05-07 13:30:52'),(31,6,42,'azealejnaleks\r\n\r\naze\r\naz\r\naze\r\na\r\nez','2025-05-07 14:54:19'),(32,6,42,'``aze``','2025-05-07 13:44:05');
/*!40000 ALTER TABLE `Replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Users` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Role` enum('User','Admin') DEFAULT 'User',
  `DateJoined` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `LastConnection` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Description` text,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Username` (`Username`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (1,'alice','alice@example.com','hash1','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Web developer from Berlin.'),(2,'bob','bob@example.com','hash2','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Learning PHP and backend dev.'),(3,'carol','carol@example.com','hash3','Admin','2025-05-07 13:30:09','2025-05-07 13:30:09','System administrator and educator.'),(4,'dave','dave@example.com','hash4','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Cybersecurity hobbyist.'),(5,'eve','eve@example.com','hash5','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Passionate about UI/UX.'),(6,'frank','frank@example.com','hash6','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Full-stack web developer.'),(7,'grace','grace@example.com','hash7','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Python and Django lover.'),(8,'heidi','heidi@example.com','hash8','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Frontend expert.'),(9,'ivan','ivan@example.com','hash9','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Rust and system-level programmer.'),(10,'judy','judy@example.com','hash10','User','2025-05-07 13:30:09','2025-05-07 13:30:09','Tech blogger and enthusiast.'),(42,'Julian','fremont.julian@gmail.com','$2y$12$uCbGhmff7M8ejsP/QX3/UORsEBCdz/xqjU.8lEfLiGDBmzoM6Qn8y','User','2025-05-07 13:31:54','2025-05-07 14:55:38',NULL);
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-07  8:04:58
